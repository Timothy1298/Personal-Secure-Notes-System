<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\ImportService;
use Core\CSRF;
use Exception;
use PDO;

class ImportController {
    private $db;
    private $importService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->importService = new ImportService($this->db);
    }

    /**
     * Show import page
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        include __DIR__ . '/../Views/import.php';
    }

    /**
     * Handle file upload and import
     */
    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, "Invalid request method");
        }

        if (!Session::get('user_id')) {
            $this->jsonResponse(false, "User not authenticated");
        }

        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->jsonResponse(false, "Invalid CSRF token");
        }

        $userId = Session::get('user_id');

        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, "No file uploaded or upload error");
        }

        $file = $_FILES['import_file'];
        $format = $_POST['format'] ?? 'json';
        $importType = $_POST['import_type'] ?? 'auto';
        $mergeMode = $_POST['merge_mode'] ?? 'skip';

        // Validate file
        $validation = $this->importService->validateImportFile($file['tmp_name'], $format);
        if (!$validation['valid']) {
            $this->jsonResponse(false, "File validation failed: " . implode(', ', $validation['errors']));
        }

        // Import data
        $options = [
            'import_type' => $importType,
            'merge_mode' => $mergeMode
        ];

        $result = $this->importService->importData($file['tmp_name'], $format, $userId, $options);
        
        if ($result['success']) {
            // Log import history
            $this->logImportHistory($userId, $file['name'], $format, $result);
            $this->jsonResponse(true, $result['message'], $result);
        } else {
            $this->jsonResponse(false, $result['message']);
        }
    }

    /**
     * Validate import file
     */
    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, "Invalid request method");
        }

        if (!Session::get('user_id')) {
            $this->jsonResponse(false, "User not authenticated");
        }

        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->jsonResponse(false, "Invalid CSRF token");
        }

        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, "No file uploaded or upload error");
        }

        $file = $_FILES['import_file'];
        $format = $_POST['format'] ?? 'json';

        // Validate file
        $validation = $this->importService->validateImportFile($file['tmp_name'], $format);
        
        $this->jsonResponse($validation['valid'], $validation['valid'] ? "File is valid" : "File validation failed", [
            'errors' => $validation['errors'],
            'item_count' => $validation['item_count']
        ]);
    }

    /**
     * Get import templates
     */
    public function getTemplates() {
        if (!Session::get('user_id')) {
            $this->jsonResponse(false, "User not authenticated");
        }

        $templates = [
            'json' => [
                'name' => 'JSON Template',
                'description' => 'Import notes and tasks from JSON format',
                'example' => [
                    [
                        'title' => 'Sample Note',
                        'content' => 'This is a sample note content.',
                        'priority' => 'medium',
                        'category' => 'general',
                        'tags' => 'sample, import'
                    ],
                    [
                        'title' => 'Sample Task',
                        'description' => 'This is a sample task description.',
                        'status' => 'pending',
                        'priority' => 'high',
                        'category' => 'work',
                        'due_date' => '2024-12-31',
                        'tags' => 'urgent, work'
                    ]
                ]
            ],
            'csv' => [
                'name' => 'CSV Template',
                'description' => 'Import data from CSV format',
                'headers' => ['title', 'content', 'priority', 'category', 'tags', 'type'],
                'example' => [
                    ['Sample Note', 'This is a sample note content.', 'medium', 'general', 'sample, import', 'note'],
                    ['Sample Task', 'This is a sample task description.', 'high', 'work', 'urgent, work', 'task']
                ]
            ],
            'txt' => [
                'name' => 'Text Template',
                'description' => 'Import from structured text format',
                'example' => "# Sample Note\nPriority: medium\nCategory: general\nTags: sample, import\n\nThis is a sample note content.\n\n# Sample Task\nStatus: pending\nPriority: high\nCategory: work\nDue Date: 2024-12-31\nTags: urgent, work\n\nThis is a sample task description."
            ]
        ];

        $this->jsonResponse(true, "Templates retrieved", $templates);
    }

    /**
     * Download import template
     */
    public function downloadTemplate() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $format = $_GET['format'] ?? 'json';
        $userId = Session::get('user_id');

        switch ($format) {
            case 'json':
                $this->downloadJsonTemplate();
                break;
            case 'csv':
                $this->downloadCsvTemplate();
                break;
            case 'txt':
                $this->downloadTextTemplate();
                break;
            default:
                header("HTTP/1.0 400 Bad Request");
                echo "Invalid format";
                exit;
        }
    }

    private function downloadJsonTemplate() {
        $template = [
            [
                'title' => 'Sample Note',
                'content' => 'This is a sample note content. You can include multiple paragraphs and formatting.',
                'priority' => 'medium',
                'category' => 'general',
                'tags' => 'sample, import, notes'
            ],
            [
                'title' => 'Sample Task',
                'description' => 'This is a sample task description. Include all relevant details here.',
                'status' => 'pending',
                'priority' => 'high',
                'category' => 'work',
                'due_date' => '2024-12-31',
                'tags' => 'urgent, work, deadline'
            ]
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="import_template.json"');
        echo json_encode($template, JSON_PRETTY_PRINT);
        exit;
    }

    private function downloadCsvTemplate() {
        $template = [
            ['title', 'content', 'priority', 'category', 'tags', 'type'],
            ['Sample Note', 'This is a sample note content.', 'medium', 'general', 'sample, import', 'note'],
            ['Sample Task', 'This is a sample task description.', 'high', 'work', 'urgent, work', 'task']
        ];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="import_template.csv"');
        
        $output = fopen('php://output', 'w');
        foreach ($template as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    private function downloadTextTemplate() {
        $template = "# Sample Note
Priority: medium
Category: general
Tags: sample, import, notes

This is a sample note content. You can include multiple paragraphs and formatting.

# Sample Task
Status: pending
Priority: high
Category: work
Due Date: 2024-12-31
Tags: urgent, work, deadline

This is a sample task description. Include all relevant details here.";

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="import_template.txt"');
        echo $template;
        exit;
    }

    /**
     * Get import history
     */
    public function getImportHistory() {
        if (!Session::get('user_id')) {
            $this->jsonResponse(false, "User not authenticated");
        }

        $userId = Session::get('user_id');
        $history = $this->getImportHistoryFromDB($userId);
        
        $this->jsonResponse(true, "Import history retrieved", ['history' => $history]);
    }

    /**
     * Log import history to database
     */
    private function logImportHistory(int $userId, string $filename, string $format, array $result) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO import_history (user_id, filename, format, status, items_imported, items_skipped, items_failed, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $status = $result['success'] ? 'success' : 'failed';
            $itemsImported = $result['summary']['notes_imported'] + $result['summary']['tasks_imported'] + $result['summary']['tags_imported'] ?? 0;
            $itemsSkipped = $result['summary']['notes_skipped'] + $result['summary']['tasks_skipped'] + $result['summary']['tags_skipped'] ?? 0;
            $itemsFailed = 0; // Could be calculated from errors
            
            $stmt->execute([$userId, $filename, $format, $status, $itemsImported, $itemsSkipped, $itemsFailed]);
        } catch (Exception $e) {
            error_log("Failed to log import history: " . $e->getMessage());
        }
    }

    /**
     * Get import history from database
     */
    private function getImportHistoryFromDB(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM import_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get import history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

