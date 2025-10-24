<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\DataManagement\ExportService;
use Core\DataManagement\ImportService;
use Core\DataManagement\MigrationService;
use Exception;

class DataManagementController {
    private $db;
    private $exportService;
    private $importService;
    private $migrationService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->exportService = new ExportService($this->db);
        $this->importService = new ImportService($this->db);
        $this->migrationService = new MigrationService($this->db);
    }
    
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        
        $userId = Session::get('user_id');
        
        // Get export history
        $exportHistory = $this->exportService->getExportHistory($userId);
        
        // Get import history
        $importHistory = $this->importService->getImportHistory($userId);
        
        // Get migration status
        $migrationStatus = $this->migrationService->getMigrationStatus();
        
        include __DIR__ . '/../Views/data_management.php';
    }
    
    public function export() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $format = $_POST['format'] ?? 'json';
        $options = $_POST['options'] ?? [];
        
        try {
            switch ($format) {
                case 'json':
                    $result = $this->exportService->exportUserData($userId, $options);
                    break;
                case 'csv':
                    $dataType = $_POST['data_type'] ?? 'notes';
                    $result = $this->exportService->exportToCSV($userId, $dataType, $options);
                    break;
                case 'xml':
                    $result = $this->exportService->exportToXML($userId, $options);
                    break;
                case 'zip':
                    $result = $this->exportService->createCompleteBackup($userId, $options);
                    break;
                default:
                    throw new Exception('Unsupported export format');
            }
            
            if ($result['success']) {
                // Log export
                $this->logExport($userId, $format, $result['filename'], $result['size']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Export completed successfully',
                    'data' => $result
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['error'] ?? 'Export failed'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function import() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
            exit;
        }
        
        $file = $_FILES['import_file'];
        $format = $_POST['format'] ?? 'json';
        $options = $_POST['options'] ?? [];
        
        try {
            // Validate file
            $validation = $this->importService->validateImportFile($file['tmp_name'], $format);
            if (!$validation['valid']) {
                throw new Exception($validation['error']);
            }
            
            // Move uploaded file to import directory
            $importDir = __DIR__ . '/../../imports/';
            if (!is_dir($importDir)) {
                mkdir($importDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . $file['name'];
            $filepath = $importDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Import data
            switch ($format) {
                case 'json':
                    $result = $this->importService->importFromJSON($userId, $filepath, $options);
                    break;
                case 'csv':
                    $dataType = $_POST['data_type'] ?? 'notes';
                    $result = $this->importService->importFromCSV($userId, $filepath, $dataType, $options);
                    break;
                case 'xml':
                    $result = $this->importService->importFromXML($userId, $filepath, $options);
                    break;
                case 'zip':
                    $result = $this->importService->importFromZIP($userId, $filepath, $options);
                    break;
                default:
                    throw new Exception('Unsupported import format');
            }
            
            // Clean up uploaded file
            unlink($filepath);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Import completed successfully',
                    'data' => $result
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['error'] ?? 'Import failed'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function validateFile() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit;
        }
        
        $file = $_FILES['import_file'];
        $format = $_POST['format'] ?? 'json';
        
        try {
            $validation = $this->importService->validateImportFile($file['tmp_name'], $format);
            echo json_encode($validation);
        } catch (Exception $e) {
            echo json_encode([
                'valid' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function runMigrations() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $result = $this->migrationService->runMigrations();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function migrationStatus() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $status = $this->migrationService->getMigrationStatus();
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function createMigration() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Migration name is required']);
            exit;
        }
        
        try {
            $result = $this->migrationService->createMigration($name, $description);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function validateMigration() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $migrationFile = $_POST['migration_file'] ?? '';
        
        if (empty($migrationFile)) {
            echo json_encode(['success' => false, 'message' => 'Migration file is required']);
            exit;
        }
        
        try {
            $result = $this->migrationService->validateMigration($migrationFile);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function rollbackMigration() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $migrationFile = $_POST['migration_file'] ?? '';
        
        if (empty($migrationFile)) {
            echo json_encode(['success' => false, 'message' => 'Migration file is required']);
            exit;
        }
        
        try {
            $result = $this->migrationService->rollbackMigration($migrationFile);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function backupDatabase() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $result = $this->migrationService->backupDatabase();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function getExportHistory() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        
        try {
            $history = $this->exportService->getExportHistory($userId);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function getImportHistory() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        
        try {
            $history = $this->importService->getImportHistory($userId);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function getMigrationHistory() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        try {
            $history = $this->migrationService->getMigrationHistory();
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function cleanupExports() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $days = $_POST['days'] ?? 30;
        
        try {
            $result = $this->exportService->cleanupOldExports($days);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function downloadExport() {
        if (!Session::get('user_id')) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }
        
        $userId = Session::get('user_id');
        $filename = $_GET['filename'] ?? '';
        
        if (empty($filename)) {
            header("HTTP/1.1 400 Bad Request");
            exit;
        }
        
        try {
            // Verify file belongs to user
            $stmt = $this->db->prepare("
                SELECT filename, file_size 
                FROM export_history 
                WHERE user_id = ? AND filename = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $filename]);
            $export = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$export) {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
            
            $filepath = __DIR__ . '/../../exports/' . $filename;
            
            if (!file_exists($filepath)) {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
            
            // Set headers for download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            readfile($filepath);
            exit;
            
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            exit;
        }
    }
    
    /**
     * Log export operation
     */
    private function logExport(int $userId, string $type, string $filename, int $size): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO export_history (user_id, export_type, filename, file_size, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $type, $filename, $size]);
        } catch (Exception $e) {
            error_log("Error logging export: " . $e->getMessage());
        }
    }
}
