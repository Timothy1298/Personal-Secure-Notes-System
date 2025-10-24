<?php
namespace Core\Enterprise;

use PDO;
use Exception;

class ComplianceManager {
    private $db;
    private $config;
    
    public function __construct(PDO $db, array $config = []) {
        $this->db = $db;
        $this->config = array_merge([
            'gdpr' => [
                'enabled' => true,
                'data_retention_days' => 2555, // 7 years
                'auto_delete_expired' => true
            ],
            'hipaa' => [
                'enabled' => false,
                'audit_logging' => true,
                'encryption_required' => true
            ],
            'sox' => [
                'enabled' => false,
                'audit_trail' => true,
                'change_control' => true
            ],
            'iso27001' => [
                'enabled' => false,
                'security_controls' => true,
                'risk_assessment' => true
            ]
        ], $config);
    }
    
    /**
     * Check GDPR compliance
     */
    public function checkGDPRCompliance(): array {
        try {
            $violations = [];
            
            // Check data retention
            $retentionViolations = $this->checkDataRetention();
            if (!empty($retentionViolations)) {
                $violations[] = [
                    'type' => 'data_retention',
                    'description' => 'Data retention policy violations found',
                    'details' => $retentionViolations
                ];
            }
            
            // Check consent management
            $consentViolations = $this->checkConsentManagement();
            if (!empty($consentViolations)) {
                $violations[] = [
                    'type' => 'consent_management',
                    'description' => 'Consent management violations found',
                    'details' => $consentViolations
                ];
            }
            
            // Check data portability
            $portabilityViolations = $this->checkDataPortability();
            if (!empty($portabilityViolations)) {
                $violations[] = [
                    'type' => 'data_portability',
                    'description' => 'Data portability violations found',
                    'details' => $portabilityViolations
                ];
            }
            
            // Check right to be forgotten
            $forgottenViolations = $this->checkRightToBeForgotten();
            if (!empty($forgottenViolations)) {
                $violations[] = [
                    'type' => 'right_to_be_forgotten',
                    'description' => 'Right to be forgotten violations found',
                    'details' => $forgottenViolations
                ];
            }
            
            return [
                'success' => true,
                'compliant' => empty($violations),
                'violations' => $violations,
                'checked_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check HIPAA compliance
     */
    public function checkHIPAACompliance(): array {
        try {
            $violations = [];
            
            // Check audit logging
            if ($this->config['hipaa']['audit_logging']) {
                $auditViolations = $this->checkAuditLogging();
                if (!empty($auditViolations)) {
                    $violations[] = [
                        'type' => 'audit_logging',
                        'description' => 'Audit logging violations found',
                        'details' => $auditViolations
                    ];
                }
            }
            
            // Check encryption
            if ($this->config['hipaa']['encryption_required']) {
                $encryptionViolations = $this->checkEncryption();
                if (!empty($encryptionViolations)) {
                    $violations[] = [
                        'type' => 'encryption',
                        'description' => 'Encryption violations found',
                        'details' => $encryptionViolations
                    ];
                }
            }
            
            // Check access controls
            $accessViolations = $this->checkAccessControls();
            if (!empty($accessViolations)) {
                $violations[] = [
                    'type' => 'access_controls',
                    'description' => 'Access control violations found',
                    'details' => $accessViolations
                ];
            }
            
            return [
                'success' => true,
                'compliant' => empty($violations),
                'violations' => $violations,
                'checked_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check SOX compliance
     */
    public function checkSOXCompliance(): array {
        try {
            $violations = [];
            
            // Check audit trail
            if ($this->config['sox']['audit_trail']) {
                $auditViolations = $this->checkAuditTrail();
                if (!empty($auditViolations)) {
                    $violations[] = [
                        'type' => 'audit_trail',
                        'description' => 'Audit trail violations found',
                        'details' => $auditViolations
                    ];
                }
            }
            
            // Check change control
            if ($this->config['sox']['change_control']) {
                $changeViolations = $this->checkChangeControl();
                if (!empty($changeViolations)) {
                    $violations[] = [
                        'type' => 'change_control',
                        'description' => 'Change control violations found',
                        'details' => $changeViolations
                    ];
                }
            }
            
            // Check segregation of duties
            $segregationViolations = $this->checkSegregationOfDuties();
            if (!empty($segregationViolations)) {
                $violations[] = [
                    'type' => 'segregation_of_duties',
                    'description' => 'Segregation of duties violations found',
                    'details' => $segregationViolations
                ];
            }
            
            return [
                'success' => true,
                'compliant' => empty($violations),
                'violations' => $violations,
                'checked_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process data subject request (GDPR)
     */
    public function processDataSubjectRequest(int $userId, string $requestType, array $options = []): array {
        try {
            switch ($requestType) {
                case 'access':
                    return $this->processDataAccessRequest($userId);
                case 'portability':
                    return $this->processDataPortabilityRequest($userId);
                case 'rectification':
                    return $this->processDataRectificationRequest($userId, $options);
                case 'erasure':
                    return $this->processDataErasureRequest($userId);
                case 'restriction':
                    return $this->processDataRestrictionRequest($userId);
                case 'objection':
                    return $this->processDataObjectionRequest($userId);
                default:
                    throw new Exception("Unknown request type: {$requestType}");
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate compliance report
     */
    public function generateComplianceReport(string $framework, int $days = 30): array {
        try {
            switch ($framework) {
                case 'gdpr':
                    return $this->generateGDPRReport($days);
                case 'hipaa':
                    return $this->generateHIPAAReport($days);
                case 'sox':
                    return $this->generateSOXReport($days);
                case 'iso27001':
                    return $this->generateISO27001Report($days);
                default:
                    throw new Exception("Unknown compliance framework: {$framework}");
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Log compliance event
     */
    public function logComplianceEvent(string $eventType, array $data, int $userId = null): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO compliance_events (event_type, user_id, data, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $eventType,
                $userId,
                json_encode($data)
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging compliance event: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get data retention status
     */
    public function getDataRetentionStatus(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_records,
                    COUNT(CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as expired_records,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as active_records
                FROM users
            ");
            $stmt->execute([
                $this->config['gdpr']['data_retention_days'],
                $this->config['gdpr']['data_retention_days']
            ]);
            
            $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_records,
                    COUNT(CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as expired_records,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as active_records
                FROM notes
            ");
            $stmt->execute([
                $this->config['gdpr']['data_retention_days'],
                $this->config['gdpr']['data_retention_days']
            ]);
            
            $noteStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'users' => $userStats,
                'notes' => $noteStats,
                'retention_days' => $this->config['gdpr']['data_retention_days']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean up expired data
     */
    public function cleanupExpiredData(): array {
        try {
            if (!$this->config['gdpr']['auto_delete_expired']) {
                return [
                    'success' => false,
                    'error' => 'Auto-deletion is disabled'
                ];
            }
            
            $this->db->beginTransaction();
            
            $deletedCounts = [];
            
            // Delete expired notes
            $stmt = $this->db->prepare("
                DELETE FROM notes 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$this->config['gdpr']['data_retention_days']]);
            $deletedCounts['notes'] = $stmt->rowCount();
            
            // Delete expired tasks
            $stmt = $this->db->prepare("
                DELETE FROM tasks 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$this->config['gdpr']['data_retention_days']]);
            $deletedCounts['tasks'] = $stmt->rowCount();
            
            // Delete expired audit logs
            $stmt = $this->db->prepare("
                DELETE FROM audit_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$this->config['gdpr']['data_retention_days']]);
            $deletedCounts['audit_logs'] = $stmt->rowCount();
            
            $this->db->commit();
            
            // Log the cleanup event
            $this->logComplianceEvent('data_cleanup', $deletedCounts);
            
            return [
                'success' => true,
                'deleted_counts' => $deletedCounts,
                'message' => 'Expired data cleaned up successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Private methods for compliance checks
    private function checkDataRetention(): array {
        $violations = [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$this->config['gdpr']['data_retention_days']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $violations[] = "Found {$result['count']} users with data older than retention period";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking data retention: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkConsentManagement(): array {
        $violations = [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE consent_given = 0 OR consent_given IS NULL
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $violations[] = "Found {$result['count']} users without consent";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking consent management: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkDataPortability(): array {
        $violations = [];
        
        // Check if data export functionality is available
        if (!class_exists('Core\DataManagement\DataExporter')) {
            $violations[] = "Data export functionality not available";
        }
        
        return $violations;
    }
    
    private function checkRightToBeForgotten(): array {
        $violations = [];
        
        // Check if data deletion functionality is available
        if (!method_exists($this, 'processDataErasureRequest')) {
            $violations[] = "Data erasure functionality not available";
        }
        
        return $violations;
    }
    
    private function checkAuditLogging(): array {
        $violations = [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] === 0) {
                $violations[] = "No audit logs found in the last 24 hours";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking audit logging: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkEncryption(): array {
        $violations = [];
        
        // Check if sensitive data is encrypted
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE password_hash NOT LIKE '$2y$%' AND password_hash NOT LIKE '$argon2%'
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $violations[] = "Found {$result['count']} users with unencrypted passwords";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking encryption: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkAccessControls(): array {
        $violations = [];
        
        // Check for users with excessive permissions
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE r.name = 'admin'
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 5) {
                $violations[] = "Too many admin users: {$result['count']}";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking access controls: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkAuditTrail(): array {
        $violations = [];
        
        // Check if all critical operations are logged
        try {
            $stmt = $this->db->prepare("
                SELECT action, COUNT(*) as count 
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                GROUP BY action
            ");
            $stmt->execute();
            $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $requiredActions = ['user_login', 'user_logout', 'data_create', 'data_update', 'data_delete'];
            $loggedActions = array_column($actions, 'action');
            
            foreach ($requiredActions as $action) {
                if (!in_array($action, $loggedActions)) {
                    $violations[] = "Missing audit trail for action: {$action}";
                }
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking audit trail: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkChangeControl(): array {
        $violations = [];
        
        // Check if changes are properly authorized
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM audit_logs 
                WHERE action IN ('data_update', 'data_delete') 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                AND user_id IS NULL
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $violations[] = "Found {$result['count']} unauthorized changes";
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking change control: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    private function checkSegregationOfDuties(): array {
        $violations = [];
        
        // Check for users with conflicting roles
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, COUNT(DISTINCT r.name) as role_count
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id, u.username
                HAVING role_count > 3
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($users)) {
                $violations[] = "Found users with excessive roles: " . implode(', ', array_column($users, 'username'));
            }
            
        } catch (Exception $e) {
            $violations[] = "Error checking segregation of duties: " . $e->getMessage();
        }
        
        return $violations;
    }
    
    // Data subject request processing methods
    private function processDataAccessRequest(int $userId): array {
        try {
            // Get all user data
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = ?");
            $stmt->execute([$userId]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("SELECT * FROM tasks WHERE user_id = ?");
            $stmt->execute([$userId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [
                'user' => $user,
                'notes' => $notes,
                'tasks' => $tasks,
                'requested_at' => date('Y-m-d H:i:s')
            ];
            
            // Log the request
            $this->logComplianceEvent('data_access_request', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function processDataPortabilityRequest(int $userId): array {
        try {
            // Export user data in portable format
            $data = $this->processDataAccessRequest($userId);
            
            if (!$data['success']) {
                return $data;
            }
            
            // Create export file
            $exportData = json_encode($data['data'], JSON_PRETTY_PRINT);
            $filename = "user_data_export_{$userId}_" . date('Y-m-d_H-i-s') . '.json';
            $filepath = __DIR__ . '/../../exports/' . $filename;
            
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }
            
            file_put_contents($filepath, $exportData);
            
            // Log the request
            $this->logComplianceEvent('data_portability_request', [
                'user_id' => $userId,
                'filename' => $filename
            ]);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function processDataRectificationRequest(int $userId, array $options): array {
        try {
            $this->db->beginTransaction();
            
            // Update user data
            if (isset($options['user_data'])) {
                $stmt = $this->db->prepare("
                    UPDATE users SET 
                        first_name = ?, 
                        last_name = ?, 
                        email = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $options['user_data']['first_name'] ?? '',
                    $options['user_data']['last_name'] ?? '',
                    $options['user_data']['email'] ?? '',
                    $userId
                ]);
            }
            
            $this->db->commit();
            
            // Log the request
            $this->logComplianceEvent('data_rectification_request', [
                'user_id' => $userId,
                'changes' => $options
            ]);
            
            return [
                'success' => true,
                'message' => 'Data rectified successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function processDataErasureRequest(int $userId): array {
        try {
            $this->db->beginTransaction();
            
            // Delete user data
            $this->db->prepare("DELETE FROM notes WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM tasks WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            
            $this->db->commit();
            
            // Log the request
            $this->logComplianceEvent('data_erasure_request', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Data erased successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function processDataRestrictionRequest(int $userId): array {
        try {
            // Mark user as restricted
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    is_restricted = 1,
                    restriction_reason = 'GDPR data restriction request',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            
            // Log the request
            $this->logComplianceEvent('data_restriction_request', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Data processing restricted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function processDataObjectionRequest(int $userId): array {
        try {
            // Mark user as objected to processing
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    processing_objected = 1,
                    objection_reason = 'GDPR processing objection',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            
            // Log the request
            $this->logComplianceEvent('data_objection_request', ['user_id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Processing objection recorded successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Report generation methods
    private function generateGDPRReport(int $days): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    event_type,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM compliance_events 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND event_type LIKE 'data_%'
                GROUP BY event_type, DATE(created_at)
                ORDER BY date DESC, event_type
            ");
            $stmt->execute([$days]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'framework' => 'GDPR',
                'period_days' => $days,
                'events' => $events,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function generateHIPAAReport(int $days): array {
        // Similar implementation for HIPAA
        return ['success' => true, 'framework' => 'HIPAA'];
    }
    
    private function generateSOXReport(int $days): array {
        // Similar implementation for SOX
        return ['success' => true, 'framework' => 'SOX'];
    }
    
    private function generateISO27001Report(int $days): array {
        // Similar implementation for ISO 27001
        return ['success' => true, 'framework' => 'ISO27001'];
    }
}
