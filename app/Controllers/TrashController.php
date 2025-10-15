<?php

namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use Core\Logger;
use App\Models\TasksModel;
use App\Models\NotesModel;

class TrashController {
    private $db;
    private $tasksModel;
    private $notesModel;
    private $auditLogger;

    public function __construct(\PDO $db) {
        $this->db = $db;
        $this->tasksModel = new TasksModel($db);
        $this->notesModel = new NotesModel($db);
        $this->auditLogger = new Logger();
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get deleted tasks
            $deletedTasks = $this->tasksModel->getDeletedTasks($userId);
            
            // Get deleted notes
            $deletedNotes = $this->notesModel->getDeletedNotes($userId);
            
            // Get trash statistics for both tasks and notes
            $taskTrashStats = $this->tasksModel->getTrashStats($userId);
            $noteTrashStats = $this->notesModel->getTrashStats($userId);
            
            // Combine statistics
            $trashStats = [
                'total_deleted' => ($taskTrashStats['total_deleted'] ?? 0) + ($noteTrashStats['total_deleted'] ?? 0),
                'deleted_last_week' => ($taskTrashStats['deleted_last_week'] ?? 0) + ($noteTrashStats['deleted_last_week'] ?? 0),
                'deleted_last_month' => ($taskTrashStats['deleted_last_month'] ?? 0) + ($noteTrashStats['deleted_last_month'] ?? 0),
                'tasks_deleted' => $taskTrashStats['total_deleted'] ?? 0,
                'notes_deleted' => $noteTrashStats['total_deleted'] ?? 0,
                'total_words_deleted' => $noteTrashStats['total_words_deleted'] ?? 0,
                'total_read_time_deleted' => $noteTrashStats['total_read_time_deleted'] ?? 0
            ];

            include __DIR__ . '/../Views/trash.php';
        } catch (Exception $e) {
            $_SESSION['errors'] = ["Error loading trash: " . $e->getMessage()];
            include __DIR__ . '/../Views/trash.php';
        }
    }

    public function restoreTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $_SESSION['errors'] = ["Invalid task ID."];
                header("Location: /trash");
                exit;
            }

            try {
                $result = $this->tasksModel->restoreTask($taskId, $userId);
                if ($result) {
                    $this->auditLogger->logEvent($userId, 'restored task from trash with ID: ' . $taskId);
                    $_SESSION['success'] = "Task restored successfully!";
                } else {
                    $_SESSION['errors'] = ["Failed to restore task."];
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error restoring task: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function permanentDeleteTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $taskId = $_POST['task_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId) {
                $_SESSION['errors'] = ["Invalid task ID."];
                header("Location: /trash");
                exit;
            }

            try {
                $result = $this->tasksModel->permanentDeleteTask($taskId, $userId);
                if ($result) {
                    $this->auditLogger->logEvent($userId, 'permanently deleted task with ID: ' . $taskId);
                    $_SESSION['success'] = "Task permanently deleted!";
                } else {
                    $_SESSION['errors'] = ["Failed to permanently delete task."];
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error permanently deleting task: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function emptyTrash() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $userId = Session::get('user_id');

            try {
                $deletedTasksCount = $this->tasksModel->emptyTrash($userId);
                $deletedNotesCount = $this->notesModel->emptyTrash($userId);
                $totalDeleted = $deletedTasksCount + $deletedNotesCount;
                
                $this->auditLogger->logEvent($userId, 'emptied trash - permanently deleted ' . $totalDeleted . ' items (' . $deletedTasksCount . ' tasks, ' . $deletedNotesCount . ' notes)');
                $_SESSION['success'] = "Trash emptied successfully! {$totalDeleted} items permanently deleted ({$deletedTasksCount} tasks, {$deletedNotesCount} notes).";
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error emptying trash: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function autoCleanup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $userId = Session::get('user_id');
            $daysOld = $_POST['days_old'] ?? 30;

            try {
                $deletedCount = $this->tasksModel->autoCleanupTrash($userId, $daysOld);
                $this->auditLogger->logEvent($userId, 'auto-cleanup trash - permanently deleted ' . $deletedCount . ' old tasks');
                $_SESSION['success'] = "Auto-cleanup completed! {$deletedCount} old items permanently deleted.";
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error during auto-cleanup: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function bulkRestore() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $taskIds = $_POST['task_ids'] ?? [];
            $userId = Session::get('user_id');

            if (empty($taskIds)) {
                $_SESSION['errors'] = ["No tasks selected."];
                header("Location: /trash");
                exit;
            }

            $restoredCount = 0;
            foreach ($taskIds as $taskId) {
                if ($this->tasksModel->restoreTask($taskId, $userId)) {
                    $restoredCount++;
                }
            }

            $this->auditLogger->logEvent($userId, 'bulk restored ' . $restoredCount . ' tasks from trash');
            $_SESSION['success'] = "Bulk restore completed! {$restoredCount} tasks restored.";
            header("Location: /trash");
            exit;
        }
    }

    public function bulkDelete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $taskIds = $_POST['task_ids'] ?? [];
            $userId = Session::get('user_id');

            if (empty($taskIds)) {
                $_SESSION['errors'] = ["No tasks selected."];
                header("Location: /trash");
                exit;
            }

            $deletedCount = 0;
            foreach ($taskIds as $taskId) {
                if ($this->tasksModel->permanentDeleteTask($taskId, $userId)) {
                    $deletedCount++;
                }
            }

            $this->auditLogger->logEvent($userId, 'bulk permanently deleted ' . $deletedCount . ' tasks from trash');
            $_SESSION['success'] = "Bulk delete completed! {$deletedCount} tasks permanently deleted.";
            header("Location: /trash");
            exit;
        }
    }

    // =======================================================
    // NOTE TRASH METHODS
    // =======================================================

    public function restoreNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $noteId = $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $_SESSION['errors'] = ["Invalid note ID."];
                header("Location: /trash");
                exit;
            }

            try {
                $result = $this->notesModel->restoreNote($noteId, $userId);
                if ($result) {
                    $this->auditLogger->logEvent($userId, 'restored note from trash with ID: ' . $noteId);
                    $_SESSION['success'] = "Note restored successfully!";
                } else {
                    $_SESSION['errors'] = ["Failed to restore note."];
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error restoring note: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function permanentDeleteNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $noteId = $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $_SESSION['errors'] = ["Invalid note ID."];
                header("Location: /trash");
                exit;
            }

            try {
                $result = $this->notesModel->permanentDeleteNote($noteId, $userId);
                if ($result) {
                    $this->auditLogger->logEvent($userId, 'permanently deleted note with ID: ' . $noteId);
                    $_SESSION['success'] = "Note permanently deleted!";
                } else {
                    $_SESSION['errors'] = ["Failed to permanently delete note."];
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error permanently deleting note: " . $e->getMessage()];
            }

            header("Location: /trash");
            exit;
        }
    }

    public function bulkRestoreNotes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $noteIds = $_POST['note_ids'] ?? [];
            $userId = Session::get('user_id');

            if (empty($noteIds)) {
                $_SESSION['errors'] = ["No notes selected."];
                header("Location: /trash");
                exit;
            }

            $restoredCount = 0;
            foreach ($noteIds as $noteId) {
                if ($this->notesModel->restoreNote($noteId, $userId)) {
                    $restoredCount++;
                }
            }

            $this->auditLogger->logEvent($userId, 'bulk restored ' . $restoredCount . ' notes from trash');
            $_SESSION['success'] = "Bulk restore completed! {$restoredCount} notes restored.";
            header("Location: /trash");
            exit;
        }
    }

    public function bulkDeleteNotes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $noteIds = $_POST['note_ids'] ?? [];
            $userId = Session::get('user_id');

            if (empty($noteIds)) {
                $_SESSION['errors'] = ["No notes selected."];
                header("Location: /trash");
                exit;
            }

            $deletedCount = 0;
            foreach ($noteIds as $noteId) {
                if ($this->notesModel->permanentDeleteNote($noteId, $userId)) {
                    $deletedCount++;
                }
            }

            $this->auditLogger->logEvent($userId, 'bulk permanently deleted ' . $deletedCount . ' notes from trash');
            $_SESSION['success'] = "Bulk delete completed! {$deletedCount} notes permanently deleted.";
            header("Location: /trash");
            exit;
        }
    }

    public function export() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['errors'] = ["Invalid CSRF token."];
                header("Location: /trash");
                exit;
            }

            $userId = Session::get('user_id');
            $format = $_POST['format'] ?? 'json';

            try {
                $deletedTasks = $this->tasksModel->getDeletedTasks($userId);
                $deletedNotes = $this->notesModel->getDeletedNotes($userId);
                
                $exportData = [
                    'export_date' => date('Y-m-d H:i:s'),
                    'user_id' => $userId,
                    'tasks' => $deletedTasks,
                    'notes' => $deletedNotes,
                    'summary' => [
                        'total_tasks' => count($deletedTasks),
                        'total_notes' => count($deletedNotes),
                        'total_items' => count($deletedTasks) + count($deletedNotes)
                    ]
                ];

                switch ($format) {
                    case 'json':
                        $this->exportAsJson($exportData);
                        break;
                    case 'csv':
                        $this->exportAsCsv($exportData);
                        break;
                    case 'txt':
                        $this->exportAsText($exportData);
                        break;
                    default:
                        $_SESSION['errors'] = ["Invalid export format."];
                        header("Location: /trash");
                        exit;
                }

                $this->auditLogger->logEvent($userId, 'exported trash data in ' . strtoupper($format) . ' format');
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Error exporting trash: " . $e->getMessage()];
                header("Location: /trash");
                exit;
            }
        }
    }

    private function exportAsJson($data) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="trash_export_' . date('Y-m-d_H-i-s') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function exportAsCsv($data) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="trash_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['Type', 'ID', 'Title', 'Content/Description', 'Priority', 'Deleted Date', 'Created Date']);
        
        // Export tasks
        foreach ($data['tasks'] as $task) {
            fputcsv($output, [
                'Task',
                $task['id'],
                $task['title'],
                substr(strip_tags($task['description']), 0, 200),
                $task['priority'] ?? 'medium',
                $task['deleted_at'],
                $task['created_at']
            ]);
        }
        
        // Export notes
        foreach ($data['notes'] as $note) {
            fputcsv($output, [
                'Note',
                $note['id'],
                $note['title'],
                substr(strip_tags($note['content']), 0, 200),
                $note['priority'] ?? 'medium',
                $note['deleted_at'],
                $note['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    private function exportAsText($data) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="trash_export_' . date('Y-m-d_H-i-s') . '.txt"');
        
        echo "TRASH EXPORT REPORT\n";
        echo "==================\n\n";
        echo "Export Date: " . $data['export_date'] . "\n";
        echo "User ID: " . $data['user_id'] . "\n\n";
        echo "SUMMARY:\n";
        echo "- Total Tasks: " . $data['summary']['total_tasks'] . "\n";
        echo "- Total Notes: " . $data['summary']['total_notes'] . "\n";
        echo "- Total Items: " . $data['summary']['total_items'] . "\n\n";
        
        echo "DELETED TASKS:\n";
        echo "==============\n";
        foreach ($data['tasks'] as $task) {
            echo "ID: " . $task['id'] . "\n";
            echo "Title: " . $task['title'] . "\n";
            echo "Description: " . substr(strip_tags($task['description']), 0, 200) . "\n";
            echo "Priority: " . ($task['priority'] ?? 'medium') . "\n";
            echo "Deleted: " . $task['deleted_at'] . "\n";
            echo "Created: " . $task['created_at'] . "\n";
            echo "---\n";
        }
        
        echo "\nDELETED NOTES:\n";
        echo "==============\n";
        foreach ($data['notes'] as $note) {
            echo "ID: " . $note['id'] . "\n";
            echo "Title: " . $note['title'] . "\n";
            echo "Content: " . substr(strip_tags($note['content']), 0, 200) . "\n";
            echo "Priority: " . ($note['priority'] ?? 'medium') . "\n";
            echo "Deleted: " . $note['deleted_at'] . "\n";
            echo "Created: " . $note['created_at'] . "\n";
            echo "---\n";
        }
        
        exit;
    }
}
