<?php
namespace App\Controllers;

use Core\Session;
use PDO;
use Exception;

class BackupController {
    private $db;
    private $backupDir;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->backupDir = __DIR__ . '/../../backups/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function index() {
        include __DIR__ . '/../Views/backup_export.php';
    }

    public function createBackup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $userId = Session::get('user_id');
                $backupData = $this->generateBackupData($userId);
                $filename = 'backup_full_' . date('Y-m-d_H-i-s') . '.json';
                $filepath = $this->backupDir . $filename;
                
                file_put_contents($filepath, json_encode($backupData, JSON_PRETTY_PRINT));
                
                // Store backup record in database
                $stmt = $this->db->prepare("
                    INSERT INTO backup_history (user_id, filename, file_path, file_size, backup_type, created_at) 
                    VALUES (:user_id, :filename, :file_path, :file_size, :backup_type, NOW())
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':filename' => $filename,
                    ':file_path' => $filepath,
                    ':file_size' => filesize($filepath),
                    ':backup_type' => 'full'
                ]);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Backup created successfully!',
                        'filename' => $filename,
                        'file_size' => $this->formatFileSize(filesize($filepath))
                    ]);
                } else {
                    $_SESSION['success'] = 'Backup created successfully!';
                    header("Location: /backup");
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to create backup: ' . $e->getMessage()]);
                } else {
                    $_SESSION['errors'] = ['Failed to create backup: ' . $e->getMessage()];
                    header("Location: /backup");
                }
            }
        }
    }

    public function exportPDF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $userId = Session::get('user_id');
                $backupData = $this->generateBackupData($userId);
                
                // Generate PDF content
                $pdfContent = $this->generatePDFContent($backupData);
                $filename = 'backup_export_' . date('Y-m-d_H-i-s') . '.pdf';
                $filepath = $this->backupDir . $filename;
                
                // For now, we'll create a simple HTML file that can be printed as PDF
                // In production, you'd use a library like TCPDF or mPDF
                file_put_contents($filepath, $pdfContent);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => 'PDF export created successfully!',
                        'filename' => $filename,
                        'download_url' => '/backup/download/' . $filename
                    ]);
                } else {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    readfile($filepath);
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to export PDF: ' . $e->getMessage()]);
                } else {
                    $_SESSION['errors'] = ['Failed to export PDF: ' . $e->getMessage()];
                    header("Location: /backup");
                }
            }
        }
    }

    public function downloadBackup() {
        $filename = $_GET['file'] ?? '';
        if (empty($filename)) {
            http_response_code(400);
            echo "Invalid filename";
            return;
        }
        
        $filepath = $this->backupDir . $filename;
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo "File not found";
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Verify user owns this backup
        $stmt = $this->db->prepare("SELECT * FROM backup_history WHERE user_id = :user_id AND filename = :filename");
        $stmt->execute([':user_id' => $userId, ':filename' => $filename]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo "Access denied";
            return;
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
    }

    public function getBackupStatus() {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::get('user_id');
            
            // Get latest backup
            $stmt = $this->db->prepare("
                SELECT * FROM backup_history 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            $latestBackup = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get backup statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_backups,
                    SUM(file_size) as total_size,
                    MAX(created_at) as last_backup
                FROM backup_history 
                WHERE user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'latest_backup' => $latestBackup,
                    'statistics' => $stats,
                    'storage_used' => $this->getStorageUsed($userId),
                    'cloud_connections' => $this->getCloudConnections($userId)
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getBackupHistory() {
        header('Content-Type: application/json');
        
        try {
            $userId = Session::get('user_id');
            
            $stmt = $this->db->prepare("
                SELECT * FROM backup_history 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            $stmt->execute([':user_id' => $userId]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format file sizes
            foreach ($history as &$backup) {
                $backup['formatted_size'] = $this->formatFileSize($backup['file_size']);
            }
            
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function verifyBackup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $userId = Session::get('user_id');
                $filename = $_POST['filename'] ?? '';
                
                if (empty($filename)) {
                    throw new Exception('No backup file specified');
                }
                
                $filepath = $this->backupDir . $filename;
                if (!file_exists($filepath)) {
                    throw new Exception('Backup file not found');
                }
                
                $backupData = json_decode(file_get_contents($filepath), true);
                if (!$backupData) {
                    throw new Exception('Invalid backup file format');
                }
                
                $verification = $this->verifyBackupIntegrity($backupData, $userId);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'verification' => $verification
                    ]);
                } else {
                    $_SESSION['success'] = 'Backup verification completed!';
                    header("Location: /backup");
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['errors'] = [$e->getMessage()];
                    header("Location: /backup");
                }
            }
        }
    }

    public function connectCloudService() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $userId = Session::get('user_id');
                $service = $_POST['service'] ?? '';
                $credentials = $_POST['credentials'] ?? [];
                
                if (empty($service)) {
                    throw new Exception('No cloud service specified');
                }
                
                $result = $this->establishCloudConnection($service, $credentials, $userId);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => "Successfully connected to {$service}",
                        'connection_status' => $result
                    ]);
                } else {
                    $_SESSION['success'] = "Successfully connected to {$service}";
                    header("Location: /backup");
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['errors'] = [$e->getMessage()];
                    header("Location: /backup");
                }
            }
        }
    }

    public function updateBackupSettings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $userId = Session::get('user_id');
                $settings = $_POST['settings'] ?? [];
                
                // Store settings in database
                $stmt = $this->db->prepare("
                    INSERT INTO backup_settings (user_id, settings, updated_at) 
                    VALUES (:user_id, :settings, NOW())
                    ON DUPLICATE KEY UPDATE 
                    settings = :settings, updated_at = NOW()
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':settings' => json_encode($settings)
                ]);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Backup settings updated successfully!'
                    ]);
                } else {
                    $_SESSION['success'] = 'Backup settings updated successfully!';
                    header("Location: /backup");
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['errors'] = [$e->getMessage()];
                    header("Location: /backup");
                }
            }
        }
    }

    private function generateBackupData($userId) {
        $backupData = [
            'metadata' => [
                'created_at' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'version' => '1.0'
            ],
            'notes' => [],
            'tasks' => [],
            'tags' => [],
            'settings' => []
        ];
        
        // Get notes
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $backupData['notes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tasks
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $backupData['tasks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tags
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $backupData['tags'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get settings
        $stmt = $this->db->prepare("SELECT * FROM backup_settings WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings) {
            $backupData['settings'] = json_decode($settings['settings'], true);
        }
        
        return $backupData;
    }

    private function generatePDFContent($backupData) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Backup Export - ' . date('Y-m-d H:i:s') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 30px; page-break-inside: avoid; }
        .section h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .item { margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .metadata { background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SecureNote Pro - Backup Export</h1>
        <p>Generated on: ' . $backupData['metadata']['created_at'] . '</p>
    </div>
    
    <div class="metadata">
        <h3>Export Information</h3>
        <p><strong>Total Notes:</strong> ' . count($backupData['notes']) . '</p>
        <p><strong>Total Tasks:</strong> ' . count($backupData['tasks']) . '</p>
        <p><strong>Total Tags:</strong> ' . count($backupData['tags']) . '</p>
    </div>';
        
        // Add notes section
        if (!empty($backupData['notes'])) {
            $html .= '<div class="section">
                <h2>Notes</h2>';
            foreach ($backupData['notes'] as $note) {
                $html .= '<div class="item">
                    <h3>' . htmlspecialchars($note['title']) . '</h3>
                    <p><strong>Created:</strong> ' . $note['created_at'] . '</p>
                    <p><strong>Content:</strong> ' . htmlspecialchars(substr($note['content'], 0, 200)) . '...</p>
                </div>';
            }
            $html .= '</div>';
        }
        
        // Add tasks section
        if (!empty($backupData['tasks'])) {
            $html .= '<div class="section">
                <h2>Tasks</h2>';
            foreach ($backupData['tasks'] as $task) {
                $html .= '<div class="item">
                    <h3>' . htmlspecialchars($task['title']) . '</h3>
                    <p><strong>Status:</strong> ' . $task['status'] . '</p>
                    <p><strong>Priority:</strong> ' . $task['priority'] . '</p>
                    <p><strong>Description:</strong> ' . htmlspecialchars(substr($task['description'], 0, 200)) . '...</p>
                </div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }

    private function verifyBackupIntegrity($backupData, $userId) {
        $verification = [
            'is_valid' => true,
            'issues' => [],
            'statistics' => []
        ];
        
        // Check metadata
        if (!isset($backupData['metadata'])) {
            $verification['is_valid'] = false;
            $verification['issues'][] = 'Missing metadata section';
        }
        
        // Check data integrity
        $expectedTables = ['notes', 'tasks', 'tags'];
        foreach ($expectedTables as $table) {
            if (!isset($backupData[$table])) {
                $verification['issues'][] = "Missing {$table} section";
            } else {
                $verification['statistics'][$table] = count($backupData[$table]);
            }
        }
        
        // Check for data consistency
        if (isset($backupData['notes'])) {
            foreach ($backupData['notes'] as $note) {
                if (empty($note['title']) || empty($note['content'])) {
                    $verification['issues'][] = 'Note with missing title or content found';
                }
            }
        }
        
        return $verification;
    }

    private function establishCloudConnection($service, $credentials, $userId) {
        // Simulate cloud connection
        // In production, you'd implement actual OAuth flows
        
        $connectionData = [
            'service' => $service,
            'connected_at' => date('Y-m-d H:i:s'),
            'status' => 'connected',
            'user_id' => $userId
        ];
        
        // Store connection in database
        $stmt = $this->db->prepare("
            INSERT INTO cloud_connections (user_id, service, connection_data, created_at) 
            VALUES (:user_id, :service, :connection_data, NOW())
            ON DUPLICATE KEY UPDATE 
            connection_data = :connection_data, created_at = NOW()
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':service' => $service,
            ':connection_data' => json_encode($connectionData)
        ]);
        
        return $connectionData;
    }

    private function getStorageUsed($userId) {
        $stmt = $this->db->prepare("
            SELECT SUM(file_size) as total_size 
            FROM backup_history 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->formatFileSize($result['total_size'] ?? 0);
    }

    private function getCloudConnections($userId) {
        $stmt = $this->db->prepare("
            SELECT service, connection_data, created_at 
            FROM cloud_connections 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $connections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($connections as $connection) {
            $data = json_decode($connection['connection_data'], true);
            $result[$connection['service']] = [
                'status' => $data['status'] ?? 'disconnected',
                'connected_at' => $connection['created_at']
            ];
        }
        
        return $result;
    }

    private function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
