<?php
namespace App\Models;

use PDO;
use Exception;

class TagsModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new tag
     */
    public function createTag(int $userId, string $name, string $color = '#3b82f6'): int|false {
        try {
            // Check if tag already exists
            $stmt = $this->db->prepare("SELECT id FROM tags WHERE user_id = ? AND name = ?");
            $stmt->execute([$userId, $name]);
            if ($stmt->fetch()) {
                return false; // Tag already exists
            }

            $sql = "INSERT INTO tags (user_id, name, color, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$userId, $name, $color])) {
                return (int)$this->db->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error creating tag: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tags for a user
     */
    public function getTagsByUserId(int $userId): array {
        try {
            $sql = "SELECT * FROM tags WHERE user_id = ? ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting tags: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get tag by ID
     */
    public function getTagById(int $tagId, int $userId): array|false {
        try {
            $sql = "SELECT * FROM tags WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tagId, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting tag by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update tag
     */
    public function updateTag(int $tagId, int $userId, string $name, string $color): bool {
        try {
            $sql = "UPDATE tags SET name = ?, color = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $color, $tagId, $userId]);
        } catch (Exception $e) {
            error_log("Error updating tag: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete tag
     */
    public function deleteTag(int $tagId, int $userId): bool {
        try {
            // First remove all associations
            $stmt = $this->db->prepare("DELETE FROM note_tags WHERE tag_id = ?");
            $stmt->execute([$tagId]);
            
            $stmt = $this->db->prepare("DELETE FROM task_tags WHERE tag_id = ?");
            $stmt->execute([$tagId]);
            
            // Then delete the tag
            $sql = "DELETE FROM tags WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$tagId, $userId]);
        } catch (Exception $e) {
            error_log("Error deleting tag: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tag usage statistics
     */
    public function getTagStats(int $userId): array {
        try {
            $sql = "
                SELECT 
                    t.id,
                    t.name,
                    t.color,
                    COUNT(DISTINCT nt.note_id) as note_count,
                    COUNT(DISTINCT tt.task_id) as task_count
                FROM tags t
                LEFT JOIN note_tags nt ON t.id = nt.tag_id
                LEFT JOIN task_tags tt ON t.id = tt.tag_id
                WHERE t.user_id = ?
                GROUP BY t.id, t.name, t.color
                ORDER BY (note_count + task_count) DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting tag stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search tags
     */
    public function searchTags(int $userId, string $query): array {
        try {
            $searchTerm = '%' . $query . '%';
            $sql = "SELECT * FROM tags WHERE user_id = ? AND name LIKE ? ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error searching tags: " . $e->getMessage());
            return [];
        }
    }
}

