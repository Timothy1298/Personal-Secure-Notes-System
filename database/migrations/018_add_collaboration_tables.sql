-- Migration 018: Add Collaboration Tables

-- Table for teams
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner_id (owner_id),
    INDEX idx_created_at (created_at)
);

-- Table for team members
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'moderator', 'member') DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_user (team_id, user_id),
    INDEX idx_team_id (team_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role (role)
);

-- Table for team workspaces
CREATE TABLE IF NOT EXISTS team_workspaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_team_id (team_id),
    INDEX idx_created_at (created_at)
);

-- Table for team shared notes
CREATE TABLE IF NOT EXISTS team_shared_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    team_id INT NOT NULL,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_note_team (note_id, team_id),
    INDEX idx_note_id (note_id),
    INDEX idx_team_id (team_id),
    INDEX idx_permission (permission)
);

-- Table for team shared tasks
CREATE TABLE IF NOT EXISTS team_shared_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    team_id INT NOT NULL,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_team (task_id, team_id),
    INDEX idx_task_id (task_id),
    INDEX idx_team_id (team_id),
    INDEX idx_permission (permission)
);

-- Table for real-time editing sessions
CREATE TABLE IF NOT EXISTS editing_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    resource_type ENUM('note', 'task') NOT NULL,
    resource_id INT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_by (created_by),
    INDEX idx_is_active (is_active)
);

-- Table for editing session participants
CREATE TABLE IF NOT EXISTS editing_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cursor_position INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (session_id) REFERENCES editing_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_session_user (session_id, user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
);

-- Table for collaboration comments
CREATE TABLE IF NOT EXISTS collaboration_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('note', 'task') NOT NULL,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    parent_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_resolved BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES collaboration_comments(id) ON DELETE CASCADE,
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_created_at (created_at)
);

-- Table for collaboration mentions
CREATE TABLE IF NOT EXISTS collaboration_mentions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    mentioned_user_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES collaboration_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (mentioned_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_comment_mention (comment_id, mentioned_user_id),
    INDEX idx_comment_id (comment_id),
    INDEX idx_mentioned_user_id (mentioned_user_id),
    INDEX idx_is_read (is_read)
);

-- Table for team invitations
CREATE TABLE IF NOT EXISTS team_invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    invited_by INT NOT NULL,
    invited_email VARCHAR(255) NOT NULL,
    invitation_token VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'moderator', 'member') DEFAULT 'member',
    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    accepted_at DATETIME NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_team_id (team_id),
    INDEX idx_invited_email (invited_email),
    INDEX idx_invitation_token (invitation_token),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
);

-- Table for team activity logs
CREATE TABLE IF NOT EXISTS team_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NULL,
    resource_id INT NULL,
    description TEXT,
    metadata JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_team_id (team_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_at (created_at)
);

-- Table for shared links
CREATE TABLE IF NOT EXISTS shared_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('note', 'task', 'team') NOT NULL,
    resource_id INT NOT NULL,
    created_by INT NOT NULL,
    share_token VARCHAR(255) NOT NULL UNIQUE,
    permission ENUM('read', 'write', 'admin') DEFAULT 'read',
    expires_at DATETIME NULL,
    password_hash VARCHAR(255) NULL,
    access_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_accessed DATETIME NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_share_token (share_token),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_by (created_by),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
);

-- Table for shared link access logs
CREATE TABLE IF NOT EXISTS shared_link_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shared_link_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shared_link_id) REFERENCES shared_links(id) ON DELETE CASCADE,
    INDEX idx_shared_link_id (shared_link_id),
    INDEX idx_accessed_at (accessed_at)
);

-- Table for real-time presence
CREATE TABLE IF NOT EXISTS user_presence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_type ENUM('note', 'task', 'team') NOT NULL,
    resource_id INT NOT NULL,
    status ENUM('online', 'away', 'busy', 'offline') DEFAULT 'online',
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_resource (user_id, resource_type, resource_id),
    INDEX idx_user_id (user_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_status (status),
    INDEX idx_last_seen (last_seen)
);

-- Insert default team for system administration
INSERT INTO teams (name, description, owner_id) VALUES
('System Administrators', 'Default team for system administrators', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Add system admin to the default team
INSERT INTO team_members (team_id, user_id, role) 
SELECT 1, 1, 'admin'
WHERE NOT EXISTS (SELECT 1 FROM team_members WHERE team_id = 1 AND user_id = 1);
