<?php
namespace App\Models;

use Core\Database;

class User {
    
    // Assumes you have a database connection available, e.g., from a global variable or a service.
    private static function getDb() {
        return Database::getInstance();
    }

    /**
     * Creates a new user with a password from the registration form.
     */
    public static function create(string $username, string $email, string $password): bool {
        $pdo = self::getDb();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$username, $email, $passwordHash]);
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
        $sql = "INSERT INTO users (username, email, $column) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$username, $email, $socialId]);
    }

}