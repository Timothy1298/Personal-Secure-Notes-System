<?php
namespace Core\Enterprise;

use PDO;
use Exception;

class SSOProvider {
    private $db;
    private $config;
    
    public function __construct(PDO $db, array $config = []) {
        $this->db = $db;
        $this->config = array_merge([
            'saml' => [
                'enabled' => false,
                'entity_id' => '',
                'sso_url' => '',
                'slo_url' => '',
                'certificate' => '',
                'private_key' => ''
            ],
            'oauth' => [
                'enabled' => false,
                'providers' => [
                    'google' => [
                        'client_id' => '',
                        'client_secret' => '',
                        'redirect_uri' => ''
                    ],
                    'microsoft' => [
                        'client_id' => '',
                        'client_secret' => '',
                        'redirect_uri' => ''
                    ],
                    'azure' => [
                        'client_id' => '',
                        'client_secret' => '',
                        'tenant_id' => '',
                        'redirect_uri' => ''
                    ]
                ]
            ],
            'ldap' => [
                'enabled' => false,
                'host' => '',
                'port' => 389,
                'base_dn' => '',
                'bind_dn' => '',
                'bind_password' => '',
                'user_search_base' => '',
                'user_search_filter' => '(uid=%s)',
                'group_search_base' => '',
                'group_search_filter' => '(member=%s)'
            ]
        ], $config);
    }
    
    /**
     * Authenticate user via SAML
     */
    public function authenticateSAML(string $samlResponse): array {
        try {
            if (!$this->config['saml']['enabled']) {
                throw new Exception('SAML authentication is not enabled');
            }
            
            // Parse SAML response (simplified - in production use proper SAML library)
            $samlData = $this->parseSAMLResponse($samlResponse);
            
            if (!$samlData) {
                throw new Exception('Invalid SAML response');
            }
            
            // Extract user information
            $email = $samlData['email'] ?? null;
            $username = $samlData['username'] ?? null;
            $firstName = $samlData['first_name'] ?? '';
            $lastName = $samlData['last_name'] ?? '';
            $groups = $samlData['groups'] ?? [];
            
            if (!$email) {
                throw new Exception('Email not found in SAML response');
            }
            
            // Find or create user
            $user = $this->findOrCreateSSOUser($email, $username, $firstName, $lastName, 'saml');
            
            // Update user groups
            $this->updateUserGroups($user['id'], $groups);
            
            // Create SSO session
            $sessionId = $this->createSSOSession($user['id'], 'saml', $samlData);
            
            return [
                'success' => true,
                'user' => $user,
                'session_id' => $sessionId,
                'redirect_url' => '/dashboard'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Authenticate user via OAuth
     */
    public function authenticateOAuth(string $provider, string $code): array {
        try {
            if (!$this->config['oauth']['enabled']) {
                throw new Exception('OAuth authentication is not enabled');
            }
            
            if (!isset($this->config['oauth']['providers'][$provider])) {
                throw new Exception("OAuth provider '{$provider}' is not configured");
            }
            
            $providerConfig = $this->config['oauth']['providers'][$provider];
            
            // Exchange code for access token
            $tokenData = $this->exchangeCodeForToken($provider, $code, $providerConfig);
            
            if (!$tokenData) {
                throw new Exception('Failed to exchange code for token');
            }
            
            // Get user information
            $userInfo = $this->getOAuthUserInfo($provider, $tokenData['access_token']);
            
            if (!$userInfo) {
                throw new Exception('Failed to get user information');
            }
            
            // Find or create user
            $user = $this->findOrCreateSSOUser(
                $userInfo['email'],
                $userInfo['username'],
                $userInfo['first_name'] ?? '',
                $userInfo['last_name'] ?? '',
                $provider
            );
            
            // Store OAuth tokens
            $this->storeOAuthTokens($user['id'], $provider, $tokenData);
            
            // Create SSO session
            $sessionId = $this->createSSOSession($user['id'], $provider, $userInfo);
            
            return [
                'success' => true,
                'user' => $user,
                'session_id' => $sessionId,
                'redirect_url' => '/dashboard'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Authenticate user via LDAP
     */
    public function authenticateLDAP(string $username, string $password): array {
        try {
            if (!$this->config['ldap']['enabled']) {
                throw new Exception('LDAP authentication is not enabled');
            }
            
            // Connect to LDAP server
            $ldap = $this->connectLDAP();
            
            if (!$ldap) {
                throw new Exception('Failed to connect to LDAP server');
            }
            
            // Search for user
            $userDN = $this->searchLDAPUser($ldap, $username);
            
            if (!$userDN) {
                throw new Exception('User not found in LDAP');
            }
            
            // Authenticate user
            $authenticated = $this->authenticateLDAPUser($ldap, $userDN, $password);
            
            if (!$authenticated) {
                throw new Exception('Invalid credentials');
            }
            
            // Get user information
            $userInfo = $this->getLDAPUserInfo($ldap, $userDN);
            
            // Get user groups
            $groups = $this->getLDAPUserGroups($ldap, $userDN);
            
            // Find or create user
            $user = $this->findOrCreateSSOUser(
                $userInfo['email'],
                $username,
                $userInfo['first_name'] ?? '',
                $userInfo['last_name'] ?? '',
                'ldap'
            );
            
            // Update user groups
            $this->updateUserGroups($user['id'], $groups);
            
            // Create SSO session
            $sessionId = $this->createSSOSession($user['id'], 'ldap', $userInfo);
            
            ldap_close($ldap);
            
            return [
                'success' => true,
                'user' => $user,
                'session_id' => $sessionId,
                'redirect_url' => '/dashboard'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get OAuth authorization URL
     */
    public function getOAuthAuthorizationUrl(string $provider): array {
        try {
            if (!$this->config['oauth']['enabled']) {
                throw new Exception('OAuth authentication is not enabled');
            }
            
            if (!isset($this->config['oauth']['providers'][$provider])) {
                throw new Exception("OAuth provider '{$provider}' is not configured");
            }
            
            $providerConfig = $this->config['oauth']['providers'][$provider];
            
            $authUrl = $this->buildOAuthAuthorizationUrl($provider, $providerConfig);
            
            return [
                'success' => true,
                'authorization_url' => $authUrl
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get SAML login URL
     */
    public function getSAMLLoginUrl(): array {
        try {
            if (!$this->config['saml']['enabled']) {
                throw new Exception('SAML authentication is not enabled');
            }
            
            $loginUrl = $this->buildSAMLLoginUrl();
            
            return [
                'success' => true,
                'login_url' => $loginUrl
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Logout from SSO
     */
    public function logoutSSO(string $sessionId, string $provider): array {
        try {
            // Get session information
            $stmt = $this->db->prepare("
                SELECT * FROM sso_sessions WHERE session_id = ? AND provider = ?
            ");
            $stmt->execute([$sessionId, $provider]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                throw new Exception('SSO session not found');
            }
            
            // Perform provider-specific logout
            switch ($provider) {
                case 'saml':
                    $this->performSAMLLogout($session);
                    break;
                case 'oauth':
                    $this->performOAuthLogout($session);
                    break;
                case 'ldap':
                    // LDAP doesn't require special logout
                    break;
            }
            
            // Remove session from database
            $stmt = $this->db->prepare("DELETE FROM sso_sessions WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            
            return [
                'success' => true,
                'message' => 'Logged out successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Private methods
    private function parseSAMLResponse(string $samlResponse): ?array {
        // Simplified SAML parsing - in production use proper SAML library
        // This is just a placeholder implementation
        return [
            'email' => 'user@example.com',
            'username' => 'user',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'groups' => ['users', 'employees']
        ];
    }
    
    private function exchangeCodeForToken(string $provider, string $code, array $config): ?array {
        $tokenUrl = $this->getOAuthTokenUrl($provider);
        
        $postData = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri']
        ];
        
        $response = $this->makeHttpRequest($tokenUrl, 'POST', $postData);
        
        return json_decode($response, true);
    }
    
    private function getOAuthUserInfo(string $provider, string $accessToken): ?array {
        $userInfoUrl = $this->getOAuthUserInfoUrl($provider);
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $response = $this->makeHttpRequest($userInfoUrl, 'GET', null, $headers);
        
        return json_decode($response, true);
    }
    
    private function findOrCreateSSOUser(string $email, string $username, string $firstName, string $lastName, string $provider): array {
        // Check if user exists
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = ? OR username = ?
        ");
        $stmt->execute([$email, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Update user information
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email_verified_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$firstName, $lastName, $user['id']]);
            
            return $user;
        }
        
        // Create new user
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, first_name, last_name, email_verified_at, created_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$username, $email, $firstName, $lastName]);
        $userId = $this->db->lastInsertId();
        
        // Get created user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function updateUserGroups(int $userId, array $groups): void {
        // Remove existing groups
        $stmt = $this->db->prepare("DELETE FROM user_groups WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Add new groups
        foreach ($groups as $groupName) {
            // Find or create group
            $stmt = $this->db->prepare("
                SELECT id FROM groups WHERE name = ?
            ");
            $stmt->execute([$groupName]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$group) {
                $stmt = $this->db->prepare("
                    INSERT INTO groups (name, created_at) VALUES (?, NOW())
                ");
                $stmt->execute([$groupName]);
                $groupId = $this->db->lastInsertId();
            } else {
                $groupId = $group['id'];
            }
            
            // Add user to group
            $stmt = $this->db->prepare("
                INSERT INTO user_groups (user_id, group_id, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $groupId]);
        }
    }
    
    private function createSSOSession(int $userId, string $provider, array $data): string {
        $sessionId = bin2hex(random_bytes(32));
        
        $stmt = $this->db->prepare("
            INSERT INTO sso_sessions (session_id, user_id, provider, data, created_at, expires_at)
            VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 8 HOUR))
        ");
        $stmt->execute([$sessionId, $userId, $provider, json_encode($data)]);
        
        return $sessionId;
    }
    
    private function storeOAuthTokens(int $userId, string $provider, array $tokens): void {
        $stmt = $this->db->prepare("
            INSERT INTO oauth_tokens (user_id, provider, access_token, refresh_token, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                expires_at = VALUES(expires_at),
                updated_at = NOW()
        ");
        
        $expiresAt = isset($tokens['expires_in']) 
            ? date('Y-m-d H:i:s', time() + $tokens['expires_in'])
            : null;
            
        $stmt->execute([
            $userId,
            $provider,
            $tokens['access_token'],
            $tokens['refresh_token'] ?? null,
            $expiresAt
        ]);
    }
    
    private function connectLDAP() {
        $ldap = ldap_connect($this->config['ldap']['host'], $this->config['ldap']['port']);
        
        if (!$ldap) {
            return false;
        }
        
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        
        return $ldap;
    }
    
    private function searchLDAPUser($ldap, string $username): ?string {
        $searchBase = $this->config['ldap']['user_search_base'];
        $searchFilter = str_replace('%s', $username, $this->config['ldap']['user_search_filter']);
        
        $result = ldap_search($ldap, $searchBase, $searchFilter, ['dn']);
        
        if (!$result) {
            return null;
        }
        
        $entries = ldap_get_entries($ldap, $result);
        
        if ($entries['count'] > 0) {
            return $entries[0]['dn'];
        }
        
        return null;
    }
    
    private function authenticateLDAPUser($ldap, string $userDN, string $password): bool {
        return @ldap_bind($ldap, $userDN, $password);
    }
    
    private function getLDAPUserInfo($ldap, string $userDN): array {
        $result = ldap_read($ldap, $userDN, '(objectClass=*)', [
            'mail', 'givenName', 'sn', 'displayName'
        ]);
        
        if (!$result) {
            return [];
        }
        
        $entries = ldap_get_entries($ldap, $result);
        
        if ($entries['count'] > 0) {
            $entry = $entries[0];
            return [
                'email' => $entry['mail'][0] ?? '',
                'first_name' => $entry['givenname'][0] ?? '',
                'last_name' => $entry['sn'][0] ?? '',
                'display_name' => $entry['displayname'][0] ?? ''
            ];
        }
        
        return [];
    }
    
    private function getLDAPUserGroups($ldap, string $userDN): array {
        $searchBase = $this->config['ldap']['group_search_base'];
        $searchFilter = str_replace('%s', $userDN, $this->config['ldap']['group_search_filter']);
        
        $result = ldap_search($ldap, $searchBase, $searchFilter, ['cn']);
        
        if (!$result) {
            return [];
        }
        
        $entries = ldap_get_entries($ldap, $result);
        $groups = [];
        
        for ($i = 0; $i < $entries['count']; $i++) {
            $groups[] = $entries[$i]['cn'][0];
        }
        
        return $groups;
    }
    
    private function buildOAuthAuthorizationUrl(string $provider, array $config): string {
        $baseUrls = [
            'google' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'microsoft' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'azure' => 'https://login.microsoftonline.com/' . $config['tenant_id'] . '/oauth2/v2.0/authorize'
        ];
        
        $baseUrl = $baseUrls[$provider] ?? '';
        
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->getOAuthScope($provider),
            'state' => bin2hex(random_bytes(16))
        ];
        
        return $baseUrl . '?' . http_build_query($params);
    }
    
    private function buildSAMLLoginUrl(): string {
        $params = [
            'SAMLRequest' => $this->generateSAMLRequest(),
            'RelayState' => bin2hex(random_bytes(16))
        ];
        
        return $this->config['saml']['sso_url'] . '?' . http_build_query($params);
    }
    
    private function getOAuthTokenUrl(string $provider): string {
        $urls = [
            'google' => 'https://oauth2.googleapis.com/token',
            'microsoft' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'azure' => 'https://login.microsoftonline.com/' . $this->config['oauth']['providers']['azure']['tenant_id'] . '/oauth2/v2.0/token'
        ];
        
        return $urls[$provider] ?? '';
    }
    
    private function getOAuthUserInfoUrl(string $provider): string {
        $urls = [
            'google' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'microsoft' => 'https://graph.microsoft.com/v1.0/me',
            'azure' => 'https://graph.microsoft.com/v1.0/me'
        ];
        
        return $urls[$provider] ?? '';
    }
    
    private function getOAuthScope(string $provider): string {
        $scopes = [
            'google' => 'openid email profile',
            'microsoft' => 'openid email profile',
            'azure' => 'openid email profile'
        ];
        
        return $scopes[$provider] ?? 'openid email profile';
    }
    
    private function generateSAMLRequest(): string {
        // Simplified SAML request generation
        return base64_encode('<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" />');
    }
    
    private function performSAMLLogout(array $session): void {
        // Implement SAML logout
    }
    
    private function performOAuthLogout(array $session): void {
        // Implement OAuth logout
    }
    
    private function makeHttpRequest(string $url, string $method = 'GET', array $data = null, array $headers = []): string {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
