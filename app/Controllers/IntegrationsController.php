<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Integrations\GoogleIntegration;
use Core\Integrations\MicrosoftIntegration;
use Core\Integrations\SlackIntegration;
use Exception;

class IntegrationsController {
    private $db;
    private $googleIntegration;
    private $microsoftIntegration;
    private $slackIntegration;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->googleIntegration = new GoogleIntegration($this->db);
        $this->microsoftIntegration = new MicrosoftIntegration($this->db);
        $this->slackIntegration = new SlackIntegration($this->db);
    }

    /**
     * Show integrations dashboard
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        // Check which integrations are connected
        $googleConnected = $this->googleIntegration->hasIntegration($userId);
        $microsoftConnected = $this->microsoftIntegration->hasIntegration($userId);
        $slackConnected = $this->slackIntegration->hasIntegration($userId);

        include __DIR__ . '/../Views/integrations.php';
    }

    /**
     * Google OAuth callback
     */
    public function googleCallback() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;

        if (!$code || !$state) {
            Session::set('error', 'Invalid OAuth callback parameters');
            header("Location: /integrations");
            exit;
        }

        try {
            $this->googleIntegration->exchangeCodeForTokens($code, $state);
            Session::set('success', 'Google integration connected successfully!');
        } catch (Exception $e) {
            Session::set('error', 'Failed to connect Google integration: ' . $e->getMessage());
        }

        header("Location: /integrations");
        exit;
    }

    /**
     * Microsoft OAuth callback
     */
    public function microsoftCallback() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;

        if (!$code || !$state) {
            Session::set('error', 'Invalid OAuth callback parameters');
            header("Location: /integrations");
            exit;
        }

        try {
            $this->microsoftIntegration->exchangeCodeForTokens($code, $state);
            Session::set('success', 'Microsoft integration connected successfully!');
        } catch (Exception $e) {
            Session::set('error', 'Failed to connect Microsoft integration: ' . $e->getMessage());
        }

        header("Location: /integrations");
        exit;
    }

    /**
     * Slack OAuth callback
     */
    public function slackCallback() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;

        if (!$code || !$state) {
            Session::set('error', 'Invalid OAuth callback parameters');
            header("Location: /integrations");
            exit;
        }

        try {
            $this->slackIntegration->exchangeCodeForTokens($code, $state);
            Session::set('success', 'Slack integration connected successfully!');
        } catch (Exception $e) {
            Session::set('error', 'Failed to connect Slack integration: ' . $e->getMessage());
        }

        header("Location: /integrations");
        exit;
    }

    /**
     * Disconnect Google integration
     */
    public function disconnectGoogle() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $result = $this->googleIntegration->disconnect($userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Google integration disconnected successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disconnect Google integration']);
        }
        exit;
    }

    /**
     * Disconnect Microsoft integration
     */
    public function disconnectMicrosoft() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $result = $this->microsoftIntegration->disconnect($userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Microsoft integration disconnected successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disconnect Microsoft integration']);
        }
        exit;
    }

    /**
     * Disconnect Slack integration
     */
    public function disconnectSlack() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $result = $this->slackIntegration->disconnect($userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Slack integration disconnected successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disconnect Slack integration']);
        }
        exit;
    }

    /**
     * Get Google profile
     */
    public function getGoogleProfile() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');

        try {
            $profile = $this->googleIntegration->getUserProfile($userId);
            echo json_encode(['success' => true, 'profile' => $profile]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get Microsoft profile
     */
    public function getMicrosoftProfile() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');

        try {
            $profile = $this->microsoftIntegration->getUserProfile($userId);
            echo json_encode(['success' => true, 'profile' => $profile]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get Slack team info
     */
    public function getSlackTeamInfo() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');

        try {
            $teamInfo = $this->slackIntegration->getTeamInfo($userId);
            echo json_encode(['success' => true, 'team' => $teamInfo]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Upload file to Google Drive
     */
    public function uploadToGoogleDrive() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['file_path']) || !isset($data['file_name'])) {
            echo json_encode(['success' => false, 'message' => 'File path and name required']);
            exit;
        }

        try {
            $result = $this->googleIntegration->uploadToDrive(
                $userId,
                $data['file_path'],
                $data['file_name'],
                $data['folder_id'] ?? null
            );
            echo json_encode(['success' => true, 'file' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Upload file to OneDrive
     */
    public function uploadToOneDrive() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['file_path']) || !isset($data['file_name'])) {
            echo json_encode(['success' => false, 'message' => 'File path and name required']);
            exit;
        }

        try {
            $result = $this->microsoftIntegration->uploadToOneDrive(
                $userId,
                $data['file_path'],
                $data['file_name'],
                $data['folder_id'] ?? null
            );
            echo json_encode(['success' => true, 'file' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Send Slack message
     */
    public function sendSlackMessage() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['channel']) || !isset($data['message'])) {
            echo json_encode(['success' => false, 'message' => 'Channel and message required']);
            exit;
        }

        try {
            $result = $this->slackIntegration->sendMessage(
                $userId,
                $data['channel'],
                $data['message'],
                $data['attachments'] ?? []
            );
            echo json_encode(['success' => true, 'message' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get Slack channels
     */
    public function getSlackChannels() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');

        try {
            $channels = $this->slackIntegration->getChannels($userId);
            echo json_encode(['success' => true, 'channels' => $channels]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Create calendar event
     */
    public function createCalendarEvent() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['provider']) || !isset($data['event_data'])) {
            echo json_encode(['success' => false, 'message' => 'Provider and event data required']);
            exit;
        }

        try {
            if ($data['provider'] === 'google') {
                $result = $this->googleIntegration->createCalendarEvent($userId, $data['event_data']);
            } elseif ($data['provider'] === 'microsoft') {
                $result = $this->microsoftIntegration->createCalendarEvent($userId, $data['event_data']);
            } else {
                throw new Exception('Unsupported provider');
            }
            
            echo json_encode(['success' => true, 'event' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
