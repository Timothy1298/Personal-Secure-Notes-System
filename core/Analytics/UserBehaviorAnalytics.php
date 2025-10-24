<?php
namespace Core\Analytics;

use PDO;
use Exception;

class UserBehaviorAnalytics {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Track user action
     */
    public function trackAction($userId, $action, $data = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_behavior_events 
                (user_id, action, data, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error tracking user action: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user behavior summary
     */
    public function getUserBehaviorSummary($userId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    action,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action, DATE(created_at)
                ORDER BY date DESC, count DESC
            ");
            
            $stmt->execute([$userId, $days]);
            $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get most active hours
            $stmt = $this->db->prepare("
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY HOUR(created_at)
                ORDER BY count DESC
                LIMIT 5
            ");
            
            $stmt->execute([$userId, $days]);
            $activeHours = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get feature usage
            $stmt = $this->db->prepare("
                SELECT 
                    JSON_EXTRACT(data, '$.feature') as feature,
                    COUNT(*) as count
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND JSON_EXTRACT(data, '$.feature') IS NOT NULL
                GROUP BY JSON_EXTRACT(data, '$.feature')
                ORDER BY count DESC
                LIMIT 10
            ");
            
            $stmt->execute([$userId, $days]);
            $featureUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'activity' => $activity,
                'active_hours' => $activeHours,
                'feature_usage' => $featureUsage,
                'total_actions' => array_sum(array_column($activity, 'count')),
                'unique_actions' => count(array_unique(array_column($activity, 'action')))
            ];
        } catch (Exception $e) {
            error_log("Error getting user behavior summary: " . $e->getMessage());
            return [
                'activity' => [],
                'active_hours' => [],
                'feature_usage' => [],
                'total_actions' => 0,
                'unique_actions' => 0
            ];
        }
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    action,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action, DATE(created_at)
                ORDER BY date DESC, count DESC
            ");
            $stmt->execute([$userId, $days]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by action
            $summary = [];
            foreach ($activities as $activity) {
                if (!isset($summary[$activity['action']])) {
                    $summary[$activity['action']] = [
                        'total_count' => 0,
                        'daily_breakdown' => []
                    ];
                }
                $summary[$activity['action']]['total_count'] += $activity['count'];
                $summary[$activity['action']]['daily_breakdown'][$activity['date']] = $activity['count'];
            }
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting user activity summary: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user engagement metrics
     */
    public function getUserEngagementMetrics($userId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT DATE(created_at)) as active_days,
                    COUNT(*) as total_actions,
                    AVG(daily_actions) as avg_daily_actions
                FROM (
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as daily_actions
                    FROM user_behavior_events 
                    WHERE user_id = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                ) daily_stats
            ");
            $stmt->execute([$userId, $days]);
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate engagement score (0-100)
            $engagementScore = 0;
            if ($metrics['active_days'] > 0) {
                $engagementScore = min(100, ($metrics['active_days'] / $days) * 100);
            }
            
            $metrics['engagement_score'] = round($engagementScore, 2);
            
            return $metrics;
        } catch (Exception $e) {
            error_log("Error getting user engagement metrics: " . $e->getMessage());
            return [
                'active_days' => 0,
                'total_actions' => 0,
                'avg_daily_actions' => 0,
                'engagement_score' => 0
            ];
        }
    }
    
    /**
     * Get user session analytics
     */
    public function getUserSessionAnalytics($userId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    MIN(created_at) as first_action,
                    MAX(created_at) as last_action,
                    COUNT(*) as actions_count,
                    TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(created_at)) as session_duration_minutes
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute([$userId, $days]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate session metrics
            $totalSessions = count($sessions);
            $totalDuration = array_sum(array_column($sessions, 'session_duration_minutes'));
            $avgSessionDuration = $totalSessions > 0 ? $totalDuration / $totalSessions : 0;
            
            return [
                'sessions' => $sessions,
                'total_sessions' => $totalSessions,
                'avg_session_duration_minutes' => round($avgSessionDuration, 2),
                'total_duration_minutes' => $totalDuration
            ];
        } catch (Exception $e) {
            error_log("Error getting user session analytics: " . $e->getMessage());
            return [
                'sessions' => [],
                'total_sessions' => 0,
                'avg_session_duration_minutes' => 0,
                'total_duration_minutes' => 0
            ];
        }
    }
    
    /**
     * Get feature usage analytics
     */
    public function getFeatureUsageAnalytics($userId, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    action,
                    COUNT(*) as usage_count,
                    COUNT(DISTINCT DATE(created_at)) as days_used,
                    MAX(created_at) as last_used
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action
                ORDER BY usage_count DESC
            ");
            $stmt->execute([$userId, $days]);
            $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Categorize features
            $categorized = [
                'notes' => [],
                'tasks' => [],
                'search' => [],
                'automation' => [],
                'integrations' => [],
                'other' => []
            ];
            
            foreach ($features as $feature) {
                $action = $feature['action'];
                if (strpos($action, 'note') !== false) {
                    $categorized['notes'][] = $feature;
                } elseif (strpos($action, 'task') !== false) {
                    $categorized['tasks'][] = $feature;
                } elseif (strpos($action, 'search') !== false) {
                    $categorized['search'][] = $feature;
                } elseif (strpos($action, 'automation') !== false || strpos($action, 'workflow') !== false) {
                    $categorized['automation'][] = $feature;
                } elseif (strpos($action, 'integration') !== false) {
                    $categorized['integrations'][] = $feature;
                } else {
                    $categorized['other'][] = $feature;
                }
            }
            
            return $categorized;
        } catch (Exception $e) {
            error_log("Error getting feature usage analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user productivity insights
     */
    public function getUserProductivityInsights($userId, $days = 30) {
        try {
            // Get notes and tasks created
            $stmt = $this->db->prepare("
                SELECT 
                    'notes' as type,
                    COUNT(*) as created_count,
                    DATE(created_at) as date
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                
                UNION ALL
                
                SELECT 
                    'tasks' as type,
                    COUNT(*) as created_count,
                    DATE(created_at) as date
                FROM tasks 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                
                ORDER BY date DESC
            ");
            $stmt->execute([$userId, $days, $userId, $days]);
            $productivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate insights
            $totalNotes = 0;
            $totalTasks = 0;
            $mostProductiveDay = null;
            $maxProductivity = 0;
            
            $dailyProductivity = [];
            foreach ($productivity as $item) {
                $date = $item['date'];
                if (!isset($dailyProductivity[$date])) {
                    $dailyProductivity[$date] = ['notes' => 0, 'tasks' => 0];
                }
                $dailyProductivity[$date][$item['type']] = $item['created_count'];
                
                if ($item['type'] === 'notes') {
                    $totalNotes += $item['created_count'];
                } else {
                    $totalTasks += $item['created_count'];
                }
                
                $dayTotal = $dailyProductivity[$date]['notes'] + $dailyProductivity[$date]['tasks'];
                if ($dayTotal > $maxProductivity) {
                    $maxProductivity = $dayTotal;
                    $mostProductiveDay = $date;
                }
            }
            
            return [
                'total_notes_created' => $totalNotes,
                'total_tasks_created' => $totalTasks,
                'most_productive_day' => $mostProductiveDay,
                'max_daily_productivity' => $maxProductivity,
                'daily_breakdown' => $dailyProductivity,
                'avg_daily_notes' => round($totalNotes / $days, 2),
                'avg_daily_tasks' => round($totalTasks / $days, 2)
            ];
        } catch (Exception $e) {
            error_log("Error getting user productivity insights: " . $e->getMessage());
            return [
                'total_notes_created' => 0,
                'total_tasks_created' => 0,
                'most_productive_day' => null,
                'max_daily_productivity' => 0,
                'daily_breakdown' => [],
                'avg_daily_notes' => 0,
                'avg_daily_tasks' => 0
            ];
        }
    }
    
    /**
     * Get user behavior patterns
     */
    public function getUserBehaviorPatterns($userId, $days = 30) {
        try {
            // Get hourly activity patterns
            $stmt = $this->db->prepare("
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as activity_count
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY HOUR(created_at)
                ORDER BY hour
            ");
            $stmt->execute([$userId, $days]);
            $hourlyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get weekly patterns
            $stmt = $this->db->prepare("
                SELECT 
                    DAYOFWEEK(created_at) as day_of_week,
                    COUNT(*) as activity_count
                FROM user_behavior_events 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DAYOFWEEK(created_at)
                ORDER BY day_of_week
            ");
            $stmt->execute([$userId, $days]);
            $weeklyPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Find peak hours and days
            $peakHour = 0;
            $maxHourlyActivity = 0;
            foreach ($hourlyPatterns as $pattern) {
                if ($pattern['activity_count'] > $maxHourlyActivity) {
                    $maxHourlyActivity = $pattern['activity_count'];
                    $peakHour = $pattern['hour'];
                }
            }
            
            $peakDay = 1;
            $maxDailyActivity = 0;
            foreach ($weeklyPatterns as $pattern) {
                if ($pattern['activity_count'] > $maxDailyActivity) {
                    $maxDailyActivity = $pattern['activity_count'];
                    $peakDay = $pattern['day_of_week'];
                }
            }
            
            return [
                'hourly_patterns' => $hourlyPatterns,
                'weekly_patterns' => $weeklyPatterns,
                'peak_hour' => $peakHour,
                'peak_day_of_week' => $peakDay,
                'max_hourly_activity' => $maxHourlyActivity,
                'max_daily_activity' => $maxDailyActivity
            ];
        } catch (Exception $e) {
            error_log("Error getting user behavior patterns: " . $e->getMessage());
            return [
                'hourly_patterns' => [],
                'weekly_patterns' => [],
                'peak_hour' => 0,
                'peak_day_of_week' => 1,
                'max_hourly_activity' => 0,
                'max_daily_activity' => 0
            ];
        }
    }
    
    /**
     * Get user retention metrics
     */
    public function getUserRetentionMetrics($userId) {
        try {
            // Get user registration date
            $stmt = $this->db->prepare("SELECT created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return null;
            }
            
            $registrationDate = new \DateTime($user['created_at']);
            $now = new \DateTime();
            $daysSinceRegistration = $now->diff($registrationDate)->days;
            
            // Get first and last activity
            $stmt = $this->db->prepare("
                SELECT 
                    MIN(created_at) as first_activity,
                    MAX(created_at) as last_activity
                FROM user_behavior_events 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$activity['first_activity']) {
                return [
                    'days_since_registration' => $daysSinceRegistration,
                    'first_activity' => null,
                    'last_activity' => null,
                    'days_since_last_activity' => null,
                    'retention_status' => 'inactive'
                ];
            }
            
            $lastActivity = new \DateTime($activity['last_activity']);
            $daysSinceLastActivity = $now->diff($lastActivity)->days;
            
            // Determine retention status
            $retentionStatus = 'active';
            if ($daysSinceLastActivity > 30) {
                $retentionStatus = 'churned';
            } elseif ($daysSinceLastActivity > 7) {
                $retentionStatus = 'at_risk';
            }
            
            return [
                'days_since_registration' => $daysSinceRegistration,
                'first_activity' => $activity['first_activity'],
                'last_activity' => $activity['last_activity'],
                'days_since_last_activity' => $daysSinceLastActivity,
                'retention_status' => $retentionStatus
            ];
        } catch (Exception $e) {
            error_log("Error getting user retention metrics: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get comprehensive user analytics
     */
    public function getComprehensiveUserAnalytics($userId, $days = 30) {
        return [
            'user_id' => $userId,
            'period_days' => $days,
            'engagement' => $this->getUserEngagementMetrics($userId, $days),
            'sessions' => $this->getUserSessionAnalytics($userId, $days),
            'feature_usage' => $this->getFeatureUsageAnalytics($userId, $days),
            'productivity' => $this->getUserProductivityInsights($userId, $days),
            'behavior_patterns' => $this->getUserBehaviorPatterns($userId, $days),
            'retention' => $this->getUserRetentionMetrics($userId),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
