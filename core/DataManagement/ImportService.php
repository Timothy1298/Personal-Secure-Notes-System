<?php
namespace Core\DataManagement;

use PDO;
use Exception;

class ImportService {
    private $db;
    private $importDir;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->importDir = __DIR__ . '/../../imports/';
        if (!is_dir($this->importDir)) {
            mkdir($this->importDir, 0777, true);
        }
    }
    
    /**
     * Import data from JSON file
     */
    public function importFromJSON(int $userId, string $filepath, array $options = []): array {
        try {
            if (!file_exists($filepath)) {
                throw new Exception('Import file not found');
            }
            
            $jsonData = file_get_contents($filepath);
            $data = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format: ' . json_last_error_msg());
            }
            
            $importResults = [
                'success' => true,
                'imported' => [
                    'notes' => 0,
                    'tasks' => 0,
                    'tags' => 0,
                    'settings' => 0
                ],
                'errors' => [],
                'warnings' => []
            ];
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Import tags first (they might be referenced by notes)
                if (isset($data['data']['tags']) && $options['import_tags'] !== false) {
                    $tagResults = $this->importTags($userId, $data['data']['tags']);
                    $importResults['imported']['tags'] = $tagResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $tagResults['errors']);
                }
                
                // Import notes
                if (isset($data['data']['notes']) && $options['import_notes'] !== false) {
                    $noteResults = $this->importNotes($userId, $data['data']['notes'], $options);
                    $importResults['imported']['notes'] = $noteResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $noteResults['errors']);
                }
                
                // Import tasks
                if (isset($data['data']['tasks']) && $options['import_tasks'] !== false) {
                    $taskResults = $this->importTasks($userId, $data['data']['tasks'], $options);
                    $importResults['imported']['tasks'] = $taskResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $taskResults['errors']);
                }
                
                // Import settings
                if (isset($data['data']['settings']) && $options['import_settings'] !== false) {
                    $settingsResults = $this->importSettings($userId, $data['data']['settings']);
                    $importResults['imported']['settings'] = $settingsResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $settingsResults['errors']);
                }
                
                $this->db->commit();
                
                // Log import
                $this->logImport($userId, 'json', $filepath, $importResults);
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
            return $importResults;
            
        } catch (Exception $e) {
            error_log("Error importing from JSON: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Import data from CSV file
     */
    public function importFromCSV(int $userId, string $filepath, string $dataType, array $options = []): array {
        try {
            if (!file_exists($filepath)) {
                throw new Exception('Import file not found');
            }
            
            $file = fopen($filepath, 'r');
            if (!$file) {
                throw new Exception('Cannot open CSV file');
            }
            
            $headers = fgetcsv($file);
            if (!$headers) {
                throw new Exception('Invalid CSV format - no headers found');
            }
            
            $importResults = [
                'success' => true,
                'imported' => 0,
                'errors' => [],
                'warnings' => []
            ];
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                $rowNumber = 1;
                while (($row = fgetcsv($file)) !== FALSE) {
                    $rowNumber++;
                    $rowData = array_combine($headers, $row);
                    
                    try {
                        switch ($dataType) {
                            case 'notes':
                                $this->importSingleNote($userId, $rowData, $options);
                                break;
                            case 'tasks':
                                $this->importSingleTask($userId, $rowData, $options);
                                break;
                            case 'tags':
                                $this->importSingleTag($userId, $rowData, $options);
                                break;
                            default:
                                throw new Exception("Unsupported data type: {$dataType}");
                        }
                        $importResults['imported']++;
                    } catch (Exception $e) {
                        $importResults['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    }
                }
                
                $this->db->commit();
                fclose($file);
                
                // Log import
                $this->logImport($userId, 'csv', $filepath, $importResults);
                
            } catch (Exception $e) {
                $this->db->rollBack();
                fclose($file);
                throw $e;
            }
            
            return $importResults;
            
        } catch (Exception $e) {
            error_log("Error importing from CSV: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Import data from XML file
     */
    public function importFromXML(int $userId, string $filepath, array $options = []): array {
        try {
            if (!file_exists($filepath)) {
                throw new Exception('Import file not found');
            }
            
            $xml = simplexml_load_file($filepath);
            if ($xml === false) {
                throw new Exception('Invalid XML format');
            }
            
            $importResults = [
                'success' => true,
                'imported' => [
                    'notes' => 0,
                    'tasks' => 0,
                    'tags' => 0
                ],
                'errors' => [],
                'warnings' => []
            ];
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Import tags
                if (isset($xml->tags) && $options['import_tags'] !== false) {
                    $tagResults = $this->importTagsFromXML($userId, $xml->tags);
                    $importResults['imported']['tags'] = $tagResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $tagResults['errors']);
                }
                
                // Import notes
                if (isset($xml->notes) && $options['import_notes'] !== false) {
                    $noteResults = $this->importNotesFromXML($userId, $xml->notes, $options);
                    $importResults['imported']['notes'] = $noteResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $noteResults['errors']);
                }
                
                // Import tasks
                if (isset($xml->tasks) && $options['import_tasks'] !== false) {
                    $taskResults = $this->importTasksFromXML($userId, $xml->tasks, $options);
                    $importResults['imported']['tasks'] = $taskResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $taskResults['errors']);
                }
                
                $this->db->commit();
                
                // Log import
                $this->logImport($userId, 'xml', $filepath, $importResults);
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
            return $importResults;
            
        } catch (Exception $e) {
            error_log("Error importing from XML: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Import from ZIP backup
     */
    public function importFromZIP(int $userId, string $filepath, array $options = []): array {
        try {
            if (!file_exists($filepath)) {
                throw new Exception('Import file not found');
            }
            
            $zip = new \ZipArchive();
            if ($zip->open($filepath) !== TRUE) {
                throw new Exception('Cannot open ZIP file');
            }
            
            $extractDir = $this->importDir . 'temp_' . uniqid() . '/';
            if (!mkdir($extractDir, 0777, true)) {
                throw new Exception('Cannot create extraction directory');
            }
            
            $zip->extractTo($extractDir);
            $zip->close();
            
            $importResults = [
                'success' => true,
                'imported' => [
                    'notes' => 0,
                    'tasks' => 0,
                    'tags' => 0,
                    'files' => 0
                ],
                'errors' => [],
                'warnings' => []
            ];
            
            try {
                // Import JSON data
                $jsonFile = $extractDir . 'data.json';
                if (file_exists($jsonFile)) {
                    $jsonResults = $this->importFromJSON($userId, $jsonFile, $options);
                    if ($jsonResults['success']) {
                        $importResults['imported']['notes'] += $jsonResults['imported']['notes'];
                        $importResults['imported']['tasks'] += $jsonResults['imported']['tasks'];
                        $importResults['imported']['tags'] += $jsonResults['imported']['tags'];
                    }
                    $importResults['errors'] = array_merge($importResults['errors'], $jsonResults['errors'] ?? []);
                }
                
                // Import files
                $filesDir = $extractDir . 'files/';
                if (is_dir($filesDir)) {
                    $fileResults = $this->importFiles($userId, $filesDir);
                    $importResults['imported']['files'] = $fileResults['imported'];
                    $importResults['errors'] = array_merge($importResults['errors'], $fileResults['errors']);
                }
                
                // Clean up
                $this->deleteDirectory($extractDir);
                
            } catch (Exception $e) {
                $this->deleteDirectory($extractDir);
                throw $e;
            }
            
            return $importResults;
            
        } catch (Exception $e) {
            error_log("Error importing from ZIP: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Import tags from array
     */
    private function importTags(int $userId, array $tags): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($tags as $tag) {
            try {
                $this->importSingleTag($userId, $tag);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Tag '{$tag['name']}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import single tag
     */
    private function importSingleTag(int $userId, array $tagData): void {
        $name = trim($tagData['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Tag name is required');
        }
        
        // Check if tag already exists
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
        $stmt->execute([$name, $userId]);
        if ($stmt->fetch()) {
            return; // Tag already exists, skip
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO tags (user_id, name, color, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $name,
            $tagData['color'] ?? '#3b82f6'
        ]);
    }
    
    /**
     * Import notes from array
     */
    private function importNotes(int $userId, array $notes, array $options): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($notes as $note) {
            try {
                $this->importSingleNote($userId, $note, $options);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Note '{$note['title']}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import single note
     */
    private function importSingleNote(int $userId, array $noteData, array $options): void {
        $title = trim($noteData['title'] ?? '');
        if (empty($title)) {
            throw new Exception('Note title is required');
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO notes (user_id, title, content, is_pinned, color, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $createdAt = $noteData['created_at'] ?? date('Y-m-d H:i:s');
        $updatedAt = $noteData['updated_at'] ?? date('Y-m-d H:i:s');
        
        $stmt->execute([
            $userId,
            $title,
            $noteData['content'] ?? '',
            $noteData['is_pinned'] ? 1 : 0,
            $noteData['color'] ?? '#ffffff',
            $createdAt,
            $updatedAt
        ]);
        
        $noteId = $this->db->lastInsertId();
        
        // Import tags if provided
        if (!empty($noteData['tags']) && is_array($noteData['tags'])) {
            $this->linkNoteTags($noteId, $userId, $noteData['tags']);
        }
    }
    
    /**
     * Import tasks from array
     */
    private function importTasks(int $userId, array $tasks, array $options): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($tasks as $task) {
            try {
                $this->importSingleTask($userId, $task, $options);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Task '{$task['title']}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import single task
     */
    private function importSingleTask(int $userId, array $taskData, array $options): void {
        $title = trim($taskData['title'] ?? '');
        if (empty($title)) {
            throw new Exception('Task title is required');
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, title, description, status, priority, due_date, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $createdAt = $taskData['created_at'] ?? date('Y-m-d H:i:s');
        $updatedAt = $taskData['updated_at'] ?? date('Y-m-d H:i:s');
        $dueDate = !empty($taskData['due_date']) ? $taskData['due_date'] : null;
        
        $stmt->execute([
            $userId,
            $title,
            $taskData['description'] ?? '',
            $taskData['status'] ?? 'pending',
            $taskData['priority'] ?? 'medium',
            $dueDate,
            $createdAt,
            $updatedAt
        ]);
    }
    
    /**
     * Import settings from array
     */
    private function importSettings(int $userId, array $settings): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($settings as $setting) {
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO user_settings (user_id, setting_key, setting_value, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)
                ");
                
                $createdAt = $setting['created_at'] ?? date('Y-m-d H:i:s');
                $updatedAt = $setting['updated_at'] ?? date('Y-m-d H:i:s');
                
                $stmt->execute([
                    $userId,
                    $setting['setting_key'],
                    $setting['setting_value'],
                    $createdAt,
                    $updatedAt
                ]);
                
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Setting '{$setting['setting_key']}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import tags from XML
     */
    private function importTagsFromXML(int $userId, $tagsNode): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($tagsNode->tag as $tag) {
            try {
                $tagData = [
                    'name' => (string)$tag->name,
                    'color' => (string)$tag->color
                ];
                $this->importSingleTag($userId, $tagData);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Tag '{$tag->name}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import notes from XML
     */
    private function importNotesFromXML(int $userId, $notesNode, array $options): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($notesNode->note as $note) {
            try {
                $noteData = [
                    'title' => (string)$note->title,
                    'content' => (string)$note->content,
                    'is_pinned' => (string)$note->is_pinned === 'true',
                    'color' => (string)$note->color,
                    'created_at' => (string)$note->created_at,
                    'updated_at' => (string)$note->updated_at
                ];
                
                // Import tags
                if (isset($note->tags)) {
                    $tags = [];
                    foreach ($note->tags->tag as $tag) {
                        $tags[] = (string)$tag;
                    }
                    $noteData['tags'] = $tags;
                }
                
                $this->importSingleNote($userId, $noteData, $options);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Note '{$note->title}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import tasks from XML
     */
    private function importTasksFromXML(int $userId, $tasksNode, array $options): array {
        $results = ['imported' => 0, 'errors' => []];
        
        foreach ($tasksNode->task as $task) {
            try {
                $taskData = [
                    'title' => (string)$task->title,
                    'description' => (string)$task->description,
                    'status' => (string)$task->status,
                    'priority' => (string)$task->priority,
                    'due_date' => (string)$task->due_date,
                    'created_at' => (string)$task->created_at,
                    'updated_at' => (string)$task->updated_at
                ];
                
                $this->importSingleTask($userId, $taskData, $options);
                $results['imported']++;
            } catch (Exception $e) {
                $results['errors'][] = "Task '{$task->title}': " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import files from directory
     */
    private function importFiles(int $userId, string $filesDir): array {
        $results = ['imported' => 0, 'errors' => []];
        
        // Import voice notes
        $voiceNotesDir = $filesDir . 'voice_notes/';
        if (is_dir($voiceNotesDir)) {
            $files = glob($voiceNotesDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $targetDir = __DIR__ . '/../../uploads/voice_notes/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    if (copy($file, $targetDir . basename($file))) {
                        $results['imported']++;
                    } else {
                        $results['errors'][] = "Failed to copy voice note: " . basename($file);
                    }
                }
            }
        }
        
        // Import OCR images
        $ocrDir = $filesDir . 'ocr_images/';
        if (is_dir($ocrDir)) {
            $files = glob($ocrDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $targetDir = __DIR__ . '/../../uploads/ocr_images/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    if (copy($file, $targetDir . basename($file))) {
                        $results['imported']++;
                    } else {
                        $results['errors'][] = "Failed to copy OCR image: " . basename($file);
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Link note with tags
     */
    private function linkNoteTags(int $noteId, int $userId, array $tagNames): void {
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            // Get or create tag
            $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
            $stmt->execute([$tagName, $userId]);
            $tag = $stmt->fetch();
            
            if (!$tag) {
                $stmt = $this->db->prepare("INSERT INTO tags (user_id, name, color, created_at) VALUES (?, ?, '#3b82f6', NOW())");
                $stmt->execute([$userId, $tagName]);
                $tagId = $this->db->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            
            // Link note and tag
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO note_tags (note_id, tag_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$noteId, $tagId]);
        }
    }
    
    /**
     * Log import operation
     */
    private function logImport(int $userId, string $format, string $filepath, array $results): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO import_history (user_id, import_format, filename, file_size, imported_count, error_count, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $importedCount = is_array($results['imported']) 
                ? array_sum($results['imported']) 
                : $results['imported'];
            
            $errorCount = count($results['errors'] ?? []);
            
            $stmt->execute([
                $userId,
                $format,
                basename($filepath),
                file_exists($filepath) ? filesize($filepath) : 0,
                $importedCount,
                $errorCount
            ]);
        } catch (Exception $e) {
            error_log("Error logging import: " . $e->getMessage());
        }
    }
    
    /**
     * Get import history for user
     */
    public function getImportHistory(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, import_format, filename, file_size, imported_count, error_count, created_at, status
                FROM import_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting import history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate import file
     */
    public function validateImportFile(string $filepath, string $format): array {
        try {
            if (!file_exists($filepath)) {
                return ['valid' => false, 'error' => 'File not found'];
            }
            
            switch ($format) {
                case 'json':
                    $json = file_get_contents($filepath);
                    json_decode($json);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return ['valid' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()];
                    }
                    break;
                    
                case 'xml':
                    $xml = simplexml_load_file($filepath);
                    if ($xml === false) {
                        return ['valid' => false, 'error' => 'Invalid XML format'];
                    }
                    break;
                    
                case 'csv':
                    $file = fopen($filepath, 'r');
                    if (!$file) {
                        return ['valid' => false, 'error' => 'Cannot open CSV file'];
                    }
                    $headers = fgetcsv($file);
                    fclose($file);
                    if (!$headers) {
                        return ['valid' => false, 'error' => 'Invalid CSV format - no headers'];
                    }
                    break;
                    
                case 'zip':
                    $zip = new \ZipArchive();
                    if ($zip->open($filepath) !== TRUE) {
                        return ['valid' => false, 'error' => 'Invalid ZIP file'];
                    }
                    $zip->close();
                    break;
                    
                default:
                    return ['valid' => false, 'error' => 'Unsupported format'];
            }
            
            return [
                'valid' => true,
                'size' => filesize($filepath),
                'format' => $format
            ];
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
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
}
