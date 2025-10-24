<?php
namespace Core;

use PDO;
use Exception;

class ThemeManager {
    private $db;
    private $currentTheme = 'light';
    private $availableThemes = [
        'light' => [
            'name' => 'Light Mode',
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
            'background' => '#ffffff',
            'surface' => '#f8fafc',
            'text' => '#1e293b',
            'textSecondary' => '#64748b',
            'border' => '#e2e8f0',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'error' => '#ef4444',
            'info' => '#06b6d4'
        ],
        'dark' => [
            'name' => 'Dark Mode',
            'primary' => '#60a5fa',
            'secondary' => '#94a3b8',
            'background' => '#0f172a',
            'surface' => '#1e293b',
            'text' => '#f1f5f9',
            'textSecondary' => '#94a3b8',
            'border' => '#334155',
            'success' => '#34d399',
            'warning' => '#fbbf24',
            'error' => '#f87171',
            'info' => '#22d3ee'
        ],
        'blue' => [
            'name' => 'Blue Theme',
            'primary' => '#1d4ed8',
            'secondary' => '#3b82f6',
            'background' => '#eff6ff',
            'surface' => '#dbeafe',
            'text' => '#1e3a8a',
            'textSecondary' => '#3b82f6',
            'border' => '#93c5fd',
            'success' => '#059669',
            'warning' => '#d97706',
            'error' => '#dc2626',
            'info' => '#0891b2'
        ],
        'green' => [
            'name' => 'Green Theme',
            'primary' => '#059669',
            'secondary' => '#10b981',
            'background' => '#f0fdf4',
            'surface' => '#dcfce7',
            'text' => '#14532d',
            'textSecondary' => '#16a34a',
            'border' => '#86efac',
            'success' => '#16a34a',
            'warning' => '#ca8a04',
            'error' => '#dc2626',
            'info' => '#0891b2'
        ],
        'purple' => [
            'name' => 'Purple Theme',
            'primary' => '#7c3aed',
            'secondary' => '#a855f7',
            'background' => '#faf5ff',
            'surface' => '#f3e8ff',
            'text' => '#581c87',
            'textSecondary' => '#9333ea',
            'border' => '#c4b5fd',
            'success' => '#059669',
            'warning' => '#d97706',
            'error' => '#dc2626',
            'info' => '#0891b2'
        ]
    ];
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get current theme
     */
    public function getCurrentTheme() {
        return $this->currentTheme;
    }
    
    /**
     * Set theme for user
     */
    public function setUserTheme($userId, $theme) {
        if (!isset($this->availableThemes[$theme])) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                VALUES (?, 'theme', ?, NOW())
                ON DUPLICATE KEY UPDATE 
                preference_value = VALUES(preference_value),
                updated_at = NOW()
            ");
            
            return $stmt->execute([$userId, $theme]);
        } catch (Exception $e) {
            error_log("Theme setting failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user theme
     */
    public function getUserTheme($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT preference_value 
                FROM user_preferences 
                WHERE user_id = ? AND preference_key = 'theme'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['preference_value'] : 'light';
        } catch (Exception $e) {
            error_log("Theme retrieval failed: " . $e->getMessage());
            return 'light';
        }
    }
    
    /**
     * Get all available themes
     */
    public function getAvailableThemes() {
        return $this->availableThemes;
    }
    
    /**
     * Get theme colors
     */
    public function getThemeColors($theme = null) {
        if ($theme === null) {
            $theme = $this->currentTheme;
        }
        
        return $this->availableThemes[$theme] ?? $this->availableThemes['light'];
    }
    
    /**
     * Generate CSS variables for theme
     */
    public function generateCSSVariables($theme = null) {
        $colors = $this->getThemeColors($theme);
        $css = ":root {\n";
        
        foreach ($colors as $key => $value) {
            $css .= "  --color-{$key}: {$value};\n";
        }
        
        $css .= "}\n";
        
        // Add dark mode specific styles
        if ($theme === 'dark') {
            $css .= "
            .dark-mode {
                background-color: var(--color-background);
                color: var(--color-text);
            }
            
            .dark-mode .bg-white {
                background-color: var(--color-surface) !important;
            }
            
            .dark-mode .text-gray-900 {
                color: var(--color-text) !important;
            }
            
            .dark-mode .text-gray-600 {
                color: var(--color-textSecondary) !important;
            }
            
            .dark-mode .border-gray-200 {
                border-color: var(--color-border) !important;
            }
            ";
        }
        
        return $css;
    }
    
    /**
     * Get theme-specific classes
     */
    public function getThemeClasses($theme = null) {
        if ($theme === null) {
            $theme = $this->currentTheme;
        }
        
        $classes = [
            'light' => '',
            'dark' => 'dark-mode',
            'blue' => 'theme-blue',
            'green' => 'theme-green',
            'purple' => 'theme-purple'
        ];
        
        return $classes[$theme] ?? '';
    }
    
    /**
     * Initialize theme for user
     */
    public function initializeUserTheme($userId) {
        $theme = $this->getUserTheme($userId);
        $this->currentTheme = $theme;
        return $theme;
    }
    
    /**
     * Create custom theme
     */
    public function createCustomTheme($userId, $themeName, $colors) {
        try {
            $customTheme = [
                'name' => $themeName,
                'primary' => $colors['primary'] ?? '#3b82f6',
                'secondary' => $colors['secondary'] ?? '#64748b',
                'background' => $colors['background'] ?? '#ffffff',
                'surface' => $colors['surface'] ?? '#f8fafc',
                'text' => $colors['text'] ?? '#1e293b',
                'textSecondary' => $colors['textSecondary'] ?? '#64748b',
                'border' => $colors['border'] ?? '#e2e8f0',
                'success' => $colors['success'] ?? '#10b981',
                'warning' => $colors['warning'] ?? '#f59e0b',
                'error' => $colors['error'] ?? '#ef4444',
                'info' => $colors['info'] ?? '#06b6d4'
            ];
            
            $themeKey = 'custom_' . $userId . '_' . time();
            $this->availableThemes[$themeKey] = $customTheme;
            
            return $themeKey;
        } catch (Exception $e) {
            error_log("Custom theme creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get system theme preference
     */
    public function getSystemThemePreference() {
        if (isset($_COOKIE['theme_preference'])) {
            return $_COOKIE['theme_preference'];
        }
        
        // Check for system preference
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accept = $_SERVER['HTTP_ACCEPT'];
            if (strpos($accept, 'dark') !== false) {
                return 'dark';
            }
        }
        
        return 'light';
    }
    
    /**
     * Set theme preference cookie
     */
    public function setThemePreferenceCookie($theme) {
        setcookie('theme_preference', $theme, time() + (365 * 24 * 60 * 60), '/', '', false, true);
    }
    
    /**
     * Toggle between light and dark mode
     */
    public function toggleTheme($userId) {
        $currentTheme = $this->getUserTheme($userId);
        $newTheme = ($currentTheme === 'light') ? 'dark' : 'light';
        
        $this->setUserTheme($userId, $newTheme);
        $this->setThemePreferenceCookie($newTheme);
        
        return $newTheme;
    }
    
    /**
     * Get theme statistics
     */
    public function getThemeStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    preference_value as theme,
                    COUNT(*) as user_count
                FROM user_preferences 
                WHERE preference_key = 'theme'
                GROUP BY preference_value
                ORDER BY user_count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Theme stats failed: " . $e->getMessage());
            return [];
        }
    }
}
