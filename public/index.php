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
use App\Controllers\ApiController;
use App\Controllers\APIController as NewAPIController;
use App\Controllers\VoiceNotesController;
use App\Controllers\OCRController;
use App\Controllers\AutomationController;
use App\Controllers\IntegrationsController;
use App\Controllers\AnalyticsController;
use App\Controllers\DataManagementController;
use App\Controllers\DatabaseController;
use App\Controllers\AIAssistantController;
use App\Controllers\TeamsController;
use App\Controllers\SharedController;
use Core\Session;
use Core\Database;
use Core\Security;
use Core\CSRF;
use Core\SecurityHeaders;
use Core\Cache;
use Core\ThemeManager;
use Core\KeyboardShortcuts;
use Core\RichTextEditor;
use App\Views\View;
// ✅ NEW: Import the Models
use App\Models\NotesModel;
use App\Models\TasksModel;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set security headers
SecurityHeaders::setAll();

// Start session
Session::start();

// Create database connection instance first
// $db holds the PDO instance
$db = Database::getInstance();

// Initialize cache
$cache = Cache::getInstance($db);

// Initialize theme manager
$themeManager = new ThemeManager($db);

// Initialize keyboard shortcuts
$keyboardShortcuts = new KeyboardShortcuts($db);

// Initialize rich text editor
$richTextEditor = new RichTextEditor($db);

// Rate limiting check
$ip = Security::getClientIP();
$endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!Security::checkRateLimit($db, $endpoint, 100, 60)) { // 100 requests per minute
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
$voiceNotes = new VoiceNotesController();
$ocr = new OCRController();
$automation = new AutomationController();
$integrations = new IntegrationsController();
$analytics = new AnalyticsController();
$dataManagement = new DataManagementController();
$database = new DatabaseController();
$aiAssistant = new AIAssistantController();
$teams = new TeamsController();
$shared = new SharedController();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle API routes first
if (strpos($uri, '/api') === 0) {
    $newApi = new NewAPIController();
    $newApi->handleRequest();
    exit;
}

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
        if ($method === 'GET') $pwdReset->showResetForm();
        if ($method === 'POST') $pwdReset->resetPassword();
        break;

    case '/password-reset-direct':
        if ($method === 'POST') $pwdReset->directReset();
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

        // Voice Notes Routes
        case '/voice-notes':
            if ($method === 'GET') $voiceNotes->index();
            break;

        case '/voice-notes/save':
            if ($method === 'POST') $voiceNotes->save();
            break;

        case '/voice-notes/list':
            if ($method === 'GET') $voiceNotes->list();
            break;

        // OCR Routes
        case '/ocr':
            if ($method === 'GET') $ocr->index();
            break;

        case '/ocr/process':
            if ($method === 'POST') $ocr->process();
            break;

        case '/ocr/results':
            if ($method === 'GET') $ocr->results();
            break;

        // Dynamic Voice Notes Routes
        case (preg_match('/^\/voice-notes\/transcribe\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'POST') $voiceNotes->transcribe($matches[1]);
            break;

        case (preg_match('/^\/voice-notes\/convert\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'POST') $voiceNotes->convert($matches[1]);
            break;

        case (preg_match('/^\/voice-notes\/delete\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'DELETE') $voiceNotes->delete($matches[1]);
            break;

        case (preg_match('/^\/voice-notes\/file\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'GET') $voiceNotes->getFile($matches[1]);
            break;

        // Dynamic OCR Routes
        case (preg_match('/^\/ocr\/convert\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'POST') $ocr->convert($matches[1]);
            break;

        case (preg_match('/^\/ocr\/delete\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'DELETE') $ocr->delete($matches[1]);
            break;

        case (preg_match('/^\/ocr\/image\/(\d+)$/', $uri, $matches) ? true : false):
            if ($method === 'GET') $ocr->getImage($matches[1]);
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

        // Automation Routes
        case '/automation':
            if ($method === 'GET') $automation->index();
            break;

        case '/automation/workflows':
            if ($method === 'GET') $automation->getWorkflows();
            if ($method === 'POST') $automation->createWorkflow();
            break;

        case '/automation/workflows/execute':
            if ($method === 'POST') $automation->executeWorkflow();
            break;

        case '/automation/workflows/get':
            if ($method === 'GET') $automation->getWorkflow();
            break;

        case '/automation/tasks':
            if ($method === 'GET') $automation->getScheduledTasks();
            if ($method === 'POST') $automation->createScheduledTask();
            break;

        case '/automation/tasks/update':
            if ($method === 'PUT') $automation->updateScheduledTask();
            break;

        case '/automation/tasks/delete':
            if ($method === 'DELETE') $automation->deleteScheduledTask();
            break;

        case '/automation/webhooks':
            if ($method === 'GET') $automation->getWebhooks();
            if ($method === 'POST') $automation->createWebhook();
            break;

        case '/automation/webhooks/update':
            if ($method === 'PUT') $automation->updateWebhook();
            break;

        case '/automation/webhooks/delete':
            if ($method === 'DELETE') $automation->deleteWebhook();
            break;

        case '/automation/webhooks/test':
            if ($method === 'POST') $automation->testWebhook();
            break;

        case '/automation/webhooks/executions':
            if ($method === 'GET') $automation->getWebhookExecutions();
            break;

        case '/automation/webhooks/stats':
            if ($method === 'GET') $automation->getWebhookStats();
            break;

        case '/automation/webhooks/trigger':
            if ($method === 'POST') $automation->triggerWebhook();
            break;

        // Integrations Routes
        case '/integrations':
            if ($method === 'GET') $integrations->index();
            break;

        case '/integrations/google/callback':
            if ($method === 'GET') $integrations->googleCallback();
            break;

        case '/integrations/microsoft/callback':
            if ($method === 'GET') $integrations->microsoftCallback();
            break;

        case '/integrations/slack/callback':
            if ($method === 'GET') $integrations->slackCallback();
            break;

        case '/integrations/google/disconnect':
            if ($method === 'POST') $integrations->disconnectGoogle();
            break;

        case '/integrations/microsoft/disconnect':
            if ($method === 'POST') $integrations->disconnectMicrosoft();
            break;

        case '/integrations/slack/disconnect':
            if ($method === 'POST') $integrations->disconnectSlack();
            break;

        case '/integrations/google/profile':
            if ($method === 'GET') $integrations->getGoogleProfile();
            break;

        case '/integrations/microsoft/profile':
            if ($method === 'GET') $integrations->getMicrosoftProfile();
            break;

        case '/integrations/slack/team':
            if ($method === 'GET') $integrations->getSlackTeamInfo();
            break;

        case '/integrations/slack/channels':
            if ($method === 'GET') $integrations->getSlackChannels();
            break;

        case '/integrations/google/upload':
            if ($method === 'POST') $integrations->uploadToGoogleDrive();
            break;

        case '/integrations/microsoft/upload':
            if ($method === 'POST') $integrations->uploadToOneDrive();
            break;

        case '/integrations/slack/message':
            if ($method === 'POST') $integrations->sendSlackMessage();
            break;

        case '/integrations/calendar/event':
            if ($method === 'POST') $integrations->createCalendarEvent();
            break;

        // Analytics Routes
        case '/analytics':
            if ($method === 'GET') $analytics->index();
            break;

        case '/analytics/user-behavior':
            if ($method === 'GET') $analytics->userBehavior();
            break;

        case '/analytics/performance':
            if ($method === 'GET') $analytics->performance();
            break;

        case '/analytics/usage':
            if ($method === 'GET') $analytics->usage();
            break;

        case '/analytics/user-activity':
            if ($method === 'GET') $analytics->userActivity();
            break;

        case '/analytics/track-behavior':
            if ($method === 'POST') $analytics->trackBehavior();
            break;

        case '/analytics/track-feature-usage':
            if ($method === 'POST') $analytics->trackFeatureUsage();
            break;

        case '/analytics/track-content-interaction':
            if ($method === 'POST') $analytics->trackContentInteraction();
            break;

        case '/analytics/cleanup':
            if ($method === 'POST') $analytics->cleanup();
            break;

        // Data Management Routes
        case '/data-management':
            if ($method === 'GET') $dataManagement->index();
            break;

        case '/data-management/export':
            if ($method === 'POST') $dataManagement->export();
            break;

        case '/data-management/import':
            if ($method === 'POST') $dataManagement->import();
            break;

        case '/data-management/validate-file':
            if ($method === 'POST') $dataManagement->validateFile();
            break;

        case '/data-management/run-migrations':
            if ($method === 'POST') $dataManagement->runMigrations();
            break;

        case '/data-management/migration-status':
            if ($method === 'GET') $dataManagement->migrationStatus();
            break;

        case '/data-management/create-migration':
            if ($method === 'POST') $dataManagement->createMigration();
            break;

        case '/data-management/validate-migration':
            if ($method === 'POST') $dataManagement->validateMigration();
            break;

        case '/data-management/rollback-migration':
            if ($method === 'POST') $dataManagement->rollbackMigration();
            break;

        case '/data-management/backup-database':
            if ($method === 'POST') $dataManagement->backupDatabase();
            break;

        case '/data-management/export-history':
            if ($method === 'GET') $dataManagement->getExportHistory();
            break;

        case '/data-management/import-history':
            if ($method === 'GET') $dataManagement->getImportHistory();
            break;

        case '/data-management/migration-history':
            if ($method === 'GET') $dataManagement->getMigrationHistory();
            break;

        case '/data-management/cleanup-exports':
            if ($method === 'POST') $dataManagement->cleanupExports();
            break;

        case '/data-management/download-export':
            if ($method === 'GET') $dataManagement->downloadExport();
            break;

        // Database Management Routes
        case '/database':
            if ($method === 'GET') $database->index();
            break;

        case '/database/analyze-performance':
            if ($method === 'GET') $database->analyzePerformance();
            break;

        case '/database/optimize-table':
            if ($method === 'POST') $database->optimizeTable();
            break;

        case '/database/create-index':
            if ($method === 'POST') $database->createIndex();
            break;

        case '/database/drop-index':
            if ($method === 'POST') $database->dropIndex();
            break;

        case '/database/analyze-table':
            if ($method === 'POST') $database->analyzeTable();
            break;

        case '/database/explain-query':
            if ($method === 'POST') $database->explainQuery();
            break;

        case '/database/replication-status':
            if ($method === 'GET') $database->getReplicationStatus();
            break;

        case '/database/connection-pool-stats':
            if ($method === 'GET') $database->getConnectionPoolStats();
            break;

        case '/database/configuration-recommendations':
            if ($method === 'GET') $database->getConfigurationRecommendations();
            break;

        case '/database/slow-queries':
            if ($method === 'GET') $database->getSlowQueries();
            break;

        case '/database/optimization-recommendations':
            if ($method === 'GET') $database->getOptimizationRecommendations();
            break;

        case '/database/update-recommendation-status':
            if ($method === 'POST') $database->updateRecommendationStatus();
            break;

        case '/database/table-statistics':
            if ($method === 'GET') $database->getTableStatistics();
            break;

        case '/database/health':
            if ($method === 'GET') $database->getDatabaseHealth();
            break;

        case '/database/execute-query':
            if ($method === 'POST') $database->executeCustomQuery();
            break;

        // AI Assistant Routes
        case '/ai-assistant':
            if ($method === 'GET') $aiAssistant->index();
            break;

        case '/ai-assistant/generate':
            if ($method === 'POST') $aiAssistant->generateContent();
            break;

        case '/ai-assistant/analyze':
            if ($method === 'POST') $aiAssistant->analyzeContent();
            break;

        case '/ai-assistant/suggestions':
            if ($method === 'POST') $aiAssistant->getSuggestions();
            break;

        case '/ai-assistant/summarize':
            if ($method === 'POST') $aiAssistant->summarizeContent();
            break;

        case '/ai-assistant/title':
            if ($method === 'POST') $aiAssistant->generateTitle();
            break;

        case '/ai-assistant/history':
            if ($method === 'GET') $aiAssistant->getHistory();
            break;

        // Teams Routes
        case '/teams':
            if ($method === 'GET') $teams->index();
            break;

        case '/teams/create':
            if ($method === 'POST') $teams->create();
            break;

        case '/teams/add-member':
            if ($method === 'POST') $teams->addMember();
            break;

        case '/teams/remove-member':
            if ($method === 'POST') $teams->removeMember();
            break;

        case '/teams/update-member-role':
            if ($method === 'POST') $teams->updateMemberRole();
            break;

        case '/teams/share-note':
            if ($method === 'POST') $teams->shareNote();
            break;

        case '/teams/share-task':
            if ($method === 'POST') $teams->shareTask();
            break;

        case '/teams/delete':
            if ($method === 'POST') $teams->delete();
            break;

        // Shared Content Routes
        case '/shared':
            if ($method === 'GET') $shared->index();
            break;

        case '/shared/create-link':
            if ($method === 'POST') $shared->createLink();
            break;

        case '/shared/revoke-link':
            if ($method === 'POST') $shared->revokeLink();
            break;

        case '/shared/authenticate':
            if ($method === 'POST') $shared->authenticateLink();
            break;
            
        default:
            // Handle dynamic routes
            if (preg_match('/^\/teams\/(\d+)$/', $uri, $matches)) {
                if ($method === 'GET') $teams->view($matches[1]);
                break;
            }
            
            if (preg_match('/^\/shared\/([a-f0-9]{64})$/', $uri, $matches)) {
                if ($method === 'GET') $shared->accessLink($matches[1]);
                break;
            }
            
            if (preg_match('/^\/voice-notes\/transcribe\/(\d+)$/', $uri, $matches)) {
                if ($method === 'POST') $voiceNotes->transcribe($matches[1]);
                break;
            }
            
            if (preg_match('/^\/voice-notes\/convert\/(\d+)$/', $uri, $matches)) {
                if ($method === 'POST') $voiceNotes->convert($matches[1]);
                break;
            }
            
            if (preg_match('/^\/voice-notes\/delete\/(\d+)$/', $uri, $matches)) {
                if ($method === 'POST') $voiceNotes->delete($matches[1]);
                break;
            }
            
            if (preg_match('/^\/voice-notes\/file\/(\d+)$/', $uri, $matches)) {
                if ($method === 'GET') $voiceNotes->getFile($matches[1]);
                break;
            }
            
            if (preg_match('/^\/ocr\/convert\/(\d+)$/', $uri, $matches)) {
                if ($method === 'POST') $ocr->convert($matches[1]);
                break;
            }
            
            if (preg_match('/^\/ocr\/delete\/(\d+)$/', $uri, $matches)) {
                if ($method === 'POST') $ocr->delete($matches[1]);
                break;
            }
            
            if (preg_match('/^\/ocr\/image\/(\d+)$/', $uri, $matches)) {
                if ($method === 'GET') $ocr->getImage($matches[1]);
                break;
            }
            
            // If no dynamic route matches, show 404
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
        '/cloud-integration/connect-dropbox', '/cloud-integration/dropbox/callback', '/cloud-integration/disconnect-dropbox', '/cloud-integration/upload-backup-to-dropbox', '/cloud-integration/download-dropbox-file', '/cloud-integration/list-dropbox-files', '/cloud-integration/delete-dropbox-file',
        '/automation', '/automation/workflows', '/automation/workflows/execute', '/automation/workflows/get', '/automation/tasks', '/automation/tasks/update', '/automation/tasks/delete', '/automation/webhooks', '/automation/webhooks/update', '/automation/webhooks/delete', '/automation/webhooks/test', '/automation/webhooks/executions', '/automation/webhooks/stats', '/automation/webhooks/trigger',
        '/integrations', '/integrations/google/callback', '/integrations/microsoft/callback', '/integrations/slack/callback', '/integrations/google/disconnect', '/integrations/microsoft/disconnect', '/integrations/slack/disconnect', '/integrations/google/profile', '/integrations/microsoft/profile', '/integrations/slack/team', '/integrations/slack/channels', '/integrations/google/upload', '/integrations/microsoft/upload', '/integrations/slack/message', '/integrations/calendar/event',
        '/analytics', '/analytics/user-behavior', '/analytics/performance', '/analytics/usage', '/analytics/user-activity', '/analytics/track-behavior', '/analytics/track-feature-usage', '/analytics/track-content-interaction', '/analytics/cleanup',
        '/data-management', '/data-management/export', '/data-management/import', '/data-management/validate-file', '/data-management/run-migrations', '/data-management/migration-status', '/data-management/create-migration', '/data-management/validate-migration', '/data-management/rollback-migration', '/data-management/backup-database', '/data-management/export-history', '/data-management/import-history', '/data-management/migration-history', '/data-management/cleanup-exports', '/data-management/download-export',
        '/database', '/database/analyze-performance', '/database/optimize-table', '/database/create-index', '/database/drop-index', '/database/analyze-table', '/database/explain-query', '/database/replication-status', '/database/connection-pool-stats', '/database/configuration-recommendations', '/database/slow-queries', '/database/optimization-recommendations', '/database/update-recommendation-status', '/database/table-statistics', '/database/health', '/database/execute-query',
        '/ai-assistant', '/ai-assistant/generate', '/ai-assistant/analyze', '/ai-assistant/suggestions', '/ai-assistant/summarize', '/ai-assistant/title', '/ai-assistant/history',
        '/teams', '/teams/create', '/teams/add-member', '/teams/remove-member', '/teams/update-member-role', '/teams/share-note', '/teams/share-task', '/teams/delete',
        '/shared', '/shared/create-link', '/shared/revoke-link', '/shared/authenticate'
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
    '/backup', '/backup/create', '/backup/download', '/backup/status', '/backup/history', '/backup/export', '/backup/schedule', '/backup/import', '/backup/settings', '/backup/test', '/backup/delete', '/backup/analytics', '/backup/connect-cloud', '/backup/verify', '/backup/repair', '/backup/report', '/search', '/search/api', '/search/global', '/search/suggestions', '/offline', '/security', '/security/enable-2fa', '/security/verify-2fa', '/security/disable-2fa', '/security/change-password', '/security/sessions', '/security/terminate-session', '/security/terminate-all-sessions', '/security/regenerate-backup-codes', '/security/events', '/trash', '/trash/empty', '/automation', '/automation/workflows', '/automation/workflows/execute', '/automation/workflows/get', '/automation/tasks', '/automation/tasks/update', '/automation/tasks/delete', '/automation/webhooks', '/automation/webhooks/update', '/automation/webhooks/delete', '/automation/webhooks/test', '/automation/webhooks/executions', '/automation/webhooks/stats', '/automation/webhooks/trigger', '/integrations', '/integrations/google/callback', '/integrations/microsoft/callback', '/integrations/slack/callback', '/integrations/google/disconnect', '/integrations/microsoft/disconnect', '/integrations/slack/disconnect', '/integrations/google/profile', '/integrations/microsoft/profile', '/integrations/slack/team', '/integrations/slack/channels', '/integrations/google/upload', '/integrations/microsoft/upload', '/integrations/slack/message',     '/integrations/calendar/event', '/analytics', '/analytics/user-behavior', '/analytics/performance', '/analytics/usage', '/analytics/user-activity', '/analytics/track-behavior', '/analytics/track-feature-usage', '/analytics/track-content-interaction', '/analytics/cleanup', '/data-management', '/data-management/export', '/data-management/import', '/data-management/validate-file', '/data-management/run-migrations', '/data-management/migration-status', '/data-management/create-migration', '/data-management/validate-migration', '/data-management/rollback-migration', '/data-management/backup-database', '/data-management/export-history', '/data-management/import-history', '/data-management/migration-history', '/data-management/cleanup-exports', '/data-management/download-export', '/ai-assistant', '/ai-assistant/generate', '/ai-assistant/analyze', '/ai-assistant/suggestions', '/ai-assistant/summarize', '/ai-assistant/title', '/ai-assistant/history', '/teams', '/teams/create', '/teams/add-member', '/teams/remove-member', '/teams/update-member-role', '/teams/share-note', '/teams/share-task', '/teams/delete', '/shared', '/shared/create-link', '/shared/revoke-link', '/shared/authenticate', '/profile'
];

if (!in_array($uri, $allRoutes)) {
    http_response_code(404);
    echo "404 - Not Found";
}