<?php
namespace App\Controllers;

use App\Models\User;
use Core\Session;
use Core\Logger;
use Core\CSRF;
use Core\Security;
use Core\TwoFactorAuth;
use Core\EmailService;
use Core\Provider; // Assuming you have a Provider class for social APIs

// Ensure Security class is loaded
require_once __DIR__ . '/../../core/Security.php';

// Social Login Providers Configuration
// Real OAuth credentials from environment variables
$socialProviders = [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '1056131269048-i5ibcufnobb547cppbjd96b7c5i39efr.apps.googleusercontent.com',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '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',
        'redirect_uri' => 'http://localhost:3000/social/google/callback',
        'scope' => 'email profile',
    ],
    'github' => [
        'client_id' => $_ENV['GITHUB_CLIENT_ID'] ?? 'Ov23liE0G2KEfFRMJhk3',
        'client_secret' => $_ENV['GITHUB_CLIENT_SECRET'] ?? 'f4471951f42d8b2129c4718cf9dbf23464637c0c',
        'redirect_uri' => 'http://localhost:3000/social/github/callback',
        'scope' => 'user:email',
    ],
    'facebook' => [
        'client_id' => 'YOUR_FACEBOOK_CLIENT_ID',
        'client_secret' => 'YOUR_FACEBOOK_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost:3000/social/facebook/callback',
        'scope' => 'email public_profile',
    ],
];

// ---
// Route Handling
// This section handles all incoming requests and directs them to the correct controller method.
// ---

// Get the clean request path and break it into segments for robust routing
$requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = explode('/', $requestPath);

// Existing password reset routes
if ($requestPath === 'password-reset' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $pwdReset = new \App\Controllers\AuthController();
    $pwdReset->showPasswordReset();
    exit;
}

if ($requestPath === 'password-reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwdReset = new \App\Controllers\AuthController();
    $pwdReset->passwordResetRequest();
    exit;
}

// Social login routes
if (isset($segments[0]) && $segments[0] === 'social' && isset($segments[1])) {
    $provider = $segments[1];
    $auth = new \App\Controllers\AuthController();
    
    // Check if the provider is valid before attempting to redirect
    if (in_array($provider, ['google', 'github', 'facebook'])) {
        // Check if this is the callback route
        if (isset($segments[2]) && $segments[2] === 'callback') {
            $auth->handleProviderCallback($provider);
        } else {
            $auth->redirectToProvider($provider);
        }
    }
    // If the provider isn't valid, the script will simply continue
    // and your main routing system should handle the 404.
    exit;
}

// ---
// AuthController Class
// This class contains all the methods for handling user authentication.
// ---

class AuthController {
    
    // Properties to store provider config
    private $providers;
    private $db;

    public function __construct() {
        global $socialProviders;
        $this->providers = $socialProviders;
        $this->db = \Core\Database::getInstance();
    }

    // Displays the registration form
    public function showRegister() {
        include __DIR__ . '/../Views/register.php';
    }

    // Handles user registration from the form
    public function register() {
        // CSRF Protection
        if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ["Invalid request. Please try again."];
            header("Location: /register");
            exit;
        }

        // Rate Limiting
        $ip = Security::getClientIP();
        if (!Security::checkRateLimit($this->db, $ip, 5, 300)) { // 5 attempts per 5 minutes
            $_SESSION['errors'] = ["Too many registration attempts. Please try again later."];
            header("Location: /register");
            exit;
        }

        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $firstName = Security::sanitizeInput($_POST['first_name'] ?? '');
        $lastName = Security::sanitizeInput($_POST['last_name'] ?? '');

        // Enhanced Validation
        $errors = [];
        
        if (strlen($username) < 4) $errors[] = "Username must be at least 4 characters.";
        if (strlen($username) > 50) $errors[] = "Username must be less than 50 characters.";
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username can only contain letters, numbers, and underscores.";
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        
        $passwordErrors = Security::validatePasswordStrength($password);
        $errors = array_merge($errors, $passwordErrors);
        
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        if (User::findByEmailOrUsername($email) || User::findByEmailOrUsername($username)) {
            $errors[] = "Email or Username already taken.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /register");
            exit;
        }
        

        // Create user with enhanced data
        $userId = User::create($username, $email, $password, $firstName, $lastName);
        
        if ($userId) {
            // Send welcome email
            EmailService::sendWelcomeEmail($email, $username);
            
            // Send email verification
            $verificationToken = Security::generateToken();
            User::createEmailVerification($userId, $verificationToken);
            EmailService::sendEmailVerification($email, $username, $verificationToken);
            
            Security::logSecurityEvent($this->db, $userId, 'user_registered', 'user', $userId, [
                'email' => $email,
                'username' => $username
            ]);
            
            $_SESSION['success'] = "Account created successfully! Please check your email to verify your account.";
        } else {
            $_SESSION['errors'] = ["Failed to create account. Please try again."];
        }
        
        header("Location: /login");
        exit;
    }

    // Displays the login form
    public function showLogin() {
        include __DIR__ . '/../Views/login.php';
    }

    // Handles user login from the form
    public function login() {
        // CSRF Protection
        if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ["Invalid request. Please try again."];
            header("Location: /login");
            exit;
        }

        // Rate Limiting
        $ip = Security::getClientIP();
        if (!Security::checkRateLimit($this->db, $ip, 10, 300)) { // 10 attempts per 5 minutes
            $_SESSION['errors'] = ["Too many login attempts. Please try again later."];
            header("Location: /login");
            exit;
        }

        $identifier = Security::sanitizeInput($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        $user = User::findByEmailOrUsername($identifier);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $_SESSION['errors'] = ["Account is temporarily locked. Please try again later."];
                header("Location: /login");
                exit;
            }

            // Check if 2FA is enabled
            if ($user['two_factor_enabled']) {
                // Store user data in session for 2FA verification
                Session::start();
                Session::set('2fa_user_id', $user['id']);
                Session::set('2fa_username', $user['username']);
                Session::set('2fa_remember_me', $rememberMe);
                
                // Send 2FA code via email
                TwoFactorAuth::sendEmailCode($user['id'], $user['email']);
                
                header("Location: /2fa-verify");
                exit;
            }

            // Complete login process
            $this->completeLogin($user, $rememberMe);
        } else {
            // Increment login attempts
            if ($user) {
                User::incrementLoginAttempts($user['id']);
            }
            
        Security::logSecurityEvent($this->db, $user['id'] ?? 0, 'login_failed', 'user', null, [
            'identifier' => $identifier,
            'ip' => $ip
        ]);
            
            $_SESSION['errors'] = ["Invalid login credentials"];
            header("Location: /login");
            exit;
        }
    }

    // Complete login process
    private function completeLogin($user, $rememberMe = false) {
        Session::start();
        session_regenerate_id(true);
        
        // Update user login info
        User::updateLastLogin($user['id']);
        User::resetLoginAttempts($user['id']);
        
        // Set session data
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('login_time', time());
        
        // Remember me functionality
        if ($rememberMe) {
            $token = Security::generateToken();
            User::createRememberToken($user['id'], $token);
            
            $cookieValue = base64_encode($user['id'] . ':' . $token);
            setcookie('remember_token', $cookieValue, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
        }
        
        // Create session record
        User::createSession($user['id'], session_id(), Security::getClientIP());
        
        Security::logSecurityEvent($this->db, $user['id'], 'login_success', 'user', $user['id'], [
            'ip' => Security::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        header("Location: /dashboard");
        exit;
    }

    // Handle 2FA verification
    public function verify2FA() {
        if (!Session::get('2fa_user_id')) {
            header("Location: /login");
            exit;
        }

        $code = Security::sanitizeInput($_POST['2fa_code'] ?? '');
        $userId = Session::get('2fa_user_id');
        $rememberMe = Session::get('2fa_remember_me', false);

        if (TwoFactorAuth::verifyCode($userId, $code)) {
            // Get user data and complete login
            $user = User::findById($userId);
            if ($user) {
                // Clear 2FA session data
                Session::remove('2fa_user_id');
                Session::remove('2fa_username');
                Session::remove('2fa_remember_me');
                
                $this->completeLogin($user, $rememberMe);
            }
        } else {
            $_SESSION['errors'] = ["Invalid 2FA code. Please try again."];
            header("Location: /2fa-verify");
            exit;
        }
    }

    // Resend 2FA code
    public function resend2FACode() {
        if (!Session::get('2fa_user_id')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit;
        }

        $userId = Session::get('2fa_user_id');
        $user = User::findById($userId);
        
        if ($user) {
            TwoFactorAuth::sendEmailCode($userId, $user['email']);
            echo json_encode(['success' => true, 'message' => 'Code sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        exit;
    }

    // Verify email
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (User::verifyEmail($token)) {
            $_SESSION['success'] = "Email verified successfully! You can now log in.";
        } else {
            $_SESSION['errors'] = ["Invalid or expired verification link."];
        }
        
        header("Location: /login");
        exit;
    }

    // Handles user logout
    public function logout() {
        Session::start();
        Logger::log(Session::get('user_id'), 'logout');
        Session::destroy();
        header("Location: /login");
        exit;
    }

    // Displays the password reset form (placeholder)
    public function showPasswordReset() {
        // You can add your password reset form here
        echo "Password Reset Form - Coming Soon!";
        exit;
    }

    // Handles a password reset request (placeholder)
    public function passwordResetRequest() {
        // You can add your password reset logic here
        echo "Password Reset Request Handled - Coming Soon!";
        exit;
    }

    /**
     * Redirects the user to the social provider's authorization page.
     * @param string $provider The social provider (e.g., 'google').
     */
    public function redirectToProvider(string $provider) {
        if (!isset($this->providers[$provider])) {
            $_SESSION['errors'] = ["Invalid provider specified."];
            header("Location: /login");
            exit;
        }

        $config = $this->providers[$provider];
        $authUrl = '';

        switch ($provider) {
            case 'google':
                $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'response_type' => 'code',
                    'scope' => $config['scope'],
                ]);
                break;
            case 'github':
                $authUrl = "https://github.com/login/oauth/authorize?" . http_build_query([
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'scope' => $config['scope'],
                ]);
                break;
            case 'facebook':
                $authUrl = "https://www.facebook.com/v19.0/dialog/oauth?" . http_build_query([
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'scope' => $config['scope'],
                ]);
                break;
        }

        header("Location: " . $authUrl);
        exit;
    }

    /**
     * Handles the callback from the social provider, logs in or registers the user.
     * @param string $provider The social provider (e.g., 'google').
     */
    public function handleProviderCallback(string $provider) {
        if (!isset($_GET['code'])) {
            $_SESSION['errors'] = ["Social login failed. No code received."];
            header("Location: /login");
            exit;
        }
        
        $code = $_GET['code'];
        $config = $this->providers[$provider];
        $userProfile = [];

        // Exchange code for an access token and fetch user profile
        switch ($provider) {
            case 'google':
                $tokenUrl = "https://oauth2.googleapis.com/token";
                $tokenData = [
                    'code' => $code,
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect_uri'],
                    'grant_type' => 'authorization_code',
                ];

                // Simulate token exchange and API call
                // In a real app, you would use a library like Guzzle to make these requests
                $accessToken = "simulated_access_token"; // Mock token
                $userProfile['id'] = "google_user_id";
                $userProfile['email'] = "user@example.com";
                $userProfile['username'] = "Google User";
                break;
            case 'github':
                $tokenUrl = "https://github.com/login/oauth/access_token";
                $tokenData = [
                    'code' => $code,
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect_uri'],
                ];
                $accessToken = "simulated_access_token"; // Mock token
                $userProfile['id'] = "github_user_id";
                $userProfile['email'] = "github_user@example.com";
                $userProfile['username'] = "GitHub User";
                break;
            case 'facebook':
                $tokenUrl = "https://graph.facebook.com/v19.0/oauth/access_token";
                $tokenData = [
                    'code' => $code,
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect_uri'],
                ];
                $accessToken = "simulated_access_token"; // Mock token
                $userProfile['id'] = "facebook_user_id";
                $userProfile['email'] = "facebook_user@example.com";
                $userProfile['username'] = "Facebook User";
                break;
        }

        // Check if user exists based on provider ID or email
        $user = User::findBySocialId($provider, $userProfile['id']);
        if (!$user) {
            $user = User::findByEmail($userProfile['email']);
        }

        if ($user) {
            // User exists, log them in and update social ID if needed
            if (!isset($user[$provider . '_id'])) {
                // This is a normal user who is now linking their social account
                User::updateSocialId($user['id'], $provider, $userProfile['id']);
            }
        } else {
            // User does not exist, create a new user account
            User::createFromSocial($userProfile['username'], $userProfile['email'], $provider, $userProfile['id']);
            $user = User::findBySocialId($provider, $userProfile['id']);
        }
        
        // Log the user in
        Session::start();
        session_regenerate_id(true);
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Logger::log($user['id'], 'social_login_success');

        header("Location: /dashboard");
        exit;
    }
}
