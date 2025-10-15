<?php
namespace Core;

use PDO;
use Exception;

class FileManager {
    private $db;
    private $uploadDir;
    private $encryptionKey;
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const FALLBACK_KEY = 'a_very_secure_32_byte_key_for_files';

    public function __construct(PDO $db, string $uploadDir = __DIR__ . '/../uploads') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
        
        $this->encryptionKey = defined('APP_ENCRYPTION_KEY') ? APP_ENCRYPTION_KEY : self::FALLBACK_KEY;
    }

    /**
     * Encrypts file content.
     * @param string $plaintext The content to encrypt.
     * @return string|false Base64 encoded IV:Tag:Ciphertext or false on failure.
     */
    private function encryptContent(string $plaintext): string|false {
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER_METHOD,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16 // Tag length for GCM
        );
        if ($ciphertext === false) {
            return false;
        }
        return base64_encode($iv . ':' . $tag . ':' . $ciphertext);
    }

    /**
     * Decrypts file content.
     * @param string $encryptedData Base64 encoded IV:Tag:Ciphertext.
     * @return string|false Decrypted plaintext or false on failure.
     */
    private function decryptContent(string $encryptedData): string|false {
        $decoded = base64_decode($encryptedData);
        $parts = explode(':', $decoded, 3);
        
        if (count($parts) !== 3) {
            // Not encrypted or invalid format
            return false; 
        }

        list($iv, $tag, $ciphertext) = $parts;
        
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if (strlen($iv) !== $iv_length) {
            return false;
        }
        
        $tag_length = 16; // GCM tag length
        if (strlen($tag) !== $tag_length) {
            return false;
        }

        return openssl_decrypt(
            $ciphertext,
            self::CIPHER_METHOD,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }

    /**
     * Uploads and encrypts a file.
     * @param array $file The $_FILES array entry for the uploaded file.
     * @param int $userId The ID of the user uploading the file.
     * @param string $associatedType 'note' or 'task'.
     * @param int $associatedId The ID of the note or task.
     * @return array|false An array with file details on success, false on failure.
     */
    public function uploadFile(array $file, int $userId, string $associatedType, int $associatedId): array|false {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("File upload error: " . $file['error']);
            return false;
        }

        $originalFilename = basename($file['name']);
        $mimeType = $file['type'];
        $fileSize = $file['size'];
        $tempFilePath = $file['tmp_name'];

        // Read file content
        $fileContent = file_get_contents($tempFilePath);
        if ($fileContent === false) {
            error_log("Failed to read uploaded file content: " . $tempFilePath);
            return false;
        }

        // Encrypt content
        $encryptedContent = $this->encryptContent($fileContent);
        if ($encryptedContent === false) {
            error_log("Failed to encrypt file content for user {$userId}");
            return false;
        }

        // Generate unique filename for storage
        $storedFilename = uniqid('file_') . '.' . pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filePath = $this->uploadDir . '/' . $storedFilename;

        // Save encrypted content to file
        if (file_put_contents($filePath, $encryptedContent) === false) {
            error_log("Failed to write encrypted file to disk: " . $filePath);
            return false;
        }

        $fileHash = hash_file('sha256', $filePath);

        // Store metadata in database
        $tableName = $associatedType === 'note' ? 'note_attachments' : 'task_attachments';
        $foreignKey = $associatedType === 'note' ? 'note_id' : 'task_id';

        $stmt = $this->db->prepare("
            INSERT INTO {$tableName} ({$foreignKey}, original_filename, stored_filename, file_path, file_size, mime_type, file_hash, is_encrypted, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $result = $stmt->execute([
            $associatedId,
            $originalFilename,
            $storedFilename,
            $filePath,
            $fileSize,
            $mimeType,
            $fileHash
        ]);

        if (!$result) {
            unlink($filePath); // Clean up uploaded file if DB insert fails
            error_log("Failed to store file metadata in database for user {$userId}, type {$associatedType}, id {$associatedId}");
            return false;
        }

        return [
            'id' => (int)$this->db->lastInsertId(),
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'file_hash' => $fileHash,
            'is_encrypted' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Retrieves and decrypts a file.
     * @param int $attachmentId The ID of the attachment.
     * @param int $userId The ID of the user requesting the file (for authorization).
     * @param string $associatedType 'note' or 'task'.
     * @return array|false An array with file content and metadata on success, false on failure.
     */
    public function downloadFile(int $attachmentId, int $userId, string $associatedType): array|false {
        $tableName = $associatedType === 'note' ? 'note_attachments' : 'task_attachments';
        $foreignKey = $associatedType === 'note' ? 'note_id' : 'task_id';

        // Fetch file metadata and verify ownership
        $stmt = $this->db->prepare("
            SELECT a.*, t.user_id as owner_id 
            FROM {$tableName} a
            JOIN {$associatedType}s t ON a.{$foreignKey} = t.id
            WHERE a.id = ? AND t.user_id = ?
        ");
        $stmt->execute([$attachmentId, $userId]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attachment) {
            error_log("Attachment {$attachmentId} not found or unauthorized access for user {$userId}");
            return false;
        }

        $filePath = $attachment['file_path'];
        if (!file_exists($filePath)) {
            error_log("Stored file not found on disk: " . $filePath);
            return false;
        }

        $encryptedContent = file_get_contents($filePath);
        if ($encryptedContent === false) {
            error_log("Failed to read encrypted file from disk: " . $filePath);
            return false;
        }

        $decryptedContent = $this->decryptContent($encryptedContent);
        if ($decryptedContent === false) {
            error_log("Failed to decrypt file content for attachment {$attachmentId}");
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
     * Deletes a file and its database record.
     * @param int $attachmentId The ID of the attachment.
     * @param int $userId The ID of the user (for authorization).
     * @param string $associatedType 'note' or 'task'.
     * @return bool True on success, false on failure.
     */
    public function deleteFile(int $attachmentId, int $userId, string $associatedType): bool {
        $tableName = $associatedType === 'note' ? 'note_attachments' : 'task_attachments';
        $foreignKey = $associatedType === 'note' ? 'note_id' : 'task_id';

        // Fetch file path and verify ownership
        $stmt = $this->db->prepare("
            SELECT a.file_path FROM {$tableName} a
            JOIN {$associatedType}s t ON a.{$foreignKey} = t.id
            WHERE a.id = ? AND t.user_id = ?
        ");
        $stmt->execute([$attachmentId, $userId]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attachment) {
            error_log("Attachment {$attachmentId} not found or unauthorized access for user {$userId}");
            return false;
        }

        // Delete from database
        $deleteStmt = $this->db->prepare("DELETE FROM {$tableName} WHERE id = ?");
        if (!$deleteStmt->execute([$attachmentId])) {
            error_log("Failed to delete attachment record from database: {$attachmentId}");
            return false;
        }

        // Delete file from disk
        if (file_exists($attachment['file_path'])) {
            unlink($attachment['file_path']);
        }

        return true;
    }

    /**
     * Get file attachments for a note or task.
     * @param int $associatedId The ID of the note or task.
     * @param int $userId The ID of the user (for authorization).
     * @param string $associatedType 'note' or 'task'.
     * @return array Array of attachment metadata.
     */
    public function getAttachments(int $associatedId, int $userId, string $associatedType): array {
        $tableName = $associatedType === 'note' ? 'note_attachments' : 'task_attachments';
        $foreignKey = $associatedType === 'note' ? 'note_id' : 'task_id';

        // Verify ownership
        $stmt = $this->db->prepare("
            SELECT 1 FROM {$associatedType}s WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$associatedId, $userId]);
        if (!$stmt->fetch()) {
            return [];
        }

        // Get attachments
        $stmt = $this->db->prepare("
            SELECT id, original_filename, file_size, mime_type, created_at
            FROM {$tableName}
            WHERE {$foreignKey} = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$associatedId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate file upload.
     * @param array $file The $_FILES array entry.
     * @return array Array of validation errors (empty if valid).
     */
    public function validateFile(array $file): array {
        $errors = [];
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'text/plain', 'text/csv',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'application/x-rar-compressed'
        ];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $file['error'];
        }

        if ($file['size'] > $maxSize) {
            $errors[] = "File size exceeds 10MB limit";
        }

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "File type not allowed";
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "File extension not allowed";
        }

        return $errors;
    }

    /**
     * Clean up orphaned files.
     * @return int Number of files cleaned up.
     */
    public function cleanupOrphanedFiles(): int {
        $cleaned = 0;

        // Get all stored file paths from database
        $stmt = $this->db->prepare("
            SELECT file_path FROM note_attachments
            UNION
            SELECT file_path FROM task_attachments
        ");
        $stmt->execute();
        $dbFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get all files in upload directory
        $uploadFiles = glob($this->uploadDir . '/*');

        foreach ($uploadFiles as $file) {
            if (is_file($file) && !in_array($file, $dbFiles)) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Get storage statistics.
     * @return array Storage statistics.
     */
    public function getStorageStats(): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_size,
                AVG(file_size) as avg_size
            FROM (
                SELECT file_size FROM note_attachments
                UNION ALL
                SELECT file_size FROM task_attachments
            ) as all_files
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get disk usage
        $diskUsage = 0;
        if (is_dir($this->uploadDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->uploadDir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $diskUsage += $file->getSize();
                }
            }
        }

        return [
            'total_files' => (int)$stats['total_files'],
            'total_size' => (int)$stats['total_size'],
            'avg_size' => (int)$stats['avg_size'],
            'disk_usage' => $diskUsage
        ];
    }
}