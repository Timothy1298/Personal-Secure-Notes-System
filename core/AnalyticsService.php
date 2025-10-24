<?php
namespace Core;

use PDO;
use Exception;

class AnalyticsService {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Track user activity
     */
    public function trackActivity($userId, $activityType, $metadata = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_analytics 
                (user_id, metric_type, metric_value, metadata, recorded_at) 
                VALUES (?, ?, 1, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $activityType,
                json_encode($metadata)
            ]);
        } catch (Exception $e) {
            error_log("Error tracking activity: " . $e->getMessage());
        }
    }
    
    /**
     * Get user productivity insights
     */
    public function getProductivityInsights($userId, $days = 30) {
        $insights = [];
        
        // Notes created per day
        $notesPerDay = $this->getNotesPerDay($userId, $days);
        $insights['notes_per_day'] = $notesPerDay;
        
        // Tasks completed per day
        $tasksPerDay = $this->getTasksPerDay($userId, $days);
        $insights['tasks_per_day'] = $tasksPerDay;
        
        // Most productive hours
        $productiveHours = $this->getMostProductiveHours($userId, $days);
        $insights['productive_hours'] = $productiveHours;
        
        // Most used tags
        $popularTags = $this->getPopularTags($userId, $days);
        $insights['popular_tags'] = $popularTags;
        
        // Content analysis
        $contentAnalysis = $this->analyzeContent($userId, $days);
        $insights['content_analysis'] = $contentAnalysis;
        
        // Productivity trends
        $trends = $this->getProductivityTrends($userId, $days);
        $insights['trends'] = $trends;
        
        return $insights;
    }
    
    /**
     * Get notes created per day
     */
    private function getNotesPerDay($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute([$userId, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get tasks completed per day
     */
    private function getTasksPerDay($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT DATE(updated_at) as date, COUNT(*) as count
                FROM tasks 
                WHERE user_id = ? 
                AND status = 'completed'
                AND updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(updated_at)
                ORDER BY date DESC
            ");
            $stmt->execute([$userId, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get most productive hours
     */
    private function getMostProductiveHours($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT HOUR(created_at) as hour, COUNT(*) as count
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY HOUR(created_at)
                ORDER BY count DESC
                LIMIT 5
            ");
            $stmt->execute([$userId, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get popular tags
     */
    private function getPopularTags($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.name, t.color, COUNT(nt.note_id) as usage_count
                FROM tags t
                JOIN note_tags nt ON t.id = nt.tag_id
                JOIN notes n ON nt.note_id = n.id
                WHERE t.user_id = ? 
                AND n.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY t.id, t.name, t.color
                ORDER BY usage_count DESC
                LIMIT 10
            ");
            $stmt->execute([$userId, $days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Analyze content patterns
     */
    private function analyzeContent($userId, $days) {
        try {
            // Get all notes content
            $stmt = $this->db->prepare("
                SELECT content, word_count, created_at
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$userId, $days]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $analysis = [
                'total_notes' => count($notes),
                'total_words' => array_sum(array_column($notes, 'word_count')),
                'avg_words_per_note' => 0,
                'longest_note' => 0,
                'shortest_note' => 0,
                'common_words' => [],
                'writing_patterns' => []
            ];
            
            if (!empty($notes)) {
                $wordCounts = array_column($notes, 'word_count');
                $analysis['avg_words_per_note'] = round(array_sum($wordCounts) / count($wordCounts), 2);
                $analysis['longest_note'] = max($wordCounts);
                $analysis['shortest_note'] = min($wordCounts);
                
                // Analyze common words
                $allContent = implode(' ', array_column($notes, 'content'));
                $words = str_word_count(strtolower($allContent), 1);
                $wordFreq = array_count_values($words);
                arsort($wordFreq);
                $analysis['common_words'] = array_slice($wordFreq, 0, 20, true);
                
                // Analyze writing patterns
                $analysis['writing_patterns'] = $this->analyzeWritingPatterns($notes);
            }
            
            return $analysis;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Analyze writing patterns
     */
    private function analyzeWritingPatterns($notes) {
        $patterns = [
            'uses_markdown' => 0,
            'uses_lists' => 0,
            'uses_headers' => 0,
            'avg_sentence_length' => 0,
            'question_ratio' => 0
        ];
        
        $totalSentences = 0;
        $totalQuestions = 0;
        $totalLength = 0;
        
        foreach ($notes as $note) {
            $content = $note['content'];
            
            // Check for markdown usage
            if (strpos($content, '#') !== false) $patterns['uses_markdown']++;
            if (strpos($content, '-') !== false || strpos($content, '*') !== false) $patterns['uses_lists']++;
            if (strpos($content, '#') !== false) $patterns['uses_headers']++;
            
            // Analyze sentences
            $sentences = preg_split('/[.!?]+/', $content);
            $totalSentences += count($sentences);
            
            foreach ($sentences as $sentence) {
                $totalLength += strlen(trim($sentence));
                if (strpos($sentence, '?') !== false) $totalQuestions++;
            }
        }
        
        if ($totalSentences > 0) {
            $patterns['avg_sentence_length'] = round($totalLength / $totalSentences, 2);
            $patterns['question_ratio'] = round(($totalQuestions / $totalSentences) * 100, 2);
        }
        
        $totalNotes = count($notes);
        if ($totalNotes > 0) {
            $patterns['uses_markdown'] = round(($patterns['uses_markdown'] / $totalNotes) * 100, 2);
            $patterns['uses_lists'] = round(($patterns['uses_lists'] / $totalNotes) * 100, 2);
            $patterns['uses_headers'] = round(($patterns['uses_headers'] / $totalNotes) * 100, 2);
        }
        
        return $patterns;
    }
    
    /**
     * Get productivity trends
     */
    private function getProductivityTrends($userId, $days) {
        try {
            // Weekly trends
            $stmt = $this->db->prepare("
                SELECT 
                    WEEK(created_at) as week,
                    YEAR(created_at) as year,
                    COUNT(*) as notes_count
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY YEAR(created_at), WEEK(created_at)
                ORDER BY year DESC, week DESC
                LIMIT 8
            ");
            $stmt->execute([$userId, $days]);
            $weeklyTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Task completion trends
            $stmt = $this->db->prepare("
                SELECT 
                    WEEK(updated_at) as week,
                    YEAR(updated_at) as year,
                    COUNT(*) as tasks_completed
                FROM tasks 
                WHERE user_id = ? 
                AND status = 'completed'
                AND updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY YEAR(updated_at), WEEK(updated_at)
                ORDER BY year DESC, week DESC
                LIMIT 8
            ");
            $stmt->execute([$userId, $days]);
            $taskTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'weekly_notes' => $weeklyTrends,
                'weekly_tasks' => $taskTrends
            ];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get personalized recommendations
     */
    public function getPersonalizedRecommendations($userId) {
        $recommendations = [];
        
        // Analyze user patterns
        $insights = $this->getProductivityInsights($userId, 30);
        
        // Generate recommendations based on patterns
        if (isset($insights['notes_per_day']) && count($insights['notes_per_day']) < 5) {
            $recommendations[] = [
                'type' => 'productivity',
                'title' => 'Increase Note Taking',
                'message' => 'You\'ve been creating fewer notes recently. Consider setting a daily goal to capture more ideas.',
                'action' => 'Set a daily note-taking goal',
                'priority' => 'medium'
            ];
        }
        
        if (isset($insights['productive_hours']) && !empty($insights['productive_hours'])) {
            $bestHour = $insights['productive_hours'][0]['hour'];
            $recommendations[] = [
                'type' => 'optimization',
                'title' => 'Optimize Your Schedule',
                'message' => "You're most productive at {$bestHour}:00. Consider scheduling important tasks during this time.",
                'action' => 'Schedule important tasks at your peak hour',
                'priority' => 'high'
            ];
        }
        
        if (isset($insights['content_analysis']['writing_patterns']['uses_markdown']) && 
            $insights['content_analysis']['writing_patterns']['uses_markdown'] < 30) {
            $recommendations[] = [
                'type' => 'formatting',
                'title' => 'Improve Note Formatting',
                'message' => 'Consider using markdown formatting to make your notes more organized and readable.',
                'action' => 'Learn markdown formatting',
                'priority' => 'low'
            ];
        }
        
        // Check for overdue tasks
        $overdueTasks = $this->getOverdueTasks($userId);
        if (count($overdueTasks) > 0) {
            $recommendations[] = [
                'type' => 'urgent',
                'title' => 'Overdue Tasks',
                'message' => "You have " . count($overdueTasks) . " overdue tasks. Consider reviewing and updating them.",
                'action' => 'Review overdue tasks',
                'priority' => 'high'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Get overdue tasks
     */
    private function getOverdueTasks($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, title, due_date
                FROM tasks 
                WHERE user_id = ? 
                AND status != 'completed'
                AND due_date < NOW()
                ORDER BY due_date ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate productivity score
     */
    public function calculateProductivityScore($userId, $days = 7) {
        try {
            // Get recent activity
            $notesCount = $this->getNotesCount($userId, $days);
            $tasksCompleted = $this->getTasksCompletedCount($userId, $days);
            $overdueTasks = count($this->getOverdueTasks($userId));
            
            // Calculate score (0-100)
            $score = 0;
            
            // Notes component (40% of score)
            $notesScore = min($notesCount * 5, 40);
            $score += $notesScore;
            
            // Tasks component (50% of score)
            $tasksScore = min($tasksCompleted * 10, 50);
            $score += $tasksScore;
            
            // Overdue penalty (10% penalty)
            $overduePenalty = min($overdueTasks * 5, 10);
            $score -= $overduePenalty;
            
            return max(0, min(100, $score));
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get notes count for period
     */
    private function getNotesCount($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM notes 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$userId, $days]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get tasks completed count for period
     */
    private function getTasksCompletedCount($userId, $days) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM tasks 
                WHERE user_id = ? 
                AND status = 'completed'
                AND updated_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$userId, $days]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
