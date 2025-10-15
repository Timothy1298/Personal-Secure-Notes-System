<?php
namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use Core\Database;
use App\Models\NotesModel;
use App\Models\TasksModel;
use Exception;

class SearchController {
    private $db;
    private $notesModel;
    private $tasksModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->notesModel = new NotesModel($this->db);
        $this->tasksModel = new TasksModel($this->db);
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        include __DIR__ . '/../Views/search_enhanced.php';
    }

    public function globalSearch() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $query = trim($input['query'] ?? '');
            $filters = $input['filters'] ?? [];
            $page = (int)($input['page'] ?? 1);
            $limit = 20;
            $offset = ($page - 1) * $limit;

            // CSRF protection
            $csrfToken = $input['csrf_token'] ?? '';
            if (!CSRF::verify($csrfToken)) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }

            if (empty($query)) {
                echo json_encode(['success' => false, 'message' => 'Search query is required']);
                exit;
            }

            $results = [];
            $totalResults = 0;

            // Search notes
            if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'notes') {
                $noteResults = $this->searchNotes($userId, $query, $filters, $limit, $offset);
                $results = array_merge($results, $noteResults);
            }

            // Search tasks
            if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'tasks') {
                $taskResults = $this->searchTasks($userId, $query, $filters, $limit, $offset);
                $results = array_merge($results, $taskResults);
            }

            // Search tags
            if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'tags') {
                $tagResults = $this->searchTags($userId, $query, $filters, $limit, $offset);
                $results = array_merge($results, $tagResults);
            }

            // Sort results by relevance and date
            usort($results, function($a, $b) {
                // First by relevance score
                if ($a['relevance_score'] !== $b['relevance_score']) {
                    return $b['relevance_score'] - $a['relevance_score'];
                }
                // Then by date (newest first)
                return strtotime($b['updated_at']) - strtotime($a['updated_at']);
            });

            $totalResults = count($results);
            $results = array_slice($results, 0, $limit);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'results' => $results,
                'total' => $totalResults,
                'page' => $page,
                'has_more' => $totalResults > ($page * $limit)
            ]);

        } catch (Exception $e) {
            error_log("Search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Search failed']);
        }
    }

    private function searchNotes($userId, $query, $filters, $limit, $offset) {
        $results = [];
        
        try {
            // Build search query for notes
            $sql = "SELECT n.*, 
                           GROUP_CONCAT(DISTINCT t.name) as tags,
                           (CASE 
                               WHEN n.title LIKE :query_like THEN 100
                               WHEN n.content LIKE :query_like THEN 50
                               ELSE 10
                           END) as relevance
                    FROM notes n
                    LEFT JOIN note_tags nt ON n.id = nt.note_id
                    LEFT JOIN tags t ON nt.tag_id = t.id
                    WHERE n.user_id = :user_id 
                    AND n.is_deleted = 0 
                    AND n.is_archived = 0
                    AND (n.title LIKE :query_like 
                         OR n.content LIKE :query_like)";
            
            $params = [
                ':user_id' => $userId,
                ':query' => $query,
                ':query_like' => '%' . $query . '%'
            ];

            // Add date filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND n.created_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $sql .= " AND n.created_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $sql .= " GROUP BY n.id ORDER BY relevance DESC, n.updated_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $notes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($notes as $note) {
                $relevanceScore = $this->calculateRelevanceScore($query, $note['title'], $note['content']);
                
                $results[] = [
                    'id' => $note['id'],
                    'type' => 'note',
                    'title' => $note['title'],
                    'content' => $this->highlightSearchTerms($query, substr($note['content'], 0, 200)),
                    'tags' => $note['tags'] ? explode(',', $note['tags']) : [],
                    'priority' => $note['priority'],
                    'created_at' => $note['created_at'],
                    'updated_at' => $note['updated_at'],
                    'relevance_score' => $relevanceScore,
                    'url' => '/notes'
                ];
            }

        } catch (Exception $e) {
            error_log("Note search error: " . $e->getMessage());
        }

        return $results;
    }

    private function searchTasks($userId, $query, $filters, $limit, $offset) {
        $results = [];
        
        try {
            // Build search query for tasks
            $sql = "SELECT t.*, 
                           GROUP_CONCAT(DISTINCT tg.name) as tags,
                           (CASE 
                               WHEN t.title LIKE :query_like THEN 100
                               WHEN t.description LIKE :query_like THEN 50
                               ELSE 10
                           END) as relevance
                    FROM tasks t
                    LEFT JOIN task_tags tt ON t.id = tt.task_id
                    LEFT JOIN tags tg ON tt.tag_id = tg.id
                    WHERE t.user_id = :user_id 
                    AND t.is_deleted = 0 
                    AND t.is_archived = 0
                    AND (t.title LIKE :query_like 
                         OR t.description LIKE :query_like)";
            
            $params = [
                ':user_id' => $userId,
                ':query' => $query,
                ':query_like' => '%' . $query . '%'
            ];

            // Add priority filter
            if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
                $sql .= " AND t.priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            // Add date filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND t.created_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $sql .= " AND t.created_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $sql .= " GROUP BY t.id ORDER BY relevance DESC, t.updated_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tasks as $task) {
                $relevanceScore = $this->calculateRelevanceScore($query, $task['title'], $task['description']);
                
                $results[] = [
                    'id' => $task['id'],
                    'type' => 'task',
                    'title' => $task['title'],
                    'content' => $this->highlightSearchTerms($query, substr($task['description'], 0, 200)),
                    'tags' => $task['tags'] ? explode(',', $task['tags']) : [],
                    'priority' => $task['priority'],
                    'status' => $task['status'],
                    'due_date' => $task['due_date'],
                    'created_at' => $task['created_at'],
                    'updated_at' => $task['updated_at'],
                    'relevance_score' => $relevanceScore,
                    'url' => '/tasks'
                ];
            }

        } catch (Exception $e) {
            error_log("Task search error: " . $e->getMessage());
        }

        return $results;
    }

    private function searchTags($userId, $query, $filters, $limit, $offset) {
        $results = [];
        
        try {
            $sql = "SELECT * FROM tags 
                    WHERE user_id = :user_id 
                    AND name LIKE :query_like
                    ORDER BY name ASC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':query_like', '%' . $query . '%');
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tags as $tag) {
                $relevanceScore = $this->calculateRelevanceScore($query, $tag['name'], '');
                
                $results[] = [
                    'id' => $tag['id'],
                    'type' => 'tag',
                    'title' => $tag['name'],
                    'content' => 'Tag: ' . $tag['name'],
                    'tags' => [],
                    'color' => $tag['color'],
                    'created_at' => $tag['created_at'],
                    'updated_at' => $tag['created_at'],
                    'relevance_score' => $relevanceScore,
                    'url' => '/tags'
                ];
            }

        } catch (Exception $e) {
            error_log("Tag search error: " . $e->getMessage());
        }

        return $results;
    }

    private function calculateRelevanceScore($query, $title, $content) {
        $score = 0;
        $queryLower = strtolower($query);
        $titleLower = strtolower($title);
        $contentLower = strtolower($content);

        // Exact title match gets highest score
        if ($titleLower === $queryLower) {
            $score += 100;
        }
        // Title starts with query
        elseif (strpos($titleLower, $queryLower) === 0) {
            $score += 80;
        }
        // Title contains query
        elseif (strpos($titleLower, $queryLower) !== false) {
            $score += 60;
        }

        // Content contains query
        if (strpos($contentLower, $queryLower) !== false) {
            $score += 20;
        }

        // Word boundary matches get bonus points
        $queryWords = explode(' ', $queryLower);
        foreach ($queryWords as $word) {
            if (strpos($titleLower, $word) !== false) {
                $score += 10;
            }
            if (strpos($contentLower, $word) !== false) {
                $score += 5;
            }
        }

        return $score;
    }

    private function highlightSearchTerms($query, $text) {
        $queryWords = explode(' ', $query);
        $highlightedText = $text;

        foreach ($queryWords as $word) {
            if (strlen($word) > 2) { // Only highlight words longer than 2 characters
                $highlightedText = preg_replace(
                    '/\b(' . preg_quote($word, '/') . ')\b/i',
                    '<mark>$1</mark>',
                    $highlightedText
                );
            }
        }

        return $highlightedText;
    }

    public function getSuggestions() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $query = trim($_GET['q'] ?? '');
        
        try {
            $suggestions = [];
            
            if (strlen($query) >= 2) {
                // Get note titles
                $stmt = $this->db->prepare("
                    SELECT DISTINCT title FROM notes 
                    WHERE user_id = ? AND is_deleted = 0 AND is_archived = 0 
                    AND title LIKE ? 
                    ORDER BY title ASC 
                    LIMIT 5
                ");
                $stmt->execute([$userId, '%' . $query . '%']);
                $noteTitles = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Get task titles
                $stmt = $this->db->prepare("
                    SELECT DISTINCT title FROM tasks 
                    WHERE user_id = ? AND is_deleted = 0 AND is_archived = 0 
                    AND title LIKE ? 
                    ORDER BY title ASC 
                    LIMIT 5
                ");
                $stmt->execute([$userId, '%' . $query . '%']);
                $taskTitles = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Get tag names
                $stmt = $this->db->prepare("
                    SELECT DISTINCT name FROM tags 
                    WHERE user_id = ? AND name LIKE ? 
                    ORDER BY name ASC 
                    LIMIT 5
                ");
                $stmt->execute([$userId, '%' . $query . '%']);
                $tagNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                $suggestions = array_merge($noteTitles, $taskTitles, $tagNames);
                $suggestions = array_unique($suggestions);
                $suggestions = array_slice($suggestions, 0, 10);
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (Exception $e) {
            error_log("Suggestions error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to get suggestions']);
        }
    }
}

