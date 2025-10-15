<?php
namespace App\Controllers;

use Core\Session;
use PDO;
use Exception;

class ArchivedController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        $userId = Session::get('user_id');

        // Fetch archived notes
        $notesStmt = $this->db->prepare("
            SELECT * FROM notes 
            WHERE user_id = :uid AND is_archived = 1
            ORDER BY created_at DESC
        ");
        $notesStmt->execute([':uid' => $userId]);
        $notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch archived tasks
        $tasksStmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE user_id = :uid AND is_archived = 1
            ORDER BY created_at DESC
        ");
        $tasksStmt->execute([':uid' => $userId]);
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/archived.php';
    }

    public function unarchiveNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if it's an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                // Handle JSON input
                $input = json_decode(file_get_contents('php://input'), true);
                $noteId = $input['note_id'] ?? null;
                $csrfToken = $input['csrf_token'] ?? '';
                
                // Verify CSRF token
                if (!\Core\CSRF::verify($csrfToken)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                    exit;
                }
            } else {
                // Handle form input
                $noteId = $_POST['note_id'] ?? null;
            }
            
            $userId = Session::get('user_id');

            if (!$noteId) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid note ID.']);
                    exit;
                } else {
                    $_SESSION['errors'] = ["Invalid note ID."];
                    header("Location: /archived");
                    exit;
                }
            }

            $stmt = $this->db->prepare("
                UPDATE notes 
                SET is_archived = 0 
                WHERE id = :id AND user_id = :uid
            ");
            $result = $stmt->execute([
                ':id' => $noteId,
                ':uid' => $userId
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                if ($result && $stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Note unarchived successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to unarchive note.']);
                }
                exit;
            } else {
                $_SESSION['success'] = "Note unarchived successfully!";
                header("Location: /archived");
                exit;
            }
        }
    }

    public function unarchiveTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if it's an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                // Handle JSON input
                $input = json_decode(file_get_contents('php://input'), true);
                $taskId = $input['task_id'] ?? null;
                $csrfToken = $input['csrf_token'] ?? '';
                
                // Verify CSRF token
                if (!\Core\CSRF::verify($csrfToken)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                    exit;
                }
            } else {
                // Handle form input
                $taskId = $_POST['task_id'] ?? null;
            }
            
            $userId = Session::get('user_id');

            if (!$taskId) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
                    exit;
                } else {
                    $_SESSION['errors'] = ["Invalid task ID."];
                    header("Location: /archived");
                    exit;
                }
            }

            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET is_archived = 0 
                WHERE id = :id AND user_id = :uid
            ");
            $result = $stmt->execute([
                ':id' => $taskId,
                ':uid' => $userId
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                if ($result && $stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Task unarchived successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to unarchive task.']);
                }
                exit;
            } else {
                $_SESSION['success'] = "Task unarchived successfully!";
                header("Location: /archived");
                exit;
            }
        }
    }

    public function trash() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get deleted notes
            $notesStmt = $this->db->prepare("
                SELECT * FROM notes 
                WHERE user_id = :uid AND is_deleted = 1 
                ORDER BY deleted_at DESC
            ");
            $notesStmt->execute([':uid' => $userId]);
            $deletedNotes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get deleted tasks
            $tasksStmt = $this->db->prepare("
                SELECT * FROM tasks 
                WHERE user_id = :uid AND is_deleted = 1 
                ORDER BY deleted_at DESC
            ");
            $tasksStmt->execute([':uid' => $userId]);
            $deletedTasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

            include __DIR__ . '/../Views/trash.php';
        } catch (Exception $e) {
            $_SESSION['errors'] = ["Error loading trash: " . $e->getMessage()];
            include __DIR__ . '/../Views/trash.php';
        }
    }

    public function emptyTrash() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            
            try {
                // Permanently delete notes
                $notesStmt = $this->db->prepare("
                    DELETE FROM notes 
                    WHERE user_id = :uid AND is_deleted = 1
                ");
                $notesStmt->execute([':uid' => $userId]);

                // Permanently delete tasks
                $tasksStmt = $this->db->prepare("
                    DELETE FROM tasks 
                    WHERE user_id = :uid AND is_deleted = 1
                ");
                $tasksStmt->execute([':uid' => $userId]);

                $_SESSION['success'] = "Trash emptied successfully!";
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Failed to empty trash: " . $e->getMessage()];
            }
            
            header("Location: /trash");
            exit;
        }
    }
}