-- Migration 011: Add Automation Tables

-- Table for workflow templates
CREATE TABLE IF NOT EXISTS workflow_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    workflow_data JSON NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for workflow executions
CREATE TABLE IF NOT EXISTS workflow_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_id INT NOT NULL,
    execution_data JSON,
    status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES workflow_templates(id) ON DELETE CASCADE
);

-- Table for scheduled tasks
CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    task_type VARCHAR(100) NOT NULL,
    task_data JSON NOT NULL,
    schedule_type ENUM('once', 'daily', 'weekly', 'monthly', 'interval') NOT NULL,
    schedule_data JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    next_execution DATETIME NULL,
    last_execution DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for task executions
CREATE TABLE IF NOT EXISTS task_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    result_data JSON,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (task_id) REFERENCES scheduled_tasks(id) ON DELETE CASCADE
);

-- Table for webhooks
CREATE TABLE IF NOT EXISTS webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    webhook_id VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    url VARCHAR(500) NOT NULL,
    method ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH') DEFAULT 'POST',
    headers JSON,
    authentication_type ENUM('none', 'bearer', 'basic', 'api_key') DEFAULT 'none',
    authentication_data JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for webhook executions
CREATE TABLE IF NOT EXISTS webhook_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webhook_id VARCHAR(255) NOT NULL,
    request_data JSON,
    response_data JSON,
    status ENUM('running', 'completed', 'failed') DEFAULT 'running',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(webhook_id) ON DELETE CASCADE
);

-- Table for automation triggers
CREATE TABLE IF NOT EXISTS automation_triggers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    trigger_type VARCHAR(100) NOT NULL,
    trigger_data JSON NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    action_data JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for automation executions
CREATE TABLE IF NOT EXISTS automation_executions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trigger_id INT NOT NULL,
    user_id INT NOT NULL,
    execution_data JSON,
    status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (trigger_id) REFERENCES automation_triggers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance (only create if they don't exist)
-- Note: MySQL doesn't support IF NOT EXISTS for CREATE INDEX, so we'll skip these
-- as they may already exist from previous migrations
