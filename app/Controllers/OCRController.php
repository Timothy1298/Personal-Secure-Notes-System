<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\OCRService;
use Core\CSRF;
use Exception;
use PDO;

class OCRController {
    private $db;
    private $ocrService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->ocrService = new OCRService($this->db);
    }
    
    /**
     * Show OCR interface
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        
        $userId = Session::get('user_id');
        $ocrResults = $this->ocrService->getUserOCRResults($userId);
        
        include __DIR__ . '/../Views/ocr.php';
    }
    
    /**
     * Process image for OCR
     */
    public function process() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        if (!CSRF::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
            return;
        }
        
        $userId = Session::get('user_id');
        
        try {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No image file uploaded');
            }
            
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $filename = $_FILES['image']['name'];
            
            $result = $this->ocrService->processImage($userId, $imageData, $filename);
            
            if ($result['success']) {
                // Auto-convert to note if requested
                if (isset($_POST['auto_convert']) && $_POST['auto_convert'] === 'true') {
                    $noteResult = $this->ocrService->convertToTextNote($result['ocr_id'], $userId);
                    if ($noteResult['success']) {
                        $result['note_id'] = $noteResult['note_id'];
                    }
                }
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("OCR processing error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get user's OCR results
     */
    public function results() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        $userId = Session::get('user_id');
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $results = $this->ocrService->getUserOCRResults($userId, $limit, $offset);
        
        echo json_encode([
            'success' => true,
            'results' => $results
        ]);
    }
    
    /**
     * Convert OCR result to text note
     */
    public function convert($ocrId) {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        if (!CSRF::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
            return;
        }
        
        $userId = Session::get('user_id');
        $title = $_POST['title'] ?? null;
        
        try {
            $result = $this->ocrService->convertToTextNote($ocrId, $userId, $title);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("OCR conversion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete OCR result
     */
    public function delete($ocrId) {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        if (!CSRF::validate()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
            return;
        }
        
        $userId = Session::get('user_id');
        
        try {
            $result = $this->ocrService->deleteOCRResult($ocrId, $userId);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("OCR deletion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get OCR result image
     */
    public function getImage($ocrId) {
        if (!Session::get('user_id')) {
            http_response_code(401);
            return;
        }
        
        $userId = Session::get('user_id');
        
        try {
            $stmt = $this->db->prepare("
                SELECT file_path, original_filename 
                FROM ocr_results 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$ocrId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !file_exists($result['file_path'])) {
                http_response_code(404);
                return;
            }
            
            $filename = $result['original_filename'];
            $filePath = $result['file_path'];
            $mimeType = $this->getMimeType($filePath);
            
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            
        } catch (Exception $e) {
            error_log("OCR image retrieval error: " . $e->getMessage());
            http_response_code(500);
        }
    }
    
    /**
     * Get MIME type for image file
     */
    private function getMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
