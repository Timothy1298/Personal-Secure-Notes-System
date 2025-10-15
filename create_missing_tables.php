<?php
require_once 'vendor/autoload.php';
require_once 'core/Database.php';

use Core\Database;

try {
    $db = Database::getInstance();
    
    // Create cloud_connections table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `cloud_connections` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `service` enum('google_drive','onedrive','dropbox') NOT NULL,
          `connection_data` json NOT NULL,
          `status` enum('connected','disconnected','error') DEFAULT 'disconnected',
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_service` (`user_id`, `service`),
          KEY `idx_user_id` (`user_id`),
          KEY `idx_service` (`service`),
          KEY `idx_status` (`status`),
          FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create backup_settings table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `backup_settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `settings` json NOT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_settings` (`user_id`),
          FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Fix rate_limits table
    $db->exec("DROP TABLE IF EXISTS `rate_limits`");
    $db->exec("
        CREATE TABLE `rate_limits` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `rate_key` varchar(255) NOT NULL,
          `ip_address` varchar(45) NOT NULL,
          `user_agent` text DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_rate_key` (`rate_key`),
          KEY `idx_ip_address` (`ip_address`),
          KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "Missing tables created successfully!\n";
    
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
