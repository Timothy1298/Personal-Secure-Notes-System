<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Automation\WorkflowEngine;
use Core\Automation\ScheduledTasks;
use Core\Automation\WebhookManager;
use Exception;

class AutomationController {
    private $db;
    private $workflowEngine;
    private $scheduledTasks;
    private $webhookManager;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->workflowEngine = new WorkflowEngine($this->db);
        $this->scheduledTasks = new ScheduledTasks($this->db);
        $this->webhookManager = new WebhookManager($this->db);
    }

    /**
     * Show automation dashboard
     */
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        // Get user's workflows, scheduled tasks, and webhooks
        $workflows = $this->workflowEngine->getUserWorkflows($userId);
        $scheduledTasks = $this->scheduledTasks->getUserScheduledTasks($userId);
        $webhooks = $this->webhookManager->getUserWebhooks($userId);

        include __DIR__ . '/../Views/automation.php';
    }

    /**
     * Get workflows
     */
    public function getWorkflows() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $workflows = $this->workflowEngine->getUserWorkflows($userId);
        
        echo json_encode(['success' => true, 'workflows' => $workflows]);
        exit;
    }

    /**
     * Create workflow
     */
    public function createWorkflow() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $workflowData = json_decode(file_get_contents('php://input'), true);

        if (!$workflowData) {
            echo json_encode(['success' => false, 'message' => 'Invalid workflow data']);
            exit;
        }

        $workflowId = $this->workflowEngine->createWorkflow($userId, $workflowData);

        if ($workflowId) {
            echo json_encode(['success' => true, 'workflow_id' => $workflowId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create workflow']);
        }
        exit;
    }

    /**
     * Execute workflow
     */
    public function executeWorkflow() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['workflow_id'])) {
            echo json_encode(['success' => false, 'message' => 'Workflow ID required']);
            exit;
        }

        $triggerData = $data['trigger_data'] ?? [];
        $result = $this->workflowEngine->executeWorkflow($data['workflow_id'], $userId, $triggerData);

        echo json_encode($result);
        exit;
    }

    /**
     * Get workflow
     */
    public function getWorkflow() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $workflowId = $_GET['id'] ?? null;
        if (!$workflowId) {
            echo json_encode(['success' => false, 'message' => 'Workflow ID required']);
            exit;
        }

        $workflow = $this->workflowEngine->getWorkflow($workflowId);
        if ($workflow) {
            echo json_encode(['success' => true, 'workflow' => $workflow]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Workflow not found']);
        }
        exit;
    }

    /**
     * Create scheduled task
     */
    public function createScheduledTask() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $taskData = json_decode(file_get_contents('php://input'), true);

        if (!$taskData) {
            echo json_encode(['success' => false, 'message' => 'Invalid task data']);
            exit;
        }

        $taskId = $this->scheduledTasks->scheduleTask($userId, $taskData);

        if ($taskId) {
            echo json_encode(['success' => true, 'task_id' => $taskId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create scheduled task']);
        }
        exit;
    }

    /**
     * Get scheduled tasks
     */
    public function getScheduledTasks() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $tasks = $this->scheduledTasks->getUserScheduledTasks($userId);

        echo json_encode(['success' => true, 'tasks' => $tasks]);
        exit;
    }

    /**
     * Update scheduled task
     */
    public function updateScheduledTask() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $taskId = $_GET['id'] ?? null;
        if (!$taskId) {
            echo json_encode(['success' => false, 'message' => 'Task ID required']);
            exit;
        }

        $taskData = json_decode(file_get_contents('php://input'), true);
        if (!$taskData) {
            echo json_encode(['success' => false, 'message' => 'Invalid task data']);
            exit;
        }

        $result = $this->scheduledTasks->updateTask($taskId, $taskData);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }
        exit;
    }

    /**
     * Delete scheduled task
     */
    public function deleteScheduledTask() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $taskId = $_GET['id'] ?? null;
        if (!$taskId) {
            echo json_encode(['success' => false, 'message' => 'Task ID required']);
            exit;
        }

        $result = $this->scheduledTasks->deleteTask($taskId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
        }
        exit;
    }

    /**
     * Create webhook
     */
    public function createWebhook() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $webhookData = json_decode(file_get_contents('php://input'), true);

        if (!$webhookData) {
            echo json_encode(['success' => false, 'message' => 'Invalid webhook data']);
            exit;
        }

        $result = $this->webhookManager->createWebhook($userId, $webhookData);

        if ($result) {
            echo json_encode(['success' => true, 'webhook' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create webhook']);
        }
        exit;
    }

    /**
     * Get webhooks
     */
    public function getWebhooks() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $webhooks = $this->webhookManager->getUserWebhooks($userId);

        echo json_encode(['success' => true, 'webhooks' => $webhooks]);
        exit;
    }

    /**
     * Update webhook
     */
    public function updateWebhook() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $webhookData = json_decode(file_get_contents('php://input'), true);
        if (!$webhookData) {
            echo json_encode(['success' => false, 'message' => 'Invalid webhook data']);
            exit;
        }

        $result = $this->webhookManager->updateWebhook($webhookId, $webhookData);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Webhook updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update webhook']);
        }
        exit;
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $result = $this->webhookManager->deleteWebhook($webhookId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Webhook deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete webhook']);
        }
        exit;
    }

    /**
     * Test webhook
     */
    public function testWebhook() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $result = $this->webhookManager->testWebhook($webhookId);

        echo json_encode($result);
        exit;
    }

    /**
     * Get webhook executions
     */
    public function getWebhookExecutions() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $limit = $_GET['limit'] ?? 50;
        $executions = $this->webhookManager->getWebhookExecutions($webhookId, $limit);

        echo json_encode(['success' => true, 'executions' => $executions]);
        exit;
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $stats = $this->webhookManager->getWebhookStats($webhookId);

        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    /**
     * Trigger webhook (for external use)
     */
    public function triggerWebhook() {
        $webhookId = $_GET['id'] ?? null;
        if (!$webhookId) {
            echo json_encode(['success' => false, 'message' => 'Webhook ID required']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $result = $this->webhookManager->triggerWebhook($webhookId, $data);

        echo json_encode($result);
        exit;
    }
}
