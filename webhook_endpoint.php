<?php
/**
 * Webhook Endpoint
 * 
 * This script handles incoming webhook requests from external services.
 * URL: /webhook/{webhook_id}
 */

require __DIR__ . '/vendor/autoload.php';

use Core\Database;
use Core\Automation\WebhookManager;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create database connection
$db = Database::getInstance();

// Initialize webhook manager
$webhookManager = new WebhookManager($db);

// Get webhook ID from URL
$requestUri = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($requestUri, '/'));
$webhookId = $pathParts[1] ?? null;

if (!$webhookId) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook ID required']);
    exit;
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST ?? [];

// Add request metadata
$data['_webhook_metadata'] = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

// Trigger webhook
$result = $webhookManager->triggerWebhook($webhookId, $data);

// Return response
http_response_code($result['success'] ? 200 : 500);
echo json_encode($result);
