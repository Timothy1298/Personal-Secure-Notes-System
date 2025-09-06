<?php
namespace App\Controllers;

use App\Models\User;
use Core\Database;

class PasswordResetController {

    public function requestForm() {
        include __DIR__ . '/../Views/password_request.php';
    }

    public function sendResetLink() {
        $email = trim($_POST['email']);
        $user = User::findByEmailOrUsername($email);

        // Security: Do not reveal if the email exists to prevent user enumeration
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :exp)");
            $stmt->execute([
                ':uid' => $user['id'],
                ':token' => $token,
                ':exp' => $expires
            ]);

            $resetLink = "http://localhost:3000/password-reset-form?token=$token";
            // In a real app, use a proper email library
            mail($user['email'], "Password Reset", "Reset your password: $resetLink");
        }

        $_SESSION['success'] = "If an account with that email exists, a password reset link has been sent.";
        header("Location: /login");
        exit;
    }

    public function resetForm() {
        $token = $_GET['token'] ?? '';
        include __DIR__ . '/../Views/password_reset.php';
    }

    public function reset() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $_SESSION['errors'] = ["Passwords do not match"];
            header("Location: /password-reset-form?token=$token");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $_SESSION['errors'] = ["Invalid or expired token"];
            header("Location: /password-reset");
            exit;
        }

        $stmt = $db->prepare("UPDATE users SET password_hash = :pw WHERE id = :uid");
        $stmt->execute([
            ':pw' => password_hash($password, PASSWORD_DEFAULT),
            ':uid' => $reset['user_id']
        ]);

        $stmt = $db->prepare("DELETE FROM password_resets WHERE id = :id");
        $stmt->execute([':id' => $reset['id']]);

        $_SESSION['success'] = "Password reset successful! Please login.";
        header("Location: /login");
        exit;
    }
}