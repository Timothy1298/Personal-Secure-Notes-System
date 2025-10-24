<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\API\GraphQLServer;
use Core\API\OAuthServer;
use Exception;

class APIController {
    private $db;
    private $graphqlServer;
    private $oauthServer;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->graphqlServer = new GraphQLServer($this->db);
        $this->oauthServer = new OAuthServer($this->db);
    }

    /**
     * Handle API requests
     */
    public function handleRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove /api prefix
        $path = substr($uri, 4);
        
        // Route API requests
        switch ($path) {
            case '/graphql':
                $this->handleGraphQL();
                break;
                
            case '/oauth/authorize':
                $this->handleOAuthAuthorize();
                break;
                
            case '/oauth/token':
                $this->handleOAuthToken();
                break;
                
            case '/oauth/revoke':
                $this->handleOAuthRevoke();
                break;
                
            case '/oauth/tokeninfo':
                $this->handleOAuthTokenInfo();
                break;
                
            case '/webhooks':
                $this->handleWebhooks();
                break;
                
            case '/webhooks/deliver':
                $this->handleWebhookDelivery();
                break;
                
            default:
                $this->handleRESTAPI($path, $method);
                break;
        }
    }
    
    /**
     * Handle GraphQL requests
     */
    private function handleGraphQL() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $variables = $input['variables'] ?? [];
        $operationName = $input['operationName'] ?? null;
        
        if (empty($query)) {
            echo json_encode([
                'errors' => [
                    ['message' => 'No query provided']
                ]
            ]);
            return;
        }
        
        $result = $this->graphqlServer->execute($query, $variables, $operationName);
        echo json_encode($result);
    }
    
    /**
     * Handle OAuth authorization
     */
    private function handleOAuthAuthorize() {
        $responseType = $_GET['response_type'] ?? '';
        $clientId = $_GET['client_id'] ?? '';
        $redirectUri = $_GET['redirect_uri'] ?? '';
        $scope = $_GET['scope'] ?? '';
        $state = $_GET['state'] ?? '';
        
        try {
            $result = $this->oauthServer->authorize($responseType, $clientId, $redirectUri, $scope, $state);
            
            $redirectUrl = $redirectUri . '?' . http_build_query([
                'code' => $result['code'],
                'state' => $result['state']
            ]);
            
            header("Location: {$redirectUrl}");
        } catch (Exception $e) {
            $redirectUrl = $redirectUri . '?' . http_build_query([
                'error' => 'server_error',
                'error_description' => $e->getMessage(),
                'state' => $state
            ]);
            
            header("Location: {$redirectUrl}");
        }
    }
    
    /**
     * Handle OAuth token exchange
     */
    private function handleOAuthToken() {
        header('Content-Type: application/json');
        
        $grantType = $_POST['grant_type'] ?? '';
        $code = $_POST['code'] ?? '';
        $clientId = $_POST['client_id'] ?? '';
        $clientSecret = $_POST['client_secret'] ?? '';
        $redirectUri = $_POST['redirect_uri'] ?? '';
        $refreshToken = $_POST['refresh_token'] ?? '';
        
        try {
            if ($grantType === 'authorization_code') {
                $result = $this->oauthServer->exchangeCodeForTokens($code, $clientId, $clientSecret, $redirectUri);
            } elseif ($grantType === 'refresh_token') {
                $result = $this->oauthServer->refreshAccessToken($refreshToken, $clientId, $clientSecret);
            } else {
                throw new Exception('Unsupported grant type');
            }
            
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_request',
                'error_description' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle OAuth token revocation
     */
    private function handleOAuthRevoke() {
        header('Content-Type: application/json');
        
        $token = $_POST['token'] ?? '';
        $tokenTypeHint = $_POST['token_type_hint'] ?? null;
        
        try {
            $this->oauthServer->revokeToken($token, $tokenTypeHint);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_request',
                'error_description' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle OAuth token info
     */
    private function handleOAuthTokenInfo() {
        header('Content-Type: application/json');
        
        $accessToken = $_GET['access_token'] ?? '';
        
        if (empty($accessToken)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_request',
                'error_description' => 'Access token required'
            ]);
            return;
        }
        
        $tokenInfo = $this->oauthServer->getTokenInfo($accessToken);
        
        if (!$tokenInfo) {
            http_response_code(401);
            echo json_encode([
                'error' => 'invalid_token',
                'error_description' => 'Invalid or expired access token'
            ]);
            return;
        }
        
        echo json_encode($tokenInfo);
    }
    
    /**
     * Handle webhooks
     */
    private function handleWebhooks() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->listWebhooks();
                break;
            case 'POST':
                $this->createWebhook();
                break;
            case 'PUT':
                $this->updateWebhook();
                break;
            case 'DELETE':
                $this->deleteWebhook();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    /**
     * List webhooks
     */
    private function listWebhooks() {
        header('Content-Type: application/json');
        
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT webhook_id, name, url, events, is_active, last_triggered_at, created_at
            FROM api_webhooks 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($webhooks as &$webhook) {
            $webhook['events'] = json_decode($webhook['events'], true);
        }
        
        echo json_encode(['webhooks' => $webhooks]);
    }
    
    /**
     * Create webhook
     */
    private function createWebhook() {
        header('Content-Type: application/json');
        
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['name']) || !isset($input['url']) || !isset($input['events'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        $webhookId = uniqid('webhook_');
        $secret = bin2hex(random_bytes(32));
        
        $stmt = $this->db->prepare("
            INSERT INTO api_webhooks 
            (user_id, webhook_id, name, url, events, secret, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $webhookId,
            $input['name'],
            $input['url'],
            json_encode($input['events']),
            $secret
        ]);
        
        echo json_encode([
            'webhook_id' => $webhookId,
            'secret' => $secret
        ]);
    }
    
    /**
     * Update webhook
     */
    private function updateWebhook() {
        header('Content-Type: application/json');
        
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $webhookId = $_GET['id'] ?? '';
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($webhookId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Webhook ID required']);
            return;
        }
        
        $updateFields = [];
        $params = [];
        
        if (isset($input['name'])) {
            $updateFields[] = 'name = ?';
            $params[] = $input['name'];
        }
        
        if (isset($input['url'])) {
            $updateFields[] = 'url = ?';
            $params[] = $input['url'];
        }
        
        if (isset($input['events'])) {
            $updateFields[] = 'events = ?';
            $params[] = json_encode($input['events']);
        }
        
        if (isset($input['is_active'])) {
            $updateFields[] = 'is_active = ?';
            $params[] = $input['is_active'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $updateFields[] = 'updated_at = NOW()';
        $params[] = $webhookId;
        $params[] = $userId;
        
        $stmt = $this->db->prepare("
            UPDATE api_webhooks 
            SET " . implode(', ', $updateFields) . "
            WHERE webhook_id = ? AND user_id = ?
        ");
        
        $stmt->execute($params);
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Delete webhook
     */
    private function deleteWebhook() {
        header('Content-Type: application/json');
        
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $webhookId = $_GET['id'] ?? '';
        
        if (empty($webhookId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Webhook ID required']);
            return;
        }
        
        $stmt = $this->db->prepare("
            DELETE FROM api_webhooks 
            WHERE webhook_id = ? AND user_id = ?
        ");
        $stmt->execute([$webhookId, $userId]);
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Handle webhook delivery
     */
    private function handleWebhookDelivery() {
        header('Content-Type: application/json');
        
        $webhookId = $_GET['id'] ?? '';
        
        if (empty($webhookId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Webhook ID required']);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM api_webhooks 
            WHERE webhook_id = ? AND is_active = 1
        ");
        $stmt->execute([$webhookId]);
        $webhook = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$webhook) {
            http_response_code(404);
            echo json_encode(['error' => 'Webhook not found']);
            return;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $eventType = $payload['event_type'] ?? 'unknown';
        
        // Create webhook delivery record
        $stmt = $this->db->prepare("
            INSERT INTO api_webhook_deliveries 
            (webhook_id, event_type, payload, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $webhookId,
            $eventType,
            json_encode($payload)
        ]);
        
        $deliveryId = $this->db->lastInsertId();
        
        // Send webhook
        $this->sendWebhook($webhook, $payload, $deliveryId);
        
        echo json_encode(['success' => true, 'delivery_id' => $deliveryId]);
    }
    
    /**
     * Send webhook
     */
    private function sendWebhook($webhook, $payload, $deliveryId) {
        $signature = hash_hmac('sha256', json_encode($payload), $webhook['secret']);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Webhook-Signature: sha256=' . $signature,
            'X-Webhook-Event: ' . ($payload['event_type'] ?? 'unknown')
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Update delivery record
        $stmt = $this->db->prepare("
            UPDATE api_webhook_deliveries 
            SET response_status = ?, response_body = ?, delivered_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$httpCode, $response, $deliveryId]);
        
        // Update webhook last triggered
        $stmt = $this->db->prepare("
            UPDATE api_webhooks 
            SET last_triggered_at = NOW() 
            WHERE webhook_id = ?
        ");
        $stmt->execute([$webhook['webhook_id']]);
    }
    
    /**
     * Handle REST API requests
     */
    private function handleRESTAPI($path, $method) {
        header('Content-Type: application/json');
        
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // Route REST API requests
        $pathParts = explode('/', trim($path, '/'));
        $resource = $pathParts[0] ?? '';
        $id = $pathParts[1] ?? null;
        
        switch ($resource) {
            case 'notes':
                $this->handleNotesAPI($method, $id, $userId);
                break;
            case 'tasks':
                $this->handleTasksAPI($method, $id, $userId);
                break;
            case 'tags':
                $this->handleTagsAPI($method, $id, $userId);
                break;
            case 'users':
                $this->handleUsersAPI($method, $id, $userId);
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Resource not found']);
        }
    }
    
    /**
     * Handle notes API
     */
    private function handleNotesAPI($method, $id, $userId) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getNote($id, $userId);
                } else {
                    $this->listNotes($userId);
                }
                break;
            case 'POST':
                $this->createNote($userId);
                break;
            case 'PUT':
                $this->updateNote($id, $userId);
                break;
            case 'DELETE':
                $this->deleteNote($id, $userId);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    /**
     * Get note
     */
    private function getNote($id, $userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notes 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }
        
        echo json_encode($note);
    }
    
    /**
     * List notes
     */
    private function listNotes($userId) {
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $stmt = $this->db->prepare("
            SELECT * FROM notes 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['notes' => $notes]);
    }
    
    /**
     * Create note
     */
    private function createNote($userId) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['title']) || !isset($input['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and content required']);
            return;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO notes (title, content, user_id, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $input['title'],
            $input['content'],
            $userId
        ]);
        
        $noteId = $this->db->lastInsertId();
        
        echo json_encode(['id' => $noteId, 'success' => true]);
    }
    
    /**
     * Update note
     */
    private function updateNote($id, $userId) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("
            UPDATE notes 
            SET title = ?, content = ?, updated_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $input['title'] ?? '',
            $input['content'] ?? '',
            $id,
            $userId
        ]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Delete note
     */
    private function deleteNote($id, $userId) {
        $stmt = $this->db->prepare("
            DELETE FROM notes 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Note not found']);
            return;
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Handle tasks API
     */
    private function handleTasksAPI($method, $id, $userId) {
        // Similar implementation to notes API
        echo json_encode(['message' => 'Tasks API not implemented yet']);
    }
    
    /**
     * Handle tags API
     */
    private function handleTagsAPI($method, $id, $userId) {
        // Similar implementation to notes API
        echo json_encode(['message' => 'Tags API not implemented yet']);
    }
    
    /**
     * Handle users API
     */
    private function handleUsersAPI($method, $id, $userId) {
        // Similar implementation to notes API
        echo json_encode(['message' => 'Users API not implemented yet']);
    }
    
    /**
     * Get authenticated user ID
     */
    private function getAuthenticatedUserId() {
        // Check for Bearer token
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            $accessToken = $matches[1];
            $tokenInfo = $this->oauthServer->validateAccessToken($accessToken);
            
            if ($tokenInfo) {
                return $tokenInfo['user_id'];
            }
        }
        
        // Check for API key
        $apiKey = $headers['X-API-Key'] ?? '';
        if (!empty($apiKey)) {
            $stmt = $this->db->prepare("
                SELECT user_id FROM api_keys 
                WHERE api_key = ? AND is_active = 1 
                AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$apiKey]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Update last used
                $stmt = $this->db->prepare("
                    UPDATE api_keys 
                    SET last_used_at = NOW() 
                    WHERE api_key = ?
                ");
                $stmt->execute([$apiKey]);
                
                return $result['user_id'];
            }
        }
        
        return null;
    }
}
