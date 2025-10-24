-- Migration 015: Add Data Management Tables

-- Table for export history
CREATE TABLE IF NOT EXISTS export_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    export_type VARCHAR(50) NOT NULL, -- 'json', 'csv', 'xml', 'zip'
    filename VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    status ENUM('active', 'deleted') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for import history
CREATE TABLE IF NOT EXISTS import_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    import_format VARCHAR(50) NOT NULL, -- 'json', 'csv', 'xml', 'zip'
    filename VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    imported_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    status ENUM('success', 'failed', 'partial') DEFAULT 'success',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for migration history (if not exists)
CREATE TABLE IF NOT EXISTS migration_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_file VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    execution_time_ms INT DEFAULT 0,
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT NULL
);

-- Table for data sync operations
CREATE TABLE IF NOT EXISTS data_sync_operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    operation_type VARCHAR(50) NOT NULL, -- 'export', 'import', 'migration', 'backup'
    source VARCHAR(255) NULL, -- source file or system
    destination VARCHAR(255) NULL, -- destination file or system
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    progress_percentage INT DEFAULT 0,
    total_items INT DEFAULT 0,
    processed_items INT DEFAULT 0,
    error_message TEXT NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for backup operations
CREATE TABLE IF NOT EXISTS backup_operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    backup_type VARCHAR(50) NOT NULL, -- 'full', 'incremental', 'differential'
    backup_path VARCHAR(500) NOT NULL,
    backup_size BIGINT NOT NULL,
    compression_ratio DECIMAL(5,2) NULL,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for data validation results
CREATE TABLE IF NOT EXISTS data_validation_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    validation_type VARCHAR(50) NOT NULL, -- 'import', 'export', 'migration'
    file_path VARCHAR(500) NOT NULL,
    validation_status ENUM('valid', 'invalid', 'warning') NOT NULL,
    total_records INT DEFAULT 0,
    valid_records INT DEFAULT 0,
    invalid_records INT DEFAULT 0,
    warning_records INT DEFAULT 0,
    validation_details JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
-- CREATE INDEX idx_export_history_user_created ON export_history(user_id, created_at);
-- CREATE INDEX idx_import_history_user_created ON import_history(user_id, created_at);
-- CREATE INDEX idx_data_sync_user_status ON data_sync_operations(user_id, status);
-- CREATE INDEX idx_backup_operations_user_created ON backup_operations(user_id, created_at);
-- CREATE INDEX idx_validation_results_user_created ON data_validation_results(user_id, created_at);
