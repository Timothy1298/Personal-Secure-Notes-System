<?php

namespace App\Models;

class TasksModel {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getTasksByUserId($userId) {
        $sql = "SELECT id, description, is_completed, created_at FROM tasks WHERE user_id = :user_id AND is_archived = 0 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}