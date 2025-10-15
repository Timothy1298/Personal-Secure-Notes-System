-- Add import_history table for tracking import operations
CREATE TABLE IF NOT EXISTS import_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    format VARCHAR(50) NOT NULL,
    status ENUM('success', 'failed', 'partial') NOT NULL,
    items_imported INT DEFAULT 0,
    items_skipped INT DEFAULT 0,
    items_failed INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
