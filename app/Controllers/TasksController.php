<?php
namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use PDO;
use Exception;
use App\Models\TasksModel; // <-- 1. Added use statement

class TasksController {
    private $db;
    private $auditLogger;
    private $tasksModel; // <-- New property for TasksModel

    // 2. Constructor updated to inject TasksModel
    public function __construct(PDO $db, AuditLogsController $auditLogger, TasksModel $tasksModel) {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
        $this->tasksModel = $tasksModel; // <-- Assigned TasksModel
    }

    private function handleAjaxResponse($success, $message, $httpCode = 200) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    public function index() {
        $userId = Session::get('user_id');

        // 3. REFACTORED: Now uses the injected TasksModel
        // This will fetch only unarchived tasks, as defined in TasksModel::getTasksByUserId()
        $tasks = $this->tasksModel->getTasksByUserId($userId);

        // Get tags for the user
        $tags = $this->getUserTags($userId);
        
        // Ensure $tags is always an array
        if (!is_array($tags)) {
            $tags = [];
        }

        include __DIR__ . '/../Views/tasks.php';
    }

    private function getUserTags($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, color 
                FROM tags 
                WHERE user_id = :user_id 
                ORDER BY name ASC
            ");
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : [];
        } catch (Exception $e) {
            error_log("Error fetching tags: " . $e->getMessage());
            return [];
        }
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
                // Use TasksModel to create task (handles encryption)
                $taskId = $this->tasksModel->createTask($userId, $title, $description);
                
                if ($taskId === false) {
                    $this->handleAjaxResponse(false, 'Failed to save task due to an encryption error.', 500);
                }

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
                    SET status = 'completed', completed_at = NOW()
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
                    SET status = 'pending', completed_at = NULL
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
            // Handle both JSON and form data
            $input = json_decode(file_get_contents('php://input'), true);
            $taskId = $input['task_id'] ?? $_POST['task_id'] ?? null;
            $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? null;
            $userId = Session::get('user_id');

            // Validate CSRF token
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }

            if (!$taskId) {
                $this->handleAjaxResponse(false, 'Invalid task ID.', 400);
            }

            try {
                // Soft delete - move to trash
                $stmt = $this->db->prepare("
                    UPDATE tasks 
                    SET is_deleted = 1, deleted_at = NOW()
                    WHERE id = :id AND user_id = :uid
                ");
                $stmt->execute([
                    ':id' => $taskId,
                    ':uid' => $userId
                ]);

                $this->auditLogger->logEvent($userId, 'moved task to trash with ID: ' . $taskId);
                $this->handleAjaxResponse(true, 'Task moved to trash successfully!');

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

    // API endpoint for Kanban board data
    public function apiGetKanban() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            
            // Group tasks by status for Kanban board
            $kanbanData = [
                'pending' => [],
                'in_progress' => [],
                'completed' => [],
                'cancelled' => []
            ];
            
            foreach ($tasks as $task) {
                $status = $task['status'] ?? 'pending';
                if (isset($kanbanData[$status])) {
                    $kanbanData[$status][] = $task;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $kanbanData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load tasks']);
        }
    }

    // API endpoint for calendar data
    public function apiCalendar() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        
        // Log for debugging
        error_log("Calendar API called for user $userId, start: $start, end: $end");
        
        try {
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            error_log("Found " . count($tasks) . " tasks for user $userId");
            
            // Format tasks for FullCalendar
            $calendarEvents = [];
            foreach ($tasks as $task) {
                // Include all tasks, not just those with due dates
                $startDate = $task['due_date'] ?? $task['created_at'];
                if (!empty($startDate)) {
                    $calendarEvents[] = [
                        'id' => $task['id'],
                        'title' => $task['title'],
                        'start' => $startDate,
                        'end' => $task['due_date'] ?? $startDate,
                        'color' => $this->getPriorityColor($task['priority'] ?? 'medium'),
                        'backgroundColor' => $this->getPriorityColor($task['priority'] ?? 'medium'),
                        'borderColor' => $this->getPriorityColor($task['priority'] ?? 'medium'),
                        'extendedProps' => [
                            'description' => $task['description'] ?? '',
                            'status' => $task['status'] ?? 'pending',
                            'priority' => $task['priority'] ?? 'medium',
                            'category' => $task['category'] ?? '',
                            'tags' => $task['tags'] ?? ''
                        ]
                    ];
                }
            }
            
            error_log("Formatted " . count($calendarEvents) . " calendar events");
            
            header('Content-Type: application/json');
            echo json_encode($calendarEvents);
        } catch (Exception $e) {
            error_log("Calendar API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load calendar tasks: ' . $e->getMessage()]);
        }
    }

    // Helper method to get priority color
    private function getPriorityColor($priority) {
        $colors = [
            'low' => '#10B981',
            'medium' => '#3B82F6',
            'high' => '#F59E0B',
            'urgent' => '#EF4444'
        ];
        return $colors[$priority] ?? '#3B82F6';
    }

    // Update task status
    public function updateStatus() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = $input['task_id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$taskId || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        try {
            $result = $this->tasksModel->updateTaskStatus($taskId, $status, Session::get('user_id'));
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating task status']);
        }
    }

    // Get subtasks for a task
    public function getSubtasks() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get task ID from URL path or GET parameter
        $taskId = null;
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check if task ID is in the URL path (e.g., /tasks/api/get-subtasks/123)
        if (preg_match('/\/tasks\/api\/get-subtasks\/(\d+)/', $requestUri, $matches)) {
            $taskId = $matches[1];
        } else {
            // Fallback to GET parameter
            $taskId = $_GET['task_id'] ?? null;
        }
        
        if (!$taskId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Task ID required']);
            exit;
        }

        try {
            $subtasks = $this->tasksModel->getSubtasksByTaskId($taskId, Session::get('user_id'));
            echo json_encode(['success' => true, 'subtasks' => $subtasks]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error loading subtasks']);
        }
    }

    // Store new subtask
    public function storeSubtask() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = $input['task_id'] ?? null;
        $title = $input['title'] ?? null;

        if (!$taskId || !$title) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        try {
            $subtaskId = $this->tasksModel->createSubtask($taskId, Session::get('user_id'), $title);
            if ($subtaskId) {
                echo json_encode(['success' => true, 'message' => 'Subtask created successfully', 'subtask_id' => $subtaskId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create subtask']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating subtask']);
        }
    }

    // Update subtask
    public function updateSubtask() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $subtaskId = $input['subtask_id'] ?? null;
        $title = $input['title'] ?? null;
        $status = $input['status'] ?? null;

        if (!$subtaskId || (!$title && !$status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        try {
            $data = [];
            if ($title !== null) $data['title'] = $title;
            if ($status !== null) $data['status'] = $status;
            
            $result = $this->tasksModel->updateSubtask($subtaskId, Session::get('user_id'), $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Subtask updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update subtask']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating subtask']);
        }
    }

    // Delete subtask
    public function deleteSubtask() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $subtaskId = $input['subtask_id'] ?? null;

        if (!$subtaskId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Subtask ID required']);
            exit;
        }

        try {
            $result = $this->tasksModel->deleteSubtask($subtaskId, Session::get('user_id'));
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Subtask deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete subtask']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting subtask']);
        }
    }

    // Update task priority
    public function updatePriority() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $taskId = $input['task_id'] ?? $_POST['task_id'] ?? null;
            $priority = $input['priority'] ?? $_POST['priority'] ?? null;
            $userId = Session::get('user_id');

            if (!$taskId || !$priority) {
                $this->handleAjaxResponse(false, 'Task ID and priority are required.', 400);
            }

            if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
                $this->handleAjaxResponse(false, 'Invalid priority level.', 400);
            }

            try {
                $result = $this->tasksModel->updateTaskPriority($taskId, $userId, $priority);
                if ($result) {
                    $this->auditLogger->logEvent($userId, 'updated task priority with ID: ' . $taskId . ' to ' . $priority);
                    $this->handleAjaxResponse(true, 'Task priority updated successfully!');
                } else {
                    $this->handleAjaxResponse(false, 'Failed to update task priority.', 500);
                }
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Error updating task priority: ' . $e->getMessage(), 500);
            }
        }
    }

    // Export tasks
    public function export() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $format = $input['format'] ?? 'json';
        $scope = $input['scope'] ?? 'all';
        $taskIds = $input['task_ids'] ?? [];

        try {
            $userId = Session::get('user_id');
            $tasks = [];

            if ($scope === 'selected' && !empty($taskIds)) {
                $tasks = $this->tasksModel->getTasksByIds($taskIds, $userId);
            } else {
                $tasks = $this->tasksModel->getTasksByUserId($userId);
            }

            switch ($format) {
                case 'json':
                    $this->exportAsJson($tasks);
                    break;
                case 'markdown':
                case 'md':
                    $this->exportAsMarkdown($tasks);
                    break;
                case 'docx':
                case 'word':
                    $this->exportAsDocx($tasks);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Unsupported export format']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Export failed']);
        }
    }

    private function exportAsJson($tasks) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d_H-i-s') . '.json"');
        echo json_encode($tasks, JSON_PRETTY_PRINT);
    }

    private function exportAsMarkdown($tasks) {
        header('Content-Type: text/markdown');
        header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d_H-i-s') . '.md"');
        
        $markdown = "# Tasks Export\n\n";
        $markdown .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tasks as $task) {
            $markdown .= "## " . $task['title'] . "\n\n";
            $markdown .= "**Status:** " . ucfirst($task['status']) . "\n";
            $markdown .= "**Priority:** " . ucfirst($task['priority']) . "\n";
            $markdown .= "**Category:** " . ($task['category'] ?? 'None') . "\n";
            $markdown .= "**Due Date:** " . ($task['due_date'] ?? 'None') . "\n\n";
            
            if (!empty($task['description'])) {
                $markdown .= "**Description:**\n" . $task['description'] . "\n\n";
            }
            
            $markdown .= "---\n\n";
        }
        
        echo $markdown;
    }

    private function exportAsDocx($tasks) {
        try {
            // Create new Word document
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            
            // Set document properties
            $phpWord->getDocInfo()->setCreator('SecureNote Pro');
            $phpWord->getDocInfo()->setTitle('Tasks Export');
            $phpWord->getDocInfo()->setDescription('Exported tasks from SecureNote Pro');
            $phpWord->getDocInfo()->setCreated(time());
            
            // Add a section
            $section = $phpWord->addSection();
            
            // Add title
            $section->addText('Tasks Export', ['bold' => true, 'size' => 16]);
            $section->addText('Generated on: ' . date('Y-m-d H:i:s'), ['size' => 10]);
            $section->addTextBreak(2);
            
            // Add tasks
            foreach ($tasks as $index => $task) {
                $section->addText(($index + 1) . '. ' . $task['title'], ['bold' => true, 'size' => 12]);
                
                $section->addText('Status: ' . ucfirst($task['status']), ['size' => 10]);
                $section->addText('Priority: ' . ucfirst($task['priority']), ['size' => 10]);
                $section->addText('Category: ' . ($task['category'] ?? 'None'), ['size' => 10]);
                $section->addText('Due Date: ' . ($task['due_date'] ?? 'None'), ['size' => 10]);
                
                if (!empty($task['description'])) {
                    $section->addText('Description:', ['bold' => true, 'size' => 10]);
                    $section->addText($task['description'], ['size' => 10]);
                }
                
                $section->addTextBreak(1);
            }
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d_H-i-s') . '.docx"');
            
            // Write document to output
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
            
        } catch (Exception $e) {
            error_log("DOCX export error: " . $e->getMessage());
            
            // Fallback to simple text format
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d_H-i-s') . '.txt"');
            
            $content = "Tasks Export\n\n";
            $content .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($tasks as $task) {
                $content .= "Task: " . $task['title'] . "\n";
                $content .= "Status: " . ucfirst($task['status']) . "\n";
                $content .= "Priority: " . ucfirst($task['priority']) . "\n";
                $content .= "Category: " . ($task['category'] ?? 'None') . "\n";
                $content .= "Due Date: " . ($task['due_date'] ?? 'None') . "\n";
                
                if (!empty($task['description'])) {
                    $content .= "Description: " . $task['description'] . "\n";
                }
                
                $content .= "\n" . str_repeat("-", 50) . "\n\n";
            }
            
            echo $content;
        }
    }
}