-- Migration 019: Add Enterprise Tables

-- Table for SSO sessions
CREATE TABLE IF NOT EXISTS sso_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    provider ENUM('saml', 'oauth', 'ldap') NOT NULL,
    data JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_provider (provider),
    INDEX idx_expires_at (expires_at)
);

-- Table for OAuth tokens
CREATE TABLE IF NOT EXISTS oauth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_provider (user_id, provider),
    INDEX idx_user_id (user_id),
    INDEX idx_provider (provider),
    INDEX idx_expires_at (expires_at)
);

-- Table for user groups
CREATE TABLE IF NOT EXISTS user_groups_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Table for user group memberships
CREATE TABLE IF NOT EXISTS user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES user_groups_table(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_group (user_id, group_id),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id)
);

-- Table for roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Table for user roles
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id),
    INDEX idx_assigned_by (assigned_by)
);

-- Table for compliance events
CREATE TABLE IF NOT EXISTS compliance_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    user_id INT,
    data JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Table for data subject requests
CREATE TABLE IF NOT EXISTS data_subject_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_type ENUM('access', 'portability', 'rectification', 'erasure', 'restriction', 'objection') NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
    request_data JSON,
    response_data JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_request_type (request_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Table for consent management
CREATE TABLE IF NOT EXISTS user_consent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    consent_type VARCHAR(100) NOT NULL,
    consent_given BOOLEAN NOT NULL,
    consent_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    withdrawal_date DATETIME NULL,
    consent_version VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_consent_type (user_id, consent_type),
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_consent_given (consent_given),
    INDEX idx_consent_date (consent_date)
);

-- Table for audit trail
CREATE TABLE IF NOT EXISTS audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id)
);

-- Table for security incidents
CREATE TABLE IF NOT EXISTS security_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_type VARCHAR(100) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    affected_user_id INT,
    affected_resource VARCHAR(100),
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    assigned_to INT,
    resolution_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (affected_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_incident_type (incident_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_affected_user_id (affected_user_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_at (created_at)
);

-- Table for compliance policies
CREATE TABLE IF NOT EXISTS compliance_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_name VARCHAR(255) NOT NULL,
    policy_type ENUM('gdpr', 'hipaa', 'sox', 'iso27001', 'custom') NOT NULL,
    policy_content TEXT NOT NULL,
    version VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATETIME NOT NULL,
    expiry_date DATETIME NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_policy_name (policy_name),
    INDEX idx_policy_type (policy_type),
    INDEX idx_version (version),
    INDEX idx_is_active (is_active),
    INDEX idx_effective_date (effective_date),
    INDEX idx_created_by (created_by)
);

-- Table for policy acknowledgments
CREATE TABLE IF NOT EXISTS policy_acknowledgments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_id INT NOT NULL,
    user_id INT NOT NULL,
    acknowledged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (policy_id) REFERENCES compliance_policies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_policy_user (policy_id, user_id),
    INDEX idx_policy_id (policy_id),
    INDEX idx_user_id (user_id),
    INDEX idx_acknowledged_at (acknowledged_at)
);

-- Table for risk assessments
CREATE TABLE IF NOT EXISTS risk_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_name VARCHAR(255) NOT NULL,
    assessment_type ENUM('security', 'privacy', 'compliance', 'operational') NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    impact_description TEXT,
    likelihood ENUM('very_low', 'low', 'medium', 'high', 'very_high') NOT NULL,
    impact ENUM('very_low', 'low', 'medium', 'high', 'very_high') NOT NULL,
    mitigation_measures TEXT,
    residual_risk ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    assessed_by INT NOT NULL,
    assessment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    review_date DATETIME,
    status ENUM('open', 'mitigated', 'accepted', 'closed') DEFAULT 'open',
    FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_assessment_name (assessment_name),
    INDEX idx_assessment_type (assessment_type),
    INDEX idx_risk_level (risk_level),
    INDEX idx_assessed_by (assessed_by),
    INDEX idx_assessment_date (assessment_date),
    INDEX idx_status (status)
);

-- Add additional columns to users table for enterprise features
-- Note: MySQL doesn't support IF NOT EXISTS for ADD COLUMN, so we'll add them individually
-- and ignore errors if they already exist

-- Add first_name column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'first_name') = 0,
    'ALTER TABLE users ADD COLUMN first_name VARCHAR(255) AFTER username',
    'SELECT "Column first_name already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add last_name column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'last_name') = 0,
    'ALTER TABLE users ADD COLUMN last_name VARCHAR(255) AFTER first_name',
    'SELECT "Column last_name already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_restricted column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'is_restricted') = 0,
    'ALTER TABLE users ADD COLUMN is_restricted BOOLEAN DEFAULT FALSE AFTER two_factor_secret',
    'SELECT "Column is_restricted already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add restriction_reason column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'restriction_reason') = 0,
    'ALTER TABLE users ADD COLUMN restriction_reason TEXT AFTER is_restricted',
    'SELECT "Column restriction_reason already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add processing_objected column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'processing_objected') = 0,
    'ALTER TABLE users ADD COLUMN processing_objected BOOLEAN DEFAULT FALSE AFTER restriction_reason',
    'SELECT "Column processing_objected already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add objection_reason column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'objection_reason') = 0,
    'ALTER TABLE users ADD COLUMN objection_reason TEXT AFTER processing_objected',
    'SELECT "Column objection_reason already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add consent_given column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'consent_given') = 0,
    'ALTER TABLE users ADD COLUMN consent_given BOOLEAN DEFAULT FALSE AFTER objection_reason',
    'SELECT "Column consent_given already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add consent_date column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'consent_date') = 0,
    'ALTER TABLE users ADD COLUMN consent_date DATETIME NULL AFTER consent_given',
    'SELECT "Column consent_date already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add last_login_at column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'last_login_at') = 0,
    'ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL AFTER consent_date',
    'SELECT "Column last_login_at already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add login_count column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'login_count') = 0,
    'ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER last_login_at',
    'SELECT "Column login_count already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add failed_login_count column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'failed_login_count') = 0,
    'ALTER TABLE users ADD COLUMN failed_login_count INT DEFAULT 0 AFTER login_count',
    'SELECT "Column failed_login_count already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add locked_until column
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'locked_until') = 0,
    'ALTER TABLE users ADD COLUMN locked_until DATETIME NULL AFTER failed_login_count',
    'SELECT "Column locked_until already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert default roles
INSERT INTO roles (name, description, permissions) VALUES
('admin', 'System Administrator', '["all"]'),
('user', 'Regular User', '["read_own", "write_own"]'),
('moderator', 'Content Moderator', '["read_all", "write_own", "moderate_content"]'),
('auditor', 'Compliance Auditor', '["read_all", "audit_logs"]')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default groups
INSERT INTO user_groups_table (name, description) VALUES
('employees', 'Company Employees'),
('contractors', 'External Contractors'),
('administrators', 'System Administrators'),
('auditors', 'Compliance Auditors')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default compliance policies
INSERT INTO compliance_policies (policy_name, policy_type, policy_content, version, effective_date, created_by) VALUES
('GDPR Data Protection Policy', 'gdpr', 'This policy outlines how we handle personal data in compliance with GDPR requirements.', '1.0', NOW(), 1),
('HIPAA Privacy Policy', 'hipaa', 'This policy outlines how we protect health information in compliance with HIPAA requirements.', '1.0', NOW(), 1),
('SOX Compliance Policy', 'sox', 'This policy outlines our financial controls and audit procedures in compliance with SOX requirements.', '1.0', NOW(), 1),
('Information Security Policy', 'iso27001', 'This policy outlines our information security controls and procedures.', '1.0', NOW(), 1)
ON DUPLICATE KEY UPDATE policy_content = VALUES(policy_content);
