<?php
namespace Core;

use PDO;
use Exception;

class DynamicContentService {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get weather data for a location
     */
    public function getWeatherData($location = 'New York, NY') {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM weather_data 
                WHERE location = ? 
                ORDER BY updated_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$location]);
            $weather = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$weather) {
                // Return default weather if not found
                return [
                    'location' => $location,
                    'temperature' => 22.0,
                    'description' => 'Partly Cloudy',
                    'humidity' => 65,
                    'wind_speed' => 12.0
                ];
            }
            
            return $weather;
        } catch (Exception $e) {
            // Fallback to default weather
            return [
                'location' => $location,
                'temperature' => 22.0,
                'description' => 'Partly Cloudy',
                'humidity' => 65,
                'wind_speed' => 12.0
            ];
        }
    }
    
    /**
     * Get a random motivational quote
     */
    public function getMotivationalQuote($category = 'general') {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM motivational_quotes 
                WHERE category = ? AND is_active = 1 
                ORDER BY RAND() 
                LIMIT 1
            ");
            $stmt->execute([$category]);
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quote) {
                // Fallback to any active quote
                $stmt = $this->db->prepare("
                    SELECT * FROM motivational_quotes 
                    WHERE is_active = 1 
                    ORDER BY RAND() 
                    LIMIT 1
                ");
                $stmt->execute();
                $quote = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$quote) {
                // Ultimate fallback
                return [
                    'text' => 'The way to get started is to quit talking and begin doing.',
                    'author' => 'Walt Disney',
                    'category' => 'motivation'
                ];
            }
            
            return $quote;
        } catch (Exception $e) {
            // Fallback quote
            return [
                'text' => 'The way to get started is to quit talking and begin doing.',
                'author' => 'Walt Disney',
                'category' => 'motivation'
            ];
        }
    }
    
    /**
     * Get user content preferences
     */
    public function getUserContentPreferences($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_content_preferences 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$preferences) {
                // Create default preferences
                $stmt = $this->db->prepare("
                    INSERT INTO user_content_preferences 
                    (user_id, weather_location, quote_category, show_weather, show_quotes) 
                    VALUES (?, 'New York, NY', 'general', 1, 1)
                ");
                $stmt->execute([$userId]);
                
                return [
                    'user_id' => $userId,
                    'weather_location' => 'New York, NY',
                    'quote_category' => 'general',
                    'show_weather' => 1,
                    'show_quotes' => 1
                ];
            }
            
            return $preferences;
        } catch (Exception $e) {
            return [
                'user_id' => $userId,
                'weather_location' => 'New York, NY',
                'quote_category' => 'general',
                'show_weather' => 1,
                'show_quotes' => 1
            ];
        }
    }
    
    /**
     * Update user content preferences
     */
    public function updateUserContentPreferences($userId, $preferences) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_content_preferences 
                (user_id, weather_location, quote_category, show_weather, show_quotes) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                weather_location = VALUES(weather_location),
                quote_category = VALUES(quote_category),
                show_weather = VALUES(show_weather),
                show_quotes = VALUES(show_quotes),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                $userId,
                $preferences['weather_location'] ?? 'New York, NY',
                $preferences['quote_category'] ?? 'general',
                $preferences['show_weather'] ?? 1,
                $preferences['show_quotes'] ?? 1
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get dashboard content for user
     */
    public function getDashboardContent($userId) {
        $preferences = $this->getUserContentPreferences($userId);
        
        $content = [];
        
        if ($preferences['show_weather']) {
            $content['weather'] = $this->getWeatherData($preferences['weather_location']);
        }
        
        if ($preferences['show_quotes']) {
            $content['quote'] = $this->getMotivationalQuote($preferences['quote_category']);
        }
        
        return $content;
    }
    
    /**
     * Update weather data (for external API integration)
     */
    public function updateWeatherData($location, $weatherData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO weather_data 
                (location, temperature, description, humidity, wind_speed) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                temperature = VALUES(temperature),
                description = VALUES(description),
                humidity = VALUES(humidity),
                wind_speed = VALUES(wind_speed),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                $location,
                $weatherData['temperature'],
                $weatherData['description'],
                $weatherData['humidity'] ?? null,
                $weatherData['wind_speed'] ?? null
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Add new motivational quote
     */
    public function addMotivationalQuote($text, $author, $category = 'general') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO motivational_quotes (text, author, category) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$text, $author, $category]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all quote categories
     */
    public function getQuoteCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT category 
                FROM motivational_quotes 
                WHERE is_active = 1 
                ORDER BY category
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return ['general', 'motivation', 'inspiration', 'career', 'life'];
        }
    }
}
