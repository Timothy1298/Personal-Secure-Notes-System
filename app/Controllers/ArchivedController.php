<?php
namespace App\Controllers;

use Core\Session;
use PDO;

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
            $noteId = $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $_SESSION['errors'] = ["Invalid note ID."];
                header("Location: /archived");
                exit;
            }

            $stmt = $this->db->prepare("
                UPDATE notes 
                SET is_archived = 0 
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':id' => $noteId,
                ':uid' => $userId
            ]);

            $_SESSION['success'] = "Note unarchived successfully!";
            header("Location: /archived");
            exit;
        }
    }

    public function unarchiveTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $_SESSION['errors'] = ["Invalid task ID."];
                header("Location: /archived");
                exit;
            }

            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET is_archived = 0 
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':id' => $taskId,
                ':uid' => $userId
            ]);

            $_SESSION['success'] = "Task unarchived successfully!";
            header("Location: /archived");
            exit;
        }
    }
}