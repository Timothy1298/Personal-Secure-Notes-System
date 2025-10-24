<?php
namespace Core\Integrations;

use PDO;
use Exception;

class GoogleIntegration {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';
    }
    
    /**
     * Get Google OAuth URL
     */
    public function getAuthUrl($userId, $scopes = []) {
        $defaultScopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/drive.file'
        ];
        
        $scopes = array_merge($defaultScopes, $scopes);
        $state = base64_encode(json_encode(['user_id' => $userId, 'timestamp' => time()]));
        
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
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
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
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
        
        $tokens = json_decode($response, true);
        
        // Save tokens to database
        $this->saveTokens($userId, $tokens);
        
        return $tokens;
    }
    
    /**
     * Save tokens to database
     */
    private function saveTokens($userId, $tokens) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO google_integrations 
                (user_id, access_token, refresh_token, token_type, expires_at, scope, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                token_type = VALUES(token_type),
                expires_at = VALUES(expires_at),
                scope = VALUES(scope),
                updated_at = NOW()
            ");
            
            $expiresAt = null;
            if (isset($tokens['expires_in'])) {
                $expiresAt = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
            }
            
            $stmt->execute([
                $userId,
                $tokens['access_token'],
                $tokens['refresh_token'] ?? null,
                $tokens['token_type'] ?? 'Bearer',
                $expiresAt,
                $tokens['scope'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Error saving Google tokens: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get valid access token
     */
    public function getValidAccessToken($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM google_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $integration = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$integration) {
                return null;
            }
            
            // Check if token is expired
            if ($integration['expires_at'] && new \DateTime() > new \DateTime($integration['expires_at'])) {
                // Refresh token
                return $this->refreshAccessToken($userId, $integration['refresh_token']);
            }
            
            return $integration['access_token'];
        } catch (Exception $e) {
            error_log("Error getting valid access token: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Refresh access token
     */
    private function refreshAccessToken($userId, $refreshToken) {
        try {
            $tokenData = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
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
                throw new Exception('Failed to refresh token: ' . $response);
            }
            
            $tokens = json_decode($response, true);
            
            // Update tokens in database
            $this->saveTokens($userId, $tokens);
            
            return $tokens['access_token'];
        } catch (Exception $e) {
            error_log("Error refreshing access token: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user profile
     */
    public function getUserProfile($userId) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get user profile: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Upload file to Google Drive
     */
    public function uploadToDrive($userId, $filePath, $fileName, $folderId = null) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $metadata = [
            'name' => $fileName
        ];
        
        if ($folderId) {
            $metadata['parents'] = [$folderId];
        }
        
        $boundary = uniqid();
        $delimiter = '--' . $boundary;
        
        $postBody = $delimiter . "\r\n";
        $postBody .= 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n";
        $postBody .= json_encode($metadata) . "\r\n";
        $postBody .= $delimiter . "\r\n";
        $postBody .= 'Content-Type: ' . mime_content_type($filePath) . "\r\n\r\n";
        $postBody .= file_get_contents($filePath) . "\r\n";
        $postBody .= $delimiter . '--';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: multipart/related; boundary=' . $boundary
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to upload file to Google Drive: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Download file from Google Drive
     */
    public function downloadFromDrive($userId, $fileId) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to download file from Google Drive: ' . $response);
        }
        
        return $response;
    }
    
    /**
     * List files in Google Drive
     */
    public function listDriveFiles($userId, $folderId = null, $pageSize = 100) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $params = [
            'pageSize' => $pageSize,
            'fields' => 'files(id,name,mimeType,size,createdTime,modifiedTime,parents)'
        ];
        
        if ($folderId) {
            $params['q'] = "'{$folderId}' in parents";
        }
        
        $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to list Google Drive files: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Create Google Calendar event
     */
    public function createCalendarEvent($userId, $eventData) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/calendars/primary/events');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to create calendar event: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Disconnect Google integration
     */
    public function disconnect($userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM google_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error disconnecting Google integration: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has Google integration
     */
    public function hasIntegration($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM google_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error checking Google integration: " . $e->getMessage());
            return false;
        }
    }
}
