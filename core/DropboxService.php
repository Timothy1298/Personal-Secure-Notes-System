<?php
namespace Core;

use PDO;
use Exception;

class DropboxService {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scopes;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['DROPBOX_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['DROPBOX_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['DROPBOX_REDIRECT_URI'] ?? 'http://localhost:3000/cloud-integration/dropbox/callback';
        $this->scopes = [
            'files.metadata.write',
            'files.metadata.read',
            'files.content.write',
            'files.content.read'
        ];
    }
    
    /**
     * Get the Dropbox OAuth authorization URL
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
            'response_type' => 'code',
            'state' => $state
        ];
        
        return 'https://www.dropbox.com/oauth2/authorize?' . http_build_query($params);
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
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri
        ];
        
        $response = $this->makeHttpRequest('https://api.dropboxapi.com/oauth2/token', $tokenData);
        
        if (isset($response['error'])) {
            throw new Exception('Token exchange failed: ' . $response['error_description']);
        }
        
        // Store tokens in database
        $this->storeTokens($stateData['user_id'], $response);
        
        return $response;
    }
    
    /**
     * Store access token
     */
    private function storeTokens(int $userId, array $tokenData): void {
        $stmt = $this->db->prepare("
            INSERT INTO user_cloud_tokens (user_id, provider, access_token, refresh_token, expires_at, created_at)
            VALUES (?, 'dropbox', ?, NULL, NULL, NOW())
            ON DUPLICATE KEY UPDATE
            access_token = VALUES(access_token),
            updated_at = NOW()
        ");
        
        $stmt->execute([
            $userId,
            $tokenData['access_token']
        ]);
    }
    
    /**
     * Get valid access token for user
     */
    public function getValidToken(int $userId): ?string {
        $stmt = $this->db->prepare("
            SELECT access_token 
            FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'dropbox'
        ");
        $stmt->execute([$userId]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $tokenData ? $tokenData['access_token'] : null;
    }
    
    /**
     * Upload file to Dropbox
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
        
        // Dropbox API path for the file
        $dropboxPath = '/SecureNotePro/' . $fileName;
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: ' . json_encode(['path' => $dropboxPath, 'mode' => 'add', 'autorename' => true])
        ];
        
        $response = $this->makeHttpRequest(
            'https://content.dropboxapi.com/2/files/upload',
            $fileContent,
            $headers,
            'POST',
            true
        );
        
        if (isset($response['error'])) {
            throw new Exception('File upload failed: ' . $response['error_summary']);
        }
        
        return $response;
    }
    
    /**
     * Download file from Dropbox
     */
    public function downloadFile(int $userId, string $fileId): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Dropbox-API-Arg: ' . json_encode(['path' => $fileId])
        ];
        
        $response = $this->makeHttpRequest(
            'https://content.dropboxapi.com/2/files/download',
            null,
            $headers,
            'POST',
            true
        );
        
        return $response;
    }
    
    /**
     * List files in Dropbox
     */
    public function listFiles(int $userId, int $limit = 100): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $requestData = [
            'path' => '/SecureNotePro',
            'recursive' => false,
            'include_media_info' => false,
            'include_deleted' => false,
            'include_has_explicit_shared_members' => false,
            'include_mounted_folders' => true,
            'limit' => $limit
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest(
            'https://api.dropboxapi.com/2/files/list_folder',
            json_encode($requestData),
            $headers,
            'POST'
        );
        
        if (isset($response['error'])) {
            throw new Exception('Failed to list files: ' . $response['error_summary']);
        }
        
        return $response['entries'] ?? [];
    }
    
    /**
     * Delete file from Dropbox
     */
    public function deleteFile(int $userId, string $filePath): bool {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $requestData = [
            'path' => $filePath
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest(
            'https://api.dropboxapi.com/2/files/delete_v2',
            json_encode($requestData),
            $headers,
            'POST'
        );
        
        return !isset($response['error']);
    }
    
    /**
     * Get file metadata
     */
    public function getFileMetadata(int $userId, string $filePath): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $requestData = [
            'path' => $filePath,
            'include_media_info' => false,
            'include_deleted' => false,
            'include_has_explicit_shared_members' => false
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest(
            'https://api.dropboxapi.com/2/files/get_metadata',
            json_encode($requestData),
            $headers,
            'POST'
        );
        
        if (isset($response['error'])) {
            throw new Exception('Failed to get file metadata: ' . $response['error_summary']);
        }
        
        return $response;
    }
    
    /**
     * Create folder in Dropbox
     */
    public function createFolder(int $userId, string $folderPath): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $requestData = [
            'path' => $folderPath,
            'autorename' => false
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest(
            'https://api.dropboxapi.com/2/files/create_folder_v2',
            json_encode($requestData),
            $headers,
            'POST'
        );
        
        if (isset($response['error'])) {
            throw new Exception('Failed to create folder: ' . $response['error_summary']);
        }
        
        return $response;
    }
    
    /**
     * Get account info
     */
    public function getAccountInfo(int $userId): array {
        $accessToken = $this->getValidToken($userId);
        if (!$accessToken) {
            throw new Exception('No valid access token available');
        }
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest(
            'https://api.dropboxapi.com/2/users/get_current_account',
            null,
            $headers,
            'POST'
        );
        
        if (isset($response['error'])) {
            throw new Exception('Failed to get account info: ' . $response['error_summary']);
        }
        
        return $response;
    }
    
    /**
     * Disconnect Dropbox account
     */
    public function disconnect(int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'dropbox'
        ");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Check if user has Dropbox connected
     */
    public function isConnected(int $userId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM user_cloud_tokens 
            WHERE user_id = ? AND provider = 'dropbox'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Make HTTP request to Dropbox API
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
            $error = $decodedResponse['error'] ?? ['error_summary' => 'HTTP ' . $httpCode];
            throw new Exception('API request failed: ' . $error['error_summary']);
        }
        
        return $decodedResponse;
    }
}
