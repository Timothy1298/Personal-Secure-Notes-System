<?php
namespace Core;

use PDO;
use Exception;

class Cache {
    private static $instance = null;
    private $db;
    private $cacheEnabled = true;
    private $defaultTTL = 3600; // 1 hour
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->initializeCache();
    }
    
    public static function getInstance(PDO $db = null) {
        if (self::$instance === null) {
            if ($db === null) {
                $db = Database::getInstance();
            }
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    private function initializeCache() {
        try {
            // Create cache table if it doesn't exist
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cache (
                    cache_key VARCHAR(255) PRIMARY KEY,
                    cache_value LONGTEXT,
                    expires_at TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Exception $e) {
            error_log("Cache initialization failed: " . $e->getMessage());
            $this->cacheEnabled = false;
        }
    }
    
    /**
     * Get cached value
     */
    public function get($key) {
        if (!$this->cacheEnabled) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT cache_value 
                FROM cache 
                WHERE cache_key = ? AND expires_at > NOW()
            ");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? json_decode($result['cache_value'], true) : null;
        } catch (Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set cached value
     */
    public function set($key, $value, $ttl = null) {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        if ($ttl === null) {
            $ttl = $this->defaultTTL;
        }
        
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            $stmt = $this->db->prepare("
                INSERT INTO cache (cache_key, cache_value, expires_at) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                cache_value = VALUES(cache_value),
                expires_at = VALUES(expires_at)
            ");
            
            return $stmt->execute([$key, json_encode($value), $expiresAt]);
        } catch (Exception $e) {
            error_log("Cache set error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete cached value
     */
    public function delete($key) {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM cache WHERE cache_key = ?");
            return $stmt->execute([$key]);
        } catch (Exception $e) {
            error_log("Cache delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM cache");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Cache clear error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanup() {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM cache WHERE expires_at < NOW()");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Cache cleanup error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        if (!$this->cacheEnabled) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_entries,
                    COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_entries,
                    COUNT(CASE WHEN expires_at <= NOW() THEN 1 END) as expired_entries
                FROM cache
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Cache stats error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cache with callback function
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Cache user-specific data
     */
    public function getUserCache($userId, $key, $callback = null, $ttl = null) {
        $cacheKey = "user_{$userId}_{$key}";
        
        if ($callback === null) {
            return $this->get($cacheKey);
        }
        
        return $this->remember($cacheKey, $callback, $ttl);
    }
    
    /**
     * Invalidate user cache
     */
    public function invalidateUserCache($userId, $pattern = null) {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        try {
            if ($pattern === null) {
                $stmt = $this->db->prepare("DELETE FROM cache WHERE cache_key LIKE ?");
                $stmt->execute(["user_{$userId}_%"]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM cache WHERE cache_key LIKE ?");
                $stmt->execute(["user_{$userId}_{$pattern}%"]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Cache invalidation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enable/disable cache
     */
    public function setEnabled($enabled) {
        $this->cacheEnabled = $enabled;
    }
    
    /**
     * Check if cache is enabled
     */
    public function isEnabled() {
        return $this->cacheEnabled;
    }
}
