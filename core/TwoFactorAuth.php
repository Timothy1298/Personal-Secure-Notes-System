<?php
namespace Core;

use Core\Database;
use Core\Security;
use PDO;

class TwoFactorAuth {
    private static $db;
    
    public static function init() {
        self::$db = Database::getInstance();
    }
    
    // Enable 2FA for user
    public static function enable2FA($userId, $secret = null) {
        self::init();
        
        if (!$secret) {
            $secret = Security::generate2FASecret();
        }
        
        $stmt = self::$db->prepare("
            UPDATE users 
            SET two_factor_enabled = 1, two_factor_secret = ? 
            WHERE id = ?
        ");
        
        return $stmt->execute([$secret, $userId]);
    }
    
    // Disable 2FA for user
    public static function disable2FA($userId) {
        self::init();
        
        $stmt = self::$db->prepare("
            UPDATE users 
            SET two_factor_enabled = 0, two_factor_secret = NULL, backup_codes = NULL 
            WHERE id = ?
        ");
        
        return $stmt->execute([$userId]);
    }
    
    // Generate backup codes
    public static function generateBackupCodes($userId) {
        self::init();
        
        $codes = Security::generateBackupCodes();
        $hashedCodes = array_map('password_hash', $codes, array_fill(0, count($codes), PASSWORD_DEFAULT));
        
        $stmt = self::$db->prepare("
            UPDATE users 
            SET backup_codes = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([json_encode($hashedCodes), $userId]);
        
        return $codes; // Return plain codes for display
    }
    
    // Verify 2FA code
    public static function verifyCode($userId, $code) {
        self::init();
        
        // Get user's 2FA secret
        $stmt = self::$db->prepare("
            SELECT two_factor_secret, backup_codes 
            FROM users 
            WHERE id = ? AND two_factor_enabled = 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        // Check if it's a backup code
        if ($user['backup_codes']) {
            $backupCodes = json_decode($user['backup_codes'], true);
            foreach ($backupCodes as $index => $hashedCode) {
                if (password_verify($code, $hashedCode)) {
                    // Remove used backup code
                    unset($backupCodes[$index]);
                    $stmt = self::$db->prepare("
                        UPDATE users 
                        SET backup_codes = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([json_encode(array_values($backupCodes)), $userId]);
                    
                    Security::logSecurityEvent($userId, 'backup_code_used', ['code' => substr($code, 0, 2) . '****']);
                    return true;
                }
            }
        }
        
        // Verify TOTP code
        if (Security::verify2FACode($user['two_factor_secret'], $code)) {
            Security::logSecurityEvent($userId, '2fa_verified');
            return true;
        }
        
        Security::logSecurityEvent($userId, '2fa_failed');
        return false;
    }
    
    // Check if user has 2FA enabled
    public static function isEnabled($userId) {
        self::init();
        
        $stmt = self::$db->prepare("
            SELECT two_factor_enabled 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['two_factor_enabled'] == 1;
    }
    
    // Get user's 2FA secret
    public static function getSecret($userId) {
        self::init();
        
        $stmt = self::$db->prepare("
            SELECT two_factor_secret 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['two_factor_secret'] : null;
    }
    
    // Get remaining backup codes count
    public static function getBackupCodesCount($userId) {
        self::init();
        
        $stmt = self::$db->prepare("
            SELECT backup_codes 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || !$result['backup_codes']) {
            return 0;
        }
        
        $codes = json_decode($result['backup_codes'], true);
        return count($codes);
    }
    
    // Send 2FA code via email
    public static function sendEmailCode($userId, $email) {
        self::init();
        
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code in database
        $stmt = self::$db->prepare("
            INSERT INTO two_factor_codes (user_id, code, type, expires_at) 
            VALUES (?, ?, 'email', DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        $stmt->execute([$userId, $code]);
        
        // Send email (implement your email service)
        $subject = "Your 2FA Code - SecureNote Pro";
        $message = "Your two-factor authentication code is: {$code}\n\nThis code will expire in 10 minutes.";
        $headers = "From: " . ($_ENV['EMAIL_FROM'] ?? 'tkuria30@gmail.com') . "\r\n";
        
        // For now, just log it (implement actual email sending)
        error_log("2FA Email Code for user {$userId}: {$code}");
        
        return mail($email, $subject, $message, $headers);
    }
    
    // Verify email code
    public static function verifyEmailCode($userId, $code) {
        self::init();
        
        $stmt = self::$db->prepare("
            SELECT id FROM two_factor_codes 
            WHERE user_id = ? AND code = ? AND type = 'email' 
            AND is_used = 0 AND expires_at > NOW()
        ");
        $stmt->execute([$userId, $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Mark code as used
            $stmt = self::$db->prepare("
                UPDATE two_factor_codes 
                SET is_used = 1 
                WHERE id = ?
            ");
            $stmt->execute([$result['id']]);
            
            Security::logSecurityEvent($userId, 'email_2fa_verified');
            return true;
        }
        
        Security::logSecurityEvent($userId, 'email_2fa_failed');
        return false;
    }
    
    // Clean up expired codes
    public static function cleanupExpiredCodes() {
        self::init();
        
        $stmt = self::$db->prepare("
            DELETE FROM two_factor_codes 
            WHERE expires_at < NOW()
        ");
        
        return $stmt->execute();
    }
}
