<?php
namespace Core;

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_strict_mode', 1);
            // Only set secure cookie if HTTPS is actually being used
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        session_unset();
        session_destroy();
    }
    public static function flash($key) {
    if(!empty($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

}
