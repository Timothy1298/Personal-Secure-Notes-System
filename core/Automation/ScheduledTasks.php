<?php
namespace Core\Automation;

use PDO;
use Exception;

class ScheduledTasks {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Schedule a task
     */
    public function scheduleTask($userId, $taskData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO scheduled_tasks 
                (user_id, name, description, task_type, task_data, schedule_type, schedule_data, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $taskData['name'],
                $taskData['description'],
                $taskData['task_type'],
                json_encode($taskData['task_data']),
                $taskData['schedule_type'],
                json_encode($taskData['schedule_data']),
                $taskData['is_active'] ?? true
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Schedule task failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user scheduled tasks
     */
    public function getUserScheduledTasks($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM scheduled_tasks 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tasks as &$task) {
                $task['task_data'] = json_decode($task['task_data'], true);
                $task['schedule_data'] = json_decode($task['schedule_data'], true);
            }
            
            return $tasks;
        } catch (Exception $e) {
            error_log("Get user scheduled tasks failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tasks ready for execution
     */
    public function getTasksReadyForExecution() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM scheduled_tasks 
                WHERE is_active = 1 
                AND (next_execution IS NULL OR next_execution <= NOW())
                ORDER BY next_execution ASC
            ");
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tasks as &$task) {
                $task['task_data'] = json_decode($task['task_data'], true);
                $task['schedule_data'] = json_decode($task['schedule_data'], true);
            }
            
            return $tasks;
        } catch (Exception $e) {
            error_log("Get tasks ready for execution failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute scheduled task
     */
    public function executeTask($taskId) {
        try {
            $task = $this->getTask($taskId);
            if (!$task) {
                throw new Exception("Task not found: {$taskId}");
            }
            
            // Create execution record
            $executionId = $this->createExecution($taskId);
            
            // Execute task based on type
            $result = $this->executeTaskByType($task);
            
            // Update execution record
            $this->updateExecution($executionId, $result);
            
            // Update next execution time
            $this->updateNextExecution($taskId);
            
            return $result;
        } catch (Exception $e) {
            error_log("Execute task failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get task
     */
    public function getTask($taskId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM scheduled_tasks WHERE id = ?
            ");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($task) {
                $task['task_data'] = json_decode($task['task_data'], true);
                $task['schedule_data'] = json_decode($task['schedule_data'], true);
            }
            
            return $task;
        } catch (Exception $e) {
            error_log("Get task failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update task
     */
    public function updateTask($taskId, $taskData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE scheduled_tasks 
                SET name = ?, description = ?, task_type = ?, task_data = ?, 
                    schedule_type = ?, schedule_data = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $taskData['name'],
                $taskData['description'],
                $taskData['task_type'],
                json_encode($taskData['task_data']),
                $taskData['schedule_type'],
                json_encode($taskData['schedule_data']),
                $taskData['is_active'] ?? true,
                $taskId
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update task failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete task
     */
    public function deleteTask($taskId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM scheduled_tasks WHERE id = ?
            ");
            $stmt->execute([$taskId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Delete task failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute task by type
     */
    private function executeTaskByType($task) {
        $taskType = $task['task_type'];
        $taskData = $task['task_data'];
        $userId = $task['user_id'];
        
        switch ($taskType) {
            case 'workflow':
                return $this->executeWorkflowTask($taskData, $userId);
            
            case 'email_reminder':
                return $this->executeEmailReminderTask($taskData, $userId);
            
            case 'data_cleanup':
                return $this->executeDataCleanupTask($taskData, $userId);
            
            case 'backup':
                return $this->executeBackupTask($taskData, $userId);
            
            case 'report_generation':
                return $this->executeReportGenerationTask($taskData, $userId);
            
            case 'api_sync':
                return $this->executeApiSyncTask($taskData, $userId);
            
            default:
                throw new Exception("Unknown task type: {$taskType}");
        }
    }
    
    /**
     * Execute workflow task
     */
    private function executeWorkflowTask($taskData, $userId) {
        try {
            $workflowEngine = new WorkflowEngine($this->db);
            $workflowId = $taskData['workflow_id'];
            $triggerData = $taskData['trigger_data'] ?? [];
            
            $result = $workflowEngine->executeWorkflow($workflowId, $userId, $triggerData);
            
            return [
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Workflow executed successfully' : 'Workflow execution failed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Workflow task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute email reminder task
     */
    private function executeEmailReminderTask($taskData, $userId) {
        try {
            $email = $taskData['email'];
            $subject = $taskData['subject'];
            $body = $taskData['body'];
            
            $emailService = new \Core\EmailService($this->db);
            $result = $emailService->sendEmail($email, $subject, $body);
            
            return [
                'success' => $result,
                'data' => ['email_sent' => $result],
                'message' => $result ? 'Email reminder sent successfully' : 'Email reminder failed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Email reminder task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute data cleanup task
     */
    private function executeDataCleanupTask($taskData, $userId) {
        try {
            $cleanupType = $taskData['cleanup_type'];
            $daysOld = $taskData['days_old'] ?? 30;
            
            $cleanedCount = 0;
            
            switch ($cleanupType) {
                case 'old_notes':
                    $cleanedCount = $this->cleanupOldNotes($userId, $daysOld);
                    break;
                
                case 'old_tasks':
                    $cleanedCount = $this->cleanupOldTasks($userId, $daysOld);
                    break;
                
                case 'old_files':
                    $cleanedCount = $this->cleanupOldFiles($userId, $daysOld);
                    break;
                
                case 'cache':
                    $cleanedCount = $this->cleanupCache($daysOld);
                    break;
            }
            
            return [
                'success' => true,
                'data' => ['cleaned_count' => $cleanedCount],
                'message' => "Cleaned up {$cleanedCount} items"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Data cleanup task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute backup task
     */
    private function executeBackupTask($taskData, $userId) {
        try {
            $backupType = $taskData['backup_type'];
            $backupPath = $taskData['backup_path'] ?? '/tmp/backups/';
            
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $backupFile = $backupPath . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Create database backup
            $command = "mysqldump -u timothy -p41181671Timothy@ personal > {$backupFile}";
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                return [
                    'success' => true,
                    'data' => ['backup_file' => $backupFile],
                    'message' => 'Backup created successfully'
                ];
            } else {
                throw new Exception("Backup command failed with return code: {$returnCode}");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Backup task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute report generation task
     */
    private function executeReportGenerationTask($taskData, $userId) {
        try {
            $reportType = $taskData['report_type'];
            $reportData = $taskData['report_data'] ?? [];
            
            $report = $this->generateReport($reportType, $userId, $reportData);
            
            return [
                'success' => true,
                'data' => $report,
                'message' => 'Report generated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Report generation task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute API sync task
     */
    private function executeApiSyncTask($taskData, $userId) {
        try {
            $apiEndpoint = $taskData['api_endpoint'];
            $syncType = $taskData['sync_type'];
            $syncData = $taskData['sync_data'] ?? [];
            
            $result = $this->syncWithApi($apiEndpoint, $syncType, $syncData, $userId);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'API sync completed successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "API sync task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cleanup old notes
     */
    private function cleanupOldNotes($userId, $daysOld) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notes 
                WHERE user_id = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND is_deleted = 1
            ");
            $stmt->execute([$userId, $daysOld]);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Cleanup old notes failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cleanup old tasks
     */
    private function cleanupOldTasks($userId, $daysOld) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM tasks 
                WHERE user_id = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND status = 'completed'
            ");
            $stmt->execute([$userId, $daysOld]);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Cleanup old tasks failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cleanup old files
     */
    private function cleanupOldFiles($userId, $daysOld) {
        try {
            $stmt = $this->db->prepare("
                SELECT file_path FROM files 
                WHERE user_id = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND is_deleted = 1
            ");
            $stmt->execute([$userId, $daysOld]);
            $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $deletedCount = 0;
            foreach ($files as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                    $deletedCount++;
                }
            }
            
            // Delete database records
            $stmt = $this->db->prepare("
                DELETE FROM files 
                WHERE user_id = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND is_deleted = 1
            ");
            $stmt->execute([$userId, $daysOld]);
            
            return $deletedCount;
        } catch (Exception $e) {
            error_log("Cleanup old files failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cleanup cache
     */
    private function cleanupCache($daysOld) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM cache 
                WHERE expiration_time < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$daysOld]);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Cleanup cache failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Generate report
     */
    private function generateReport($reportType, $userId, $reportData) {
        // This would generate various types of reports
        // For now, return a placeholder
        return [
            'report_type' => $reportType,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $reportData
        ];
    }
    
    /**
     * Sync with API
     */
    private function syncWithApi($apiEndpoint, $syncType, $syncData, $userId) {
        // This would sync data with external APIs
        // For now, return a placeholder
        return [
            'api_endpoint' => $apiEndpoint,
            'sync_type' => $syncType,
            'synced_at' => date('Y-m-d H:i:s'),
            'data' => $syncData
        ];
    }
    
    /**
     * Create execution record
     */
    private function createExecution($taskId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO task_executions 
                (task_id, status, started_at) 
                VALUES (?, 'running', NOW())
            ");
            
            $stmt->execute([$taskId]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Create execution failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update execution record
     */
    private function updateExecution($executionId, $result) {
        try {
            $stmt = $this->db->prepare("
                UPDATE task_executions 
                SET status = ?, result_data = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $result['success'] ? 'completed' : 'failed',
                json_encode($result),
                $executionId
            ]);
        } catch (Exception $e) {
            error_log("Update execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update next execution time
     */
    private function updateNextExecution($taskId) {
        try {
            $task = $this->getTask($taskId);
            if (!$task) {
                return;
            }
            
            $scheduleType = $task['schedule_type'];
            $scheduleData = $task['schedule_data'];
            
            $nextExecution = $this->calculateNextExecution($scheduleType, $scheduleData);
            
            $stmt = $this->db->prepare("
                UPDATE scheduled_tasks 
                SET next_execution = ?, last_execution = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([$nextExecution, $taskId]);
        } catch (Exception $e) {
            error_log("Update next execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Calculate next execution time
     */
    private function calculateNextExecution($scheduleType, $scheduleData) {
        $now = new \DateTime();
        
        switch ($scheduleType) {
            case 'once':
                return null; // No next execution
            
            case 'daily':
                $hour = $scheduleData['hour'] ?? 0;
                $minute = $scheduleData['minute'] ?? 0;
                $next = clone $now;
                $next->setTime($hour, $minute, 0);
                if ($next <= $now) {
                    $next->add(new \DateInterval('P1D'));
                }
                return $next->format('Y-m-d H:i:s');
            
            case 'weekly':
                $dayOfWeek = $scheduleData['day_of_week'] ?? 1;
                $hour = $scheduleData['hour'] ?? 0;
                $minute = $scheduleData['minute'] ?? 0;
                $next = clone $now;
                $next->setTime($hour, $minute, 0);
                $daysUntilTarget = ($dayOfWeek - $now->format('w') + 7) % 7;
                if ($daysUntilTarget === 0 && $next <= $now) {
                    $daysUntilTarget = 7;
                }
                $next->add(new \DateInterval("P{$daysUntilTarget}D"));
                return $next->format('Y-m-d H:i:s');
            
            case 'monthly':
                $dayOfMonth = $scheduleData['day_of_month'] ?? 1;
                $hour = $scheduleData['hour'] ?? 0;
                $minute = $scheduleData['minute'] ?? 0;
                $next = clone $now;
                $next->setTime($hour, $minute, 0);
                $next->setDate($now->format('Y'), $now->format('m'), $dayOfMonth);
                if ($next <= $now) {
                    $next->add(new \DateInterval('P1M'));
                }
                return $next->format('Y-m-d H:i:s');
            
            case 'interval':
                $interval = $scheduleData['interval'] ?? 1;
                $unit = $scheduleData['unit'] ?? 'hours';
                $next = clone $now;
                $next->add(new \DateInterval("PT{$interval}" . strtoupper($unit[0])));
                return $next->format('Y-m-d H:i:s');
            
            default:
                return null;
        }
    }
}
