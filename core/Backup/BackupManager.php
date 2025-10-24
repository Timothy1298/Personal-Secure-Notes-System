<?php
namespace Core\Backup;

use PDO;
use Exception;
use ZipArchive;

class BackupManager {
    private $db;
    private $backupDir;
    private $maxBackups;
    private $compressionLevel;
    
    public function __construct(PDO $db, string $backupDir = null) {
        $this->db = $db;
        $this->backupDir = $backupDir ?? __DIR__ . '/../../backups/';
        $this->maxBackups = 30; // Keep 30 days of backups
        $this->compressionLevel = 6; // Medium compression
        
        $this->ensureBackupDirectory();
    }
    
    /**
     * Create a full system backup
     */
    public function createFullBackup(): array {
        $backupId = uniqid('backup_');
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_full_{$timestamp}_{$backupId}";
        
        try {
            $backupPath = $this->backupDir . $backupName;
            mkdir($backupPath, 0755, true);
            
            // Backup database
            $dbBackup = $this->backupDatabase($backupPath);
            
            // Backup files
            $filesBackup = $this->backupFiles($backupPath);
            
            // Backup configuration
            $configBackup = $this->backupConfiguration($backupPath);
            
            // Create backup manifest
            $manifest = $this->createBackupManifest($backupPath, [
                'database' => $dbBackup,
                'files' => $filesBackup,
                'configuration' => $configBackup
            ]);
            
            // Compress backup
            $compressedBackup = $this->compressBackup($backupPath, $backupName);
            
            // Clean up temporary directory
            $this->removeDirectory($backupPath);
            
            // Record backup in database
            $this->recordBackup($backupName, 'full', $compressedBackup['size'], $compressedBackup['path']);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'backup_name' => $backupName,
                'backup_path' => $compressedBackup['path'],
                'backup_size' => $compressedBackup['size'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Backup failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create an incremental backup
     */
    public function createIncrementalBackup(): array {
        $backupId = uniqid('backup_inc_');
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_incremental_{$timestamp}_{$backupId}";
        
        try {
            $backupPath = $this->backupDir . $backupName;
            mkdir($backupPath, 0755, true);
            
            // Get last backup timestamp
            $lastBackup = $this->getLastBackup();
            $lastBackupTime = $lastBackup ? strtotime($lastBackup['created_at']) : 0;
            
            // Backup changed database records
            $dbBackup = $this->backupDatabaseIncremental($backupPath, $lastBackupTime);
            
            // Backup changed files
            $filesBackup = $this->backupFilesIncremental($backupPath, $lastBackupTime);
            
            // Create backup manifest
            $manifest = $this->createBackupManifest($backupPath, [
                'database' => $dbBackup,
                'files' => $filesBackup,
                'incremental_since' => $lastBackupTime
            ]);
            
            // Compress backup
            $compressedBackup = $this->compressBackup($backupPath, $backupName);
            
            // Clean up temporary directory
            $this->removeDirectory($backupPath);
            
            // Record backup in database
            $this->recordBackup($backupName, 'incremental', $compressedBackup['size'], $compressedBackup['path']);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'backup_name' => $backupName,
                'backup_path' => $compressedBackup['path'],
                'backup_size' => $compressedBackup['size'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Incremental backup failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Backup database
     */
    private function backupDatabase(string $backupPath): array {
        $dbFile = $backupPath . '/database.sql';
        
        // Get database configuration
        $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_ENV['DB_DATABASE'] ?? 'personal';
        $dbUser = $_ENV['DB_USERNAME'] ?? 'root';
        $dbPass = $_ENV['DB_PASSWORD'] ?? '';
        
        // Create mysqldump command
        $command = "mysqldump -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$dbFile}";
        
        // Execute mysqldump
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database backup failed: " . implode("\n", $output));
        }
        
        return [
            'file' => $dbFile,
            'size' => filesize($dbFile)
        ];
    }
    
    /**
     * Backup database incrementally
     */
    private function backupDatabaseIncremental(string $backupPath, int $sinceTimestamp): array {
        $dbFile = $backupPath . '/database_incremental.sql';
        
        // Get tables with updated_at columns
        $tables = $this->getTablesWithTimestamps();
        
        $sql = "-- Incremental database backup since " . date('Y-m-d H:i:s', $sinceTimestamp) . "\n";
        $sql .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $sql .= "-- Table: {$table}\n";
            $sql .= "DELETE FROM {$table} WHERE updated_at > FROM_UNIXTIME({$sinceTimestamp});\n";
            $sql .= "INSERT INTO {$table} SELECT * FROM {$table} WHERE updated_at > FROM_UNIXTIME({$sinceTimestamp});\n\n";
        }
        
        file_put_contents($dbFile, $sql);
        
        return [
            'file' => $dbFile,
            'size' => filesize($dbFile)
        ];
    }
    
    /**
     * Backup files
     */
    private function backupFiles(string $backupPath): array {
        $filesDir = $backupPath . '/files';
        mkdir($filesDir, 0755, true);
        
        $directories = [
            'uploads' => __DIR__ . '/../../public/uploads',
            'storage' => __DIR__ . '/../../storage',
            'exports' => __DIR__ . '/../../exports',
            'imports' => __DIR__ . '/../../imports',
            'backups' => __DIR__ . '/../../backups'
        ];
        
        $totalSize = 0;
        $backedUpFiles = [];
        
        foreach ($directories as $name => $sourcePath) {
            if (is_dir($sourcePath)) {
                $destPath = $filesDir . '/' . $name;
                $this->copyDirectory($sourcePath, $destPath);
                $totalSize += $this->getDirectorySize($destPath);
                $backedUpFiles[] = $name;
            }
        }
        
        return [
            'directory' => $filesDir,
            'size' => $totalSize,
            'files' => $backedUpFiles
        ];
    }
    
    /**
     * Backup files incrementally
     */
    private function backupFilesIncremental(string $backupPath, int $sinceTimestamp): array {
        $filesDir = $backupPath . '/files';
        mkdir($filesDir, 0755, true);
        
        $directories = [
            'uploads' => __DIR__ . '/../../public/uploads',
            'storage' => __DIR__ . '/../../storage',
            'exports' => __DIR__ . '/../../exports',
            'imports' => __DIR__ . '/../../imports'
        ];
        
        $totalSize = 0;
        $backedUpFiles = [];
        
        foreach ($directories as $name => $sourcePath) {
            if (is_dir($sourcePath)) {
                $destPath = $filesDir . '/' . $name;
                $this->copyDirectoryIncremental($sourcePath, $destPath, $sinceTimestamp);
                $totalSize += $this->getDirectorySize($destPath);
                $backedUpFiles[] = $name;
            }
        }
        
        return [
            'directory' => $filesDir,
            'size' => $totalSize,
            'files' => $backedUpFiles
        ];
    }
    
    /**
     * Backup configuration
     */
    private function backupConfiguration(string $backupPath): array {
        $configDir = $backupPath . '/config';
        mkdir($configDir, 0755, true);
        
        $configFiles = [
            'config.php' => __DIR__ . '/../../config/config.php',
            '.env' => __DIR__ . '/../../.env',
            'composer.json' => __DIR__ . '/../../composer.json',
            'composer.lock' => __DIR__ . '/../../composer.lock'
        ];
        
        $totalSize = 0;
        $backedUpFiles = [];
        
        foreach ($configFiles as $name => $sourcePath) {
            if (file_exists($sourcePath)) {
                $destPath = $configDir . '/' . $name;
                copy($sourcePath, $destPath);
                $totalSize += filesize($destPath);
                $backedUpFiles[] = $name;
            }
        }
        
        return [
            'directory' => $configDir,
            'size' => $totalSize,
            'files' => $backedUpFiles
        ];
    }
    
    /**
     * Create backup manifest
     */
    private function createBackupManifest(string $backupPath, array $components): array {
        $manifest = [
            'backup_id' => uniqid(),
            'created_at' => date('Y-m-d H:i:s'),
            'backup_type' => 'full',
            'version' => '1.0',
            'components' => $components,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'mysql_version' => $this->getMySQLVersion(),
                'server_os' => PHP_OS,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ];
        
        $manifestFile = $backupPath . '/manifest.json';
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));
        
        return [
            'file' => $manifestFile,
            'size' => filesize($manifestFile)
        ];
    }
    
    /**
     * Compress backup
     */
    private function compressBackup(string $backupPath, string $backupName): array {
        $zipFile = $this->backupDir . $backupName . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create zip file: {$zipFile}");
        }
        
        $this->addDirectoryToZip($zip, $backupPath, '');
        $zip->close();
        
        return [
            'path' => $zipFile,
            'size' => filesize($zipFile)
        ];
    }
    
    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupPath): array {
        try {
            // Extract backup
            $extractPath = $this->backupDir . 'restore_' . uniqid();
            $this->extractBackup($backupPath, $extractPath);
            
            // Read manifest
            $manifestFile = $extractPath . '/manifest.json';
            if (!file_exists($manifestFile)) {
                throw new Exception("Backup manifest not found");
            }
            
            $manifest = json_decode(file_get_contents($manifestFile), true);
            
            // Restore database
            if (isset($manifest['components']['database'])) {
                $this->restoreDatabase($extractPath . '/database.sql');
            }
            
            // Restore files
            if (isset($manifest['components']['files'])) {
                $this->restoreFiles($extractPath . '/files');
            }
            
            // Restore configuration
            if (isset($manifest['components']['configuration'])) {
                $this->restoreConfiguration($extractPath . '/config');
            }
            
            // Clean up
            $this->removeDirectory($extractPath);
            
            return [
                'success' => true,
                'message' => 'Backup restored successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Restore failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM backup_history 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error listing backups: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean up old backups
     */
    public function cleanupOldBackups(): array {
        try {
            // Get old backups
            $stmt = $this->db->prepare("
                SELECT * FROM backup_history 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY created_at ASC
            ");
            $stmt->execute([$this->maxBackups]);
            $oldBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $deletedCount = 0;
            $freedSpace = 0;
            
            foreach ($oldBackups as $backup) {
                if (file_exists($backup['backup_path'])) {
                    $freedSpace += filesize($backup['backup_path']);
                    unlink($backup['backup_path']);
                }
                
                $stmt = $this->db->prepare("DELETE FROM backup_history WHERE id = ?");
                $stmt->execute([$backup['id']]);
                $deletedCount++;
            }
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'freed_space' => $freedSpace
            ];
            
        } catch (Exception $e) {
            error_log("Error cleaning up backups: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify backup integrity
     */
    public function verifyBackup(string $backupPath): array {
        try {
            // Check if file exists
            if (!file_exists($backupPath)) {
                return [
                    'success' => false,
                    'error' => 'Backup file not found'
                ];
            }
            
            // Check file size
            $fileSize = filesize($backupPath);
            if ($fileSize === 0) {
                return [
                    'success' => false,
                    'error' => 'Backup file is empty'
                ];
            }
            
            // Test zip integrity
            $zip = new ZipArchive();
            $result = $zip->open($backupPath, ZipArchive::CHECKCONS);
            
            if ($result !== TRUE) {
                return [
                    'success' => false,
                    'error' => 'Backup file is corrupted'
                ];
            }
            
            $zip->close();
            
            return [
                'success' => true,
                'file_size' => $fileSize,
                'integrity_check' => 'passed'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Helper methods
    private function ensureBackupDirectory(): void {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    private function getTablesWithTimestamps(): array {
        $stmt = $this->db->prepare("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND COLUMN_NAME = 'updated_at'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function copyDirectory(string $source, string $destination): void {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                mkdir($destPath, 0755, true);
            } else {
                copy($item, $destPath);
            }
        }
    }
    
    private function copyDirectoryIncremental(string $source, string $destination, int $sinceTimestamp): void {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->getMTime() > $sinceTimestamp) {
                $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                
                if ($item->isDir()) {
                    mkdir($destPath, 0755, true);
                } else {
                    copy($item, $destPath);
                }
            }
        }
    }
    
    private function getDirectorySize(string $directory): int {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    private function addDirectoryToZip(ZipArchive $zip, string $path, string $zipPath): void {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . substr($filePath, strlen($path) + 1);
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    private function extractBackup(string $zipPath, string $extractPath): void {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            throw new Exception("Cannot open zip file: {$zipPath}");
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
    }
    
    private function restoreDatabase(string $sqlFile): void {
        $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_ENV['DB_DATABASE'] ?? 'personal';
        $dbUser = $_ENV['DB_USERNAME'] ?? 'root';
        $dbPass = $_ENV['DB_PASSWORD'] ?? '';
        
        $command = "mysql -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} < {$sqlFile}";
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database restore failed: " . implode("\n", $output));
        }
    }
    
    private function restoreFiles(string $filesPath): void {
        $directories = [
            'uploads' => __DIR__ . '/../../public/uploads',
            'storage' => __DIR__ . '/../../storage',
            'exports' => __DIR__ . '/../../exports',
            'imports' => __DIR__ . '/../../imports'
        ];
        
        foreach ($directories as $name => $destPath) {
            $sourcePath = $filesPath . '/' . $name;
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            }
        }
    }
    
    private function restoreConfiguration(string $configPath): void {
        $configFiles = [
            'config.php' => __DIR__ . '/../../config/config.php',
            '.env' => __DIR__ . '/../../.env'
        ];
        
        foreach ($configFiles as $name => $destPath) {
            $sourcePath = $configPath . '/' . $name;
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destPath);
            }
        }
    }
    
    private function removeDirectory(string $path): void {
        if (is_dir($path)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            
            rmdir($path);
        }
    }
    
    private function recordBackup(string $name, string $type, int $size, string $path): void {
        $stmt = $this->db->prepare("
            INSERT INTO backup_history (backup_name, backup_type, backup_size, backup_path, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $type, $size, $path]);
    }
    
    private function getLastBackup(): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM backup_history 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getMySQLVersion(): string {
        $stmt = $this->db->prepare("SELECT VERSION() as version");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['version'] ?? 'Unknown';
    }
}
