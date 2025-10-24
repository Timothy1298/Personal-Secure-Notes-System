<?php
/**
 * Background Worker for Personal Notes System
 * Handles queued jobs, scheduled tasks, and background processing
 */

require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;
use Core\Cache;
use Core\Automation\ScheduledTasks;
use Core\Automation\WorkflowEngine;
use Core\DataManagement\MigrationService;
use Core\Analytics\UserBehaviorAnalytics;
use Core\Analytics\PerformanceAnalytics;
use Core\Analytics\UsageAnalytics;

class Worker {
    private $db;
    private $cache;
    private $running = true;
    private $jobs = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance($this->db);
        $this->setupSignalHandlers();
        $this->registerJobs();
    }
    
    public function start() {
        echo "Worker started at " . date('Y-m-d H:i:s') . "\n";
        
        while ($this->running) {
            try {
                $this->processJobs();
                $this->processScheduledTasks();
                $this->processWorkflows();
                $this->cleanupExpiredData();
                $this->processAnalytics();
                
                sleep(5); // Wait 5 seconds before next iteration
            } catch (Exception $e) {
                error_log("Worker error: " . $e->getMessage());
                sleep(10); // Wait longer on error
            }
        }
        
        echo "Worker stopped at " . date('Y-m-d H:i:s') . "\n";
    }
    
    private function setupSignalHandlers() {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }
    }
    
    public function handleSignal($signal) {
        echo "Received signal $signal, shutting down gracefully...\n";
        $this->running = false;
    }
    
    private function registerJobs() {
        $this->jobs = [
            'send_email' => [$this, 'processEmailJob'],
            'process_export' => [$this, 'processExportJob'],
            'process_import' => [$this, 'processImportJob'],
            'backup_database' => [$this, 'processBackupJob'],
            'cleanup_files' => [$this, 'processCleanupJob'],
            'generate_analytics' => [$this, 'processAnalyticsJob'],
            'process_webhook' => [$this, 'processWebhookJob'],
            'migrate_database' => [$this, 'processMigrationJob']
        ];
    }
    
    private function processJobs() {
        // Get pending jobs from queue (Redis or database)
        $jobs = $this->getPendingJobs();
        
        foreach ($jobs as $job) {
            try {
                $this->executeJob($job);
                $this->markJobCompleted($job['id']);
            } catch (Exception $e) {
                error_log("Job {$job['id']} failed: " . $e->getMessage());
                $this->markJobFailed($job['id'], $e->getMessage());
            }
        }
    }
    
    private function processScheduledTasks() {
        try {
            $scheduledTasks = new ScheduledTasks($this->db);
            $tasks = $scheduledTasks->getDueTasks();
            
            foreach ($tasks as $task) {
                try {
                    $scheduledTasks->executeTask($task['id']);
                    echo "Executed scheduled task: {$task['name']}\n";
                } catch (Exception $e) {
                    error_log("Scheduled task {$task['id']} failed: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Error processing scheduled tasks: " . $e->getMessage());
        }
    }
    
    private function processWorkflows() {
        try {
            $workflowEngine = new WorkflowEngine($this->db);
            $workflows = $workflowEngine->getActiveWorkflows();
            
            foreach ($workflows as $workflow) {
                try {
                    $workflowEngine->processWorkflow($workflow['id']);
                } catch (Exception $e) {
                    error_log("Workflow {$workflow['id']} failed: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Error processing workflows: " . $e->getMessage());
        }
    }
    
    private function cleanupExpiredData() {
        try {
            // Clean up expired sessions
            $this->db->exec("DELETE FROM sessions WHERE expires_at < NOW()");
            
            // Clean up expired cache entries
            $this->db->exec("DELETE FROM cache WHERE expiration_time < NOW()");
            
            // Clean up old analytics data (keep 1 year)
            $this->db->exec("DELETE FROM user_behavior_analytics WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
            $this->db->exec("DELETE FROM performance_metrics WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)");
            $this->db->exec("DELETE FROM feature_usage_analytics WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
            
            // Clean up old export/import files
            $this->cleanupOldFiles();
            
        } catch (Exception $e) {
            error_log("Error during cleanup: " . $e->getMessage());
        }
    }
    
    private function processAnalytics() {
        try {
            // Generate daily analytics summaries
            $this->generateDailyAnalytics();
            
            // Process user behavior patterns
            $this->processUserBehaviorPatterns();
            
            // Update performance metrics
            $this->updatePerformanceMetrics();
            
        } catch (Exception $e) {
            error_log("Error processing analytics: " . $e->getMessage());
        }
    }
    
    private function getPendingJobs() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, job_type, payload, priority, created_at
                FROM job_queue 
                WHERE status = 'pending' 
                ORDER BY priority DESC, created_at ASC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting pending jobs: " . $e->getMessage());
            return [];
        }
    }
    
    private function executeJob($job) {
        $jobType = $job['job_type'];
        $payload = json_decode($job['payload'], true);
        
        if (isset($this->jobs[$jobType])) {
            call_user_func($this->jobs[$jobType], $payload);
        } else {
            throw new Exception("Unknown job type: $jobType");
        }
    }
    
    private function markJobCompleted($jobId) {
        $stmt = $this->db->prepare("
            UPDATE job_queue 
            SET status = 'completed', completed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$jobId]);
    }
    
    private function markJobFailed($jobId, $error) {
        $stmt = $this->db->prepare("
            UPDATE job_queue 
            SET status = 'failed', error_message = ?, failed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$error, $jobId]);
    }
    
    // Job Processors
    private function processEmailJob($payload) {
        // Send email notification
        $to = $payload['to'];
        $subject = $payload['subject'];
        $body = $payload['body'];
        
        // Use mail() function or SMTP library
        mail($to, $subject, $body);
        echo "Email sent to: $to\n";
    }
    
    private function processExportJob($payload) {
        $userId = $payload['user_id'];
        $format = $payload['format'];
        $options = $payload['options'] ?? [];
        
        $exportService = new \Core\DataManagement\ExportService($this->db);
        
        switch ($format) {
            case 'json':
                $result = $exportService->exportUserData($userId, $options);
                break;
            case 'csv':
                $dataType = $payload['data_type'] ?? 'notes';
                $result = $exportService->exportToCSV($userId, $dataType, $options);
                break;
            case 'zip':
                $result = $exportService->createCompleteBackup($userId, $options);
                break;
            default:
                throw new Exception("Unsupported export format: $format");
        }
        
        if ($result['success']) {
            echo "Export completed: {$result['filename']}\n";
        } else {
            throw new Exception($result['error'] ?? 'Export failed');
        }
    }
    
    private function processImportJob($payload) {
        $userId = $payload['user_id'];
        $filepath = $payload['filepath'];
        $format = $payload['format'];
        $options = $payload['options'] ?? [];
        
        $importService = new \Core\DataManagement\ImportService($this->db);
        
        switch ($format) {
            case 'json':
                $result = $importService->importFromJSON($userId, $filepath, $options);
                break;
            case 'csv':
                $dataType = $payload['data_type'] ?? 'notes';
                $result = $importService->importFromCSV($userId, $filepath, $dataType, $options);
                break;
            case 'zip':
                $result = $importService->importFromZIP($userId, $filepath, $options);
                break;
            default:
                throw new Exception("Unsupported import format: $format");
        }
        
        if ($result['success']) {
            echo "Import completed: " . ($result['imported'] ?? 'N/A') . " items\n";
        } else {
            throw new Exception($result['error'] ?? 'Import failed');
        }
    }
    
    private function processBackupJob($payload) {
        $backupPath = $payload['backup_path'] ?? null;
        
        $migrationService = new MigrationService($this->db);
        $result = $migrationService->backupDatabase($backupPath);
        
        if ($result['success']) {
            echo "Database backup completed: {$result['backup_path']}\n";
        } else {
            throw new Exception($result['error'] ?? 'Backup failed');
        }
    }
    
    private function processCleanupJob($payload) {
        $days = $payload['days'] ?? 30;
        
        // Clean up old files
        $this->cleanupOldFiles($days);
        
        // Clean up old exports
        $exportService = new \Core\DataManagement\ExportService($this->db);
        $result = $exportService->cleanupOldExports($days);
        
        echo "Cleanup completed: {$result['deleted_files']} files deleted\n";
    }
    
    private function processAnalyticsJob($payload) {
        $userId = $payload['user_id'] ?? null;
        $type = $payload['type'] ?? 'daily';
        
        if ($type === 'daily') {
            $this->generateDailyAnalytics($userId);
        } elseif ($type === 'user_behavior') {
            $this->processUserBehaviorPatterns($userId);
        }
        
        echo "Analytics job completed: $type\n";
    }
    
    private function processWebhookJob($payload) {
        $url = $payload['url'];
        $data = $payload['data'];
        $headers = $payload['headers'] ?? [];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json'
        ], $headers));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "Webhook sent successfully to: $url\n";
        } else {
            throw new Exception("Webhook failed with HTTP code: $httpCode");
        }
    }
    
    private function processMigrationJob($payload) {
        $migrationFile = $payload['migration_file'] ?? null;
        
        $migrationService = new MigrationService($this->db);
        
        if ($migrationFile) {
            $result = $migrationService->runMigration($migrationFile);
        } else {
            $result = $migrationService->runMigrations();
        }
        
        if ($result['success']) {
            echo "Migration completed: {$result['migrations_run']} migrations run\n";
        } else {
            throw new Exception($result['error'] ?? 'Migration failed');
        }
    }
    
    private function cleanupOldFiles($days = 30) {
        $directories = [
            __DIR__ . '/exports',
            __DIR__ . '/imports',
            __DIR__ . '/backups',
            __DIR__ . '/uploads/temp'
        ];
        
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffTime) {
                        unlink($file);
                    }
                }
            }
        }
    }
    
    private function generateDailyAnalytics($userId = null) {
        try {
            $analytics = new UsageAnalytics($this->db);
            
            // Generate daily summaries
            $dailyStats = $analytics->getUsageTrends(1);
            
            // Store in cache for quick access
            $this->cache->set('daily_analytics_' . date('Y-m-d'), $dailyStats, 86400);
            
        } catch (Exception $e) {
            error_log("Error generating daily analytics: " . $e->getMessage());
        }
    }
    
    private function processUserBehaviorPatterns($userId = null) {
        try {
            $behaviorAnalytics = new UserBehaviorAnalytics($this->db);
            
            if ($userId) {
                $patterns = $behaviorAnalytics->getUserBehaviorPatterns($userId, 30);
            } else {
                // Process all users
                $stmt = $this->db->prepare("SELECT id FROM users");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($users as $uid) {
                    $patterns = $behaviorAnalytics->getUserBehaviorPatterns($uid, 30);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error processing user behavior patterns: " . $e->getMessage());
        }
    }
    
    private function updatePerformanceMetrics() {
        try {
            $performanceAnalytics = new PerformanceAnalytics($this->db);
            
            // Get system performance summary
            $metrics = $performanceAnalytics->getSystemPerformanceMetrics(1);
            
            // Store in cache
            $this->cache->set('performance_metrics_' . date('Y-m-d'), $metrics, 3600);
            
        } catch (Exception $e) {
            error_log("Error updating performance metrics: " . $e->getMessage());
        }
    }
}

// Start worker if run directly
if (php_sapi_name() === 'cli') {
    $worker = new Worker();
    $worker->start();
}
