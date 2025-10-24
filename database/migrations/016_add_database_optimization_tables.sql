-- Migration 016: Add Database Optimization Tables

-- Table for storing query performance metrics
CREATE TABLE IF NOT EXISTS query_performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query_hash VARCHAR(64) NOT NULL,
    query_text TEXT NOT NULL,
    execution_time_ms DECIMAL(10,3) NOT NULL,
    rows_examined INT DEFAULT 0,
    rows_sent INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_query_hash (query_hash),
    INDEX idx_execution_time (execution_time_ms),
    INDEX idx_created_at (created_at)
);

-- Table for storing slow query logs
CREATE TABLE IF NOT EXISTS slow_query_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query_text TEXT NOT NULL,
    execution_time_ms DECIMAL(10,3) NOT NULL,
    rows_examined INT DEFAULT 0,
    rows_sent INT DEFAULT 0,
    user_id INT NULL,
    session_id VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_execution_time (execution_time_ms),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Table for storing database optimization recommendations
CREATE TABLE IF NOT EXISTS optimization_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recommendation_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'dismissed') DEFAULT 'pending',
    estimated_impact VARCHAR(100) NULL,
    sql_command TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (recommendation_type),
    INDEX idx_priority (priority),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Table for storing database health checks
CREATE TABLE IF NOT EXISTS database_health_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    check_type VARCHAR(50) NOT NULL,
    check_name VARCHAR(255) NOT NULL,
    status ENUM('healthy', 'warning', 'critical') NOT NULL,
    value DECIMAL(10,3) NULL,
    threshold DECIMAL(10,3) NULL,
    message TEXT NULL,
    details JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_check_type (check_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Table for storing connection pool statistics
CREATE TABLE IF NOT EXISTS connection_pool_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pool_name VARCHAR(100) NOT NULL,
    active_connections INT NOT NULL,
    idle_connections INT NOT NULL,
    max_connections INT NOT NULL,
    utilization_percent DECIMAL(5,2) NOT NULL,
    avg_response_time_ms DECIMAL(10,3) NULL,
    total_requests INT DEFAULT 0,
    failed_requests INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pool_name (pool_name),
    INDEX idx_created_at (created_at)
);

-- Table for storing replication status
CREATE TABLE IF NOT EXISTS replication_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_type ENUM('master', 'slave') NOT NULL,
    server_host VARCHAR(255) NOT NULL,
    server_port INT NOT NULL,
    status ENUM('active', 'inactive', 'error') NOT NULL,
    seconds_behind_master INT NULL,
    io_running ENUM('Yes', 'No') NULL,
    sql_running ENUM('Yes', 'No') NULL,
    last_error TEXT NULL,
    details JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_type (server_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Table for storing index usage statistics
CREATE TABLE IF NOT EXISTS index_usage_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    index_name VARCHAR(255) NOT NULL,
    usage_count INT DEFAULT 0,
    last_used DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_table_index (table_name, index_name),
    INDEX idx_usage_count (usage_count),
    INDEX idx_last_used (last_used)
);

-- Table for storing table statistics
CREATE TABLE IF NOT EXISTS table_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    row_count BIGINT NOT NULL,
    data_size_mb DECIMAL(10,2) NOT NULL,
    index_size_mb DECIMAL(10,2) NOT NULL,
    total_size_mb DECIMAL(10,2) NOT NULL,
    fragmentation_percent DECIMAL(5,2) DEFAULT 0,
    last_optimized DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_table_name (table_name),
    INDEX idx_total_size (total_size_mb),
    INDEX idx_fragmentation (fragmentation_percent)
);

-- Table for storing database configuration
CREATE TABLE IF NOT EXISTS database_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) NOT NULL,
    config_value TEXT NOT NULL,
    recommended_value TEXT NULL,
    description TEXT NULL,
    is_optimized BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_config_key (config_key),
    INDEX idx_is_optimized (is_optimized)
);

-- Insert initial database configuration
INSERT INTO database_config (config_key, config_value, recommended_value, description) VALUES
('innodb_buffer_pool_size', '134217728', '1073741824', 'Buffer pool size in bytes (128MB -> 1GB)'),
('max_connections', '151', '200', 'Maximum number of connections'),
('query_cache_size', '0', '67108864', 'Query cache size in bytes (0 -> 64MB)'),
('tmp_table_size', '16777216', '67108864', 'Temporary table size in bytes (16MB -> 64MB)'),
('max_heap_table_size', '16777216', '67108864', 'Maximum heap table size in bytes (16MB -> 64MB)'),
('innodb_log_file_size', '50331648', '134217728', 'InnoDB log file size in bytes (48MB -> 128MB)'),
('innodb_flush_log_at_trx_commit', '1', '2', 'InnoDB flush log at transaction commit (1 -> 2 for better performance)')
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);

-- Create indexes for better performance
-- CREATE INDEX idx_notes_user_created ON notes(user_id, created_at);
-- CREATE INDEX idx_notes_user_title ON notes(user_id, title);
-- CREATE INDEX idx_tasks_user_status ON tasks(user_id, status);
-- CREATE INDEX idx_tasks_user_due ON tasks(user_id, due_date);
-- CREATE INDEX idx_note_tags_note ON note_tags(note_id);
-- CREATE INDEX idx_note_tags_tag ON note_tags(tag_id);
-- CREATE INDEX idx_user_behavior_user_created ON user_behavior_analytics(user_id, created_at);
-- CREATE INDEX idx_performance_metrics_created ON performance_metrics(created_at);
-- CREATE INDEX idx_feature_usage_user_created ON feature_usage_analytics(user_id, created_at);
