<?php
namespace Core\DataManagement;

use PDO;
use Exception;
use ZipArchive;

class ExportService {
    private $db;
    private $exportDir;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->exportDir = __DIR__ . '/../../exports/';
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0777, true);
        }
    }
    
    /**
     * Export user data in JSON format
     */
    public function exportUserData(int $userId, array $options = []): array {
        try {
            $includeNotes = $options['include_notes'] ?? true;
            $includeTasks = $options['include_tasks'] ?? true;
            $includeTags = $options['include_tags'] ?? true;
            $includeSettings = $options['include_settings'] ?? true;
            $includeAnalytics = $options['include_analytics'] ?? false;
            $dateRange = $options['date_range'] ?? null;
            
            $exportData = [
                'export_info' => [
                    'user_id' => $userId,
                    'export_date' => date('Y-m-d H:i:s'),
                    'export_version' => '1.0',
                    'options' => $options
                ],
                'user_profile' => $this->getUserProfile($userId),
                'data' => []
            ];
            
            if ($includeNotes) {
                $exportData['data']['notes'] = $this->getUserNotes($userId, $dateRange);
            }
            
            if ($includeTasks) {
                $exportData['data']['tasks'] = $this->getUserTasks($userId, $dateRange);
            }
            
            if ($includeTags) {
                $exportData['data']['tags'] = $this->getUserTags($userId);
            }
            
            if ($includeSettings) {
                $exportData['data']['settings'] = $this->getUserSettings($userId);
            }
            
            if ($includeAnalytics) {
                $exportData['data']['analytics'] = $this->getUserAnalytics($userId, $dateRange);
            }
            
            $filename = 'user_export_' . $userId . '_' . date('Y-m-d_H-i-s') . '.json';
            $filepath = $this->exportDir . $filename;
            
            if (file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT))) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                throw new Exception('Failed to write export file');
            }
            
        } catch (Exception $e) {
            error_log("Error exporting user data: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Export data in CSV format
     */
    public function exportToCSV(int $userId, string $dataType, array $options = []): array {
        try {
            $dateRange = $options['date_range'] ?? null;
            $filename = $dataType . '_export_' . $userId . '_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = $this->exportDir . $filename;
            
            $data = [];
            $headers = [];
            
            switch ($dataType) {
                case 'notes':
                    $data = $this->getUserNotes($userId, $dateRange);
                    $headers = ['id', 'title', 'content', 'tags', 'created_at', 'updated_at', 'is_pinned', 'color'];
                    break;
                case 'tasks':
                    $data = $this->getUserTasks($userId, $dateRange);
                    $headers = ['id', 'title', 'description', 'status', 'priority', 'due_date', 'created_at', 'updated_at'];
                    break;
                case 'tags':
                    $data = $this->getUserTags($userId);
                    $headers = ['id', 'name', 'color', 'created_at'];
                    break;
                default:
                    throw new Exception('Unsupported data type for CSV export');
            }
            
            $file = fopen($filepath, 'w');
            if (!$file) {
                throw new Exception('Failed to create CSV file');
            }
            
            // Write headers
            fputcsv($file, $headers);
            
            // Write data
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($headers as $header) {
                    if ($header === 'tags' && isset($row['tags'])) {
                        $csvRow[] = is_array($row['tags']) ? implode(',', $row['tags']) : $row['tags'];
                    } else {
                        $csvRow[] = $row[$header] ?? '';
                    }
                }
                fputcsv($file, $csvRow);
            }
            
            fclose($file);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'rows' => count($data)
            ];
            
        } catch (Exception $e) {
            error_log("Error exporting to CSV: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Export data in XML format
     */
    public function exportToXML(int $userId, array $options = []): array {
        try {
            $includeNotes = $options['include_notes'] ?? true;
            $includeTasks = $options['include_tasks'] ?? true;
            $includeTags = $options['include_tags'] ?? true;
            $dateRange = $options['date_range'] ?? null;
            
            $filename = 'user_export_' . $userId . '_' . date('Y-m-d_H-i-s') . '.xml';
            $filepath = $this->exportDir . $filename;
            
            $xml = new \SimpleXMLElement('<export></export>');
            $xml->addAttribute('version', '1.0');
            $xml->addAttribute('user_id', $userId);
            $xml->addAttribute('export_date', date('Y-m-d H:i:s'));
            
            if ($includeNotes) {
                $notesNode = $xml->addChild('notes');
                $notes = $this->getUserNotes($userId, $dateRange);
                foreach ($notes as $note) {
                    $noteNode = $notesNode->addChild('note');
                    $noteNode->addAttribute('id', $note['id']);
                    $noteNode->addChild('title', htmlspecialchars($note['title']));
                    $noteNode->addChild('content', htmlspecialchars($note['content']));
                    $noteNode->addChild('created_at', $note['created_at']);
                    $noteNode->addChild('updated_at', $note['updated_at']);
                    $noteNode->addChild('is_pinned', $note['is_pinned'] ? 'true' : 'false');
                    $noteNode->addChild('color', $note['color']);
                    
                    if (!empty($note['tags'])) {
                        $tagsNode = $noteNode->addChild('tags');
                        foreach ($note['tags'] as $tag) {
                            $tagsNode->addChild('tag', htmlspecialchars($tag));
                        }
                    }
                }
            }
            
            if ($includeTasks) {
                $tasksNode = $xml->addChild('tasks');
                $tasks = $this->getUserTasks($userId, $dateRange);
                foreach ($tasks as $task) {
                    $taskNode = $tasksNode->addChild('task');
                    $taskNode->addAttribute('id', $task['id']);
                    $taskNode->addChild('title', htmlspecialchars($task['title']));
                    $taskNode->addChild('description', htmlspecialchars($task['description']));
                    $taskNode->addChild('status', $task['status']);
                    $taskNode->addChild('priority', $task['priority']);
                    $taskNode->addChild('due_date', $task['due_date']);
                    $taskNode->addChild('created_at', $task['created_at']);
                    $taskNode->addChild('updated_at', $task['updated_at']);
                }
            }
            
            if ($includeTags) {
                $tagsNode = $xml->addChild('tags');
                $tags = $this->getUserTags($userId);
                foreach ($tags as $tag) {
                    $tagNode = $tagsNode->addChild('tag');
                    $tagNode->addAttribute('id', $tag['id']);
                    $tagNode->addChild('name', htmlspecialchars($tag['name']));
                    $tagNode->addChild('color', $tag['color']);
                    $tagNode->addChild('created_at', $tag['created_at']);
                }
            }
            
            if ($xml->asXML($filepath)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                throw new Exception('Failed to write XML file');
            }
            
        } catch (Exception $e) {
            error_log("Error exporting to XML: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a complete backup with files
     */
    public function createCompleteBackup(int $userId, array $options = []): array {
        try {
            $backupName = 'complete_backup_' . $userId . '_' . date('Y-m-d_H-i-s');
            $backupDir = $this->exportDir . $backupName . '/';
            
            if (!mkdir($backupDir, 0777, true)) {
                throw new Exception('Failed to create backup directory');
            }
            
            // Export JSON data
            $jsonExport = $this->exportUserData($userId, $options);
            if (!$jsonExport['success']) {
                throw new Exception('Failed to export JSON data: ' . $jsonExport['error']);
            }
            
            // Copy JSON file to backup directory
            copy($jsonExport['filepath'], $backupDir . 'data.json');
            
            // Export CSV files
            $csvNotes = $this->exportToCSV($userId, 'notes', $options);
            if ($csvNotes['success']) {
                copy($csvNotes['filepath'], $backupDir . 'notes.csv');
            }
            
            $csvTasks = $this->exportToCSV($userId, 'tasks', $options);
            if ($csvTasks['success']) {
                copy($csvTasks['filepath'], $backupDir . 'tasks.csv');
            }
            
            // Copy user files (voice notes, OCR images, etc.)
            $this->copyUserFiles($userId, $backupDir);
            
            // Create ZIP archive
            $zipFilename = $backupName . '.zip';
            $zipFilepath = $this->exportDir . $zipFilename;
            
            $zip = new ZipArchive();
            if ($zip->open($zipFilepath, ZipArchive::CREATE) !== TRUE) {
                throw new Exception('Cannot create ZIP file');
            }
            
            $this->addDirectoryToZip($zip, $backupDir, '');
            $zip->close();
            
            // Clean up temporary directory
            $this->deleteDirectory($backupDir);
            
            return [
                'success' => true,
                'filename' => $zipFilename,
                'filepath' => $zipFilepath,
                'size' => filesize($zipFilepath)
            ];
            
        } catch (Exception $e) {
            error_log("Error creating complete backup: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user profile data
     */
    private function getUserProfile(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT id, username, email, first_name, last_name, created_at, updated_at
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Get user notes
     */
    private function getUserNotes(int $userId, ?string $dateRange = null): array {
        $sql = "
            SELECT n.id, n.title, n.content, n.created_at, n.updated_at, n.is_pinned, n.color,
                   GROUP_CONCAT(t.name) as tags
            FROM notes n
            LEFT JOIN note_tags nt ON n.id = nt.note_id
            LEFT JOIN tags t ON nt.tag_id = t.id
            WHERE n.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($dateRange) {
            $sql .= " AND n.created_at >= ?";
            $params[] = $dateRange;
        }
        
        $sql .= " GROUP BY n.id ORDER BY n.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process tags
        foreach ($notes as &$note) {
            $note['tags'] = $note['tags'] ? explode(',', $note['tags']) : [];
        }
        
        return $notes;
    }
    
    /**
     * Get user tasks
     */
    private function getUserTasks(int $userId, ?string $dateRange = null): array {
        $sql = "
            SELECT id, title, description, status, priority, due_date, created_at, updated_at
            FROM tasks
            WHERE user_id = ?
        ";
        
        $params = [$userId];
        
        if ($dateRange) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateRange;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user tags
     */
    private function getUserTags(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT t.id, t.name, t.color, t.created_at
            FROM tags t
            JOIN note_tags nt ON t.id = nt.tag_id
            JOIN notes n ON nt.note_id = n.id
            WHERE n.user_id = ?
            ORDER BY t.name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user settings
     */
    private function getUserSettings(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value, created_at, updated_at
            FROM user_settings
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user analytics data
     */
    private function getUserAnalytics(int $userId, ?string $dateRange = null): array {
        $analytics = [];
        
        // Get behavior analytics
        $sql = "
            SELECT action, page, COUNT(*) as count, MAX(created_at) as last_action
            FROM user_behavior_analytics
            WHERE user_id = ?
        ";
        
        $params = [$userId];
        
        if ($dateRange) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateRange;
        }
        
        $sql .= " GROUP BY action, page ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $analytics['behavior'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get feature usage
        $sql = "
            SELECT feature, action, COUNT(*) as count, MAX(created_at) as last_used
            FROM feature_usage_analytics
            WHERE user_id = ?
        ";
        
        $params = [$userId];
        
        if ($dateRange) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateRange;
        }
        
        $sql .= " GROUP BY feature, action ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $analytics['feature_usage'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $analytics;
    }
    
    /**
     * Copy user files to backup directory
     */
    private function copyUserFiles(int $userId, string $backupDir): void {
        $filesDir = $backupDir . 'files/';
        mkdir($filesDir, 0777, true);
        
        // Copy voice notes
        $voiceNotesDir = __DIR__ . '/../../uploads/voice_notes/';
        if (is_dir($voiceNotesDir)) {
            $userVoiceNotes = glob($voiceNotesDir . '*');
            foreach ($userVoiceNotes as $file) {
                if (is_file($file)) {
                    copy($file, $filesDir . 'voice_notes/' . basename($file));
                }
            }
        }
        
        // Copy OCR images
        $ocrDir = __DIR__ . '/../../uploads/ocr_images/';
        if (is_dir($ocrDir)) {
            $userOcrImages = glob($ocrDir . '*');
            foreach ($userOcrImages as $file) {
                if (is_file($file)) {
                    copy($file, $filesDir . 'ocr_images/' . basename($file));
                }
            }
        }
    }
    
    /**
     * Add directory to ZIP archive
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void {
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $zip->addEmptyDir($zipPath . basename($file) . '/');
                $this->addDirectoryToZip($zip, $file . '/', $zipPath . basename($file) . '/');
            } else {
                $zip->addFile($file, $zipPath . basename($file));
            }
        }
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory(string $dir): void {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDirectory($file . '/');
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
    
    /**
     * Get export history for user
     */
    public function getExportHistory(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, export_type, filename, file_size, created_at, status
                FROM export_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting export history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean up old export files
     */
    public function cleanupOldExports(int $days = 30): array {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            // Delete old files from filesystem
            $files = glob($this->exportDir . '*');
            $deletedFiles = 0;
            $deletedSize = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < strtotime($cutoffDate)) {
                    $deletedSize += filesize($file);
                    unlink($file);
                    $deletedFiles++;
                }
            }
            
            // Update database records
            $stmt = $this->db->prepare("
                UPDATE export_history 
                SET status = 'deleted' 
                WHERE created_at < ? AND status = 'active'
            ");
            $stmt->execute([$cutoffDate]);
            
            return [
                'deleted_files' => $deletedFiles,
                'deleted_size' => $deletedSize,
                'cutoff_date' => $cutoffDate
            ];
            
        } catch (Exception $e) {
            error_log("Error cleaning up old exports: " . $e->getMessage());
            return [
                'deleted_files' => 0,
                'deleted_size' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
