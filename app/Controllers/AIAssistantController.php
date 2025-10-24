<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\AI\SmartSuggestions;
use Core\AI\ContentGenerator;
use Core\AI\ContentAnalyzer;
use Exception;

class AIAssistantController {
    private $db;
    private $smartSuggestions;
    private $contentGenerator;
    private $contentAnalyzer;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->smartSuggestions = new SmartSuggestions($this->db);
        $this->contentGenerator = new ContentGenerator($this->db);
        $this->contentAnalyzer = new ContentAnalyzer($this->db);
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        // Get recent AI interactions
        $recentInteractions = $this->getRecentInteractions($userId);
        
        include __DIR__ . '/../Views/ai_assistant.php';
    }

    public function generateContent() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $prompt = $_POST['prompt'] ?? '';
        $type = $_POST['type'] ?? 'general';

        if (empty($prompt)) {
            echo json_encode(['success' => false, 'message' => 'Prompt is required']);
            exit;
        }

        try {
            $result = $this->contentGenerator->generateText($prompt);
            
            // Save interaction
            $this->saveInteraction($userId, $prompt, $result, $type);
            
            echo json_encode([
                'success' => true,
                'content' => $result,
                'type' => $type
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function analyzeContent() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $content = $_POST['content'] ?? '';

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Content is required']);
            exit;
        }

        try {
            $analysis = $this->contentAnalyzer->analyzeText($content);
            
            echo json_encode([
                'success' => true,
                'analysis' => $analysis
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to analyze content: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function getSuggestions() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $content = $_POST['content'] ?? '';
        $title = $_POST['title'] ?? '';

        try {
            $suggestions = $this->smartSuggestions->generateSuggestions($userId, $content, $title);
            
            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to get suggestions: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function summarizeContent() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $content = $_POST['content'] ?? '';
        $wordLimit = (int)($_POST['word_limit'] ?? 50);

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Content is required']);
            exit;
        }

        try {
            $summary = $this->contentGenerator->generateSummary($content, $wordLimit);
            
            // Save interaction
            $this->saveInteraction($userId, "Summarize: " . substr($content, 0, 100), $summary, 'summarize');
            
            echo json_encode([
                'success' => true,
                'summary' => $summary,
                'word_limit' => $wordLimit
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to summarize content: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function generateTitle() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $content = $_POST['content'] ?? '';

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Content is required']);
            exit;
        }

        try {
            $title = $this->contentGenerator->generateTitle($content);
            
            echo json_encode([
                'success' => true,
                'title' => $title
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate title: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function getHistory() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $limit = (int)($_GET['limit'] ?? 20);

        try {
            $history = $this->getRecentInteractions($userId, $limit);
            
            echo json_encode([
                'success' => true,
                'history' => $history
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to get history: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    private function getRecentInteractions($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ai_content_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent interactions: " . $e->getMessage());
            return [];
        }
    }

    private function saveInteraction($userId, $prompt, $response, $type) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_content_history (user_id, request_type, prompt, generated_content, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $type, $prompt, $response]);
        } catch (Exception $e) {
            error_log("Error saving AI interaction: " . $e->getMessage());
        }
    }
}
