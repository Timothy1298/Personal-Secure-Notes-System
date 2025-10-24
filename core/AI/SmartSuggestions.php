<?php
namespace Core\AI;

use PDO;
use Exception;

class SmartSuggestions {
    private $db;
    private $contentAnalyzer;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->contentAnalyzer = new ContentAnalyzer($db);
    }
    
    /**
     * Generate smart suggestions for a given piece of content (e.g., a note or task).
     */
    public function generateSuggestions(int $userId, string $content, string $title = ''): array {
        $suggestions = [];

        // 1. Content Analysis based suggestions
        $analysis = $this->contentAnalyzer->analyzeText($content);
        if (!empty($analysis['keywords'])) {
            $suggestions[] = [
                'type' => 'keywords',
                'title' => 'Extracted Keywords',
                'data' => $analysis['keywords'],
                'confidence' => 0.9
            ];
        }
        if ($analysis['sentiment'] !== 'neutral') {
            $suggestions[] = [
                'type' => 'sentiment',
                'title' => 'Content Sentiment',
                'data' => $analysis['sentiment'],
                'confidence' => 0.7
            ];
        }
        if (!empty($analysis['summary_suggestion'])) {
            $suggestions[] = [
                'type' => 'summary',
                'title' => 'Summary Suggestion',
                'data' => $analysis['summary_suggestion'],
                'confidence' => 0.8
            ];
        }

        // 2. Auto-tagging suggestions (based on keywords and existing tags)
        $tagSuggestions = $this->suggestTags($userId, $content, $analysis['keywords']);
        if (!empty($tagSuggestions)) {
            $suggestions[] = [
                'type' => 'tags',
                'title' => 'Suggested Tags',
                'data' => $tagSuggestions,
                'confidence' => 0.85
            ];
        }

        // 3. Category suggestions (based on content and user's past categorization)
        $categorySuggestion = $this->suggestCategory($userId, $content, $analysis['keywords']);
        if ($categorySuggestion) {
            $suggestions[] = [
                'type' => 'category',
                'title' => 'Suggested Category',
                'data' => $categorySuggestion,
                'confidence' => 0.75
            ];
        }

        // 4. Priority suggestions (based on keywords like "urgent", "deadline", "important")
        $prioritySuggestion = $this->suggestPriority($content);
        if ($prioritySuggestion) {
            $suggestions[] = [
                'type' => 'priority',
                'title' => 'Suggested Priority',
                'data' => $prioritySuggestion,
                'confidence' => 0.6
            ];
        }

        // 5. Related notes/tasks suggestions (based on keyword similarity)
        $relatedItems = $this->suggestRelatedItems($userId, $content, $analysis['keywords']);
        if (!empty($relatedItems)) {
            $suggestions[] = [
                'type' => 'related_items',
                'title' => 'Related Notes/Tasks',
                'data' => $relatedItems,
                'confidence' => 0.9
            ];
        }
        
        // 6. Actionable item suggestions (e.g., "todo", "follow up")
        $actionableItems = $this->suggestActionableItems($content);
        if (!empty($actionableItems)) {
            $suggestions[] = [
                'type' => 'actionable_items',
                'title' => 'Suggested Actions',
                'data' => $actionableItems,
                'confidence' => 0.7
            ];
        }

        return $suggestions;
    }

    /**
     * Generate smart suggestions for notes
     */
    public function generateNoteSuggestions($userId, $content, $title = '') {
        $suggestions = [];
        
        try {
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
                    'title' => 'Content Improvements',
                    'data' => $contentSuggestions,
                    'confidence' => 0.7
                ];
            }
            
            // Save suggestions to database
            $this->saveSuggestions($userId, 'note', $suggestions);
            
            return $suggestions;
        } catch (Exception $e) {
            error_log("Note suggestions generation failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate smart suggestions for tasks
     */
    public function generateTaskSuggestions($userId, $content, $title = '') {
        $suggestions = [];
        
        try {
            // Due date suggestions
            $dueDateSuggestion = $this->suggestDueDate($content, $title);
            if ($dueDateSuggestion) {
                $suggestions[] = [
                    'type' => 'due_date',
                    'title' => 'Suggested Due Date',
                    'data' => $dueDateSuggestion,
                    'confidence' => 0.8
                ];
            }
            
            // Priority suggestions
            $prioritySuggestion = $this->suggestTaskPriority($content, $title);
            if ($prioritySuggestion) {
                $suggestions[] = [
                    'type' => 'priority',
                    'title' => 'Suggested Priority',
                    'data' => $prioritySuggestion,
                    'confidence' => 0.7
                ];
            }
            
            // Tag suggestions
            $tagSuggestions = $this->suggestTags($content, $title);
            if (!empty($tagSuggestions)) {
                $suggestions[] = [
                    'type' => 'tags',
                    'title' => 'Suggested Tags',
                    'data' => $tagSuggestions,
                    'confidence' => 0.8
                ];
            }
            
            // Subtask suggestions
            $subtaskSuggestions = $this->suggestSubtasks($content, $title);
            if (!empty($subtaskSuggestions)) {
                $suggestions[] = [
                    'type' => 'subtasks',
                    'title' => 'Suggested Subtasks',
                    'data' => $subtaskSuggestions,
                    'confidence' => 0.6
                ];
            }
            
            // Related tasks suggestions
            $relatedTasks = $this->suggestRelatedTasks($userId, $content, $title);
            if (!empty($relatedTasks)) {
                $suggestions[] = [
                    'type' => 'related_tasks',
                    'title' => 'Related Tasks',
                    'data' => $relatedTasks,
                    'confidence' => 0.9
                ];
            }
            
            // Save suggestions to database
            $this->saveSuggestions($userId, 'task', $suggestions);
            
            return $suggestions;
        } catch (Exception $e) {
            error_log("Task suggestions generation failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Suggest tags based on content
     */
    private function suggestTags($content, $title = '') {
        $text = $title . ' ' . $content;
        $keywords = $this->contentAnalyzer->extractKeywords($text, 15);
        
        // Get existing tags for the user
        $existingTags = $this->getExistingTags();
        
        $suggestions = [];
        foreach ($keywords as $keyword) {
            // Check if similar tag exists
            $similarTag = $this->findSimilarTag($keyword, $existingTags);
            if ($similarTag) {
                $suggestions[] = [
                    'name' => $similarTag['name'],
                    'reason' => 'Similar to existing tag',
                    'confidence' => 0.9
                ];
            } else {
                $suggestions[] = [
                    'name' => $keyword,
                    'reason' => 'Extracted from content',
                    'confidence' => 0.7
                ];
            }
        }
        
        return array_slice($suggestions, 0, 5);
    }
    
    /**
     * Suggest category based on content
     */
    private function suggestCategory($content, $title = '') {
        $text = $title . ' ' . $content;
        $topics = $this->contentAnalyzer->detectTopics($text);
        
        if (empty($topics)) {
            return null;
        }
        
        $topTopic = $topics[0];
        
        // Map topics to categories
        $topicToCategory = [
            'technology' => 'Technology',
            'business' => 'Business',
            'education' => 'Education',
            'health' => 'Health',
            'travel' => 'Travel',
            'food' => 'Food',
            'entertainment' => 'Entertainment',
            'sports' => 'Sports'
        ];
        
        $category = $topicToCategory[$topTopic['category']] ?? 'General';
        
        return [
            'category' => $category,
            'confidence' => $topTopic['confidence'],
            'reason' => 'Detected from content topics'
        ];
    }
    
    /**
     * Suggest priority based on content
     */
    private function suggestPriority($content, $title = '') {
        $text = strtolower($title . ' ' . $content);
        
        // High priority keywords
        $highPriorityKeywords = ['urgent', 'asap', 'immediately', 'critical', 'important', 'deadline', 'emergency'];
        $mediumPriorityKeywords = ['soon', 'later', 'sometime', 'when possible', 'moderate'];
        
        foreach ($highPriorityKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return [
                    'priority' => 'high',
                    'confidence' => 0.8,
                    'reason' => "Contains high priority keyword: {$keyword}"
                ];
            }
        }
        
        foreach ($mediumPriorityKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return [
                    'priority' => 'medium',
                    'confidence' => 0.7,
                    'reason' => "Contains medium priority keyword: {$keyword}"
                ];
            }
        }
        
        // Default to low priority
        return [
            'priority' => 'low',
            'confidence' => 0.5,
            'reason' => 'No priority indicators found'
        ];
    }
    
    /**
     * Suggest task priority
     */
    private function suggestTaskPriority($content, $title = '') {
        $priority = $this->suggestPriority($content, $title);
        
        // Additional task-specific priority logic
        $text = strtolower($title . ' ' . $content);
        
        // Check for time-sensitive words
        $timeSensitiveWords = ['today', 'tomorrow', 'this week', 'deadline', 'due'];
        foreach ($timeSensitiveWords as $word) {
            if (strpos($text, $word) !== false) {
                $priority['priority'] = 'high';
                $priority['confidence'] = 0.9;
                $priority['reason'] = "Time-sensitive task: {$word}";
                break;
            }
        }
        
        return $priority;
    }
    
    /**
     * Suggest due date based on content
     */
    private function suggestDueDate($content, $title = '') {
        $text = strtolower($title . ' ' . $content);
        
        // Look for date patterns
        $datePatterns = [
            'today' => 0,
            'tomorrow' => 1,
            'this week' => 7,
            'next week' => 14,
            'this month' => 30,
            'next month' => 60
        ];
        
        foreach ($datePatterns as $pattern => $days) {
            if (strpos($text, $pattern) !== false) {
                $dueDate = date('Y-m-d', strtotime("+{$days} days"));
                return [
                    'due_date' => $dueDate,
                    'confidence' => 0.8,
                    'reason' => "Detected time reference: {$pattern}"
                ];
            }
        }
        
        // Look for specific date patterns
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $text, $matches)) {
            $dueDate = $matches[3] . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return [
                'due_date' => $dueDate,
                'confidence' => 0.9,
                'reason' => 'Detected specific date'
            ];
        }
        
        return null;
    }
    
    /**
     * Suggest subtasks based on content
     */
    private function suggestSubtasks($content, $title = '') {
        $text = $title . ' ' . $content;
        
        // Look for action items or steps
        $actionPatterns = [
            '/(?:first|1\.|step 1)[:\s]+([^.!?]+)/i',
            '/(?:second|2\.|step 2)[:\s]+([^.!?]+)/i',
            '/(?:third|3\.|step 3)[:\s]+([^.!?]+)/i',
            '/(?:then|next|after that)[:\s]+([^.!?]+)/i',
            '/(?:finally|last|lastly)[:\s]+([^.!?]+)/i'
        ];
        
        $subtasks = [];
        foreach ($actionPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $subtasks[] = [
                        'title' => trim($match),
                        'confidence' => 0.7,
                        'reason' => 'Detected as action step'
                    ];
                }
            }
        }
        
        return array_slice($subtasks, 0, 5);
    }
    
    /**
     * Suggest related notes
     */
    private function suggestRelatedNotes($userId, $content, $title = '') {
        try {
            $keywords = $this->contentAnalyzer->extractKeywords($title . ' ' . $content, 10);
            
            if (empty($keywords)) {
                return [];
            }
            
            $keywordPlaceholders = str_repeat('?,', count($keywords) - 1) . '?';
            
            $stmt = $this->db->prepare("
                SELECT n.id, n.title, n.content, n.created_at,
                       COUNT(nt.tag_id) as tag_matches
                FROM notes n
                LEFT JOIN note_tags nt ON n.id = nt.note_id
                LEFT JOIN tags t ON nt.tag_id = t.id
                WHERE n.user_id = ? 
                AND n.id != ?
                AND (n.title LIKE CONCAT('%', ?, '%') 
                     OR n.content LIKE CONCAT('%', ?, '%')
                     OR t.name IN ({$keywordPlaceholders}))
                GROUP BY n.id
                ORDER BY tag_matches DESC, n.created_at DESC
                LIMIT 5
            ");
            
            $params = [$userId, 0, $keywords[0], $keywords[0]];
            $params = array_merge($params, $keywords);
            
            $stmt->execute($params);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $relatedNotes = [];
            foreach ($notes as $note) {
                $relatedNotes[] = [
                    'id' => $note['id'],
                    'title' => $note['title'],
                    'excerpt' => substr(strip_tags($note['content']), 0, 100) . '...',
                    'created_at' => $note['created_at'],
                    'relevance_score' => $note['tag_matches']
                ];
            }
            
            return $relatedNotes;
        } catch (Exception $e) {
            error_log("Related notes suggestion failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Suggest related tasks
     */
    private function suggestRelatedTasks($userId, $content, $title = '') {
        try {
            $keywords = $this->contentAnalyzer->extractKeywords($title . ' ' . $content, 10);
            
            if (empty($keywords)) {
                return [];
            }
            
            $keywordPlaceholders = str_repeat('?,', count($keywords) - 1) . '?';
            
            $stmt = $this->db->prepare("
                SELECT t.id, t.title, t.description, t.status, t.due_date,
                       COUNT(tt.tag_id) as tag_matches
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags tag ON tt.tag_id = tag.id
                WHERE t.user_id = ? 
                AND t.id != ?
                AND (t.title LIKE CONCAT('%', ?, '%') 
                     OR t.description LIKE CONCAT('%', ?, '%')
                     OR tag.name IN ({$keywordPlaceholders}))
                GROUP BY t.id
                ORDER BY tag_matches DESC, t.created_at DESC
                LIMIT 5
            ");
            
            $params = [$userId, 0, $keywords[0], $keywords[0]];
            $params = array_merge($params, $keywords);
            
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $relatedTasks = [];
            foreach ($tasks as $task) {
                $relatedTasks[] = [
                    'id' => $task['id'],
                    'title' => $task['title'],
                    'status' => $task['status'],
                    'due_date' => $task['due_date'],
                    'relevance_score' => $task['tag_matches']
                ];
            }
            
            return $relatedTasks;
        } catch (Exception $e) {
            error_log("Related tasks suggestion failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Suggest content improvements
     */
    private function suggestContentImprovements($content) {
        $suggestions = [];
        
        // Analyze content
        $readability = $this->contentAnalyzer->analyzeReadability($content);
        $sentiment = $this->contentAnalyzer->analyzeSentiment($content);
        
        // Readability suggestions
        if ($readability['flesch_score'] < 50) {
            $suggestions[] = [
                'type' => 'readability',
                'title' => 'Improve Readability',
                'description' => 'Your content is difficult to read. Consider using shorter sentences and simpler words.',
                'priority' => 'high'
            ];
        }
        
        // Sentiment suggestions
        if ($sentiment['score'] < -0.3) {
            $suggestions[] = [
                'type' => 'tone',
                'title' => 'Adjust Tone',
                'description' => 'Your content has a negative tone. Consider adding more positive language.',
                'priority' => 'medium'
            ];
        }
        
        // Length suggestions
        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount < 50) {
            $suggestions[] = [
                'type' => 'length',
                'title' => 'Add More Detail',
                'description' => 'Your content is quite short. Consider adding more details and examples.',
                'priority' => 'low'
            ];
        } elseif ($wordCount > 1000) {
            $suggestions[] = [
                'type' => 'length',
                'title' => 'Consider Breaking Up',
                'description' => 'Your content is quite long. Consider breaking it into smaller sections.',
                'priority' => 'medium'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get existing tags
     */
    private function getExistingTags() {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM tags ORDER BY usage_count DESC LIMIT 100");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get existing tags failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find similar tag
     */
    private function findSimilarTag($keyword, $existingTags) {
        foreach ($existingTags as $tag) {
            if (similar_text($keyword, $tag['name']) > 80) {
                return $tag;
            }
        }
        return null;
    }
    
    /**
     * Save suggestions to database
     */
    private function saveSuggestions($userId, $targetType, $suggestions) {
        try {
            foreach ($suggestions as $suggestion) {
                $stmt = $this->db->prepare("
                    INSERT INTO smart_suggestions 
                    (user_id, suggestion_type, target_type, suggestion_data, confidence, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $userId,
                    $suggestion['type'],
                    $targetType,
                    json_encode($suggestion),
                    $suggestion['confidence']
                ]);
            }
        } catch (Exception $e) {
            error_log("Save suggestions failed: " . $e->getMessage());
        }
    }
}
