-- Migration 020: Add Monitoring Tables

-- Table for APM transactions
CREATE TABLE IF NOT EXISTS apm_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(255) NOT NULL UNIQUE,
    transaction_name VARCHAR(255) NOT NULL,
    start_time DECIMAL(20,6) NOT NULL,
    end_time DECIMAL(20,6) NULL,
    duration DECIMAL(10,3) NULL,
    start_memory BIGINT NOT NULL,
    end_memory BIGINT NULL,
    memory_delta BIGINT NULL,
    context JSON,
    status ENUM('started', 'completed', 'failed') DEFAULT 'started',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_transaction_name (transaction_name),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_duration (duration)
);

-- Table for APM queries
CREATE TABLE IF NOT EXISTS apm_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query TEXT NOT NULL,
    duration DECIMAL(10,3) NOT NULL,
    context JSON,
    is_slow BOOLEAN DEFAULT FALSE,
    timestamp DECIMAL(20,6) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_duration (duration),
    INDEX idx_is_slow (is_slow),
    INDEX idx_created_at (created_at),
    INDEX idx_timestamp (timestamp)
);

-- Table for APM errors
CREATE TABLE IF NOT EXISTS apm_errors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    error_type VARCHAR(255) NOT NULL,
    error_message TEXT NOT NULL,
    error_code INT,
    file VARCHAR(500),
    line INT,
    trace TEXT,
    context JSON,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    timestamp DECIMAL(20,6) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_error_type (error_type),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at),
    INDEX idx_timestamp (timestamp)
);

-- Table for APM events
CREATE TABLE IF NOT EXISTS apm_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    data JSON,
    context JSON,
    timestamp DECIMAL(20,6) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_name (event_name),
    INDEX idx_created_at (created_at),
    INDEX idx_timestamp (timestamp)
);

-- Table for performance alerts
CREATE TABLE IF NOT EXISTS performance_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(100) NOT NULL,
    alert_name VARCHAR(255) NOT NULL,
    description TEXT,
    threshold_value DECIMAL(10,3),
    current_value DECIMAL(10,3),
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    status ENUM('active', 'acknowledged', 'resolved', 'suppressed') DEFAULT 'active',
    triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at DATETIME NULL,
    acknowledged_by INT NULL,
    resolved_at DATETIME NULL,
    resolved_by INT NULL,
    metadata JSON,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_triggered_at (triggered_at)
);

-- Table for alert rules
CREATE TABLE IF NOT EXISTS alert_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(255) NOT NULL,
    rule_type ENUM('threshold', 'anomaly', 'pattern') NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    condition_operator ENUM('>', '<', '>=', '<=', '=', '!=') NOT NULL,
    threshold_value DECIMAL(10,3) NOT NULL,
    time_window INT NOT NULL, -- in seconds
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_rule_name (rule_name),
    INDEX idx_rule_type (rule_type),
    INDEX idx_metric_name (metric_name),
    INDEX idx_is_active (is_active),
    INDEX idx_created_by (created_by)
);

-- Table for monitoring dashboards
CREATE TABLE IF NOT EXISTS monitoring_dashboards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    layout JSON NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_name (name),
    INDEX idx_is_public (is_public),
    INDEX idx_created_by (created_by)
);

-- Table for dashboard widgets
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dashboard_id INT NOT NULL,
    widget_type VARCHAR(100) NOT NULL,
    widget_name VARCHAR(255) NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    width INT NOT NULL,
    height INT NOT NULL,
    config JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dashboard_id) REFERENCES monitoring_dashboards(id) ON DELETE CASCADE,
    INDEX idx_dashboard_id (dashboard_id),
    INDEX idx_widget_type (widget_type)
);

-- Table for system metrics
CREATE TABLE IF NOT EXISTS system_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(255) NOT NULL,
    metric_value DECIMAL(15,6) NOT NULL,
    metric_unit VARCHAR(50),
    tags JSON,
    timestamp DECIMAL(20,6) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_name (metric_name),
    INDEX idx_timestamp (timestamp),
    INDEX idx_created_at (created_at)
);

-- Table for log entries
CREATE TABLE IF NOT EXISTS log_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    channel VARCHAR(100),
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_level (level),
    INDEX idx_channel (channel),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Table for uptime monitoring
CREATE TABLE IF NOT EXISTS uptime_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    check_name VARCHAR(255) NOT NULL,
    check_url VARCHAR(500) NOT NULL,
    check_type ENUM('http', 'https', 'tcp', 'ping') DEFAULT 'http',
    expected_status_code INT DEFAULT 200,
    timeout_seconds INT DEFAULT 30,
    check_interval_seconds INT DEFAULT 300,
    is_active BOOLEAN DEFAULT TRUE,
    last_check_at DATETIME NULL,
    last_status ENUM('up', 'down', 'unknown') DEFAULT 'unknown',
    last_response_time DECIMAL(10,3) NULL,
    consecutive_failures INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_check_name (check_name),
    INDEX idx_is_active (is_active),
    INDEX idx_last_status (last_status),
    INDEX idx_last_check_at (last_check_at)
);

-- Table for uptime check results
CREATE TABLE IF NOT EXISTS uptime_check_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    check_id INT NOT NULL,
    status ENUM('up', 'down', 'timeout', 'error') NOT NULL,
    response_time DECIMAL(10,3) NULL,
    status_code INT NULL,
    error_message TEXT NULL,
    response_headers JSON,
    checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (check_id) REFERENCES uptime_checks(id) ON DELETE CASCADE,
    INDEX idx_check_id (check_id),
    INDEX idx_status (status),
    INDEX idx_checked_at (checked_at)
);

-- Insert default alert rules
INSERT INTO alert_rules (rule_name, rule_type, metric_name, condition_operator, threshold_value, time_window, severity, created_by) VALUES
('High Response Time', 'threshold', 'response_time', '>', 2000, 300, 'high', 1),
('High Error Rate', 'threshold', 'error_rate', '>', 10, 300, 'critical', 1),
('High Memory Usage', 'threshold', 'memory_usage', '>', 90, 300, 'high', 1),
('Slow Database Queries', 'threshold', 'slow_query_count', '>', 5, 300, 'medium', 1),
('High CPU Usage', 'threshold', 'cpu_usage', '>', 80, 300, 'medium', 1)
ON DUPLICATE KEY UPDATE threshold_value = VALUES(threshold_value);

-- Insert default uptime checks
INSERT INTO uptime_checks (check_name, check_url, check_type, expected_status_code, check_interval_seconds) VALUES
('Application Health Check', '/health', 'http', 200, 60),
('Database Connectivity', 'tcp://localhost:3306', 'tcp', NULL, 120),
('API Endpoint', '/api/health', 'http', 200, 60)
ON DUPLICATE KEY UPDATE check_url = VALUES(check_url);

-- Insert default monitoring dashboard
INSERT INTO monitoring_dashboards (name, description, layout, is_public, created_by) VALUES
('System Overview', 'Default system monitoring dashboard', '{
    "widgets": [
        {
            "id": "response_time_chart",
            "type": "line_chart",
            "title": "Response Time",
            "position": {"x": 0, "y": 0, "width": 6, "height": 4},
            "config": {
                "metric": "response_time",
                "time_range": "1h"
            }
        },
        {
            "id": "error_rate_gauge",
            "type": "gauge",
            "title": "Error Rate",
            "position": {"x": 6, "y": 0, "width": 3, "height": 4},
            "config": {
                "metric": "error_rate",
                "max_value": 100
            }
        },
        {
            "id": "memory_usage_chart",
            "type": "area_chart",
            "title": "Memory Usage",
            "position": {"x": 9, "y": 0, "width": 3, "height": 4},
            "config": {
                "metric": "memory_usage",
                "time_range": "1h"
            }
        },
        {
            "id": "active_alerts",
            "type": "alert_list",
            "title": "Active Alerts",
            "position": {"x": 0, "y": 4, "width": 12, "height": 4},
            "config": {
                "severity_filter": ["high", "critical"],
                "limit": 10
            }
        }
    ]
}', TRUE, 1)
ON DUPLICATE KEY UPDATE layout = VALUES(layout);
