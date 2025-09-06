<?php

namespace App\Controllers;

use Core\Session;
use PDO;
use Core\Database;

class NotesController {
    private $db;
    private $auditLogger; // Property to hold the audit logger instance

    // Constructor updated to accept both Database and AuditLogsController
     public function __construct(PDO $db, AuditLogsController $auditLogger) {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Ensures all provided tag names exist in the database and returns their IDs.
     * Creates new tags if they don't exist.
     * @param array $tagNames The array of tag names from the form.
     * @return array An array of corresponding tag IDs.
     */
    private function ensureTagsExist(array $tagNames, int $userId): array
    {
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            // Check if tag already exists for the user
            $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = :name AND user_id = :uid");
            $stmt->execute([':name' => $tagName, ':uid' => $userId]);
            $existingTag = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingTag) {
                $tagIds[] = $existingTag['id'];
            } else {
                // If not, create the new tag
                $stmt = $this->db->prepare("INSERT INTO tags (name, user_id) VALUES (:name, :uid)");
                $stmt->execute([':name' => $tagName, ':uid' => $userId]);
                $tagIds[] = $this->db->lastInsertId();
            }
        }
        return $tagIds;
    }

    // Show all notes for logged-in user
    public function index() {
        $userId = Session::get('user_id');

        // Prepare and execute a query to get notes with their associated tags
        $stmtNotes = $this->db->prepare("
    SELECT
        n.id,
        n.user_id,
        n.title,
        n.content,
        n.is_archived,
        n.created_at,
        n.updated_at,
        GROUP_CONCAT(t.name SEPARATOR ',') AS tags,
        GROUP_CONCAT(t.id SEPARATOR ',') AS tag_ids
    FROM notes n
    LEFT JOIN note_tags nt ON n.id = nt.note_id
    LEFT JOIN tags t ON nt.tag_id = t.id
    WHERE n.user_id = :uid
      AND n.is_archived = 0
    GROUP BY n.id
    ORDER BY n.created_at DESC
");
$stmtNotes->execute([':uid' => $userId]);
$notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);


        // Fetch all tags for the user to populate the filter dropdown
        $stmtTags = $this->db->prepare("
            SELECT id, name FROM tags WHERE user_id = :uid
        ");
        $stmtTags->execute([':uid' => $userId]);
        $allTags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);

        $tags = $allTags;

        include __DIR__ . '/../Views/notes.php';
    }

    // Handle new note creation
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $tagNames = $_POST['tags'] ?? []; // Array of tag names/IDs

            if (!$title || !$content) {
                $_SESSION['errors'] = ["Both title and content are required."];
                header("Location: /notes");
                exit;
            }

            // Insert the note and get the last inserted ID
            $stmt = $this->db->prepare("
                INSERT INTO notes (user_id, title, content)
                VALUES (:user_id, :title, :content)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':title'   => $title,
                ':content' => $content
            ]);
            $noteId = $this->db->lastInsertId();

            // Insert associations into the note_tags table
            if (!empty($tagNames) && $noteId) {
                foreach ($tagNames as $tagId) { // The frontend sends IDs now, so we can use them directly
                    $stmtTag = $this->db->prepare("
                        INSERT INTO note_tags (note_id, tag_id)
                        VALUES (:note_id, :tag_id)
                    ");
                    $stmtTag->execute([
                        ':note_id' => $noteId,
                        ':tag_id'  => $tagId
                    ]);
                }
            }

            // Add audit log entry using the injected logger
            $this->auditLogger->logEvent($userId, 'created note: ' . $title);

            $_SESSION['success'] = "Note added successfully!";
            header("Location: /notes");
            exit;
        }
    }    

    // Update existing note
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $_POST['id'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $tagIds = $_POST['tags'] ?? []; // Array of tag IDs
            $userId = Session::get('user_id');

            // Update the note's title and content
            $stmt = $this->db->prepare("
                UPDATE notes
                SET title = :title, content = :content, updated_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                ':id'      => $noteId,
                ':user_id' => $userId,
                ':title'   => $title,
                ':content' => $content
            ]);

            // First, delete existing tag associations for the note
            $stmtDeleteTags = $this->db->prepare("
                DELETE FROM note_tags WHERE note_id = :note_id
            ");
            $stmtDeleteTags->execute([':note_id' => $noteId]);

            // Then, insert new tag associations
            if (!empty($tagIds)) {
                // Prepare a check to ensure tag exists before linking
                $stmtCheckTag = $this->db->prepare("
                    SELECT id FROM tags WHERE id = :tag_id
                ");

                foreach ($tagIds as $tagId) {
                    $stmtCheckTag->execute([':tag_id' => $tagId]);
                    $tagExists = $stmtCheckTag->fetch();

                    if ($tagExists) {
                        $stmtTag = $this->db->prepare("
                            INSERT INTO note_tags (note_id, tag_id)
                            VALUES (:note_id, :tag_id)
                        ");
                        $stmtTag->execute([
                            ':note_id' => $noteId,
                            ':tag_id'  => $tagId
                        ]);
                    }
                }
            }

            // Add audit log entry using the injected logger
            $this->auditLogger->logEvent($userId, 'updated note with ID: ' . $noteId);

            $_SESSION['success'] = "Note updated successfully!";
            header("Location: /notes");
            exit;
        }
    }
    
    // Archive a note
    public function archive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $_SESSION['errors'] = ["Invalid note ID."];
                header("Location: /notes");
                exit;
            }

            $stmt = $this->db->prepare("
                UPDATE notes 
                SET is_archived = 1 
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':id' => $noteId,
                ':uid' => $userId
            ]);

            // Add audit log entry for archiving using the injected logger
            $this->auditLogger->logEvent($userId, 'archived note with ID: ' . $noteId);

            $_SESSION['success'] = "Note archived successfully!";
            header("Location: /notes");
            exit;
        }
    }
    
    // Delete a note
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $_SESSION['errors'] = ["Invalid note ID."];
                header("Location: /notes");
                exit;
            }

            // First, delete the note's tag associations to prevent orphaned data
            $stmtDeleteTags = $this->db->prepare("
                DELETE FROM note_tags WHERE note_id = :note_id
            ");
            $stmtDeleteTags->execute([':note_id' => $noteId]);

            // Then, delete the note itself
            $stmt = $this->db->prepare("
                DELETE FROM notes
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                ':id' => $noteId,
                ':uid' => $userId
            ]);

            // Add audit log entry using the injected logger
            $this->auditLogger->logEvent($userId, 'deleted note with ID: ' . $noteId);

            $_SESSION['success'] = "Note deleted successfully!";
            header("Location: /notes");
            exit;
        }
    }
}