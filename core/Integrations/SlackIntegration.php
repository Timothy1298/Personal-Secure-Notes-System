<?php
namespace Core\Integrations;

use PDO;
use Exception;

class SlackIntegration {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['SLACK_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['SLACK_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['SLACK_REDIRECT_URI'] ?? '';
    }
    
    /**
     * Get Slack OAuth URL
     */
    public function getAuthUrl($userId, $scopes = []) {
        $defaultScopes = [
            'chat:write',
            'files:write',
            'channels:read',
            'groups:read',
            'im:read',
            'mpim:read',
            'users:read'
        ];
        
        $scopes = array_merge($defaultScopes, $scopes);
        $state = base64_encode(json_encode(['user_id' => $userId, 'timestamp' => time()]));
        
        $params = [
            'client_id' => $this->clientId,
            'scope' => implode(',', $scopes),
            'redirect_uri' => $this->redirectUri,
            'state' => $state
        ];
        
        return 'https://slack.com/oauth/v2/authorize?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens($code, $state) {
        $stateData = json_decode(base64_decode($state), true);
        $userId = $stateData['user_id'] ?? null;
        
        if (!$userId) {
            throw new Exception('Invalid state parameter');
        }
        
        $tokenData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/oauth.v2.access');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to exchange code for tokens: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        // Save tokens to database
        $this->saveTokens($userId, $result);
        
        return $result;
    }
    
    /**
     * Save tokens to database
     */
    private function saveTokens($userId, $result) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO slack_integrations 
                (user_id, access_token, team_id, team_name, authed_user_id, scope, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                access_token = VALUES(access_token),
                team_id = VALUES(team_id),
                team_name = VALUES(team_name),
                authed_user_id = VALUES(authed_user_id),
                scope = VALUES(scope),
                updated_at = NOW()
            ");
            
            $stmt->execute([
                $userId,
                $result['access_token'],
                $result['team']['id'] ?? null,
                $result['team']['name'] ?? null,
                $result['authed_user']['id'] ?? null,
                $result['scope'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Error saving Slack tokens: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get access token
     */
    public function getAccessToken($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT access_token FROM slack_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['access_token'] : null;
        } catch (Exception $e) {
            error_log("Error getting Slack access token: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send message to Slack channel
     */
    public function sendMessage($userId, $channel, $message, $attachments = []) {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $data = [
            'channel' => $channel,
            'text' => $message
        ];
        
        if (!empty($attachments)) {
            $data['attachments'] = json_encode($attachments);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/chat.postMessage');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to send Slack message: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result;
    }
    
    /**
     * Upload file to Slack
     */
    public function uploadFile($userId, $filePath, $channels, $title = null, $comment = null) {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $data = [
            'channels' => is_array($channels) ? implode(',', $channels) : $channels,
            'token' => $accessToken
        ];
        
        if ($title) {
            $data['title'] = $title;
        }
        
        if ($comment) {
            $data['initial_comment'] = $comment;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/files.upload');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, [
            'file' => new \CURLFile($filePath)
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to upload file to Slack: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result;
    }
    
    /**
     * Get list of channels
     */
    public function getChannels($userId, $types = 'public_channel,private_channel') {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $params = [
            'token' => $accessToken,
            'types' => $types,
            'limit' => 1000
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/conversations.list?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get Slack channels: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result['channels'];
    }
    
    /**
     * Get list of users
     */
    public function getUsers($userId) {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $params = [
            'token' => $accessToken,
            'limit' => 1000
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/users.list?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get Slack users: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result['members'];
    }
    
    /**
     * Create a reminder
     */
    public function createReminder($userId, $text, $time) {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $data = [
            'token' => $accessToken,
            'text' => $text,
            'time' => $time
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/reminders.add');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to create Slack reminder: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result;
    }
    
    /**
     * Get team information
     */
    public function getTeamInfo($userId) {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $params = [
            'token' => $accessToken
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/team.info?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get Slack team info: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            throw new Exception('Slack API error: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return $result['team'];
    }
    
    /**
     * Disconnect Slack integration
     */
    public function disconnect($userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM slack_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error disconnecting Slack integration: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has Slack integration
     */
    public function hasIntegration($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM slack_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error checking Slack integration: " . $e->getMessage());
            return false;
        }
    }
}
