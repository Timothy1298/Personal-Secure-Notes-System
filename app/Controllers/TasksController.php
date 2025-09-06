<?php
namespace App\Controllers;

use Core\Session;
use PDO;
use Exception;

class TasksController {
    private $db;
    private $auditLogger;

    public function __construct(PDO $db, AuditLogsController $auditLogger) {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
    }

    private function handleAjaxResponse($success, $message, $httpCode = 200) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    public function index() {
        $userId = Session::get('user_id');

        // The SQL query is updated to fetch all tasks, including archived and completed ones.
        // It now only filters by user ID.
        $stmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/tasks.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$title) {
                $this->handleAjaxResponse(false, 'Task title is required.', 400);
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO tasks (user_id, title, description, is_completed, is_archived)
                    VALUES (:user_id, :title, :description, 0, 0)
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':title' => $title,
                    ':description' => $description
                ]);

                $this->auditLogger->logEvent($userId, 'created task: ' . $title);
                $this->handleAjaxResponse(true, 'Task added successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to add task.', 500);
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $userId = Session::get('user_id');

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                $stmt = $this->db->prepare("
                    UPDATE tasks
                    SET title = :title, description = :description
                    WHERE id = :id AND user_id = :user_id
                ");
                $stmt->execute([
                    ':id' => $taskId,
                    ':title' => $title,
                    ':description' => $description,
                    ':user_id' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'updated task with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task updated successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update task.', 500);
            }
        }
    }

    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                $updateStmt = $this->db->prepare("
                    UPDATE tasks
                    SET is_completed = 1
                    WHERE id = :id AND user_id = :uid
                ");
                $updateStmt->execute([
                    ':id' => $taskId,
                    ':uid' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'completed task with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task marked as completed!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to mark task as completed.', 500);
            }
        }
    }

    public function uncomplete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                $updateStmt = $this->db->prepare("
                    UPDATE tasks
                    SET is_completed = 0
                    WHERE id = :id AND user_id = :uid
                ");
                $updateStmt->execute([
                    ':id' => $taskId,
                    ':uid' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'uncompleted task with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task marked as not completed!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to mark task as not completed.', 500);
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                $stmt = $this->db->prepare("
                    DELETE FROM tasks
                    WHERE id = :id AND user_id = :uid
                ");
                $stmt->execute([
                    ':id' => $taskId,
                    ':uid' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'deleted task with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task deleted successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to delete task.', 500);
            }
        }
    }

    public function archive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                $stmt = $this->db->prepare("
                    UPDATE tasks 
                    SET is_archived = 1 
                    WHERE id = :id AND user_id = :uid
                ");
                $stmt->execute([
                    ':id' => $taskId,
                    ':uid' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'archived task with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task archived successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to archive task.', 500);
            }
        }
    }
}