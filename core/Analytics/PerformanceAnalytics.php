<?php
namespace Core\Analytics;

use PDO;
use Exception;

class PerformanceAnalytics {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Track API performance
     */
    public function trackAPIPerformance($endpoint, $method, $responseTime, $statusCode, $userId = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO performance_metrics 
                (endpoint, method, response_time_ms, status_code, user_id, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $endpoint,
                $method,
                $responseTime,
                $statusCode,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking API performance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track database query performance
     */
    public function trackDatabasePerformance($query, $executionTime, $rowsAffected = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO database_performance_metrics 
                (query_hash, query_text, execution_time_ms, rows_affected, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $queryHash = hash('sha256', $query);
            $queryText = strlen($query) > 1000 ? substr($query, 0, 1000) . '...' : $query;
            
            $stmt->execute([
                $queryHash,
                $queryText,
                $executionTime,
                $rowsAffected
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking database performance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track page load performance
     */
    public function trackPagePerformance($page, $loadTime, $userId = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO page_performance_metrics 
                (page, load_time_ms, user_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $page,
                $loadTime,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking page performance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get API performance summary
     */
    public function getAPIPerformanceSummary($days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    endpoint,
                    method,
                    COUNT(*) as request_count,
                    AVG(response_time_ms) as avg_response_time,
                    MIN(response_time_ms) as min_response_time,
                    MAX(response_time_ms) as max_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
                FROM performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY endpoint, method
                ORDER BY request_count DESC
            ");
            $stmt->execute([$days]);
            $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate success rates
            foreach ($summary as &$item) {
                $item['success_rate'] = $item['request_count'] > 0 
                    ? round(($item['success_count'] / $item['request_count']) * 100, 2) 
                    : 0;
                $item['error_rate'] = $item['request_count'] > 0 
                    ? round(($item['error_count'] / $item['request_count']) * 100, 2) 
                    : 0;
            }
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting API performance summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get database performance summary
     */
    public function getDatabasePerformanceSummary($days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    query_hash,
                    query_text,
                    COUNT(*) as execution_count,
                    AVG(execution_time_ms) as avg_execution_time,
                    MIN(execution_time_ms) as min_execution_time,
                    MAX(execution_time_ms) as max_execution_time,
                    AVG(rows_affected) as avg_rows_affected
                FROM database_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY query_hash
                ORDER BY avg_execution_time DESC
                LIMIT 20
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting database performance summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get page performance summary
     */
    public function getPagePerformanceSummary($days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    page,
                    COUNT(*) as load_count,
                    AVG(load_time_ms) as avg_load_time,
                    MIN(load_time_ms) as min_load_time,
                    MAX(load_time_ms) as max_load_time
                FROM page_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY page
                ORDER BY avg_load_time DESC
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting page performance summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system performance metrics
     */
    public function getSystemPerformanceMetrics($days = 7) {
        try {
            // Get overall API metrics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_requests,
                    AVG(response_time_ms) as avg_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as successful_requests,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed_requests
                FROM performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $apiMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get overall database metrics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_queries,
                    AVG(execution_time_ms) as avg_execution_time,
                    MAX(execution_time_ms) as slowest_query_time
                FROM database_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $dbMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get overall page metrics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_page_loads,
                    AVG(load_time_ms) as avg_load_time,
                    MAX(load_time_ms) as slowest_page_load
                FROM page_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $pageMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate success rates
            $apiSuccessRate = $apiMetrics['total_requests'] > 0 
                ? round(($apiMetrics['successful_requests'] / $apiMetrics['total_requests']) * 100, 2) 
                : 0;
            
            return [
                'api' => array_merge($apiMetrics, ['success_rate' => $apiSuccessRate]),
                'database' => $dbMetrics,
                'pages' => $pageMetrics,
                'period_days' => $days,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Error getting system performance metrics: " . $e->getMessage());
            return [
                'api' => ['total_requests' => 0, 'avg_response_time' => 0, 'success_rate' => 0],
                'database' => ['total_queries' => 0, 'avg_execution_time' => 0],
                'pages' => ['total_page_loads' => 0, 'avg_load_time' => 0],
                'period_days' => $days,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Get performance trends
     */
    public function getPerformanceTrends($days = 30) {
        try {
            // API performance trends
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as request_count,
                    AVG(response_time_ms) as avg_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count
                FROM performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            $apiTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Database performance trends
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as query_count,
                    AVG(execution_time_ms) as avg_execution_time
                FROM database_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            $dbTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Page performance trends
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as load_count,
                    AVG(load_time_ms) as avg_load_time
                FROM page_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            $pageTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate success rates for API trends
            foreach ($apiTrends as &$trend) {
                $trend['success_rate'] = $trend['request_count'] > 0 
                    ? round(($trend['success_count'] / $trend['request_count']) * 100, 2) 
                    : 0;
            }
            
            return [
                'api_trends' => $apiTrends,
                'database_trends' => $dbTrends,
                'page_trends' => $pageTrends,
                'period_days' => $days
            ];
        } catch (Exception $e) {
            error_log("Error getting performance trends: " . $e->getMessage());
            return [
                'api_trends' => [],
                'database_trends' => [],
                'page_trends' => [],
                'period_days' => $days
            ];
        }
    }
    
    /**
     * Get slow queries
     */
    public function getSlowQueries($threshold = 1000, $days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    query_hash,
                    query_text,
                    execution_time_ms,
                    rows_affected,
                    created_at
                FROM database_performance_metrics 
                WHERE execution_time_ms > ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY execution_time_ms DESC
                LIMIT 50
            ");
            $stmt->execute([$threshold, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting slow queries: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get error analysis
     */
    public function getErrorAnalysis($days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    status_code,
                    endpoint,
                    method,
                    COUNT(*) as error_count,
                    AVG(response_time_ms) as avg_response_time
                FROM performance_metrics 
                WHERE status_code >= 400 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY status_code, endpoint, method
                ORDER BY error_count DESC
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting error analysis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user performance metrics
     */
    public function getUserPerformanceMetrics($userId, $days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_requests,
                    AVG(response_time_ms) as avg_response_time,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as successful_requests,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed_requests
                FROM performance_metrics 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$userId, $days]);
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate success rate
            $metrics['success_rate'] = $metrics['total_requests'] > 0 
                ? round(($metrics['successful_requests'] / $metrics['total_requests']) * 100, 2) 
                : 0;
            
            return $metrics;
        } catch (Exception $e) {
            error_log("Error getting user performance metrics: " . $e->getMessage());
            return [
                'total_requests' => 0,
                'avg_response_time' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'success_rate' => 0
            ];
        }
    }
    
    /**
     * Clean up old performance data
     */
    public function cleanupOldData($days = 90) {
        try {
            // Clean up performance metrics
            $stmt = $this->db->prepare("
                DELETE FROM performance_metrics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $performanceDeleted = $stmt->rowCount();
            
            // Clean up database performance metrics
            $stmt = $this->db->prepare("
                DELETE FROM database_performance_metrics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $dbDeleted = $stmt->rowCount();
            
            // Clean up page performance metrics
            $stmt = $this->db->prepare("
                DELETE FROM page_performance_metrics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $pageDeleted = $stmt->rowCount();
            
            return [
                'performance_metrics_deleted' => $performanceDeleted,
                'database_metrics_deleted' => $dbDeleted,
                'page_metrics_deleted' => $pageDeleted,
                'total_deleted' => $performanceDeleted + $dbDeleted + $pageDeleted
            ];
        } catch (Exception $e) {
            error_log("Error cleaning up old performance data: " . $e->getMessage());
            return [
                'performance_metrics_deleted' => 0,
                'database_metrics_deleted' => 0,
                'page_metrics_deleted' => 0,
                'total_deleted' => 0
            ];
        }
    }
}
