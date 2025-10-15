<?php

namespace App\Models;

class SettingsModel {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getSettingsByUserId($userId) {
        $sql = "SELECT * FROM settings WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$settings) {
            return [
                'theme' => 'light',
                'font_size' => 16,
                'note_layout' => 'grid',
                'is_2fa_enabled' => 0,
                'is_email_notifications_enabled' => 1,
                'is_desktop_notifications_enabled' => 0,
                'is_automatic_archiving_enabled' => 0,
                'is_auto_empty_trash_enabled' => 0,
                'audit_log_retention' => 90,
            ];
        }
        return $settings;
    }

    public function updateSettings($userId, $data) {
        // Your existing update logic here
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $columnName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $fields[] = "`$columnName` = :$columnName";
            $params[":$columnName"] = $value;
        }
        $params[':user_id'] = $userId;
        
        $sql = "INSERT INTO settings (" . implode(', ', array_keys($params)) . ") VALUES (" . implode(', ', array_keys($params)) . ") ON DUPLICATE KEY UPDATE " . implode(', ', $fields);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    // New method for changing password
    public function changePassword($userId, $oldPassword, $newPassword) {
        $sql = "SELECT password FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($oldPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password = :new_password WHERE id = :user_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindValue(':new_password', $hashedPassword);
            $updateStmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            return $updateStmt->execute();
        }
        return false;
    }

    // New method for importing data
    public function importData($userId, $data) {
        try {
            $this->db->beginTransaction();

            if (isset($data['notes']) && is_array($data['notes'])) {
                $notesSql = "INSERT INTO notes (user_id, title, content, created_at) VALUES (:user_id, :title, :content, :created_at)";
                $notesStmt = $this->db->prepare($notesSql);
                foreach ($data['notes'] as $note) {
                    $notesStmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                    $notesStmt->bindValue(':title', $note['title']);
                    $notesStmt->bindValue(':content', $note['content']);
                    $notesStmt->bindValue(':created_at', $note['created_at']);
                    $notesStmt->execute();
                }
            }

            if (isset($data['tasks']) && is_array($data['tasks'])) {
                $tasksSql = "INSERT INTO tasks (user_id, title, description, status, created_at) VALUES (:user_id, :title, :description, :status, :created_at)";
                $tasksStmt = $this->db->prepare($tasksSql);
                foreach ($data['tasks'] as $task) {
                    $status = isset($task['is_completed']) && $task['is_completed'] ? 'completed' : 'pending';
                    $title = isset($task['title']) ? $task['title'] : 'Imported Task';
                    $tasksStmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                    $tasksStmt->bindValue(':title', $title);
                    $tasksStmt->bindValue(':description', $task['description']);
                    $tasksStmt->bindValue(':status', $status);
                    $tasksStmt->bindValue(':created_at', $task['created_at']);
                    $tasksStmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // New method for deleting account
    public function deleteAccount($userId) {
        try {
            $this->db->beginTransaction();
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}