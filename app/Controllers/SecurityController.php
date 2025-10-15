<?php
namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use Core\Security;
use Core\Database;
use PDO;
use Exception;

class SecurityController {
    private $db;
    private $auditLogger;

    public function __construct(PDO $db, AuditLogsController $auditLogger) {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
    }

    public function index() {
        $userId = Session::get('user_id');
        
        try {
            // Get user security settings
            $user = $this->getUserSecurityInfo($userId);
            $sessions = $this->fetchActiveSessions($userId);
            $securityEvents = $this->getRecentSecurityEvents($userId);
            
            include __DIR__ . '/../Views/security.php';
        } catch (Exception $e) {
            error_log("Security page error: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading security page.";
        }
    }

    public function enable2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Generate 2FA secret
                $secret = Security::generate2FASecret();
                
                // Get user email
                $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    $this->handleAjaxResponse(false, 'User not found.', 404);
                }
                
                // Generate QR code URL
                $qrCodeUrl = Security::generate2FAQRCode($secret, $user['email']);
                
                // Store secret temporarily (not activated yet)
                $stmt = $this->db->prepare("UPDATE users SET two_factor_secret = ?, two_factor_enabled = 0 WHERE id = ?");
                $stmt->execute([$secret, $userId]);
                
                Security::logSecurityEvent($this->db, $userId, '2fa_setup_initiated', 'user', $userId);
                
                $this->handleAjaxResponse(true, '2FA setup initiated', 200, [
                    'secret' => $secret,
                    'qr_code_url' => $qrCodeUrl
                ]);
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to enable 2FA.', 500);
            }
        }
    }

    public function verify2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $code = $input['code'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Get user's 2FA secret
                $stmt = $this->db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user || !$user['two_factor_secret']) {
                    $this->handleAjaxResponse(false, '2FA not set up.', 400);
                }
                
                // Verify the code
                if (Security::verify2FACode($user['two_factor_secret'], $code)) {
                    // Enable 2FA
                    $stmt = $this->db->prepare("UPDATE users SET two_factor_enabled = 1 WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    // Generate backup codes
                    $backupCodes = Security::generateBackupCodes();
                    $this->storeBackupCodes($userId, $backupCodes);
                    
                    Security::logSecurityEvent($this->db, $userId, '2fa_enabled', 'user', $userId);
                    
                    $this->handleAjaxResponse(true, '2FA enabled successfully', 200, [
                        'backup_codes' => $backupCodes
                    ]);
                } else {
                    Security::logSecurityEvent($this->db, $userId, '2fa_verification_failed', 'user', $userId);
                    $this->handleAjaxResponse(false, 'Invalid verification code.', 400);
                }
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to verify 2FA.', 500);
            }
        }
    }

    public function disable2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $code = $input['code'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Get user's 2FA secret
                $stmt = $this->db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user || !$user['two_factor_secret']) {
                    $this->handleAjaxResponse(false, '2FA not enabled.', 400);
                }
                
                // Verify the code
                if (Security::verify2FACode($user['two_factor_secret'], $code)) {
                    // Disable 2FA
                    $stmt = $this->db->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    // Remove backup codes
                    $this->removeBackupCodes($userId);
                    
                    Security::logSecurityEvent($this->db, $userId, '2fa_disabled', 'user', $userId);
                    
                    $this->handleAjaxResponse(true, '2FA disabled successfully');
                } else {
                    Security::logSecurityEvent($this->db, $userId, '2fa_disable_verification_failed', 'user', $userId);
                    $this->handleAjaxResponse(false, 'Invalid verification code.', 400);
                }
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to disable 2FA.', 500);
            }
        }
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Validate passwords
                if ($newPassword !== $confirmPassword) {
                    $this->handleAjaxResponse(false, 'New passwords do not match.', 400);
                }
                
                $passwordErrors = Security::validatePasswordStrength($newPassword);
                if (!empty($passwordErrors)) {
                    $this->handleAjaxResponse(false, 'Password does not meet requirements: ' . implode(', ', $passwordErrors), 400);
                }
                
                // Get current password hash
                $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    $this->handleAjaxResponse(false, 'User not found.', 404);
                }
                
                // Verify current password
                if (!Security::verifyPassword($currentPassword, $user['password'])) {
                    Security::logSecurityEvent($this->db, $userId, 'password_change_failed', 'user', $userId);
                    $this->handleAjaxResponse(false, 'Current password is incorrect.', 400);
                }
                
                // Hash new password
                $newPasswordHash = Security::hashPassword($newPassword);
                
                // Update password
                $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newPasswordHash, $userId]);
                
                Security::logSecurityEvent($this->db, $userId, 'password_changed', 'user', $userId);
                
                $this->handleAjaxResponse(true, 'Password changed successfully');
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to change password.', 500);
            }
        }
    }

    public function getActiveSessions() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = Session::get('user_id');
            
            try {
                $sessions = $this->fetchActiveSessions($userId);
                $this->handleAjaxResponse(true, 'Sessions retrieved', 200, ['sessions' => $sessions]);
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to retrieve sessions.', 500);
            }
        }
    }

    public function terminateSession() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $sessionId = $input['session_id'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Terminate specific session
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE id = ? AND user_id = ?");
                $stmt->execute([$sessionId, $userId]);
                
                Security::logSecurityEvent($this->db, $userId, 'session_terminated', 'session', $sessionId);
                
                $this->handleAjaxResponse(true, 'Session terminated successfully');
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to terminate session.', 500);
            }
        }
    }

    public function terminateAllSessions() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Terminate all sessions except current
                $currentSessionId = session_id();
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id != ?");
                $stmt->execute([$userId, $currentSessionId]);
                
                Security::logSecurityEvent($this->db, $userId, 'all_sessions_terminated', 'session', null);
                
                $this->handleAjaxResponse(true, 'All sessions terminated successfully');
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to terminate sessions.', 500);
            }
        }
    }

    public function generateBackupCodes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $input = json_decode(file_get_contents('php://input'), true);
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!CSRF::verify($csrfToken)) {
                $this->handleAjaxResponse(false, 'Invalid CSRF token.', 403);
            }
            
            try {
                // Check if 2FA is enabled
                $stmt = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user || !$user['two_factor_enabled']) {
                    $this->handleAjaxResponse(false, '2FA is not enabled.', 400);
                }
                
                // Generate new backup codes
                $backupCodes = Security::generateBackupCodes();
                $this->storeBackupCodes($userId, $backupCodes);
                
                Security::logSecurityEvent($this->db, $userId, 'backup_codes_generated', 'user', $userId);
                
                $this->handleAjaxResponse(true, 'Backup codes generated successfully', 200, [
                    'backup_codes' => $backupCodes
                ]);
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to generate backup codes.', 500);
            }
        }
    }

    public function getSecurityEvents() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $userId = Session::get('user_id');
            $page = (int) ($_GET['page'] ?? 1);
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            try {
                $events = $this->getRecentSecurityEvents($userId, $limit, $offset);
                $total = $this->getSecurityEventsCount($userId);
                
                $this->handleAjaxResponse(true, 'Security events retrieved', 200, [
                    'events' => $events,
                    'total' => $total,
                    'page' => $page,
                    'total_pages' => ceil($total / $limit)
                ]);
                
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to retrieve security events.', 500);
            }
        }
    }

    private function getUserSecurityInfo($userId) {
        $stmt = $this->db->prepare("
            SELECT id, email, two_factor_enabled, two_factor_secret, 
                   last_login, created_at, updated_at
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchActiveSessions($userId) {
        $stmt = $this->db->prepare("
            SELECT id, ip_address, user_agent, last_activity, created_at
            FROM user_sessions 
            WHERE user_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    private function getRecentSecurityEvents($userId, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT action, ip_address, user_agent, created_at, metadata
            FROM audit_logs 
            WHERE user_id = ? AND resource_type = 'security'
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, (int)$limit, (int)$offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSecurityEventsCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM audit_logs 
            WHERE user_id = ? AND resource_type = 'security'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    private function storeBackupCodes($userId, $codes) {
        // Store backup codes as JSON in users table
        $hashedCodes = Security::hashBackupCodes($codes);
        $stmt = $this->db->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
        $stmt->execute([$hashedCodes, $userId]);
    }

    private function removeBackupCodes($userId) {
        $stmt = $this->db->prepare("UPDATE users SET backup_codes = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    private function handleAjaxResponse($success, $message, $httpCode = 200, $data = null) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
}
