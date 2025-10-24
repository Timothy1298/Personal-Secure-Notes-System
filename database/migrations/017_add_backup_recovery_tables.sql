-- Migration 017: Add Backup and Recovery Tables

-- Table for backup history
CREATE TABLE IF NOT EXISTS backup_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_name VARCHAR(255) NOT NULL,
    backup_type ENUM('full', 'incremental', 'differential') NOT NULL,
    backup_size BIGINT NOT NULL,
    backup_path VARCHAR(500) NOT NULL,
    backup_id VARCHAR(100) NOT NULL,
    status ENUM('completed', 'failed', 'in_progress') DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_backup_type (backup_type),
    INDEX idx_created_at (created_at),
    INDEX idx_backup_id (backup_id)
);

-- Table for recovery points
CREATE TABLE IF NOT EXISTS recovery_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    backup_id VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_backup_id (backup_id),
    INDEX idx_created_at (created_at)
);

-- Table for recovery actions
CREATE TABLE IF NOT EXISTS recovery_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recovery_point_id INT NULL,
    action_type VARCHAR(100) NOT NULL,
    action_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    duration_seconds INT NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recovery_point_id) REFERENCES recovery_points(id) ON DELETE SET NULL,
    INDEX idx_action_type (action_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Table for disaster recovery plans
CREATE TABLE IF NOT EXISTS disaster_recovery_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(255) NOT NULL,
    disaster_type VARCHAR(100) NOT NULL,
    plan_description TEXT,
    rto_minutes INT NOT NULL, -- Recovery Time Objective
    rpo_minutes INT NOT NULL, -- Recovery Point Objective
    plan_steps JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_disaster_type (disaster_type),
    INDEX idx_is_active (is_active)
);

-- Table for backup schedules
CREATE TABLE IF NOT EXISTS backup_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_name VARCHAR(255) NOT NULL,
    backup_type ENUM('full', 'incremental', 'differential') NOT NULL,
    schedule_cron VARCHAR(100) NOT NULL,
    retention_days INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_run DATETIME NULL,
    next_run DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_next_run (next_run)
);

-- Table for backup verification
CREATE TABLE IF NOT EXISTS backup_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_id VARCHAR(100) NOT NULL,
    verification_type ENUM('integrity', 'restore_test', 'data_validation') NOT NULL,
    status ENUM('passed', 'failed', 'pending') NOT NULL,
    verification_details JSON,
    verified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_backup_id (backup_id),
    INDEX idx_status (status),
    INDEX idx_verified_at (verified_at)
);

-- Table for disaster recovery tests
CREATE TABLE IF NOT EXISTS disaster_recovery_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(255) NOT NULL,
    disaster_type VARCHAR(100) NOT NULL,
    test_status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    test_results JSON,
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    duration_seconds INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_disaster_type (disaster_type),
    INDEX idx_test_status (test_status),
    INDEX idx_created_at (created_at)
);

-- Table for system health monitoring
CREATE TABLE IF NOT EXISTS system_health_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    check_type VARCHAR(100) NOT NULL,
    check_name VARCHAR(255) NOT NULL,
    status ENUM('healthy', 'warning', 'critical') NOT NULL,
    value DECIMAL(10,3) NULL,
    threshold DECIMAL(10,3) NULL,
    message TEXT,
    details JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_check_type (check_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert default disaster recovery plans
INSERT INTO disaster_recovery_plans (plan_name, disaster_type, plan_description, rto_minutes, rpo_minutes, plan_steps) VALUES
('Database Corruption Recovery', 'database_corruption', 'Recovery plan for database corruption scenarios', 30, 15, '[
    {"name": "Stop Application", "action": "stop_application", "timeout": 300},
    {"name": "Restore Database", "action": "restore_database", "timeout": 1800},
    {"name": "Verify Data Integrity", "action": "verify_data_integrity", "timeout": 600},
    {"name": "Start Application", "action": "start_application", "timeout": 300}
]'),
('Server Failure Recovery', 'server_failure', 'Recovery plan for server failure scenarios', 60, 30, '[
    {"name": "Failover to Backup Server", "action": "failover_server", "timeout": 1800},
    {"name": "Restore Data", "action": "restore_data", "timeout": 3600},
    {"name": "Update DNS", "action": "update_dns", "timeout": 300},
    {"name": "Verify Services", "action": "verify_services", "timeout": 600}
]'),
('Data Center Failure Recovery', 'data_center_failure', 'Recovery plan for data center failure scenarios', 240, 60, '[
    {"name": "Activate DR Site", "action": "activate_dr_site", "timeout": 3600},
    {"name": "Restore from Backup", "action": "restore_from_backup", "timeout": 7200},
    {"name": "Update Network Configuration", "action": "update_network_config", "timeout": 1800},
    {"name": "Verify System Health", "action": "verify_system_health", "timeout": 1200}
]'),
('Cyber Attack Recovery', 'cyber_attack', 'Recovery plan for cyber attack scenarios', 480, 120, '[
    {"name": "Isolate Systems", "action": "isolate_systems", "timeout": 600},
    {"name": "Assess Damage", "action": "assess_damage", "timeout": 1800},
    {"name": "Clean Systems", "action": "clean_systems", "timeout": 3600},
    {"name": "Restore from Clean Backup", "action": "restore_from_backup", "timeout": 7200},
    {"name": "Implement Security Measures", "action": "implement_security", "timeout": 1800},
    {"name": "Verify System Security", "action": "verify_security", "timeout": 1200}
]'),
('Natural Disaster Recovery', 'natural_disaster', 'Recovery plan for natural disaster scenarios', 1440, 240, '[
    {"name": "Assess Infrastructure Damage", "action": "assess_damage", "timeout": 3600},
    {"name": "Activate DR Site", "action": "activate_dr_site", "timeout": 7200},
    {"name": "Restore from Backup", "action": "restore_from_backup", "timeout": 14400},
    {"name": "Update Network Configuration", "action": "update_network_config", "timeout": 3600},
    {"name": "Verify System Health", "action": "verify_system_health", "timeout": 2400}
]')
ON DUPLICATE KEY UPDATE plan_description = VALUES(plan_description);

-- Insert default backup schedules
INSERT INTO backup_schedules (schedule_name, backup_type, schedule_cron, retention_days) VALUES
('Daily Full Backup', 'full', '0 2 * * *', 30),
('Hourly Incremental Backup', 'incremental', '0 * * * *', 7),
('Weekly Full Backup', 'full', '0 3 * * 0', 90),
('Monthly Full Backup', 'full', '0 4 1 * *', 365)
ON DUPLICATE KEY UPDATE schedule_cron = VALUES(schedule_cron);
