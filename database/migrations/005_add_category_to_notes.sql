-- Add category field to notes table
ALTER TABLE `notes` ADD COLUMN `category` ENUM('general', 'work', 'personal', 'study', 'ideas', 'meetings', 'projects', 'research', 'journal', 'other') DEFAULT 'general' AFTER `priority`;

-- Add index for category filtering
ALTER TABLE `notes` ADD INDEX `idx_category` (`category`);
