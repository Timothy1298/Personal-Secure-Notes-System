<?php
namespace App\Controllers;

use App\Models\User;
use Core\Session;
use Core\Logger;
use Core\CSRF;
use Core\Provider; // Assuming you have a Provider class for social APIs

// Social Login Providers Configuration
// You MUST get these credentials from the respective developer consoles.
// IMPORTANT: The redirect_uri must match the port you are using (3000).
$socialProviders = [
    'google' => [
        'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost:3000/social/google/callback',
        'scope' => 'email profile',
    ],
    'github' => [
        'client_id' => 'YOUR_GITHUB_CLIENT_ID',
        'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET',
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

    public function __construct() {
        global $socialProviders;
        $this->providers = $socialProviders;
    }

    // Displays the registration form
    public function showRegister() {
        include __DIR__ . '/../Views/register.php';
    }

    // Handles user registration from the form
    public function register() {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        // Validation
        $errors = [];
        if (strlen($username) < 4) $errors[] = "Username must be at least 4 characters.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        if (User::findByEmailOrUsername($email) || User::findByEmailOrUsername($username)) {
            $errors[] = "Email or Username already taken.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /register");
            exit;
        }

        User::create($username, $email, $password);
        $_SESSION['success'] = "Account created! Please login.";
        header("Location: /login");
        exit;
    }

    // Displays the login form
    public function showLogin() {
        include __DIR__ . '/../Views/login.php';
    }

    // Handles user login from the form
    public function login() {
        $identifier = trim($_POST['identifier']);
        $password = $_POST['password'];
        $user = User::findByEmailOrUsername($identifier);

        if ($user && password_verify($password, $user['password_hash'])) {
            Session::start();
            session_regenerate_id(true);
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);

            Logger::log($user['id'], 'login_success');

            header("Location: /dashboard");
            exit;
        } else {
            Logger::log($user['id'] ?? null, 'login_failed');
            $_SESSION['errors'] = ["Invalid login credentials"];
            header("Location: /login");
            exit;
        }
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
