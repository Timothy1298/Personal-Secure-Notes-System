<?php
namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use Core\GoogleDriveService;
use Core\DropboxService;
use PDO;
use Exception;

class CloudController {
    private $db;
    private $googleDriveService;
    private $dropboxService;

    public function __construct() {
        $this->db = \Core\Database::getInstance();
        $this->googleDriveService = new GoogleDriveService($this->db);
        $this->dropboxService = new DropboxService($this->db);
    }

    private function handleAjaxResponse($success, $message, $data = [], $httpCode = 200) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
        exit;
    }

    /**
     * Show cloud integration settings page
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $googleDriveConnected = $this->googleDriveService->isConnected($userId);
        $dropboxConnected = $this->dropboxService->isConnected($userId);

        $page_title = "Cloud Integration";
        include __DIR__ . '/../Views/cloud_integration.php';
    }

    /**
     * Initiate Google Drive connection
     */
    public function connectGoogleDrive() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        
        try {
            $authUrl = $this->googleDriveService->getAuthorizationUrl($userId);
            $this->handleAjaxResponse(true, 'Authorization URL generated', ['auth_url' => $authUrl]);
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to generate authorization URL: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Handle Google Drive OAuth callback
     */
    public function googleDriveCallback() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code) || empty($state)) {
            $_SESSION['errors'] = ["Google Drive authorization failed. Missing parameters."];
            header("Location: /cloud-integration");
            exit;
        }

        try {
            $this->googleDriveService->exchangeCodeForToken($code, $state);
            $_SESSION['success'] = "Google Drive connected successfully!";
        } catch (Exception $e) {
            $_SESSION['errors'] = ["Google Drive connection failed: " . $e->getMessage()];
        }

        header("Location: /cloud-integration");
        exit;
    }

    /**
     * Disconnect Google Drive
     */
    public function disconnectGoogleDrive() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        
        try {
            $success = $this->googleDriveService->disconnect($userId);
            if ($success) {
                $this->handleAjaxResponse(true, 'Google Drive disconnected successfully');
            } else {
                $this->handleAjaxResponse(false, 'Failed to disconnect Google Drive', [], 500);
            }
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to disconnect: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Upload backup to Google Drive
     */
    public function uploadBackupToGoogleDrive() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        $backupId = $_POST['backup_id'] ?? '';

        if (empty($backupId)) {
            $this->handleAjaxResponse(false, 'Backup ID is required', [], 400);
        }

        try {
            // Get backup file path from database
            $stmt = $this->db->prepare("
                SELECT file_path, backup_name, file_size 
                FROM backup_history 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$backupId, $userId]);
            $backup = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$backup) {
                $this->handleAjaxResponse(false, 'Backup not found', [], 404);
            }

            if (!file_exists($backup['file_path'])) {
                $this->handleAjaxResponse(false, 'Backup file not found on disk', [], 404);
            }

            // Upload to Google Drive
            $result = $this->googleDriveService->uploadFile(
                $userId,
                $backup['file_path'],
                $backup['backup_name'],
                'application/zip'
            );

            // Update backup record with Google Drive file ID
            $updateStmt = $this->db->prepare("
                UPDATE backup_history 
                SET google_drive_file_id = ?, google_drive_uploaded_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->execute([$result['id'], $backupId]);

            $this->handleAjaxResponse(true, 'Backup uploaded to Google Drive successfully', [
                'file_id' => $result['id'],
                'file_name' => $result['name']
            ]);

        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Upload failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Download backup from Google Drive
     */
    public function downloadBackupFromGoogleDrive() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $fileId = $_GET['file_id'] ?? '';

        if (empty($fileId)) {
            $_SESSION['errors'] = ["File ID is required"];
            header("Location: /cloud-integration");
            exit;
        }

        try {
            $result = $this->googleDriveService->downloadFile($userId, $fileId);
            
            // Set appropriate headers for file download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.zip"');
            header('Content-Length: ' . strlen($result['content']));
            
            echo $result['content'];
            exit;

        } catch (Exception $e) {
            $_SESSION['errors'] = ["Download failed: " . $e->getMessage()];
            header("Location: /cloud-integration");
            exit;
        }
    }

    /**
     * List Google Drive files
     */
    public function listGoogleDriveFiles() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        $userId = Session::get('user_id');
        
        try {
            $files = $this->googleDriveService->listFiles($userId);
            $this->handleAjaxResponse(true, 'Files retrieved successfully', ['files' => $files]);
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to list files: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Delete file from Google Drive
     */
    public function deleteGoogleDriveFile() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        $fileId = $_POST['file_id'] ?? '';

        if (empty($fileId)) {
            $this->handleAjaxResponse(false, 'File ID is required', [], 400);
        }

        try {
            $success = $this->googleDriveService->deleteFile($userId, $fileId);
            if ($success) {
                $this->handleAjaxResponse(true, 'File deleted successfully');
            } else {
                $this->handleAjaxResponse(false, 'Failed to delete file', [], 500);
            }
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Delete failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Initiate Dropbox connection
     */
    public function connectDropbox() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        
        try {
            $authUrl = $this->dropboxService->getAuthorizationUrl($userId);
            $this->handleAjaxResponse(true, 'Authorization URL generated', ['auth_url' => $authUrl]);
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to generate authorization URL: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Handle Dropbox OAuth callback
     */
    public function dropboxCallback() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code) || empty($state)) {
            $_SESSION['errors'] = ["Dropbox authorization failed. Missing parameters."];
            header("Location: /cloud-integration");
            exit;
        }

        try {
            $this->dropboxService->exchangeCodeForToken($code, $state);
            $_SESSION['success'] = "Dropbox connected successfully!";
        } catch (Exception $e) {
            $_SESSION['errors'] = ["Dropbox connection failed: " . $e->getMessage()];
        }

        header("Location: /cloud-integration");
        exit;
    }

    /**
     * Disconnect Dropbox
     */
    public function disconnectDropbox() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        
        try {
            $success = $this->dropboxService->disconnect($userId);
            if ($success) {
                $this->handleAjaxResponse(true, 'Dropbox disconnected successfully');
            } else {
                $this->handleAjaxResponse(false, 'Failed to disconnect Dropbox', [], 500);
            }
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to disconnect: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Upload backup to Dropbox
     */
    public function uploadBackupToDropbox() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        $backupId = $_POST['backup_id'] ?? '';

        if (empty($backupId)) {
            $this->handleAjaxResponse(false, 'Backup ID is required', [], 400);
        }

        try {
            // Get backup file path from database
            $stmt = $this->db->prepare("
                SELECT file_path, backup_name, file_size 
                FROM backup_history 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$backupId, $userId]);
            $backup = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$backup) {
                $this->handleAjaxResponse(false, 'Backup not found', [], 404);
            }

            if (!file_exists($backup['file_path'])) {
                $this->handleAjaxResponse(false, 'Backup file not found on disk', [], 404);
            }

            // Upload to Dropbox
            $result = $this->dropboxService->uploadFile(
                $userId,
                $backup['file_path'],
                $backup['backup_name'],
                'application/zip'
            );

            // Update backup record with Dropbox file path
            $updateStmt = $this->db->prepare("
                UPDATE backup_history 
                SET dropbox_file_path = ?, dropbox_uploaded_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->execute([$result['path_display'], $backupId]);

            $this->handleAjaxResponse(true, 'Backup uploaded to Dropbox successfully', [
                'file_path' => $result['path_display'],
                'file_name' => $result['name']
            ]);

        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Upload failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Download backup from Dropbox
     */
    public function downloadBackupFromDropbox() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $filePath = $_GET['file_path'] ?? '';

        if (empty($filePath)) {
            $_SESSION['errors'] = ["File path is required"];
            header("Location: /cloud-integration");
            exit;
        }

        try {
            $result = $this->dropboxService->downloadFile($userId, $filePath);
            
            // Set appropriate headers for file download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.zip"');
            header('Content-Length: ' . strlen($result['content']));
            
            echo $result['content'];
            exit;

        } catch (Exception $e) {
            $_SESSION['errors'] = ["Download failed: " . $e->getMessage()];
            header("Location: /cloud-integration");
            exit;
        }
    }

    /**
     * List Dropbox files
     */
    public function listDropboxFiles() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        $userId = Session::get('user_id');
        
        try {
            $files = $this->dropboxService->listFiles($userId);
            $this->handleAjaxResponse(true, 'Files retrieved successfully', ['files' => $files]);
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Failed to list files: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Delete file from Dropbox
     */
    public function deleteDropboxFile() {
        if (!Session::get('user_id')) {
            $this->handleAjaxResponse(false, 'Unauthorized', [], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->handleAjaxResponse(false, 'Invalid request method', [], 405);
        }

        // CSRF protection
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::verify($csrfToken)) {
            $this->handleAjaxResponse(false, 'Invalid CSRF token.', [], 403);
        }

        $userId = Session::get('user_id');
        $filePath = $_POST['file_path'] ?? '';

        if (empty($filePath)) {
            $this->handleAjaxResponse(false, 'File path is required', [], 400);
        }

        try {
            $success = $this->dropboxService->deleteFile($userId, $filePath);
            if ($success) {
                $this->handleAjaxResponse(true, 'File deleted successfully');
            } else {
                $this->handleAjaxResponse(false, 'Failed to delete file', [], 500);
            }
        } catch (Exception $e) {
            $this->handleAjaxResponse(false, 'Delete failed: ' . $e->getMessage(), [], 500);
        }
    }
}

