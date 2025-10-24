<?php
namespace Core\Database;

use PDO;
use Exception;

class ConnectionPool {
    private $connections = [];
    private $maxConnections;
    private $minConnections;
    private $connectionTimeout;
    private $idleTimeout;
    private $config;
    private $activeConnections = 0;
    private $idleConnections = [];
    private $connectionStats = [];
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->maxConnections = $config['max_connections'] ?? 20;
        $this->minConnections = $config['min_connections'] ?? 5;
        $this->connectionTimeout = $config['connection_timeout'] ?? 30;
        $this->idleTimeout = $config['idle_timeout'] ?? 300; // 5 minutes
        
        $this->initializePool();
    }
    
    /**
     * Initialize the connection pool
     */
    private function initializePool(): void {
        for ($i = 0; $i < $this->minConnections; $i++) {
            $connection = $this->createConnection();
            if ($connection) {
                $this->idleConnections[] = [
                    'connection' => $connection,
                    'created_at' => time(),
                    'last_used' => time()
                ];
            }
        }
    }
    
    /**
     * Create a new database connection
     */
    private function createConnection(): ?PDO {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4";
            
            $connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => $this->connectionTimeout
            ]);
            
            $this->activeConnections++;
            
            return $connection;
            
        } catch (Exception $e) {
            error_log("Failed to create database connection: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get a connection from the pool
     */
    public function getConnection(): ?PDO {
        // Try to get an idle connection first
        if (!empty($this->idleConnections)) {
            $idleConnection = array_shift($this->idleConnections);
            
            // Check if connection is still valid
            if ($this->isConnectionValid($idleConnection['connection'])) {
                $idleConnection['last_used'] = time();
                $this->connections[] = $idleConnection;
                return $idleConnection['connection'];
            } else {
                $this->activeConnections--;
            }
        }
        
        // Create new connection if under limit
        if ($this->activeConnections < $this->maxConnections) {
            $connection = $this->createConnection();
            if ($connection) {
                $this->connections[] = [
                    'connection' => $connection,
                    'created_at' => time(),
                    'last_used' => time()
                ];
                return $connection;
            }
        }
        
        // Wait for available connection
        return $this->waitForConnection();
    }
    
    /**
     * Return a connection to the pool
     */
    public function returnConnection(PDO $connection): void {
        foreach ($this->connections as $index => $conn) {
            if ($conn['connection'] === $connection) {
                unset($this->connections[$index]);
                
                // Check if connection is still valid
                if ($this->isConnectionValid($connection)) {
                    $conn['last_used'] = time();
                    $this->idleConnections[] = $conn;
                } else {
                    $this->activeConnections--;
                }
                
                break;
            }
        }
        
        // Clean up idle connections
        $this->cleanupIdleConnections();
    }
    
    /**
     * Check if connection is still valid
     */
    private function isConnectionValid(PDO $connection): bool {
        try {
            $stmt = $connection->prepare("SELECT 1");
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Wait for an available connection
     */
    private function waitForConnection(): ?PDO {
        $timeout = time() + $this->connectionTimeout;
        
        while (time() < $timeout) {
            // Try to get an idle connection
            if (!empty($this->idleConnections)) {
                $idleConnection = array_shift($this->idleConnections);
                
                if ($this->isConnectionValid($idleConnection['connection'])) {
                    $idleConnection['last_used'] = time();
                    $this->connections[] = $idleConnection;
                    return $idleConnection['connection'];
                } else {
                    $this->activeConnections--;
                }
            }
            
            // Try to create new connection
            if ($this->activeConnections < $this->maxConnections) {
                $connection = $this->createConnection();
                if ($connection) {
                    $this->connections[] = [
                        'connection' => $connection,
                        'created_at' => time(),
                        'last_used' => time()
                    ];
                    return $connection;
                }
            }
            
            // Wait a bit before trying again
            usleep(100000); // 100ms
        }
        
        throw new Exception("Connection pool timeout: No available connections");
    }
    
    /**
     * Clean up idle connections
     */
    private function cleanupIdleConnections(): void {
        $currentTime = time();
        $minIdleConnections = $this->minConnections;
        
        foreach ($this->idleConnections as $index => $connection) {
            $idleTime = $currentTime - $connection['last_used'];
            
            // Remove connections that have been idle too long
            if ($idleTime > $this->idleTimeout && count($this->idleConnections) > $minIdleConnections) {
                unset($this->idleConnections[$index]);
                $this->activeConnections--;
            }
        }
        
        // Re-index array
        $this->idleConnections = array_values($this->idleConnections);
    }
    
    /**
     * Execute a query using a connection from the pool
     */
    public function execute(string $query, array $params = []): array {
        $connection = $this->getConnection();
        
        try {
            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            
            if (stripos($query, 'SELECT') === 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = [
                    'affected_rows' => $stmt->rowCount(),
                    'last_insert_id' => $connection->lastInsertId()
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Query execution error: " . $e->getMessage());
            throw $e;
        } finally {
            $this->returnConnection($connection);
        }
    }
    
    /**
     * Execute a transaction using a connection from the pool
     */
    public function transaction(callable $callback) {
        $connection = $this->getConnection();
        
        try {
            $connection->beginTransaction();
            $result = $callback($connection);
            $connection->commit();
            return $result;
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        } finally {
            $this->returnConnection($connection);
        }
    }
    
    /**
     * Get pool statistics
     */
    public function getStats(): array {
        $this->cleanupIdleConnections();
        
        return [
            'active_connections' => $this->activeConnections,
            'idle_connections' => count($this->idleConnections),
            'max_connections' => $this->maxConnections,
            'min_connections' => $this->minConnections,
            'utilization_percent' => round(($this->activeConnections / $this->maxConnections) * 100, 2),
            'connection_stats' => $this->connectionStats
        ];
    }
    
    /**
     * Get detailed connection information
     */
    public function getConnectionInfo(): array {
        $info = [
            'active' => [],
            'idle' => []
        ];
        
        foreach ($this->connections as $conn) {
            $info['active'][] = [
                'created_at' => date('Y-m-d H:i:s', $conn['created_at']),
                'last_used' => date('Y-m-d H:i:s', $conn['last_used']),
                'age_seconds' => time() - $conn['created_at'],
                'idle_seconds' => time() - $conn['last_used']
            ];
        }
        
        foreach ($this->idleConnections as $conn) {
            $info['idle'][] = [
                'created_at' => date('Y-m-d H:i:s', $conn['created_at']),
                'last_used' => date('Y-m-d H:i:s', $conn['last_used']),
                'age_seconds' => time() - $conn['created_at'],
                'idle_seconds' => time() - $conn['last_used']
            ];
        }
        
        return $info;
    }
    
    /**
     * Close all connections
     */
    public function closeAll(): void {
        // Close active connections
        foreach ($this->connections as $conn) {
            $conn['connection'] = null;
        }
        
        // Close idle connections
        foreach ($this->idleConnections as $conn) {
            $conn['connection'] = null;
        }
        
        $this->connections = [];
        $this->idleConnections = [];
        $this->activeConnections = 0;
    }
    
    /**
     * Health check for the connection pool
     */
    public function healthCheck(): array {
        $health = [
            'status' => 'healthy',
            'active_connections' => $this->activeConnections,
            'idle_connections' => count($this->idleConnections),
            'max_connections' => $this->maxConnections,
            'utilization_percent' => round(($this->activeConnections / $this->maxConnections) * 100, 2),
            'issues' => []
        ];
        
        // Check for high utilization
        if ($health['utilization_percent'] > 80) {
            $health['issues'][] = 'High connection utilization';
            $health['status'] = 'warning';
        }
        
        // Check for connection errors
        if ($this->activeConnections === 0 && count($this->idleConnections) === 0) {
            $health['issues'][] = 'No available connections';
            $health['status'] = 'critical';
        }
        
        // Test connection
        try {
            $connection = $this->getConnection();
            if ($connection) {
                $stmt = $connection->prepare("SELECT 1");
                $stmt->execute();
                $this->returnConnection($connection);
            }
        } catch (Exception $e) {
            $health['issues'][] = 'Connection test failed: ' . $e->getMessage();
            $health['status'] = 'critical';
        }
        
        return $health;
    }
    
    /**
     * Resize the connection pool
     */
    public function resize(int $minConnections, int $maxConnections): void {
        $this->minConnections = $minConnections;
        $this->maxConnections = $maxConnections;
        
        // Add more connections if needed
        while (count($this->idleConnections) < $this->minConnections) {
            $connection = $this->createConnection();
            if ($connection) {
                $this->idleConnections[] = [
                    'connection' => $connection,
                    'created_at' => time(),
                    'last_used' => time()
                ];
            } else {
                break;
            }
        }
        
        // Remove excess connections
        $this->cleanupIdleConnections();
    }
    
    /**
     * Get connection pool configuration
     */
    public function getConfig(): array {
        return [
            'max_connections' => $this->maxConnections,
            'min_connections' => $this->minConnections,
            'connection_timeout' => $this->connectionTimeout,
            'idle_timeout' => $this->idleTimeout,
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'database' => $this->config['database']
        ];
    }
    
    /**
     * Update connection statistics
     */
    private function updateStats(string $operation, float $duration): void {
        if (!isset($this->connectionStats[$operation])) {
            $this->connectionStats[$operation] = [
                'count' => 0,
                'total_duration' => 0,
                'avg_duration' => 0,
                'min_duration' => PHP_FLOAT_MAX,
                'max_duration' => 0
            ];
        }
        
        $stats = &$this->connectionStats[$operation];
        $stats['count']++;
        $stats['total_duration'] += $duration;
        $stats['avg_duration'] = $stats['total_duration'] / $stats['count'];
        $stats['min_duration'] = min($stats['min_duration'], $duration);
        $stats['max_duration'] = max($stats['max_duration'], $duration);
    }
}
