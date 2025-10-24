-- Migration 008: Add Performance Indexes (Fixed)
-- This migration adds database indexes for performance optimization

-- Notes table indexes
CREATE INDEX idx_notes_user_created ON notes(user_id, created_at);
CREATE INDEX idx_notes_user_updated ON notes(user_id, updated_at);
CREATE INDEX idx_notes_user_status ON notes(user_id, is_archived, is_pinned);
CREATE INDEX idx_notes_priority ON notes(user_id, priority);
CREATE INDEX idx_notes_color ON notes(user_id, color);

-- Tasks table indexes
CREATE INDEX idx_tasks_user_created ON tasks(user_id, created_at);
CREATE INDEX idx_tasks_user_updated ON tasks(user_id, updated_at);
CREATE INDEX idx_tasks_user_status ON tasks(user_id, status);
CREATE INDEX idx_tasks_due_date ON tasks(user_id, due_date);
CREATE INDEX idx_tasks_priority ON tasks(user_id, priority);

-- Tags table indexes
CREATE INDEX idx_tags_user_name ON tags(user_id, name);
CREATE INDEX idx_tags_user_usage ON tags(user_id, usage_count);

-- Note tags junction table indexes
CREATE INDEX idx_note_tags_note ON note_tags(note_id);
CREATE INDEX idx_note_tags_tag ON note_tags(tag_id);

-- Task tags junction table indexes
CREATE INDEX idx_task_tags_task ON task_tags(task_id);
CREATE INDEX idx_task_tags_tag ON task_tags(tag_id);

-- Subtasks indexes
CREATE INDEX idx_subtasks_task ON subtasks(task_id);
CREATE INDEX idx_subtasks_user ON subtasks(user_id, created_at);

-- Audit logs indexes
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id, created_at);
CREATE INDEX idx_audit_logs_action ON audit_logs(action, created_at);
CREATE INDEX idx_audit_logs_resource ON audit_logs(resource_type, resource_id);

-- User sessions indexes
CREATE INDEX idx_user_sessions_user ON user_sessions(user_id, created_at);
CREATE INDEX idx_user_sessions_active ON user_sessions(user_id, is_active);

-- Rate limits indexes
CREATE INDEX idx_rate_limits_endpoint ON rate_limits(endpoint, created_at);
CREATE INDEX idx_rate_limits_ip ON rate_limits(ip_address, created_at);

-- User analytics indexes
CREATE INDEX idx_user_analytics_user ON user_analytics(user_id, recorded_at);
CREATE INDEX idx_user_analytics_metric ON user_analytics(metric_type, recorded_at);

-- Collaboration indexes
CREATE INDEX idx_collaboration_sessions_resource ON collaboration_sessions(resource_type, resource_id);
CREATE INDEX idx_collaboration_sessions_active ON collaboration_sessions(is_active, created_at);
CREATE INDEX idx_collaboration_events_session ON collaboration_events(session_id, timestamp);

-- Mobile sessions indexes
CREATE INDEX idx_mobile_sessions_user ON mobile_sessions(user_id, is_active);
CREATE INDEX idx_mobile_sessions_device ON mobile_sessions(device_id, is_active);

-- API keys indexes
CREATE INDEX idx_api_keys_user ON api_keys(user_id, is_active);
CREATE INDEX idx_api_keys_active ON api_keys(is_active, expires_at);

-- Plugin indexes
CREATE INDEX idx_plugins_active ON plugins(is_active, plugin_type);
CREATE INDEX idx_user_plugin_preferences ON user_plugin_preferences(user_id, plugin_id);

-- Full-text search indexes
ALTER TABLE notes ADD FULLTEXT(title, content);
ALTER TABLE tasks ADD FULLTEXT(title, description);

-- Composite indexes for common queries
CREATE INDEX idx_notes_user_archived_pinned ON notes(user_id, is_archived, is_pinned, created_at);
CREATE INDEX idx_tasks_user_status_due ON tasks(user_id, status, due_date);
CREATE INDEX idx_audit_logs_user_action_date ON audit_logs(user_id, action, created_at);
