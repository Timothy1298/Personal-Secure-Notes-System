<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Collaboration\TeamManager;
use Exception;

class SharedController {
    private $db;
    private $teamManager;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->teamManager = new TeamManager($this->db);
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        // Get user's teams
        $teams = $this->teamManager->getUserTeams($userId);
        
        // Get all shared content from user's teams
        $sharedNotes = [];
        $sharedTasks = [];
        
        foreach ($teams as $team) {
            $teamNotes = $this->teamManager->getTeamSharedNotes($team['id']);
            $teamTasks = $this->teamManager->getTeamSharedTasks($team['id']);
            
            foreach ($teamNotes as $note) {
                $note['team_name'] = $team['name'];
                $note['team_id'] = $team['id'];
                $sharedNotes[] = $note;
            }
            
            foreach ($teamTasks as $task) {
                $task['team_name'] = $team['name'];
                $task['team_id'] = $team['id'];
                $sharedTasks[] = $task;
            }
        }
        
        // Get shared links
        $sharedLinks = $this->getSharedLinks($userId);
        
        include __DIR__ . '/../Views/shared.php';
    }

    public function createLink() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $resourceType = $_POST['resource_type'] ?? '';
        $resourceId = $_POST['resource_id'] ?? null;
        $permission = $_POST['permission'] ?? 'read';
        $expiresAt = $_POST['expires_at'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$resourceType || !$resourceId) {
            echo json_encode(['success' => false, 'message' => 'Resource type and ID are required']);
            exit;
        }

        try {
            $shareToken = bin2hex(random_bytes(32));
            $passwordHash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
            
            $stmt = $this->db->prepare("
                INSERT INTO shared_links (resource_type, resource_id, created_by, share_token, permission, expires_at, password_hash, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $resourceType,
                $resourceId,
                $userId,
                $shareToken,
                $permission,
                $expiresAt,
                $passwordHash
            ]);
            
            $linkId = $this->db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'link_id' => $linkId,
                'share_token' => $shareToken,
                'share_url' => $this->getShareUrl($shareToken)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function revokeLink() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $linkId = $_POST['link_id'] ?? null;

        if (!$linkId) {
            echo json_encode(['success' => false, 'message' => 'Link ID is required']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE shared_links 
                SET is_active = FALSE 
                WHERE id = ? AND created_by = ?
            ");
            
            $stmt->execute([$linkId, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Link revoked successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function accessLink($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT sl.*, u.username as created_by_name
                FROM shared_links sl
                JOIN users u ON sl.created_by = u.id
                WHERE sl.share_token = ? AND sl.is_active = TRUE
                AND (sl.expires_at IS NULL OR sl.expires_at > NOW())
            ");
            $stmt->execute([$token]);
            $link = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$link) {
                throw new Exception('Link not found or expired');
            }

            // Check password if required
            if ($link['password_hash'] && !isset($_SESSION['shared_link_authenticated_' . $token])) {
                include __DIR__ . '/../Views/shared_link_auth.php';
                exit;
            }

            // Get resource content
            $resource = $this->getResourceContent($link['resource_type'], $link['resource_id']);
            
            if (!$resource) {
                throw new Exception('Resource not found');
            }

            // Record access
            $this->recordAccess($link['id']);

            include __DIR__ . '/../Views/shared_link_view.php';
        } catch (Exception $e) {
            include __DIR__ . '/../Views/shared_link_error.php';
        }
    }

    public function authenticateLink() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$token || !$password) {
            echo json_encode(['success' => false, 'message' => 'Token and password are required']);
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT password_hash FROM shared_links 
                WHERE share_token = ? AND is_active = TRUE
            ");
            $stmt->execute([$token]);
            $link = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$link || !password_verify($password, $link['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
                exit;
            }

            $_SESSION['shared_link_authenticated_' . $token] = true;
            
            echo json_encode(['success' => true, 'message' => 'Authentication successful']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function getSharedLinks($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sl.*, 
                       CASE 
                           WHEN sl.resource_type = 'note' THEN n.title
                           WHEN sl.resource_type = 'task' THEN t.title
                           ELSE 'Unknown'
                       END as resource_title
                FROM shared_links sl
                LEFT JOIN notes n ON sl.resource_type = 'note' AND sl.resource_id = n.id
                LEFT JOIN tasks t ON sl.resource_type = 'task' AND sl.resource_id = t.id
                WHERE sl.created_by = ? AND sl.is_active = TRUE
                ORDER BY sl.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting shared links: " . $e->getMessage());
            return [];
        }
    }

    private function getResourceContent($resourceType, $resourceId) {
        try {
            if ($resourceType === 'note') {
                $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
            } elseif ($resourceType === 'task') {
                $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
            } else {
                return null;
            }
            
            $stmt->execute([$resourceId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting resource content: " . $e->getMessage());
            return null;
        }
    }

    private function recordAccess($linkId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO shared_link_access_logs (shared_link_id, ip_address, user_agent, accessed_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $linkId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            // Update access count
            $stmt = $this->db->prepare("
                UPDATE shared_links 
                SET access_count = access_count + 1, last_accessed = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$linkId]);
        } catch (Exception $e) {
            error_log("Error recording access: " . $e->getMessage());
        }
    }

    private function getShareUrl($token) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "{$protocol}://{$host}/shared/{$token}";
    }
}
