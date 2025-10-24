<?php
namespace App\Controllers;

use Core\Database;
use Core\Security;
use Core\Session;
use App\Models\User;
use Exception;
use PDO;

class PasswordResetController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Show password reset form
     */
    public function showResetForm() {
        include __DIR__ . '/../Views/password_reset.php';
    }

    /**
     * Process password reset request
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /password-reset");
            exit;
        }

        $email = $_POST['email'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All fields are required.";
            include __DIR__ . '/../Views/password_reset.php';
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
            include __DIR__ . '/../Views/password_reset.php';
            return;
        }

        // Validate password strength
        $passwordValidation = Security::validatePasswordStrength($newPassword);
        if (!$passwordValidation['valid']) {
            $error = "Password does not meet requirements: " . implode(', ', $passwordValidation['errors']);
            include __DIR__ . '/../Views/password_reset.php';
            return;
        }

        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "No account found with that email address.";
                include __DIR__ . '/../Views/password_reset.php';
                return;
            }

            // Hash the new password
            $hashedPassword = Security::hashPassword($newPassword);

            // Update password in database
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);

            // Log the password reset
            Security::logSecurityEvent($this->db, $user['id'], 'password_reset', 'user', $user['id'], [
                'email' => $email,
                'ip_address' => Security::getClientIP()
            ]);

            $success = "Password has been successfully reset! You can now login with your new password.";
            include __DIR__ . '/../Views/password_reset.php';

        } catch (Exception $e) {
            $error = "An error occurred while resetting your password. Please try again.";
            include __DIR__ . '/../Views/password_reset.php';
        }
    }

    /**
     * Direct password reset for admin use (bypasses email verification)
     */
    public function directReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /password-reset");
            exit;
        }

        $username = $_POST['username'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($username) || empty($newPassword)) {
            $error = "Username and new password are required.";
            include __DIR__ . '/../Views/password_reset.php';
            return;
        }

        try {
            // Find user by username
            $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "User not found.";
                include __DIR__ . '/../Views/password_reset.php';
                return;
            }

            // Hash the new password
            $hashedPassword = Security::hashPassword($newPassword);

            // Update password
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);

            $success = "Password for user '{$user['username']}' has been successfully reset!";
            include __DIR__ . '/../Views/password_reset.php';

        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            include __DIR__ . '/../Views/password_reset.php';
        }
    }

    /**
     * API endpoint for password reset
     */
    public function apiResetPassword() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($email) || empty($newPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and new password are required']);
            exit;
        }

        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            // Hash the new password
            $hashedPassword = Security::hashPassword($newPassword);

            // Update password
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);

            // Log the password reset
            Security::logSecurityEvent($this->db, $user['id'], 'password_reset', 'user', $user['id'], [
                'email' => $email,
                'ip_address' => Security::getClientIP()
            ]);

            echo json_encode([
                'success' => true, 
                'message' => 'Password reset successfully',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
}