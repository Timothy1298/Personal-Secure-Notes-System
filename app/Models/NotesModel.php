<?php

namespace App\Models;

class NotesModel {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getNotesByUserId($userId) {
        $sql = "SELECT id, title, content, created_at, updated_at FROM notes WHERE user_id = :user_id AND is_archived = 0 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}