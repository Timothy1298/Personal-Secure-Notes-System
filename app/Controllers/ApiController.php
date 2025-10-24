<?php
namespace App\Controllers;

use Core\Database;
use Core\Security;
use Core\Session;
use App\Models\NotesModel;
use App\Models\TasksModel;
use App\Models\User;
use Exception;
use PDO;

class ApiController {
    private $db;
    private $notesModel;
    private $tasksModel;
    private $apiKey;
    private $userId;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->notesModel = new NotesModel($this->db);
        $this->tasksModel = new TasksModel($this->db);
        
        // Set JSON response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        $this->authenticate();
    }

    private function authenticate() {
        $headers = getallheaders();
        $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;
        
        if (!$apiKey) {
            $this->sendError('API key required', 401);
        }
        
        // Verify API key
        $stmt = $this->db->prepare("
            SELECT ak.*, u.username, u.email 
            FROM api_keys ak 
            JOIN users u ON ak.user_id = u.id 
            WHERE ak.api_key = ? AND ak.is_active = 1 
            AND (ak.expires_at IS NULL OR ak.expires_at > NOW())
        ");
        $stmt->execute([$apiKey]);
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$keyData) {
            $this->sendError('Invalid or expired API key', 401);
        }
        
        $this->apiKey = $apiKey;
        $this->userId = $keyData['user_id'];
        
        // Update last used timestamp
        $updateStmt = $this->db->prepare("UPDATE api_keys SET last_used = NOW() WHERE id = ?");
        $updateStmt->execute([$keyData['id']]);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/api', '', $path);
        
        // Route the request
        switch ($path) {
            case '/notes':
                return $this->handleNotes($method);
            case '/tasks':
                return $this->handleTasks($method);
            case '/tags':
                return $this->handleTags($method);
            case '/user':
                return $this->handleUser($method);
            case '/analytics':
                return $this->handleAnalytics($method);
            case '/collaboration':
                return $this->handleCollaboration($method);
            case '/plugins':
                return $this->handlePlugins($method);
            case '/mobile/sync':
                return $this->handleMobileSync($method);
            case '/password-reset':
                return $this->handlePasswordReset($method);
            default:
                $this->sendError('Endpoint not found', 404);
        }
    }

    private function handleNotes($method) {
        switch ($method) {
            case 'GET':
                $notes = $this->notesModel->getNotesWithTagsByUserId($this->userId);
                $this->sendSuccess($notes);
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $noteId = $this->notesModel->createNote($this->userId, $data);
                $this->sendSuccess(['id' => $noteId], 'Note created successfully', 201);
                break;
            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                $noteId = $data['id'] ?? null;
                if (!$noteId) {
                    $this->sendError('Note ID required', 400);
                }
                $this->notesModel->updateNote($noteId, $this->userId, $data);
                $this->sendSuccess(null, 'Note updated successfully');
                break;
            case 'DELETE':
                $noteId = $_GET['id'] ?? null;
                if (!$noteId) {
                    $this->sendError('Note ID required', 400);
                }
                $this->notesModel->deleteNote($noteId, $this->userId);
                $this->sendSuccess(null, 'Note deleted successfully');
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleTasks($method) {
        switch ($method) {
            case 'GET':
                $tasks = $this->tasksModel->getTasksByUserId($this->userId);
                $this->sendSuccess($tasks);
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $taskId = $this->tasksModel->createTask($this->userId, $data);
                $this->sendSuccess(['id' => $taskId], 'Task created successfully', 201);
                break;
            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                $taskId = $data['id'] ?? null;
                if (!$taskId) {
                    $this->sendError('Task ID required', 400);
                }
                $this->tasksModel->updateTask($taskId, $this->userId, $data);
                $this->sendSuccess(null, 'Task updated successfully');
                break;
            case 'DELETE':
                $taskId = $_GET['id'] ?? null;
                if (!$taskId) {
                    $this->sendError('Task ID required', 400);
                }
                $this->tasksModel->deleteTask($taskId, $this->userId);
                $this->sendSuccess(null, 'Task deleted successfully');
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleTags($method) {
        switch ($method) {
            case 'GET':
                $stmt = $this->db->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name");
                $stmt->execute([$this->userId]);
                $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->sendSuccess($tags);
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $this->db->prepare("
                    INSERT INTO tags (user_id, name, color, icon, description) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $this->userId,
                    $data['name'],
                    $data['color'] ?? '#3b82f6',
                    $data['icon'] ?? 'fas fa-tag',
                    $data['description'] ?? ''
                ]);
                $this->sendSuccess(['id' => $this->db->lastInsertId()], 'Tag created successfully', 201);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleUser($method) {
        if ($method !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        $user = User::findById($this->userId);
        if (!$user) {
            $this->sendError('User not found', 404);
        }
        
        // Remove sensitive data
        unset($user['password'], $user['two_factor_secret'], $user['backup_codes']);
        $this->sendSuccess($user);
    }

    private function handleAnalytics($method) {
        if ($method !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        // Get user analytics
        $stmt = $this->db->prepare("
            SELECT metric_type, AVG(metric_value) as avg_value, 
                   MAX(metric_value) as max_value, MIN(metric_value) as min_value,
                   COUNT(*) as data_points
            FROM user_analytics 
            WHERE user_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY metric_type
        ");
        $stmt->execute([$this->userId]);
        $analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($analytics);
    }

    private function handleCollaboration($method) {
        switch ($method) {
            case 'GET':
                $resourceType = $_GET['type'] ?? null;
                $resourceId = $_GET['id'] ?? null;
                
                if (!$resourceType || !$resourceId) {
                    $this->sendError('Resource type and ID required', 400);
                }
                
                $stmt = $this->db->prepare("
                    SELECT * FROM collaboration_sessions 
                    WHERE resource_type = ? AND resource_id = ? AND is_active = 1
                ");
                $stmt->execute([$resourceType, $resourceId]);
                $session = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->sendSuccess($session);
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $sessionId = Security::generateToken(32);
                
                $stmt = $this->db->prepare("
                    INSERT INTO collaboration_sessions 
                    (session_id, resource_type, resource_id, owner_id, participants) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sessionId,
                    $data['resource_type'],
                    $data['resource_id'],
                    $this->userId,
                    json_encode([$this->userId])
                ]);
                
                $this->sendSuccess(['session_id' => $sessionId], 'Collaboration session created', 201);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handlePlugins($method) {
        if ($method !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        $stmt = $this->db->prepare("
            SELECT p.*, up.is_enabled, up.user_config 
            FROM plugins p 
            LEFT JOIN user_plugin_preferences up ON p.id = up.plugin_id AND up.user_id = ?
            WHERE p.is_active = 1
        ");
        $stmt->execute([$this->userId]);
        $plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($plugins);
    }

    private function handleMobileSync($method) {
        if ($method !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $deviceId = $data['device_id'] ?? null;
        $deviceType = $data['device_type'] ?? 'web';
        $appVersion = $data['app_version'] ?? null;
        $pushToken = $data['push_token'] ?? null;
        
        if (!$deviceId) {
            $this->sendError('Device ID required', 400);
        }
        
        // Update or create mobile session
        $stmt = $this->db->prepare("
            INSERT INTO mobile_sessions (user_id, device_id, device_type, app_version, push_token, last_sync)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            device_type = VALUES(device_type),
            app_version = VALUES(app_version),
            push_token = VALUES(push_token),
            last_sync = NOW(),
            is_active = 1
        ");
        $stmt->execute([$this->userId, $deviceId, $deviceType, $appVersion, $pushToken]);
        
        // Get sync data
        $notes = $this->notesModel->getNotesWithTagsByUserId($this->userId);
        $tasks = $this->tasksModel->getTasksByUserId($this->userId);
        
        $this->sendSuccess([
            'notes' => $notes,
            'tasks' => $tasks,
            'last_sync' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Handle password reset API
     */
    private function handlePasswordReset($method) {
        if ($method !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        
        if (empty($email) || empty($newPassword)) {
            $this->sendError('Email and new password are required', 400);
        }
        
        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            // Hash the new password
            $hashedPassword = Security::hashPassword($newPassword);
            
            // Update password
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            // Log the password reset
            Security::logSecurityEvent($this->db, $user['id'], 'password_reset', 'user', $user['id'], [
                'email' => $email,
                'ip_address' => Security::getClientIP()
            ]);
            
            $this->sendSuccess([
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ], 'Password reset successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to reset password', 500);
        }
    }

    private function sendSuccess($data = null, $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
