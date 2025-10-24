<?php
namespace Core;

use PDO;
use Exception;

class AIService {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Generate smart suggestions for notes
     */
    public function generateNoteSuggestions($userId, $content, $title = '') {
        $suggestions = [];
        
        // Auto-tagging suggestions
        $tagSuggestions = $this->suggestTags($content, $title);
        if (!empty($tagSuggestions)) {
            $suggestions[] = [
                'type' => 'tags',
                'title' => 'Suggested Tags',
                'data' => $tagSuggestions,
                'confidence' => 0.8
            ];
        }
        
        // Category suggestions
        $categorySuggestion = $this->suggestCategory($content, $title);
        if ($categorySuggestion) {
            $suggestions[] = [
                'type' => 'category',
                'title' => 'Suggested Category',
                'data' => $categorySuggestion,
                'confidence' => 0.7
            ];
        }
        
        // Priority suggestions
        $prioritySuggestion = $this->suggestPriority($content, $title);
        if ($prioritySuggestion) {
            $suggestions[] = [
                'type' => 'priority',
                'title' => 'Suggested Priority',
                'data' => $prioritySuggestion,
                'confidence' => 0.6
            ];
        }
        
        // Related notes suggestions
        $relatedNotes = $this->suggestRelatedNotes($userId, $content, $title);
        if (!empty($relatedNotes)) {
            $suggestions[] = [
                'type' => 'related_notes',
                'title' => 'Related Notes',
                'data' => $relatedNotes,
                'confidence' => 0.9
            ];
        }
        
        // Content improvement suggestions
        $contentSuggestions = $this->suggestContentImprovements($content);
        if (!empty($contentSuggestions)) {
            $suggestions[] = [
                'type' => 'content_improvements',
                'title' => 'Content Suggestions',
                'data' => $contentSuggestions,
                'confidence' => 0.5
            ];
        }
        
        // Save suggestions to database
        $this->saveSuggestions($userId, 'note', $suggestions);
        
        return $suggestions;
    }
    
    /**
     * Generate smart suggestions for tasks
     */
    public function generateTaskSuggestions($userId, $title, $description = '') {
        $suggestions = [];
        
        // Due date suggestions
        $dueDateSuggestion = $this->suggestDueDate($title, $description);
        if ($dueDateSuggestion) {
            $suggestions[] = [
                'type' => 'due_date',
                'title' => 'Suggested Due Date',
                'data' => $dueDateSuggestion,
                'confidence' => 0.7
            ];
        }
        
        // Priority suggestions
        $prioritySuggestion = $this->suggestTaskPriority($title, $description);
        if ($prioritySuggestion) {
            $suggestions[] = [
                'type' => 'priority',
                'title' => 'Suggested Priority',
                'data' => $prioritySuggestion,
                'confidence' => 0.8
            ];
        }
        
        // Tag suggestions
        $tagSuggestions = $this->suggestTags($title . ' ' . $description);
        if (!empty($tagSuggestions)) {
            $suggestions[] = [
                'type' => 'tags',
                'title' => 'Suggested Tags',
                'data' => $tagSuggestions,
                'confidence' => 0.6
            ];
        }
        
        // Subtask suggestions
        $subtaskSuggestions = $this->suggestSubtasks($title, $description);
        if (!empty($subtaskSuggestions)) {
            $suggestions[] = [
                'type' => 'subtasks',
                'title' => 'Suggested Subtasks',
                'data' => $subtaskSuggestions,
                'confidence' => 0.5
            ];
        }
        
        // Save suggestions to database
        $this->saveSuggestions($userId, 'task', $suggestions);
        
        return $suggestions;
    }
    
    /**
     * Suggest tags based on content
     */
    private function suggestTags($content, $title = '') {
        $text = strtolower($title . ' ' . $content);
        $suggestions = [];
        
        // Define keyword patterns for common tags
        $tagPatterns = [
            'work' => ['meeting', 'project', 'deadline', 'client', 'business', 'office', 'work'],
            'personal' => ['family', 'home', 'personal', 'myself', 'private'],
            'health' => ['exercise', 'doctor', 'medicine', 'health', 'fitness', 'gym', 'diet'],
            'finance' => ['money', 'budget', 'expense', 'income', 'investment', 'bank', 'payment'],
            'learning' => ['study', 'course', 'book', 'education', 'learn', 'training', 'tutorial'],
            'travel' => ['trip', 'vacation', 'flight', 'hotel', 'travel', 'destination', 'booking'],
            'shopping' => ['buy', 'purchase', 'shop', 'store', 'shopping', 'order', 'delivery'],
            'home' => ['house', 'home', 'repair', 'maintenance', 'cleaning', 'furniture'],
            'family' => ['family', 'children', 'kids', 'parents', 'siblings', 'relatives'],
            'projects' => ['project', 'planning', 'development', 'design', 'implementation']
        ];
        
        foreach ($tagPatterns as $tag => $keywords) {
            $matches = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $matches++;
                }
            }
            
            if ($matches > 0) {
                $suggestions[] = [
                    'tag' => $tag,
                    'confidence' => min($matches / count($keywords), 1.0),
                    'reason' => "Found {$matches} related keywords"
                ];
            }
        }
        
        // Sort by confidence
        usort($suggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return array_slice($suggestions, 0, 3); // Return top 3 suggestions
    }
    
    /**
     * Suggest category based on content
     */
    private function suggestCategory($content, $title = '') {
        $text = strtolower($title . ' ' . $content);
        
        $categories = [
            'work' => ['meeting', 'project', 'deadline', 'client', 'business'],
            'personal' => ['family', 'home', 'personal', 'myself'],
            'health' => ['exercise', 'doctor', 'medicine', 'health'],
            'finance' => ['money', 'budget', 'expense', 'income'],
            'learning' => ['study', 'course', 'book', 'education'],
            'travel' => ['trip', 'vacation', 'flight', 'hotel'],
            'shopping' => ['buy', 'purchase', 'shop', 'store'],
            'home' => ['house', 'home', 'repair', 'maintenance'],
            'family' => ['family', 'children', 'kids', 'parents'],
            'projects' => ['project', 'planning', 'development']
        ];
        
        $bestCategory = null;
        $bestScore = 0;
        
        foreach ($categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCategory = $category;
            }
        }
        
        return $bestCategory ? [
            'category' => $bestCategory,
            'confidence' => min($bestScore / 3, 1.0)
        ] : null;
    }
    
    /**
     * Suggest priority based on content
     */
    private function suggestPriority($content, $title = '') {
        $text = strtolower($title . ' ' . $content);
        
        $highPriorityKeywords = ['urgent', 'asap', 'immediately', 'critical', 'important', 'deadline'];
        $lowPriorityKeywords = ['someday', 'maybe', 'optional', 'low priority', 'when possible'];
        
        foreach ($highPriorityKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return [
                    'priority' => 'high',
                    'confidence' => 0.8,
                    'reason' => "Contains high priority keyword: {$keyword}"
                ];
            }
        }
        
        foreach ($lowPriorityKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return [
                    'priority' => 'low',
                    'confidence' => 0.7,
                    'reason' => "Contains low priority keyword: {$keyword}"
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Suggest task priority
     */
    private function suggestTaskPriority($title, $description = '') {
        return $this->suggestPriority($description, $title);
    }
    
    /**
     * Suggest due date based on content
     */
    private function suggestDueDate($title, $description = '') {
        $text = strtolower($title . ' ' . $description);
        
        // Look for date patterns
        $datePatterns = [
            'today' => 0,
            'tomorrow' => 1,
            'next week' => 7,
            'next month' => 30,
            'this week' => 3,
            'this month' => 15
        ];
        
        foreach ($datePatterns as $pattern => $days) {
            if (strpos($text, $pattern) !== false) {
                return [
                    'due_date' => date('Y-m-d', strtotime("+{$days} days")),
                    'confidence' => 0.8,
                    'reason' => "Contains date reference: {$pattern}"
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Suggest related notes
     */
    private function suggestRelatedNotes($userId, $content, $title = '') {
        try {
            $text = strtolower($title . ' ' . $content);
            $words = array_filter(explode(' ', $text), function($word) {
                return strlen($word) > 3;
            });
            
            if (empty($words)) {
                return [];
            }
            
            $placeholders = str_repeat('?,', count($words) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT id, title, content, created_at
                FROM notes 
                WHERE user_id = ? 
                AND (LOWER(title) LIKE ? OR LOWER(content) LIKE ?)
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            
            $searchTerm = '%' . implode('%', $words) . '%';
            $stmt->execute([$userId, $searchTerm, $searchTerm]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(function($note) {
                return [
                    'id' => $note['id'],
                    'title' => $note['title'],
                    'excerpt' => substr($note['content'], 0, 100) . '...',
                    'created_at' => $note['created_at']
                ];
            }, $notes);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Suggest content improvements
     */
    private function suggestContentImprovements($content) {
        $suggestions = [];
        
        // Check for common issues
        if (strlen($content) < 50) {
            $suggestions[] = [
                'type' => 'length',
                'message' => 'Consider adding more detail to make this note more useful',
                'confidence' => 0.6
            ];
        }
        
        if (!preg_match('/[.!?]$/', trim($content))) {
            $suggestions[] = [
                'type' => 'punctuation',
                'message' => 'Consider ending with proper punctuation',
                'confidence' => 0.4
            ];
        }
        
        if (strpos($content, '#') === false && strpos($content, '*') === false) {
            $suggestions[] = [
                'type' => 'formatting',
                'message' => 'Consider using markdown formatting for better readability',
                'confidence' => 0.3
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Suggest subtasks
     */
    private function suggestSubtasks($title, $description = '') {
        $text = strtolower($title . ' ' . $description);
        $suggestions = [];
        
        // Common task patterns
        if (strpos($text, 'plan') !== false || strpos($text, 'organize') !== false) {
            $suggestions[] = [
                'title' => 'Research requirements',
                'description' => 'Gather all necessary information and requirements'
            ];
            $suggestions[] = [
                'title' => 'Create timeline',
                'description' => 'Set up a schedule and milestones'
            ];
        }
        
        if (strpos($text, 'meeting') !== false) {
            $suggestions[] = [
                'title' => 'Prepare agenda',
                'description' => 'Create and share meeting agenda'
            ];
            $suggestions[] = [
                'title' => 'Send invitations',
                'description' => 'Invite all required participants'
            ];
        }
        
        if (strpos($text, 'project') !== false) {
            $suggestions[] = [
                'title' => 'Define scope',
                'description' => 'Clearly define project boundaries and deliverables'
            ];
            $suggestions[] = [
                'title' => 'Assign resources',
                'description' => 'Allocate team members and budget'
            ];
        }
        
        return array_slice($suggestions, 0, 3);
    }
    
    /**
     * Save suggestions to database
     */
    private function saveSuggestions($userId, $type, $suggestions) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_suggestions 
                (user_id, suggestion_type, suggestions_data, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $type,
                json_encode($suggestions)
            ]);
        } catch (Exception $e) {
            error_log("Error saving AI suggestions: " . $e->getMessage());
        }
    }
    
    /**
     * Get user's AI suggestions history
     */
    public function getUserSuggestions($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ai_suggestions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
