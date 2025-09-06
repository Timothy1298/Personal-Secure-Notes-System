<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\PasswordResetController;
use App\Controllers\DashboardController;
use App\Controllers\NotesController;
use App\Controllers\TasksController;
use App\Controllers\TagsController;
use App\Controllers\AuditLogsController;
use App\Controllers\ArchivedController;
use App\Controllers\SettingsController;
use Core\Session;
use Core\Database;
use App\Views\View; // ✅ Add View import

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Start session
Session::start();

// Create database connection instance first
$db = Database::getInstance();

// ✅ Create View instance
$view = new View();

// Create controller instances
$auth = new AuthController();
$pwdReset = new PasswordResetController();
$auditLogs = new AuditLogsController($db, $view); // ✅ Pass both $db and $view
$notes = new NotesController($db, $auditLogs); // Inject auditLogs here
$tasks = new TasksController($db, $auditLogs); // Inject auditLogs here
$tags = new TagsController($db);
$archived = new ArchivedController($db);
$settings = new SettingsController($db);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to check for authentication
function isAuthenticated() {
    return Session::get('user_id') !== null;
}

// --- Public Routes (no authentication required) ---
switch ($uri) {
    case '/':
    case '/login':
        if ($method === 'GET') $auth->showLogin();
        if ($method === 'POST') $auth->login();
        break;

    case '/register':
        if ($method === 'GET') $auth->showRegister();
        if ($method === 'POST') $auth->register();
        break;

    case '/logout':
        $auth->logout();
        break;

    // Password Reset Routes
    case '/password-reset':
        if ($method === 'GET') $pwdReset->requestForm();
        if ($method === 'POST') $pwdReset->sendResetLink();
        break;

    case '/password-reset-form':
        $pwdReset->resetForm();
        break;

    case '/password-reset-submit':
        if ($method === 'POST') $pwdReset->reset();
        break;
}

// --- Protected Routes (authentication required) ---
if (isAuthenticated()) {
    switch ($uri) {
        case '/dashboard':
            (new DashboardController())->index();
            break;

        // Notes Routes
        case '/notes':
            if ($method === 'GET') $notes->index();
            break;

        case '/notes/store':
            if ($method === 'POST') $notes->store();
            break;
            
        case '/notes/update':
            if ($method === 'POST') $notes->update();
            break;
            
        case '/notes/delete':
            if ($method === 'POST') $notes->delete();
            break;
            
        case '/notes/archive':
            if ($method === 'POST') $notes->archive();
            break;
            
        // Task Routes
        case '/tasks':
            if ($method === 'GET') $tasks->index();
            break;

        case '/tasks/store':
            if ($method === 'POST') $tasks->store();
            break;

        case '/tasks/update':
            if ($method === 'POST') $tasks->update();
            break;
            
        case '/tasks/delete':
            if ($method === 'POST') $tasks->delete();
            break;
            
        case '/tasks/complete':
            if ($method === 'POST') $tasks->Complete();
            break;
        case '/tasks/uncomplete':
            if ($method === 'POST') $tasks->Uncomplete();
            break;

        case '/tasks/archive':
            if ($method === 'POST') $tasks->archive();
            break;

        // Tag Routes
        case '/tags':
            if ($method === 'GET') $tags->index();
            break;
    
        case '/tags/store':
            if ($method === 'POST') $tags->store();
            break;
            
        case '/audit-logs':
            if ($method === 'GET') $auditLogs->index();
            break;
            
        case '/audit-logs/filter':
            if ($method === 'GET') $auditLogs->filterLogs();
            break;

        // Archived Routes
        case '/archived':
            if ($method === 'GET') $archived->index();
            break;

        case '/archived/note/unarchive':
            if ($method === 'POST') $archived->unarchiveNote();
            break;

        case '/archived/task/unarchive':
            if ($method === 'POST') $archived->unarchiveTask();
            break;
            
        // Settings Routes
        case '/settings':
            if ($method === 'GET') $settings->index();
            break;
        case '/settings/account/password':
            if ($method === 'POST') $settings->updatePassword();
            break;
        case '/settings/security/2fa':
            if ($method === 'POST') $settings->update2fa();
            break;
        case '/settings/data/export':
            if ($method === 'POST') $settings->exportData();
            break;
        case '/settings/account/delete':
            if ($method === 'POST') $settings->deleteAccount();
            break;
        case '/settings/appearance/theme':
            if ($method === 'POST') $settings->updateTheme();
            break;
        case '/settings/appearance/font-size':
            if ($method === 'POST') $settings->updateFontSize();
            break;
        case '/settings/appearance/note-layout':
            if ($method === 'POST') $settings->updateNoteLayout();
            break;
        case '/settings/notes/default-state':
            if ($method === 'POST') $settings->updateDefaultNoteState();
            break;
        case '/settings/notes/default-tags':
            if ($method === 'POST') $settings->updateDefaultTags();
            break;
        case '/settings/notes/auto-archive':
            if ($method === 'POST') $settings->updateAutoArchive();
            break;
        case '/settings/notes/auto-empty-trash':
            if ($method === 'POST') $settings->updateAutoEmptyTrash();
            break;
        case '/settings/notifications/email':
            if ($method === 'POST') $settings->updateEmailNotifications();
            break;
        case '/settings/notifications/desktop':
            if ($method === 'POST') $settings->updateDesktopNotifications();
            break;
        case '/settings/notifications/reminders':
            if ($method === 'POST') $settings->updateReminderAlerts();
            break;
        case '/settings/advanced/log-retention':
            if ($method === 'POST') $settings->updateLogRetention();
            break;

        
            
        default:
            http_response_code(404);
            echo "404 - Not Found";
            break;
    }
} else {
    $protectedRoutes = [
        '/dashboard', '/notes', '/notes/create', '/notes/store', '/notes/update', '/notes/delete', '/notes/archive',
        '/tasks', '/tasks/store', '/tasks/update', '/tasks/delete', '/tasks/complete', '/tasks/archive',
        '/tags', '/tags/store', '/audit-logs', '/audit-logs/filter', '/archived', '/archived/note/unarchive', '/archived/task/unarchive',
'/settings', '/settings/update',
        '/settings/account/password', '/settings/security/2fa', '/settings/data/export', '/settings/account/delete',
        '/settings/appearance/theme', '/settings/appearance/font-size', '/settings/appearance/note-layout',
        '/settings/notes/default-state', '/settings/notes/default-tags', '/settings/notes/auto-archive', '/settings/notes/auto-empty-trash',
        '/settings/notifications/email', '/settings/notifications/desktop', '/settings/notifications/reminders',
        '/settings/advanced/log-retention'
    ];
    
    if (in_array($uri, $protectedRoutes)) {
        header("Location: /login");
        exit;
    }
}

$allRoutes = [
    '/', '/login', '/register', '/logout', '/password-reset', '/password-reset-form',
    '/password-reset-submit', '/dashboard', '/notes', '/notes/create', '/notes/store',
    '/notes/update', '/notes/delete', '/notes/archive', '/tasks', '/tasks/store',
    '/tasks/update', '/tasks/delete', '/tasks/complete', '/tasks/archive', '/tags', '/tags/store', '/audit-logs', '/audit-logs/filter',
    '/archived', '/archived/note/unarchive', '/archived/task/unarchive', '/settings', '/settings/update'
];

if (!in_array($uri, $allRoutes)) {
    http_response_code(404);
    echo "404 - Not Found";
}
