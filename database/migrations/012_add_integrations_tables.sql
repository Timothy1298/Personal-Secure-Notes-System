-- Migration 012: Add Third-Party Integrations Tables

-- Table for Google integrations
CREATE TABLE IF NOT EXISTS google_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    token_type VARCHAR(50) DEFAULT 'Bearer',
    expires_at DATETIME NULL,
    scope TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_google (user_id)
);

-- Table for Microsoft integrations
CREATE TABLE IF NOT EXISTS microsoft_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    token_type VARCHAR(50) DEFAULT 'Bearer',
    expires_at DATETIME NULL,
    scope TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_microsoft (user_id)
);

-- Table for Slack integrations
CREATE TABLE IF NOT EXISTS slack_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    team_id VARCHAR(255),
    team_name VARCHAR(255),
    authed_user_id VARCHAR(255),
    scope TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_slack (user_id)
);

-- Table for integration activities (logs)
CREATE TABLE IF NOT EXISTS integration_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    integration_type ENUM('google', 'microsoft', 'slack') NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    activity_data JSON,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for integration settings
CREATE TABLE IF NOT EXISTS integration_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    integration_type ENUM('google', 'microsoft', 'slack') NOT NULL,
    setting_key VARCHAR(255) NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_integration_setting (user_id, integration_type, setting_key)
);

-- Indexes for performance
CREATE INDEX idx_google_integrations_user ON google_integrations(user_id);
CREATE INDEX idx_microsoft_integrations_user ON microsoft_integrations(user_id);
CREATE INDEX idx_slack_integrations_user ON slack_integrations(user_id);
CREATE INDEX idx_integration_activities_user ON integration_activities(user_id);
CREATE INDEX idx_integration_activities_type ON integration_activities(integration_type);
CREATE INDEX idx_integration_activities_status ON integration_activities(status);
CREATE INDEX idx_integration_settings_user ON integration_settings(user_id);
CREATE INDEX idx_integration_settings_type ON integration_settings(integration_type);
