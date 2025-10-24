<?php
namespace Core\AI;

use PDO;
use Exception;

class ContentAnalyzer {
    private $db;
    private $openaiApiKey;
    private $googleApiKey;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        $this->googleApiKey = $_ENV['GOOGLE_AI_API_KEY'] ?? null;
    }
    
    /**
     * Analyze text content for keywords, sentiment, and entities.
     * This is a placeholder for integration with actual NLP/ML services.
     */
    public function analyzeText(string $text): array {
        $analysis = [
            'keywords' => $this->extractKeywords($text),
            'sentiment' => $this->analyzeSentiment($text),
            'entities' => $this->extractEntities($text),
            'readability_score' => $this->calculateReadability($text),
            'summary_suggestion' => $this->suggestSummary($text)
        ];
        return $analysis;
    }

    /**
     * Analyze content for sentiment
     */
    public function analyzeSentiment($content) {
        try {
            // Use OpenAI for sentiment analysis
            if ($this->openaiApiKey) {
                return $this->analyzeSentimentWithOpenAI($content);
            }
            
            // Fallback to basic sentiment analysis
            return $this->basicSentimentAnalysis($content);
        } catch (Exception $e) {
            error_log("Sentiment analysis failed: " . $e->getMessage());
            return $this->basicSentimentAnalysis($content);
        }
    }
    
    /**
     * Extract keywords from content
     */
    public function extractKeywords($content, $maxKeywords = 10) {
        try {
            // Remove HTML tags and normalize text
            $text = strip_tags($content);
            $text = strtolower($text);
            
            // Remove common stop words
            $stopWords = $this->getStopWords();
            $words = preg_split('/\s+/', $text);
            $words = array_filter($words, function($word) use ($stopWords) {
                return strlen($word) > 3 && !in_array($word, $stopWords);
            });
            
            // Count word frequency
            $wordCount = array_count_values($words);
            arsort($wordCount);
            
            // Return top keywords
            return array_slice(array_keys($wordCount), 0, $maxKeywords);
        } catch (Exception $e) {
            error_log("Keyword extraction failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate content summary
     */
    public function generateSummary($content, $maxLength = 150) {
        try {
            if ($this->openaiApiKey) {
                return $this->generateSummaryWithOpenAI($content, $maxLength);
            }
            
            // Fallback to extractive summarization
            return $this->extractiveSummarization($content, $maxLength);
        } catch (Exception $e) {
            error_log("Summary generation failed: " . $e->getMessage());
            return $this->extractiveSummarization($content, $maxLength);
        }
    }
    
    /**
     * Analyze readability
     */
    public function analyzeReadability($content) {
        $text = strip_tags($content);
        $sentences = preg_split('/[.!?]+/', $text);
        $words = preg_split('/\s+/', $text);
        $syllables = $this->countSyllables($text);
        
        $avgWordsPerSentence = count($words) / count($sentences);
        $avgSyllablesPerWord = $syllables / count($words);
        
        // Flesch Reading Ease Score
        $fleschScore = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
        
        // Determine readability level
        if ($fleschScore >= 90) {
            $level = 'Very Easy';
        } elseif ($fleschScore >= 80) {
            $level = 'Easy';
        } elseif ($fleschScore >= 70) {
            $level = 'Fairly Easy';
        } elseif ($fleschScore >= 60) {
            $level = 'Standard';
        } elseif ($fleschScore >= 50) {
            $level = 'Fairly Difficult';
        } elseif ($fleschScore >= 30) {
            $level = 'Difficult';
        } else {
            $level = 'Very Difficult';
        }
        
        return [
            'flesch_score' => round($fleschScore, 2),
            'readability_level' => $level,
            'avg_words_per_sentence' => round($avgWordsPerSentence, 2),
            'avg_syllables_per_word' => round($avgSyllablesPerWord, 2),
            'total_words' => count($words),
            'total_sentences' => count($sentences)
        ];
    }
    
    /**
     * Detect topics in content
     */
    public function detectTopics($content) {
        try {
            $keywords = $this->extractKeywords($content, 20);
            $topics = [];
            
            // Define topic categories
            $topicCategories = [
                'technology' => ['software', 'programming', 'computer', 'code', 'development', 'api', 'database'],
                'business' => ['company', 'market', 'sales', 'revenue', 'profit', 'strategy', 'management'],
                'education' => ['learning', 'study', 'course', 'training', 'knowledge', 'research', 'academic'],
                'health' => ['medical', 'health', 'fitness', 'wellness', 'treatment', 'doctor', 'medicine'],
                'travel' => ['trip', 'vacation', 'hotel', 'flight', 'destination', 'travel', 'journey'],
                'food' => ['recipe', 'cooking', 'restaurant', 'meal', 'ingredient', 'chef', 'cuisine'],
                'entertainment' => ['movie', 'music', 'game', 'book', 'show', 'entertainment', 'fun'],
                'sports' => ['game', 'team', 'player', 'match', 'sport', 'competition', 'athlete']
            ];
            
            foreach ($topicCategories as $category => $categoryKeywords) {
                $matches = array_intersect($keywords, $categoryKeywords);
                if (count($matches) > 0) {
                    $topics[] = [
                        'category' => $category,
                        'confidence' => count($matches) / count($categoryKeywords),
                        'keywords' => array_values($matches)
                    ];
                }
            }
            
            // Sort by confidence
            usort($topics, function($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });
            
            return array_slice($topics, 0, 3);
        } catch (Exception $e) {
            error_log("Topic detection failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate content suggestions
     */
    public function generateContentSuggestions($content, $contentType = 'note') {
        try {
            $suggestions = [];
            
            // Analyze content
            $sentiment = $this->analyzeSentiment($content);
            $keywords = $this->extractKeywords($content);
            $topics = $this->detectTopics($content);
            $readability = $this->analyzeReadability($content);
            
            // Generate suggestions based on analysis
            if ($sentiment['score'] < -0.3) {
                $suggestions[] = [
                    'type' => 'tone',
                    'title' => 'Consider a more positive tone',
                    'description' => 'Your content has a negative sentiment. Consider adding more positive language.',
                    'priority' => 'medium'
                ];
            }
            
            if ($readability['flesch_score'] < 50) {
                $suggestions[] = [
                    'type' => 'readability',
                    'title' => 'Improve readability',
                    'description' => 'Your content is quite difficult to read. Consider using shorter sentences and simpler words.',
                    'priority' => 'high'
                ];
            }
            
            if (count($keywords) < 3) {
                $suggestions[] = [
                    'type' => 'keywords',
                    'title' => 'Add more specific keywords',
                    'description' => 'Your content could benefit from more specific and descriptive keywords.',
                    'priority' => 'low'
                ];
            }
            
            if (!empty($topics)) {
                $suggestions[] = [
                    'type' => 'topics',
                    'title' => 'Related topics to explore',
                    'description' => 'Consider exploring these related topics: ' . implode(', ', array_column($topics, 'category')),
                    'priority' => 'low'
                ];
            }
            
            return $suggestions;
        } catch (Exception $e) {
            error_log("Content suggestions failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save analysis results to database
     */
    public function saveAnalysis($contentId, $contentType, $analysis) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO content_analysis 
                (content_id, content_type, analysis_type, analysis_data, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                analysis_data = VALUES(analysis_data),
                created_at = NOW()
            ");
            
            return $stmt->execute([
                $contentId,
                $contentType,
                'comprehensive',
                json_encode($analysis)
            ]);
        } catch (Exception $e) {
            error_log("Save analysis failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Analyze content with OpenAI
     */
    private function analyzeSentimentWithOpenAI($content) {
        $prompt = "Analyze the sentiment of the following text and return a JSON response with 'sentiment' (positive/negative/neutral) and 'score' (-1 to 1):\n\n" . $content;
        
        $response = $this->callOpenAI($prompt);
        
        if ($response) {
            $data = json_decode($response, true);
            return [
                'sentiment' => $data['sentiment'] ?? 'neutral',
                'score' => $data['score'] ?? 0,
                'confidence' => 0.9
            ];
        }
        
        return $this->basicSentimentAnalysis($content);
    }
    
    /**
     * Generate summary with OpenAI
     */
    private function generateSummaryWithOpenAI($content, $maxLength) {
        $prompt = "Summarize the following text in no more than {$maxLength} characters:\n\n" . $content;
        
        $response = $this->callOpenAI($prompt);
        
        if ($response) {
            return trim($response);
        }
        
        return $this->extractiveSummarization($content, $maxLength);
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['choices'][0]['message']['content'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Basic sentiment analysis
     */
    private function basicSentimentAnalysis($content) {
        $positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'love', 'like', 'happy', 'pleased'];
        $negativeWords = ['bad', 'terrible', 'awful', 'hate', 'dislike', 'angry', 'sad', 'disappointed', 'frustrated', 'annoyed'];
        
        $text = strtolower(strip_tags($content));
        $words = preg_split('/\s+/', $text);
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $positiveCount++;
            } elseif (in_array($word, $negativeWords)) {
                $negativeCount++;
            }
        }
        
        $total = $positiveCount + $negativeCount;
        if ($total === 0) {
            return ['sentiment' => 'neutral', 'score' => 0, 'confidence' => 0.5];
        }
        
        $score = ($positiveCount - $negativeCount) / $total;
        
        if ($score > 0.1) {
            $sentiment = 'positive';
        } elseif ($score < -0.1) {
            $sentiment = 'negative';
        } else {
            $sentiment = 'neutral';
        }
        
        return [
            'sentiment' => $sentiment,
            'score' => $score,
            'confidence' => 0.6
        ];
    }
    
    /**
     * Extractive summarization
     */
    private function extractiveSummarization($content, $maxLength) {
        $sentences = preg_split('/[.!?]+/', strip_tags($content));
        $sentences = array_filter($sentences, function($sentence) {
            return strlen(trim($sentence)) > 10;
        });
        
        if (empty($sentences)) {
            return '';
        }
        
        // Simple extractive summarization - take first few sentences
        $summary = '';
        foreach ($sentences as $sentence) {
            if (strlen($summary . $sentence) > $maxLength) {
                break;
            }
            $summary .= trim($sentence) . '. ';
        }
        
        return trim($summary);
    }
    
    /**
     * Count syllables in text
     */
    private function countSyllables($text) {
        $words = preg_split('/\s+/', strtolower($text));
        $syllables = 0;
        
        foreach ($words as $word) {
            $syllables += $this->countSyllablesInWord($word);
        }
        
        return $syllables;
    }
    
    /**
     * Count syllables in a single word
     */
    private function countSyllablesInWord($word) {
        $word = preg_replace('/[^a-z]/', '', $word);
        $vowels = 'aeiouy';
        $syllables = 0;
        $previousWasVowel = false;
        
        for ($i = 0; $i < strlen($word); $i++) {
            $isVowel = strpos($vowels, $word[$i]) !== false;
            if ($isVowel && !$previousWasVowel) {
                $syllables++;
            }
            $previousWasVowel = $isVowel;
        }
        
        // Handle silent 'e'
        if (substr($word, -1) === 'e' && $syllables > 1) {
            $syllables--;
        }
        
        return max(1, $syllables);
    }
    
    /**
     * Get common stop words
     */
    private function getStopWords() {
        return [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
            'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those',
            'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them'
        ];
    }
}
