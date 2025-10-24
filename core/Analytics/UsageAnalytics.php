<?php
namespace Core\Analytics;

use PDO;
use Exception;

class UsageAnalytics {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Track feature usage
     */
    public function trackFeatureUsage($userId, $feature, $action, $metadata = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO feature_usage_analytics 
                (user_id, feature, action, metadata, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $feature,
                $action,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking feature usage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track user session
     */
    public function trackUserSession($userId, $sessionData = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_session_analytics 
                (user_id, session_data, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                json_encode($sessionData),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking user session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track content interaction
     */
    public function trackContentInteraction($userId, $contentType, $contentId, $action, $metadata = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO content_interaction_analytics 
                (user_id, content_type, content_id, action, metadata, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $contentType,
                $contentId,
                $action,
                json_encode($metadata)
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking content interaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get feature usage statistics
     */
    public function getFeatureUsageStats($days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    feature,
                    action,
                    COUNT(*) as usage_count,
                    COUNT(DISTINCT user_id) as unique_users,
                    AVG(CASE WHEN metadata IS NOT NULL THEN JSON_LENGTH(metadata) ELSE 0 END) as avg_metadata_size
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY feature, action
                ORDER BY usage_count DESC
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting feature usage stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user engagement metrics
     */
    public function getUserEngagementMetrics($days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(DISTINCT user_id) as active_users,
                    COUNT(*) as total_actions,
                    AVG(actions_per_user) as avg_actions_per_user
                FROM (
                    SELECT 
                        user_id,
                        DATE(created_at) as date,
                        COUNT(*) as actions_per_user
                    FROM feature_usage_analytics 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY user_id, DATE(created_at)
                ) daily_user_actions
                GROUP BY date
                ORDER BY date
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user engagement metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get content interaction statistics
     */
    public function getContentInteractionStats($days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    content_type,
                    action,
                    COUNT(*) as interaction_count,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT content_id) as unique_content_items
                FROM content_interaction_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY content_type, action
                ORDER BY interaction_count DESC
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting content interaction stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30) {
        try {
            // Get feature usage summary
            $stmt = $this->db->prepare("
                SELECT 
                    feature,
                    COUNT(*) as usage_count,
                    MAX(created_at) as last_used
                FROM feature_usage_analytics 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY feature
                ORDER BY usage_count DESC
            ");
            $stmt->execute([$userId, $days]);
            $featureUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get content interaction summary
            $stmt = $this->db->prepare("
                SELECT 
                    content_type,
                    action,
                    COUNT(*) as interaction_count,
                    MAX(created_at) as last_interaction
                FROM content_interaction_analytics 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY content_type, action
                ORDER BY interaction_count DESC
            ");
            $stmt->execute([$userId, $days]);
            $contentInteractions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get session summary
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as session_count,
                    MAX(created_at) as last_session
                FROM user_session_analytics 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$userId, $days]);
            $sessionSummary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'feature_usage' => $featureUsage,
                'content_interactions' => $contentInteractions,
                'session_summary' => $sessionSummary,
                'period_days' => $days
            ];
        } catch (Exception $e) {
            error_log("Error getting user activity summary: " . $e->getMessage());
            return [
                'feature_usage' => [],
                'content_interactions' => [],
                'session_summary' => ['session_count' => 0, 'last_session' => null],
                'period_days' => $days
            ];
        }
    }
    
    /**
     * Get popular features
     */
    public function getPopularFeatures($days = 30, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    feature,
                    COUNT(*) as usage_count,
                    COUNT(DISTINCT user_id) as unique_users,
                    ROUND(COUNT(*) / COUNT(DISTINCT user_id), 2) as avg_usage_per_user
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY feature
                ORDER BY usage_count DESC
                LIMIT ?
            ");
            $stmt->execute([$days, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting popular features: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user retention metrics
     */
    public function getUserRetentionMetrics($days = 30) {
        try {
            // Get daily active users
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(DISTINCT user_id) as daily_active_users
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            $dailyActiveUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get weekly active users
            $stmt = $this->db->prepare("
                SELECT 
                    YEARWEEK(created_at) as week,
                    COUNT(DISTINCT user_id) as weekly_active_users
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY YEARWEEK(created_at)
                ORDER BY week
            ");
            $stmt->execute([$days]);
            $weeklyActiveUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get monthly active users
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(DISTINCT user_id) as monthly_active_users
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $stmt->execute([$days]);
            $monthlyActiveUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'daily_active_users' => $dailyActiveUsers,
                'weekly_active_users' => $weeklyActiveUsers,
                'monthly_active_users' => $monthlyActiveUsers,
                'period_days' => $days
            ];
        } catch (Exception $e) {
            error_log("Error getting user retention metrics: " . $e->getMessage());
            return [
                'daily_active_users' => [],
                'weekly_active_users' => [],
                'monthly_active_users' => [],
                'period_days' => $days
            ];
        }
    }
    
    /**
     * Get usage trends
     */
    public function getUsageTrends($days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_actions,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT feature) as unique_features
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting usage trends: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get feature adoption metrics
     */
    public function getFeatureAdoptionMetrics($days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    feature,
                    COUNT(DISTINCT user_id) as total_users,
                    COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN user_id END) as new_users,
                    ROUND(COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN user_id END) / COUNT(DISTINCT user_id) * 100, 2) as adoption_rate
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY feature
                ORDER BY adoption_rate DESC
            ");
            $stmt->execute([$days, $days, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting feature adoption metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user behavior patterns
     */
    public function getUserBehaviorPatterns($days = 30) {
        try {
            // Get hourly usage patterns
            $stmt = $this->db->prepare("
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as usage_count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY HOUR(created_at)
                ORDER BY hour
            ");
            $stmt->execute([$days]);
            $hourlyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get daily usage patterns
            $stmt = $this->db->prepare("
                SELECT 
                    DAYOFWEEK(created_at) as day_of_week,
                    COUNT(*) as usage_count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM feature_usage_analytics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DAYOFWEEK(created_at)
                ORDER BY day_of_week
            ");
            $stmt->execute([$days]);
            $dailyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'hourly_patterns' => $hourlyPatterns,
                'daily_patterns' => $dailyPatterns,
                'period_days' => $days
            ];
        } catch (Exception $e) {
            error_log("Error getting user behavior patterns: " . $e->getMessage());
            return [
                'hourly_patterns' => [],
                'daily_patterns' => [],
                'period_days' => $days
            ];
        }
    }
    
    /**
     * Clean up old analytics data
     */
    public function cleanupOldData($days = 365) {
        try {
            // Clean up feature usage analytics
            $stmt = $this->db->prepare("
                DELETE FROM feature_usage_analytics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $featureUsageDeleted = $stmt->rowCount();
            
            // Clean up user session analytics
            $stmt = $this->db->prepare("
                DELETE FROM user_session_analytics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $sessionDeleted = $stmt->rowCount();
            
            // Clean up content interaction analytics
            $stmt = $this->db->prepare("
                DELETE FROM content_interaction_analytics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            $contentDeleted = $stmt->rowCount();
            
            return [
                'feature_usage_deleted' => $featureUsageDeleted,
                'session_analytics_deleted' => $sessionDeleted,
                'content_interaction_deleted' => $contentDeleted,
                'total_deleted' => $featureUsageDeleted + $sessionDeleted + $contentDeleted
            ];
        } catch (Exception $e) {
            error_log("Error cleaning up old analytics data: " . $e->getMessage());
            return [
                'feature_usage_deleted' => 0,
                'session_analytics_deleted' => 0,
                'content_interaction_deleted' => 0,
                'total_deleted' => 0
            ];
        }
    }
}
