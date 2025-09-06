<?php
namespace App\Controllers;

use Core\Session;
use PDO;
use Exception;

class SettingsController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Helper function to handle AJAX responses.
     * @param bool $success
     * @param string $message
     * @param int $httpCode
     */
    private function handleAjaxResponse($success, $message, $httpCode = 200) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    public function index() {
        // This method could be used to fetch and pre-populate existing user settings.
        // For now, it just loads the view.
        // In a real application, you would fetch settings from the database here.
        
        $userId = Session::get('user_id');

        // Fetch user settings from the database
        // $stmt = $this->db->prepare("SELECT * FROM user_settings WHERE user_id = :uid");
        // $stmt->execute([':uid' => $userId]);
        // $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/settings.php';
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';

            // TODO: Add logic to verify current password and update with new hashed password
            // For now, assume a successful update.

            if (empty($currentPassword) || empty($newPassword)) {
                $this->handleAjaxResponse(false, 'Passwords cannot be empty.', 400);
            }

            // Example:
            // $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :uid");
            // $stmt->execute([':uid' => $userId]);
            // $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // if (!password_verify($currentPassword, $user['password'])) {
            //     $this->handleAjaxResponse(false, 'Incorrect current password.', 401);
            // }

            // $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // $updateStmt = $this->db->prepare("UPDATE users SET password = :new_pass WHERE id = :uid");
            // $updateStmt->execute([':new_pass' => $newHashedPassword, ':uid' => $userId]);

            $this->handleAjaxResponse(true, 'Password updated successfully!');
        }
    }

    public function update2fa() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enable2fa = $data['enable_2fa'] ?? false;

            try {
                // TODO: Store the 2FA setting in the database for the user.
                // Example: UPDATE users SET is_2fa_enabled = :status WHERE id = :uid
                $this->handleAjaxResponse(true, '2FA setting updated.');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update 2FA setting.', 500);
            }
        }
    }

    public function exportData() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');

            try {
                // TODO: Add logic to export user data (e.g., notes and tasks) to a file.
                // This would likely involve fetching data and writing it to a temporary file.

                // Example:
                // $notes = $this->db->prepare("SELECT * FROM notes WHERE user_id = ?")->execute([$userId])->fetchAll();
                // $exportFilePath = '/path/to/exports/user_' . $userId . '.json';
                // file_put_contents($exportFilePath, json_encode(['notes' => $notes]));
                
                $this->handleAjaxResponse(true, 'Data export started. Your download will begin shortly.', 200);

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to export data.', 500);
            }
        }
    }
    
    // Note: Data Import would require a separate function with a file upload handler.

    public function deleteAccount() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            try {
                // TODO: Add logic to delete all user data from the database.
                // This is a critical action, so implement carefully.
                // Example: DELETE FROM users WHERE id = :uid;
                //          DELETE FROM notes WHERE user_id = :uid;

                // Log the action for auditing
                // $this->auditLogger->logEvent($userId, 'account deleted');

                // Clear the session
                Session::destroy();

                $this->handleAjaxResponse(true, 'Account successfully deleted.');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to delete account.', 500);
            }
        }
    }

    public function updateTheme() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $theme = $data['theme'] ?? 'light';

            try {
                // TODO: Store the user's preferred theme in the database.
                // Example: UPDATE user_settings SET theme = :theme WHERE user_id = :uid
                $this->handleAjaxResponse(true, 'Theme updated successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update theme.', 500);
            }
        }
    }

    public function updateFontSize() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $fontSize = $data['font_size'] ?? '16px';

            try {
                // TODO: Store the font size setting.
                $this->handleAjaxResponse(true, 'Font size updated successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update font size.', 500);
            }
        }
    }

    public function updateNoteLayout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $layout = $data['layout'] ?? 'grid';

            try {
                // TODO: Store the note layout setting.
                $this->handleAjaxResponse(true, 'Note layout updated successfully!');

            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update note layout.', 500);
            }
        }
    }

    public function updateDefaultNoteState() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $defaultState = $data['default_state'] ?? 'active';

            try {
                // TODO: Store the default note state setting.
                $this->handleAjaxResponse(true, 'Default note state updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update default note state.', 500);
            }
        }
    }

    public function updateDefaultTags() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $defaultTags = $data['default_tags'] ?? [];

            try {
                // TODO: Store the default tags setting.
                // You might need to serialize the array or store in a separate table.
                $this->handleAjaxResponse(true, 'Default tags updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update default tags.', 500);
            }
        }
    }

    public function updateAutoArchive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enabled = $data['enabled'] ?? false;

            try {
                // TODO: Store the auto-archive setting.
                $this->handleAjaxResponse(true, 'Automatic archiving setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update auto-archive setting.', 500);
            }
        }
    }

    public function updateAutoEmptyTrash() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enabled = $data['enabled'] ?? false;

            try {
                // TODO: Store the auto-empty-trash setting.
                $this->handleAjaxResponse(true, 'Automatic trash emptying setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update auto-empty-trash setting.', 500);
            }
        }
    }
    
    public function updateEmailNotifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enabled = $data['enabled'] ?? false;

            try {
                // TODO: Store the email notifications setting.
                $this->handleAjaxResponse(true, 'Email notifications setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update email notifications setting.', 500);
            }
        }
    }
    
    public function updateDesktopNotifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enabled = $data['enabled'] ?? false;

            try {
                // TODO: Store the desktop notifications setting.
                $this->handleAjaxResponse(true, 'Desktop notifications setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update desktop notifications setting.', 500);
            }
        }
    }

    public function updateReminderAlerts() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $enabled = $data['enabled'] ?? false;

            try {
                // TODO: Store the reminder alerts setting.
                $this->handleAjaxResponse(true, 'Reminder alerts setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update reminder alerts setting.', 500);
            }
        }
    }

    public function updateLogRetention() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = Session::get('user_id');
            $retentionPeriod = $data['retention_period'] ?? '90';

            try {
                // TODO: Store the audit log retention setting.
                $this->handleAjaxResponse(true, 'Audit log retention setting updated.');
            } catch (Exception $e) {
                $this->handleAjaxResponse(false, 'Failed to update audit log retention setting.', 500);
            }
        }
    }
}