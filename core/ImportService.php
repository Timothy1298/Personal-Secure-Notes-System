<?php
namespace Core;

use PDO;
use Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class ImportService {
    private $db;
    private $notesModel;
    private $tasksModel;
    private $tagsModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->notesModel = new \App\Models\NotesModel($db);
        $this->tasksModel = new \App\Models\TasksModel($db);
        $this->tagsModel = new \App\Models\TagsModel($db);
    }

    /**
     * Import data from various formats
     * @param string $filePath Path to the file to import
     * @param string $format Format of the file (json, csv, txt, docx)
     * @param int $userId User ID to import data for
     * @param array $options Import options
     * @return array Result of the import operation
     */
    public function importData(string $filePath, string $format, int $userId, array $options = []): array {
        try {
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'File not found'];
            }

            $data = $this->parseFile($filePath, $format);
            if (!$data) {
                return ['success' => false, 'message' => 'Failed to parse file'];
            }

            $result = $this->processImportData($data, $userId, $options);
            return $result;

        } catch (Exception $e) {
            error_log("Import error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
        }
    }

    /**
     * Parse file based on format
     */
    private function parseFile(string $filePath, string $format): array|false {
        switch (strtolower($format)) {
            case 'json':
                return $this->parseJsonFile($filePath);
            case 'csv':
                return $this->parseCsvFile($filePath);
            case 'txt':
                return $this->parseTextFile($filePath);
            case 'docx':
                return $this->parseDocxFile($filePath);
            default:
                return false;
        }
    }

    /**
     * Parse JSON file
     */
    private function parseJsonFile(string $filePath): array|false {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $data;
    }

    /**
     * Parse CSV file
     */
    private function parseCsvFile(string $filePath): array|false {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return false;
        }

        $data = [];
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return false;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $data;
    }

    /**
     * Parse text file
     */
    private function parseTextFile(string $filePath): array|false {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Try to parse as structured text
        $lines = explode("\n", $content);
        $data = [];
        $currentItem = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                if (!empty($currentItem)) {
                    $data[] = $currentItem;
                    $currentItem = [];
                }
                continue;
            }

            if (preg_match('/^# (.+)$/', $line, $matches)) {
                // Title
                if (!empty($currentItem)) {
                    $data[] = $currentItem;
                }
                $currentItem = ['title' => $matches[1]];
            } elseif (preg_match('/^(.+):\s*(.+)$/', $line, $matches)) {
                // Key-value pair
                $key = strtolower(trim($matches[1]));
                $value = trim($matches[2]);
                $currentItem[$key] = $value;
            } else {
                // Content
                if (isset($currentItem['content'])) {
                    $currentItem['content'] .= "\n" . $line;
                } else {
                    $currentItem['content'] = $line;
                }
            }
        }

        if (!empty($currentItem)) {
            $data[] = $currentItem;
        }

        return $data;
    }

    /**
     * Parse DOCX file
     */
    private function parseDocxFile(string $filePath): array|false {
        try {
            $phpWord = IOFactory::load($filePath);
            $data = [];

            foreach ($phpWord->getSections() as $section) {
                $elements = $section->getElements();
                $currentItem = [];
                $content = '';

                foreach ($elements as $element) {
                    if (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        if (preg_match('/^(\d+)\.\s*(.+)$/', $text, $matches)) {
                            // New item
                            if (!empty($currentItem)) {
                                $currentItem['content'] = trim($content);
                                $data[] = $currentItem;
                            }
                            $currentItem = ['title' => $matches[2]];
                            $content = '';
                        } else {
                            $content .= $text . "\n";
                        }
                    }
                }

                if (!empty($currentItem)) {
                    $currentItem['content'] = trim($content);
                    $data[] = $currentItem;
                }
            }

            return $data;

        } catch (Exception $e) {
            error_log("DOCX parsing error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process imported data and save to database
     */
    private function processImportData(array $data, int $userId, array $options): array {
        $imported = ['notes' => 0, 'tasks' => 0, 'tags' => 0];
        $errors = [];

        $mergeMode = $options['merge_mode'] ?? 'skip'; // skip, overwrite, rename
        $importType = $options['import_type'] ?? 'auto'; // auto, notes, tasks

        foreach ($data as $item) {
            try {
                $itemType = $this->determineItemType($item, $importType);
                
                if ($itemType === 'note') {
                    $result = $this->importNote($item, $userId, $mergeMode);
                    if ($result['success']) {
                        $imported['notes']++;
                    } else {
                        $errors[] = "Note '{$item['title']}': " . $result['message'];
                    }
                } elseif ($itemType === 'task') {
                    $result = $this->importTask($item, $userId, $mergeMode);
                    if ($result['success']) {
                        $imported['tasks']++;
                    } else {
                        $errors[] = "Task '{$item['title']}': " . $result['message'];
                    }
                }

                // Import tags if present
                if (!empty($item['tags'])) {
                    $this->importTags($item['tags'], $userId);
                    $imported['tags']++;
                }

            } catch (Exception $e) {
                $errors[] = "Error processing item: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'message' => "Import completed. Notes: {$imported['notes']}, Tasks: {$imported['tasks']}, Tags: {$imported['tags']}"
        ];
    }

    /**
     * Determine if item is a note or task
     */
    private function determineItemType(array $item, string $importType): string {
        if ($importType === 'notes') return 'note';
        if ($importType === 'tasks') return 'task';

        // Auto-detect based on content
        $title = strtolower($item['title'] ?? '');
        $content = strtolower($item['content'] ?? $item['description'] ?? '');

        // Check for task indicators
        $taskIndicators = ['todo', 'task', 'due', 'deadline', 'complete', 'finish', 'priority'];
        foreach ($taskIndicators as $indicator) {
            if (strpos($title, $indicator) !== false || strpos($content, $indicator) !== false) {
                return 'task';
            }
        }

        // Check for status field
        if (isset($item['status']) && in_array(strtolower($item['status']), ['pending', 'in_progress', 'completed', 'cancelled'])) {
            return 'task';
        }

        // Check for due date
        if (isset($item['due_date']) || isset($item['due'])) {
            return 'task';
        }

        // Default to note
        return 'note';
    }

    /**
     * Import a note
     */
    private function importNote(array $item, int $userId, string $mergeMode): array {
        $title = $item['title'] ?? 'Imported Note';
        $content = $item['content'] ?? '';
        $priority = $item['priority'] ?? 'medium';
        $category = $item['category'] ?? 'imported';

        // Check for existing note
        $existing = $this->notesModel->getNoteByTitle($userId, $title);
        if ($existing && $mergeMode === 'skip') {
            return ['success' => false, 'message' => 'Note already exists, skipping'];
        }

        if ($existing && $mergeMode === 'rename') {
            $title = $title . ' (Imported)';
        }

        $noteId = $this->notesModel->createNote($userId, $title, $content, $priority, $category);
        if ($noteId) {
            return ['success' => true, 'id' => $noteId];
        } else {
            return ['success' => false, 'message' => 'Failed to create note'];
        }
    }

    /**
     * Import a task
     */
    private function importTask(array $item, int $userId, string $mergeMode): array {
        $title = $item['title'] ?? 'Imported Task';
        $description = $item['description'] ?? $item['content'] ?? '';
        $status = $item['status'] ?? 'pending';
        $priority = $item['priority'] ?? 'medium';
        $category = $item['category'] ?? 'imported';
        $dueDate = $item['due_date'] ?? $item['due'] ?? null;

        // Check for existing task
        $existing = $this->tasksModel->getTaskByTitle($userId, $title);
        if ($existing && $mergeMode === 'skip') {
            return ['success' => false, 'message' => 'Task already exists, skipping'];
        }

        if ($existing && $mergeMode === 'rename') {
            $title = $title . ' (Imported)';
        }

        $taskId = $this->tasksModel->createTask($userId, $title, $description, $status, $priority, $category, $dueDate);
        if ($taskId) {
            return ['success' => true, 'id' => $taskId];
        } else {
            return ['success' => false, 'message' => 'Failed to create task'];
        }
    }

    /**
     * Import tags
     */
    private function importTags(string $tagsString, int $userId): void {
        $tags = array_map('trim', explode(',', $tagsString));
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $this->tagsModel->createTag($userId, $tag);
            }
        }
    }

    /**
     * Validate import file
     */
    public function validateImportFile(string $filePath, string $format): array {
        $errors = [];

        if (!file_exists($filePath)) {
            $errors[] = 'File does not exist';
            return ['valid' => false, 'errors' => $errors];
        }

        $fileSize = filesize($filePath);
        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            $errors[] = 'File size exceeds 10MB limit';
        }

        if ($fileSize === 0) {
            $errors[] = 'File is empty';
        }

        // Test parsing
        $data = $this->parseFile($filePath, $format);
        if (!$data) {
            $errors[] = 'File format is invalid or corrupted';
        } elseif (is_array($data) && count($data) === 0) {
            $errors[] = 'No data found in file';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'item_count' => is_array($data) ? count($data) : 0
        ];
    }
}

