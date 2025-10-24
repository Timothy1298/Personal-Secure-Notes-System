<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Analytics\UserBehaviorAnalytics;
use Core\Analytics\PerformanceAnalytics;
use Core\Analytics\UsageAnalytics;
use Exception;

class AnalyticsController {
    private $db;
    private $userBehaviorAnalytics;
    private $performanceAnalytics;
    private $usageAnalytics;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->userBehaviorAnalytics = new UserBehaviorAnalytics($this->db);
        $this->performanceAnalytics = new PerformanceAnalytics($this->db);
        $this->usageAnalytics = new UsageAnalytics($this->db);
    }
    
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        
        $userId = Session::get('user_id');
        $days = $_GET['days'] ?? 7;
        
        // Get user behavior summary
        $behaviorSummary = $this->userBehaviorAnalytics->getUserBehaviorSummary($userId, $days);
        
        // Get performance metrics
        $performanceMetrics = $this->performanceAnalytics->getSystemPerformanceMetrics($days);
        
        // Get usage analytics
        $usageStats = $this->usageAnalytics->getFeatureUsageStats($days);
        $popularFeatures = $this->usageAnalytics->getPopularFeatures($days, 10);
        
        // Get trends
        $performanceTrends = $this->performanceAnalytics->getPerformanceTrends($days);
        $usageTrends = $this->usageAnalytics->getUsageTrends($days);
        
        include __DIR__ . '/../Views/analytics.php';
    }
    
    public function userBehavior() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $days = $_GET['days'] ?? 7;
        
        try {
            $behaviorSummary = $this->userBehaviorAnalytics->getUserBehaviorSummary($userId, $days);
            $behaviorPatterns = $this->userBehaviorAnalytics->getUserBehaviorPatterns($userId, $days);
            $pageViews = $this->userBehaviorAnalytics->getUserPageViews($userId, $days);
            $sessionData = $this->userBehaviorAnalytics->getUserSessionData($userId, $days);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'summary' => $behaviorSummary,
                    'patterns' => $behaviorPatterns,
                    'page_views' => $pageViews,
                    'session_data' => $sessionData
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function performance() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $days = $_GET['days'] ?? 7;
        
        try {
            $performanceMetrics = $this->performanceAnalytics->getSystemPerformanceMetrics($days);
            $apiPerformance = $this->performanceAnalytics->getAPIPerformanceSummary($days);
            $dbPerformance = $this->performanceAnalytics->getDatabasePerformanceSummary($days);
            $pagePerformance = $this->performanceAnalytics->getPagePerformanceSummary($days);
            $performanceTrends = $this->performanceAnalytics->getPerformanceTrends($days);
            $slowQueries = $this->performanceAnalytics->getSlowQueries(1000, $days);
            $errorAnalysis = $this->performanceAnalytics->getErrorAnalysis($days);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'metrics' => $performanceMetrics,
                    'api_performance' => $apiPerformance,
                    'database_performance' => $dbPerformance,
                    'page_performance' => $pagePerformance,
                    'trends' => $performanceTrends,
                    'slow_queries' => $slowQueries,
                    'error_analysis' => $errorAnalysis
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function usage() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $days = $_GET['days'] ?? 7;
        
        try {
            $usageStats = $this->usageAnalytics->getFeatureUsageStats($days);
            $popularFeatures = $this->usageAnalytics->getPopularFeatures($days, 10);
            $userEngagement = $this->usageAnalytics->getUserEngagementMetrics($days);
            $contentInteractions = $this->usageAnalytics->getContentInteractionStats($days);
            $usageTrends = $this->usageAnalytics->getUsageTrends($days);
            $retentionMetrics = $this->usageAnalytics->getUserRetentionMetrics($days);
            $behaviorPatterns = $this->usageAnalytics->getUserBehaviorPatterns($days);
            $adoptionMetrics = $this->usageAnalytics->getFeatureAdoptionMetrics($days);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'usage_stats' => $usageStats,
                    'popular_features' => $popularFeatures,
                    'user_engagement' => $userEngagement,
                    'content_interactions' => $contentInteractions,
                    'trends' => $usageTrends,
                    'retention_metrics' => $retentionMetrics,
                    'behavior_patterns' => $behaviorPatterns,
                    'adoption_metrics' => $adoptionMetrics
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function userActivity() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $days = $_GET['days'] ?? 7;
        
        try {
            $activitySummary = $this->usageAnalytics->getUserActivitySummary($userId, $days);
            $userPerformance = $this->performanceAnalytics->getUserPerformanceMetrics($userId, $days);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'activity_summary' => $activitySummary,
                    'performance_metrics' => $userPerformance
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function trackBehavior() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $action = $_POST['action'] ?? '';
        $page = $_POST['page'] ?? '';
        $sessionId = $_POST['session_id'] ?? '';
        $metadata = $_POST['metadata'] ?? [];
        
        if (empty($action) || empty($page) || empty($sessionId)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        try {
            $result = $this->userBehaviorAnalytics->trackUserBehavior($userId, $action, $page, $sessionId, $metadata);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Behavior tracked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to track behavior']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function trackFeatureUsage() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $feature = $_POST['feature'] ?? '';
        $action = $_POST['action'] ?? '';
        $metadata = $_POST['metadata'] ?? [];
        
        if (empty($feature) || empty($action)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        try {
            $result = $this->usageAnalytics->trackFeatureUsage($userId, $feature, $action, $metadata);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Feature usage tracked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to track feature usage']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function trackContentInteraction() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $contentType = $_POST['content_type'] ?? '';
        $contentId = $_POST['content_id'] ?? '';
        $action = $_POST['action'] ?? '';
        $metadata = $_POST['metadata'] ?? [];
        
        if (empty($contentType) || empty($contentId) || empty($action)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        try {
            $result = $this->usageAnalytics->trackContentInteraction($userId, $contentType, $contentId, $action, $metadata);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Content interaction tracked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to track content interaction']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function cleanup() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $days = $_POST['days'] ?? 365;
        
        try {
            $behaviorCleanup = $this->userBehaviorAnalytics->cleanupOldData($days);
            $performanceCleanup = $this->performanceAnalytics->cleanupOldData($days);
            $usageCleanup = $this->usageAnalytics->cleanupOldData($days);
            
            echo json_encode([
                'success' => true,
                'message' => 'Analytics data cleaned up successfully',
                'data' => [
                    'behavior_cleanup' => $behaviorCleanup,
                    'performance_cleanup' => $performanceCleanup,
                    'usage_cleanup' => $usageCleanup
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
