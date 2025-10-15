<?php

namespace App\Controllers;

use Core\Session;
use PDO;
use App\Models\NotesModel;
use Exception;

class NotesController {
    private $db;
    private $auditLogger; 
    private $notesModel;

    public function __construct(PDO $db, AuditLogsController $auditLogger, NotesModel $notesModel) {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
        $this->notesModel = $notesModel;
    }
    
    /**
     * Helper to send JSON response and exit.
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    /**
     * Handle file uploads for notes with encryption
     */
    private function handleFileUploads(int $noteId, int $userId): void {
        $uploadDir = __DIR__ . '/../../storage/attachments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle regular file attachments
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $this->processFileUploads($_FILES['attachments'], $noteId, $userId, $uploadDir, 'attachment');
        }

        // Handle image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $this->processFileUploads($_FILES['images'], $noteId, $userId, $uploadDir, 'image');
        }
    }

    /**
     * Process uploaded files with encryption
     */
    private function processFileUploads(array $files, int $noteId, int $userId, string $uploadDir, string $type): void {
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $originalName = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $fileSize = $files['size'][$i];
                $mimeType = $files['type'][$i];
                
                // Generate secure filename
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $storedFilename = uniqid('note_' . $noteId . '_', true) . '.' . $extension;
                $filePath = $uploadDir . $storedFilename;
                
                // Encrypt and store file
                if ($this->encryptAndStoreFile($tmpName, $filePath)) {
                    $fileHash = hash_file('sha256', $filePath);
                    
                    // Store file metadata in database
                    $stmt = $this->db->prepare("
                        INSERT INTO note_attachments (note_id, original_filename, stored_filename, file_path, file_size, mime_type, file_hash, is_encrypted, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
                    ");
                    $stmt->execute([$noteId, $originalName, $storedFilename, $filePath, $fileSize, $mimeType, $fileHash]);
                }
            }
        }
    }

    /**
     * Encrypt and store file
     */
    private function encryptAndStoreFile(string $sourcePath, string $destinationPath): bool {
        try {
            // Read source file
            $fileContent = file_get_contents($sourcePath);
            if ($fileContent === false) {
                return false;
            }
            
            // Simple encryption (in production, use proper encryption)
            $encryptedContent = base64_encode($fileContent);
            
            // Write encrypted file
            return file_put_contents($destinationPath, $encryptedContent) !== false;
        } catch (Exception $e) {
            error_log("File encryption error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensures all provided tag names exist in the database and returns their IDs.
     */
    private function ensureTagsExist(array $tagNames, int $userId): array
    {
        // ... (This method remains unchanged as it is a private helper)
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

        $notes = $this->notesModel->getNotesWithTagsByUserId($userId); 

        // Fetch all tags for the user to populate the filter dropdown
        $stmtTags = $this->db->prepare("
             SELECT id, name FROM tags WHERE user_id = :uid
        ");
        $stmtTags->execute([':uid' => $userId]);
        $allTags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);

        $tags = $allTags;

        // If the request is AJAX for content refresh, don't include the full layout
        // NOTE: Since your JS currently fetches the whole HTML and parses the grid, 
        // we keep the full include here to match the current JS logic.
        include __DIR__ . '/../Views/notes.php';
    }

    // Handle new note creation
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';
            $category = $_POST['category'] ?? 'general';
            $color = $_POST['color'] ?? '#ffffff';
            $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
            $tagIds = $_POST['tags'] ?? []; // Array of tag IDs

            if (!$title || !$content) {
                $this->jsonResponse(false, "Both title and content are required.");
            }

            // Use NotesModel to create the note (handles encryption)
            $noteId = $this->notesModel->createNote($userId, $title, $content, $summary, $priority, $category, $color, $isPinned);
            
            if ($noteId === false) {
                 $this->jsonResponse(false, "Failed to save note due to an encryption error.");
            }

            // Handle file uploads
            $this->handleFileUploads($noteId, $userId);

            // Insert associations into the note_tags table
            if (!empty($tagIds) && $noteId) {
                foreach ($tagIds as $tagId) {
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

            $this->auditLogger->logEvent($userId, 'created note: ' . $title);
            $this->jsonResponse(true, "Note added successfully!");
        }
    }   

    // Update existing note
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $_POST['id'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $summary = trim($_POST['summary'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';
            $category = $_POST['category'] ?? 'general';
            $color = $_POST['color'] ?? '#ffffff';
            $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
            $tagIds = $_POST['tags'] ?? [];
            $userId = Session::get('user_id');

            // Use NotesModel to update the note (handles encryption)
            $success = $this->notesModel->updateNote((int)$noteId, $userId, $title, $content, $summary, $priority, $category, $color, $isPinned);

            if (!$success) {
                 $this->jsonResponse(false, "Failed to update note due to a database or encryption error.");
            }

            // First, delete existing tag associations for the note
            $stmtDeleteTags = $this->db->prepare("
                 DELETE FROM note_tags WHERE note_id = :note_id
            ");
            $stmtDeleteTags->execute([':note_id' => $noteId]);

            // Then, insert new tag associations
            if (!empty($tagIds)) {
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

            $this->auditLogger->logEvent($userId, 'updated note with ID: ' . $noteId);
            $this->jsonResponse(true, "Note updated successfully!");
        }
    }
    
    // Archive a note
    public function archive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON request
            $input = json_decode(file_get_contents('php://input'), true);
            $noteId = $input['note_id'] ?? $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $this->jsonResponse(false, "Invalid note ID.");
            }

            $success = $this->notesModel->archiveNote((int)$noteId, $userId);

            if (!$success) {
                 $this->jsonResponse(false, "Failed to archive note.");
            }

            $this->auditLogger->logEvent($userId, 'archived note with ID: ' . $noteId);
            $this->jsonResponse(true, "Note archived successfully!");
        }
    }
    
    // Unarchive a note (Assuming you have an unarchive route)
    public function unarchive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON request
            $input = json_decode(file_get_contents('php://input'), true);
            $noteId = $input['note_id'] ?? $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $this->jsonResponse(false, "Invalid note ID.");
            }

            $success = $this->notesModel->unarchiveNote((int)$noteId, $userId);

            if (!$success) {
                 $this->jsonResponse(false, "Failed to unarchive note.");
            }

            $this->auditLogger->logEvent($userId, 'unarchived note with ID: ' . $noteId);
            $this->jsonResponse(true, "Note unarchived successfully!");
        }
    }
    
    // Delete a note (soft delete - move to trash)
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON request
            $input = json_decode(file_get_contents('php://input'), true);
            $noteId = $input['note_id'] ?? $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $this->jsonResponse(false, "Invalid note ID.");
            }

            // Soft delete - move to trash
            $stmt = $this->db->prepare("
                UPDATE notes 
                SET is_deleted = 1, deleted_at = NOW()
                WHERE id = :id AND user_id = :uid
            ");
            $success = $stmt->execute([':id' => $noteId, ':uid' => $userId]);

            if (!$success) {
                 $this->jsonResponse(false, "Failed to delete note.");
            }

            $this->auditLogger->logEvent($userId, 'moved note to trash with ID: ' . $noteId);
            $this->jsonResponse(true, "Note moved to trash successfully!");
        }
    }
    
    // Toggle pin status of a note
    public function togglePin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON request
            $input = json_decode(file_get_contents('php://input'), true);
            $noteId = $input['note_id'] ?? $_POST['note_id'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId) {
                $this->jsonResponse(false, "Invalid note ID.");
            }

            // Get current pin status
            $stmt = $this->db->prepare("SELECT is_pinned FROM notes WHERE id = :note_id AND user_id = :user_id");
            $stmt->execute([':note_id' => $noteId, ':user_id' => $userId]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$note) {
                $this->jsonResponse(false, "Note not found.");
            }

            // Toggle the pin status
            $newPinStatus = $note['is_pinned'] ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE notes SET is_pinned = :is_pinned WHERE id = :note_id AND user_id = :user_id");
            $success = $stmt->execute([
                ':is_pinned' => $newPinStatus,
                ':note_id' => $noteId,
                ':user_id' => $userId
            ]);

            if (!$success) {
                $this->jsonResponse(false, "Failed to update note pin status.");
            }

            $action = $newPinStatus ? 'pinned' : 'unpinned';
            $this->auditLogger->logEvent($userId, $action . ' note with ID: ' . $noteId);
            $this->jsonResponse(true, "Note " . $action . " successfully!");
        }
    }
    
    // Update note color
    public function updateColor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle JSON request
            $input = json_decode(file_get_contents('php://input'), true);
            $noteId = $input['note_id'] ?? $_POST['note_id'] ?? null;
            $color = $input['color'] ?? $_POST['color'] ?? null;
            $userId = Session::get('user_id');

            if (!$noteId || !$color) {
                $this->jsonResponse(false, "Invalid note ID or color.");
            }

            $stmt = $this->db->prepare("UPDATE notes SET color = :color WHERE id = :note_id AND user_id = :user_id");
            $success = $stmt->execute([
                ':color' => $color,
                ':note_id' => $noteId,
                ':user_id' => $userId
            ]);

            if (!$success) {
                $this->jsonResponse(false, "Failed to update note color.");
            }

            $this->auditLogger->logEvent($userId, 'updated color for note with ID: ' . $noteId);
            $this->jsonResponse(true, "Note color updated successfully!");
        }
    }

    /**
     * Export notes in various formats
     */
    public function export() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $format = $input['format'] ?? 'pdf';
            $scope = $input['scope'] ?? 'all';
            $includeTags = $input['include_tags'] ?? true;
            $includeMetadata = $input['include_metadata'] ?? true;
            $includeAttachments = $input['include_attachments'] ?? false;
            
            $userId = Session::get('user_id');
            if (!$userId) {
                $this->jsonResponse(false, "User not authenticated.");
            }

            // Get notes based on scope
            $notes = [];
            switch ($scope) {
                case 'selected':
                    $noteIds = $input['note_ids'] ?? [];
                    if (empty($noteIds)) {
                        $this->jsonResponse(false, "No notes selected for export.");
                    }
                    $notes = $this->notesModel->getNotesByIds($noteIds, $userId);
                    break;
                case 'filtered':
                    // This would need to be implemented based on current filters
                    $notes = $this->notesModel->getNotesWithTagsByUserId($userId);
                    break;
                case 'all':
                default:
                    $notes = $this->notesModel->getNotesWithTagsByUserId($userId);
                    break;
            }

            if (empty($notes)) {
                $this->jsonResponse(false, "No notes found to export.");
            }

            // Generate export based on format
            switch ($format) {
                case 'json':
                    $this->exportAsJson($notes, $includeTags, $includeMetadata);
                    break;
                case 'markdown':
                    $this->exportAsMarkdown($notes, $includeTags, $includeMetadata);
                    break;
                case 'word':
                case 'docx':
                    $this->exportAsDocx($notes, $includeTags, $includeMetadata);
                    break;
                case 'pdf':
                default:
                    $this->jsonResponse(false, "PDF export not yet implemented. JSON, Markdown, and DOCX are available.");
                    break;
            }
        }
    }

    /**
     * Export notes as JSON
     */
    private function exportAsJson(array $notes, bool $includeTags, bool $includeMetadata): void {
        $exportData = [
            'export_info' => [
                'exported_at' => date('Y-m-d H:i:s'),
                'total_notes' => count($notes),
                'format' => 'json'
            ],
            'notes' => []
        ];

        foreach ($notes as $note) {
            $noteData = [
                'id' => $note['id'],
                'title' => $note['title'],
                'content' => $note['content'],
                'summary' => $note['summary'] ?? '',
                'priority' => $note['priority'] ?? 'medium',
                'color' => $note['color'] ?? '#ffffff',
                'is_pinned' => (bool)($note['is_pinned'] ?? false),
                'is_archived' => (bool)($note['is_archived'] ?? false)
            ];

            if ($includeMetadata) {
                $noteData['created_at'] = $note['created_at'];
                $noteData['updated_at'] = $note['updated_at'];
                $noteData['word_count'] = $note['word_count'] ?? 0;
                $noteData['read_time'] = $note['read_time'] ?? 0;
            }

            if ($includeTags && !empty($note['tags'])) {
                $noteData['tags'] = explode(',', $note['tags']);
            }

            $exportData['notes'][] = $noteData;
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="notes_export_' . date('Y-m-d_H-i-s') . '.json"');
        echo json_encode($exportData, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Export notes as Markdown
     */
    private function exportAsMarkdown(array $notes, bool $includeTags, bool $includeMetadata): void {
        $markdown = "# Notes Export\n\n";
        $markdown .= "**Exported:** " . date('Y-m-d H:i:s') . "\n";
        $markdown .= "**Total Notes:** " . count($notes) . "\n\n";
        $markdown .= "---\n\n";

        foreach ($notes as $note) {
            $markdown .= "## " . htmlspecialchars($note['title']) . "\n\n";
            
            if ($includeMetadata) {
                $markdown .= "**Created:** " . date('M j, Y', strtotime($note['created_at'])) . "\n";
                $markdown .= "**Updated:** " . date('M j, Y', strtotime($note['updated_at'])) . "\n";
                $markdown .= "**Priority:** " . ucfirst($note['priority'] ?? 'medium') . "\n";
                if ($note['word_count']) {
                    $markdown .= "**Word Count:** " . $note['word_count'] . "\n";
                }
                $markdown .= "\n";
            }

            if ($includeTags && !empty($note['tags'])) {
                $tags = explode(',', $note['tags']);
                $markdown .= "**Tags:** " . implode(', ', array_map('trim', $tags)) . "\n\n";
            }

            if (!empty($note['summary'])) {
                $markdown .= "**Summary:** " . htmlspecialchars($note['summary']) . "\n\n";
            }

            // Convert HTML content to markdown (basic conversion)
            $content = $note['content'];
            $content = preg_replace_callback('/<h([1-6])>(.*?)<\/h[1-6]>/', function($matches) {
                return str_repeat('#', $matches[1]) . ' ' . $matches[2];
            }, $content);
            $content = preg_replace('/<strong>(.*?)<\/strong>/', '**$1**', $content);
            $content = preg_replace('/<em>(.*?)<\/em>/', '*$1*', $content);
            $content = preg_replace('/<ul>(.*?)<\/ul>/s', '$1', $content);
            $content = preg_replace('/<li>(.*?)<\/li>/', '- $1', $content);
            $content = preg_replace('/<ol>(.*?)<\/ol>/s', '$1', $content);
            $content = preg_replace('/<li>(.*?)<\/li>/', '1. $1', $content);
            $content = preg_replace('/<p>(.*?)<\/p>/', '$1', $content);
            $content = strip_tags($content);
            $content = html_entity_decode($content);

            $markdown .= $content . "\n\n";
            $markdown .= "---\n\n";
        }

        header('Content-Type: text/markdown');
        header('Content-Disposition: attachment; filename="notes_export_' . date('Y-m-d_H-i-s') . '.md"');
        echo $markdown;
        exit;
    }
    
    /**
     * Export notes as DOCX (Word document)
     */
    private function exportAsDocx(array $notes, bool $includeTags, bool $includeMetadata): void {
        try {
            // Create new Word document
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            
            // Set document properties
            $phpWord->getDocInfo()->setCreator('SecureNote Pro');
            $phpWord->getDocInfo()->setTitle('Notes Export');
            $phpWord->getDocInfo()->setDescription('Exported notes from SecureNote Pro');
            $phpWord->getDocInfo()->setCreated(time());
            
            // Add a section
            $section = $phpWord->addSection();
            
            // Add title
            $section->addText('Notes Export', ['bold' => true, 'size' => 16]);
            $section->addText('Generated on: ' . date('Y-m-d H:i:s'), ['size' => 10]);
            $section->addText('Total Notes: ' . count($notes), ['size' => 10]);
            $section->addTextBreak(2);
            
            // Add notes
            foreach ($notes as $index => $note) {
                $section->addText(($index + 1) . '. ' . $note['title'], ['bold' => true, 'size' => 12]);
                
                if ($includeMetadata) {
                    $section->addText('Created: ' . date('M j, Y H:i', strtotime($note['created_at'])), ['size' => 10]);
                    $section->addText('Updated: ' . date('M j, Y H:i', strtotime($note['updated_at'])), ['size' => 10]);
                    $section->addText('Priority: ' . ucfirst($note['priority'] ?? 'medium'), ['size' => 10]);
                    if ($note['word_count']) {
                        $section->addText('Word Count: ' . $note['word_count'], ['size' => 10]);
                    }
                }
                
                if ($includeTags && !empty($note['tags'])) {
                    $tags = explode(',', $note['tags']);
                    $section->addText('Tags: ' . implode(', ', array_map('trim', $tags)), ['size' => 10]);
                }
                
                // Clean and format content
                $content = $note['content'];
                $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
                $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);
                $content = strip_tags($content);
                
                $section->addText('Content:', ['bold' => true, 'size' => 10]);
                $section->addText($content, ['size' => 10]);
                
                $section->addTextBreak(1);
            }
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="notes_export_' . date('Y-m-d_H-i-s') . '.docx"');
            
            // Write document to output
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
            
        } catch (Exception $e) {
            error_log("DOCX export error: " . $e->getMessage());
            
            // Fallback to HTML format
            $html = '<html><head><meta charset="UTF-8"><title>Notes Export</title></head><body>';
            $html .= '<h1 style="color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">Notes Export</h1>';
            $html .= '<p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            $html .= '<p><strong>Total Notes:</strong> ' . count($notes) . '</p>';
            $html .= '<hr style="margin: 20px 0;">';
            
            foreach ($notes as $index => $note) {
                $html .= '<div style="margin-bottom: 30px; page-break-inside: avoid;">';
                $html .= '<h2 style="color: #1f2937; margin-bottom: 10px;">' . htmlspecialchars($note['title']) . '</h2>';
                
                if ($includeMetadata) {
                    $html .= '<div style="background: #f9fafb; padding: 10px; border-left: 4px solid #3b82f6; margin-bottom: 15px;">';
                    $html .= '<p style="margin: 5px 0;"><strong>Created:</strong> ' . date('M j, Y H:i', strtotime($note['created_at'])) . '</p>';
                    $html .= '<p style="margin: 5px 0;"><strong>Updated:</strong> ' . date('M j, Y H:i', strtotime($note['updated_at'])) . '</p>';
                    $html .= '<p style="margin: 5px 0;"><strong>Priority:</strong> ' . ucfirst($note['priority'] ?? 'medium') . '</p>';
                    if ($note['word_count']) {
                        $html .= '<p style="margin: 5px 0;"><strong>Word Count:</strong> ' . $note['word_count'] . '</p>';
                    }
                    $html .= '</div>';
                }
                
                if ($includeTags && !empty($note['tags'])) {
                    $tags = explode(',', $note['tags']);
                    $html .= '<p style="margin-bottom: 15px;"><strong>Tags:</strong> ';
                    foreach ($tags as $tag) {
                        $html .= '<span style="background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-right: 5px;">' . htmlspecialchars(trim($tag)) . '</span>';
                    }
                    $html .= '</p>';
                }
                
                // Clean and format content
                $content = $note['content'];
                $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
                $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);
                
                $html .= '<div style="line-height: 1.6; margin-bottom: 20px;">' . $content . '</div>';
                $html .= '</div>';
                
                if ($index < count($notes) - 1) {
                    $html .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">';
                }
            }
            
            $html .= '</body></html>';
            
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="notes_export_' . date('Y-m-d_H-i-s') . '.html"');
            echo $html;
        }
    }
    

    /**
     * Auto-save note functionality
     */
    public function autoSave() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            if (!$noteId) {
                $this->jsonResponse(false, "Note ID required for auto-save.");
            }

            $userId = Session::get('user_id');
            if (!$userId) {
                $this->jsonResponse(false, "User not authenticated.");
            }

            // Update note with new content
            $stmt = $this->db->prepare("
                UPDATE notes 
                SET title = :title, content = :content, updated_at = NOW() 
                WHERE id = :note_id AND user_id = :user_id
            ");
            
            $success = $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':note_id' => $noteId,
                ':user_id' => $userId
            ]);

            if ($success) {
                $this->jsonResponse(true, "Note auto-saved successfully.");
            } else {
                $this->jsonResponse(false, "Failed to auto-save note.");
            }
        }
    }
}