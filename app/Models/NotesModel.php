<?php

namespace App\Models;

use PDO;
use Exception;

class NotesModel {
    private $db;
    private $encryptionKey;
    
    // --- SECURITY CONSTANTS ---
    private const CIPHER_METHOD = 'aes-256-gcm';
    // This key is used only if the config key is not loaded.
    private const FALLBACK_KEY = 'a_very_secure_32_byte_key_for_notes'; 

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
        
        $decoded = base64_decode($data);
        $parts = explode(':', $decoded, 3);
        
        if (count($parts) !== 3) {
            return false; 
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
     * Fetches and decrypts all non-archived notes for a user, including associated tags.
     */
    public function getNotesWithTagsByUserId(int $userId): array {
         $sql = "
            SELECT
                n.id, n.user_id, n.title, n.content, n.summary, n.priority, n.color, n.is_pinned, n.is_archived, n.word_count, n.read_time, n.created_at, n.updated_at,
                GROUP_CONCAT(t.name SEPARATOR ',') AS tags,
                GROUP_CONCAT(t.id SEPARATOR ',') AS tag_ids
            FROM notes n
            LEFT JOIN note_tags nt ON n.id = nt.note_id
            LEFT JOIN tags t ON nt.tag_id = t.id
            WHERE n.user_id = :uid AND n.is_archived = 0 AND n.is_deleted = 0
            GROUP BY n.id
            ORDER BY n.is_pinned DESC, n.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $encryptedNotes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $decryptedNotes = [];
        
        foreach ($encryptedNotes as $note) {
            // Decrypt content after fetching from DB
            $note['content'] = $this->decryptContent($note['content']) ?? '[DECRYPTION FAILED]';
            $decryptedNotes[] = $note;
        }
        
        return $decryptedNotes;
    }

    /**
     * Creates a new note, encrypting the content before insertion.
     */
    public function createNote(int $userId, string $title, string $content, string $summary = '', string $priority = 'medium', string $category = 'general', string $color = '#ffffff', int $isPinned = 0): int|false {
        $encryptedContent = $this->encryptContent($content);
        
        if ($encryptedContent === false) {
            error_log("Encryption failed for user {$userId}");
            return false;
        }
        
        $sql = "INSERT INTO notes (user_id, title, content, summary, priority, category, color, is_pinned) VALUES (:user_id, :title, :content, :summary, :priority, :category, :color, :is_pinned)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $encryptedContent,
            ':summary' => $summary,
            ':priority' => $priority,
            ':category' => $category,
            ':color' => $color,
            ':is_pinned' => $isPinned
        ])) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Updates an existing note, encrypting the new content before update.
     */
    public function updateNote(int $noteId, int $userId, string $title, string $content, string $summary = '', string $priority = 'medium', string $category = 'general', string $color = '#ffffff', int $isPinned = 0): bool {
        $encryptedContent = $this->encryptContent($content);
        
        if ($encryptedContent === false) {
            error_log("Encryption failed during update for note ID {$noteId}");
            return false;
        }

        $sql = "
            UPDATE notes
            SET title = :title, content = :content, summary = :summary, priority = :priority, category = :category, color = :color, is_pinned = :is_pinned, updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $noteId,
            ':user_id' => $userId,
            ':title' => $title,
            ':content' => $encryptedContent,
            ':summary' => $summary,
            ':priority' => $priority,
            ':category' => $category,
            ':color' => $color,
            ':is_pinned' => $isPinned
        ]);
    }
    
    /**
     * Archives a note.
     */
    public function archiveNote(int $noteId, int $userId): bool {
        $sql = "UPDATE notes SET is_archived = 1 WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':uid' => $userId]);
    }
    
    /**
     * Unarchives a note.
     */
    public function unarchiveNote(int $noteId, int $userId): bool {
        $sql = "UPDATE notes SET is_archived = 0 WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':uid' => $userId]);
    }

    /**
     * Permanently deletes a note.
     */
    public function deleteNote(int $noteId, int $userId): bool {
        $sql = "DELETE FROM notes WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':uid' => $userId]);
    }

    /**
     * Create a new version of a note
     */
    public function createVersion(int $noteId, int $userId, string $title, string $content): bool {
        // Get current version number
        $stmt = $this->db->prepare("SELECT MAX(version_number) as max_version FROM note_versions WHERE note_id = :note_id");
        $stmt->execute([':note_id' => $noteId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $versionNumber = ($result['max_version'] ?? 0) + 1;

        $sql = "INSERT INTO note_versions (note_id, version_number, title, content) VALUES (:note_id, :version_number, :title, :content)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':note_id' => $noteId,
            ':version_number' => $versionNumber,
            ':title' => $title,
            ':content' => $content
        ]);
    }

    /**
     * Get note versions
     */
    public function getNoteVersions(int $noteId, int $userId): array {
        // First verify the note belongs to the user
        $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
        $stmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
        
        if (!$stmt->fetch()) {
            return [];
        }

        $sql = "SELECT * FROM note_versions WHERE note_id = :note_id ORDER BY version_number DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':note_id' => $noteId]);
        
        $versions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt content for each version
        foreach ($versions as &$version) {
            $version['content'] = $this->decryptContent($version['content']) ?? '[DECRYPTION FAILED]';
        }
        
        return $versions;
    }

    /**
     * Restore a note version
     */
    public function restoreVersion(int $noteId, int $userId, int $versionNumber): bool {
        // Get the version
        $stmt = $this->db->prepare("SELECT title, content FROM note_versions WHERE note_id = :note_id AND version_number = :version_number");
        $stmt->execute([':note_id' => $noteId, ':version_number' => $versionNumber]);
        $version = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$version) {
            return false;
        }

        // Encrypt the content before updating
        $encryptedContent = $this->encryptContent($version['content']);
        if ($encryptedContent === false) {
            return false;
        }

        // Update the note
        $sql = "UPDATE notes SET title = :title, content = :content, updated_at = NOW() WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $noteId,
            ':user_id' => $userId,
            ':title' => $version['title'],
            ':content' => $encryptedContent
        ]);
    }

    /**
     * Add attachment to note
     */
    public function addAttachment(int $noteId, int $userId, array $file): int|false {
        // Verify note belongs to user
        $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
        $stmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
        
        if (!$stmt->fetch()) {
            return false;
        }

        // Basic file validation
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 10 * 1024 * 1024) { // 10MB limit
            return false;
        }

        // Generate secure filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $storedFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadPath = __DIR__ . '/../../storage/attachments/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filePath = $uploadPath . $storedFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return false;
        }

        // Encrypt file content
        $fileContent = file_get_contents($filePath);
        $encryptedContent = $this->encryptContent($fileContent);
        if ($encryptedContent === false) {
            unlink($filePath);
            return false;
        }

        // Save encrypted content
        file_put_contents($filePath, $encryptedContent);

        // Save to database
        $sql = "INSERT INTO note_attachments (note_id, original_filename, stored_filename, file_path, file_size, mime_type, file_hash) VALUES (:note_id, :original_filename, :stored_filename, :file_path, :file_size, :mime_type, :file_hash)";
        $stmt = $this->db->prepare($sql);
        
        $fileHash = hash_file('sha256', $filePath);
        
        if ($stmt->execute([
            ':note_id' => $noteId,
            ':original_filename' => $file['name'],
            ':stored_filename' => $storedFilename,
            ':file_path' => $filePath,
            ':file_size' => $file['size'],
            ':mime_type' => $file['type'],
            ':file_hash' => $fileHash
        ])) {
            return (int)$this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Get note attachments
     */
    public function getNoteAttachments(int $noteId, int $userId): array {
        // Verify note belongs to user
        $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
        $stmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
        
        if (!$stmt->fetch()) {
            return [];
        }

        $sql = "SELECT * FROM note_attachments WHERE note_id = :note_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':note_id' => $noteId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(int $attachmentId, int $userId): bool {
        // Get attachment info and verify ownership
        $stmt = $this->db->prepare("
            SELECT na.* FROM note_attachments na 
            JOIN notes n ON na.note_id = n.id 
            WHERE na.id = :attachment_id AND n.user_id = :user_id
        ");
        $stmt->execute([':attachment_id' => $attachmentId, ':user_id' => $userId]);
        $attachment = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$attachment) {
            return false;
        }

        // Delete file
        if (file_exists($attachment['file_path'])) {
            unlink($attachment['file_path']);
        }

        // Delete database record
        $stmt = $this->db->prepare("DELETE FROM note_attachments WHERE id = :id");
        return $stmt->execute([':id' => $attachmentId]);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(int $attachmentId, int $userId): array|false {
        // Get attachment info and verify ownership
        $stmt = $this->db->prepare("
            SELECT na.* FROM note_attachments na 
            JOIN notes n ON na.note_id = n.id 
            WHERE na.id = :attachment_id AND n.user_id = :user_id
        ");
        $stmt->execute([':attachment_id' => $attachmentId, ':user_id' => $userId]);
        $attachment = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$attachment || !file_exists($attachment['file_path'])) {
            return false;
        }

        // Decrypt file content
        $encryptedContent = file_get_contents($attachment['file_path']);
        $decryptedContent = $this->decryptContent($encryptedContent);
        
        if ($decryptedContent === false) {
            return false;
        }

        return [
            'content' => $decryptedContent,
            'filename' => $attachment['original_filename'],
            'mime_type' => $attachment['mime_type'],
            'file_size' => $attachment['file_size']
        ];
    }

    /**
     * Pin/unpin note
     */
    public function togglePin(int $noteId, int $userId): bool {
        $sql = "UPDATE notes SET is_pinned = NOT is_pinned WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':user_id' => $userId]);
    }

    /**
     * Update note color
     */
    public function updateNoteColor(int $noteId, int $userId, string $color): bool {
        $sql = "UPDATE notes SET color = :color WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':user_id' => $userId, ':color' => $color]);
    }

    /**
     * Update note priority
     */
    public function updateNotePriority(int $noteId, int $userId, string $priority): bool {
        $sql = "UPDATE notes SET priority = :priority WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $noteId, ':user_id' => $userId, ':priority' => $priority]);
    }

    /**
     * Search notes with advanced filters
     */
    public function searchNotes(int $userId, string $query = '', array $filters = []): array {
        $sql = "
            SELECT
                n.id, n.user_id, n.title, n.content, n.is_archived, n.is_pinned, n.priority, n.color,
                n.created_at, n.updated_at, n.word_count, n.read_time,
                GROUP_CONCAT(t.name SEPARATOR ',') AS tags,
                GROUP_CONCAT(t.id SEPARATOR ',') AS tag_ids
            FROM notes n
            LEFT JOIN note_tags nt ON n.id = nt.note_id
            LEFT JOIN tags t ON nt.tag_id = t.id
            WHERE n.user_id = :uid AND n.is_deleted = 0
        ";

        $params = [':uid' => $userId];

        // Add search query
        if (!empty($query)) {
            $sql .= " AND (n.title LIKE :query OR n.content LIKE :query)";
            $params[':query'] = '%' . $query . '%';
        }

        // Add filters
        if (isset($filters['is_archived'])) {
            $sql .= " AND n.is_archived = :is_archived";
            $params[':is_archived'] = $filters['is_archived'];
        }

        if (isset($filters['is_pinned'])) {
            $sql .= " AND n.is_pinned = :is_pinned";
            $params[':is_pinned'] = $filters['is_pinned'];
        }

        if (isset($filters['priority'])) {
            $sql .= " AND n.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }

        if (isset($filters['tag_id'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM note_tags nt2 WHERE nt2.note_id = n.id AND nt2.tag_id = :tag_id)";
            $params[':tag_id'] = $filters['tag_id'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND n.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND n.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " GROUP BY n.id ORDER BY n.is_pinned DESC, n.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $encryptedNotes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $decryptedNotes = [];
        
        foreach ($encryptedNotes as $note) {
            // Decrypt content after fetching from DB
            $note['content'] = $this->decryptContent($note['content']) ?? '[DECRYPTION FAILED]';
            $decryptedNotes[] = $note;
        }
        
        return $decryptedNotes;
    }

    /**
     * Get note statistics
     */
    public function getNoteStats(int $userId): array {
        $stats = [];

        // Total notes
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notes WHERE user_id = :user_id AND is_deleted = 0");
        $stmt->execute([':user_id' => $userId]);
        $stats['total'] = $stmt->fetchColumn();

        // Pinned notes
        $stmt = $this->db->prepare("SELECT COUNT(*) as pinned FROM notes WHERE user_id = :user_id AND is_pinned = 1 AND is_deleted = 0");
        $stmt->execute([':user_id' => $userId]);
        $stats['pinned'] = $stmt->fetchColumn();

        // Archived notes
        $stmt = $this->db->prepare("SELECT COUNT(*) as archived FROM notes WHERE user_id = :user_id AND is_archived = 1 AND is_deleted = 0");
        $stmt->execute([':user_id' => $userId]);
        $stats['archived'] = $stmt->fetchColumn();

        // Total words
        $stmt = $this->db->prepare("SELECT SUM(word_count) as total_words FROM notes WHERE user_id = :user_id AND is_deleted = 0");
        $stmt->execute([':user_id' => $userId]);
        $stats['total_words'] = $stmt->fetchColumn() ?? 0;

        // Total read time (in minutes)
        $stmt = $this->db->prepare("SELECT SUM(read_time) as total_read_time FROM notes WHERE user_id = :user_id AND is_deleted = 0");
        $stmt->execute([':user_id' => $userId]);
        $stats['total_read_time'] = $stmt->fetchColumn() ?? 0;

        return $stats;
    }

    /**
     * Get notes by specific IDs for export functionality
     */
    public function getNotesByIds(array $noteIds, int $userId): array {
        if (empty($noteIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($noteIds) - 1) . '?';
        $sql = "
            SELECT 
                n.*,
                GROUP_CONCAT(t.name) as tags,
                GROUP_CONCAT(t.id) as tag_ids
            FROM notes n
            LEFT JOIN note_tags nt ON n.id = nt.note_id
            LEFT JOIN tags t ON nt.tag_id = t.id
            WHERE n.id IN ($placeholders) AND n.user_id = ?
            GROUP BY n.id
            ORDER BY n.updated_at DESC
        ";

        $params = array_merge($noteIds, [$userId]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decrypt content for each note
        foreach ($notes as &$note) {
            $note['content'] = $this->decryptContent($note['content']);
        }

        return $notes;
    }

    // =======================================================
    // TRASH SYSTEM METHODS
    // =======================================================

    /**
     * Get deleted notes for trash view
     */
    public function getDeletedNotes(int $userId): array
    {
        $sql = "SELECT * FROM notes WHERE user_id = ? AND is_deleted = 1 ORDER BY deleted_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $notes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decrypt note data
        foreach ($notes as &$note) {
            $note['content'] = $this->decryptContent($note['content'] ?? '') ?? '[DECRYPTION FAILED]';
        }
        
        return $notes;
    }

    /**
     * Restore note from trash
     */
    public function restoreNote(int $noteId, int $userId): bool
    {
        $sql = "UPDATE notes SET is_deleted = 0, deleted_at = NULL WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$noteId, $userId]);
    }

    /**
     * Permanently delete note from trash
     */
    public function permanentDeleteNote(int $noteId, int $userId): bool
    {
        // First delete associated tags
        $stmt = $this->db->prepare("DELETE FROM note_tags WHERE note_id = ?");
        $stmt->execute([$noteId]);
        
        // Then delete the note
        $sql = "DELETE FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$noteId, $userId]);
    }

    /**
     * Empty trash - permanently delete all deleted notes
     */
    public function emptyTrash(int $userId): int
    {
        // First delete all associated tags for deleted notes
        $stmt = $this->db->prepare("
            DELETE nt FROM note_tags nt 
            JOIN notes n ON nt.note_id = n.id 
            WHERE n.user_id = ? AND n.is_deleted = 1
        ");
        $stmt->execute([$userId]);
        
        // Then delete all deleted notes
        $sql = "DELETE FROM notes WHERE user_id = ? AND is_deleted = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    /**
     * Auto-cleanup old deleted notes (older than specified days)
     */
    public function autoCleanupTrash(int $userId, int $daysOld = 30): int
    {
        // First delete associated tags for old deleted notes
        $stmt = $this->db->prepare("
            DELETE nt FROM note_tags nt 
            JOIN notes n ON nt.note_id = n.id 
            WHERE n.user_id = ? AND n.is_deleted = 1 AND n.deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$userId, $daysOld]);
        
        // Then delete old deleted notes
        $sql = "DELETE FROM notes WHERE user_id = ? AND is_deleted = 1 AND deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $daysOld]);
        return $stmt->rowCount();
    }

    /**
     * Get trash statistics for notes
     */
    public function getTrashStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_deleted,
                    COUNT(CASE WHEN deleted_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as deleted_last_week,
                    COUNT(CASE WHEN deleted_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as deleted_last_month,
                    SUM(word_count) as total_words_deleted,
                    SUM(read_time) as total_read_time_deleted
                FROM notes 
                WHERE user_id = ? AND is_deleted = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get note by title for import validation
     */
    public function getNoteByTitle(int $userId, string $title): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notes 
                WHERE user_id = ? AND title = ? AND is_deleted = 0
        
                ");
            $stmt->execute([$userId, $title]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting note by title: " . $e->getMessage());
            return false;
        }
    }
}