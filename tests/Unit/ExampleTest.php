<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Database;
use Core\Cache;

class ExampleTest extends TestCase
{
    private $db;
    private $cache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup in-memory SQLite database for testing
        $this->db = new \PDO('sqlite::memory:');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Create test tables
        $this->createTestTables();
        
        $this->cache = Cache::getInstance($this->db);
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
    }
    
    public function testDatabaseConnection(): void
    {
        $this->assertInstanceOf(\PDO::class, $this->db);
    }
    
    public function testCacheInstance(): void
    {
        $this->assertInstanceOf(Cache::class, $this->cache);
    }
    
    public function testCacheSetAndGet(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->cache->set($key, $value);
        $retrieved = $this->cache->get($key);
        
        $this->assertEquals($value, $retrieved);
    }
    
    public function testCacheHas(): void
    {
        $key = 'test_has_key';
        $value = 'test_has_value';
        
        $this->assertFalse($this->cache->has($key));
        
        $this->cache->set($key, $value);
        
        $this->assertTrue($this->cache->has($key));
    }
    
    public function testCacheDelete(): void
    {
        $key = 'test_delete_key';
        $value = 'test_delete_value';
        
        $this->cache->set($key, $value);
        $this->assertTrue($this->cache->has($key));
        
        $this->cache->delete($key);
        $this->assertFalse($this->cache->has($key));
    }
    
    public function testUserInsertion(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        
        $result = $stmt->execute([
            'testuser',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $this->assertTrue($result);
        
        $userId = $this->db->lastInsertId();
        $this->assertGreaterThan(0, $userId);
    }
    
    public function testUserRetrieval(): void
    {
        // Insert test user
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'testuser2',
            'test2@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Retrieve user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($user);
        $this->assertEquals('testuser2', $user['username']);
        $this->assertEquals('test2@example.com', $user['email']);
    }
    
    public function testNoteCreation(): void
    {
        // Create user first
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'notetest',
            'note@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Create note
        $stmt = $this->db->prepare("
            INSERT INTO notes (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $userId,
            'Test Note',
            'This is a test note content'
        ]);
        
        $this->assertTrue($result);
        
        $noteId = $this->db->lastInsertId();
        $this->assertGreaterThan(0, $noteId);
    }
    
    public function testNoteRetrieval(): void
    {
        // Create user and note
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'notetest2',
            'note2@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $userId = $this->db->lastInsertId();
        
        $stmt = $this->db->prepare("
            INSERT INTO notes (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            'Test Note 2',
            'This is another test note'
        ]);
        
        $noteId = $this->db->lastInsertId();
        
        // Retrieve note
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$noteId]);
        $note = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($note);
        $this->assertEquals($userId, $note['user_id']);
        $this->assertEquals('Test Note 2', $note['title']);
        $this->assertEquals('This is another test note', $note['content']);
    }
    
    public function testPasswordHashing(): void
    {
        $password = 'testpassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrongpassword', $hash));
    }
    
    public function testCacheExpiration(): void
    {
        $key = 'expiring_key';
        $value = 'expiring_value';
        
        // Set with 1 second TTL
        $this->cache->set($key, $value, 1);
        $this->assertTrue($this->cache->has($key));
        
        // Wait for expiration
        sleep(2);
        
        $this->assertFalse($this->cache->has($key));
    }
    
    protected function tearDown(): void
    {
        $this->db = null;
        $this->cache = null;
        parent::tearDown();
    }
}
