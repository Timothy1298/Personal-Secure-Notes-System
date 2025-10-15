<?php
namespace Core;

use PDO;
use Exception;

class RateLimiter {
    private $db;
    private $window; // Time window in seconds
    private $limit;  // Max requests in the window

    public function __construct(PDO $db, int $window = 60, int $limit = 100) {
        $this->db = $db;
        $this->window = $window;
        $this->limit = $limit;
    }

    /**
     * Checks if a request is allowed based on rate limits.
     * @param string $key A unique key for the rate limit (e.g., 'login_attempts', 'api_requests').
     * @param string $identifier A unique identifier for the client (e.g., IP address, user ID).
     * @return bool True if the request is allowed, false otherwise.
     */
    public function check(string $key, string $identifier): bool {
        $currentTime = time();
        $expirationTime = $currentTime - $this->window;

        // Clean up old entries for this key and identifier
        $this->cleanup($key, $identifier, $expirationTime);

        // Count current requests within the window
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM rate_limits 
            WHERE rate_key = ? AND identifier = ? AND created_at > FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);
        $count = $stmt->fetchColumn();

        if ($count >= $this->limit) {
            return false; // Limit exceeded
        }

        // Record the current request
        $this->record($key, $identifier);

        return true; // Request allowed
    }

    /**
     * Records a new request for rate limiting.
     * @param string $key
     * @param string $identifier
     */
    private function record(string $key, string $identifier): void {
        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (rate_key, identifier, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$key, $identifier]);
    }

    /**
     * Cleans up expired rate limit entries.
     * @param string $key
     * @param string $identifier
     * @param int $expirationTime Unix timestamp for expiration.
     */
    private function cleanup(string $key, string $identifier, int $expirationTime): void {
        $stmt = $this->db->prepare("
            DELETE FROM rate_limits 
            WHERE rate_key = ? AND identifier = ? AND created_at < FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);
    }

    /**
     * Get remaining requests for a given key and identifier.
     * @param string $key
     * @param string $identifier
     * @return int
     */
    public function getRemaining(string $key, string $identifier): int {
        $currentTime = time();
        $expirationTime = $currentTime - $this->window;

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM rate_limits 
            WHERE rate_key = ? AND identifier = ? AND created_at > FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);
        $count = $stmt->fetchColumn();

        return max(0, $this->limit - $count);
    }

    /**
     * Get the reset time for a given key and identifier.
     * @param string $key
     * @param string $identifier
     * @return int Unix timestamp when the rate limit resets.
     */
    public function getResetTime(string $key, string $identifier): int {
        $stmt = $this->db->prepare("
            SELECT MIN(UNIX_TIMESTAMP(created_at)) FROM rate_limits 
            WHERE rate_key = ? AND identifier = ?
        ");
        $stmt->execute([$key, $identifier]);
        $firstRequestTime = $stmt->fetchColumn();

        if (!$firstRequestTime) {
            return time(); // No requests yet, so no reset needed
        }

        return $firstRequestTime + $this->window;
    }

    /**
     * Check rate limit with custom window and limit
     * @param string $key
     * @param string $identifier
     * @param int $window
     * @param int $limit
     * @return bool
     */
    public function checkCustom(string $key, string $identifier, int $window, int $limit): bool {
        $currentTime = time();
        $expirationTime = $currentTime - $window;

        // Clean up old entries
        $stmt = $this->db->prepare("
            DELETE FROM rate_limits 
            WHERE rate_key = ? AND ip_address = ? AND created_at < FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);

        // Count current requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM rate_limits 
            WHERE rate_key = ? AND ip_address = ? AND created_at > FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);
        $count = $stmt->fetchColumn();

        if ($count >= $limit) {
            return false;
        }

        // Record the request
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (rate_key, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$key, $identifier, $userAgent]);

        return true;
    }

    /**
     * Get rate limit status for a key and identifier
     * @param string $key
     * @param string $identifier
     * @return array
     */
    public function getStatus(string $key, string $identifier): array {
        $currentTime = time();
        $expirationTime = $currentTime - $this->window;

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, MIN(UNIX_TIMESTAMP(created_at)) as first_request
            FROM rate_limits 
            WHERE rate_key = ? AND ip_address = ? AND created_at > FROM_UNIXTIME(?)
        ");
        $stmt->execute([$key, $identifier, $expirationTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $count = (int)$result['count'];
        $remaining = max(0, $this->limit - $count);
        $resetTime = $result['first_request'] ? $result['first_request'] + $this->window : $currentTime;

        return [
            'allowed' => $count < $this->limit,
            'count' => $count,
            'limit' => $this->limit,
            'remaining' => $remaining,
            'reset_time' => $resetTime,
            'window' => $this->window
        ];
    }

    /**
     * Clear rate limit for a specific key and identifier
     * @param string $key
     * @param string $identifier
     * @return bool
     */
    public function clear(string $key, string $identifier): bool {
        $stmt = $this->db->prepare("
            DELETE FROM rate_limits 
            WHERE rate_key = ? AND ip_address = ?
        ");
        return $stmt->execute([$key, $identifier]);
    }

    /**
     * Clean up old rate limit entries (call this periodically)
     * @param int $maxAge Maximum age in seconds
     * @return int Number of entries cleaned up
     */
    public function cleanupOld(int $maxAge = 3600): int {
        $stmt = $this->db->prepare("
            DELETE FROM rate_limits 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$maxAge]);
        return $stmt->rowCount();
    }
}