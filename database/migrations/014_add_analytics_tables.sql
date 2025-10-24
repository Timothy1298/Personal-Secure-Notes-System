-- Migration 014: Add Analytics Tables

-- Table for user behavior analytics
CREATE TABLE IF NOT EXISTS user_behavior_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    page VARCHAR(255) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for performance metrics
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    response_time_ms INT NOT NULL,
    status_code INT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for database performance metrics
CREATE TABLE IF NOT EXISTS database_performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query_hash VARCHAR(64) NOT NULL,
    query_text TEXT NOT NULL,
    execution_time_ms INT NOT NULL,
    rows_affected INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table for page performance metrics
CREATE TABLE IF NOT EXISTS page_performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(255) NOT NULL,
    load_time_ms INT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for feature usage analytics
CREATE TABLE IF NOT EXISTS feature_usage_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    feature VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for user session analytics
CREATE TABLE IF NOT EXISTS user_session_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for content interaction analytics
CREATE TABLE IF NOT EXISTS content_interaction_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    content_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    metadata JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
-- CREATE INDEX idx_user_behavior_user_action ON user_behavior_analytics(user_id, action);
-- CREATE INDEX idx_user_behavior_created_at ON user_behavior_analytics(created_at);
-- CREATE INDEX idx_performance_metrics_endpoint ON performance_metrics(endpoint);
-- CREATE INDEX idx_performance_metrics_created_at ON performance_metrics(created_at);
-- CREATE INDEX idx_db_performance_query_hash ON database_performance_metrics(query_hash);
-- CREATE INDEX idx_db_performance_created_at ON database_performance_metrics(created_at);
-- CREATE INDEX idx_page_performance_page ON page_performance_metrics(page);
-- CREATE INDEX idx_page_performance_created_at ON page_performance_metrics(created_at);
-- CREATE INDEX idx_feature_usage_user_feature ON feature_usage_analytics(user_id, feature);
-- CREATE INDEX idx_feature_usage_created_at ON feature_usage_analytics(created_at);
-- CREATE INDEX idx_session_analytics_user ON user_session_analytics(user_id);
-- CREATE INDEX idx_session_analytics_created_at ON user_session_analytics(created_at);
-- CREATE INDEX idx_content_interaction_user_content ON content_interaction_analytics(user_id, content_type, content_id);
-- CREATE INDEX idx_content_interaction_created_at ON content_interaction_analytics(created_at);
