<?php
namespace Core\DataManagement;

use PDO;
use Exception;

class MigrationService {
    private $db;
    private $migrationDir;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->migrationDir = __DIR__ . '/../../database/migrations/';
    }
    
    /**
     * Run all pending migrations
     */
    public function runMigrations(): array {
        try {
            $this->ensureMigrationTable();
            
            $pendingMigrations = $this->getPendingMigrations();
            $results = [
                'success' => true,
                'migrations_run' => 0,
                'migrations' => [],
                'errors' => []
            ];
            
            foreach ($pendingMigrations as $migration) {
                try {
                    $result = $this->runMigration($migration);
                    $results['migrations'][] = $result;
                    $results['migrations_run']++;
                } catch (Exception $e) {
                    $results['errors'][] = "Migration {$migration}: " . $e->getMessage();
                    $results['success'] = false;
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error running migrations: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Run a specific migration
     */
    public function runMigration(string $migrationFile): array {
        try {
            $filepath = $this->migrationDir . $migrationFile;
            if (!file_exists($filepath)) {
                throw new Exception("Migration file not found: {$migrationFile}");
            }
            
            $sql = file_get_contents($filepath);
            if (empty($sql)) {
                throw new Exception("Migration file is empty: {$migrationFile}");
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Split SQL into individual statements
                $statements = $this->splitSQL($sql);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $this->db->exec($statement);
                    }
                }
                
                // Record migration as completed
                $this->recordMigration($migrationFile);
                
                $this->db->commit();
                
                return [
                    'success' => true,
                    'migration' => $migrationFile,
                    'statements_executed' => count($statements)
                ];
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error running migration {$migrationFile}: " . $e->getMessage());
            return [
                'success' => false,
                'migration' => $migrationFile,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Rollback a specific migration
     */
    public function rollbackMigration(string $migrationFile): array {
        try {
            // Check if migration was run
            $stmt = $this->db->prepare("SELECT id FROM migration_history WHERE migration_file = ?");
            $stmt->execute([$migrationFile]);
            if (!$stmt->fetch()) {
                throw new Exception("Migration {$migrationFile} was not run");
            }
            
            // Look for rollback file
            $rollbackFile = str_replace('.sql', '_rollback.sql', $migrationFile);
            $rollbackPath = $this->migrationDir . $rollbackFile;
            
            if (!file_exists($rollbackPath)) {
                throw new Exception("Rollback file not found: {$rollbackFile}");
            }
            
            $sql = file_get_contents($rollbackPath);
            if (empty($sql)) {
                throw new Exception("Rollback file is empty: {$rollbackFile}");
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Split SQL into individual statements
                $statements = $this->splitSQL($sql);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $this->db->exec($statement);
                    }
                }
                
                // Remove migration from history
                $this->removeMigration($migrationFile);
                
                $this->db->commit();
                
                return [
                    'success' => true,
                    'migration' => $migrationFile,
                    'rollback_file' => $rollbackFile,
                    'statements_executed' => count($statements)
                ];
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error rolling back migration {$migrationFile}: " . $e->getMessage());
            return [
                'success' => false,
                'migration' => $migrationFile,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get migration status
     */
    public function getMigrationStatus(): array {
        try {
            $this->ensureMigrationTable();
            
            $allMigrations = $this->getAllMigrations();
            $completedMigrations = $this->getCompletedMigrations();
            
            $status = [
                'total_migrations' => count($allMigrations),
                'completed_migrations' => count($completedMigrations),
                'pending_migrations' => [],
                'completed' => [],
                'last_migration' => null
            ];
            
            foreach ($allMigrations as $migration) {
                if (in_array($migration, $completedMigrations)) {
                    $status['completed'][] = $migration;
                } else {
                    $status['pending_migrations'][] = $migration;
                }
            }
            
            if (!empty($status['completed'])) {
                $stmt = $this->db->prepare("
                    SELECT migration_file, created_at 
                    FROM migration_history 
                    WHERE migration_file = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([end($status['completed'])]);
                $lastMigration = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($lastMigration) {
                    $status['last_migration'] = $lastMigration;
                }
            }
            
            return $status;
            
        } catch (Exception $e) {
            error_log("Error getting migration status: " . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new migration file
     */
    public function createMigration(string $name, string $description = ''): array {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = $timestamp . '_' . $this->sanitizeFilename($name) . '.sql';
            $filepath = $this->migrationDir . $filename;
            
            $content = "-- Migration: {$name}\n";
            $content .= "-- Description: {$description}\n";
            $content .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
            $content .= "-- Add your SQL statements here\n";
            $content .= "-- Example:\n";
            $content .= "-- CREATE TABLE example_table (\n";
            $content .= "--     id INT AUTO_INCREMENT PRIMARY KEY,\n";
            $content .= "--     name VARCHAR(255) NOT NULL,\n";
            $content .= "--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP\n";
            $content .= "-- );\n";
            
            if (file_put_contents($filepath, $content)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath
                ];
            } else {
                throw new Exception('Failed to create migration file');
            }
            
        } catch (Exception $e) {
            error_log("Error creating migration: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a rollback file for a migration
     */
    public function createRollback(string $migrationFile): array {
        try {
            $rollbackFile = str_replace('.sql', '_rollback.sql', $migrationFile);
            $rollbackPath = $this->migrationDir . $rollbackFile;
            
            $content = "-- Rollback for: {$migrationFile}\n";
            $content .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
            $content .= "-- Add your rollback SQL statements here\n";
            $content .= "-- Example:\n";
            $content .= "-- DROP TABLE IF EXISTS example_table;\n";
            
            if (file_put_contents($rollbackPath, $content)) {
                return [
                    'success' => true,
                    'filename' => $rollbackFile,
                    'filepath' => $rollbackPath
                ];
            } else {
                throw new Exception('Failed to create rollback file');
            }
            
        } catch (Exception $e) {
            error_log("Error creating rollback: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate migration file
     */
    public function validateMigration(string $migrationFile): array {
        try {
            $filepath = $this->migrationDir . $migrationFile;
            if (!file_exists($filepath)) {
                return ['valid' => false, 'error' => 'Migration file not found'];
            }
            
            $sql = file_get_contents($filepath);
            if (empty($sql)) {
                return ['valid' => false, 'error' => 'Migration file is empty'];
            }
            
            // Basic SQL validation
            $statements = $this->splitSQL($sql);
            $validStatements = 0;
            $errors = [];
            
            foreach ($statements as $i => $statement) {
                $statement = trim($statement);
                if (empty($statement)) continue;
                
                // Check for basic SQL syntax
                if (preg_match('/^(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|SELECT)\s+/i', $statement)) {
                    $validStatements++;
                } else {
                    $errors[] = "Statement " . ($i + 1) . " does not appear to be valid SQL";
                }
            }
            
            return [
                'valid' => empty($errors),
                'statements_count' => count($statements),
                'valid_statements' => $validStatements,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get migration history
     */
    public function getMigrationHistory(int $limit = 50): array {
        try {
            $this->ensureMigrationTable();
            
            $stmt = $this->db->prepare("
                SELECT id, migration_file, created_at, execution_time_ms, status, error_message
                FROM migration_history
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting migration history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ensure migration table exists
     */
    private function ensureMigrationTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migration_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_file VARCHAR(255) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                execution_time_ms INT DEFAULT 0,
                status ENUM('success', 'failed') DEFAULT 'success',
                error_message TEXT NULL
            )
        ");
    }
    
    /**
     * Get all migration files
     */
    private function getAllMigrations(): array {
        $files = glob($this->migrationDir . '*.sql');
        $migrations = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            // Skip rollback files
            if (!strpos($filename, '_rollback.sql')) {
                $migrations[] = $filename;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Get completed migrations
     */
    private function getCompletedMigrations(): array {
        $stmt = $this->db->prepare("SELECT migration_file FROM migration_history ORDER BY created_at");
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'migration_file');
    }
    
    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array {
        $allMigrations = $this->getAllMigrations();
        $completedMigrations = $this->getCompletedMigrations();
        
        return array_diff($allMigrations, $completedMigrations);
    }
    
    /**
     * Record migration as completed
     */
    private function recordMigration(string $migrationFile): void {
        $stmt = $this->db->prepare("
            INSERT INTO migration_history (migration_file, created_at, status) 
            VALUES (?, NOW(), 'success')
        ");
        $stmt->execute([$migrationFile]);
    }
    
    /**
     * Remove migration from history
     */
    private function removeMigration(string $migrationFile): void {
        $stmt = $this->db->prepare("DELETE FROM migration_history WHERE migration_file = ?");
        $stmt->execute([$migrationFile]);
    }
    
    /**
     * Split SQL into individual statements
     */
    private function splitSQL(string $sql): array {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon, but be careful with strings
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar) {
                // Check for escaped quotes
                if ($i > 0 && $sql[$i-1] !== '\\') {
                    $inString = false;
                }
            } elseif (!$inString && $char === ';') {
                $statements[] = trim($current);
                $current = '';
                continue;
            }
            
            $current .= $char;
        }
        
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        return array_filter($statements, function($stmt) {
            return !empty(trim($stmt));
        });
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $name): string {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    }
    
    /**
     * Backup database before migration
     */
    public function backupDatabase(string $backupPath = null): array {
        try {
            if (!$backupPath) {
                $backupPath = __DIR__ . '/../../backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            }
            
            $backupDir = dirname($backupPath);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            // Get database configuration
            $config = $this->getDatabaseConfig();
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['database']),
                escapeshellarg($backupPath)
            );
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupPath)) {
                return [
                    'success' => true,
                    'backup_path' => $backupPath,
                    'size' => filesize($backupPath)
                ];
            } else {
                throw new Exception('Database backup failed: ' . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            error_log("Error backing up database: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get database configuration
     */
    private function getDatabaseConfig(): array {
        // This would typically come from your config file
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'database' => $_ENV['DB_DATABASE'] ?? 'personal'
        ];
    }
}
