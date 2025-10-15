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
use App\Controllers\TrashController;
use App\Controllers\SecurityController;
use App\Controllers\SearchController;
use App\Controllers\ImportController;
use App\Controllers\CloudController;
use Core\Session;
use Core\Database;
use Core\Security;
use Core\CSRF;
use App\Views\View;
// ✅ NEW: Import the Models
use App\Models\NotesModel;
use App\Models\TasksModel;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Start session
Session::start();

// Create database connection instance first
// $db holds the PDO instance
$db = Database::getInstance();

// Rate limiting check
$ip = Security::getClientIP();
$endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!Security::checkRateLimit($db, $ip, 100, 60)) { // 100 requests per minute
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// ✅ Create View instance
$view = new View();

// 💡 CORRECTED: Use $db instead of $pdo, and removed the trailing comma
$notesModel = new NotesModel($db); 
$tasksModel = new TasksModel($db);

// Create controller instances
$auth = new AuthController();
$pwdReset = new PasswordResetController();
$auditLogs = new AuditLogsController($db, $view); 
// 💡 UPDATED: Inject $notesModel (3 arguments passed)
$notes = new NotesController($db, $auditLogs, $notesModel); 
// 💡 UPDATED: Inject $tasksModel
$tasks = new TasksController($db, $auditLogs, $tasksModel); 
$tags = new TagsController($db);
$archived = new ArchivedController($db);
$settings = new SettingsController($db);
$trash = new TrashController($db);
$security = new SecurityController($db, $auditLogs);
$search = new SearchController();
$import = new ImportController();
$cloud = new CloudController();

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

    // 2FA Routes
    case '/2fa-verify':
        if ($method === 'GET') include __DIR__ . '/../app/Views/2fa_verify.php';
        if ($method === 'POST') $auth->verify2FA();
        break;

    case '/2fa-resend':
        if ($method === 'POST') $auth->resend2FACode();
        break;

    // Email verification
    case '/verify-email':
        if ($method === 'GET') $auth->verifyEmail();
        break;
}

// --- Protected Routes (authentication required) ---
if (isAuthenticated()) {
    switch ($uri) {
        case '/dashboard':
            (new DashboardController())->index();
            break;

        // Dashboard API Routes
        case '/dashboard/api/activity':
            if ($method === 'GET') (new DashboardController())->apiActivity();
            break;

        case '/dashboard/api/todays-focus':
            if ($method === 'GET') (new DashboardController())->apiTodaysFocus();
            break;

        case '/dashboard/api/recent-notes':
            if ($method === 'GET') (new DashboardController())->apiRecentNotes();
            break;

        case '/dashboard/api/upcoming-tasks':
            if ($method === 'GET') (new DashboardController())->apiUpcomingTasks();
            break;

        case '/dashboard/api/toggle-focus':
            if ($method === 'POST') (new DashboardController())->apiToggleFocus();
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
            
        case '/notes/toggle-pin':
            if ($method === 'POST') $notes->togglePin();
            break;
            
        case '/notes/update-color':
            if ($method === 'POST') $notes->updateColor();
            break;
            
        case '/notes/export':
            if ($method === 'POST') $notes->export();
            break;
            
        case '/notes/auto-save':
            if ($method === 'POST') $notes->autoSave();
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
            
        case '/tasks/update-status':
            if ($method === 'POST') $tasks->updateStatus();
            break;
            
        case '/tasks/update-priority':
            if ($method === 'POST') $tasks->updatePriority();
            break;
            
        case '/tasks/subtasks':
            if ($method === 'GET') $tasks->getSubtasks();
            break;
            
        case '/tasks/api/get-subtasks':
            if ($method === 'GET') $tasks->getSubtasks();
            break;
            
        case '/tasks/subtasks/store':
            if ($method === 'POST') $tasks->storeSubtask();
            break;
            
        case '/tasks/subtasks/update':
            if ($method === 'POST') $tasks->updateSubtask();
            break;
            
        case '/tasks/subtasks/delete':
            if ($method === 'POST') $tasks->deleteSubtask();
            break;
            
        case '/tasks/export':
            if ($method === 'POST') $tasks->export();
            break;

        // Tasks API Routes
        case '/tasks/api/get-kanban':
            if ($method === 'GET') $tasks->apiGetKanban();
            break;

        case '/tasks/api/calendar':
            if ($method === 'GET') $tasks->apiCalendar();
            break;

        // Tag Routes
        case '/tags':
            if ($method === 'GET') $tags->index();
            break;
    
        case '/tags/store':
            if ($method === 'POST') $tags->store();
            break;

        case '/tags/api/get-all':
            if ($method === 'GET') $tags->apiGetAll();
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

        // Backup & Export Routes
        case '/backup':
            if ($method === 'GET') include __DIR__ . '/../app/Views/backup_export.php';
            break;
            
        case '/backup/create':
            if ($method === 'POST') $settings->createBackup();
            break;
            
        case '/backup/download':
            if ($method === 'GET') $settings->downloadBackup();
            break;

        case '/backup/status':
            if ($method === 'GET') $settings->getBackupStatus();
            break;

        case '/backup/history':
            if ($method === 'GET') $settings->getBackupHistory();
            break;
            
        case '/backup/export':
            if ($method === 'POST') $settings->exportBackup();
            break;
            
        case '/backup/schedule':
            if ($method === 'POST') $settings->scheduleBackup();
            break;
            
        case '/backup/import':
            if ($method === 'POST') $settings->importBackup();
            break;
            
        case '/backup/settings':
            if ($method === 'POST') $settings->saveBackupSettings();
            break;
            
        case '/backup/test':
            if ($method === 'POST') $settings->testBackup();
            break;
            
        case '/backup/delete':
            if ($method === 'POST') $settings->deleteBackup();
            break;
            
        case '/backup/analytics':
            if ($method === 'GET') $settings->getBackupAnalytics();
            break;
            
        case '/backup/connect-cloud':
            if ($method === 'POST') $settings->connectCloudService();
            break;
            
        case '/backup/verify':
            if ($method === 'POST') $settings->verifyBackup();
            break;
            
        case '/backup/repair':
            if ($method === 'POST') $settings->repairBackup();
            break;
            
        case '/backup/report':
            if ($method === 'POST') $settings->generateBackupReport();
            break;

        // Global Search Route
        case '/search':
            if ($method === 'GET') $search->index();
            break;
            
        case '/search/api':
        case '/search/global':
            if ($method === 'POST') $search->globalSearch();
            break;
            
        case '/search/suggestions':
            if ($method === 'GET') $search->getSuggestions();
            break;

        // Import Routes
        case '/import':
            if ($method === 'GET') $import->index();
            break;
            
        case '/import/import':
            if ($method === 'POST') $import->import();
            break;
            
        case '/import/validate':
            if ($method === 'POST') $import->validate();
            break;
            
        case '/import/template':
            if ($method === 'GET') $import->downloadTemplate();
            break;
        case '/import/history':
            if ($method === 'GET') $import->getImportHistory();
            break;

        // Offline Mode Route
        case '/offline':
            if ($method === 'GET') include __DIR__ . '/../app/Views/offline_mode.php';
            break;

        // Security Routes
        case '/security':
            if ($method === 'GET') $security->index();
            break;
        case '/security/enable-2fa':
            if ($method === 'POST') $security->enable2FA();
            break;
        case '/security/verify-2fa':
            if ($method === 'POST') $security->verify2FA();
            break;
        case '/security/disable-2fa':
            if ($method === 'POST') $security->disable2FA();
            break;
        case '/security/change-password':
            if ($method === 'POST') $security->changePassword();
            break;
        case '/security/sessions':
            if ($method === 'GET') $security->getActiveSessions();
            break;
        case '/security/terminate-session':
            if ($method === 'POST') $security->terminateSession();
            break;
        case '/security/terminate-all-sessions':
            if ($method === 'POST') $security->terminateAllSessions();
            break;
        case '/security/regenerate-backup-codes':
            if ($method === 'POST') $security->generateBackupCodes();
            break;
        case '/security/events':
            if ($method === 'GET') $security->getSecurityEvents();
            break;

        // Trash Routes
        case '/trash':
            if ($method === 'GET') $trash->index();
            break;
            
        case '/trash/empty':
            if ($method === 'POST') $trash->emptyTrash();
            break;
            
        case '/trash/restore-task':
            if ($method === 'POST') $trash->restoreTask();
            break;
            
        case '/trash/permanent-delete-task':
            if ($method === 'POST') $trash->permanentDeleteTask();
            break;
            
        case '/trash/auto-cleanup':
            if ($method === 'POST') $trash->autoCleanup();
            break;
            
        case '/trash/bulk-restore':
            if ($method === 'POST') $trash->bulkRestore();
            break;
            
        case '/trash/bulk-delete':
            if ($method === 'POST') $trash->bulkDelete();
            break;
            
        case '/trash/restore-note':
            if ($method === 'POST') $trash->restoreNote();
            break;
            
        case '/trash/permanent-delete-note':
            if ($method === 'POST') $trash->permanentDeleteNote();
            break;
            
        case '/trash/bulk-restore-notes':
            if ($method === 'POST') $trash->bulkRestoreNotes();
            break;
            
        case '/trash/bulk-delete-notes':
            if ($method === 'POST') $trash->bulkDeleteNotes();
            break;
            
        case '/trash/export':
            if ($method === 'POST') $trash->export();
            break;

        // Profile Routes
        case '/profile':
            if ($method === 'GET') $settings->profile();
            break;

        // Cloud Integration Routes
        case '/cloud-integration':
            if ($method === 'GET') $cloud->index();
            break;

        case '/cloud-integration/connect-google-drive':
            if ($method === 'POST') $cloud->connectGoogleDrive();
            break;

        case '/cloud-integration/google-drive/callback':
            if ($method === 'GET') $cloud->googleDriveCallback();
            break;

        case '/cloud-integration/disconnect-google-drive':
            if ($method === 'POST') $cloud->disconnectGoogleDrive();
            break;

        case '/cloud-integration/upload-backup-to-google-drive':
            if ($method === 'POST') $cloud->uploadBackupToGoogleDrive();
            break;

        case '/cloud-integration/download-google-drive-file':
            if ($method === 'GET') $cloud->downloadBackupFromGoogleDrive();
            break;

        case '/cloud-integration/list-google-drive-files':
            if ($method === 'GET') $cloud->listGoogleDriveFiles();
            break;

        case '/cloud-integration/delete-google-drive-file':
            if ($method === 'POST') $cloud->deleteGoogleDriveFile();
            break;

        case '/cloud-integration/connect-dropbox':
            if ($method === 'POST') $cloud->connectDropbox();
            break;

        case '/cloud-integration/dropbox/callback':
            if ($method === 'GET') $cloud->dropboxCallback();
            break;

        case '/cloud-integration/disconnect-dropbox':
            if ($method === 'POST') $cloud->disconnectDropbox();
            break;

        case '/cloud-integration/upload-backup-to-dropbox':
            if ($method === 'POST') $cloud->uploadBackupToDropbox();
            break;

        case '/cloud-integration/download-dropbox-file':
            if ($method === 'GET') $cloud->downloadBackupFromDropbox();
            break;

        case '/cloud-integration/list-dropbox-files':
            if ($method === 'GET') $cloud->listDropboxFiles();
            break;

        case '/cloud-integration/delete-dropbox-file':
            if ($method === 'POST') $cloud->deleteDropboxFile();
            break;
            
        default:
            http_response_code(404);
            echo "404 - Not Found";
            break;
    }
} else {
    $protectedRoutes = [
        '/dashboard', '/notes', '/notes/create', '/notes/store', '/notes/update', '/notes/delete', '/notes/archive', '/notes/toggle-pin', '/notes/update-color', '/notes/export', '/notes/auto-save',
        '/tasks', '/tasks/store', '/tasks/update', '/tasks/delete', '/tasks/complete', '/tasks/archive', '/tasks/update-status', '/tasks/update-priority', '/tasks/subtasks', '/tasks/api/get-subtasks', '/tasks/subtasks/store', '/tasks/subtasks/update', '/tasks/subtasks/delete', '/tasks/export', '/tasks/api/get-kanban', '/tasks/api/calendar',
        '/tags', '/tags/store', '/tags/api/get-all', '/audit-logs', '/audit-logs/filter', '/archived', '/archived/note/unarchive', '/archived/task/unarchive',
        '/settings', '/settings/update',
        '/settings/account/password', '/settings/security/2fa', '/settings/data/export', '/settings/account/delete',
        '/settings/appearance/theme', '/settings/appearance/font-size', '/settings/appearance/note-layout',
        '/settings/notes/default-state', '/settings/notes/default-tags', '/settings/notes/auto-archive', '/settings/notes/auto-empty-trash',
        '/settings/notifications/email', '/settings/notifications/desktop', '/settings/notifications/reminders',
        '/settings/advanced/log-retention',
        '/dashboard/api/activity', '/dashboard/api/todays-focus', '/dashboard/api/recent-notes', '/dashboard/api/upcoming-tasks', '/dashboard/api/toggle-focus',
        '/backup', '/backup/create', '/backup/download', '/backup/status', '/backup/history', '/backup/export', '/backup/schedule', '/backup/import', '/backup/settings', '/backup/test', '/backup/delete', '/backup/analytics', '/backup/connect-cloud', '/backup/verify', '/backup/repair', '/backup/report', '/search', '/search/api', '/search/global', '/search/suggestions', '/import', '/import/import', '/import/validate', '/import/template', '/import/history', '/offline', '/security', '/security/enable-2fa', '/security/verify-2fa', '/security/disable-2fa', '/security/change-password', '/security/sessions', '/security/terminate-session', '/security/terminate-all-sessions', '/security/regenerate-backup-codes', '/security/events', '/trash', '/trash/empty', '/trash/restore-task', '/trash/permanent-delete-task', '/trash/restore-note', '/trash/permanent-delete-note', '/trash/auto-cleanup', '/trash/bulk-restore', '/trash/bulk-delete', '/trash/bulk-restore-notes', '/trash/bulk-delete-notes', '/trash/export', '/profile',
        '/cloud-integration', '/cloud-integration/connect-google-drive', '/cloud-integration/google-drive/callback', '/cloud-integration/disconnect-google-drive', '/cloud-integration/upload-backup-to-google-drive', '/cloud-integration/download-google-drive-file', '/cloud-integration/list-google-drive-files', '/cloud-integration/delete-google-drive-file',
        '/cloud-integration/connect-dropbox', '/cloud-integration/dropbox/callback', '/cloud-integration/disconnect-dropbox', '/cloud-integration/upload-backup-to-dropbox', '/cloud-integration/download-dropbox-file', '/cloud-integration/list-dropbox-files', '/cloud-integration/delete-dropbox-file'
    ];
    
    if (in_array($uri, $protectedRoutes)) {
        header("Location: /login");
        exit;
    }
}

$allRoutes = [
    '/', '/login', '/register', '/logout', '/password-reset', '/password-reset-form',
    '/password-reset-submit', '/dashboard', '/notes', '/notes/create', '/notes/store',
    '/notes/update', '/notes/delete', '/notes/archive', '/notes/toggle-pin', '/notes/update-color', '/notes/export', '/notes/auto-save', '/tasks', '/tasks/store',
    '/tasks/update', '/tasks/delete', '/tasks/complete', '/tasks/archive', '/tasks/update-status', '/tasks/update-priority', '/tasks/subtasks', '/tasks/api/get-subtasks', '/tasks/subtasks/store', '/tasks/subtasks/update', '/tasks/subtasks/delete', '/tasks/export', '/tasks/api/get-kanban', '/tasks/api/calendar',
    '/tags', '/tags/store', '/tags/api/get-all', '/audit-logs', '/audit-logs/filter',
    '/archived', '/archived/note/unarchive', '/archived/task/unarchive', '/settings', '/settings/update',
    '/dashboard/api/activity', '/dashboard/api/todays-focus', '/dashboard/api/recent-notes', '/dashboard/api/upcoming-tasks', '/dashboard/api/toggle-focus',
    '/backup', '/backup/create', '/backup/download', '/backup/status', '/backup/history', '/backup/export', '/backup/schedule', '/backup/import', '/backup/settings', '/backup/test', '/backup/delete', '/backup/analytics', '/backup/connect-cloud', '/backup/verify', '/backup/repair', '/backup/report', '/search', '/search/api', '/search/global', '/search/suggestions', '/offline', '/security', '/security/enable-2fa', '/security/verify-2fa', '/security/disable-2fa', '/security/change-password', '/security/sessions', '/security/terminate-session', '/security/terminate-all-sessions', '/security/regenerate-backup-codes', '/security/events', '/trash', '/trash/empty', '/profile'
];

if (!in_array($uri, $allRoutes)) {
    http_response_code(404);
    echo "404 - Not Found";
}