<?php

namespace App\Models;

class TasksModel {
    private $db;
    private $encryptionKey;
    
    // --- SECURITY CONSTANTS ---
    private const CIPHER_METHOD = 'aes-256-gcm';
    // This key is used only if the config key is not loaded.
    private const FALLBACK_KEY = 'a_very_secure_32_byte_key_for_tasks'; // Adjusted fallback key
    
    public function __construct(\PDO $db) {
        $this->db = $db;
        
        // **CRITICAL SECURITY STEP:** Attempt to load key from global config
        // This assumes your bootstrap file loads config/config.php and defines APP_ENCRYPTION_KEY
        $this->encryptionKey = defined('APP_ENCRYPTION_KEY') ? APP_ENCRYPTION_KEY : self::FALLBACK_KEY;
    }
    
    // =======================================================
    // ENCRYPTION / DECRYPTION HELPERS
    // =======================================================
    
    private function encryptContent(string $content): string|false {
        $key = $this->encryptionKey;
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $tag = '';
        
        $ciphertext = openssl_encrypt(
            $content,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '', 
            16 // Tag length for GCM
        );
        
        if ($ciphertext === false) {
            return false;
        }

        // Store IV, Tag, and Ciphertext together, separated by colons, and Base64 encode for database storage
        return base64_encode($iv . ':' . $tag . ':' . $ciphertext);
    }
    
    private function decryptContent(string $data): string|false {
        $key = $this->encryptionKey;
        
        // Check if data is encrypted (contains colons after base64 decode)
        $decoded = base64_decode($data);
        $parts = explode(':', $decoded, 3);
        
        if (count($parts) !== 3) {
            // Data is not encrypted, return as-is
            return $data;
        }

        list($iv, $tag, $ciphertext) = $parts;
        
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if (strlen($iv) !== $iv_length) {
            return false; 
        }
        
        $tag_length = 16;
        if (strlen($tag) !== $tag_length) {
            return false; 
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $plaintext;
    }

    // =======================================================
    // CRUD METHODS
    // =======================================================
    
    /**
     * Fetches and decrypts all non-archived tasks for a user.
     */
    public function getTasksByUserId(int $userId): array {
         $sql = "
            SELECT
                id, user_id, title, description, status, priority, progress, due_date, completed_at, is_archived, created_at, updated_at
            FROM tasks
            WHERE user_id = :uid AND is_archived = 0 AND is_deleted = 0
            ORDER BY created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $encryptedTasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $decryptedTasks = [];
        
        foreach ($encryptedTasks as $task) {
            // FIX: Use null coalescing operator (?? '') to ensure $this->decryptContent 
            // always receives a string, even if the DB column returns NULL (as with old records).
            $task['title'] = $this->decryptContent($task['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $task['description'] = $this->decryptContent($task['description'] ?? '') ?? '[DECRYPTION FAILED]';
            $decryptedTasks[] = $task;
        }
        
        return $decryptedTasks;
    }

    /**
     * Creates a new task, encrypting the title and description before insertion.
     */
    public function createTask(int $userId, string $title, string $description): int|false {
        $encryptedTitle = $this->encryptContent($title);
        $encryptedDescription = $this->encryptContent($description);

        if ($encryptedTitle === false || $encryptedDescription === false) {
            error_log("Encryption failed for user {$userId} during task creation");
            return false;
        }
        
        $sql = "INSERT INTO tasks (user_id, title, description, status, is_archived) VALUES (:user_id, :title, :description, 'pending', 0)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            ':user_id' => $userId,
            ':title' => $encryptedTitle,
            ':description' => $encryptedDescription
        ])) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Updates an existing task, encrypting the new content before update.
     */
    public function updateTask(int $taskId, int $userId, string $title, string $description): bool {
        $encryptedTitle = $this->encryptContent($title);
        $encryptedDescription = $this->encryptContent($description);
        
        if ($encryptedTitle === false || $encryptedDescription === false) {
            error_log("Encryption failed during task update for ID {$taskId}");
            return false;
        }

        $sql = "
            UPDATE tasks
            SET title = :title, description = :description, updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $taskId,
            ':user_id' => $userId,
            ':title' => $encryptedTitle,
            ':description' => $encryptedDescription
        ]);
    }
    
    /**
     * Sets the completion status of a task.
     */
    public function setTaskCompletionStatus(int $taskId, int $userId, bool $isCompleted): bool {
        $status = $isCompleted ? 'completed' : 'pending';
        $completedAt = $isCompleted ? 'NOW()' : 'NULL';
        
        $sql = "
            UPDATE tasks
            SET status = :status, completed_at = {$completedAt}, updated_at = NOW()
            WHERE id = :id AND user_id = :uid
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $taskId,
            ':uid' => $userId,
            ':status' => $status
        ]);
    }

    /**
     * Archives a task.
     */
    public function setTaskArchiveStatus(int $taskId, int $userId, bool $isArchived): bool {
        $sql = "UPDATE tasks SET is_archived = :is_archived WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $taskId,
            ':uid' => $userId,
            ':is_archived' => (int)$isArchived
        ]);
    }

    /**
     * Permanently deletes a task.
     */
    public function deleteTask(int $taskId, int $userId): bool {
        $sql = "DELETE FROM tasks WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $taskId, ':uid' => $userId]);
    }

    // =======================================================
    // ADVANCED TASK FEATURES
    // =======================================================

    /**
     * Get tasks with tags and subtasks by user ID
     */
    public function getTasksWithTagsByUserId(int $userId): array
    {
        $sql = "SELECT t.*, 
                       GROUP_CONCAT(DISTINCT tg.name) as tags,
                       GROUP_CONCAT(DISTINCT tg.id) as tag_ids,
                       COUNT(DISTINCT st.id) as subtask_count,
                       COUNT(DISTINCT CASE WHEN st.status = 'completed' THEN st.id END) as completed_subtasks
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags tg ON tt.tag_id = tg.id
                LEFT JOIN subtasks st ON t.id = st.task_id AND st.deleted_at IS NULL
                WHERE t.user_id = ? AND t.deleted_at IS NULL AND t.is_deleted = 0
                GROUP BY t.id
                ORDER BY t.priority DESC, t.due_date ASC, t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt task data
        foreach ($tasks as &$task) {
            $task['title'] = $this->decryptContent($task['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $task['description'] = $this->decryptContent($task['description'] ?? '') ?? '[DECRYPTION FAILED]';
            
            // Calculate progress percentage
            if ($task['subtask_count'] > 0) {
                $task['progress_percentage'] = round(($task['completed_subtasks'] / $task['subtask_count']) * 100);
            } else {
                $task['progress_percentage'] = $task['status'] === 'completed' ? 100 : 0;
            }
            
            // Check if overdue
            if ($task['due_date'] && $task['status'] !== 'completed') {
                $task['is_overdue'] = strtotime($task['due_date']) < time();
            } else {
                $task['is_overdue'] = false;
            }
        }
        
        return $tasks;
    }

    /**
     * Create task with advanced features
     */
    public function createAdvancedTask(int $userId, array $taskData): int|false
    {
        $encryptedTitle = $this->encryptContent($taskData['title']);
        $encryptedDescription = $this->encryptContent($taskData['description'] ?? '');

        if ($encryptedTitle === false || $encryptedDescription === false) {
            error_log("Encryption failed for user {$userId} during task creation");
            return false;
        }

        $sql = "INSERT INTO tasks (user_id, title, description, status, priority, due_date, category, 
                                  is_recurring, recurrence_pattern, reminder_date, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $userId,
            $encryptedTitle,
            $encryptedDescription,
            $taskData['status'] ?? 'pending',
            $taskData['priority'] ?? 'medium',
            $taskData['due_date'] ?? null,
            $taskData['category'] ?? null,
            $taskData['is_recurring'] ?? 0,
            $taskData['recurrence_pattern'] ?? null,
            $taskData['reminder_date'] ?? null
        ]);

        if ($result) {
            $taskId = (int)$this->db->lastInsertId();
            
            // Add tags if provided
            if (!empty($taskData['tags'])) {
                $this->addTaskTags($taskId, $taskData['tags']);
            }
            
            return $taskId;
        }
        
        return false;
    }

    /**
     * Update task with advanced features
     */
    public function updateAdvancedTask(int $taskId, int $userId, array $taskData): bool
    {
        $encryptedTitle = $this->encryptContent($taskData['title']);
        $encryptedDescription = $this->encryptContent($taskData['description'] ?? '');

        if ($encryptedTitle === false || $encryptedDescription === false) {
            error_log("Encryption failed during task update for ID {$taskId}");
            return false;
        }

        $sql = "UPDATE tasks SET 
                    title = ?, description = ?, status = ?, priority = ?, due_date = ?, 
                    category = ?, is_recurring = ?, recurrence_pattern = ?, reminder_date = ?, 
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $encryptedTitle,
            $encryptedDescription,
            $taskData['status'] ?? 'pending',
            $taskData['priority'] ?? 'medium',
            $taskData['due_date'] ?? null,
            $taskData['category'] ?? null,
            $taskData['is_recurring'] ?? 0,
            $taskData['recurrence_pattern'] ?? null,
            $taskData['reminder_date'] ?? null,
            $taskId,
            $userId
        ]);

        if ($result && !empty($taskData['tags'])) {
            // Update tags
            $this->removeTaskTags($taskId);
            $this->addTaskTags($taskId, $taskData['tags']);
        }

        return $result;
    }

    /**
     * Get tasks for Kanban board
     */
    public function getTasksForKanban(int $userId): array
    {
        $tasks = $this->getTasksWithTagsByUserId($userId);
        
        $kanban = [
            'pending' => [],
            'in_progress' => [],
            'completed' => [],
            'overdue' => []
        ];

        foreach ($tasks as $task) {
            if ($task['is_overdue']) {
                $kanban['overdue'][] = $task;
            } else {
                $kanban[$task['status']][] = $task;
            }
        }

        return $kanban;
    }

    /**
     * Get tasks for calendar view
     */
    public function getTasksForCalendar(int $userId, string $startDate, string $endDate): array
    {
        $sql = "SELECT t.*, 
                       GROUP_CONCAT(DISTINCT tg.name) as tags
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags tg ON tt.tag_id = tg.id
                WHERE t.user_id = ? AND t.deleted_at IS NULL AND t.is_deleted = 0 
                AND t.due_date BETWEEN ? AND ?
                GROUP BY t.id
                ORDER BY t.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $startDate, $endDate]);
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt task data
        foreach ($tasks as &$task) {
            $task['title'] = $this->decryptContent($task['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $task['description'] = $this->decryptContent($task['description'] ?? '') ?? '[DECRYPTION FAILED]';
        }
        
        return $tasks;
    }

    /**
     * Add tags to task
     */
    public function addTaskTags(int $taskId, array $tagIds): bool
    {
        $sql = "INSERT INTO task_tags (task_id, tag_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($tagIds as $tagId) {
            if (!$stmt->execute([$taskId, $tagId])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Remove all tags from task
     */
    public function removeTaskTags(int $taskId): bool
    {
        $sql = "DELETE FROM task_tags WHERE task_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId]);
    }

    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId, string $status, int $userId): bool
    {
        $sql = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $taskId, $userId]);
    }

    /**
     * Update task priority
     */
    public function updateTaskPriority(int $taskId, int $userId, string $priority): bool
    {
        $sql = "UPDATE tasks SET priority = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$priority, $taskId, $userId]);
    }

    /**
     * Get task statistics
     */
    public function getTaskStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_tasks,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_tasks,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_tasks,
                    COUNT(CASE WHEN due_date < NOW() AND status != 'completed' THEN 1 END) as overdue_tasks,
                    COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent_tasks,
                    COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority_tasks,
                    COUNT(CASE WHEN is_recurring = 1 THEN 1 END) as recurring_tasks
                FROM tasks 
                WHERE user_id = ? AND deleted_at IS NULL AND is_deleted = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Search tasks with filters
     */
    public function searchTasks(int $userId, string $query = '', array $filters = []): array
    {
        $sql = "SELECT t.*, 
                       GROUP_CONCAT(DISTINCT tg.name) as tags,
                       GROUP_CONCAT(DISTINCT tg.id) as tag_ids
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags tg ON tt.tag_id = tg.id
                WHERE t.user_id = ? AND t.deleted_at IS NULL AND t.is_deleted = 0";
        
        $params = [$userId];
        
        // Add search query
        if (!empty($query)) {
            $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add filters
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND t.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['tags'])) {
            $sql .= " AND tt.tag_id IN (" . implode(',', array_fill(0, count($filters['tags']), '?')) . ")";
            $params = array_merge($params, $filters['tags']);
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.due_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.due_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " GROUP BY t.id ORDER BY t.priority DESC, t.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt task data
        foreach ($tasks as &$task) {
            $task['title'] = $this->decryptContent($task['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $task['description'] = $this->decryptContent($task['description'] ?? '') ?? '[DECRYPTION FAILED]';
        }
        
        return $tasks;
    }

    // =======================================================
    // SUBTASK METHODS
    // =======================================================

    /**
     * Create subtask
     */
    public function createSubtask(int $taskId, int $userId, string $title, string $description = ''): int|false
    {
        $encryptedTitle = $this->encryptContent($title);
        $encryptedDescription = $this->encryptContent($description);

        if ($encryptedTitle === false || $encryptedDescription === false) {
            return false;
        }

        $sql = "INSERT INTO subtasks (task_id, user_id, title, description, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$taskId, $userId, $encryptedTitle, $encryptedDescription]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Get subtasks for a task
     */
    public function getSubtasks(int $taskId, int $userId): array
    {
        $sql = "SELECT * FROM subtasks WHERE task_id = ? AND user_id = ? AND deleted_at IS NULL ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$taskId, $userId]);
        $subtasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt subtask data
        foreach ($subtasks as &$subtask) {
            $subtask['title'] = $this->decryptContent($subtask['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $subtask['description'] = $this->decryptContent($subtask['description'] ?? '') ?? '[DECRYPTION FAILED]';
        }
        
        return $subtasks;
    }

    /**
     * Update subtask
     */
    public function updateSubtask(int $subtaskId, int $userId, array $data): bool
    {
        $encryptedTitle = $this->encryptContent($data['title']);
        $encryptedDescription = $this->encryptContent($data['description'] ?? '');

        if ($encryptedTitle === false || $encryptedDescription === false) {
            return false;
        }

        $sql = "UPDATE subtasks SET 
                    title = ?, description = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $encryptedTitle,
            $encryptedDescription,
            $data['status'] ?? 'pending',
            $subtaskId,
            $userId
        ]);
    }

    /**
     * Delete subtask
     */
    public function deleteSubtask(int $subtaskId, int $userId): bool
    {
        $sql = "UPDATE subtasks SET deleted_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$subtaskId, $userId]);
    }

    // =======================================================
    // RECURRING TASKS
    // =======================================================

    /**
     * Process recurring tasks
     */
    public function processRecurringTasks(): int
    {
        $sql = "SELECT * FROM tasks WHERE is_recurring = 1 AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $recurringTasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $created = 0;
        
        foreach ($recurringTasks as $task) {
            if ($this->shouldCreateRecurringTask($task)) {
                $newTaskId = $this->createRecurringTaskInstance($task);
                if ($newTaskId) {
                    $created++;
                }
            }
        }
        
        return $created;
    }

    /**
     * Check if recurring task should create new instance
     */
    private function shouldCreateRecurringTask(array $task): bool
    {
        $lastCreated = $task['last_recurrence'] ?? $task['created_at'];
        $pattern = $task['recurrence_pattern'];
        
        switch ($pattern) {
            case 'daily':
                return strtotime($lastCreated) < strtotime('-1 day');
            case 'weekly':
                return strtotime($lastCreated) < strtotime('-1 week');
            case 'monthly':
                return strtotime($lastCreated) < strtotime('-1 month');
            case 'yearly':
                return strtotime($lastCreated) < strtotime('-1 year');
            default:
                return false;
        }
    }

    /**
     * Create new instance of recurring task
     */
    private function createRecurringTaskInstance(array $originalTask): int|false
    {
        $sql = "INSERT INTO tasks (user_id, title, description, status, priority, category, 
                                  is_recurring, recurrence_pattern, due_date, created_at, updated_at) 
                VALUES (?, ?, ?, 'pending', ?, ?, 0, NULL, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $originalTask['user_id'],
            $originalTask['title'],
            $originalTask['description'],
            $originalTask['priority'],
            $originalTask['category'],
            $this->calculateNextDueDate($originalTask)
        ]);
        
        if ($result) {
            // Update last recurrence date
            $this->updateLastRecurrence($originalTask['id']);
            return (int)$this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Calculate next due date for recurring task
     */
    private function calculateNextDueDate(array $task): string
    {
        $currentDue = $task['due_date'] ?: $task['created_at'];
        $pattern = $task['recurrence_pattern'];
        
        switch ($pattern) {
            case 'daily':
                return date('Y-m-d H:i:s', strtotime($currentDue . ' +1 day'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime($currentDue . ' +1 week'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime($currentDue . ' +1 month'));
            case 'yearly':
                return date('Y-m-d H:i:s', strtotime($currentDue . ' +1 year'));
            default:
                return $currentDue;
        }
    }

    /**
     * Update last recurrence date
     */
    private function updateLastRecurrence(int $taskId): bool
    {
        $sql = "UPDATE tasks SET last_recurrence = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId]);
    }

    /**
     * Get subtasks by task ID
     */
    public function getSubtasksByTaskId(int $taskId, int $userId): array
    {
        $sql = "SELECT st.* FROM subtasks st 
                JOIN tasks t ON st.task_id = t.id 
                WHERE st.task_id = ? AND t.user_id = ? 
                ORDER BY st.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$taskId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }




    /**
     * Get tasks by IDs
     */
    public function getTasksByIds(array $taskIds, int $userId): array
    {
        if (empty($taskIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($taskIds) - 1) . '?';
        $sql = "SELECT * FROM tasks WHERE id IN ($placeholders) AND user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $params = array_merge($taskIds, [$userId]);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // =======================================================
    // TRASH SYSTEM METHODS
    // =======================================================

    /**
     * Get deleted tasks for trash view
     */
    public function getDeletedTasks(int $userId): array
    {
        $sql = "SELECT * FROM tasks WHERE user_id = ? AND is_deleted = 1 ORDER BY deleted_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt task data
        foreach ($tasks as &$task) {
            $task['title'] = $this->decryptContent($task['title'] ?? '') ?? '[DECRYPTION FAILED]';
            $task['description'] = $this->decryptContent($task['description'] ?? '') ?? '[DECRYPTION FAILED]';
        }
        
        return $tasks;
    }

    /**
     * Restore task from trash
     */
    public function restoreTask(int $taskId, int $userId): bool
    {
        $sql = "UPDATE tasks SET is_deleted = 0, deleted_at = NULL WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $userId]);
    }

    /**
     * Permanently delete task from trash
     */
    public function permanentDeleteTask(int $taskId, int $userId): bool
    {
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $userId]);
    }

    /**
     * Empty trash - permanently delete all deleted tasks
     */
    public function emptyTrash(int $userId): int
    {
        $sql = "DELETE FROM tasks WHERE user_id = ? AND is_deleted = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    /**
     * Auto-cleanup old deleted tasks (older than specified days)
     */
    public function autoCleanupTrash(int $userId, int $daysOld = 30): int
    {
        $sql = "DELETE FROM tasks WHERE user_id = ? AND is_deleted = 1 AND deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $daysOld]);
        return $stmt->rowCount();
    }

    /**
     * Get trash statistics
     */
    public function getTrashStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_deleted,
                    COUNT(CASE WHEN deleted_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as deleted_last_week,
                    COUNT(CASE WHEN deleted_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as deleted_last_month
                FROM tasks 
                WHERE user_id = ? AND is_deleted = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get task by title for import validation
     */
    public function getTaskByTitle(int $userId, string $title): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tasks 
                WHERE user_id = ? AND title = ? AND is_deleted = 0
            ");
            $stmt->execute([$userId, $title]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting task by title: " . $e->getMessage());
            return false;
        }
    }

}
