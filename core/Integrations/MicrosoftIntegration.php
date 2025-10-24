<?php
namespace Core\Integrations;

use PDO;
use Exception;

class MicrosoftIntegration {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $tenantId;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['MICROSOFT_REDIRECT_URI'] ?? '';
        $this->tenantId = $_ENV['MICROSOFT_TENANT_ID'] ?? 'common';
    }
    
    /**
     * Get Microsoft OAuth URL
     */
    public function getAuthUrl($userId, $scopes = []) {
        $defaultScopes = [
            'https://graph.microsoft.com/User.Read',
            'https://graph.microsoft.com/Files.ReadWrite',
            'https://graph.microsoft.com/Calendars.ReadWrite'
        ];
        
        $scopes = array_merge($defaultScopes, $scopes);
        $state = base64_encode(json_encode(['user_id' => $userId, 'timestamp' => time()]));
        
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'response_mode' => 'query'
        ];
        
        return "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
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
        curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token");
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
                INSERT INTO microsoft_integrations 
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
            error_log("Error saving Microsoft tokens: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get valid access token
     */
    public function getValidAccessToken($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM microsoft_integrations WHERE user_id = ?
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
            curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token");
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
        curl_setopt($ch, CURLOPT_URL, 'https://graph.microsoft.com/v1.0/me');
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
     * Upload file to OneDrive
     */
    public function uploadToOneDrive($userId, $filePath, $fileName, $folderId = null) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $endpoint = $folderId 
            ? "https://graph.microsoft.com/v1.0/me/drive/items/{$folderId}/children/{$fileName}/content"
            : "https://graph.microsoft.com/v1.0/me/drive/root:/{$fileName}:/content";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, fopen($filePath, 'r'));
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: ' . mime_content_type($filePath)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('Failed to upload file to OneDrive: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Download file from OneDrive
     */
    public function downloadFromOneDrive($userId, $fileId) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/drive/items/{$fileId}/content");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to download file from OneDrive: ' . $response);
        }
        
        return $response;
    }
    
    /**
     * List files in OneDrive
     */
    public function listOneDriveFiles($userId, $folderId = null, $pageSize = 100) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $endpoint = $folderId 
            ? "https://graph.microsoft.com/v1.0/me/drive/items/{$folderId}/children"
            : "https://graph.microsoft.com/v1.0/me/drive/root/children";
        
        $params = [
            'top' => $pageSize,
            'select' => 'id,name,size,createdDateTime,lastModifiedDateTime,file'
        ];
        
        $url = $endpoint . '?' . http_build_query($params);
        
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
            throw new Exception('Failed to list OneDrive files: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Create Outlook calendar event
     */
    public function createCalendarEvent($userId, $eventData) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.microsoft.com/v1.0/me/events');
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
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('Failed to create calendar event: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Send email via Outlook
     */
    public function sendEmail($userId, $emailData) {
        $accessToken = $this->getValidAccessToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.microsoft.com/v1.0/me/sendMail');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 && $httpCode !== 202) {
            throw new Exception('Failed to send email: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Disconnect Microsoft integration
     */
    public function disconnect($userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM microsoft_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error disconnecting Microsoft integration: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has Microsoft integration
     */
    public function hasIntegration($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM microsoft_integrations WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error checking Microsoft integration: " . $e->getMessage());
            return false;
        }
    }
}
