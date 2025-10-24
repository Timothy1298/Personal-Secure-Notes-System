<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Core\DataManagement\ExportService;
use Core\DataManagement\ImportService;
use Core\DataManagement\MigrationService;

class DataManagementTest extends TestCase
{
    private $db;
    private $exportService;
    private $importService;
    private $migrationService;
    private $testUserId = 1;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test database
        $this->db = new \PDO('sqlite::memory:');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->createTestTables();
        $this->createTestData();
        
        $this->exportService = new ExportService($this->db);
        $this->importService = new ImportService($this->db);
        $this->migrationService = new MigrationService($this->db);
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
        
        $this->db->exec("
            CREATE TABLE tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT '#3b82f6',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE note_tags (
                note_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY (note_id, tag_id)
            )
        ");
        
        $this->db->exec("
            CREATE TABLE export_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                export_type VARCHAR(50) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_size BIGINT NOT NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE import_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                import_format VARCHAR(50) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_size BIGINT NOT NULL,
                imported_count INTEGER DEFAULT 0,
                error_count INTEGER DEFAULT 0,
                status VARCHAR(20) DEFAULT 'success',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE migration_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration_file VARCHAR(255) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                execution_time_ms INTEGER DEFAULT 0,
                status VARCHAR(20) DEFAULT 'success',
                error_message TEXT NULL
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
        
        // Create test tags
        $stmt = $this->db->prepare("
            INSERT INTO tags (user_id, name, color) 
            VALUES (?, ?, ?)
        ");
        
        $tags = [
            ['work', '#ff6b6b'],
            ['personal', '#4ecdc4'],
            ['important', '#45b7d1']
        ];
        
        foreach ($tags as $tag) {
            $stmt->execute([$this->testUserId, $tag[0], $tag[1]]);
        }
        
        // Create test notes
        $stmt = $this->db->prepare("
            INSERT INTO notes (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        
        $notes = [
            ['Meeting Notes', 'Notes from the team meeting'],
            ['Project Ideas', 'Ideas for the new project'],
            ['Shopping List', 'Items to buy at the store']
        ];
        
        foreach ($notes as $note) {
            $stmt->execute([$this->testUserId, $note[0], $note[1]]);
        }
        
        // Create test tasks
        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, title, description, status, priority) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $tasks = [
            ['Complete project', 'Finish the main project', 'in_progress', 'high'],
            ['Review code', 'Review team code submissions', 'pending', 'medium'],
            ['Update documentation', 'Update project documentation', 'completed', 'low']
        ];
        
        foreach ($tasks as $task) {
            $stmt->execute([$this->testUserId, $task[0], $task[1], $task[2], $task[3]]);
        }
        
        // Link notes with tags
        $stmt = $this->db->prepare("
            INSERT INTO note_tags (note_id, tag_id) 
            VALUES (?, ?)
        ");
        
        $noteTagLinks = [
            [1, 1], // Meeting Notes -> work
            [2, 1], // Project Ideas -> work
            [2, 3], // Project Ideas -> important
            [3, 2]  // Shopping List -> personal
        ];
        
        foreach ($noteTagLinks as $link) {
            $stmt->execute($link);
        }
    }
    
    public function testJsonExport(): void
    {
        $result = $this->exportService->exportUserData($this->testUserId, [
            'include_notes' => true,
            'include_tasks' => true,
            'include_tags' => true
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('filepath', $result);
        $this->assertArrayHasKey('size', $result);
        
        // Verify file exists and contains data
        $this->assertFileExists($result['filepath']);
        $this->assertGreaterThan(0, $result['size']);
        
        // Clean up
        unlink($result['filepath']);
    }
    
    public function testCsvExport(): void
    {
        $result = $this->exportService->exportToCSV($this->testUserId, 'notes');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('filepath', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals(3, $result['rows']); // 3 test notes
        
        // Verify CSV content
        $csvContent = file_get_contents($result['filepath']);
        $this->assertStringContainsString('Meeting Notes', $csvContent);
        $this->assertStringContainsString('Project Ideas', $csvContent);
        $this->assertStringContainsString('Shopping List', $csvContent);
        
        // Clean up
        unlink($result['filepath']);
    }
    
    public function testXmlExport(): void
    {
        $result = $this->exportService->exportToXML($this->testUserId, [
            'include_notes' => true,
            'include_tasks' => true
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('filepath', $result);
        
        // Verify XML content
        $xmlContent = file_get_contents($result['filepath']);
        $this->assertStringContainsString('<export', $xmlContent);
        $this->assertStringContainsString('<notes>', $xmlContent);
        $this->assertStringContainsString('<tasks>', $xmlContent);
        
        // Clean up
        unlink($result['filepath']);
    }
    
    public function testJsonImport(): void
    {
        // First export data
        $exportResult = $this->exportService->exportUserData($this->testUserId);
        $this->assertTrue($exportResult['success']);
        
        // Create a new user for import
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'importuser',
            'import@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $importUserId = $this->db->lastInsertId();
        
        // Import data
        $result = $this->importService->importFromJSON($importUserId, $exportResult['filepath'], [
            'import_notes' => true,
            'import_tasks' => true,
            'import_tags' => true
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['imported']['notes']);
        $this->assertEquals(3, $result['imported']['tasks']);
        $this->assertEquals(3, $result['imported']['tags']);
        
        // Verify imported data
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notes WHERE user_id = ?");
        $stmt->execute([$importUserId]);
        $noteCount = $stmt->fetchColumn();
        $this->assertEquals(3, $noteCount);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
        $stmt->execute([$importUserId]);
        $taskCount = $stmt->fetchColumn();
        $this->assertEquals(3, $taskCount);
        
        // Clean up
        unlink($exportResult['filepath']);
    }
    
    public function testCsvImport(): void
    {
        // Create CSV content
        $csvContent = "id,title,content,created_at,updated_at\n";
        $csvContent .= "1,Imported Note 1,Content of imported note 1,2023-01-01 10:00:00,2023-01-01 10:00:00\n";
        $csvContent .= "2,Imported Note 2,Content of imported note 2,2023-01-02 10:00:00,2023-01-02 10:00:00\n";
        
        $csvFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($csvFile, $csvContent);
        
        // Create a new user for import
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            'csvimportuser',
            'csvimport@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        $importUserId = $this->db->lastInsertId();
        
        // Import CSV
        $result = $this->importService->importFromCSV($importUserId, $csvFile, 'notes');
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['imported']);
        
        // Verify imported data
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notes WHERE user_id = ?");
        $stmt->execute([$importUserId]);
        $noteCount = $stmt->fetchColumn();
        $this->assertEquals(2, $noteCount);
        
        // Clean up
        unlink($csvFile);
    }
    
    public function testMigrationCreation(): void
    {
        $result = $this->migrationService->createMigration('test_migration', 'Test migration description');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('filepath', $result);
        
        // Verify migration file exists
        $this->assertFileExists($result['filepath']);
        
        // Verify file content
        $content = file_get_contents($result['filepath']);
        $this->assertStringContainsString('test_migration', $content);
        $this->assertStringContainsString('Test migration description', $content);
        
        // Clean up
        unlink($result['filepath']);
    }
    
    public function testMigrationValidation(): void
    {
        // Create a valid migration file
        $migrationContent = "-- Test migration\n";
        $migrationContent .= "CREATE TABLE test_table (\n";
        $migrationContent .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $migrationContent .= "    name VARCHAR(255) NOT NULL\n";
        $migrationContent .= ");\n";
        
        $migrationFile = tempnam(sys_get_temp_dir(), 'test_migration_');
        file_put_contents($migrationFile, $migrationContent);
        
        $result = $this->migrationService->validateMigration(basename($migrationFile));
        
        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['statements_count']);
        $this->assertEquals(1, $result['valid_statements']);
        
        // Clean up
        unlink($migrationFile);
    }
    
    public function testExportHistory(): void
    {
        // Create an export
        $result = $this->exportService->exportUserData($this->testUserId);
        $this->assertTrue($result['success']);
        
        // Get export history
        $history = $this->exportService->getExportHistory($this->testUserId);
        
        $this->assertIsArray($history);
        $this->assertCount(1, $history);
        $this->assertEquals('json', $history[0]['export_type']);
        
        // Clean up
        unlink($result['filepath']);
    }
    
    public function testImportHistory(): void
    {
        // Create a test import
        $csvContent = "id,title,content\n1,Test Note,Test Content\n";
        $csvFile = tempnam(sys_get_temp_dir(), 'test_import_');
        file_put_contents($csvFile, $csvContent);
        
        $result = $this->importService->importFromCSV($this->testUserId, $csvFile, 'notes');
        $this->assertTrue($result['success']);
        
        // Get import history
        $history = $this->importService->getImportHistory($this->testUserId);
        
        $this->assertIsArray($history);
        $this->assertCount(1, $history);
        $this->assertEquals('csv', $history[0]['import_format']);
        $this->assertEquals(1, $history[0]['imported_count']);
        
        // Clean up
        unlink($csvFile);
    }
    
    public function testFileValidation(): void
    {
        // Test valid JSON file
        $jsonContent = '{"test": "data"}';
        $jsonFile = tempnam(sys_get_temp_dir(), 'test_json_');
        file_put_contents($jsonFile, $jsonContent);
        
        $result = $this->importService->validateImportFile($jsonFile, 'json');
        $this->assertTrue($result['valid']);
        
        // Test invalid JSON file
        $invalidJsonContent = '{"test": "data"';
        $invalidJsonFile = tempnam(sys_get_temp_dir(), 'test_invalid_json_');
        file_put_contents($invalidJsonFile, $invalidJsonContent);
        
        $result = $this->importService->validateImportFile($invalidJsonFile, 'json');
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('error', $result);
        
        // Clean up
        unlink($jsonFile);
        unlink($invalidJsonFile);
    }
    
    protected function tearDown(): void
    {
        $this->db = null;
        $this->exportService = null;
        $this->importService = null;
        $this->migrationService = null;
        parent::tearDown();
    }
}
