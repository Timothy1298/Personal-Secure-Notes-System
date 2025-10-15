<?php
namespace Core;

use PDO;
use SessionHandlerInterface;

class SessionManager implements SessionHandlerInterface {
    private $db;
    private $sessionLifetime;

    public function __construct(PDO $db, int $sessionLifetime = 3600) {
        $this->db = $db;
        $this->sessionLifetime = $sessionLifetime;
    }

    public function open($path, $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        $stmt = $this->db->prepare("SELECT data FROM user_sessions WHERE id = ? AND last_activity > ?");
        $stmt->execute([$id, time() - $this->sessionLifetime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['data'] : '';
    }

    public function write($id, $data): bool {
        $userId = Session::get('user_id'); // Assuming Session::get can retrieve user_id
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $lastActivity = time();

        $stmt = $this->db->prepare("
            REPLACE INTO user_sessions (id, user_id, ip_address, user_agent, last_activity, data)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$id, $userId, $ipAddress, $userAgent, $lastActivity, $data]);
    }

    public function destroy($id): bool {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int|false {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE last_activity < ?");
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }

    public function start(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_save_handler(
                $this,
                true // Register shutdown function
            );
            session_start();
            return true;
        }
        return false;
    }

    public function regenerateId(): bool {
        return session_regenerate_id(true);
    }

    /**
     * Get active sessions for a user
     * @param int $userId
     * @return array
     */
    public function getActiveSessions(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT id, ip_address, user_agent, last_activity, created_at
            FROM user_sessions 
            WHERE user_id = ? AND last_activity > ?
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$userId, time() - $this->sessionLifetime]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Terminate a specific session
     * @param string $sessionId
     * @param int $userId
     * @return bool
     */
    public function terminateSession(string $sessionId, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$sessionId, $userId]);
    }

    /**
     * Terminate all sessions for a user except current
     * @param int $userId
     * @param string $currentSessionId
     * @return int Number of sessions terminated
     */
    public function terminateAllSessions(int $userId, string $currentSessionId = null): int {
        $sql = "DELETE FROM user_sessions WHERE user_id = ?";
        $params = [$userId];
        
        if ($currentSessionId) {
            $sql .= " AND id != ?";
            $params[] = $currentSessionId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Clean up expired sessions
     * @return int Number of sessions cleaned up
     */
    public function cleanupExpiredSessions(): int {
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions 
            WHERE last_activity < ?
        ");
        $stmt->execute([time() - $this->sessionLifetime]);
        return $stmt->rowCount();
    }

    /**
     * Get session statistics
     * @return array
     */
    public function getSessionStats(): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_sessions,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN last_activity > ? THEN 1 END) as active_sessions
            FROM user_sessions
        ");
        $stmt->execute([time() - $this->sessionLifetime]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update session activity
     * @param string $sessionId
     * @return bool
     */
    public function updateActivity(string $sessionId): bool {
        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET last_activity = ? 
            WHERE id = ?
        ");
        return $stmt->execute([time(), $sessionId]);
    }

    /**
     * Check if session is valid and not expired
     * @param string $sessionId
     * @return bool
     */
    public function isSessionValid(string $sessionId): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_sessions 
            WHERE id = ? AND last_activity > ?
        ");
        $stmt->execute([$sessionId, time() - $this->sessionLifetime]);
        return $stmt->fetch() !== false;
    }

    /**
     * Get session info
     * @param string $sessionId
     * @return array|false
     */
    public function getSessionInfo(string $sessionId): array|false {
        $stmt = $this->db->prepare("
            SELECT * FROM user_sessions 
            WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}