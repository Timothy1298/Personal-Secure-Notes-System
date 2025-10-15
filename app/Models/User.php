<?php
namespace App\Models;

use Core\Database;

class User {
    
    // Assumes you have a database connection available, e.g., from a global variable or a service.
    private static function getDb() {
        return Database::getInstance();
    }

    /**
     * Creates a new user with enhanced data from the registration form.
     */
    public static function create(string $username, string $email, string $password, string $firstName = null, string $lastName = null): int|false {
        $pdo = self::getDb();
        $passwordHash = \Core\Security::hashPassword($password);
        
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $email, $passwordHash, $firstName, $lastName])) {
            return (int)$pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Finds a user by their email or username.
     */
    public static function findByEmailOrUsername(string $identifier) {
        $pdo = self::getDb();
        $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    // --- SOCIAL LOGIN FUNCTIONS ---
    
    /**
     * Finds a user by their social provider ID.
     * @param string $provider The social provider (e.g., 'google', 'github').
     * @param string $socialId The unique ID from the social provider.
     */
    public static function findBySocialId(string $provider, string $socialId) {
        $pdo = self::getDb();
        $column = $provider . '_id';
        $sql = "SELECT * FROM users WHERE $column = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$socialId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Finds a user by their email address.
     */
    public static function findByEmail(string $email) {
        $pdo = self::getDb();
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing user record with a new social ID.
     */
    public static function updateSocialId(int $userId, string $provider, string $socialId): bool {
        $pdo = self::getDb();
        $column = $provider . '_id';
        $sql = "UPDATE users SET $column = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$socialId, $userId]);
    }

    /**
     * Creates a new user record from social provider data.
     */
    public static function createFromSocial(string $username, string $email, string $provider, string $socialId): bool {
        $pdo = self::getDb();
        $column = $provider . '_id';
        $sql = "INSERT INTO users (username, email, $column, email_verified) VALUES (?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$username, $email, $socialId]);
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id) {
        $pdo = self::getDb();
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Update last login time
     */
    public static function updateLastLogin(int $userId): bool {
        $pdo = self::getDb();
        $sql = "UPDATE users SET last_login = NOW(), last_activity = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Increment login attempts
     */
    public static function incrementLoginAttempts(int $userId): bool {
        $pdo = self::getDb();
        $sql = "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Reset login attempts
     */
    public static function resetLoginAttempts(int $userId): bool {
        $pdo = self::getDb();
        $sql = "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Create remember token
     */
    public static function createRememberToken(int $userId, string $token): bool {
        $pdo = self::getDb();
        $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $token]);
    }

    /**
     * Create session record
     */
    public static function createSession(int $userId, string $sessionId, string $ipAddress): bool {
        $pdo = self::getDb();
        $sql = "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $sessionId, $ipAddress, $_SERVER['HTTP_USER_AGENT'] ?? 'unknown']);
    }

    /**
     * Create email verification token
     */
    public static function createEmailVerification(int $userId, string $token): bool {
        $pdo = self::getDb();
        $sql = "INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $token]);
    }

    /**
     * Verify email with token
     */
    public static function verifyEmail(string $token): bool {
        $pdo = self::getDb();
        
        // Find valid token
        $sql = "SELECT user_id FROM email_verifications WHERE token = ? AND is_used = 0 AND expires_at > NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // Mark email as verified
        $sql = "UPDATE users SET email_verified = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$result['user_id']]);
        
        // Mark token as used
        $sql = "UPDATE email_verifications SET is_used = 1 WHERE token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        
        return true;
    }

    /**
     * Create password reset token
     */
    public static function createPasswordResetToken(int $userId, string $token): bool {
        $pdo = self::getDb();
        $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $token]);
    }

    /**
     * Reset password with token
     */
    public static function resetPassword(string $token, string $newPassword): bool {
        $pdo = self::getDb();
        
        // Find valid token
        $sql = "SELECT user_id FROM password_reset_tokens WHERE token = ? AND is_used = 0 AND expires_at > NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // Update password
        $passwordHash = \Core\Security::hashPassword($newPassword);
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$passwordHash, $result['user_id']]);
        
        // Mark token as used
        $sql = "UPDATE password_reset_tokens SET is_used = 1 WHERE token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        
        return true;
    }

    /**
     * Get user preferences
     */
    public static function getPreferences(int $userId) {
        $pdo = self::getDb();
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Update user preferences
     */
    public static function updatePreferences(int $userId, array $preferences): bool {
        $pdo = self::getDb();
        
        // Check if preferences exist
        $sql = "SELECT id FROM user_preferences WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Update existing preferences
            $fields = [];
            $values = [];
            foreach ($preferences as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
            $values[] = $userId;
            
            $sql = "UPDATE user_preferences SET " . implode(', ', $fields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($values);
        } else {
            // Insert new preferences
            $fields = array_keys($preferences);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            $values = array_values($preferences);
            $values[] = $userId;
            
            $sql = "INSERT INTO user_preferences (" . implode(', ', $fields) . ", user_id) VALUES ({$placeholders}, ?)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($values);
        }
    }

    /**
     * Get user sessions
     */
    public static function getSessions(int $userId) {
        $pdo = self::getDb();
        $sql = "SELECT * FROM user_sessions WHERE user_id = ? AND is_active = 1 ORDER BY last_activity DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Terminate session
     */
    public static function terminateSession(int $userId, string $sessionId): bool {
        $pdo = self::getDb();
        $sql = "UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $sessionId]);
    }

    /**
     * Terminate all other sessions
     */
    public static function terminateAllOtherSessions(int $userId, string $currentSessionId): bool {
        $pdo = self::getDb();
        $sql = "UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_id != ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $currentSessionId]);
    }

}