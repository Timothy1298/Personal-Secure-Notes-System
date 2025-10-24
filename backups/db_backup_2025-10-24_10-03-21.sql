-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: personal
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ai_conversations`
--

DROP TABLE IF EXISTS `ai_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_conversations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `context` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_conversations_user` (`user_id`),
  KEY `idx_ai_conversations_active` (`is_active`),
  KEY `idx_ai_conversations_created` (`created_at`),
  CONSTRAINT `ai_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_conversations`
--

LOCK TABLES `ai_conversations` WRITE;
/*!40000 ALTER TABLE `ai_conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_feedback`
--

DROP TABLE IF EXISTS `ai_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content_id` int NOT NULL,
  `content_type` enum('generated_content','smart_suggestions','content_analysis') NOT NULL,
  `feedback_type` enum('positive','negative','neutral') NOT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_feedback_user` (`user_id`),
  KEY `idx_ai_feedback_content` (`content_id`,`content_type`),
  KEY `idx_ai_feedback_type` (`feedback_type`),
  KEY `idx_ai_feedback_created` (`created_at`),
  CONSTRAINT `ai_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_feedback`
--

LOCK TABLES `ai_feedback` WRITE;
/*!40000 ALTER TABLE `ai_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_knowledge_base`
--

DROP TABLE IF EXISTS `ai_knowledge_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_knowledge_base` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_ai_knowledge_base_category` (`category`),
  KEY `idx_ai_knowledge_base_public` (`is_public`),
  KEY `idx_ai_knowledge_base_usage` (`usage_count`),
  FULLTEXT KEY `title` (`title`,`content`),
  CONSTRAINT `ai_knowledge_base_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_knowledge_base`
--

LOCK TABLES `ai_knowledge_base` WRITE;
/*!40000 ALTER TABLE `ai_knowledge_base` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_knowledge_base` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_messages`
--

DROP TABLE IF EXISTS `ai_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `role` enum('user','assistant','system') NOT NULL,
  `content` longtext NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_messages_conversation` (`conversation_id`),
  KEY `idx_ai_messages_role` (`role`),
  KEY `idx_ai_messages_created` (`created_at`),
  CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_messages`
--

LOCK TABLES `ai_messages` WRITE;
/*!40000 ALTER TABLE `ai_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_models`
--

DROP TABLE IF EXISTS `ai_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `model_id` varchar(100) NOT NULL,
  `description` text,
  `max_tokens` int DEFAULT NULL,
  `cost_per_token` decimal(10,6) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_ai_models_provider` (`provider`),
  KEY `idx_ai_models_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_models`
--

LOCK TABLES `ai_models` WRITE;
/*!40000 ALTER TABLE `ai_models` DISABLE KEYS */;
INSERT INTO `ai_models` VALUES (1,'GPT-3.5 Turbo','OpenAI','gpt-3.5-turbo','Fast and efficient model for general tasks',4096,0.000002,1,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(2,'GPT-4','OpenAI','gpt-4','Most capable model for complex tasks',8192,0.000030,1,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(3,'Claude-3 Haiku','Anthropic','claude-3-haiku-20240307','Fast and cost-effective model',200000,0.000000,1,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(4,'Claude-3 Sonnet','Anthropic','claude-3-sonnet-20240229','Balanced performance and cost',200000,0.000003,1,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(5,'Gemini Pro','Google','gemini-pro','Google\'s advanced language model',30720,0.000001,1,'2025-10-23 22:22:07','2025-10-23 22:22:07');
/*!40000 ALTER TABLE `ai_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_performance_metrics`
--

DROP TABLE IF EXISTS `ai_performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_performance_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `model_id` int NOT NULL,
  `metric_type` enum('accuracy','response_time','user_satisfaction','cost_efficiency') NOT NULL,
  `metric_value` decimal(10,4) NOT NULL,
  `sample_size` int DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_performance_model` (`model_id`),
  KEY `idx_ai_performance_type` (`metric_type`),
  KEY `idx_ai_performance_recorded` (`recorded_at`),
  CONSTRAINT `ai_performance_metrics_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_performance_metrics`
--

LOCK TABLES `ai_performance_metrics` WRITE;
/*!40000 ALTER TABLE `ai_performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_prompt_templates`
--

DROP TABLE IF EXISTS `ai_prompt_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_prompt_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` enum('note','task','meeting','project','study','creative','analysis','translation') NOT NULL,
  `template` text NOT NULL,
  `variables` json DEFAULT NULL,
  `description` text,
  `is_public` tinyint(1) DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_ai_prompt_templates_category` (`category`),
  KEY `idx_ai_prompt_templates_public` (`is_public`),
  KEY `idx_ai_prompt_templates_usage` (`usage_count`),
  CONSTRAINT `ai_prompt_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_prompt_templates`
--

LOCK TABLES `ai_prompt_templates` WRITE;
/*!40000 ALTER TABLE `ai_prompt_templates` DISABLE KEYS */;
INSERT INTO `ai_prompt_templates` VALUES (1,'Meeting Notes','meeting','Generate comprehensive meeting notes for: {meeting_title}\n\nParticipants: {participants}\nAgenda: {agenda}\n\nInclude:\n- Meeting summary\n- Key discussion points\n- Decisions made\n- Action items\n- Next steps',NULL,'Template for generating meeting notes',1,0,NULL,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(2,'Project Plan','project','Create a detailed project plan for: {project_name}\n\nDescription: {description}\nTimeline: {timeline}\n\nInclude:\n- Project objectives\n- Key milestones\n- Task breakdown\n- Resource requirements\n- Risk assessment',NULL,'Template for generating project plans',1,0,NULL,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(3,'Study Notes','study','Create comprehensive study notes for {subject}: {topic}\n\nLevel: {level}\n\nInclude:\n- Key concepts and definitions\n- Important examples\n- Practice questions\n- Summary points\n- Further reading suggestions',NULL,'Template for generating study materials',1,0,NULL,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(4,'Task Breakdown','task','Create a detailed task breakdown for: {task_description}\n\nContext: {context}\n\nInclude:\n- Clear objectives\n- Specific steps\n- Required resources\n- Estimated time\n- Success criteria',NULL,'Template for breaking down complex tasks',1,0,NULL,'2025-10-23 22:22:07','2025-10-23 22:22:07'),(5,'Content Analysis','analysis','Analyze the following content and provide insights:\n\n{content}\n\nInclude:\n- Key themes\n- Sentiment analysis\n- Readability assessment\n- Improvement suggestions\n- Related topics',NULL,'Template for content analysis',1,0,NULL,'2025-10-23 22:22:07','2025-10-23 22:22:07');
/*!40000 ALTER TABLE `ai_prompt_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_settings`
--

DROP TABLE IF EXISTS `ai_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`),
  KEY `idx_ai_settings_user` (`user_id`),
  CONSTRAINT `ai_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_settings`
--

LOCK TABLES `ai_settings` WRITE;
/*!40000 ALTER TABLE `ai_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_suggestions`
--

DROP TABLE IF EXISTS `ai_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_suggestions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `suggestion_type` enum('note_summary','task_priority','content_suggestion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `is_accepted` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_suggestion_type` (`suggestion_type`),
  CONSTRAINT `ai_suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_suggestions`
--

LOCK TABLES `ai_suggestions` WRITE;
/*!40000 ALTER TABLE `ai_suggestions` DISABLE KEYS */;
INSERT INTO `ai_suggestions` VALUES (1,1,'note_summary','Consider adding a timeline visualization to your project planning notes for better clarity.','{\"note_id\": 1, \"confidence\": 0.85}',NULL,'2025-10-14 15:02:25'),(2,1,'task_priority','Your health checkup task might benefit from being scheduled earlier given your current workload.','{\"task_id\": 2, \"current_priority\": \"medium\", \"suggested_priority\": \"high\"}',NULL,'2025-10-14 15:02:25'),(3,1,'content_suggestion','Your investment portfolio could benefit from adding REITs for better diversification.','{\"context\": \"portfolio_review\", \"note_id\": 3}',1,'2025-10-14 15:02:25'),(4,1,'task_priority','The Spanish learning task has been in progress for a while. Consider breaking it into smaller milestones.','{\"task_id\": 6, \"suggestion\": \"break_into_milestones\"}',NULL,'2025-10-14 15:02:25'),(5,1,'content_suggestion','Your travel itinerary could include backup plans for weather contingencies.','{\"context\": \"travel_planning\", \"note_id\": 4}',NULL,'2025-10-14 15:02:25');
/*!40000 ALTER TABLE `ai_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_training_data`
--

DROP TABLE IF EXISTS `ai_training_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_training_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content_type` enum('note','task','meeting','project','study','creative') NOT NULL,
  `original_content` longtext NOT NULL,
  `processed_content` longtext,
  `labels` json DEFAULT NULL,
  `quality_score` decimal(3,2) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_training_data_user` (`user_id`),
  KEY `idx_ai_training_data_type` (`content_type`),
  KEY `idx_ai_training_data_approved` (`is_approved`),
  CONSTRAINT `ai_training_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_training_data`
--

LOCK TABLES `ai_training_data` WRITE;
/*!40000 ALTER TABLE `ai_training_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_training_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_usage_tracking`
--

DROP TABLE IF EXISTS `ai_usage_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_usage_tracking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `model_id` int NOT NULL,
  `operation_type` enum('content_generation','content_analysis','smart_suggestions','translation','summarization') NOT NULL,
  `tokens_used` int NOT NULL,
  `cost` decimal(10,6) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_usage_user` (`user_id`),
  KEY `idx_ai_usage_model` (`model_id`),
  KEY `idx_ai_usage_type` (`operation_type`),
  KEY `idx_ai_usage_created` (`created_at`),
  CONSTRAINT `ai_usage_tracking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_usage_tracking_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_usage_tracking`
--

LOCK TABLES `ai_usage_tracking` WRITE;
/*!40000 ALTER TABLE `ai_usage_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_usage_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alert_rules`
--

DROP TABLE IF EXISTS `alert_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(255) NOT NULL,
  `rule_type` enum('threshold','anomaly','pattern') NOT NULL,
  `metric_name` varchar(255) NOT NULL,
  `condition_operator` enum('>','<','>=','<=','=','!=') NOT NULL,
  `threshold_value` decimal(10,3) NOT NULL,
  `time_window` int NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rule_name` (`rule_name`),
  KEY `idx_rule_type` (`rule_type`),
  KEY `idx_metric_name` (`metric_name`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `alert_rules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_rules`
--

LOCK TABLES `alert_rules` WRITE;
/*!40000 ALTER TABLE `alert_rules` DISABLE KEYS */;
INSERT INTO `alert_rules` VALUES (1,'High Response Time','threshold','response_time','>',2000.000,300,'high',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(2,'High Error Rate','threshold','error_rate','>',10.000,300,'critical',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(3,'High Memory Usage','threshold','memory_usage','>',90.000,300,'high',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(4,'Slow Database Queries','threshold','slow_query_count','>',5.000,300,'medium',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(5,'High CPU Usage','threshold','cpu_usage','>',80.000,300,'medium',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15');
/*!40000 ALTER TABLE `alert_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_used` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`),
  KEY `is_active` (`is_active`),
  KEY `idx_api_keys_key` (`api_key`),
  KEY `idx_api_keys_user` (`user_id`),
  CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_rate_limits`
--

DROP TABLE IF EXISTS `api_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `api_key_id` int DEFAULT NULL,
  `endpoint` varchar(255) NOT NULL,
  `requests_count` int DEFAULT '1',
  `window_start` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `idx_api_rate_limits_user` (`user_id`),
  KEY `idx_api_rate_limits_endpoint` (`endpoint`),
  CONSTRAINT `api_rate_limits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `api_rate_limits_ibfk_2` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_rate_limits`
--

LOCK TABLES `api_rate_limits` WRITE;
/*!40000 ALTER TABLE `api_rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_usage_logs`
--

DROP TABLE IF EXISTS `api_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_usage_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `api_key_id` int DEFAULT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `request_data` json DEFAULT NULL,
  `response_status` int DEFAULT NULL,
  `response_time_ms` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `idx_api_usage_logs_user` (`user_id`),
  KEY `idx_api_usage_logs_endpoint` (`endpoint`),
  KEY `idx_api_usage_logs_created` (`created_at`),
  CONSTRAINT `api_usage_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `api_usage_logs_ibfk_2` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_usage_logs`
--

LOCK TABLES `api_usage_logs` WRITE;
/*!40000 ALTER TABLE `api_usage_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_usage_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_webhook_deliveries`
--

DROP TABLE IF EXISTS `api_webhook_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_webhook_deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `webhook_id` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` json NOT NULL,
  `response_status` int DEFAULT NULL,
  `response_body` text,
  `attempts` int DEFAULT '1',
  `max_attempts` int DEFAULT '3',
  `next_retry_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_api_webhook_deliveries_webhook` (`webhook_id`),
  KEY `idx_api_webhook_deliveries_retry` (`next_retry_at`),
  CONSTRAINT `api_webhook_deliveries_ibfk_1` FOREIGN KEY (`webhook_id`) REFERENCES `api_webhooks` (`webhook_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_webhook_deliveries`
--

LOCK TABLES `api_webhook_deliveries` WRITE;
/*!40000 ALTER TABLE `api_webhook_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_webhook_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_webhooks`
--

DROP TABLE IF EXISTS `api_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_webhooks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `webhook_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `events` json NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_triggered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `webhook_id` (`webhook_id`),
  KEY `idx_api_webhooks_user` (`user_id`),
  KEY `idx_api_webhooks_webhook_id` (`webhook_id`),
  CONSTRAINT `api_webhooks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_webhooks`
--

LOCK TABLES `api_webhooks` WRITE;
/*!40000 ALTER TABLE `api_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apm_errors`
--

DROP TABLE IF EXISTS `apm_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apm_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `error_type` varchar(255) NOT NULL,
  `error_message` text NOT NULL,
  `error_code` int DEFAULT NULL,
  `file` varchar(500) DEFAULT NULL,
  `line` int DEFAULT NULL,
  `trace` text,
  `context` json DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `timestamp` decimal(20,6) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_error_type` (`error_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apm_errors`
--

LOCK TABLES `apm_errors` WRITE;
/*!40000 ALTER TABLE `apm_errors` DISABLE KEYS */;
/*!40000 ALTER TABLE `apm_errors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apm_events`
--

DROP TABLE IF EXISTS `apm_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apm_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `data` json DEFAULT NULL,
  `context` json DEFAULT NULL,
  `timestamp` decimal(20,6) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_name` (`event_name`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apm_events`
--

LOCK TABLES `apm_events` WRITE;
/*!40000 ALTER TABLE `apm_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `apm_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apm_queries`
--

DROP TABLE IF EXISTS `apm_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apm_queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query` text NOT NULL,
  `duration` decimal(10,3) NOT NULL,
  `context` json DEFAULT NULL,
  `is_slow` tinyint(1) DEFAULT '0',
  `timestamp` decimal(20,6) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_duration` (`duration`),
  KEY `idx_is_slow` (`is_slow`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apm_queries`
--

LOCK TABLES `apm_queries` WRITE;
/*!40000 ALTER TABLE `apm_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `apm_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apm_transactions`
--

DROP TABLE IF EXISTS `apm_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apm_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_name` varchar(255) NOT NULL,
  `start_time` decimal(20,6) NOT NULL,
  `end_time` decimal(20,6) DEFAULT NULL,
  `duration` decimal(10,3) DEFAULT NULL,
  `start_memory` bigint NOT NULL,
  `end_memory` bigint DEFAULT NULL,
  `memory_delta` bigint DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` enum('started','completed','failed') DEFAULT 'started',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_transaction_name` (`transaction_name`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_duration` (`duration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apm_transactions`
--

LOCK TABLES `apm_transactions` WRITE;
/*!40000 ALTER TABLE `apm_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `apm_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` int DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'user_registered','security',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"email\": \"Tkuria30@gmail.com\", \"username\": \"timothy\"}','2025-10-14 14:09:03'),(2,1,'login_success','security',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-14 14:09:12'),(3,1,'created note: Coding',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 14:28:38'),(4,1,'created task: Visit the hospital',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 14:37:34'),(5,1,'note_created','note',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"title\": \"Project Planning Meeting Notes\", \"priority\": \"high\"}','2025-10-14 15:02:25'),(6,1,'note_updated','note',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"changes\": [\"timeline\", \"resources\"]}','2025-10-14 15:02:25'),(7,1,'task_created','task',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"title\": \"Complete Q4 Project Proposal\", \"priority\": \"urgent\"}','2025-10-14 15:02:25'),(8,1,'task_completed','task',5,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"completion_time\": \"2025-10-10 16:00:00\"}','2025-10-14 15:02:25'),(9,1,'tag_created','tag',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"name\": \"Work\", \"color\": \"#3b82f6\"}','2025-10-14 15:02:25'),(10,1,'note_tagged','note',2,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"tag_id\": 3, \"tag_name\": \"Health\"}','2025-10-14 15:02:25'),(11,1,'task_tagged','task',2,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"tag_id\": 3, \"tag_name\": \"Health\"}','2025-10-14 15:02:25'),(12,1,'subtask_created','subtask',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"title\": \"Research market trends\", \"task_id\": 1}','2025-10-14 15:02:25'),(13,1,'reminder_set','task',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"type\": \"email\", \"reminder_time\": \"2025-10-19 09:00:00\"}','2025-10-14 15:02:25'),(14,1,'preferences_updated','user_preferences',1,'192.168.1.100','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36','{\"theme\": \"light\", \"notifications\": true}','2025-10-14 15:02:25'),(15,1,'logout',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 17:21:51'),(16,1,'login_success','security',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-14 17:49:42'),(17,1,'archived note with ID: 2',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 18:33:14'),(18,1,'deleted note with ID: 1',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 19:19:59'),(19,1,'login_success','security',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-14 20:15:07'),(20,1,'moved note to trash with ID: 11',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-14 21:19:03'),(21,1,'login_success','user',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-15 10:37:34'),(22,1,'unpinned note with ID: 4',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-15 12:14:38'),(23,1,'archived note with ID: 2',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-15 12:38:23'),(24,1,'unpinned note with ID: 6',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-15 12:38:55'),(25,1,'archived note with ID: 10',NULL,NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,'2025-10-15 12:39:28'),(27,1,'login_success','user',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-23 21:05:17'),(28,1,'login_success','user',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-23 23:07:41'),(29,1,'login_success','user',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','{\"ip\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\"}','2025-10-24 09:59:33');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_trail`
--

DROP TABLE IF EXISTS `audit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_trail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_trail`
--

LOCK TABLES `audit_trail` WRITE;
/*!40000 ALTER TABLE `audit_trail` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_trail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automation_executions`
--

DROP TABLE IF EXISTS `automation_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `automation_executions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trigger_id` int NOT NULL,
  `user_id` int NOT NULL,
  `execution_data` json DEFAULT NULL,
  `status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trigger_id` (`trigger_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `automation_executions_ibfk_1` FOREIGN KEY (`trigger_id`) REFERENCES `automation_triggers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `automation_executions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automation_executions`
--

LOCK TABLES `automation_executions` WRITE;
/*!40000 ALTER TABLE `automation_executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `automation_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `automation_triggers`
--

DROP TABLE IF EXISTS `automation_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `automation_triggers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `trigger_type` varchar(100) NOT NULL,
  `trigger_data` json NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_data` json NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `automation_triggers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automation_triggers`
--

LOCK TABLES `automation_triggers` WRITE;
/*!40000 ALTER TABLE `automation_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `automation_triggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_history`
--

DROP TABLE IF EXISTS `backup_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `backup_type` enum('full','notes','tasks') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint NOT NULL,
  `status` enum('success','failed','in_progress') DEFAULT 'in_progress',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_history`
--

LOCK TABLES `backup_history` WRITE;
/*!40000 ALTER TABLE `backup_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `backup_type` enum('manual','automatic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `backup_size` bigint DEFAULT NULL,
  `backup_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('success','failed','in_progress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_backup_type` (`backup_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
INSERT INTO `backup_logs` VALUES (1,'automatic',15728640,'/backups/secure_notes_2025_10_14_auto.sql','success',NULL,'2025-10-14 15:02:25'),(2,'manual',16384000,'/backups/secure_notes_2025_10_13_manual.sql','success',NULL,'2025-10-14 15:02:25'),(3,'automatic',15204352,'/backups/secure_notes_2025_10_12_auto.sql','success',NULL,'2025-10-14 15:02:25'),(4,'automatic',0,'/backups/secure_notes_2025_10_11_auto.sql','failed','Disk space insufficient','2025-10-14 15:02:25'),(5,'manual',14548992,'/backups/secure_notes_2025_10_10_manual.sql','success',NULL,'2025-10-14 15:02:25');
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_operations`
--

DROP TABLE IF EXISTS `backup_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_operations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `backup_type` varchar(50) NOT NULL,
  `backup_path` varchar(500) NOT NULL,
  `backup_size` bigint NOT NULL,
  `compression_ratio` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `backup_operations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_operations`
--

LOCK TABLES `backup_operations` WRITE;
/*!40000 ALTER TABLE `backup_operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_schedules`
--

DROP TABLE IF EXISTS `backup_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `schedule_name` varchar(255) NOT NULL,
  `backup_type` enum('full','incremental','differential') NOT NULL,
  `schedule_cron` varchar(100) NOT NULL,
  `retention_days` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_next_run` (`next_run`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_schedules`
--

LOCK TABLES `backup_schedules` WRITE;
/*!40000 ALTER TABLE `backup_schedules` DISABLE KEYS */;
INSERT INTO `backup_schedules` VALUES (1,'Daily Full Backup','full','0 2 * * *',30,1,NULL,NULL,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(2,'Hourly Incremental Backup','incremental','0 * * * *',7,1,NULL,NULL,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(3,'Weekly Full Backup','full','0 3 * * 0',90,1,NULL,NULL,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(4,'Monthly Full Backup','full','0 4 1 * *',365,1,NULL,NULL,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(5,'Daily Full Backup','full','0 2 * * *',30,1,NULL,NULL,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(6,'Hourly Incremental Backup','incremental','0 * * * *',7,1,NULL,NULL,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(7,'Weekly Full Backup','full','0 3 * * 0',90,1,NULL,NULL,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(8,'Monthly Full Backup','full','0 4 1 * *',365,1,NULL,NULL,'2025-10-24 13:03:14','2025-10-24 13:03:14');
/*!40000 ALTER TABLE `backup_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_verification`
--

DROP TABLE IF EXISTS `backup_verification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_verification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `backup_id` varchar(100) NOT NULL,
  `verification_type` enum('integrity','restore_test','data_validation') NOT NULL,
  `status` enum('passed','failed','pending') NOT NULL,
  `verification_details` json DEFAULT NULL,
  `verified_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_status` (`status`),
  KEY `idx_verified_at` (`verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_verification`
--

LOCK TABLES `backup_verification` WRITE;
/*!40000 ALTER TABLE `backup_verification` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_verification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cache_key`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collaboration_comments`
--

DROP TABLE IF EXISTS `collaboration_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collaboration_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resource_type` enum('note','task') NOT NULL,
  `resource_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_resolved` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `collaboration_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `collaboration_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `collaboration_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collaboration_comments`
--

LOCK TABLES `collaboration_comments` WRITE;
/*!40000 ALTER TABLE `collaboration_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `collaboration_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collaboration_events`
--

DROP TABLE IF EXISTS `collaboration_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collaboration_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `event_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_data` json DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`),
  CONSTRAINT `collaboration_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collaboration_events`
--

LOCK TABLES `collaboration_events` WRITE;
/*!40000 ALTER TABLE `collaboration_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `collaboration_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collaboration_mentions`
--

DROP TABLE IF EXISTS `collaboration_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collaboration_mentions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `comment_id` int NOT NULL,
  `mentioned_user_id` int NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comment_mention` (`comment_id`,`mentioned_user_id`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_mentioned_user_id` (`mentioned_user_id`),
  KEY `idx_is_read` (`is_read`),
  CONSTRAINT `collaboration_mentions_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `collaboration_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `collaboration_mentions_ibfk_2` FOREIGN KEY (`mentioned_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collaboration_mentions`
--

LOCK TABLES `collaboration_mentions` WRITE;
/*!40000 ALTER TABLE `collaboration_mentions` DISABLE KEYS */;
/*!40000 ALTER TABLE `collaboration_mentions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collaboration_sessions`
--

DROP TABLE IF EXISTS `collaboration_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collaboration_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` enum('note','task') COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` int NOT NULL,
  `owner_id` int NOT NULL,
  `participants` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `resource_type_id` (`resource_type`,`resource_id`),
  KEY `owner_id` (`owner_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `collaboration_sessions_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collaboration_sessions`
--

LOCK TABLES `collaboration_sessions` WRITE;
/*!40000 ALTER TABLE `collaboration_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `collaboration_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compliance_events`
--

DROP TABLE IF EXISTS `compliance_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `user_id` int DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `compliance_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compliance_events`
--

LOCK TABLES `compliance_events` WRITE;
/*!40000 ALTER TABLE `compliance_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `compliance_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compliance_policies`
--

DROP TABLE IF EXISTS `compliance_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_policies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(255) NOT NULL,
  `policy_type` enum('gdpr','hipaa','sox','iso27001','custom') NOT NULL,
  `policy_content` text NOT NULL,
  `version` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `effective_date` datetime NOT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_policy_name` (`policy_name`),
  KEY `idx_policy_type` (`policy_type`),
  KEY `idx_version` (`version`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_effective_date` (`effective_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `compliance_policies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compliance_policies`
--

LOCK TABLES `compliance_policies` WRITE;
/*!40000 ALTER TABLE `compliance_policies` DISABLE KEYS */;
INSERT INTO `compliance_policies` VALUES (1,'GDPR Data Protection Policy','gdpr','This policy outlines how we handle personal data in compliance with GDPR requirements.','1.0',1,'2025-10-24 12:05:05',NULL,1,'2025-10-24 12:05:05','2025-10-24 12:05:05'),(2,'HIPAA Privacy Policy','hipaa','This policy outlines how we protect health information in compliance with HIPAA requirements.','1.0',1,'2025-10-24 12:05:05',NULL,1,'2025-10-24 12:05:05','2025-10-24 12:05:05'),(3,'SOX Compliance Policy','sox','This policy outlines our financial controls and audit procedures in compliance with SOX requirements.','1.0',1,'2025-10-24 12:05:05',NULL,1,'2025-10-24 12:05:05','2025-10-24 12:05:05'),(4,'Information Security Policy','iso27001','This policy outlines our information security controls and procedures.','1.0',1,'2025-10-24 12:05:05',NULL,1,'2025-10-24 12:05:05','2025-10-24 12:05:05');
/*!40000 ALTER TABLE `compliance_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connection_pool_stats`
--

DROP TABLE IF EXISTS `connection_pool_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connection_pool_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pool_name` varchar(100) NOT NULL,
  `active_connections` int NOT NULL,
  `idle_connections` int NOT NULL,
  `max_connections` int NOT NULL,
  `utilization_percent` decimal(5,2) NOT NULL,
  `avg_response_time_ms` decimal(10,3) DEFAULT NULL,
  `total_requests` int DEFAULT '0',
  `failed_requests` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pool_name` (`pool_name`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connection_pool_stats`
--

LOCK TABLES `connection_pool_stats` WRITE;
/*!40000 ALTER TABLE `connection_pool_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `connection_pool_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_analysis`
--

DROP TABLE IF EXISTS `content_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_analysis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content_id` int NOT NULL,
  `content_type` enum('note','task') NOT NULL,
  `analysis_type` enum('sentiment','keywords','summary','readability','topics') NOT NULL,
  `analysis_data` json NOT NULL,
  `confidence` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_content_analysis_user` (`user_id`),
  KEY `idx_content_analysis_content` (`content_id`,`content_type`),
  KEY `idx_content_analysis_type` (`analysis_type`),
  KEY `idx_content_analysis_created` (`created_at`),
  CONSTRAINT `content_analysis_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_analysis`
--

LOCK TABLES `content_analysis` WRITE;
/*!40000 ALTER TABLE `content_analysis` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_analysis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_block_references`
--

DROP TABLE IF EXISTS `content_block_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_block_references` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `document_id` int NOT NULL,
  `document_type` enum('note','task') NOT NULL,
  `position` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_content_block_refs_block` (`block_id`),
  KEY `idx_content_block_refs_document` (`document_id`,`document_type`),
  KEY `idx_content_block_refs_position` (`position`),
  CONSTRAINT `content_block_references_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `content_blocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_block_references`
--

LOCK TABLES `content_block_references` WRITE;
/*!40000 ALTER TABLE `content_block_references` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_block_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_blocks`
--

DROP TABLE IF EXISTS `content_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `block_type` enum('text','image','code','table','list','quote','custom') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `metadata` json DEFAULT NULL,
  `is_reusable` tinyint(1) DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_content_blocks_user` (`user_id`),
  KEY `idx_content_blocks_type` (`block_type`),
  KEY `idx_content_blocks_reusable` (`is_reusable`),
  KEY `idx_content_blocks_usage` (`usage_count`),
  CONSTRAINT `content_blocks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_blocks`
--

LOCK TABLES `content_blocks` WRITE;
/*!40000 ALTER TABLE `content_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_interaction_analytics`
--

DROP TABLE IF EXISTS `content_interaction_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_interaction_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content_type` varchar(50) NOT NULL,
  `content_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `content_interaction_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_interaction_analytics`
--

LOCK TABLES `content_interaction_analytics` WRITE;
/*!40000 ALTER TABLE `content_interaction_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_interaction_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_widgets`
--

DROP TABLE IF EXISTS `dashboard_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_widgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dashboard_id` int NOT NULL,
  `widget_type` varchar(100) NOT NULL,
  `widget_name` varchar(255) NOT NULL,
  `position_x` int NOT NULL,
  `position_y` int NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `config` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dashboard_id` (`dashboard_id`),
  KEY `idx_widget_type` (`widget_type`),
  CONSTRAINT `dashboard_widgets_ibfk_1` FOREIGN KEY (`dashboard_id`) REFERENCES `monitoring_dashboards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_widgets`
--

LOCK TABLES `dashboard_widgets` WRITE;
/*!40000 ALTER TABLE `dashboard_widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboard_widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_subject_requests`
--

DROP TABLE IF EXISTS `data_subject_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_subject_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `request_type` enum('access','portability','rectification','erasure','restriction','objection') NOT NULL,
  `status` enum('pending','in_progress','completed','rejected') DEFAULT 'pending',
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_request_type` (`request_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `data_subject_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_subject_requests`
--

LOCK TABLES `data_subject_requests` WRITE;
/*!40000 ALTER TABLE `data_subject_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_subject_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_sync_operations`
--

DROP TABLE IF EXISTS `data_sync_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_sync_operations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `progress_percentage` int DEFAULT '0',
  `total_items` int DEFAULT '0',
  `processed_items` int DEFAULT '0',
  `error_message` text,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `data_sync_operations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_sync_operations`
--

LOCK TABLES `data_sync_operations` WRITE;
/*!40000 ALTER TABLE `data_sync_operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_sync_operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_validation_results`
--

DROP TABLE IF EXISTS `data_validation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_validation_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `validation_type` varchar(50) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `validation_status` enum('valid','invalid','warning') NOT NULL,
  `total_records` int DEFAULT '0',
  `valid_records` int DEFAULT '0',
  `invalid_records` int DEFAULT '0',
  `warning_records` int DEFAULT '0',
  `validation_details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `data_validation_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_validation_results`
--

LOCK TABLES `data_validation_results` WRITE;
/*!40000 ALTER TABLE `data_validation_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_validation_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_config`
--

DROP TABLE IF EXISTS `database_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  `recommended_value` text,
  `description` text,
  `is_optimized` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config_key` (`config_key`),
  KEY `idx_is_optimized` (`is_optimized`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_config`
--

LOCK TABLES `database_config` WRITE;
/*!40000 ALTER TABLE `database_config` DISABLE KEYS */;
INSERT INTO `database_config` VALUES (1,'innodb_buffer_pool_size','134217728','1073741824','Buffer pool size in bytes (128MB -> 1GB)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(2,'max_connections','151','200','Maximum number of connections',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(3,'query_cache_size','0','67108864','Query cache size in bytes (0 -> 64MB)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(4,'tmp_table_size','16777216','67108864','Temporary table size in bytes (16MB -> 64MB)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(5,'max_heap_table_size','16777216','67108864','Maximum heap table size in bytes (16MB -> 64MB)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(6,'innodb_log_file_size','50331648','134217728','InnoDB log file size in bytes (48MB -> 128MB)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23'),(7,'innodb_flush_log_at_trx_commit','1','2','InnoDB flush log at transaction commit (1 -> 2 for better performance)',0,'2025-10-24 10:58:23','2025-10-24 10:58:23');
/*!40000 ALTER TABLE `database_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_health_checks`
--

DROP TABLE IF EXISTS `database_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_health_checks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `check_type` varchar(50) NOT NULL,
  `check_name` varchar(255) NOT NULL,
  `status` enum('healthy','warning','critical') NOT NULL,
  `value` decimal(10,3) DEFAULT NULL,
  `threshold` decimal(10,3) DEFAULT NULL,
  `message` text,
  `details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_check_type` (`check_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_health_checks`
--

LOCK TABLES `database_health_checks` WRITE;
/*!40000 ALTER TABLE `database_health_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `database_health_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_performance_metrics`
--

DROP TABLE IF EXISTS `database_performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_performance_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query_hash` varchar(64) NOT NULL,
  `query_text` text NOT NULL,
  `execution_time_ms` int NOT NULL,
  `rows_affected` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_performance_metrics`
--

LOCK TABLES `database_performance_metrics` WRITE;
/*!40000 ALTER TABLE `database_performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `database_performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disaster_recovery_plans`
--

DROP TABLE IF EXISTS `disaster_recovery_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disaster_recovery_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(255) NOT NULL,
  `disaster_type` varchar(100) NOT NULL,
  `plan_description` text,
  `rto_minutes` int NOT NULL,
  `rpo_minutes` int NOT NULL,
  `plan_steps` json NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_disaster_type` (`disaster_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disaster_recovery_plans`
--

LOCK TABLES `disaster_recovery_plans` WRITE;
/*!40000 ALTER TABLE `disaster_recovery_plans` DISABLE KEYS */;
INSERT INTO `disaster_recovery_plans` VALUES (1,'Database Corruption Recovery','database_corruption','Recovery plan for database corruption scenarios',30,15,'[{\"name\": \"Stop Application\", \"action\": \"stop_application\", \"timeout\": 300}, {\"name\": \"Restore Database\", \"action\": \"restore_database\", \"timeout\": 1800}, {\"name\": \"Verify Data Integrity\", \"action\": \"verify_data_integrity\", \"timeout\": 600}, {\"name\": \"Start Application\", \"action\": \"start_application\", \"timeout\": 300}]',1,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(2,'Server Failure Recovery','server_failure','Recovery plan for server failure scenarios',60,30,'[{\"name\": \"Failover to Backup Server\", \"action\": \"failover_server\", \"timeout\": 1800}, {\"name\": \"Restore Data\", \"action\": \"restore_data\", \"timeout\": 3600}, {\"name\": \"Update DNS\", \"action\": \"update_dns\", \"timeout\": 300}, {\"name\": \"Verify Services\", \"action\": \"verify_services\", \"timeout\": 600}]',1,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(3,'Data Center Failure Recovery','data_center_failure','Recovery plan for data center failure scenarios',240,60,'[{\"name\": \"Activate DR Site\", \"action\": \"activate_dr_site\", \"timeout\": 3600}, {\"name\": \"Restore from Backup\", \"action\": \"restore_from_backup\", \"timeout\": 7200}, {\"name\": \"Update Network Configuration\", \"action\": \"update_network_config\", \"timeout\": 1800}, {\"name\": \"Verify System Health\", \"action\": \"verify_system_health\", \"timeout\": 1200}]',1,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(4,'Cyber Attack Recovery','cyber_attack','Recovery plan for cyber attack scenarios',480,120,'[{\"name\": \"Isolate Systems\", \"action\": \"isolate_systems\", \"timeout\": 600}, {\"name\": \"Assess Damage\", \"action\": \"assess_damage\", \"timeout\": 1800}, {\"name\": \"Clean Systems\", \"action\": \"clean_systems\", \"timeout\": 3600}, {\"name\": \"Restore from Clean Backup\", \"action\": \"restore_from_backup\", \"timeout\": 7200}, {\"name\": \"Implement Security Measures\", \"action\": \"implement_security\", \"timeout\": 1800}, {\"name\": \"Verify System Security\", \"action\": \"verify_security\", \"timeout\": 1200}]',1,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(5,'Natural Disaster Recovery','natural_disaster','Recovery plan for natural disaster scenarios',1440,240,'[{\"name\": \"Assess Infrastructure Damage\", \"action\": \"assess_damage\", \"timeout\": 3600}, {\"name\": \"Activate DR Site\", \"action\": \"activate_dr_site\", \"timeout\": 7200}, {\"name\": \"Restore from Backup\", \"action\": \"restore_from_backup\", \"timeout\": 14400}, {\"name\": \"Update Network Configuration\", \"action\": \"update_network_config\", \"timeout\": 3600}, {\"name\": \"Verify System Health\", \"action\": \"verify_system_health\", \"timeout\": 2400}]',1,'2025-10-24 11:41:14','2025-10-24 11:41:14'),(6,'Database Corruption Recovery','database_corruption','Recovery plan for database corruption scenarios',30,15,'[{\"name\": \"Stop Application\", \"action\": \"stop_application\", \"timeout\": 300}, {\"name\": \"Restore Database\", \"action\": \"restore_database\", \"timeout\": 1800}, {\"name\": \"Verify Data Integrity\", \"action\": \"verify_data_integrity\", \"timeout\": 600}, {\"name\": \"Start Application\", \"action\": \"start_application\", \"timeout\": 300}]',1,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(7,'Server Failure Recovery','server_failure','Recovery plan for server failure scenarios',60,30,'[{\"name\": \"Failover to Backup Server\", \"action\": \"failover_server\", \"timeout\": 1800}, {\"name\": \"Restore Data\", \"action\": \"restore_data\", \"timeout\": 3600}, {\"name\": \"Update DNS\", \"action\": \"update_dns\", \"timeout\": 300}, {\"name\": \"Verify Services\", \"action\": \"verify_services\", \"timeout\": 600}]',1,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(8,'Data Center Failure Recovery','data_center_failure','Recovery plan for data center failure scenarios',240,60,'[{\"name\": \"Activate DR Site\", \"action\": \"activate_dr_site\", \"timeout\": 3600}, {\"name\": \"Restore from Backup\", \"action\": \"restore_from_backup\", \"timeout\": 7200}, {\"name\": \"Update Network Configuration\", \"action\": \"update_network_config\", \"timeout\": 1800}, {\"name\": \"Verify System Health\", \"action\": \"verify_system_health\", \"timeout\": 1200}]',1,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(9,'Cyber Attack Recovery','cyber_attack','Recovery plan for cyber attack scenarios',480,120,'[{\"name\": \"Isolate Systems\", \"action\": \"isolate_systems\", \"timeout\": 600}, {\"name\": \"Assess Damage\", \"action\": \"assess_damage\", \"timeout\": 1800}, {\"name\": \"Clean Systems\", \"action\": \"clean_systems\", \"timeout\": 3600}, {\"name\": \"Restore from Clean Backup\", \"action\": \"restore_from_backup\", \"timeout\": 7200}, {\"name\": \"Implement Security Measures\", \"action\": \"implement_security\", \"timeout\": 1800}, {\"name\": \"Verify System Security\", \"action\": \"verify_security\", \"timeout\": 1200}]',1,'2025-10-24 13:03:14','2025-10-24 13:03:14'),(10,'Natural Disaster Recovery','natural_disaster','Recovery plan for natural disaster scenarios',1440,240,'[{\"name\": \"Assess Infrastructure Damage\", \"action\": \"assess_damage\", \"timeout\": 3600}, {\"name\": \"Activate DR Site\", \"action\": \"activate_dr_site\", \"timeout\": 7200}, {\"name\": \"Restore from Backup\", \"action\": \"restore_from_backup\", \"timeout\": 14400}, {\"name\": \"Update Network Configuration\", \"action\": \"update_network_config\", \"timeout\": 3600}, {\"name\": \"Verify System Health\", \"action\": \"verify_system_health\", \"timeout\": 2400}]',1,'2025-10-24 13:03:14','2025-10-24 13:03:14');
/*!40000 ALTER TABLE `disaster_recovery_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disaster_recovery_tests`
--

DROP TABLE IF EXISTS `disaster_recovery_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disaster_recovery_tests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `test_name` varchar(255) NOT NULL,
  `disaster_type` varchar(100) NOT NULL,
  `test_status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `test_results` json DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_disaster_type` (`disaster_type`),
  KEY `idx_test_status` (`test_status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disaster_recovery_tests`
--

LOCK TABLES `disaster_recovery_tests` WRITE;
/*!40000 ALTER TABLE `disaster_recovery_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `disaster_recovery_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_templates`
--

DROP TABLE IF EXISTS `document_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `template_content` longtext NOT NULL,
  `template_type` enum('note','task','report','custom') NOT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_document_templates_user` (`user_id`),
  KEY `idx_document_templates_type` (`template_type`),
  KEY `idx_document_templates_public` (`is_public`),
  KEY `idx_document_templates_usage` (`usage_count`),
  CONSTRAINT `document_templates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_templates`
--

LOCK TABLES `document_templates` WRITE;
/*!40000 ALTER TABLE `document_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `document_type` enum('note','task') NOT NULL,
  `user_id` int NOT NULL,
  `version_number` int NOT NULL,
  `content` longtext NOT NULL,
  `change_summary` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_document_versions_document` (`document_id`,`document_type`),
  KEY `idx_document_versions_user` (`user_id`),
  KEY `idx_document_versions_created` (`created_at`),
  CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `editing_participants`
--

DROP TABLE IF EXISTS `editing_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `editing_participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `joined_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cursor_position` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session_user` (`session_id`,`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `editing_participants_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `editing_sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `editing_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `editing_participants`
--

LOCK TABLES `editing_participants` WRITE;
/*!40000 ALTER TABLE `editing_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `editing_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `editing_sessions`
--

DROP TABLE IF EXISTS `editing_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `editing_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `resource_type` enum('note','task') NOT NULL,
  `resource_id` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `editing_sessions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `editing_sessions`
--

LOCK TABLES `editing_sessions` WRITE;
/*!40000 ALTER TABLE `editing_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `editing_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
INSERT INTO `email_verifications` VALUES (1,1,'9526a85fbd00d5e2756c8d5992a9576332f55584f4decd2b2569df31adc9b778','2025-10-15 14:09:03',0,'2025-10-14 14:09:03');
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `export_history`
--

DROP TABLE IF EXISTS `export_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `export_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `export_type` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint NOT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `export_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `export_history`
--

LOCK TABLES `export_history` WRITE;
/*!40000 ALTER TABLE `export_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `export_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_usage_analytics`
--

DROP TABLE IF EXISTS `feature_usage_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_usage_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `feature` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `feature_usage_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_usage_analytics`
--

LOCK TABLES `feature_usage_analytics` WRITE;
/*!40000 ALTER TABLE `feature_usage_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `feature_usage_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_attachments`
--

DROP TABLE IF EXISTS `file_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `note_id` int DEFAULT NULL,
  `task_id` int DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_type` enum('image','document','audio','video','other') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_attachments_user` (`user_id`),
  KEY `idx_file_attachments_note` (`note_id`),
  KEY `idx_file_attachments_task` (`task_id`),
  KEY `idx_file_attachments_type` (`file_type`),
  KEY `idx_file_attachments_created` (`created_at`),
  CONSTRAINT `file_attachments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `file_attachments_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `file_attachments_ibfk_3` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_attachments`
--

LOCK TABLES `file_attachments` WRITE;
/*!40000 ALTER TABLE `file_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generated_content`
--

DROP TABLE IF EXISTS `generated_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generated_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `content_type` enum('note','task','meeting','project','study','creative') NOT NULL,
  `prompt` text NOT NULL,
  `generated_content` longtext NOT NULL,
  `metadata` json DEFAULT NULL,
  `model_used` varchar(100) DEFAULT NULL,
  `tokens_used` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_generated_content_user` (`user_id`),
  KEY `idx_generated_content_type` (`content_type`),
  KEY `idx_generated_content_created` (`created_at`),
  CONSTRAINT `generated_content_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generated_content`
--

LOCK TABLES `generated_content` WRITE;
/*!40000 ALTER TABLE `generated_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `generated_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `google_integrations`
--

DROP TABLE IF EXISTS `google_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `google_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text,
  `token_type` varchar(50) DEFAULT 'Bearer',
  `expires_at` datetime DEFAULT NULL,
  `scope` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_google` (`user_id`),
  KEY `idx_google_integrations_user` (`user_id`),
  CONSTRAINT `google_integrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `google_integrations`
--

LOCK TABLES `google_integrations` WRITE;
/*!40000 ALTER TABLE `google_integrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `google_integrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_history`
--

DROP TABLE IF EXISTS `import_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `format` varchar(50) NOT NULL,
  `status` enum('success','failed','partial') NOT NULL,
  `items_imported` int DEFAULT '0',
  `items_skipped` int DEFAULT '0',
  `items_failed` int DEFAULT '0',
  `error_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `import_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_history`
--

LOCK TABLES `import_history` WRITE;
/*!40000 ALTER TABLE `import_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `index_usage_stats`
--

DROP TABLE IF EXISTS `index_usage_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `index_usage_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `index_name` varchar(255) NOT NULL,
  `usage_count` int DEFAULT '0',
  `last_used` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_table_index` (`table_name`,`index_name`),
  KEY `idx_usage_count` (`usage_count`),
  KEY `idx_last_used` (`last_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `index_usage_stats`
--

LOCK TABLES `index_usage_stats` WRITE;
/*!40000 ALTER TABLE `index_usage_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `index_usage_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_activities`
--

DROP TABLE IF EXISTS `integration_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `integration_type` enum('google','microsoft','slack') NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_data` json DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `error_message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_integration_activities_user` (`user_id`),
  KEY `idx_integration_activities_type` (`integration_type`),
  KEY `idx_integration_activities_status` (`status`),
  CONSTRAINT `integration_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_activities`
--

LOCK TABLES `integration_activities` WRITE;
/*!40000 ALTER TABLE `integration_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_settings`
--

DROP TABLE IF EXISTS `integration_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `integration_type` enum('google','microsoft','slack') NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_integration_setting` (`user_id`,`integration_type`,`setting_key`),
  KEY `idx_integration_settings_user` (`user_id`),
  KEY `idx_integration_settings_type` (`integration_type`),
  CONSTRAINT `integration_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_settings`
--

LOCK TABLES `integration_settings` WRITE;
/*!40000 ALTER TABLE `integration_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_entries`
--

DROP TABLE IF EXISTS `log_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` enum('debug','info','warning','error','critical') NOT NULL,
  `message` text NOT NULL,
  `context` json DEFAULT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_channel` (`channel`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `log_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_entries`
--

LOCK TABLES `log_entries` WRITE;
/*!40000 ALTER TABLE `log_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microsoft_integrations`
--

DROP TABLE IF EXISTS `microsoft_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `microsoft_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text,
  `token_type` varchar(50) DEFAULT 'Bearer',
  `expires_at` datetime DEFAULT NULL,
  `scope` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_microsoft` (`user_id`),
  KEY `idx_microsoft_integrations_user` (`user_id`),
  CONSTRAINT `microsoft_integrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microsoft_integrations`
--

LOCK TABLES `microsoft_integrations` WRITE;
/*!40000 ALTER TABLE `microsoft_integrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `microsoft_integrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_history`
--

DROP TABLE IF EXISTS `migration_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `migration_file` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `execution_time_ms` int DEFAULT '0',
  `status` enum('success','failed') DEFAULT 'success',
  `error_message` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_file` (`migration_file`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_history`
--

LOCK TABLES `migration_history` WRITE;
/*!40000 ALTER TABLE `migration_history` DISABLE KEYS */;
INSERT INTO `migration_history` VALUES (1,'009_add_voice_notes_ocr_tables.sql','2025-10-24 13:03:13',0,'success',NULL),(2,'011_add_automation_tables.sql','2025-10-24 13:03:14',0,'success',NULL),(3,'014_add_analytics_tables.sql','2025-10-24 13:03:14',0,'success',NULL),(4,'015_add_data_management_tables.sql','2025-10-24 13:03:14',0,'success',NULL),(5,'016_add_database_optimization_tables.sql','2025-10-24 13:03:14',0,'success',NULL),(6,'017_add_backup_recovery_tables.sql','2025-10-24 13:03:14',0,'success',NULL),(7,'018_add_collaboration_tables.sql','2025-10-24 13:03:15',0,'success',NULL);
/*!40000 ALTER TABLE `migration_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_sessions`
--

DROP TABLE IF EXISTS `mobile_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mobile_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` enum('ios','android','web') COLLATE utf8mb4_unicode_ci NOT NULL,
  `app_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `push_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device` (`user_id`,`device_id`),
  KEY `user_id` (`user_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `mobile_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_sessions`
--

LOCK TABLES `mobile_sessions` WRITE;
/*!40000 ALTER TABLE `mobile_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monitoring_dashboards`
--

DROP TABLE IF EXISTS `monitoring_dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_dashboards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `layout` json NOT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `monitoring_dashboards_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitoring_dashboards`
--

LOCK TABLES `monitoring_dashboards` WRITE;
/*!40000 ALTER TABLE `monitoring_dashboards` DISABLE KEYS */;
INSERT INTO `monitoring_dashboards` VALUES (1,'System Overview','Default system monitoring dashboard','{\"widgets\": [{\"id\": \"response_time_chart\", \"type\": \"line_chart\", \"title\": \"Response Time\", \"config\": {\"metric\": \"response_time\", \"time_range\": \"1h\"}, \"position\": {\"x\": 0, \"y\": 0, \"width\": 6, \"height\": 4}}, {\"id\": \"error_rate_gauge\", \"type\": \"gauge\", \"title\": \"Error Rate\", \"config\": {\"metric\": \"error_rate\", \"max_value\": 100}, \"position\": {\"x\": 6, \"y\": 0, \"width\": 3, \"height\": 4}}, {\"id\": \"memory_usage_chart\", \"type\": \"area_chart\", \"title\": \"Memory Usage\", \"config\": {\"metric\": \"memory_usage\", \"time_range\": \"1h\"}, \"position\": {\"x\": 9, \"y\": 0, \"width\": 3, \"height\": 4}}, {\"id\": \"active_alerts\", \"type\": \"alert_list\", \"title\": \"Active Alerts\", \"config\": {\"limit\": 10, \"severity_filter\": [\"high\", \"critical\"]}, \"position\": {\"x\": 0, \"y\": 4, \"width\": 12, \"height\": 4}}]}',1,1,'2025-10-24 12:12:15','2025-10-24 12:12:15');
/*!40000 ALTER TABLE `monitoring_dashboards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motivational_quotes`
--

DROP TABLE IF EXISTS `motivational_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motivational_quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motivational_quotes`
--

LOCK TABLES `motivational_quotes` WRITE;
/*!40000 ALTER TABLE `motivational_quotes` DISABLE KEYS */;
INSERT INTO `motivational_quotes` VALUES (1,'The way to get started is to quit talking and begin doing.','Walt Disney','motivation',1,'2025-10-23 20:40:46'),(2,'Don\'t be pushed around by the fears in your mind. Be led by the dreams in your heart.','Roy T. Bennett','motivation',1,'2025-10-23 20:40:46'),(3,'Success is not final, failure is not fatal: it is the courage to continue that counts.','Winston Churchill','motivation',1,'2025-10-23 20:40:46'),(4,'The future belongs to those who believe in the beauty of their dreams.','Eleanor Roosevelt','motivation',1,'2025-10-23 20:40:46'),(5,'It is during our darkest moments that we must focus to see the light.','Aristotle','inspiration',1,'2025-10-23 20:40:46'),(6,'The only way to do great work is to love what you do.','Steve Jobs','career',1,'2025-10-23 20:40:46'),(7,'Innovation distinguishes between a leader and a follower.','Steve Jobs','innovation',1,'2025-10-23 20:40:46'),(8,'Life is what happens to you while you\'re busy making other plans.','John Lennon','life',1,'2025-10-23 20:40:46'),(9,'The way to get started is to quit talking and begin doing.','Walt Disney','action',1,'2025-10-23 20:40:46'),(10,'Don\'t let yesterday take up too much of today.','Will Rogers','time',1,'2025-10-23 20:40:46');
/*!40000 ALTER TABLE `motivational_quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_attachments`
--

DROP TABLE IF EXISTS `note_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `note_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_encrypted` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_note_id` (`note_id`),
  KEY `idx_file_hash` (`file_hash`),
  CONSTRAINT `note_attachments_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_attachments`
--

LOCK TABLES `note_attachments` WRITE;
/*!40000 ALTER TABLE `note_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `note_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_tags`
--

DROP TABLE IF EXISTS `note_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `note_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_tag` (`note_id`,`tag_id`),
  KEY `idx_note_id` (`note_id`),
  KEY `idx_tag_id` (`tag_id`),
  KEY `idx_note_tags_note` (`note_id`),
  KEY `idx_note_tags_tag` (`tag_id`),
  CONSTRAINT `note_tags_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_tags`
--

LOCK TABLES `note_tags` WRITE;
/*!40000 ALTER TABLE `note_tags` DISABLE KEYS */;
INSERT INTO `note_tags` VALUES (3,2,3,'2025-10-14 15:02:25'),(4,2,2,'2025-10-14 15:02:25'),(5,3,4,'2025-10-14 15:02:25'),(6,3,2,'2025-10-14 15:02:25'),(7,4,6,'2025-10-14 15:02:25'),(8,4,2,'2025-10-14 15:02:25'),(9,5,5,'2025-10-14 15:02:25'),(10,5,1,'2025-10-14 15:02:25'),(11,5,10,'2025-10-14 15:02:25'),(12,6,8,'2025-10-14 15:02:25'),(13,6,2,'2025-10-14 15:02:25'),(14,7,9,'2025-10-14 15:02:25'),(15,7,2,'2025-10-14 15:02:25'),(16,8,7,'2025-10-14 15:02:25'),(17,8,2,'2025-10-14 15:02:25'),(18,9,3,'2025-10-14 15:02:25'),(19,9,2,'2025-10-14 15:02:25'),(20,10,5,'2025-10-14 15:02:25'),(21,10,2,'2025-10-14 15:02:25');
/*!40000 ALTER TABLE `note_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_versions`
--

DROP TABLE IF EXISTS `note_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `note_versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `version_number` int NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `change_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_note_id` (`note_id`),
  KEY `idx_version_number` (`version_number`),
  CONSTRAINT `note_versions_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_versions`
--

LOCK TABLES `note_versions` WRITE;
/*!40000 ALTER TABLE `note_versions` DISABLE KEYS */;
INSERT INTO `note_versions` VALUES (4,3,1,'Investment Portfolio Review','Basic portfolio overview','Initial creation','2025-10-14 15:02:25'),(5,3,2,'Investment Portfolio Review','Added performance metrics and analysis','Enhanced with performance data','2025-10-14 15:02:25'),(6,5,1,'Learning Path - Machine Learning','Basic learning outline','Initial creation','2025-10-14 15:02:25'),(7,5,2,'Learning Path - Machine Learning','Added detailed weekly breakdown','Enhanced with detailed schedule','2025-10-14 15:02:25'),(8,5,3,'Learning Path - Machine Learning','Added project goals and resources','Added project planning','2025-10-14 15:02:25');
/*!40000 ALTER TABLE `note_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `category` enum('general','work','personal','study','ideas','meetings','projects','research','journal','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_archived` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `word_count` int DEFAULT '0',
  `read_time` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_pinned` (`is_pinned`),
  KEY `idx_is_archived` (`is_archived`),
  KEY `idx_priority` (`priority`),
  KEY `idx_category` (`category`),
  KEY `idx_notes_user_created` (`user_id`,`created_at`),
  KEY `idx_notes_user_updated` (`user_id`,`updated_at`),
  KEY `idx_notes_user_status` (`user_id`,`is_archived`,`is_pinned`),
  KEY `idx_notes_priority` (`user_id`,`priority`),
  KEY `idx_notes_color` (`user_id`,`color`),
  FULLTEXT KEY `ft_title_content` (`title`,`content`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (2,1,'Project Planning Meeting Notes','Attended the quarterly project planning meeting today. Key points discussed:\n\n1. Q4 Objectives:\n   - Launch new mobile app by December\n   - Increase user engagement by 25%\n   - Implement new security features\n\n2. Resource Allocation:\n   - Frontend team: 3 developers\n   - Backend team: 2 developers\n   - QA team: 1 tester\n\n3. Timeline:\n   - Week 1-2: Design and prototyping\n   - Week 3-6: Development phase\n   - Week 7-8: Testing and bug fixes\n   - Week 9-10: Deployment and launch\n\nNext meeting scheduled for next Friday at 2 PM.','Quarterly project planning meeting covering Q4 objectives, resource allocation, and timeline for mobile app launch.','#e0f2fe','high','general',1,1,0,NULL,156,1,'2025-10-14 15:02:25','2025-10-15 12:38:23'),(3,1,'Healthy Recipe Collection','## Mediterranean Quinoa Bowl\n\n**Ingredients:**\n- 1 cup quinoa\n- 1 cucumber, diced\n- 1 cup cherry tomatoes\n- 1/2 red onion, sliced\n- 1/2 cup kalamata olives\n- 1/4 cup feta cheese\n- 2 tbsp olive oil\n- 1 tbsp lemon juice\n- Salt and pepper to taste\n\n**Instructions:**\n1. Cook quinoa according to package directions\n2. Let cool completely\n3. Mix all vegetables in a large bowl\n4. Add cooled quinoa\n5. Drizzle with olive oil and lemon juice\n6. Season with salt and pepper\n7. Top with feta cheese\n\n**Nutritional Info:**\n- Calories: 320 per serving\n- Protein: 12g\n- Fiber: 6g','Collection of healthy Mediterranean recipes with detailed ingredients and nutritional information.','#f0fdf4','medium','general',0,0,0,NULL,198,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(4,1,'Investment Portfolio Review','## Portfolio Performance - October 2025\n\n**Current Holdings:**\n- Tech Stocks (40%): AAPL, GOOGL, MSFT, TSLA\n- Index Funds (30%): VTI, VXUS\n- Bonds (20%): BND, TLT\n- Crypto (10%): BTC, ETH\n\n**Performance Summary:**\n- YTD Return: +12.5%\n- Monthly Return: +2.3%\n- Best Performer: TSLA (+8.2%)\n- Worst Performer: BND (-1.1%)\n\n**Action Items:**\n1. Rebalance portfolio to maintain 40/30/20/10 allocation\n2. Consider adding REITs for diversification\n3. Review bond allocation given current interest rates\n4. Set up automatic monthly contributions\n\n**Next Review:** November 15, 2025','Monthly investment portfolio review with performance analysis and rebalancing recommendations.','#fef3c7','high','general',0,0,0,NULL,167,2,'2025-10-14 15:02:25','2025-10-15 12:14:37'),(5,1,'Travel Itinerary - Japan 2025','## 10-Day Japan Adventure\n\n**Day 1-3: Tokyo**\n- Arrival at Narita Airport\n- Hotel: Park Hyatt Tokyo\n- Activities: Senso-ji Temple, Tsukiji Fish Market, Shibuya Crossing\n- Restaurants: Sushi Dai, Ramen Nagi\n\n**Day 4-6: Kyoto**\n- Bullet train to Kyoto\n- Hotel: The Ritz-Carlton Kyoto\n- Activities: Fushimi Inari Shrine, Arashiyama Bamboo Grove, Kinkaku-ji\n- Restaurants: Kikunoi, Giro Giro Hitoshina\n\n**Day 7-9: Osaka**\n- Train to Osaka\n- Hotel: Conrad Osaka\n- Activities: Osaka Castle, Dotonbori, Universal Studios\n- Restaurants: Kani Doraku, Okonomiyaki Chitose\n\n**Day 10: Return**\n- Flight from Kansai Airport\n- Souvenirs: Matcha tea, traditional crafts\n\n**Budget:** $3,500 per person\n**Travel Insurance:** Covered\n**Visa:** Not required for US citizens','Detailed 10-day Japan travel itinerary covering Tokyo, Kyoto, and Osaka with accommodations, activities, and dining recommendations.','#fce7f3','medium','general',0,0,0,NULL,189,3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(6,1,'Learning Path - Machine Learning','## ML Learning Journey 2025\n\n**Phase 1: Foundations (Weeks 1-4)**\n- Mathematics: Linear Algebra, Calculus, Statistics\n- Programming: Python, NumPy, Pandas\n- Resources: Khan Academy, Coursera ML Course\n\n**Phase 2: Core Concepts (Weeks 5-8)**\n- Supervised Learning: Regression, Classification\n- Unsupervised Learning: Clustering, Dimensionality Reduction\n- Model Evaluation: Cross-validation, Metrics\n- Resources: Scikit-learn documentation, Andrew Ng course\n\n**Phase 3: Deep Learning (Weeks 9-12)**\n- Neural Networks: Perceptrons, Backpropagation\n- Frameworks: TensorFlow, PyTorch\n- Applications: Computer Vision, NLP\n- Resources: Deep Learning Specialization, Fast.ai\n\n**Phase 4: Projects (Weeks 13-16)**\n- Project 1: Image Classification\n- Project 2: Sentiment Analysis\n- Project 3: Recommendation System\n- Portfolio: GitHub repository\n\n**Goals:**\n- Complete 4 projects by end of year\n- Contribute to open source ML projects\n- Attend 2 ML conferences','Comprehensive 16-week machine learning learning path with phases, resources, and project goals.','#ede9fe','high','general',0,0,0,NULL,201,3,'2025-10-14 15:02:25','2025-10-15 12:38:55'),(7,1,'Home Renovation Checklist','## Kitchen Renovation Project\n\n**Phase 1: Planning & Design**\n- [x] Measure kitchen dimensions\n- [x] Create 3D design mockup\n- [x] Get contractor quotes (3 received)\n- [x] Choose materials and finishes\n- [ ] Finalize budget ($25,000)\n- [ ] Obtain permits\n\n**Phase 2: Demolition**\n- [ ] Remove old cabinets\n- [ ] Remove old countertops\n- [ ] Remove old appliances\n- [ ] Dispose of debris\n- [ ] Clean and prep space\n\n**Phase 3: Installation**\n- [ ] Install new electrical outlets\n- [ ] Install new plumbing\n- [ ] Install new cabinets\n- [ ] Install new countertops\n- [ ] Install new appliances\n- [ ] Install new flooring\n\n**Phase 4: Finishing**\n- [ ] Paint walls\n- [ ] Install backsplash\n- [ ] Install lighting fixtures\n- [ ] Final cleaning\n- [ ] Move back in\n\n**Timeline:** 6-8 weeks\n**Contractor:** ABC Construction\n**Materials:** Home Depot, Lowes','Detailed kitchen renovation checklist with phases, tasks, timeline, and contractor information.','#f0f9ff','medium','general',0,0,0,NULL,178,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(8,1,'Family Reunion Planning','## Annual Family Reunion 2025\n\n**Event Details:**\n- Date: July 15, 2025\n- Time: 10:00 AM - 6:00 PM\n- Location: Central Park, Pavilion #3\n- Expected Guests: 45-50 people\n\n**Planning Committee:**\n- Coordinator: Mom (Sarah)\n- Food: Aunt Mary, Cousin Lisa\n- Activities: Uncle John, Sister Emma\n- Decorations: Grandma Rose\n\n**Menu Planning:**\n- Main Course: BBQ (burgers, hot dogs, chicken)\n- Sides: Potato salad, coleslaw, baked beans\n- Desserts: Apple pie, chocolate cake, ice cream\n- Beverages: Lemonade, iced tea, water, soda\n- Special: Vegetarian options for 5 guests\n\n**Activities:**\n- 10:00 AM: Welcome and introductions\n- 11:00 AM: Family photo session\n- 12:00 PM: Lunch and socializing\n- 2:00 PM: Games and activities\n- 4:00 PM: Family talent show\n- 5:00 PM: Cake and closing\n\n**Budget:** $800\n**RSVP Deadline:** June 30, 2025','Comprehensive family reunion planning with event details, committee assignments, menu, activities, and budget.','#fef2f2','medium','general',0,0,0,NULL,192,3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(9,1,'Shopping List - Holiday Season','## Holiday Shopping 2025\n\n**Gift List:**\n- Mom: Spa gift certificate ($100)\n- Dad: New golf clubs ($300)\n- Sister: Designer handbag ($250)\n- Brother: Gaming console ($400)\n- Grandma: Photo album with family pics ($50)\n- Grandpa: Fishing gear ($150)\n- Niece: Art supplies set ($75)\n- Nephew: LEGO set ($80)\n- Best Friend: Wine tasting experience ($120)\n- Colleague: Gift card ($25)\n\n**Home Decorations:**\n- Christmas tree (7ft artificial)\n- String lights (LED, warm white)\n- Ornaments (glass and plastic mix)\n- Wreath for front door\n- Stockings (6 pieces)\n- Tree skirt\n- Candles and candle holders\n\n**Food & Beverages:**\n- Turkey (12-14 lbs)\n- Ham (8 lbs)\n- Stuffing mix\n- Cranberry sauce\n- Green beans\n- Sweet potatoes\n- Dinner rolls\n- Wine (red and white)\n- Eggnog\n- Hot chocolate mix\n\n**Total Budget:** $2,000\n**Shopping Strategy:**\n- Week 1: Online orders (gifts)\n- Week 2: Local stores (decorations)\n- Week 3: Grocery stores (food)\n- Week 4: Last-minute items','Comprehensive holiday shopping list with gifts, decorations, food, and strategic shopping plan.','#f0fdf4','low','general',0,0,0,NULL,185,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(10,1,'Health & Fitness Goals 2025','## Annual Health & Fitness Plan\n\n**Current Stats:**\n- Weight: 180 lbs\n- Body Fat: 18%\n- BMI: 24.2\n- Resting Heart Rate: 65 bpm\n\n**Goals for 2025:**\n- Weight: 170 lbs (lose 10 lbs)\n- Body Fat: 15%\n- Run 5K in under 25 minutes\n- Complete 100 push-ups in one set\n- Deadlift 225 lbs\n- Meditate daily for 10 minutes\n\n**Workout Schedule:**\n- Monday: Upper body strength training\n- Tuesday: Cardio (running/cycling)\n- Wednesday: Lower body strength training\n- Thursday: Yoga/Pilates\n- Friday: Full body HIIT\n- Saturday: Outdoor activities (hiking/biking)\n- Sunday: Rest and recovery\n\n**Nutrition Plan:**\n- Calorie target: 2,200 per day\n- Protein: 150g daily\n- Carbs: 200g daily\n- Fats: 80g daily\n- Water: 3 liters daily\n- Supplements: Multivitamin, Omega-3, Protein powder\n\n**Progress Tracking:**\n- Weekly weigh-ins\n- Monthly body measurements\n- Fitness app logging\n- Photo progress (monthly)\n\n**Milestones:**\n- Q1: Lose 3 lbs, establish routine\n- Q2: Lose 3 lbs, improve running time\n- Q3: Lose 2 lbs, increase strength\n- Q4: Lose 2 lbs, achieve all goals','Comprehensive health and fitness plan with current stats, goals, workout schedule, nutrition plan, and progress tracking.','#fef2f2','high','general',1,1,0,NULL,203,3,'2025-10-14 15:02:25','2025-10-15 12:39:28'),(11,1,'Book Reading List 2025','## 2025 Reading Challenge\n\n**Goal:** Read 24 books (2 per month)\n\n**Completed (6 books):**\n1. \"Atomic Habits\" by James Clear ⭐⭐⭐⭐⭐\n2. \"The Lean Startup\" by Eric Ries ⭐⭐⭐⭐\n3. \"Sapiens\" by Yuval Noah Harari ⭐⭐⭐⭐⭐\n4. \"Thinking, Fast and Slow\" by Daniel Kahneman ⭐⭐⭐⭐\n5. \"The Psychology of Money\" by Morgan Housel ⭐⭐⭐⭐⭐\n6. \"Deep Work\" by Cal Newport ⭐⭐⭐⭐\n\n**Currently Reading:**\n7. \"The 7 Habits of Highly Effective People\" by Stephen Covey\n8. \"Educated\" by Tara Westover\n\n**To Read (16 books):**\n9. \"The Power of Now\" by Eckhart Tolle\n10. \"Man\'s Search for Meaning\" by Viktor Frankl\n11. \"The Alchemist\" by Paulo Coelho\n12. \"1984\" by George Orwell\n13. \"To Kill a Mockingbird\" by Harper Lee\n14. \"The Great Gatsby\" by F. Scott Fitzgerald\n15. \"Pride and Prejudice\" by Jane Austen\n16. \"The Catcher in the Rye\" by J.D. Salinger\n17. \"The Lord of the Rings\" by J.R.R. Tolkien\n18. \"Dune\" by Frank Herbert\n19. \"The Handmaid\'s Tale\" by Margaret Atwood\n20. \"The Kite Runner\" by Khaled Hosseini\n21. \"The Book Thief\" by Markus Zusak\n22. \"The Help\" by Kathryn Stockett\n23. \"The Fault in Our Stars\" by John Green\n24. \"The Martian\" by Andy Weir\n\n**Reading Schedule:**\n- Weekdays: 30 minutes before bed\n- Weekends: 1 hour in the morning\n- Commute: Audiobooks (2 hours daily)\n\n**Genres:**\n- Self-help: 6 books\n- Fiction: 8 books\n- Business: 4 books\n- History: 3 books\n- Science: 3 books','Comprehensive 2025 reading challenge with 24 books, progress tracking, and reading schedule across multiple genres.','#fef3c7','medium','general',0,0,1,'2025-10-14 21:19:03',196,3,'2025-10-14 15:02:25','2025-10-14 21:19:03');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `access_token` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `scope` text,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `access_token` (`access_token`),
  KEY `idx_oauth_access_tokens_token` (`access_token`),
  KEY `idx_oauth_access_tokens_user` (`user_id`),
  CONSTRAINT `oauth_access_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_tokens`
--

LOCK TABLES `oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_authorization_codes`
--

DROP TABLE IF EXISTS `oauth_authorization_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_authorization_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `scope` text,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_oauth_authorization_codes_code` (`code`),
  KEY `idx_oauth_authorization_codes_user` (`user_id`),
  CONSTRAINT `oauth_authorization_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_authorization_codes`
--

LOCK TABLES `oauth_authorization_codes` WRITE;
/*!40000 ALTER TABLE `oauth_authorization_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_authorization_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `refresh_token` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `scope` text,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refresh_token` (`refresh_token`),
  KEY `access_token` (`access_token`),
  KEY `idx_oauth_refresh_tokens_token` (`refresh_token`),
  KEY `idx_oauth_refresh_tokens_user` (`user_id`),
  CONSTRAINT `oauth_refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `oauth_refresh_tokens_ibfk_2` FOREIGN KEY (`access_token`) REFERENCES `oauth_access_tokens` (`access_token`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_tokens`
--

LOCK TABLES `oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_tokens`
--

DROP TABLE IF EXISTS `oauth_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider` varchar(50) NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_provider` (`user_id`,`provider`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `oauth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_tokens`
--

LOCK TABLES `oauth_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ocr_results`
--

DROP TABLE IF EXISTS `ocr_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ocr_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `extracted_text` longtext,
  `confidence` decimal(3,2) DEFAULT NULL,
  `linked_note_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `linked_note_id` (`linked_note_id`),
  KEY `idx_ocr_results_user` (`user_id`),
  KEY `idx_ocr_results_created` (`created_at`),
  KEY `idx_ocr_results_confidence` (`confidence`),
  CONSTRAINT `ocr_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ocr_results_ibfk_2` FOREIGN KEY (`linked_note_id`) REFERENCES `notes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ocr_results`
--

LOCK TABLES `ocr_results` WRITE;
/*!40000 ALTER TABLE `ocr_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `ocr_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `optimization_recommendations`
--

DROP TABLE IF EXISTS `optimization_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `optimization_recommendations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recommendation_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','dismissed') DEFAULT 'pending',
  `estimated_impact` varchar(100) DEFAULT NULL,
  `sql_command` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`recommendation_type`),
  KEY `idx_priority` (`priority`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `optimization_recommendations`
--

LOCK TABLES `optimization_recommendations` WRITE;
/*!40000 ALTER TABLE `optimization_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `optimization_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_performance_metrics`
--

DROP TABLE IF EXISTS `page_performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_performance_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  `load_time_ms` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `page_performance_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_performance_metrics`
--

LOCK TABLES `page_performance_metrics` WRITE;
/*!40000 ALTER TABLE `page_performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `performance_alerts`
--

DROP TABLE IF EXISTS `performance_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(100) NOT NULL,
  `alert_name` varchar(255) NOT NULL,
  `description` text,
  `threshold_value` decimal(10,3) DEFAULT NULL,
  `current_value` decimal(10,3) DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `status` enum('active','acknowledged','resolved','suppressed') DEFAULT 'active',
  `triggered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledged_by` int DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `acknowledged_by` (`acknowledged_by`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`),
  KEY `idx_triggered_at` (`triggered_at`),
  CONSTRAINT `performance_alerts_ibfk_1` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `performance_alerts_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `performance_alerts`
--

LOCK TABLES `performance_alerts` WRITE;
/*!40000 ALTER TABLE `performance_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `performance_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `performance_metrics`
--

DROP TABLE IF EXISTS `performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `response_time_ms` int NOT NULL,
  `status_code` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `performance_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `performance_metrics`
--

LOCK TABLES `performance_metrics` WRITE;
/*!40000 ALTER TABLE `performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plugin_type` enum('core','extension','theme','integration') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `config` json DEFAULT NULL,
  `dependencies` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_version` (`name`,`version`),
  KEY `plugin_type` (`plugin_type`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_acknowledgments`
--

DROP TABLE IF EXISTS `policy_acknowledgments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `policy_acknowledgments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `policy_id` int NOT NULL,
  `user_id` int NOT NULL,
  `acknowledged_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_policy_user` (`policy_id`,`user_id`),
  KEY `idx_policy_id` (`policy_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_acknowledged_at` (`acknowledged_at`),
  CONSTRAINT `policy_acknowledgments_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `compliance_policies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `policy_acknowledgments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_acknowledgments`
--

LOCK TABLES `policy_acknowledgments` WRITE;
/*!40000 ALTER TABLE `policy_acknowledgments` DISABLE KEYS */;
/*!40000 ALTER TABLE `policy_acknowledgments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `query_performance_metrics`
--

DROP TABLE IF EXISTS `query_performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `query_performance_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query_hash` varchar(64) NOT NULL,
  `query_text` text NOT NULL,
  `execution_time_ms` decimal(10,3) NOT NULL,
  `rows_examined` int DEFAULT '0',
  `rows_sent` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_query_hash` (`query_hash`),
  KEY `idx_execution_time` (`execution_time_ms`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `query_performance_metrics`
--

LOCK TABLES `query_performance_metrics` WRITE;
/*!40000 ALTER TABLE `query_performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `query_performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `endpoint` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_count` int DEFAULT '1',
  `window_start` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_endpoint` (`ip_address`,`endpoint`),
  KEY `idx_window_start` (`window_start`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
INSERT INTO `rate_limits` VALUES (1,'127.0.0.1','/',2,'2025-10-14 22:15:40','2025-10-14 10:15:25'),(2,'127.0.0.1','/.well-known/appspecific/com.chrome.devtools.json',43,'2025-10-14 20:31:53','2025-10-14 10:15:25'),(3,'127.0.0.1','/login',29,'2025-10-14 17:21:51','2025-10-14 14:00:21'),(4,'127.0.0.1','/register',7,'2025-10-14 14:03:05','2025-10-14 14:03:05'),(5,'127.0.0.1','register',3,'2025-10-14 14:07:53','2025-10-14 14:07:53'),(6,'127.0.0.1','login',2,'2025-10-14 17:49:41','2025-10-14 14:09:11'),(7,'127.0.0.1','/dashboard',5,'2025-10-14 21:19:22','2025-10-14 14:09:12'),(8,'127.0.0.1','/dashboard/api/activity',3,'2025-10-14 21:19:23','2025-10-14 14:09:14'),(9,'127.0.0.1','/dashboard/api/todays-focus',3,'2025-10-14 21:19:23','2025-10-14 14:09:14'),(10,'127.0.0.1','/dashboard/api/recent-notes',3,'2025-10-14 21:19:23','2025-10-14 14:09:14'),(11,'127.0.0.1','/dashboard/api/upcoming-tasks',3,'2025-10-14 21:19:23','2025-10-14 14:09:14'),(12,'127.0.0.1','/notes',7,'2025-10-14 20:23:34','2025-10-14 14:12:47'),(13,'127.0.0.1','/notes/store',1,'2025-10-14 14:28:38','2025-10-14 14:28:38'),(14,'127.0.0.1','/tasks',2,'2025-10-14 23:07:26','2025-10-14 14:29:06'),(15,'127.0.0.1','/tasks/api/get-kanban',1,'2025-10-14 23:07:27','2025-10-14 14:31:25'),(16,'127.0.0.1','/tasks/api/calendar',1,'2025-10-14 23:07:27','2025-10-14 14:31:25'),(17,'127.0.0.1','/tasks/store',1,'2025-10-14 14:37:34','2025-10-14 14:37:34'),(18,'127.0.0.1','/tags',8,'2025-10-14 20:55:20','2025-10-14 14:59:22'),(19,'127.0.0.1','/tags/api/get-all',6,'2025-10-14 20:55:25','2025-10-14 14:59:26'),(20,'127.0.0.1','/archived',6,'2025-10-14 20:56:13','2025-10-14 14:59:45'),(21,'127.0.0.1','/trash',7,'2025-10-14 20:56:29','2025-10-14 14:59:52'),(22,'127.0.0.1','/settings',3,'2025-10-14 22:15:40','2025-10-14 15:00:04'),(23,'192.168.1.100','/api/notes',45,'2025-10-14 14:00:00','2025-10-14 15:02:25'),(24,'192.168.1.100','/api/tasks',32,'2025-10-14 14:00:00','2025-10-14 15:02:25'),(25,'192.168.1.100','/api/tags',8,'2025-10-14 14:00:00','2025-10-14 15:02:25'),(26,'192.168.1.101','/api/notes',12,'2025-10-14 14:00:00','2025-10-14 15:02:25'),(27,'192.168.1.102','/api/tasks',28,'2025-10-14 14:00:00','2025-10-14 15:02:25'),(28,'127.0.0.1','/notifications',1,'2025-10-14 15:48:47','2025-10-14 15:48:47'),(29,'127.0.0.1','/backup',21,'2025-10-14 20:58:45','2025-10-14 15:59:03'),(30,'127.0.0.1','/backup/status',30,'2025-10-14 20:58:48','2025-10-14 15:59:06'),(31,'127.0.0.1','/backup/history',29,'2025-10-14 20:58:48','2025-10-14 15:59:06'),(32,'127.0.0.1','/audit-logs',15,'2025-10-14 21:48:55','2025-10-14 15:59:20'),(33,'127.0.0.1','/offline',4,'2025-10-14 22:15:39','2025-10-14 15:59:30'),(34,'127.0.0.1','/sw.js',2,'2025-10-14 15:59:32','2025-10-14 15:59:32'),(35,'127.0.0.1','/security',4,'2025-10-14 22:22:59','2025-10-14 16:00:48'),(36,'127.0.0.1','/notes/archive',5,'2025-10-14 16:21:34','2025-10-14 16:21:34'),(37,'127.0.0.1','/logout',1,'2025-10-14 17:21:51','2025-10-14 17:21:51'),(38,'127.0.0.1','/notes/delete',2,'2025-10-14 19:19:59','2025-10-14 19:19:59'),(39,'127.0.0.1','/notes/export',2,'2025-10-14 19:20:33','2025-10-14 19:20:33'),(40,'127.0.0.1','/tasks/export',4,'2025-10-14 20:31:26','2025-10-14 20:31:26'),(41,'127.0.0.1','/backup/create',1,'2025-10-14 20:58:51','2025-10-14 20:58:51'),(42,'127.0.0.1','/backup/settings',2,'2025-10-14 20:59:33','2025-10-14 20:59:33'),(43,'127.0.0.1','/tasks/delete',2,'2025-10-14 21:20:49','2025-10-14 21:20:49'),(44,'127.0.0.1','/backup/test',1,'2025-10-14 21:47:02','2025-10-14 21:47:02'),(45,'127.0.0.1','/css/app.css',2,'2025-10-14 22:15:41','2025-10-14 22:15:41'),(46,'127.0.0.1','/js/app.js',2,'2025-10-14 22:15:41','2025-10-14 22:15:41'),(47,'127.0.0.1','/search',1,'2025-10-14 22:23:28','2025-10-14 22:23:28'),(48,'127.0.0.1','/offline/download',1,'2025-10-14 23:08:03','2025-10-14 23:08:03');
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recovery_actions`
--

DROP TABLE IF EXISTS `recovery_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recovery_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recovery_point_id` int DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_name` varchar(255) NOT NULL,
  `status` enum('pending','in_progress','completed','failed') DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `error_message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recovery_point_id` (`recovery_point_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `recovery_actions_ibfk_1` FOREIGN KEY (`recovery_point_id`) REFERENCES `recovery_points` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recovery_actions`
--

LOCK TABLES `recovery_actions` WRITE;
/*!40000 ALTER TABLE `recovery_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `recovery_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recovery_points`
--

DROP TABLE IF EXISTS `recovery_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recovery_points` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `backup_id` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recovery_points`
--

LOCK TABLES `recovery_points` WRITE;
/*!40000 ALTER TABLE `recovery_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `recovery_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `replication_status`
--

DROP TABLE IF EXISTS `replication_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `replication_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `server_type` enum('master','slave') NOT NULL,
  `server_host` varchar(255) NOT NULL,
  `server_port` int NOT NULL,
  `status` enum('active','inactive','error') NOT NULL,
  `seconds_behind_master` int DEFAULT NULL,
  `io_running` enum('Yes','No') DEFAULT NULL,
  `sql_running` enum('Yes','No') DEFAULT NULL,
  `last_error` text,
  `details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_type` (`server_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `replication_status`
--

LOCK TABLES `replication_status` WRITE;
/*!40000 ALTER TABLE `replication_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `replication_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_assessments`
--

DROP TABLE IF EXISTS `risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_assessments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assessment_name` varchar(255) NOT NULL,
  `assessment_type` enum('security','privacy','compliance','operational') NOT NULL,
  `risk_level` enum('low','medium','high','critical') NOT NULL,
  `description` text NOT NULL,
  `impact_description` text,
  `likelihood` enum('very_low','low','medium','high','very_high') NOT NULL,
  `impact` enum('very_low','low','medium','high','very_high') NOT NULL,
  `mitigation_measures` text,
  `residual_risk` enum('low','medium','high','critical') NOT NULL,
  `assessed_by` int NOT NULL,
  `assessment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `review_date` datetime DEFAULT NULL,
  `status` enum('open','mitigated','accepted','closed') DEFAULT 'open',
  PRIMARY KEY (`id`),
  KEY `idx_assessment_name` (`assessment_name`),
  KEY `idx_assessment_type` (`assessment_type`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_assessed_by` (`assessed_by`),
  KEY `idx_assessment_date` (`assessment_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `risk_assessments_ibfk_1` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_assessments`
--

LOCK TABLES `risk_assessments` WRITE;
/*!40000 ALTER TABLE `risk_assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `permissions` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','System Administrator','[\"all\"]','2025-10-24 12:05:05','2025-10-24 12:05:05'),(2,'user','Regular User','[\"read_own\", \"write_own\"]','2025-10-24 12:05:05','2025-10-24 12:05:05'),(3,'moderator','Content Moderator','[\"read_all\", \"write_own\", \"moderate_content\"]','2025-10-24 12:05:05','2025-10-24 12:05:05'),(4,'auditor','Compliance Auditor','[\"read_all\", \"audit_logs\"]','2025-10-24 12:05:05','2025-10-24 12:05:05');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduled_tasks`
--

DROP TABLE IF EXISTS `scheduled_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduled_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `task_type` varchar(100) NOT NULL,
  `task_data` json NOT NULL,
  `schedule_type` enum('once','daily','weekly','monthly','interval') NOT NULL,
  `schedule_data` json NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `next_execution` datetime DEFAULT NULL,
  `last_execution` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `scheduled_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_tasks`
--

LOCK TABLES `scheduled_tasks` WRITE;
/*!40000 ALTER TABLE `scheduled_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduled_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_incidents`
--

DROP TABLE IF EXISTS `security_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `incident_type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `description` text NOT NULL,
  `affected_user_id` int DEFAULT NULL,
  `affected_resource` varchar(100) DEFAULT NULL,
  `status` enum('open','investigating','resolved','closed') DEFAULT 'open',
  `assigned_to` int DEFAULT NULL,
  `resolution_notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_incident_type` (`incident_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`),
  KEY `idx_affected_user_id` (`affected_user_id`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `security_incidents_ibfk_1` FOREIGN KEY (`affected_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_incidents_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_incidents`
--

LOCK TABLES `security_incidents` WRITE;
/*!40000 ALTER TABLE `security_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shared_link_access_logs`
--

DROP TABLE IF EXISTS `shared_link_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shared_link_access_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shared_link_id` int NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `accessed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_shared_link_id` (`shared_link_id`),
  KEY `idx_accessed_at` (`accessed_at`),
  CONSTRAINT `shared_link_access_logs_ibfk_1` FOREIGN KEY (`shared_link_id`) REFERENCES `shared_links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shared_link_access_logs`
--

LOCK TABLES `shared_link_access_logs` WRITE;
/*!40000 ALTER TABLE `shared_link_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `shared_link_access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shared_links`
--

DROP TABLE IF EXISTS `shared_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shared_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resource_type` enum('note','task','team') NOT NULL,
  `resource_id` int NOT NULL,
  `created_by` int NOT NULL,
  `share_token` varchar(255) NOT NULL,
  `permission` enum('read','write','admin') DEFAULT 'read',
  `expires_at` datetime DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `access_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_accessed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `share_token` (`share_token`),
  KEY `idx_share_token` (`share_token`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `shared_links_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shared_links`
--

LOCK TABLES `shared_links` WRITE;
/*!40000 ALTER TABLE `shared_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `shared_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slack_integrations`
--

DROP TABLE IF EXISTS `slack_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slack_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `access_token` text NOT NULL,
  `team_id` varchar(255) DEFAULT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `authed_user_id` varchar(255) DEFAULT NULL,
  `scope` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_slack` (`user_id`),
  KEY `idx_slack_integrations_user` (`user_id`),
  CONSTRAINT `slack_integrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slack_integrations`
--

LOCK TABLES `slack_integrations` WRITE;
/*!40000 ALTER TABLE `slack_integrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `slack_integrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slow_query_logs`
--

DROP TABLE IF EXISTS `slow_query_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slow_query_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query_text` text NOT NULL,
  `execution_time_ms` decimal(10,3) NOT NULL,
  `rows_examined` int DEFAULT '0',
  `rows_sent` int DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_execution_time` (`execution_time_ms`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slow_query_logs`
--

LOCK TABLES `slow_query_logs` WRITE;
/*!40000 ALTER TABLE `slow_query_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `slow_query_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smart_suggestions`
--

DROP TABLE IF EXISTS `smart_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smart_suggestions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `suggestion_type` enum('tag','category','priority','related_content','improvement','template') NOT NULL,
  `target_id` int DEFAULT NULL,
  `target_type` enum('note','task','user') NOT NULL,
  `suggestion_data` json NOT NULL,
  `confidence` decimal(3,2) NOT NULL,
  `is_accepted` tinyint(1) DEFAULT NULL,
  `is_dismissed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_smart_suggestions_user` (`user_id`),
  KEY `idx_smart_suggestions_type` (`suggestion_type`),
  KEY `idx_smart_suggestions_target` (`target_id`,`target_type`),
  KEY `idx_smart_suggestions_confidence` (`confidence`),
  KEY `idx_smart_suggestions_status` (`is_accepted`,`is_dismissed`),
  CONSTRAINT `smart_suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smart_suggestions`
--

LOCK TABLES `smart_suggestions` WRITE;
/*!40000 ALTER TABLE `smart_suggestions` DISABLE KEYS */;
/*!40000 ALTER TABLE `smart_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sso_sessions`
--

DROP TABLE IF EXISTS `sso_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sso_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `provider` enum('saml','oauth','ldap') NOT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `sso_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sso_sessions`
--

LOCK TABLES `sso_sessions` WRITE;
/*!40000 ALTER TABLE `sso_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sso_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtasks`
--

DROP TABLE IF EXISTS `subtasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_subtasks_task` (`task_id`),
  CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtasks`
--

LOCK TABLES `subtasks` WRITE;
/*!40000 ALTER TABLE `subtasks` DISABLE KEYS */;
INSERT INTO `subtasks` VALUES (1,1,'Research market trends and competitor analysis',1,NULL,1,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(2,1,'Create project timeline with milestones',1,NULL,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(3,1,'Estimate budget and resource requirements',1,NULL,3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(4,1,'Identify potential risks and mitigation strategies',0,NULL,4,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(5,1,'Prepare presentation slides',0,NULL,5,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(6,1,'Schedule stakeholder review meeting',0,NULL,6,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(7,6,'Complete Duolingo daily lessons (5 lessons/day)',0,NULL,1,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(8,6,'Practice speaking with native speakers (2x/week)',0,NULL,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(9,6,'Review past tense conjugations',1,NULL,3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(10,6,'Study future tense conjugations',0,NULL,4,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(11,6,'Take A2 level assessment test',0,NULL,5,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(12,7,'Declutter desk and filing cabinets',0,NULL,1,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(13,7,'Install new shelving units',0,NULL,2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(14,7,'Set up cable management system',0,NULL,3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(15,7,'Purchase and install ergonomic chair',0,NULL,4,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(16,7,'Install proper lighting fixtures',0,NULL,5,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(17,7,'Create digital filing system',0,NULL,6,'2025-10-14 15:02:25','2025-10-14 15:02:25');
/*!40000 ALTER TABLE `subtasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_health_logs`
--

DROP TABLE IF EXISTS `system_health_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_health_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `check_type` varchar(100) NOT NULL,
  `check_name` varchar(255) NOT NULL,
  `status` enum('healthy','warning','critical') NOT NULL,
  `value` decimal(10,3) DEFAULT NULL,
  `threshold` decimal(10,3) DEFAULT NULL,
  `message` text,
  `details` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_check_type` (`check_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_health_logs`
--

LOCK TABLES `system_health_logs` WRITE;
/*!40000 ALTER TABLE `system_health_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_health_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_metrics`
--

DROP TABLE IF EXISTS `system_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_metrics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(255) NOT NULL,
  `metric_value` decimal(15,6) NOT NULL,
  `metric_unit` varchar(50) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `timestamp` decimal(20,6) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_metric_name` (`metric_name`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_metrics`
--

LOCK TABLES `system_metrics` WRITE;
/*!40000 ALTER TABLE `system_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','integer','boolean','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'app_name','SecureNote Pro','string','Application name',1,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(2,'app_version','2.0.0','string','Application version',1,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(3,'maintenance_mode','false','boolean','Maintenance mode status',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(4,'max_file_size','10485760','integer','Maximum file upload size in bytes',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(5,'allowed_file_types','pdf,doc,docx,txt,jpg,jpeg,png,gif,mp4,mp3','string','Allowed file types for upload',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(6,'backup_retention_days','30','integer','Number of days to retain backups',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(7,'session_timeout','7200','integer','Session timeout in seconds',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(8,'rate_limit_requests','100','integer','Rate limit requests per window',0,'2025-10-14 10:04:56','2025-10-14 10:04:56'),(9,'rate_limit_window','60','integer','Rate limit window in seconds',0,'2025-10-14 10:04:56','2025-10-14 10:04:56');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_statistics`
--

DROP TABLE IF EXISTS `table_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_statistics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `row_count` bigint NOT NULL,
  `data_size_mb` decimal(10,2) NOT NULL,
  `index_size_mb` decimal(10,2) NOT NULL,
  `total_size_mb` decimal(10,2) NOT NULL,
  `fragmentation_percent` decimal(5,2) DEFAULT '0.00',
  `last_optimized` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_table_name` (`table_name`),
  KEY `idx_total_size` (`total_size_mb`),
  KEY `idx_fragmentation` (`fragmentation_percent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_statistics`
--

LOCK TABLES `table_statistics` WRITE;
/*!40000 ALTER TABLE `table_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `usage_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_tag` (`user_id`,`name`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_usage_count` (`usage_count`),
  KEY `idx_tags_user_name` (`user_id`,`name`),
  KEY `idx_tags_user_usage` (`user_id`,`usage_count`),
  CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,1,'Work','#3b82f6','fas fa-briefcase','Work-related tasks and notes',4,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(2,1,'Personal','#10b981','fas fa-user','Personal tasks and notes',16,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(3,1,'Health','#ef4444','fas fa-heart','Health and fitness related',3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(4,1,'Finance','#f59e0b','fas fa-dollar-sign','Financial planning and budgeting',3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(5,1,'Learning','#8b5cf6','fas fa-graduation-cap','Educational content and courses',4,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(6,1,'Travel','#06b6d4','fas fa-plane','Travel plans and itineraries',2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(7,1,'Shopping','#ec4899','fas fa-shopping-cart','Shopping lists and purchases',1,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(8,1,'Home','#84cc16','fas fa-home','Home improvement and maintenance',2,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(9,1,'Family','#f97316','fas fa-users','Family events and activities',3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(10,1,'Projects','#6366f1','fas fa-project-diagram','Project management and planning',3,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(11,1,'Special','#3b82f6',NULL,NULL,0,'2025-10-15 12:19:41','2025-10-15 12:19:41'),(12,1,'Timothy kuria','#3b82f6',NULL,NULL,0,'2025-10-15 12:43:33','2025-10-15 12:43:33'),(13,1,'admin','#3b82f6',NULL,NULL,0,'2025-10-15 12:43:58','2025-10-15 12:43:58');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_attachments`
--

DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_encrypted` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_file_hash` (`file_hash`),
  CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_attachments`
--

LOCK TABLES `task_attachments` WRITE;
/*!40000 ALTER TABLE `task_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_executions`
--

DROP TABLE IF EXISTS `task_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_executions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `result_data` json DEFAULT NULL,
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `task_executions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `scheduled_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_executions`
--

LOCK TABLES `task_executions` WRITE;
/*!40000 ALTER TABLE `task_executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_reminders`
--

DROP TABLE IF EXISTS `task_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `reminder_time` datetime NOT NULL,
  `reminder_type` enum('email','push','sms') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `is_sent` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_reminder_time` (`reminder_time`),
  CONSTRAINT `task_reminders_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_reminders`
--

LOCK TABLES `task_reminders` WRITE;
/*!40000 ALTER TABLE `task_reminders` DISABLE KEYS */;
INSERT INTO `task_reminders` VALUES (1,1,'2025-10-19 09:00:00','email',0,NULL,'2025-10-14 15:02:25'),(2,1,'2025-10-20 08:00:00','push',0,NULL,'2025-10-14 15:02:25'),(3,2,'2025-11-10 10:00:00','email',0,NULL,'2025-10-14 15:02:25'),(4,2,'2025-11-14 18:00:00','push',0,NULL,'2025-10-14 15:02:25'),(5,3,'2025-12-05 09:00:00','email',0,NULL,'2025-10-14 15:02:25'),(6,4,'2025-11-15 10:00:00','email',0,NULL,'2025-10-14 15:02:25'),(7,6,'2025-10-15 20:00:00','push',0,NULL,'2025-10-14 15:02:25'),(8,8,'2026-01-15 09:00:00','email',0,NULL,'2025-10-14 15:02:25'),(9,9,'2025-10-20 18:00:00','push',0,NULL,'2025-10-14 15:02:25');
/*!40000 ALTER TABLE `task_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_tags`
--

DROP TABLE IF EXISTS `task_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_task_tag` (`task_id`,`tag_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_tag_id` (`tag_id`),
  KEY `idx_task_tags_task` (`task_id`),
  KEY `idx_task_tags_tag` (`tag_id`),
  CONSTRAINT `task_tags_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_tags`
--

LOCK TABLES `task_tags` WRITE;
/*!40000 ALTER TABLE `task_tags` DISABLE KEYS */;
INSERT INTO `task_tags` VALUES (1,1,1,'2025-10-14 15:02:25'),(2,1,10,'2025-10-14 15:02:25'),(3,2,3,'2025-10-14 15:02:25'),(4,2,2,'2025-10-14 15:02:25'),(5,3,4,'2025-10-14 15:02:25'),(6,3,2,'2025-10-14 15:02:25'),(7,4,9,'2025-10-14 15:02:25'),(8,4,2,'2025-10-14 15:02:25'),(9,5,1,'2025-10-14 15:02:25'),(10,5,5,'2025-10-14 15:02:25'),(11,6,5,'2025-10-14 15:02:25'),(12,6,2,'2025-10-14 15:02:25'),(13,7,8,'2025-10-14 15:02:25'),(14,7,2,'2025-10-14 15:02:25'),(15,8,4,'2025-10-14 15:02:25'),(16,8,2,'2025-10-14 15:02:25'),(17,9,6,'2025-10-14 15:02:25'),(18,9,2,'2025-10-14 15:02:25'),(19,10,9,'2025-10-14 15:02:25'),(20,10,2,'2025-10-14 15:02:25');
/*!40000 ALTER TABLE `task_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `progress` int DEFAULT '0',
  `due_date` datetime DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurring_pattern` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring_interval` int DEFAULT NULL,
  `parent_task_id` int DEFAULT NULL,
  `estimated_time` int DEFAULT NULL,
  `actual_time` int DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_priority` (`priority`),
  KEY `idx_parent_task` (`parent_task_id`),
  KEY `idx_is_archived` (`is_archived`),
  KEY `idx_tasks_user_created` (`user_id`,`created_at`),
  KEY `idx_tasks_user_updated` (`user_id`,`updated_at`),
  KEY `idx_tasks_user_status` (`user_id`,`status`),
  KEY `idx_tasks_due_date` (`user_id`,`due_date`),
  KEY `idx_tasks_priority` (`user_id`,`priority`),
  KEY `idx_tasks_search` (`user_id`,`title`,`description`(100)),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,1,'Visit the hospital','This was the day prepared to see the patients','pending','medium',0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,NULL,'2025-10-14 14:37:34','2025-10-14 14:37:34'),(2,1,'Complete Q4 Project Proposal','Prepare comprehensive project proposal for Q4 mobile app development including timeline, budget, resource allocation, and risk assessment. Include market research, competitor analysis, and technical specifications.','in_progress','urgent',65,'2025-10-20 17:00:00',NULL,0,NULL,NULL,NULL,480,312,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(3,1,'Schedule Annual Health Checkup','Book annual physical examination with Dr. Smith. Include blood work, cholesterol check, blood pressure monitoring, and discuss any health concerns. Bring list of current medications and supplements.','pending','medium',0,'2025-11-15 09:00:00',NULL,1,'yearly',NULL,NULL,120,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(4,1,'Renew Car Insurance Policy','Compare insurance quotes from 3 different providers. Current policy expires on December 15th. Consider adding roadside assistance and rental car coverage. Review coverage limits and deductibles.','pending','high',0,'2025-12-10 23:59:59',NULL,1,'yearly',NULL,NULL,180,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(5,1,'Plan Thanksgiving Dinner Menu','Create detailed menu for 12 guests including appetizers, main course, sides, desserts, and beverages. Consider dietary restrictions (2 vegetarians, 1 gluten-free). Create shopping list and cooking timeline.','pending','medium',0,'2025-11-20 18:00:00',NULL,1,'yearly',NULL,NULL,240,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(6,1,'Update LinkedIn Profile','Refresh professional profile with recent achievements, skills, and experience. Add new project portfolio, update headline, and request recommendations from colleagues. Optimize for relevant keywords.','completed','low',100,'2025-10-10 16:00:00',NULL,0,NULL,NULL,NULL,90,87,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(7,1,'Learn Spanish - Complete Level A2','Finish Duolingo Spanish course Level A2. Practice speaking with native speakers twice a week. Complete 5 lessons per day. Focus on past tense and future tense conjugations.','in_progress','medium',40,'2025-12-31 23:59:59',NULL,0,NULL,NULL,NULL,1200,480,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(8,1,'Organize Home Office Space','Declutter and reorganize home office for better productivity. Install new shelving, cable management system, and ergonomic chair. Set up proper lighting and create filing system for documents.','pending','low',0,'2025-11-30 17:00:00',NULL,0,NULL,NULL,NULL,360,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(9,1,'Prepare Tax Documents','Gather all necessary documents for 2025 tax filing including W-2s, 1099s, receipts for deductions, mortgage statements, and charitable contribution records. Schedule appointment with tax preparer.','pending','high',0,'2026-01-31 23:59:59',NULL,1,'yearly',NULL,NULL,300,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(10,1,'Plan Weekend Hiking Trip','Research and plan 2-day hiking trip to Blue Ridge Mountains. Book accommodation, check weather forecast, prepare gear list, and plan meals. Invite 3 friends and coordinate transportation.','pending','low',0,'2025-10-25 08:00:00',NULL,0,NULL,NULL,NULL,120,0,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25'),(11,1,'Review and Update Emergency Contacts','Update emergency contact information in phone, workplace records, and medical forms. Include family members, close friends, and healthcare providers. Share updated list with family members.','completed','medium',100,'2025-10-12 14:00:00',NULL,1,'yearly',NULL,NULL,60,45,0,0,NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_activity_logs`
--

DROP TABLE IF EXISTS `team_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int DEFAULT NULL,
  `description` text,
  `metadata` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `team_activity_logs_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_activity_logs`
--

LOCK TABLES `team_activity_logs` WRITE;
/*!40000 ALTER TABLE `team_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_invitations`
--

DROP TABLE IF EXISTS `team_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_invitations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `invited_by` int NOT NULL,
  `invited_email` varchar(255) NOT NULL,
  `invitation_token` varchar(255) NOT NULL,
  `role` enum('admin','moderator','member') DEFAULT 'member',
  `status` enum('pending','accepted','declined','expired') DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_token` (`invitation_token`),
  KEY `invited_by` (`invited_by`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_invited_email` (`invited_email`),
  KEY `idx_invitation_token` (`invitation_token`),
  KEY `idx_status` (`status`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `team_invitations_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_invitations_ibfk_2` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_invitations`
--

LOCK TABLES `team_invitations` WRITE;
/*!40000 ALTER TABLE `team_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('admin','moderator','member') DEFAULT 'member',
  `joined_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_user` (`team_id`,`user_id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role` (`role`),
  CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES (1,1,1,'admin','2025-10-24 11:50:53','2025-10-24 11:50:53');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_shared_notes`
--

DROP TABLE IF EXISTS `team_shared_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_shared_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note_id` int NOT NULL,
  `team_id` int NOT NULL,
  `permission` enum('read','write','admin') DEFAULT 'read',
  `shared_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_team` (`note_id`,`team_id`),
  KEY `idx_note_id` (`note_id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_permission` (`permission`),
  CONSTRAINT `team_shared_notes_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_shared_notes_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_shared_notes`
--

LOCK TABLES `team_shared_notes` WRITE;
/*!40000 ALTER TABLE `team_shared_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_shared_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_shared_tasks`
--

DROP TABLE IF EXISTS `team_shared_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_shared_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `team_id` int NOT NULL,
  `permission` enum('read','write','admin') DEFAULT 'read',
  `shared_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_task_team` (`task_id`,`team_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_permission` (`permission`),
  CONSTRAINT `team_shared_tasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_shared_tasks_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_shared_tasks`
--

LOCK TABLES `team_shared_tasks` WRITE;
/*!40000 ALTER TABLE `team_shared_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_shared_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_workspaces`
--

DROP TABLE IF EXISTS `team_workspaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_workspaces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `team_workspaces_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_workspaces`
--

LOCK TABLES `team_workspaces` WRITE;
/*!40000 ALTER TABLE `team_workspaces` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_workspaces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `owner_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'System Administrators','Default team for system administrators',1,'2025-10-24 11:50:53','2025-10-24 11:50:53'),(2,'System Administrators','Default team for system administrators',1,'2025-10-24 13:03:15','2025-10-24 13:03:15');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `two_factor_codes`
--

DROP TABLE IF EXISTS `two_factor_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `two_factor_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('email','sms','backup') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `two_factor_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `two_factor_codes`
--

LOCK TABLES `two_factor_codes` WRITE;
/*!40000 ALTER TABLE `two_factor_codes` DISABLE KEYS */;
INSERT INTO `two_factor_codes` VALUES (1,1,'123456','email',0,'2025-10-14 15:12:25','2025-10-14 15:02:25'),(2,1,'789012','sms',0,'2025-10-14 15:12:25','2025-10-14 15:02:25');
/*!40000 ALTER TABLE `two_factor_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uptime_check_results`
--

DROP TABLE IF EXISTS `uptime_check_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uptime_check_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `check_id` int NOT NULL,
  `status` enum('up','down','timeout','error') NOT NULL,
  `response_time` decimal(10,3) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `error_message` text,
  `response_headers` json DEFAULT NULL,
  `checked_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_check_id` (`check_id`),
  KEY `idx_status` (`status`),
  KEY `idx_checked_at` (`checked_at`),
  CONSTRAINT `uptime_check_results_ibfk_1` FOREIGN KEY (`check_id`) REFERENCES `uptime_checks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uptime_check_results`
--

LOCK TABLES `uptime_check_results` WRITE;
/*!40000 ALTER TABLE `uptime_check_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `uptime_check_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uptime_checks`
--

DROP TABLE IF EXISTS `uptime_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uptime_checks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `check_name` varchar(255) NOT NULL,
  `check_url` varchar(500) NOT NULL,
  `check_type` enum('http','https','tcp','ping') DEFAULT 'http',
  `expected_status_code` int DEFAULT '200',
  `timeout_seconds` int DEFAULT '30',
  `check_interval_seconds` int DEFAULT '300',
  `is_active` tinyint(1) DEFAULT '1',
  `last_check_at` datetime DEFAULT NULL,
  `last_status` enum('up','down','unknown') DEFAULT 'unknown',
  `last_response_time` decimal(10,3) DEFAULT NULL,
  `consecutive_failures` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_check_name` (`check_name`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_last_status` (`last_status`),
  KEY `idx_last_check_at` (`last_check_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uptime_checks`
--

LOCK TABLES `uptime_checks` WRITE;
/*!40000 ALTER TABLE `uptime_checks` DISABLE KEYS */;
INSERT INTO `uptime_checks` VALUES (1,'Application Health Check','/health','http',200,30,60,1,NULL,'unknown',NULL,0,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(2,'Database Connectivity','tcp://localhost:3306','tcp',NULL,30,120,1,NULL,'unknown',NULL,0,'2025-10-24 12:12:15','2025-10-24 12:12:15'),(3,'API Endpoint','/api/health','http',200,30,60,1,NULL,'unknown',NULL,0,'2025-10-24 12:12:15','2025-10-24 12:12:15');
/*!40000 ALTER TABLE `uptime_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_analytics`
--

DROP TABLE IF EXISTS `user_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `metric_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_value` decimal(10,4) NOT NULL,
  `metadata` json DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `metric_type` (`metric_type`),
  KEY `recorded_at` (`recorded_at`),
  CONSTRAINT `user_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_analytics`
--

LOCK TABLES `user_analytics` WRITE;
/*!40000 ALTER TABLE `user_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_behavior_analytics`
--

DROP TABLE IF EXISTS `user_behavior_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_behavior_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `page` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_behavior_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_behavior_analytics`
--

LOCK TABLES `user_behavior_analytics` WRITE;
/*!40000 ALTER TABLE `user_behavior_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_behavior_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_cloud_tokens`
--

DROP TABLE IF EXISTS `user_cloud_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_cloud_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service` enum('google_drive','dropbox','onedrive') NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text,
  `token_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_service` (`user_id`,`service`),
  CONSTRAINT `user_cloud_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_cloud_tokens`
--

LOCK TABLES `user_cloud_tokens` WRITE;
/*!40000 ALTER TABLE `user_cloud_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_cloud_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_consent`
--

DROP TABLE IF EXISTS `user_consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_consent` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `consent_type` varchar(100) NOT NULL,
  `consent_given` tinyint(1) NOT NULL,
  `consent_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `withdrawal_date` datetime DEFAULT NULL,
  `consent_version` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_consent_type` (`user_id`,`consent_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_consent_type` (`consent_type`),
  KEY `idx_consent_given` (`consent_given`),
  KEY `idx_consent_date` (`consent_date`),
  CONSTRAINT `user_consent_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_consent`
--

LOCK TABLES `user_consent` WRITE;
/*!40000 ALTER TABLE `user_consent` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_consent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_content_preferences`
--

DROP TABLE IF EXISTS `user_content_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_content_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `weather_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quote_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `show_weather` tinyint(1) DEFAULT '1',
  `show_quotes` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_content_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_content_preferences`
--

LOCK TABLES `user_content_preferences` WRITE;
/*!40000 ALTER TABLE `user_content_preferences` DISABLE KEYS */;
INSERT INTO `user_content_preferences` VALUES (1,1,'New York, NY','general',1,1,'2025-10-23 21:05:17','2025-10-23 21:05:17');
/*!40000 ALTER TABLE `user_content_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `group_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_group` (`user_id`,`group_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_group_id` (`group_id`),
  CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups_table` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_groups`
--

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_groups_table`
--

DROP TABLE IF EXISTS `user_groups_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_groups_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_groups_table`
--

LOCK TABLES `user_groups_table` WRITE;
/*!40000 ALTER TABLE `user_groups_table` DISABLE KEYS */;
INSERT INTO `user_groups_table` VALUES (1,'employees','Company Employees','2025-10-24 12:05:05','2025-10-24 12:05:05'),(2,'contractors','External Contractors','2025-10-24 12:05:05','2025-10-24 12:05:05'),(3,'administrators','System Administrators','2025-10-24 12:05:05','2025-10-24 12:05:05'),(4,'auditors','Compliance Auditors','2025-10-24 12:05:05','2025-10-24 12:05:05');
/*!40000 ALTER TABLE `user_groups_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_plugin_preferences`
--

DROP TABLE IF EXISTS `user_plugin_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_plugin_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plugin_id` int NOT NULL,
  `is_enabled` tinyint(1) DEFAULT '1',
  `user_config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_plugin` (`user_id`,`plugin_id`),
  KEY `user_id` (`user_id`),
  KEY `plugin_id` (`plugin_id`),
  CONSTRAINT `user_plugin_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_plugin_preferences_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_plugin_preferences`
--

LOCK TABLES `user_plugin_preferences` WRITE;
/*!40000 ALTER TABLE `user_plugin_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_plugin_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `theme` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `language` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `date_format` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y-m-d',
  `time_format` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '24h',
  `notifications_email` tinyint(1) DEFAULT '1',
  `notifications_push` tinyint(1) DEFAULT '1',
  `notifications_sound` tinyint(1) DEFAULT '1',
  `auto_save_notes` tinyint(1) DEFAULT '1',
  `auto_save_interval` int DEFAULT '30',
  `default_note_color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `default_task_priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `dashboard_layout` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_preferences` (`user_id`),
  CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES (1,1,'light','en','America/New_York','Y-m-d','12h',1,1,1,1,30,'#ffffff','medium',NULL,'2025-10-14 15:02:25','2025-10-14 15:02:25');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_presence`
--

DROP TABLE IF EXISTS `user_presence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_presence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `resource_type` enum('note','task','team') NOT NULL,
  `resource_id` int NOT NULL,
  `status` enum('online','away','busy','offline') DEFAULT 'online',
  `last_seen` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_resource` (`user_id`,`resource_type`,`resource_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_status` (`status`),
  KEY `idx_last_seen` (`last_seen`),
  CONSTRAINT `user_presence_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_presence`
--

LOCK TABLES `user_presence` WRITE;
/*!40000 ALTER TABLE `user_presence` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_presence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_assigned_by` (`assigned_by`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_session_analytics`
--

DROP TABLE IF EXISTS `user_session_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_session_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_session_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_session_analytics`
--

LOCK TABLES `user_session_analytics` WRITE;
/*!40000 ALTER TABLE `user_session_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_session_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `device_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES (1,1,'9e8197b7aad18a23b332a59b7ed89901','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-14 14:09:11','2025-10-14 14:09:11','2025-10-14 16:09:11'),(2,1,'a7eb6b2325b10aaa4f66de088830b99b','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-14 17:49:42','2025-10-14 17:49:42','2025-10-14 19:49:42'),(3,1,'e1ff933a8c02848a65c6d4ef967c5776','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-14 20:15:07','2025-10-14 20:15:07','2025-10-14 22:15:07'),(4,1,'01f213ccbb2a2a720d45da1bfdf4a080','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-15 10:37:34','2025-10-15 10:37:34','2025-10-15 12:37:34'),(5,1,'778u4gsifkccv5m4d3c1sn2kea','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-23 21:05:17','2025-10-23 21:05:17','2025-10-23 23:05:17'),(6,1,'st1p995tt7q872ktqurbs6e9rf','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-23 23:07:41','2025-10-23 23:07:41','2025-10-24 01:07:41'),(7,1,'b3o1bmfnuoun8tkqh2g2gjf66h','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',NULL,1,'2025-10-24 09:59:33','2025-10-24 09:59:33','2025-10-24 11:59:33');
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `two_factor_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_restricted` tinyint(1) DEFAULT '0',
  `restriction_reason` text COLLATE utf8mb4_unicode_ci,
  `processing_objected` tinyint(1) DEFAULT '0',
  `objection_reason` text COLLATE utf8mb4_unicode_ci,
  `consent_given` tinyint(1) DEFAULT '0',
  `consent_date` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `login_count` int DEFAULT '0',
  `failed_login_count` int DEFAULT '0',
  `backup_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `google_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'timothy','Tkuria30@gmail.com','$argon2id$v=19$m=65536,t=4,p=3$aDZuTHppemRNLmtBZW9ZQQ$GvQd4PJJWRqpnnHmU9U7szQ/+Dzu/zH8g8SegLqQHuY','Timothy','timothy',NULL,0,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,0,NULL,'2025-10-24 09:59:33','2025-10-24 09:59:33',0,NULL,NULL,NULL,NULL,'2025-10-14 14:09:03','2025-10-24 09:59:33');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voice_notes`
--

DROP TABLE IF EXISTS `voice_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voice_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `duration` int DEFAULT NULL,
  `file_size` int NOT NULL,
  `transcription` text,
  `is_processed` tinyint(1) DEFAULT '0',
  `linked_note_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `linked_note_id` (`linked_note_id`),
  KEY `idx_voice_notes_user` (`user_id`),
  KEY `idx_voice_notes_created` (`created_at`),
  KEY `idx_voice_notes_processed` (`is_processed`),
  CONSTRAINT `voice_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voice_notes_ibfk_2` FOREIGN KEY (`linked_note_id`) REFERENCES `notes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voice_notes`
--

LOCK TABLES `voice_notes` WRITE;
/*!40000 ALTER TABLE `voice_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `voice_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather_data`
--

DROP TABLE IF EXISTS `weather_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weather_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `humidity` int DEFAULT NULL,
  `wind_speed` decimal(5,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather_data`
--

LOCK TABLES `weather_data` WRITE;
/*!40000 ALTER TABLE `weather_data` DISABLE KEYS */;
INSERT INTO `weather_data` VALUES (1,'New York, NY',22.50,'Partly Cloudy',65,12.30,'2025-10-23 20:40:46'),(2,'Los Angeles, CA',28.00,'Sunny',45,8.70,'2025-10-23 20:40:46'),(3,'Chicago, IL',18.20,'Cloudy',78,15.10,'2025-10-23 20:40:46'),(4,'Houston, TX',32.10,'Hot and Humid',85,6.20,'2025-10-23 20:40:46'),(5,'Phoenix, AZ',35.80,'Very Hot',25,4.50,'2025-10-23 20:40:46');
/*!40000 ALTER TABLE `weather_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_executions`
--

DROP TABLE IF EXISTS `webhook_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_executions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `webhook_id` varchar(255) NOT NULL,
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `status` enum('running','completed','failed') DEFAULT 'running',
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhook_id` (`webhook_id`),
  CONSTRAINT `webhook_executions_ibfk_1` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`webhook_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_executions`
--

LOCK TABLES `webhook_executions` WRITE;
/*!40000 ALTER TABLE `webhook_executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhooks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `webhook_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `url` varchar(500) NOT NULL,
  `method` enum('GET','POST','PUT','DELETE','PATCH') DEFAULT 'POST',
  `headers` json DEFAULT NULL,
  `authentication_type` enum('none','bearer','basic','api_key') DEFAULT 'none',
  `authentication_data` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `webhook_id` (`webhook_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `webhooks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_executions`
--

DROP TABLE IF EXISTS `workflow_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_executions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `template_id` int NOT NULL,
  `execution_data` json NOT NULL,
  `status` enum('pending','running','completed','failed','cancelled') NOT NULL,
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_executions_user` (`user_id`),
  KEY `idx_workflow_executions_template` (`template_id`),
  KEY `idx_workflow_executions_status` (`status`),
  KEY `idx_workflow_executions_started` (`started_at`),
  CONSTRAINT `workflow_executions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workflow_executions_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `workflow_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_executions`
--

LOCK TABLES `workflow_executions` WRITE;
/*!40000 ALTER TABLE `workflow_executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_templates`
--

DROP TABLE IF EXISTS `workflow_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `workflow_data` json NOT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_templates_user` (`user_id`),
  KEY `idx_workflow_templates_public` (`is_public`),
  KEY `idx_workflow_templates_usage` (`usage_count`),
  CONSTRAINT `workflow_templates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_templates`
--

LOCK TABLES `workflow_templates` WRITE;
/*!40000 ALTER TABLE `workflow_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_templates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-24 13:03:23
