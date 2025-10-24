<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Database\Optimizer;
use Core\Database\ReplicationManager;
use Core\Database\QueryBuilder;
use Core\Database\ConnectionPool;
use Exception;

class DatabaseController {
    private $db;
    private $optimizer;
    private $replicationManager;
    private $queryBuilder;
    private $connectionPool;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->optimizer = new Optimizer($this->db);
        $this->queryBuilder = new QueryBuilder($this->db);
        
        // Initialize replication manager if configured
        $slaveConfigs = $this->getSlaveConfigs();
        if (!empty($slaveConfigs)) {
            $this->replicationManager = new ReplicationManager($this->db, $slaveConfigs);
        }
        
        // Initialize connection pool
        $poolConfig = $this->getPoolConfig();
        $this->connectionPool = new ConnectionPool($poolConfig);
    }
    
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        
        // Get database performance analysis
        $performanceAnalysis = $this->optimizer->analyzePerformance();
        
        // Get replication status if available
        $replicationStatus = null;
        if ($this->replicationManager) {
            $replicationStatus = $this->replicationManager->getReplicationStatus();
        }
        
        // Get connection pool stats
        $poolStats = $this->connectionPool->getStats();
        
        // Get database health
        $healthCheck = $this->performHealthCheck();
        
        include __DIR__ . '/../Views/database_management.php';
    }
    
    public function analyzePerformance() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $analysis = $this->optimizer->analyzePerformance();
            echo json_encode(['success' => true, 'data' => $analysis]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function optimizeTable() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $tableName = $_POST['table_name'] ?? '';
        
        if (empty($tableName)) {
            echo json_encode(['success' => false, 'message' => 'Table name is required']);
            exit;
        }
        
        try {
            $result = $this->optimizer->optimizeTable($tableName);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function createIndex() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $tableName = $_POST['table_name'] ?? '';
        $indexName = $_POST['index_name'] ?? '';
        $columns = $_POST['columns'] ?? [];
        $type = $_POST['type'] ?? 'INDEX';
        
        if (empty($tableName) || empty($indexName) || empty($columns)) {
            echo json_encode(['success' => false, 'message' => 'Table name, index name, and columns are required']);
            exit;
        }
        
        try {
            $result = $this->optimizer->createIndex($tableName, $indexName, $columns, $type);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function dropIndex() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $tableName = $_POST['table_name'] ?? '';
        $indexName = $_POST['index_name'] ?? '';
        
        if (empty($tableName) || empty($indexName)) {
            echo json_encode(['success' => false, 'message' => 'Table name and index name are required']);
            exit;
        }
        
        try {
            $result = $this->optimizer->dropIndex($tableName, $indexName);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function analyzeTable() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $tableName = $_POST['table_name'] ?? '';
        
        if (empty($tableName)) {
            echo json_encode(['success' => false, 'message' => 'Table name is required']);
            exit;
        }
        
        try {
            $result = $this->optimizer->analyzeTable($tableName);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function explainQuery() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $query = $_POST['query'] ?? '';
        $params = $_POST['params'] ?? [];
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'Query is required']);
            exit;
        }
        
        try {
            $result = $this->optimizer->explainQuery($query, $params);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getReplicationStatus() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        if (!$this->replicationManager) {
            echo json_encode(['success' => false, 'message' => 'Replication not configured']);
            exit;
        }
        
        try {
            $status = $this->replicationManager->getReplicationStatus();
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getConnectionPoolStats() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $stats = $this->connectionPool->getStats();
            $info = $this->connectionPool->getConnectionInfo();
            $health = $this->connectionPool->healthCheck();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'info' => $info,
                    'health' => $health
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getConfigurationRecommendations() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $recommendations = $this->optimizer->getConfigurationRecommendations();
            echo json_encode(['success' => true, 'data' => $recommendations]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getSlowQueries() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    query_text,
                    execution_time_ms,
                    rows_examined,
                    rows_sent,
                    user_id,
                    created_at
                FROM slow_query_logs 
                ORDER BY execution_time_ms DESC 
                LIMIT 50
            ");
            $stmt->execute();
            $slowQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $slowQueries]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getOptimizationRecommendations() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    recommendation_type,
                    title,
                    description,
                    priority,
                    status,
                    estimated_impact,
                    sql_command,
                    created_at
                FROM optimization_recommendations 
                WHERE status IN ('pending', 'in_progress')
                ORDER BY 
                    CASE priority 
                        WHEN 'critical' THEN 1 
                        WHEN 'high' THEN 2 
                        WHEN 'medium' THEN 3 
                        WHEN 'low' THEN 4 
                    END,
                    created_at DESC
            ");
            $stmt->execute();
            $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $recommendations]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function updateRecommendationStatus() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $recommendationId = $_POST['recommendation_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if (empty($recommendationId) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Recommendation ID and status are required']);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE optimization_recommendations 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $recommendationId]);
            
            echo json_encode(['success' => true, 'message' => 'Recommendation status updated']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getTableStatistics() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    table_name,
                    row_count,
                    data_size_mb,
                    index_size_mb,
                    total_size_mb,
                    fragmentation_percent,
                    last_optimized,
                    updated_at
                FROM table_statistics 
                ORDER BY total_size_mb DESC
            ");
            $stmt->execute();
            $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $statistics]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getDatabaseHealth() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $healthCheck = $this->performHealthCheck();
            echo json_encode(['success' => true, 'data' => $healthCheck]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function executeCustomQuery() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $query = $_POST['query'] ?? '';
        $params = $_POST['params'] ?? [];
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'Query is required']);
            exit;
        }
        
        // Only allow SELECT queries for security
        if (stripos(trim($query), 'SELECT') !== 0) {
            echo json_encode(['success' => false, 'message' => 'Only SELECT queries are allowed']);
            exit;
        }
        
        try {
            $startTime = microtime(true);
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            // Log query performance
            $this->logQueryPerformance($query, $executionTime, $stmt->rowCount());
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'execution_time_ms' => $executionTime,
                'rows_returned' => count($results)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    private function performHealthCheck(): array {
        $healthChecks = [];
        
        try {
            // Check database connection
            $startTime = microtime(true);
            $stmt = $this->db->prepare("SELECT 1");
            $stmt->execute();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $healthChecks[] = [
                'check_type' => 'connection',
                'check_name' => 'Database Connection',
                'status' => 'healthy',
                'value' => $responseTime,
                'threshold' => 100,
                'message' => "Response time: {$responseTime}ms"
            ];
            
            // Check buffer pool hit ratio
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Innodb_buffer_pool_read_requests'");
            $stmt->execute();
            $readRequests = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;
            
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Innodb_buffer_pool_reads'");
            $stmt->execute();
            $reads = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;
            
            if ($readRequests > 0) {
                $hitRatio = round((1 - ($reads / $readRequests)) * 100, 2);
                $status = $hitRatio >= 95 ? 'healthy' : ($hitRatio >= 90 ? 'warning' : 'critical');
                
                $healthChecks[] = [
                    'check_type' => 'performance',
                    'check_name' => 'Buffer Pool Hit Ratio',
                    'status' => $status,
                    'value' => $hitRatio,
                    'threshold' => 95,
                    'message' => "Hit ratio: {$hitRatio}%"
                ];
            }
            
            // Check connection count
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Threads_connected'");
            $stmt->execute();
            $connected = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;
            
            $stmt = $this->db->prepare("SHOW VARIABLES LIKE 'max_connections'");
            $stmt->execute();
            $maxConnections = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;
            
            $connectionPercent = $maxConnections > 0 ? round(($connected / $maxConnections) * 100, 2) : 0;
            $status = $connectionPercent < 80 ? 'healthy' : ($connectionPercent < 90 ? 'warning' : 'critical');
            
            $healthChecks[] = [
                'check_type' => 'connections',
                'check_name' => 'Connection Usage',
                'status' => $status,
                'value' => $connectionPercent,
                'threshold' => 80,
                'message' => "Using {$connected}/{$maxConnections} connections ({$connectionPercent}%)"
            ];
            
        } catch (Exception $e) {
            $healthChecks[] = [
                'check_type' => 'error',
                'check_name' => 'Health Check Error',
                'status' => 'critical',
                'message' => $e->getMessage()
            ];
        }
        
        return $healthChecks;
    }
    
    private function logQueryPerformance(string $query, float $executionTime, int $rowsReturned): void {
        try {
            $queryHash = hash('sha256', $query);
            
            $stmt = $this->db->prepare("
                INSERT INTO query_performance_metrics 
                (query_hash, query_text, execution_time_ms, rows_sent, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$queryHash, $query, $executionTime, $rowsReturned]);
            
            // Log slow queries
            if ($executionTime > 1000) { // 1 second
                $stmt = $this->db->prepare("
                    INSERT INTO slow_query_logs 
                    (query_text, execution_time_ms, rows_sent, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$query, $executionTime, $rowsReturned]);
            }
            
        } catch (Exception $e) {
            error_log("Error logging query performance: " . $e->getMessage());
        }
    }
    
    private function getSlaveConfigs(): array {
        // This would typically come from configuration
        return [
            // Example slave configuration
            // [
            //     'host' => 'slave1.example.com',
            //     'port' => 3306,
            //     'database' => 'personal',
            //     'username' => 'replication_user',
            //     'password' => 'replication_password',
            //     'weight' => 1
            // ]
        ];
    }
    
    private function getPoolConfig(): array {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'personal',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'max_connections' => 20,
            'min_connections' => 5,
            'connection_timeout' => 30,
            'idle_timeout' => 300
        ];
    }
}
