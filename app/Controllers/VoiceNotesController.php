<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\VoiceNotes;
use Core\CSRF;
use Exception;
use PDO;

class VoiceNotesController {
    private $db;
    private $voiceNotes;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->voiceNotes = new VoiceNotes($this->db);
    }
    
    /**
     * Show voice notes interface
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        
        $userId = Session::get('user_id');
        $voiceNotes = $this->voiceNotes->getUserVoiceNotes($userId);
        $stats = $this->voiceNotes->getVoiceNoteStats($userId);
        
        include __DIR__ . '/../Views/voice_notes.php';
    }
    
    /**
     * Save voice note
     */
    public function save() {
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
            if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No audio file uploaded');
            }
            
            $audioData = file_get_contents($_FILES['audio']['tmp_name']);
            $filename = $_FILES['audio']['name'];
            $duration = $_POST['duration'] ?? null;
            
            $result = $this->voiceNotes->saveVoiceNote($userId, $audioData, $filename, $duration);
            
            if ($result['success']) {
                // Auto-transcribe if requested
                if (isset($_POST['auto_transcribe']) && $_POST['auto_transcribe'] === 'true') {
                    $this->transcribeVoiceNote($result['voice_note_id']);
                }
                
                // Auto-convert to note if requested
                if (isset($_POST['convert_to_note']) && $_POST['convert_to_note'] === 'true') {
                    $this->convertToNote($result['voice_note_id']);
                }
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("Voice note save error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get user's voice notes list
     */
    public function list() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        $userId = Session::get('user_id');
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $voiceNotes = $this->voiceNotes->getUserVoiceNotes($userId, $limit, $offset);
        
        echo json_encode([
            'success' => true,
            'voice_notes' => $voiceNotes
        ]);
    }
    
    /**
     * Transcribe voice note
     */
    public function transcribe($voiceNoteId) {
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
            $result = $this->transcribeVoiceNote($voiceNoteId);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Voice note transcription error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Convert voice note to text note
     */
    public function convert($voiceNoteId) {
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
            $result = $this->voiceNotes->convertToTextNote($voiceNoteId, $userId, $title);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Voice note conversion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete voice note
     */
    public function delete($voiceNoteId) {
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
            $result = $this->voiceNotes->deleteVoiceNote($voiceNoteId, $userId);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("Voice note deletion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get voice note file
     */
    public function getFile($voiceNoteId) {
        if (!Session::get('user_id')) {
            http_response_code(401);
            return;
        }
        
        $userId = Session::get('user_id');
        $voiceNote = $this->voiceNotes->getVoiceNote($voiceNoteId, $userId);
        
        if (!$voiceNote || !file_exists($voiceNote['file_path'])) {
            http_response_code(404);
            return;
        }
        
        $filename = $voiceNote['original_filename'];
        $filePath = $voiceNote['file_path'];
        $mimeType = $this->getMimeType($filePath);
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
    }
    
    /**
     * Private method to transcribe voice note
     */
    private function transcribeVoiceNote($voiceNoteId) {
        // This is a placeholder for actual transcription service
        // In a real implementation, you would integrate with services like:
        // - Google Speech-to-Text
        // - Azure Speech Services
        // - AWS Transcribe
        // - OpenAI Whisper
        
        $userId = Session::get('user_id');
        $voiceNote = $this->voiceNotes->getVoiceNote($voiceNoteId, $userId);
        
        if (!$voiceNote) {
            return ['success' => false, 'error' => 'Voice note not found'];
        }
        
        // Simulate transcription (replace with actual service)
        $transcription = "This is a simulated transcription of the voice note. In a real implementation, this would be replaced with actual speech-to-text conversion.";
        
        $result = $this->voiceNotes->updateTranscription($voiceNoteId, $userId, $transcription);
        
        return [
            'success' => $result,
            'transcription' => $transcription
        ];
    }
    
    /**
     * Private method to convert to note
     */
    private function convertToNote($voiceNoteId) {
        $userId = Session::get('user_id');
        $title = 'Voice Note - ' . date('Y-m-d H:i:s');
        
        return $this->voiceNotes->convertToTextNote($voiceNoteId, $userId, $title);
    }
    
    /**
     * Get MIME type for file
     */
    private function getMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'webm' => 'audio/webm'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
