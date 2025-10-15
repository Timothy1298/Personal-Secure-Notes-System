-- Add Dropbox fields to backup_history table
ALTER TABLE backup_history 
ADD COLUMN dropbox_file_path VARCHAR(500) NULL AFTER google_drive_uploaded_at,
ADD COLUMN dropbox_uploaded_at TIMESTAMP NULL AFTER dropbox_file_path,
ADD INDEX idx_dropbox_file_path (dropbox_file_path);
