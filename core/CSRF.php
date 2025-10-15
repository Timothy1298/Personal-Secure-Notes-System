<?php
namespace Core;

class CSRF {
    private static $tokenLifetime = 3600; // 1 hour
    
    public static function generate() {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > self::$tokenLifetime) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check if token has expired
        if ((time() - $_SESSION['csrf_token_time']) > self::$tokenLifetime) {
            self::regenerate();
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function regenerate() {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return self::generate();
    }
    
    public static function getTokenField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generate() . '">';
    }
    
    public static function getTokenMeta() {
        return '<meta name="csrf-token" content="' . self::generate() . '">';
    }
}
