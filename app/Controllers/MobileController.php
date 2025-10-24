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

class MobileController {
    private $db;
    private $notesModel;
    private $tasksModel;

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
    }

    /**
     * Mobile app authentication
     */
    public function authenticate($username, $password) {
        try {
            $user = User::findByUsername($username);
            
            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                return $this->sendError('Invalid credentials', 401);
            }
            
            // Generate mobile session token
            $token = Security::generateToken(64);
            
            // Store mobile session
            $stmt = $this->db->prepare("
                INSERT INTO mobile_sessions 
                (user_id, device_id, device_type, app_version, last_sync) 
                VALUES (?, ?, 'mobile', ?, NOW())
                ON DUPLICATE KEY UPDATE 
                last_sync = NOW(),
                is_active = 1
            ");
            $stmt->execute([$user['id'], $token, '1.0.0']);
            
            return $this->sendSuccess([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } catch (Exception $e) {
            return $this->sendError('Authentication failed', 500);
        }
    }

    /**
     * Sync data for mobile app
     */
    public function syncData($token, $lastSync = null) {
        try {
            // Verify token
            $stmt = $this->db->prepare("
                SELECT ms.*, u.username, u.email 
                FROM mobile_sessions ms
                JOIN users u ON ms.user_id = u.id
                WHERE ms.device_id = ? AND ms.is_active = 1
            ");
            $stmt->execute([$token]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                return $this->sendError('Invalid session token', 401);
            }
            
            $userId = $session['user_id'];
            
            // Get data since last sync
            $whereClause = '';
            $params = [$userId];
            
            if ($lastSync) {
                $whereClause = ' AND updated_at > ?';
                $params[] = $lastSync;
            }
            
            // Get notes
            $stmt = $this->db->prepare("
                SELECT n.*, GROUP_CONCAT(t.name) as tags
                FROM notes n
                LEFT JOIN note_tags nt ON n.id = nt.note_id
                LEFT JOIN tags t ON nt.tag_id = t.id
                WHERE n.user_id = ? {$whereClause}
                GROUP BY n.id
                ORDER BY n.updated_at DESC
            ");
            $stmt->execute($params);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tasks
            $stmt = $this->db->prepare("
                SELECT t.*, GROUP_CONCAT(tg.name) as tags
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags tg ON tt.tag_id = tg.id
                WHERE t.user_id = ? {$whereClause}
                GROUP BY t.id
                ORDER BY t.updated_at DESC
            ");
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tags
            $stmt = $this->db->prepare("
                SELECT * FROM tags 
                WHERE user_id = ? 
                ORDER BY name
            ");
            $stmt->execute([$userId]);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Update last sync
            $stmt = $this->db->prepare("
                UPDATE mobile_sessions 
                SET last_sync = NOW() 
                WHERE device_id = ?
            ");
            $stmt->execute([$token]);
            
            return $this->sendSuccess([
                'notes' => $notes,
                'tasks' => $tasks,
                'tags' => $tags,
                'sync_time' => date('Y-m-d H:i:s'),
                'user' => [
                    'id' => $session['user_id'],
                    'username' => $session['username'],
                    'email' => $session['email']
                ]
            ]);
        } catch (Exception $e) {
            return $this->sendError('Sync failed', 500);
        }
    }

    /**
     * Create note from mobile
     */
    public function createNote($token, $noteData) {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            $noteId = $this->notesModel->createNote($userId, $noteData);
            
            return $this->sendSuccess([
                'id' => $noteId,
                'message' => 'Note created successfully'
            ], 201);
        } catch (Exception $e) {
            return $this->sendError('Failed to create note', 500);
        }
    }

    /**
     * Update note from mobile
     */
    public function updateNote($token, $noteId, $noteData) {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            $this->notesModel->updateNote($noteId, $userId, $noteData);
            
            return $this->sendSuccess([
                'message' => 'Note updated successfully'
            ]);
        } catch (Exception $e) {
            return $this->sendError('Failed to update note', 500);
        }
    }

    /**
     * Create task from mobile
     */
    public function createTask($token, $taskData) {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            $taskId = $this->tasksModel->createTask($userId, $taskData);
            
            return $this->sendSuccess([
                'id' => $taskId,
                'message' => 'Task created successfully'
            ], 201);
        } catch (Exception $e) {
            return $this->sendError('Failed to create task', 500);
        }
    }

    /**
     * Update task from mobile
     */
    public function updateTask($token, $taskId, $taskData) {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            $this->tasksModel->updateTask($taskId, $userId, $taskData);
            
            return $this->sendSuccess([
                'message' => 'Task updated successfully'
            ]);
        } catch (Exception $e) {
            return $this->sendError('Failed to update task', 500);
        }
    }

    /**
     * Get offline data for mobile
     */
    public function getOfflineData($token) {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            // Get all user data for offline use
            $notes = $this->notesModel->getNotesWithTagsByUserId($userId);
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            
            // Get tags
            $stmt = $this->db->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name");
            $stmt->execute([$userId]);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->sendSuccess([
                'notes' => $notes,
                'tasks' => $tasks,
                'tags' => $tags,
                'offline_time' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            return $this->sendError('Failed to get offline data', 500);
        }
    }

    /**
     * Handle mobile app push notifications
     */
    public function registerPushToken($token, $pushToken, $deviceType = 'mobile') {
        try {
            $userId = $this->verifyToken($token);
            if (!$userId) {
                return $this->sendError('Invalid token', 401);
            }
            
            $stmt = $this->db->prepare("
                UPDATE mobile_sessions 
                SET push_token = ?, device_type = ?, updated_at = NOW()
                WHERE device_id = ?
            ");
            $stmt->execute([$pushToken, $deviceType, $token]);
            
            return $this->sendSuccess([
                'message' => 'Push token registered successfully'
            ]);
        } catch (Exception $e) {
            return $this->sendError('Failed to register push token', 500);
        }
    }

    /**
     * Send push notification
     */
    public function sendPushNotification($userId, $title, $message, $data = []) {
        try {
            $stmt = $this->db->prepare("
                SELECT push_token 
                FROM mobile_sessions 
                WHERE user_id = ? AND push_token IS NOT NULL AND is_active = 1
            ");
            $stmt->execute([$userId]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                return false;
            }
            
            // Here you would integrate with FCM or APNS
            // For now, we'll just log the notification
            error_log("Push notification to user {$userId}: {$title} - {$message}");
            
            return true;
        } catch (Exception $e) {
            error_log("Error sending push notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify mobile token
     */
    private function verifyToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id 
                FROM mobile_sessions 
                WHERE device_id = ? AND is_active = 1
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['user_id'] : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Send success response
     */
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

    /**
     * Send error response
     */
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
