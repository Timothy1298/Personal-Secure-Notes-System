-- Migration 009: Add Voice Notes and OCR Tables
-- This migration adds tables for voice notes and OCR functionality

-- Voice Notes Table
CREATE TABLE IF NOT EXISTS voice_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    duration INT DEFAULT NULL,
    file_size INT NOT NULL,
    transcription TEXT DEFAULT NULL,
    is_processed BOOLEAN DEFAULT FALSE,
    linked_note_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_note_id) REFERENCES notes(id) ON DELETE SET NULL,
    INDEX idx_voice_notes_user (user_id),
    INDEX idx_voice_notes_created (created_at),
    INDEX idx_voice_notes_processed (is_processed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OCR Results Table
CREATE TABLE IF NOT EXISTS ocr_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    extracted_text LONGTEXT DEFAULT NULL,
    confidence DECIMAL(3,2) DEFAULT NULL,
    linked_note_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_note_id) REFERENCES notes(id) ON DELETE SET NULL,
    INDEX idx_ocr_results_user (user_id),
    INDEX idx_ocr_results_created (created_at),
    INDEX idx_ocr_results_confidence (confidence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- File Attachments Table (for general file uploads)
CREATE TABLE IF NOT EXISTS file_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_id INT DEFAULT NULL,
    task_id INT DEFAULT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('image', 'document', 'audio', 'video', 'other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    INDEX idx_file_attachments_user (user_id),
    INDEX idx_file_attachments_note (note_id),
    INDEX idx_file_attachments_task (task_id),
    INDEX idx_file_attachments_type (file_type),
    INDEX idx_file_attachments_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Document Templates Table
CREATE TABLE IF NOT EXISTS document_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    template_content LONGTEXT NOT NULL,
    template_type ENUM('note', 'task', 'report', 'custom') NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document_templates_user (user_id),
    INDEX idx_document_templates_type (template_type),
    INDEX idx_document_templates_public (is_public),
    INDEX idx_document_templates_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Document Versions Table (for version control)
CREATE TABLE IF NOT EXISTS document_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    document_type ENUM('note', 'task') NOT NULL,
    user_id INT NOT NULL,
    version_number INT NOT NULL,
    content LONGTEXT NOT NULL,
    change_summary TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document_versions_document (document_id, document_type),
    INDEX idx_document_versions_user (user_id),
    INDEX idx_document_versions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Content Blocks Table (for modular content)
CREATE TABLE IF NOT EXISTS content_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    block_type ENUM('text', 'image', 'code', 'table', 'list', 'quote', 'custom') NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    content LONGTEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    is_reusable BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_content_blocks_user (user_id),
    INDEX idx_content_blocks_type (block_type),
    INDEX idx_content_blocks_reusable (is_reusable),
    INDEX idx_content_blocks_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Content Block References Table (for linking blocks to documents)
CREATE TABLE IF NOT EXISTS content_block_references (
    id INT AUTO_INCREMENT PRIMARY KEY,
    block_id INT NOT NULL,
    document_id INT NOT NULL,
    document_type ENUM('note', 'task') NOT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (block_id) REFERENCES content_blocks(id) ON DELETE CASCADE,
    INDEX idx_content_block_refs_block (block_id),
    INDEX idx_content_block_refs_document (document_id, document_type),
    INDEX idx_content_block_refs_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Smart Suggestions Table (for AI-powered suggestions)
CREATE TABLE IF NOT EXISTS smart_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    suggestion_type ENUM('tag', 'category', 'priority', 'related_content', 'improvement', 'template') NOT NULL,
    target_id INT DEFAULT NULL,
    target_type ENUM('note', 'task', 'user') NOT NULL,
    suggestion_data JSON NOT NULL,
    confidence DECIMAL(3,2) NOT NULL,
    is_accepted BOOLEAN DEFAULT NULL,
    is_dismissed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_smart_suggestions_user (user_id),
    INDEX idx_smart_suggestions_type (suggestion_type),
    INDEX idx_smart_suggestions_target (target_id, target_type),
    INDEX idx_smart_suggestions_confidence (confidence),
    INDEX idx_smart_suggestions_status (is_accepted, is_dismissed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Content Analysis Table (for AI analysis results)
CREATE TABLE IF NOT EXISTS content_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    content_type ENUM('note', 'task') NOT NULL,
    analysis_type ENUM('sentiment', 'keywords', 'summary', 'readability', 'topics') NOT NULL,
    analysis_data JSON NOT NULL,
    confidence DECIMAL(3,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_content_analysis_user (user_id),
    INDEX idx_content_analysis_content (content_id, content_type),
    INDEX idx_content_analysis_type (analysis_type),
    INDEX idx_content_analysis_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workflow Templates Table
CREATE TABLE IF NOT EXISTS workflow_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    workflow_data JSON NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workflow_templates_user (user_id),
    INDEX idx_workflow_templates_public (is_public),
    INDEX idx_workflow_templates_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workflow Executions Table
CREATE TABLE IF NOT EXISTS workflow_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_id INT NOT NULL,
    execution_data JSON NOT NULL,
    status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES workflow_templates(id) ON DELETE CASCADE,
    INDEX idx_workflow_executions_user (user_id),
    INDEX idx_workflow_executions_template (template_id),
    INDEX idx_workflow_executions_status (status),
    INDEX idx_workflow_executions_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
