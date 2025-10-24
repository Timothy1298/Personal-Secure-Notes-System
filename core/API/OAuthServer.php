<?php
namespace Core\API;

use PDO;
use Exception;

class OAuthServer {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $accessTokenExpiry = 3600; // 1 hour
    private $refreshTokenExpiry = 2592000; // 30 days
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clientId = $_ENV['OAUTH_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['OAUTH_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['OAUTH_REDIRECT_URI'] ?? '';
    }
    
    /**
     * Authorize client
     */
    public function authorize($responseType, $clientId, $redirectUri, $scope, $state) {
        // Validate client
        if (!$this->validateClient($clientId, $redirectUri)) {
            throw new Exception('Invalid client');
        }
        
        // Validate response type
        if ($responseType !== 'code') {
            throw new Exception('Unsupported response type');
        }
        
        // Generate authorization code
        $code = $this->generateAuthorizationCode($clientId, $scope);
        
        // Store authorization code
        $this->storeAuthorizationCode($code, $clientId, $scope);
        
        return [
            'code' => $code,
            'state' => $state
        ];
    }
    
    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens($code, $clientId, $clientSecret, $redirectUri) {
        // Validate client
        if (!$this->validateClient($clientId, $redirectUri)) {
            throw new Exception('Invalid client');
        }
        
        if ($clientSecret !== $this->clientSecret) {
            throw new Exception('Invalid client secret');
        }
        
        // Validate authorization code
        $authCode = $this->getAuthorizationCode($code);
        if (!$authCode) {
            throw new Exception('Invalid authorization code');
        }
        
        if ($authCode['client_id'] !== $clientId) {
            throw new Exception('Authorization code mismatch');
        }
        
        if (new \DateTime() > new \DateTime($authCode['expires_at'])) {
            throw new Exception('Authorization code expired');
        }
        
        // Generate tokens
        $accessToken = $this->generateAccessToken();
        $refreshToken = $this->generateRefreshToken();
        
        // Store tokens
        $this->storeTokens($accessToken, $refreshToken, $authCode['user_id'], $authCode['scope']);
        
        // Delete authorization code
        $this->deleteAuthorizationCode($code);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenExpiry,
            'scope' => $authCode['scope']
        ];
    }
    
    /**
     * Refresh access token
     */
    public function refreshAccessToken($refreshToken, $clientId, $clientSecret) {
        // Validate client
        if ($clientId !== $this->clientId || $clientSecret !== $this->clientSecret) {
            throw new Exception('Invalid client');
        }
        
        // Validate refresh token
        $token = $this->getRefreshToken($refreshToken);
        if (!$token) {
            throw new Exception('Invalid refresh token');
        }
        
        if (new \DateTime() > new \DateTime($token['expires_at'])) {
            throw new Exception('Refresh token expired');
        }
        
        // Generate new access token
        $newAccessToken = $this->generateAccessToken();
        
        // Update access token
        $this->updateAccessToken($token['access_token'], $newAccessToken);
        
        return [
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenExpiry,
            'scope' => $token['scope']
        ];
    }
    
    /**
     * Validate access token
     */
    public function validateAccessToken($accessToken) {
        $stmt = $this->db->prepare("
            SELECT * FROM oauth_access_tokens 
            WHERE access_token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$accessToken]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$token) {
            return null;
        }
        
        return [
            'user_id' => $token['user_id'],
            'scope' => $token['scope'],
            'client_id' => $token['client_id']
        ];
    }
    
    /**
     * Revoke token
     */
    public function revokeToken($token, $tokenTypeHint = null) {
        if ($tokenTypeHint === 'refresh_token' || !$tokenTypeHint) {
            $stmt = $this->db->prepare("DELETE FROM oauth_refresh_tokens WHERE refresh_token = ?");
            $stmt->execute([$token]);
        }
        
        if ($tokenTypeHint === 'access_token' || !$tokenTypeHint) {
            $stmt = $this->db->prepare("DELETE FROM oauth_access_tokens WHERE access_token = ?");
            $stmt->execute([$token]);
        }
        
        return true;
    }
    
    /**
     * Get token info
     */
    public function getTokenInfo($accessToken) {
        $stmt = $this->db->prepare("
            SELECT 
                at.access_token,
                at.scope,
                at.expires_at,
                at.user_id,
                at.client_id,
                u.username,
                u.email
            FROM oauth_access_tokens at
            JOIN users u ON at.user_id = u.id
            WHERE at.access_token = ?
        ");
        $stmt->execute([$accessToken]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$token) {
            return null;
        }
        
        return [
            'access_token' => $token['access_token'],
            'scope' => $token['scope'],
            'expires_in' => strtotime($token['expires_at']) - time(),
            'user_id' => $token['user_id'],
            'username' => $token['username'],
            'email' => $token['email']
        ];
    }
    
    /**
     * Validate client
     */
    private function validateClient($clientId, $redirectUri) {
        return $clientId === $this->clientId && $redirectUri === $this->redirectUri;
    }
    
    /**
     * Generate authorization code
     */
    private function generateAuthorizationCode($clientId, $scope) {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Store authorization code
     */
    private function storeAuthorizationCode($code, $clientId, $scope) {
        $stmt = $this->db->prepare("
            INSERT INTO oauth_authorization_codes 
            (code, client_id, user_id, scope, expires_at, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
        
        $stmt->execute([
            $code,
            $clientId,
            $_SESSION['user_id'] ?? 1, // In production, get from authentication
            $scope,
            $expiresAt
        ]);
    }
    
    /**
     * Get authorization code
     */
    private function getAuthorizationCode($code) {
        $stmt = $this->db->prepare("
            SELECT * FROM oauth_authorization_codes 
            WHERE code = ?
        ");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete authorization code
     */
    private function deleteAuthorizationCode($code) {
        $stmt = $this->db->prepare("DELETE FROM oauth_authorization_codes WHERE code = ?");
        $stmt->execute([$code]);
    }
    
    /**
     * Generate access token
     */
    private function generateAccessToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Generate refresh token
     */
    private function generateRefreshToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Store tokens
     */
    private function storeTokens($accessToken, $refreshToken, $userId, $scope) {
        $accessExpiresAt = date('Y-m-d H:i:s', time() + $this->accessTokenExpiry);
        $refreshExpiresAt = date('Y-m-d H:i:s', time() + $this->refreshTokenExpiry);
        
        // Store access token
        $stmt = $this->db->prepare("
            INSERT INTO oauth_access_tokens 
            (access_token, user_id, client_id, scope, expires_at, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $accessToken,
            $userId,
            $this->clientId,
            $scope,
            $accessExpiresAt
        ]);
        
        // Store refresh token
        $stmt = $this->db->prepare("
            INSERT INTO oauth_refresh_tokens 
            (refresh_token, access_token, user_id, client_id, scope, expires_at, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $refreshToken,
            $accessToken,
            $userId,
            $this->clientId,
            $scope,
            $refreshExpiresAt
        ]);
    }
    
    /**
     * Get refresh token
     */
    private function getRefreshToken($refreshToken) {
        $stmt = $this->db->prepare("
            SELECT * FROM oauth_refresh_tokens 
            WHERE refresh_token = ?
        ");
        $stmt->execute([$refreshToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update access token
     */
    private function updateAccessToken($oldAccessToken, $newAccessToken) {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->accessTokenExpiry);
        
        $stmt = $this->db->prepare("
            UPDATE oauth_access_tokens 
            SET access_token = ?, expires_at = ?, updated_at = NOW() 
            WHERE access_token = ?
        ");
        $stmt->execute([$newAccessToken, $expiresAt, $oldAccessToken]);
    }
    
    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens() {
        // Delete expired access tokens
        $stmt = $this->db->prepare("DELETE FROM oauth_access_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        
        // Delete expired refresh tokens
        $stmt = $this->db->prepare("DELETE FROM oauth_refresh_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        
        // Delete expired authorization codes
        $stmt = $this->db->prepare("DELETE FROM oauth_authorization_codes WHERE expires_at < NOW()");
        $stmt->execute();
    }
    
    /**
     * Get user's active tokens
     */
    public function getUserTokens($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                at.access_token,
                at.scope,
                at.expires_at,
                at.created_at
            FROM oauth_access_tokens at
            WHERE at.user_id = ? AND at.expires_at > NOW()
            ORDER BY at.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Revoke all user tokens
     */
    public function revokeAllUserTokens($userId) {
        $stmt = $this->db->prepare("DELETE FROM oauth_access_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $stmt = $this->db->prepare("DELETE FROM oauth_refresh_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return true;
    }
}
