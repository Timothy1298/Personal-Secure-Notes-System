-- Migration 021: Add Collaboration Tables

-- Table for Teams
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Team Members
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_member (team_id, user_id)
);

-- Table for Team Shared Notes
CREATE TABLE IF NOT EXISTS team_shared_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    team_id INT NOT NULL,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_note (note_id, team_id)
);

-- Table for Team Shared Tasks
CREATE TABLE IF NOT EXISTS team_shared_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    team_id INT NOT NULL,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_task (task_id, team_id)
);

-- Table for Shared Links
CREATE TABLE IF NOT EXISTS shared_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('note', 'task') NOT NULL,
    resource_id INT NOT NULL,
    created_by INT NOT NULL,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    expires_at DATETIME NULL,
    password_hash VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    access_count INT DEFAULT 0,
    last_accessed DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Shared Link Access Logs
CREATE TABLE IF NOT EXISTS shared_link_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shared_link_id INT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shared_link_id) REFERENCES shared_links(id) ON DELETE CASCADE
);

-- Table for User Preferences
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme_preference ENUM('light', 'dark', 'auto') DEFAULT 'light',
    font_size ENUM('small', 'medium', 'large') DEFAULT 'medium',
    note_layout ENUM('grid', 'list') DEFAULT 'grid',
    auto_save BOOLEAN DEFAULT TRUE,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);
