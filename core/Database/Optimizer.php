<?php
namespace Core\Database;

use PDO;
use Exception;
use Core\Cache;

class Optimizer {
    private $db;
    private $cache;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->cache = Cache::getInstance($db);
    }
    
    /**
     * Analyze and optimize database performance
     */
    public function analyzePerformance(): array {
        $analysis = [
            'slow_queries' => $this->getSlowQueries(),
            'missing_indexes' => $this->findMissingIndexes(),
            'unused_indexes' => $this->findUnusedIndexes(),
            'table_sizes' => $this->getTableSizes(),
            'fragmentation' => $this->checkFragmentation(),
            'connection_stats' => $this->getConnectionStats(),
            'buffer_pool_stats' => $this->getBufferPoolStats(),
            'recommendations' => []
        ];
        
        // Generate recommendations
        $analysis['recommendations'] = $this->generateRecommendations($analysis);
        
        return $analysis;
    }
    
    /**
     * Get slow queries from performance schema
     */
    private function getSlowQueries(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DIGEST_TEXT as query,
                    COUNT_STAR as executions,
                    AVG_TIMER_WAIT/1000000000 as avg_time_seconds,
                    MAX_TIMER_WAIT/1000000000 as max_time_seconds,
                    SUM_ROWS_EXAMINED as total_rows_examined,
                    SUM_ROWS_SENT as total_rows_sent
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE AVG_TIMER_WAIT > 1000000000 
                ORDER BY AVG_TIMER_WAIT DESC 
                LIMIT 20
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting slow queries: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find missing indexes based on query patterns
     */
    private function findMissingIndexes(): array {
        $missingIndexes = [];
        
        try {
            // Check for common missing index patterns
            $queries = [
                "SELECT * FROM notes WHERE user_id = ? AND created_at > ?" => "idx_notes_user_created",
                "SELECT * FROM tasks WHERE user_id = ? AND status = ?" => "idx_tasks_user_status",
                "SELECT * FROM notes WHERE user_id = ? AND title LIKE ?" => "idx_notes_user_title",
                "SELECT * FROM tasks WHERE user_id = ? AND due_date < ?" => "idx_tasks_user_due",
                "SELECT * FROM note_tags WHERE note_id = ?" => "idx_note_tags_note",
                "SELECT * FROM note_tags WHERE tag_id = ?" => "idx_note_tags_tag"
            ];
            
            foreach ($queries as $query => $suggestedIndex) {
                if (!$this->indexExists($suggestedIndex)) {
                    $missingIndexes[] = [
                        'query' => $query,
                        'suggested_index' => $suggestedIndex,
                        'impact' => 'high'
                    ];
                }
            }
            
        } catch (Exception $e) {
            error_log("Error finding missing indexes: " . $e->getMessage());
        }
        
        return $missingIndexes;
    }
    
    /**
     * Find unused indexes
     */
    private function findUnusedIndexes(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.TABLE_NAME,
                    t.INDEX_NAME,
                    t.CARDINALITY,
                    s.INDEX_NAME as usage_count
                FROM information_schema.STATISTICS t
                LEFT JOIN (
                    SELECT 
                        OBJECT_SCHEMA,
                        OBJECT_NAME,
                        INDEX_NAME,
                        COUNT(*) as usage_count
                    FROM performance_schema.table_io_waits_summary_by_index_usage
                    WHERE OBJECT_SCHEMA = DATABASE()
                    GROUP BY OBJECT_SCHEMA, OBJECT_NAME, INDEX_NAME
                ) s ON t.TABLE_NAME = s.OBJECT_NAME AND t.INDEX_NAME = s.INDEX_NAME
                WHERE t.TABLE_SCHEMA = DATABASE()
                AND t.INDEX_NAME != 'PRIMARY'
                AND (s.usage_count IS NULL OR s.usage_count = 0)
                ORDER BY t.CARDINALITY DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding unused indexes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get table sizes and row counts
     */
    private function getTableSizes(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    TABLE_NAME,
                    TABLE_ROWS,
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size_MB',
                    ROUND((DATA_LENGTH / 1024 / 1024), 2) AS 'Data_MB',
                    ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS 'Index_MB'
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting table sizes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check table fragmentation
     */
    private function checkFragmentation(): array {
        $fragmentation = [];
        
        try {
            $tables = $this->getTableNames();
            
            foreach ($tables as $table) {
                $stmt = $this->db->prepare("SHOW TABLE STATUS LIKE ?");
                $stmt->execute([$table]);
                $status = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($status) {
                    $dataLength = $status['Data_length'];
                    $dataFree = $status['Data_free'];
                    
                    if ($dataLength > 0) {
                        $fragmentationPercent = ($dataFree / $dataLength) * 100;
                        
                        if ($fragmentationPercent > 10) {
                            $fragmentation[] = [
                                'table' => $table,
                                'fragmentation_percent' => round($fragmentationPercent, 2),
                                'data_free_mb' => round($dataFree / 1024 / 1024, 2),
                                'recommendation' => 'OPTIMIZE TABLE'
                            ];
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error checking fragmentation: " . $e->getMessage());
        }
        
        return $fragmentation;
    }
    
    /**
     * Get connection statistics
     */
    private function getConnectionStats(): array {
        try {
            $stats = [];
            
            // Current connections
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Threads_connected'");
            $stmt->execute();
            $stats['current_connections'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
            
            // Max connections
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Max_used_connections'");
            $stmt->execute();
            $stats['max_used_connections'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
            
            // Connection errors
            $stmt = $this->db->prepare("SHOW STATUS LIKE 'Connection_errors%'");
            $stmt->execute();
            $connectionErrors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($connectionErrors as $error) {
                $stats['connection_errors'][$error['Variable_name']] = $error['Value'];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting connection stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get buffer pool statistics
     */
    private function getBufferPoolStats(): array {
        try {
            $stats = [];
            
            $bufferPoolVars = [
                'Innodb_buffer_pool_size',
                'Innodb_buffer_pool_pages_data',
                'Innodb_buffer_pool_pages_free',
                'Innodb_buffer_pool_pages_total',
                'Innodb_buffer_pool_read_requests',
                'Innodb_buffer_pool_reads',
                'Innodb_buffer_pool_wait_free'
            ];
            
            foreach ($bufferPoolVars as $var) {
                $stmt = $this->db->prepare("SHOW STATUS LIKE ?");
                $stmt->execute([$var]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $stats[$var] = $result['Value'];
                }
            }
            
            // Calculate hit ratio
            if (isset($stats['Innodb_buffer_pool_read_requests']) && 
                isset($stats['Innodb_buffer_pool_reads'])) {
                $requests = $stats['Innodb_buffer_pool_read_requests'];
                $reads = $stats['Innodb_buffer_pool_reads'];
                
                if ($requests > 0) {
                    $stats['buffer_pool_hit_ratio'] = round((1 - ($reads / $requests)) * 100, 2);
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting buffer pool stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations(array $analysis): array {
        $recommendations = [];
        
        // Slow query recommendations
        if (!empty($analysis['slow_queries'])) {
            $recommendations[] = [
                'type' => 'slow_queries',
                'priority' => 'high',
                'title' => 'Optimize Slow Queries',
                'description' => 'Found ' . count($analysis['slow_queries']) . ' slow queries that need optimization',
                'action' => 'Review and optimize slow queries, add appropriate indexes'
            ];
        }
        
        // Missing index recommendations
        if (!empty($analysis['missing_indexes'])) {
            $recommendations[] = [
                'type' => 'missing_indexes',
                'priority' => 'high',
                'title' => 'Add Missing Indexes',
                'description' => 'Found ' . count($analysis['missing_indexes']) . ' missing indexes',
                'action' => 'Create recommended indexes to improve query performance'
            ];
        }
        
        // Unused index recommendations
        if (!empty($analysis['unused_indexes'])) {
            $recommendations[] = [
                'type' => 'unused_indexes',
                'priority' => 'medium',
                'title' => 'Remove Unused Indexes',
                'description' => 'Found ' . count($analysis['unused_indexes']) . ' unused indexes',
                'action' => 'Consider removing unused indexes to reduce storage overhead'
            ];
        }
        
        // Fragmentation recommendations
        if (!empty($analysis['fragmentation'])) {
            $recommendations[] = [
                'type' => 'fragmentation',
                'priority' => 'medium',
                'title' => 'Optimize Fragmented Tables',
                'description' => 'Found ' . count($analysis['fragmentation']) . ' fragmented tables',
                'action' => 'Run OPTIMIZE TABLE on fragmented tables'
            ];
        }
        
        // Buffer pool recommendations
        if (isset($analysis['buffer_pool_stats']['buffer_pool_hit_ratio'])) {
            $hitRatio = $analysis['buffer_pool_stats']['buffer_pool_hit_ratio'];
            if ($hitRatio < 95) {
                $recommendations[] = [
                    'type' => 'buffer_pool',
                    'priority' => 'high',
                    'title' => 'Improve Buffer Pool Hit Ratio',
                    'description' => "Current hit ratio: {$hitRatio}% (should be >95%)",
                    'action' => 'Consider increasing innodb_buffer_pool_size'
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Optimize specific table
     */
    public function optimizeTable(string $tableName): array {
        try {
            $startTime = microtime(true);
            
            $stmt = $this->db->prepare("OPTIMIZE TABLE ?");
            $stmt->execute([$tableName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            return [
                'success' => true,
                'table' => $tableName,
                'duration_ms' => $duration,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            error_log("Error optimizing table {$tableName}: " . $e->getMessage());
            return [
                'success' => false,
                'table' => $tableName,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create recommended index
     */
    public function createIndex(string $tableName, string $indexName, array $columns, string $type = 'INDEX'): array {
        try {
            $columnList = implode(', ', $columns);
            $sql = "CREATE {$type} {$indexName} ON {$tableName} ({$columnList})";
            
            $this->db->exec($sql);
            
            return [
                'success' => true,
                'index' => $indexName,
                'table' => $tableName,
                'sql' => $sql
            ];
            
        } catch (Exception $e) {
            error_log("Error creating index {$indexName}: " . $e->getMessage());
            return [
                'success' => false,
                'index' => $indexName,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Drop unused index
     */
    public function dropIndex(string $tableName, string $indexName): array {
        try {
            $sql = "DROP INDEX {$indexName} ON {$tableName}";
            $this->db->exec($sql);
            
            return [
                'success' => true,
                'index' => $indexName,
                'table' => $tableName,
                'sql' => $sql
            ];
            
        } catch (Exception $e) {
            error_log("Error dropping index {$indexName}: " . $e->getMessage());
            return [
                'success' => false,
                'index' => $indexName,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Analyze table statistics
     */
    public function analyzeTable(string $tableName): array {
        try {
            $stmt = $this->db->prepare("ANALYZE TABLE ?");
            $stmt->execute([$tableName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'table' => $tableName,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            error_log("Error analyzing table {$tableName}: " . $e->getMessage());
            return [
                'success' => false,
                'table' => $tableName,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get query execution plan
     */
    public function explainQuery(string $query, array $params = []): array {
        try {
            $explainQuery = "EXPLAIN " . $query;
            $stmt = $this->db->prepare($explainQuery);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error explaining query: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if index exists
     */
    private function indexExists(string $indexName): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND INDEX_NAME = ?
            ");
            $stmt->execute([$indexName]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all table names
     */
    private function getTableNames(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT TABLE_NAME 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get database configuration recommendations
     */
    public function getConfigurationRecommendations(): array {
        $recommendations = [];
        
        try {
            // Check key configuration variables
            $configVars = [
                'innodb_buffer_pool_size',
                'max_connections',
                'query_cache_size',
                'tmp_table_size',
                'max_heap_table_size',
                'innodb_log_file_size',
                'innodb_flush_log_at_trx_commit'
            ];
            
            foreach ($configVars as $var) {
                $stmt = $this->db->prepare("SHOW VARIABLES LIKE ?");
                $stmt->execute([$var]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $value = $result['Value'];
                    
                    switch ($var) {
                        case 'innodb_buffer_pool_size':
                            $valueMB = $value / 1024 / 1024;
                            if ($valueMB < 1024) {
                                $recommendations[] = [
                                    'variable' => $var,
                                    'current_value' => $valueMB . 'MB',
                                    'recommendation' => 'Consider increasing to at least 1GB for better performance',
                                    'priority' => 'high'
                                ];
                            }
                            break;
                            
                        case 'max_connections':
                            if ($value < 200) {
                                $recommendations[] = [
                                    'variable' => $var,
                                    'current_value' => $value,
                                    'recommendation' => 'Consider increasing to 200+ for better concurrency',
                                    'priority' => 'medium'
                                ];
                            }
                            break;
                            
                        case 'query_cache_size':
                            if ($value == 0) {
                                $recommendations[] = [
                                    'variable' => $var,
                                    'current_value' => 'Disabled',
                                    'recommendation' => 'Consider enabling query cache for read-heavy workloads',
                                    'priority' => 'low'
                                ];
                            }
                            break;
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error getting configuration recommendations: " . $e->getMessage());
        }
        
        return $recommendations;
    }
}
