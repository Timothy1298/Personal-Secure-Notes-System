<?php
namespace Core;

use Core\Database;
use PDO;

class Logger {
    public static function log($user_id, $action, $ip = null, $user_agent = null) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address, user_agent) VALUES (:user_id, :action, :ip, :ua)");
        $stmt->execute([
            ':user_id'   => $user_id,
            ':action'    => $action,
            ':ip'        => $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':ua'        => $user_agent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}
