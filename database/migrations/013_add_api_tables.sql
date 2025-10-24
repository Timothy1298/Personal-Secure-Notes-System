-- Migration 013: Add API Tables

-- Table for OAuth authorization codes
CREATE TABLE IF NOT EXISTS oauth_authorization_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) UNIQUE NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    scope TEXT,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for OAuth access tokens
CREATE TABLE IF NOT EXISTS oauth_access_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token VARCHAR(255) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    scope TEXT,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for OAuth refresh tokens
CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    refresh_token VARCHAR(255) UNIQUE NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    scope TEXT,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (access_token) REFERENCES oauth_access_tokens(access_token) ON DELETE CASCADE
);

-- Table for API keys
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at DATETIME NULL,
    expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for API usage logs
CREATE TABLE IF NOT EXISTS api_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    api_key_id INT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_data JSON,
    response_status INT,
    response_time_ms INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL
);

-- Table for API rate limits
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    api_key_id INT NULL,
    endpoint VARCHAR(255) NOT NULL,
    requests_count INT DEFAULT 1,
    window_start DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL
);

-- Table for API webhooks
CREATE TABLE IF NOT EXISTS api_webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    webhook_id VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    events JSON NOT NULL,
    secret VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_triggered_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for API webhook deliveries
CREATE TABLE IF NOT EXISTS api_webhook_deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webhook_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    response_status INT,
    response_body TEXT,
    attempts INT DEFAULT 1,
    max_attempts INT DEFAULT 3,
    next_retry_at DATETIME NULL,
    delivered_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES api_webhooks(webhook_id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_oauth_authorization_codes_code ON oauth_authorization_codes(code);
CREATE INDEX idx_oauth_authorization_codes_user ON oauth_authorization_codes(user_id);
CREATE INDEX idx_oauth_access_tokens_token ON oauth_access_tokens(access_token);
CREATE INDEX idx_oauth_access_tokens_user ON oauth_access_tokens(user_id);
CREATE INDEX idx_oauth_refresh_tokens_token ON oauth_refresh_tokens(refresh_token);
CREATE INDEX idx_oauth_refresh_tokens_user ON oauth_refresh_tokens(user_id);
CREATE INDEX idx_api_keys_key ON api_keys(api_key);
CREATE INDEX idx_api_keys_user ON api_keys(user_id);
CREATE INDEX idx_api_usage_logs_user ON api_usage_logs(user_id);
CREATE INDEX idx_api_usage_logs_endpoint ON api_usage_logs(endpoint);
CREATE INDEX idx_api_usage_logs_created ON api_usage_logs(created_at);
CREATE INDEX idx_api_rate_limits_user ON api_rate_limits(user_id);
CREATE INDEX idx_api_rate_limits_endpoint ON api_rate_limits(endpoint);
CREATE INDEX idx_api_webhooks_user ON api_webhooks(user_id);
CREATE INDEX idx_api_webhooks_webhook_id ON api_webhooks(webhook_id);
CREATE INDEX idx_api_webhook_deliveries_webhook ON api_webhook_deliveries(webhook_id);
CREATE INDEX idx_api_webhook_deliveries_retry ON api_webhook_deliveries(next_retry_at);
