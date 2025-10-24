<?php
namespace Core\Monitoring;

use PDO;
use Exception;

class APM {
    private $db;
    private $config;
    
    public function __construct(PDO $db, array $config = []) {
        $this->db = $db;
        $this->config = array_merge([
            'enabled' => true,
            'sample_rate' => 1.0, // 100% sampling
            'slow_query_threshold' => 1000, // 1 second
            'memory_threshold' => 128 * 1024 * 1024, // 128MB
            'error_tracking' => true,
            'performance_tracking' => true
        ], $config);
    }
    
    /**
     * Start performance monitoring
     */
    public function startTransaction(string $transactionName, array $context = []): string {
        if (!$this->config['enabled']) {
            return '';
        }
        
        $transactionId = uniqid('txn_');
        
        $this->recordTransaction([
            'transaction_id' => $transactionId,
            'transaction_name' => $transactionName,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'context' => $context,
            'status' => 'started'
        ]);
        
        return $transactionId;
    }
    
    /**
     * End performance monitoring
     */
    public function endTransaction(string $transactionId, array $context = []): array {
        if (!$this->config['enabled'] || !$transactionId) {
            return [];
        }
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        // Get transaction start data
        $transaction = $this->getTransaction($transactionId);
        
        if (!$transaction) {
            return [];
        }
        
        $duration = ($endTime - $transaction['start_time']) * 1000; // Convert to milliseconds
        $memoryDelta = $endMemory - $transaction['start_memory'];
        
        $transactionData = [
            'transaction_id' => $transactionId,
            'transaction_name' => $transaction['transaction_name'],
            'duration' => $duration,
            'memory_delta' => $memoryDelta,
            'end_time' => $endTime,
            'end_memory' => $endMemory,
            'context' => array_merge($transaction['context'], $context),
            'status' => 'completed'
        ];
        
        $this->updateTransaction($transactionData);
        
        // Check for performance issues
        $this->checkPerformanceIssues($transactionData);
        
        return $transactionData;
    }
    
    /**
     * Record database query performance
     */
    public function recordQuery(string $query, float $duration, array $context = []): void {
        if (!$this->config['enabled'] || !$this->config['performance_tracking']) {
            return;
        }
        
        $queryData = [
            'query' => $this->sanitizeQuery($query),
            'duration' => $duration,
            'context' => $context,
            'is_slow' => $duration > $this->config['slow_query_threshold'],
            'timestamp' => microtime(true)
        ];
        
        $this->saveQuery($queryData);
        
        // Alert on slow queries
        if ($queryData['is_slow']) {
            $this->alertSlowQuery($queryData);
        }
    }
    
    /**
     * Record error
     */
    public function recordError(\Throwable $error, array $context = []): void {
        if (!$this->config['enabled'] || !$this->config['error_tracking']) {
            return;
        }
        
        $errorData = [
            'error_type' => get_class($error),
            'error_message' => $error->getMessage(),
            'error_code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
            'timestamp' => microtime(true),
            'severity' => $this->determineSeverity($error)
        ];
        
        $this->saveError($errorData);
        
        // Alert on critical errors
        if ($errorData['severity'] === 'critical') {
            $this->alertCriticalError($errorData);
        }
    }
    
    /**
     * Record custom event
     */
    public function recordEvent(string $eventName, array $data = [], array $context = []): void {
        if (!$this->config['enabled']) {
            return;
        }
        
        $eventData = [
            'event_name' => $eventName,
            'data' => $data,
            'context' => $context,
            'timestamp' => microtime(true)
        ];
        
        $this->saveEvent($eventData);
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(int $timeRange = 3600): array {
        try {
            $since = date('Y-m-d H:i:s', time() - $timeRange);
            
            // Get transaction metrics
            $stmt = $this->db->prepare("
                SELECT 
                    transaction_name,
                    COUNT(*) as count,
                    AVG(duration) as avg_duration,
                    MIN(duration) as min_duration,
                    MAX(duration) as max_duration,
                    PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY duration) as p95_duration
                FROM apm_transactions 
                WHERE created_at >= ? AND status = 'completed'
                GROUP BY transaction_name
                ORDER BY avg_duration DESC
            ");
            $stmt->execute([$since]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get query metrics
            $stmt = $this->db->prepare("
                SELECT 
                    query,
                    COUNT(*) as count,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration,
                    SUM(CASE WHEN is_slow = 1 THEN 1 ELSE 0 END) as slow_count
                FROM apm_queries 
                WHERE created_at >= ?
                GROUP BY query
                ORDER BY avg_duration DESC
                LIMIT 20
            ");
            $stmt->execute([$since]);
            $queries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get error metrics
            $stmt = $this->db->prepare("
                SELECT 
                    error_type,
                    severity,
                    COUNT(*) as count,
                    MAX(created_at) as last_occurrence
                FROM apm_errors 
                WHERE created_at >= ?
                GROUP BY error_type, severity
                ORDER BY count DESC
            ");
            $stmt->execute([$since]);
            $errors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'time_range' => $timeRange,
                'transactions' => $transactions,
                'queries' => $queries,
                'errors' => $errors,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get system health status
     */
    public function getSystemHealth(): array {
        try {
            $health = [
                'overall_status' => 'healthy',
                'checks' => [],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Check database connectivity
            $dbCheck = $this->checkDatabaseHealth();
            $health['checks']['database'] = $dbCheck;
            
            // Check memory usage
            $memoryCheck = $this->checkMemoryHealth();
            $health['checks']['memory'] = $memoryCheck;
            
            // Check error rate
            $errorCheck = $this->checkErrorRate();
            $health['checks']['error_rate'] = $errorCheck;
            
            // Check response time
            $responseCheck = $this->checkResponseTime();
            $health['checks']['response_time'] = $responseCheck;
            
            // Determine overall status
            $criticalIssues = array_filter($health['checks'], function($check) {
                return $check['status'] === 'critical';
            });
            
            $warningIssues = array_filter($health['checks'], function($check) {
                return $check['status'] === 'warning';
            });
            
            if (!empty($criticalIssues)) {
                $health['overall_status'] = 'critical';
            } elseif (!empty($warningIssues)) {
                $health['overall_status'] = 'warning';
            }
            
            return $health;
            
        } catch (Exception $e) {
            return [
                'overall_status' => 'critical',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Generate performance report
     */
    public function generatePerformanceReport(int $days = 7): array {
        try {
            $since = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));
            
            // Get daily transaction counts
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as transaction_count,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration
                FROM apm_transactions 
                WHERE created_at >= ? AND status = 'completed'
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute([$since]);
            $dailyTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get top slow queries
            $stmt = $this->db->prepare("
                SELECT 
                    query,
                    COUNT(*) as count,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration
                FROM apm_queries 
                WHERE created_at >= ? AND is_slow = 1
                GROUP BY query
                ORDER BY avg_duration DESC
                LIMIT 10
            ");
            $stmt->execute([$since]);
            $slowQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get error trends
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    error_type,
                    severity,
                    COUNT(*) as count
                FROM apm_errors 
                WHERE created_at >= ?
                GROUP BY DATE(created_at), error_type, severity
                ORDER BY date DESC, count DESC
            ");
            $stmt->execute([$since]);
            $errorTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'report_period' => "Last {$days} days",
                'daily_transactions' => $dailyTransactions,
                'slow_queries' => $slowQueries,
                'error_trends' => $errorTrends,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Private methods
    private function recordTransaction(array $data): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO apm_transactions (
                    transaction_id, transaction_name, start_time, start_memory, 
                    context, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['transaction_id'],
                $data['transaction_name'],
                $data['start_time'],
                $data['start_memory'],
                json_encode($data['context']),
                $data['status']
            ]);
            
        } catch (Exception $e) {
            error_log("Error recording transaction: " . $e->getMessage());
        }
    }
    
    private function updateTransaction(array $data): void {
        try {
            $stmt = $this->db->prepare("
                UPDATE apm_transactions SET 
                    duration = ?, 
                    memory_delta = ?, 
                    end_time = ?, 
                    end_memory = ?, 
                    context = ?, 
                    status = ?
                WHERE transaction_id = ?
            ");
            
            $stmt->execute([
                $data['duration'],
                $data['memory_delta'],
                $data['end_time'],
                $data['end_memory'],
                json_encode($data['context']),
                $data['status'],
                $data['transaction_id']
            ]);
            
        } catch (Exception $e) {
            error_log("Error updating transaction: " . $e->getMessage());
        }
    }
    
    private function getTransaction(string $transactionId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM apm_transactions WHERE transaction_id = ?
            ");
            $stmt->execute([$transactionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['context'] = json_decode($result['context'], true) ?? [];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error getting transaction: " . $e->getMessage());
            return null;
        }
    }
    
    private function saveQuery(array $data): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO apm_queries (
                    query, duration, context, is_slow, timestamp, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['query'],
                $data['duration'],
                json_encode($data['context']),
                $data['is_slow'] ? 1 : 0,
                $data['timestamp']
            ]);
            
        } catch (Exception $e) {
            error_log("Error saving query: " . $e->getMessage());
        }
    }
    
    private function saveError(array $data): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO apm_errors (
                    error_type, error_message, error_code, file, line, 
                    trace, context, severity, timestamp, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['error_type'],
                $data['error_message'],
                $data['error_code'],
                $data['file'],
                $data['line'],
                $data['trace'],
                json_encode($data['context']),
                $data['severity'],
                $data['timestamp']
            ]);
            
        } catch (Exception $e) {
            error_log("Error saving error: " . $e->getMessage());
        }
    }
    
    private function saveEvent(array $data): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO apm_events (
                    event_name, data, context, timestamp, created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['event_name'],
                json_encode($data['data']),
                json_encode($data['context']),
                $data['timestamp']
            ]);
            
        } catch (Exception $e) {
            error_log("Error saving event: " . $e->getMessage());
        }
    }
    
    private function sanitizeQuery(string $query): string {
        // Remove sensitive data from queries
        $query = preg_replace('/\b\d{4}-\d{2}-\d{2}\b/', 'YYYY-MM-DD', $query);
        $query = preg_replace('/\b\d{2}:\d{2}:\d{2}\b/', 'HH:MM:SS', $query);
        $query = preg_replace('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', 'email@domain.com', $query);
        
        return $query;
    }
    
    private function determineSeverity(\Throwable $error): string {
        $criticalErrors = [
            'PDOException',
            'DatabaseException',
            'FatalError',
            'ParseError'
        ];
        
        $errorClass = get_class($error);
        
        if (in_array($errorClass, $criticalErrors)) {
            return 'critical';
        }
        
        if ($error->getCode() >= 500) {
            return 'high';
        }
        
        if ($error->getCode() >= 400) {
            return 'medium';
        }
        
        return 'low';
    }
    
    private function checkPerformanceIssues(array $transactionData): void {
        $issues = [];
        
        // Check duration
        if ($transactionData['duration'] > 5000) { // 5 seconds
            $issues[] = [
                'type' => 'slow_transaction',
                'message' => "Transaction '{$transactionData['transaction_name']}' took {$transactionData['duration']}ms",
                'severity' => 'high'
            ];
        }
        
        // Check memory usage
        if ($transactionData['memory_delta'] > $this->config['memory_threshold']) {
            $issues[] = [
                'type' => 'high_memory_usage',
                'message' => "Transaction '{$transactionData['transaction_name']}' used " . 
                           round($transactionData['memory_delta'] / 1024 / 1024, 2) . "MB",
                'severity' => 'medium'
            ];
        }
        
        // Alert on issues
        foreach ($issues as $issue) {
            $this->alertPerformanceIssue($issue, $transactionData);
        }
    }
    
    private function alertSlowQuery(array $queryData): void {
        $this->recordEvent('slow_query_alert', [
            'query' => $queryData['query'],
            'duration' => $queryData['duration'],
            'threshold' => $this->config['slow_query_threshold']
        ]);
    }
    
    private function alertCriticalError(array $errorData): void {
        $this->recordEvent('critical_error_alert', [
            'error_type' => $errorData['error_type'],
            'error_message' => $errorData['error_message'],
            'file' => $errorData['file'],
            'line' => $errorData['line']
        ]);
    }
    
    private function alertPerformanceIssue(array $issue, array $transactionData): void {
        $this->recordEvent('performance_issue_alert', [
            'issue' => $issue,
            'transaction' => $transactionData
        ]);
    }
    
    private function checkDatabaseHealth(): array {
        try {
            $start = microtime(true);
            $this->db->query("SELECT 1");
            $duration = (microtime(true) - $start) * 1000;
            
            if ($duration > 1000) {
                return [
                    'status' => 'critical',
                    'message' => "Database response time: {$duration}ms",
                    'value' => $duration
                ];
            } elseif ($duration > 500) {
                return [
                    'status' => 'warning',
                    'message' => "Database response time: {$duration}ms",
                    'value' => $duration
                ];
            }
            
            return [
                'status' => 'healthy',
                'message' => "Database response time: {$duration}ms",
                'value' => $duration
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'message' => "Database connection failed: " . $e->getMessage(),
                'value' => null
            ];
        }
    }
    
    private function checkMemoryHealth(): array {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        
        $usagePercent = ($memoryUsage / $memoryLimitBytes) * 100;
        
        if ($usagePercent > 90) {
            return [
                'status' => 'critical',
                'message' => "Memory usage: " . round($usagePercent, 2) . "%",
                'value' => $usagePercent
            ];
        } elseif ($usagePercent > 80) {
            return [
                'status' => 'warning',
                'message' => "Memory usage: " . round($usagePercent, 2) . "%",
                'value' => $usagePercent
            ];
        }
        
        return [
            'status' => 'healthy',
            'message' => "Memory usage: " . round($usagePercent, 2) . "%",
            'value' => $usagePercent
        ];
    }
    
    private function checkErrorRate(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as error_count
                FROM apm_errors 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $errorCount = $result['error_count'];
            
            if ($errorCount > 100) {
                return [
                    'status' => 'critical',
                    'message' => "High error rate: {$errorCount} errors in last hour",
                    'value' => $errorCount
                ];
            } elseif ($errorCount > 50) {
                return [
                    'status' => 'warning',
                    'message' => "Elevated error rate: {$errorCount} errors in last hour",
                    'value' => $errorCount
                ];
            }
            
            return [
                'status' => 'healthy',
                'message' => "Error rate: {$errorCount} errors in last hour",
                'value' => $errorCount
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'message' => "Error checking error rate: " . $e->getMessage(),
                'value' => null
            ];
        }
    }
    
    private function checkResponseTime(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(duration) as avg_response_time
                FROM apm_transactions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND status = 'completed'
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $avgResponseTime = $result['avg_response_time'] ?? 0;
            
            if ($avgResponseTime > 2000) {
                return [
                    'status' => 'critical',
                    'message' => "High response time: {$avgResponseTime}ms",
                    'value' => $avgResponseTime
                ];
            } elseif ($avgResponseTime > 1000) {
                return [
                    'status' => 'warning',
                    'message' => "Elevated response time: {$avgResponseTime}ms",
                    'value' => $avgResponseTime
                ];
            }
            
            return [
                'status' => 'healthy',
                'message' => "Response time: {$avgResponseTime}ms",
                'value' => $avgResponseTime
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'message' => "Error checking response time: " . $e->getMessage(),
                'value' => null
            ];
        }
    }
    
    private function parseMemoryLimit(string $memoryLimit): int {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;
        
        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }
        
        return $memoryLimit;
    }
}
