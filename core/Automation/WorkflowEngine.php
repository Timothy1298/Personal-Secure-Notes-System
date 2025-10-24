<?php
namespace Core\Automation;

use PDO;
use Exception;

class WorkflowEngine {
    private $db;
    private $workflows = [];
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->loadWorkflows();
    }
    
    /**
     * Execute workflow
     */
    public function executeWorkflow($workflowId, $userId, $triggerData = []) {
        try {
            $workflow = $this->getWorkflow($workflowId);
            if (!$workflow) {
                throw new Exception("Workflow not found: {$workflowId}");
            }
            
            // Check if user has permission to execute this workflow
            if (!$this->hasPermission($workflow, $userId)) {
                throw new Exception("User does not have permission to execute this workflow");
            }
            
            // Create execution record
            $executionId = $this->createExecution($workflowId, $userId, $triggerData);
            
            // Execute workflow steps
            $result = $this->executeSteps($workflow, $userId, $triggerData, $executionId);
            
            // Update execution status
            $this->updateExecutionStatus($executionId, $result['status'], $result['data']);
            
            return $result;
        } catch (Exception $e) {
            error_log("Workflow execution failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }
    
    /**
     * Create workflow
     */
    public function createWorkflow($userId, $workflowData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO workflow_templates 
                (user_id, name, description, workflow_data, is_public, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $workflowData['name'],
                $workflowData['description'],
                json_encode($workflowData),
                $workflowData['is_public'] ?? false
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Workflow creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get workflow
     */
    public function getWorkflow($workflowId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM workflow_templates WHERE id = ?
            ");
            $stmt->execute([$workflowId]);
            $workflow = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($workflow) {
                $workflow['workflow_data'] = json_decode($workflow['workflow_data'], true);
            }
            
            return $workflow;
        } catch (Exception $e) {
            error_log("Get workflow failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user workflows
     */
    public function getUserWorkflows($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM workflow_templates 
                WHERE user_id = ? OR is_public = 1
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($workflows as &$workflow) {
                $workflow['workflow_data'] = json_decode($workflow['workflow_data'], true);
            }
            
            return $workflows;
        } catch (Exception $e) {
            error_log("Get user workflows failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute workflow steps
     */
    private function executeSteps($workflow, $userId, $triggerData, $executionId) {
        $workflowData = $workflow['workflow_data'];
        $steps = $workflowData['steps'] ?? [];
        $context = array_merge($triggerData, ['user_id' => $userId]);
        $results = [];
        
        foreach ($steps as $step) {
            try {
                $result = $this->executeStep($step, $context, $executionId);
                $results[] = $result;
                
                // Update context with step result
                if ($result['success']) {
                    $context = array_merge($context, $result['data'] ?? []);
                } else {
                    // Stop execution on failure
                    return [
                        'success' => false,
                        'error' => $result['error'],
                        'status' => 'failed',
                        'data' => $results
                    ];
                }
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => "Step execution failed: " . $e->getMessage(),
                    'status' => 'failed',
                    'data' => $results
                ];
            }
        }
        
        return [
            'success' => true,
            'status' => 'completed',
            'data' => $results
        ];
    }
    
    /**
     * Execute individual step
     */
    private function executeStep($step, $context, $executionId) {
        $stepType = $step['type'];
        $stepData = $step['data'] ?? [];
        
        switch ($stepType) {
            case 'create_note':
                return $this->executeCreateNote($stepData, $context);
            
            case 'create_task':
                return $this->executeCreateTask($stepData, $context);
            
            case 'send_email':
                return $this->executeSendEmail($stepData, $context);
            
            case 'webhook':
                return $this->executeWebhook($stepData, $context);
            
            case 'delay':
                return $this->executeDelay($stepData, $context);
            
            case 'condition':
                return $this->executeCondition($stepData, $context);
            
            case 'transform_data':
                return $this->executeTransformData($stepData, $context);
            
            case 'api_call':
                return $this->executeApiCall($stepData, $context);
            
            default:
                throw new Exception("Unknown step type: {$stepType}");
        }
    }
    
    /**
     * Execute create note step
     */
    private function executeCreateNote($stepData, $context) {
        try {
            $title = $this->interpolateVariables($stepData['title'], $context);
            $content = $this->interpolateVariables($stepData['content'], $context);
            
            $stmt = $this->db->prepare("
                INSERT INTO notes (user_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$context['user_id'], $title, $content]);
            $noteId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'data' => ['note_id' => $noteId, 'title' => $title]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Create note failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute create task step
     */
    private function executeCreateTask($stepData, $context) {
        try {
            $title = $this->interpolateVariables($stepData['title'], $context);
            $description = $this->interpolateVariables($stepData['description'] ?? '', $context);
            $priority = $stepData['priority'] ?? 'medium';
            $dueDate = $stepData['due_date'] ?? null;
            
            if ($dueDate) {
                $dueDate = $this->interpolateVariables($dueDate, $context);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO tasks (user_id, title, description, priority, due_date, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())
            ");
            
            $stmt->execute([$context['user_id'], $title, $description, $priority, $dueDate]);
            $taskId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'data' => ['task_id' => $taskId, 'title' => $title]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Create task failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute send email step
     */
    private function executeSendEmail($stepData, $context) {
        try {
            $to = $this->interpolateVariables($stepData['to'], $context);
            $subject = $this->interpolateVariables($stepData['subject'], $context);
            $body = $this->interpolateVariables($stepData['body'], $context);
            
            // Use existing email service
            $emailService = new \Core\EmailService($this->db);
            $result = $emailService->sendEmail($to, $subject, $body);
            
            return [
                'success' => $result,
                'data' => ['email_sent' => $result, 'to' => $to]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Send email failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute webhook step
     */
    private function executeWebhook($stepData, $context) {
        try {
            $url = $this->interpolateVariables($stepData['url'], $context);
            $method = $stepData['method'] ?? 'POST';
            $headers = $stepData['headers'] ?? [];
            $data = $stepData['data'] ?? [];
            
            // Interpolate variables in data
            $interpolatedData = [];
            foreach ($data as $key => $value) {
                $interpolatedData[$key] = $this->interpolateVariables($value, $context);
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($interpolatedData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
                'Content-Type: application/json'
            ], $headers));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return [
                'success' => $httpCode >= 200 && $httpCode < 300,
                'data' => [
                    'http_code' => $httpCode,
                    'response' => $response
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Webhook failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute delay step
     */
    private function executeDelay($stepData, $context) {
        $delay = $stepData['delay'] ?? 0;
        $unit = $stepData['unit'] ?? 'seconds';
        
        // Convert to seconds
        switch ($unit) {
            case 'minutes':
                $delay *= 60;
                break;
            case 'hours':
                $delay *= 3600;
                break;
            case 'days':
                $delay *= 86400;
                break;
        }
        
        sleep($delay);
        
        return [
            'success' => true,
            'data' => ['delay_completed' => $delay]
        ];
    }
    
    /**
     * Execute condition step
     */
    private function executeCondition($stepData, $context) {
        $condition = $stepData['condition'];
        $operator = $stepData['operator'] ?? 'equals';
        $value = $stepData['value'];
        
        $contextValue = $this->getContextValue($condition, $context);
        
        $result = false;
        switch ($operator) {
            case 'equals':
                $result = $contextValue == $value;
                break;
            case 'not_equals':
                $result = $contextValue != $value;
                break;
            case 'greater_than':
                $result = $contextValue > $value;
                break;
            case 'less_than':
                $result = $contextValue < $value;
                break;
            case 'contains':
                $result = strpos($contextValue, $value) !== false;
                break;
            case 'not_contains':
                $result = strpos($contextValue, $value) === false;
                break;
        }
        
        return [
            'success' => true,
            'data' => ['condition_result' => $result]
        ];
    }
    
    /**
     * Execute transform data step
     */
    private function executeTransformData($stepData, $context) {
        $transformations = $stepData['transformations'] ?? [];
        $transformedData = [];
        
        foreach ($transformations as $transformation) {
            $input = $this->getContextValue($transformation['input'], $context);
            $output = $transformation['output'];
            $operation = $transformation['operation'] ?? 'copy';
            
            switch ($operation) {
                case 'copy':
                    $transformedData[$output] = $input;
                    break;
                case 'uppercase':
                    $transformedData[$output] = strtoupper($input);
                    break;
                case 'lowercase':
                    $transformedData[$output] = strtolower($input);
                    break;
                case 'trim':
                    $transformedData[$output] = trim($input);
                    break;
                case 'date_format':
                    $format = $transformation['format'] ?? 'Y-m-d H:i:s';
                    $transformedData[$output] = date($format, strtotime($input));
                    break;
                case 'json_encode':
                    $transformedData[$output] = json_encode($input);
                    break;
                case 'json_decode':
                    $transformedData[$output] = json_decode($input, true);
                    break;
            }
        }
        
        return [
            'success' => true,
            'data' => $transformedData
        ];
    }
    
    /**
     * Execute API call step
     */
    private function executeApiCall($stepData, $context) {
        try {
            $url = $this->interpolateVariables($stepData['url'], $context);
            $method = $stepData['method'] ?? 'GET';
            $headers = $stepData['headers'] ?? [];
            $data = $stepData['data'] ?? [];
            
            // Interpolate variables in data
            $interpolatedData = [];
            foreach ($data as $key => $value) {
                $interpolatedData[$key] = $this->interpolateVariables($value, $context);
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            
            if (!empty($interpolatedData)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($interpolatedData));
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
                'Content-Type: application/json'
            ], $headers));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $responseData = json_decode($response, true);
            
            return [
                'success' => $httpCode >= 200 && $httpCode < 300,
                'data' => [
                    'http_code' => $httpCode,
                    'response' => $responseData
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "API call failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Interpolate variables in string
     */
    private function interpolateVariables($string, $context) {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($context) {
            $key = trim($matches[1]);
            return $this->getContextValue($key, $context);
        }, $string);
    }
    
    /**
     * Get value from context
     */
    private function getContextValue($key, $context) {
        $keys = explode('.', $key);
        $value = $context;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return '';
            }
        }
        
        return $value;
    }
    
    /**
     * Create execution record
     */
    private function createExecution($workflowId, $userId, $triggerData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO workflow_executions 
                (user_id, template_id, execution_data, status, started_at) 
                VALUES (?, ?, ?, 'running', NOW())
            ");
            
            $stmt->execute([
                $userId,
                $workflowId,
                json_encode($triggerData)
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Create execution failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update execution status
     */
    private function updateExecutionStatus($executionId, $status, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE workflow_executions 
                SET status = ?, execution_data = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([
                $status,
                json_encode($data),
                $executionId
            ]);
        } catch (Exception $e) {
            error_log("Update execution status failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check user permission
     */
    private function hasPermission($workflow, $userId) {
        return $workflow['user_id'] == $userId || $workflow['is_public'] == 1;
    }
    
    /**
     * Load workflows
     */
    private function loadWorkflows() {
        // This could be used to preload common workflows
    }
}
