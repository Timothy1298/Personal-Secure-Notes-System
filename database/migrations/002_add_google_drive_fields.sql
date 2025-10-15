-- Add Google Drive fields to backup_history table
ALTER TABLE backup_history 
ADD COLUMN google_drive_file_id VARCHAR(255) NULL AFTER file_path,
ADD COLUMN google_drive_uploaded_at TIMESTAMP NULL AFTER google_drive_file_id,
ADD INDEX idx_google_drive_file_id (google_drive_file_id);

