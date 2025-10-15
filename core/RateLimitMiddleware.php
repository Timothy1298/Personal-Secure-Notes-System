<?php
namespace Core;

use PDO;
use Exception;

class RateLimitMiddleware {
    private $rateLimiter;
    private $rules;

    public function __construct(PDO $db) {
        $this->rateLimiter = new RateLimiter($db);
        $this->rules = [
            'login' => ['window' => 900, 'limit' => 5], // 5 attempts per 15 minutes
            'register' => ['window' => 3600, 'limit' => 3], // 3 attempts per hour
            'password_reset' => ['window' => 3600, 'limit' => 3], // 3 attempts per hour
            'api_general' => ['window' => 60, 'limit' => 100], // 100 requests per minute
            'api_heavy' => ['window' => 60, 'limit' => 20], // 20 requests per minute
            'export' => ['window' => 300, 'limit' => 5], // 5 exports per 5 minutes
            'import' => ['window' => 300, 'limit' => 3], // 3 imports per 5 minutes
            'backup' => ['window' => 3600, 'limit' => 10], // 10 backups per hour
            'search' => ['window' => 60, 'limit' => 50], // 50 searches per minute
        ];
    }

    /**
     * Apply rate limiting to a request
     * @param string $ruleName The rate limit rule to apply
     * @param string $identifier Optional custom identifier (defaults to IP)
     * @return bool True if request is allowed, false if rate limited
     */
    public function apply(string $ruleName, string $identifier = null): bool {
        if (!isset($this->rules[$ruleName])) {
            // Default rule if not found
            $ruleName = 'api_general';
        }

        $rule = $this->rules[$ruleName];
        $identifier = $identifier ?? $this->getClientIdentifier();

        return $this->rateLimiter->checkCustom(
            $ruleName,
            $identifier,
            $rule['window'],
            $rule['limit']
        );
    }

    /**
     * Get rate limit status for a rule and identifier
     * @param string $ruleName
     * @param string $identifier
     * @return array
     */
    public function getStatus(string $ruleName, string $identifier = null): array {
        if (!isset($this->rules[$ruleName])) {
            $ruleName = 'api_general';
        }

        $rule = $this->rules[$ruleName];
        $identifier = $identifier ?? $this->getClientIdentifier();

        // Create a temporary rate limiter with custom settings
        $tempLimiter = new RateLimiter($this->rateLimiter->db, $rule['window'], $rule['limit']);
        return $tempLimiter->getStatus($ruleName, $identifier);
    }

    /**
     * Send rate limit headers
     * @param string $ruleName
     * @param string $identifier
     */
    public function sendHeaders(string $ruleName, string $identifier = null): void {
        $status = $this->getStatus($ruleName, $identifier);
        
        header("X-RateLimit-Limit: " . $status['limit']);
        header("X-RateLimit-Remaining: " . $status['remaining']);
        header("X-RateLimit-Reset: " . $status['reset_time']);
        header("X-RateLimit-Window: " . $status['window']);
    }

    /**
     * Handle rate limit exceeded
     * @param string $ruleName
     * @param string $identifier
     */
    public function handleRateLimitExceeded(string $ruleName, string $identifier = null): void {
        $status = $this->getStatus($ruleName, $identifier);
        
        http_response_code(429);
        header("X-RateLimit-Limit: " . $status['limit']);
        header("X-RateLimit-Remaining: 0");
        header("X-RateLimit-Reset: " . $status['reset_time']);
        header("Retry-After: " . ($status['reset_time'] - time()));
        
        echo json_encode([
            'success' => false,
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $status['reset_time'] - time()
        ]);
        exit;
    }

    /**
     * Get client identifier (IP address with some anonymization)
     * @return string
     */
    private function getClientIdentifier(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // For IPv4, anonymize the last octet for privacy
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '0';
                $ip = implode('.', $parts);
            }
        }
        
        return $ip;
    }

    /**
     * Add custom rate limit rule
     * @param string $name
     * @param int $window
     * @param int $limit
     */
    public function addRule(string $name, int $window, int $limit): void {
        $this->rules[$name] = ['window' => $window, 'limit' => $limit];
    }

    /**
     * Remove rate limit rule
     * @param string $name
     */
    public function removeRule(string $name): void {
        unset($this->rules[$name]);
    }

    /**
     * Get all rate limit rules
     * @return array
     */
    public function getRules(): array {
        return $this->rules;
    }

    /**
     * Clear rate limit for a specific rule and identifier
     * @param string $ruleName
     * @param string $identifier
     * @return bool
     */
    public function clear(string $ruleName, string $identifier = null): bool {
        $identifier = $identifier ?? $this->getClientIdentifier();
        return $this->rateLimiter->clear($ruleName, $identifier);
    }

    /**
     * Clean up old rate limit entries
     * @return int
     */
    public function cleanup(): int {
        return $this->rateLimiter->cleanupOld();
    }
}

