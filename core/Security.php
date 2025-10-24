<?php

namespace Core;

use PDO;
use PDOException;

class Security
{
    /**
     * Generate a 2FA secret key
     */
    public static function generate2FASecret(): string
    {
        return base32_encode(random_bytes(20));
    }

    /**
     * Generate QR code URL for 2FA setup
     */
    public static function generate2FAQRCode(string $secret, string $email, string $issuer = 'Secure Notes'): string
    {
        $encodedSecret = urlencode($secret);
        $encodedEmail = urlencode($email);
        $encodedIssuer = urlencode($issuer);
        
        return "otpauth://totp/{$encodedIssuer}:{$encodedEmail}?secret={$encodedSecret}&issuer={$encodedIssuer}";
    }

    /**
     * Verify 2FA code
     */
    public static function verify2FACode(string $secret, string $code): bool
    {
        $timeSlice = floor(time() / 30);
        
        // Check current time slice and adjacent ones (for clock skew)
        for ($i = -1; $i <= 1; $i++) {
            if (self::calculateTOTP($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Calculate TOTP code
     */
    private static function calculateTOTP(string $secret, int $timeSlice): string
    {
        $secretKey = base32_decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord($hm[19]) & 0xf;
        $code = (
            ((ord($hm[$offset + 0]) & 0x7f) << 24) |
            ((ord($hm[$offset + 1]) & 0xff) << 16) |
            ((ord($hm[$offset + 2]) & 0xff) << 8) |
            (ord($hm[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate backup codes for 2FA
     */
    public static function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        }
        return $codes;
    }

    /**
     * Hash backup codes for storage
     */
    public static function hashBackupCodes(array $codes): string
    {
        // Store codes as JSON for easier verification
        return json_encode($codes);
    }

    /**
     * Verify backup code
     */
    public static function verifyBackupCode(string $hashedCodes, string $code): bool
    {
        $codes = json_decode($hashedCodes, true);
        return is_array($codes) && in_array($code, $codes);
    }

    /**
     * Generate secure session ID
     */
    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash password with Argon2ID
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Validate password strength
     */
    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }

    /**
     * Generate secure token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Sanitize input to prevent XSS
     */
    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email format
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit(PDO $db, string $identifier, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        try {
            $stmt = $db->prepare("
                SELECT COUNT(*) as attempts 
                FROM rate_limits 
                WHERE endpoint = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$identifier, $timeWindow]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['attempts'] < $maxAttempts;
        } catch (PDOException $e) {
            error_log("Rate limit check failed: " . $e->getMessage());
            return true; // Allow on error
        }
    }

    /**
     * Record rate limit attempt
     */
    public static function recordRateLimitAttempt(PDO $db, string $identifier): void
    {
        try {
            $stmt = $db->prepare("
                INSERT INTO rate_limits (endpoint, ip_address, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$identifier, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (PDOException $e) {
            error_log("Rate limit recording failed: " . $e->getMessage());
        }
    }

    /**
     * Get client IP address
     */
    public static function getClientIP(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent(PDO $db, int $userId, string $action, string $resourceType = null, int $resourceId = null, array $metadata = []): void
    {
        try {
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, resource_type, resource_id, ip_address, user_agent, metadata, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                $resourceType,
                $resourceId,
                self::getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                json_encode($metadata)
            ]);
        } catch (PDOException $e) {
            error_log("Security event logging failed: " . $e->getMessage());
        }
    }
}

/**
 * Base32 encoding/decoding functions
 */
function base32_encode($data)
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 8;
        $v += ord($data[$i]);
        $vbits += 8;
        
        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $alphabet[$v >> $vbits];
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    if ($vbits > 0) {
        $v <<= (5 - $vbits);
        $output .= $alphabet[$v];
    }
    
    return $output;
}

function base32_decode($data)
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 5;
        $v += strpos($alphabet, $data[$i]);
        $vbits += 5;
        
        if ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr($v >> $vbits);
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    return $output;
}
