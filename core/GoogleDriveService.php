<?php
namespace Core;

use PDO;
use Exception;

class GoogleDriveService {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scopes;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '1056131269048-i5ibcufnobb547cppbjd96b7c5i39efr.apps.googleusercontent.com';
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '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';
        $this->redirectUri = $_ENV['GOOGLE_DRIVE_REDIRECT_URI'] ?? 'http://localhost:3000/cloud-integration/google-drive/callback';
        $this->scopes = [
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.metadata.readonly'
        ];
    }
    
    /**
     * Get the Google OAuth authorization URL
     */
    public function getAuthorizationUrl(int $userId): string {
        $state = base64_encode(json_encode([
            'user_id' => $userId,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16))
        ]));
        
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $state): array {
        $stateData = json_decode(base64_decode($state), true);
        if (!$stateData || !isset($stateData['user_id'])) {
            throw new Exception('Invalid state parameter');
        }
        
        $tokenData = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $response = $this->makeHttpRequest('https://oauth2.googleapis.com/token', $tokenData);
        
        if (isset($response['error'])) {
            throw new Exception('Token exchange failed: ' . $response['error_description']);
        }
        
        // Store tokens in database
        $this->storeTokens($stateData['user_id'], $response);
        
        return $response;
    }
    
    /**
     * Store access and refresh tokens
     */
    private function storeTokens(int $userId, array $tokenData): void {
        $stmt = $this->db->prepare("
            INSERT INTO user_cloud_tokens (user_id, provider, access_token, refresh_token, expires_at, created_at)
            VALUES (?, 'google_drive', ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            access_token = VALUES(access_token),
            refresh_token = VALUES(refresh_token),
            expires_at = VALUES(expires_at),
            updated_at = NOW()
        ");
        
        $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        $stmt->execute([
            $userId,
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expiresAt
        ]);
    }
    
    /**
     * Get valid access token for user
     */
    public function getValidToken(int $userId): ?string {
        $stmt = $this->db->prepare("
            SELECT access_token, refresh_token, expires_at 
            FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'google_drive'
        ");
        $stmt->execute([$userId]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            return null;
        }
        
        // Check if token is expired
        if (strtotime($tokenData['expires_at']) <= time()) {
            if ($tokenData['refresh_token']) {
                $newToken = $this->refreshAccessToken($userId, $tokenData['refresh_token']);
                return $newToken ? $newToken['access_token'] : null;
            }
            return null;
        }
        
        return $tokenData['access_token'];
    }
    
    /**
     * Refresh access token using refresh token
     */
    private function refreshAccessToken(int $userId, string $refreshToken): ?array {
        $tokenData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ];
        
        $response = $this->makeHttpRequest('https://oauth2.googleapis.com/token', $tokenData);
        
        if (isset($response['error'])) {
            error_log("Google Drive token refresh failed: " . $response['error_description']);
            return null;
        }
        
        // Update stored tokens
        $this->storeTokens($userId, $response);
        
        return $response;
    }
    
    /**
     * Upload file to Google Drive
     */
    public function uploadFile(int $userId, string $filePath, string $fileName, string $mimeType = 'application/octet-stream'): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new Exception('Failed to read file content');
        }
        
        // Create file metadata
        $metadata = [
            'name' => $fileName,
            'parents' => ['appDataFolder'] // Store in app-specific folder
        ];
        
        // Upload file using multipart upload
        $boundary = uniqid();
        $delimiter = '--' . $boundary;
        
        $body = $delimiter . "\r\n";
        $body .= "Content-Type: application/json\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= $delimiter . "\r\n";
        $body .= "Content-Type: " . $mimeType . "\r\n\r\n";
        $body .= $fileContent . "\r\n";
        $body .= $delimiter . "--\r\n";
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: multipart/related; boundary=' . $boundary
        ];
        
        $response = $this->makeHttpRequest(
            'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
            $body,
            $headers,
            'POST'
        );
        
        if (isset($response['error'])) {
            throw new Exception('File upload failed: ' . $response['error']['message']);
        }
        
        return $response;
    }
    
    /**
     * Download file from Google Drive
     */
    public function downloadFile(int $userId, string $fileId): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        $response = $this->makeHttpRequest(
            'https://www.googleapis.com/drive/v3/files/' . $fileId . '?alt=media',
            null,
            $headers,
            'GET',
            true // Return raw response for file download
        );
        
        return $response;
    }
    
    /**
     * List files in Google Drive
     */
    public function listFiles(int $userId, int $pageSize = 100): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $params = [
            'pageSize' => $pageSize,
            'fields' => 'files(id,name,size,createdTime,modifiedTime,mimeType)',
            'q' => "parents in 'appDataFolder'"
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        $response = $this->makeHttpRequest(
            'https://www.googleapis.com/drive/v3/files?' . http_build_query($params),
            null,
            $headers,
            'GET'
        );
        
        if (isset($response['error'])) {
            throw new Exception('Failed to list files: ' . $response['error']['message']);
        }
        
        return $response['files'] ?? [];
    }
    
    /**
     * Delete file from Google Drive
     */
    public function deleteFile(int $userId, string $fileId): bool {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        $response = $this->makeHttpRequest(
            'https://www.googleapis.com/drive/v3/files/' . $fileId,
            null,
            $headers,
            'DELETE'
        );
        
        return !isset($response['error']);
    }
    
    /**
     * Disconnect Google Drive account
     */
    public function disconnect(int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'google_drive'
        ");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Check if user has Google Drive connected
     */
    public function isConnected(int $userId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'google_drive'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Make HTTP request to Google APIs
     */
    private function makeHttpRequest(string $url, $data = null, array $headers = [], string $method = 'POST', bool $returnRaw = false) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('HTTP request failed: ' . $error);
        }
        
        if ($returnRaw) {
            return [
                'content' => $response,
                'http_code' => $httpCode
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $error = $decodedResponse['error'] ?? ['message' => 'HTTP ' . $httpCode];
            throw new Exception('API request failed: ' . $error['message']);
        }
        
        return $decodedResponse;
    }
}

