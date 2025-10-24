<?php
require __DIR__ . '/vendor/autoload.php';

use Core\Database;
use Core\Security;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database connection
$db = Database::getInstance();

// Check if username is provided as command line argument
if ($argc < 2) {
    echo "Usage: php admin_reset_password.php <username> [new_password]\n";
    echo "Example: php admin_reset_password.php timothy MyNewPassword123!\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2] ?? 'TempPassword123!';

try {
    // Find user
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ User '{$username}' not found!\n";
        exit(1);
    }
    
    echo "✅ Found user: {$user['username']} ({$user['email']})\n";
    
    // Hash new password
    $hashedPassword = Security::hashPassword($newPassword);
    
    // Update password
    $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);
    
    echo "✅ Password updated successfully!\n";
    echo "📧 Email: {$user['email']}\n";
    echo "👤 Username: {$user['username']}\n";
    echo "🔑 New Password: {$newPassword}\n";
    echo "\n🌐 You can now login at: http://localhost:8000/login\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
