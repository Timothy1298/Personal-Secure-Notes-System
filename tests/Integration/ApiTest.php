<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Core\Database;
use Core\Session;

class ApiTest extends TestCase
{
    private $db;
    private $baseUrl = 'http://localhost';
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test database
        $this->db = new \PDO('sqlite::memory:');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->createTestTables();
        $this->createTestData();
    }
    
    private function createTestTables(): void
    {
        $this->db->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                priority VARCHAR(50) DEFAULT 'medium',
                due_date DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    private function createTestData(): void
    {
        // Create test user
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'testuser',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Create test notes
        $stmt = $this->db->prepare("
            INSERT INTO notes (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        
        $notes = [
            ['Test Note 1', 'This is the first test note'],
            ['Test Note 2', 'This is the second test note'],
            ['Test Note 3', 'This is the third test note']
        ];
        
        foreach ($notes as $note) {
            $stmt->execute([$userId, $note[0], $note[1]]);
        }
        
        // Create test tasks
        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, title, description, status, priority) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $tasks = [
            ['Task 1', 'First task description', 'pending', 'high'],
            ['Task 2', 'Second task description', 'in_progress', 'medium'],
            ['Task 3', 'Third task description', 'completed', 'low']
        ];
        
        foreach ($tasks as $task) {
            $stmt->execute([$userId, $task[0], $task[1], $task[2], $task[3]]);
        }
    }
    
    public function testUserLogin(): void
    {
        $loginData = [
            'username' => 'testuser',
            'password' => 'password123'
        ];
        
        // Simulate login request
        $response = $this->makeRequest('POST', '/api/auth/login', $loginData);
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('success', $response['data']);
        $this->assertTrue($response['data']['success']);
    }
    
    public function testGetNotes(): void
    {
        // First login to get session
        $this->loginUser();
        
        $response = $this->makeRequest('GET', '/api/notes');
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertIsArray($response['data']['data']);
        $this->assertCount(3, $response['data']['data']);
    }
    
    public function testCreateNote(): void
    {
        $this->loginUser();
        
        $noteData = [
            'title' => 'New Test Note',
            'content' => 'This is a new test note created via API'
        ];
        
        $response = $this->makeRequest('POST', '/api/notes', $noteData);
        
        $this->assertEquals(201, $response['status']);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertEquals('New Test Note', $response['data']['data']['title']);
    }
    
    public function testUpdateNote(): void
    {
        $this->loginUser();
        
        // Get first note
        $notesResponse = $this->makeRequest('GET', '/api/notes');
        $noteId = $notesResponse['data']['data'][0]['id'];
        
        $updateData = [
            'title' => 'Updated Note Title',
            'content' => 'Updated note content'
        ];
        
        $response = $this->makeRequest('PUT', "/api/notes/{$noteId}", $updateData);
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Updated Note Title', $response['data']['data']['title']);
    }
    
    public function testDeleteNote(): void
    {
        $this->loginUser();
        
        // Get first note
        $notesResponse = $this->makeRequest('GET', '/api/notes');
        $noteId = $notesResponse['data']['data'][0]['id'];
        
        $response = $this->makeRequest('DELETE', "/api/notes/{$noteId}");
        
        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['data']['success']);
        
        // Verify note is deleted
        $notesResponse = $this->makeRequest('GET', '/api/notes');
        $this->assertCount(2, $notesResponse['data']['data']);
    }
    
    public function testGetTasks(): void
    {
        $this->loginUser();
        
        $response = $this->makeRequest('GET', '/api/tasks');
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertIsArray($response['data']['data']);
        $this->assertCount(3, $response['data']['data']);
    }
    
    public function testCreateTask(): void
    {
        $this->loginUser();
        
        $taskData = [
            'title' => 'New Test Task',
            'description' => 'This is a new test task',
            'priority' => 'high',
            'status' => 'pending'
        ];
        
        $response = $this->makeRequest('POST', '/api/tasks', $taskData);
        
        $this->assertEquals(201, $response['status']);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertEquals('New Test Task', $response['data']['data']['title']);
    }
    
    public function testUpdateTaskStatus(): void
    {
        $this->loginUser();
        
        // Get first task
        $tasksResponse = $this->makeRequest('GET', '/api/tasks');
        $taskId = $tasksResponse['data']['data'][0]['id'];
        
        $updateData = [
            'status' => 'completed'
        ];
        
        $response = $this->makeRequest('PATCH', "/api/tasks/{$taskId}/status", $updateData);
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('completed', $response['data']['data']['status']);
    }
    
    public function testUnauthorizedAccess(): void
    {
        $response = $this->makeRequest('GET', '/api/notes');
        
        $this->assertEquals(401, $response['status']);
        $this->assertArrayHasKey('error', $response['data']);
    }
    
    public function testInvalidEndpoint(): void
    {
        $this->loginUser();
        
        $response = $this->makeRequest('GET', '/api/invalid-endpoint');
        
        $this->assertEquals(404, $response['status']);
    }
    
    public function testRateLimiting(): void
    {
        $this->loginUser();
        
        // Make multiple requests quickly
        for ($i = 0; $i < 15; $i++) {
            $response = $this->makeRequest('GET', '/api/notes');
            
            if ($i < 10) {
                $this->assertEquals(200, $response['status']);
            } else {
                $this->assertEquals(429, $response['status']);
                break;
            }
        }
    }
    
    private function loginUser(): void
    {
        $loginData = [
            'username' => 'testuser',
            'password' => 'password123'
        ];
        
        $response = $this->makeRequest('POST', '/api/auth/login', $loginData);
        
        if ($response['status'] === 200) {
            // Store session token for subsequent requests
            $this->sessionToken = $response['data']['token'] ?? null;
        }
    }
    
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = ['Content-Type: application/json'];
        
        if (isset($this->sessionToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->sessionToken;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL Error: $error");
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'status' => $httpCode,
            'data' => $decodedResponse ?: $response
        ];
    }
    
    protected function tearDown(): void
    {
        $this->db = null;
        parent::tearDown();
    }
}
