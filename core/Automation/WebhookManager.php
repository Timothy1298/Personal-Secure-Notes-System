<?php
namespace Core\Automation;

use PDO;
use Exception;

class WebhookManager {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Create webhook
     */
    public function createWebhook($userId, $webhookData) {
        try {
            $webhookId = uniqid('webhook_');
            $secret = $this->generateSecret();
            
            $stmt = $this->db->prepare("
                INSERT INTO webhooks 
                (user_id, webhook_id, name, description, url, method, headers, 
                 authentication_type, authentication_data, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $webhookId,
                $webhookData['name'],
                $webhookData['description'],
                $webhookData['url'],
                $webhookData['method'] ?? 'POST',
                json_encode($webhookData['headers'] ?? []),
                $webhookData['authentication_type'] ?? 'none',
                json_encode($webhookData['authentication_data'] ?? []),
                $webhookData['is_active'] ?? true
            ]);
            
            return [
                'webhook_id' => $webhookId,
                'secret' => $secret
            ];
        } catch (Exception $e) {
            error_log("Create webhook failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user webhooks
     */
    public function getUserWebhooks($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM webhooks 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($webhooks as &$webhook) {
                $webhook['headers'] = json_decode($webhook['headers'], true);
                $webhook['authentication_data'] = json_decode($webhook['authentication_data'], true);
            }
            
            return $webhooks;
        } catch (Exception $e) {
            error_log("Get user webhooks failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get webhook by ID
     */
    public function getWebhook($webhookId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM webhooks WHERE webhook_id = ?
            ");
            $stmt->execute([$webhookId]);
            $webhook = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($webhook) {
                $webhook['headers'] = json_decode($webhook['headers'], true);
                $webhook['authentication_data'] = json_decode($webhook['authentication_data'], true);
            }
            
            return $webhook;
        } catch (Exception $e) {
            error_log("Get webhook failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update webhook
     */
    public function updateWebhook($webhookId, $webhookData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE webhooks 
                SET name = ?, description = ?, url = ?, method = ?, headers = ?, 
                    authentication_type = ?, authentication_data = ?, is_active = ?, updated_at = NOW()
                WHERE webhook_id = ?
            ");
            
            $stmt->execute([
                $webhookData['name'],
                $webhookData['description'],
                $webhookData['url'],
                $webhookData['method'] ?? 'POST',
                json_encode($webhookData['headers'] ?? []),
                $webhookData['authentication_type'] ?? 'none',
                json_encode($webhookData['authentication_data'] ?? []),
                $webhookData['is_active'] ?? true,
                $webhookId
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update webhook failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete webhook
     */
    public function deleteWebhook($webhookId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM webhooks WHERE webhook_id = ?
            ");
            $stmt->execute([$webhookId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Delete webhook failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trigger webhook
     */
    public function triggerWebhook($webhookId, $data) {
        try {
            $webhook = $this->getWebhook($webhookId);
            if (!$webhook) {
                throw new Exception("Webhook not found: {$webhookId}");
            }
            
            if (!$webhook['is_active']) {
                throw new Exception("Webhook is not active");
            }
            
            // Create webhook execution record
            $executionId = $this->createExecution($webhookId, $data);
            
            // Send webhook
            $result = $this->sendWebhook($webhook, $data);
            
            // Update execution record
            $this->updateExecution($executionId, $result);
            
            return $result;
        } catch (Exception $e) {
            error_log("Trigger webhook failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send webhook
     */
    private function sendWebhook($webhook, $data) {
        try {
            $url = $webhook['url'];
            $method = $webhook['method'];
            $headers = $webhook['headers'] ?? [];
            $authType = $webhook['authentication_type'];
            $authData = $webhook['authentication_data'] ?? [];
            
            // Prepare headers
            $httpHeaders = array_merge([
                'Content-Type: application/json',
                'User-Agent: Personal-Notes-System/1.0'
            ], $headers);
            
            // Add authentication
            if ($authType === 'bearer') {
                $httpHeaders[] = 'Authorization: Bearer ' . ($authData['token'] ?? '');
            } elseif ($authType === 'basic') {
                $username = $authData['username'] ?? '';
                $password = $authData['password'] ?? '';
                $httpHeaders[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
            } elseif ($authType === 'api_key') {
                $keyName = $authData['key_name'] ?? 'X-API-Key';
                $keyValue = $authData['key_value'] ?? '';
                $httpHeaders[] = $keyName . ': ' . $keyValue;
            }
            
            // Prepare data
            $payload = json_encode($data);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception("cURL error: {$error}");
            }
            
            return [
                'success' => $httpCode >= 200 && $httpCode < 300,
                'http_code' => $httpCode,
                'response' => $response,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => $data
            ];
        }
    }
    
    /**
     * Create webhook execution record
     */
    private function createExecution($webhookId, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO webhook_executions 
                (webhook_id, request_data, status, started_at) 
                VALUES (?, ?, 'running', NOW())
            ");
            
            $stmt->execute([
                $webhookId,
                json_encode($data)
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Create webhook execution failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update webhook execution record
     */
    private function updateExecution($executionId, $result) {
        try {
            $stmt = $this->db->prepare("
                UPDATE webhook_executions 
                SET status = ?, response_data = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $result['success'] ? 'completed' : 'failed',
                json_encode($result),
                $executionId
            ]);
        } catch (Exception $e) {
            error_log("Update webhook execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Generate webhook secret
     */
    private function generateSecret() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Verify webhook signature
     */
    public function verifySignature($payload, $signature, $secret) {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get webhook executions
     */
    public function getWebhookExecutions($webhookId, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM webhook_executions 
                WHERE webhook_id = ? 
                ORDER BY started_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$webhookId, $limit]);
            $executions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($executions as &$execution) {
                $execution['request_data'] = json_decode($execution['request_data'], true);
                $execution['response_data'] = json_decode($execution['response_data'], true);
            }
            
            return $executions;
        } catch (Exception $e) {
            error_log("Get webhook executions failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Test webhook
     */
    public function testWebhook($webhookId) {
        try {
            $testData = [
                'event' => 'test',
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'This is a test webhook from Personal Notes System'
            ];
            
            return $this->triggerWebhook($webhookId, $testData);
        } catch (Exception $e) {
            error_log("Test webhook failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get webhook statistics
     */
    public function getWebhookStats($webhookId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_executions,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_executions,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_executions,
                    AVG(CASE WHEN status = 'completed' THEN 
                        TIMESTAMPDIFF(MICROSECOND, started_at, completed_at) / 1000 
                    END) as avg_response_time_ms
                FROM webhook_executions 
                WHERE webhook_id = ?
            ");
            $stmt->execute([$webhookId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats['total_executions'] > 0) {
                $stats['success_rate'] = round(($stats['successful_executions'] / $stats['total_executions']) * 100, 2);
            } else {
                $stats['success_rate'] = 0;
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get webhook stats failed: " . $e->getMessage());
            return [
                'total_executions' => 0,
                'successful_executions' => 0,
                'failed_executions' => 0,
                'success_rate' => 0,
                'avg_response_time_ms' => 0
            ];
        }
    }
}
