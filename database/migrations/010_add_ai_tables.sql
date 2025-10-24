-- Migration 010: Add AI-related Tables
-- This migration adds tables for AI features, content analysis, and smart suggestions

-- Generated Content Table
CREATE TABLE IF NOT EXISTS generated_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('note', 'task', 'meeting', 'project', 'study', 'creative') NOT NULL,
    prompt TEXT NOT NULL,
    generated_content LONGTEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    model_used VARCHAR(100) DEFAULT NULL,
    tokens_used INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_generated_content_user (user_id),
    INDEX idx_generated_content_type (content_type),
    INDEX idx_generated_content_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Models Configuration Table
CREATE TABLE IF NOT EXISTS ai_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    provider VARCHAR(50) NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    max_tokens INT DEFAULT NULL,
    cost_per_token DECIMAL(10, 6) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ai_models_provider (provider),
    INDEX idx_ai_models_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Usage Tracking Table
CREATE TABLE IF NOT EXISTS ai_usage_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    model_id INT NOT NULL,
    operation_type ENUM('content_generation', 'content_analysis', 'smart_suggestions', 'translation', 'summarization') NOT NULL,
    tokens_used INT NOT NULL,
    cost DECIMAL(10, 6) DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
    INDEX idx_ai_usage_user (user_id),
    INDEX idx_ai_usage_model (model_id),
    INDEX idx_ai_usage_type (operation_type),
    INDEX idx_ai_usage_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Prompts Templates Table
CREATE TABLE IF NOT EXISTS ai_prompt_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('note', 'task', 'meeting', 'project', 'study', 'creative', 'analysis', 'translation') NOT NULL,
    template TEXT NOT NULL,
    variables JSON DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ai_prompt_templates_category (category),
    INDEX idx_ai_prompt_templates_public (is_public),
    INDEX idx_ai_prompt_templates_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Training Data Table
CREATE TABLE IF NOT EXISTS ai_training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('note', 'task', 'meeting', 'project', 'study', 'creative') NOT NULL,
    original_content LONGTEXT NOT NULL,
    processed_content LONGTEXT DEFAULT NULL,
    labels JSON DEFAULT NULL,
    quality_score DECIMAL(3, 2) DEFAULT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ai_training_data_user (user_id),
    INDEX idx_ai_training_data_type (content_type),
    INDEX idx_ai_training_data_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Feedback Table
CREATE TABLE IF NOT EXISTS ai_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    content_type ENUM('generated_content', 'smart_suggestions', 'content_analysis') NOT NULL,
    feedback_type ENUM('positive', 'negative', 'neutral') NOT NULL,
    rating INT DEFAULT NULL,
    comment TEXT DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ai_feedback_user (user_id),
    INDEX idx_ai_feedback_content (content_id, content_type),
    INDEX idx_ai_feedback_type (feedback_type),
    INDEX idx_ai_feedback_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Settings Table
CREATE TABLE IF NOT EXISTS ai_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_key),
    INDEX idx_ai_settings_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Conversations Table (for chat-like interactions)
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    context JSON DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ai_conversations_user (user_id),
    INDEX idx_ai_conversations_active (is_active),
    INDEX idx_ai_conversations_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Messages Table (for conversation history)
CREATE TABLE IF NOT EXISTS ai_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content LONGTEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE,
    INDEX idx_ai_messages_conversation (conversation_id),
    INDEX idx_ai_messages_role (role),
    INDEX idx_ai_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Knowledge Base Table
CREATE TABLE IF NOT EXISTS ai_knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    source VARCHAR(255) DEFAULT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ai_knowledge_base_category (category),
    INDEX idx_ai_knowledge_base_public (is_public),
    INDEX idx_ai_knowledge_base_usage (usage_count),
    FULLTEXT(title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Performance Metrics Table
CREATE TABLE IF NOT EXISTS ai_performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    metric_type ENUM('accuracy', 'response_time', 'user_satisfaction', 'cost_efficiency') NOT NULL,
    metric_value DECIMAL(10, 4) NOT NULL,
    sample_size INT DEFAULT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
    INDEX idx_ai_performance_model (model_id),
    INDEX idx_ai_performance_type (metric_type),
    INDEX idx_ai_performance_recorded (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default AI models
INSERT INTO ai_models (name, provider, model_id, description, max_tokens, cost_per_token) VALUES
('GPT-3.5 Turbo', 'OpenAI', 'gpt-3.5-turbo', 'Fast and efficient model for general tasks', 4096, 0.000002),
('GPT-4', 'OpenAI', 'gpt-4', 'Most capable model for complex tasks', 8192, 0.00003),
('Claude-3 Haiku', 'Anthropic', 'claude-3-haiku-20240307', 'Fast and cost-effective model', 200000, 0.00000025),
('Claude-3 Sonnet', 'Anthropic', 'claude-3-sonnet-20240229', 'Balanced performance and cost', 200000, 0.000003),
('Gemini Pro', 'Google', 'gemini-pro', 'Google\'s advanced language model', 30720, 0.0000005);

-- Insert default prompt templates
INSERT INTO ai_prompt_templates (name, category, template, description, is_public) VALUES
('Meeting Notes', 'meeting', 'Generate comprehensive meeting notes for: {meeting_title}\n\nParticipants: {participants}\nAgenda: {agenda}\n\nInclude:\n- Meeting summary\n- Key discussion points\n- Decisions made\n- Action items\n- Next steps', 'Template for generating meeting notes', TRUE),
('Project Plan', 'project', 'Create a detailed project plan for: {project_name}\n\nDescription: {description}\nTimeline: {timeline}\n\nInclude:\n- Project objectives\n- Key milestones\n- Task breakdown\n- Resource requirements\n- Risk assessment', 'Template for generating project plans', TRUE),
('Study Notes', 'study', 'Create comprehensive study notes for {subject}: {topic}\n\nLevel: {level}\n\nInclude:\n- Key concepts and definitions\n- Important examples\n- Practice questions\n- Summary points\n- Further reading suggestions', 'Template for generating study materials', TRUE),
('Task Breakdown', 'task', 'Create a detailed task breakdown for: {task_description}\n\nContext: {context}\n\nInclude:\n- Clear objectives\n- Specific steps\n- Required resources\n- Estimated time\n- Success criteria', 'Template for breaking down complex tasks', TRUE),
('Content Analysis', 'analysis', 'Analyze the following content and provide insights:\n\n{content}\n\nInclude:\n- Key themes\n- Sentiment analysis\n- Readability assessment\n- Improvement suggestions\n- Related topics', 'Template for content analysis', TRUE);
