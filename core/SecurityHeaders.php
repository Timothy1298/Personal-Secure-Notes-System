<?php
namespace Core;

class SecurityHeaders {
    
    /**
     * Set all security headers
     */
    public static function setAll() {
        self::setHTTPS();
        self::setCSP();
        self::setHSTS();
        self::setXFrameOptions();
        self::setXContentTypeOptions();
        self::setXSSProtection();
        self::setReferrerPolicy();
        self::setPermissionsPolicy();
    }
    
    /**
     * Force HTTPS
     */
    public static function setHTTPS() {
        if (!self::isHTTPS() && !self::isLocalhost()) {
            $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirectURL", true, 301);
            exit;
        }
    }
    
    /**
     * Content Security Policy
     */
    public static function setCSP() {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' ws: wss:",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'"
        ];
        
        header("Content-Security-Policy: " . implode('; ', $csp));
    }
    
    /**
     * HTTP Strict Transport Security
     */
    public static function setHSTS() {
        if (self::isHTTPS()) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }
    
    /**
     * X-Frame-Options
     */
    public static function setXFrameOptions() {
        header("X-Frame-Options: DENY");
    }
    
    /**
     * X-Content-Type-Options
     */
    public static function setXContentTypeOptions() {
        header("X-Content-Type-Options: nosniff");
    }
    
    /**
     * X-XSS-Protection
     */
    public static function setXSSProtection() {
        header("X-XSS-Protection: 1; mode=block");
    }
    
    /**
     * Referrer Policy
     */
    public static function setReferrerPolicy() {
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
    
    /**
     * Permissions Policy
     */
    public static function setPermissionsPolicy() {
        $permissions = [
            "geolocation=()",
            "microphone=()",
            "camera=()",
            "payment=()",
            "usb=()",
            "magnetometer=()",
            "gyroscope=()",
            "speaker=()",
            "vibrate=()",
            "fullscreen=(self)",
            "sync-xhr=()"
        ];
        
        header("Permissions-Policy: " . implode(', ', $permissions));
    }
    
    /**
     * Check if request is HTTPS
     */
    private static function isHTTPS() {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        );
    }
    
    /**
     * Check if request is localhost
     */
    private static function isLocalhost() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return (
            $host === 'localhost' ||
            $host === '127.0.0.1' ||
            strpos($host, 'localhost:') === 0 ||
            strpos($host, '127.0.0.1:') === 0
        );
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate and sanitize email
     */
    public static function sanitizeEmail($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }
    
    /**
     * Validate and sanitize URL
     */
    public static function sanitizeURL($url) {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }
    
    /**
     * Generate secure random token
     */
    public static function generateSecureToken($length = 32) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            // Fallback for older PHP versions
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= chr(mt_rand(0, 255));
            }
            return bin2hex($token);
        }
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'No file uploaded';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File too large';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Invalid file type';
            }
        }
        
        // Check for malicious content
        $content = file_get_contents($file['tmp_name']);
        if (strpos($content, '<?php') !== false || strpos($content, '<script') !== false) {
            $errors[] = 'Potentially malicious file content';
        }
        
        return $errors;
    }
    
    /**
     * Rate limiting per user
     */
    public static function checkUserRateLimit(PDO $db, $userId, $action, $maxAttempts = 10, $timeWindow = 300) {
        try {
            $stmt = $db->prepare("
                SELECT COUNT(*) as attempts 
                FROM rate_limits 
                WHERE endpoint = ? AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$action, $userId, $timeWindow]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['attempts'] < $maxAttempts;
        } catch (Exception $e) {
            error_log("Rate limit check failed: " . $e->getMessage());
            return true; // Allow on error
        }
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent(PDO $db, $userId, $action, $details = []) {
        try {
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, ip_address, user_agent, details, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                self::getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                json_encode($details)
            ]);
        } catch (Exception $e) {
            error_log("Security event logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
