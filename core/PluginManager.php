<?php
namespace Core;

use PDO;
use Exception;

class PluginManager {
    private $db;
    private $loadedPlugins = [];
    private $pluginHooks = [];
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->loadActivePlugins();
    }
    
    /**
     * Load all active plugins
     */
    private function loadActivePlugins() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM plugins 
                WHERE is_active = 1 
                ORDER BY name
            ");
            $stmt->execute();
            $plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($plugins as $plugin) {
                $this->loadPlugin($plugin);
            }
        } catch (Exception $e) {
            error_log("Error loading plugins: " . $e->getMessage());
        }
    }
    
    /**
     * Load a specific plugin
     */
    private function loadPlugin($pluginData) {
        try {
            $pluginName = $pluginData['name'];
            $pluginPath = __DIR__ . "/../plugins/{$pluginName}/{$pluginName}.php";
            
            if (file_exists($pluginPath)) {
                require_once $pluginPath;
                
                $pluginClass = "\\Plugins\\{$pluginName}\\{$pluginName}";
                if (class_exists($pluginClass)) {
                    $plugin = new $pluginClass($this->db, $pluginData);
                    $this->loadedPlugins[$pluginName] = $plugin;
                    
                    // Register plugin hooks
                    $this->registerPluginHooks($plugin);
                }
            }
        } catch (Exception $e) {
            error_log("Error loading plugin {$pluginData['name']}: " . $e->getMessage());
        }
    }
    
    /**
     * Register plugin hooks
     */
    private function registerPluginHooks($plugin) {
        if (method_exists($plugin, 'getHooks')) {
            $hooks = $plugin->getHooks();
            foreach ($hooks as $hook => $callback) {
                if (!isset($this->pluginHooks[$hook])) {
                    $this->pluginHooks[$hook] = [];
                }
                $this->pluginHooks[$hook][] = [
                    'plugin' => $plugin,
                    'callback' => $callback
                ];
            }
        }
    }
    
    /**
     * Execute plugin hooks
     */
    public function executeHook($hookName, $data = []) {
        if (!isset($this->pluginHooks[$hookName])) {
            return $data;
        }
        
        foreach ($this->pluginHooks[$hookName] as $hook) {
            try {
                $data = call_user_func([$hook['plugin'], $hook['callback']], $data);
            } catch (Exception $e) {
                error_log("Error executing hook {$hookName}: " . $e->getMessage());
            }
        }
        
        return $data;
    }
    
    /**
     * Install a new plugin
     */
    public function installPlugin($pluginName, $version, $description = '', $author = '', $pluginType = 'extension') {
        try {
            // Check if plugin already exists
            $stmt = $this->db->prepare("
                SELECT id FROM plugins 
                WHERE name = ? AND version = ?
            ");
            $stmt->execute([$pluginName, $version]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Plugin already installed'];
            }
            
            // Insert plugin record
            $stmt = $this->db->prepare("
                INSERT INTO plugins 
                (name, version, description, author, plugin_type, is_active) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$pluginName, $version, $description, $author, $pluginType]);
            
            $pluginId = $this->db->lastInsertId();
            
            return [
                'success' => true, 
                'message' => 'Plugin installed successfully',
                'plugin_id' => $pluginId
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error installing plugin: ' . $e->getMessage()];
        }
    }
    
    /**
     * Activate a plugin
     */
    public function activatePlugin($pluginId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE plugins 
                SET is_active = 1 
                WHERE id = ?
            ");
            $stmt->execute([$pluginId]);
            
            // Reload plugins
            $this->loadActivePlugins();
            
            return ['success' => true, 'message' => 'Plugin activated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error activating plugin: ' . $e->getMessage()];
        }
    }
    
    /**
     * Deactivate a plugin
     */
    public function deactivatePlugin($pluginId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE plugins 
                SET is_active = 0 
                WHERE id = ?
            ");
            $stmt->execute([$pluginId]);
            
            // Reload plugins
            $this->loadActivePlugins();
            
            return ['success' => true, 'message' => 'Plugin deactivated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deactivating plugin: ' . $e->getMessage()];
        }
    }
    
    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin($pluginId) {
        try {
            // Get plugin data
            $stmt = $this->db->prepare("SELECT * FROM plugins WHERE id = ?");
            $stmt->execute([$pluginId]);
            $plugin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plugin) {
                return ['success' => false, 'message' => 'Plugin not found'];
            }
            
            // Remove user preferences
            $stmt = $this->db->prepare("DELETE FROM user_plugin_preferences WHERE plugin_id = ?");
            $stmt->execute([$pluginId]);
            
            // Remove plugin
            $stmt = $this->db->prepare("DELETE FROM plugins WHERE id = ?");
            $stmt->execute([$pluginId]);
            
            // Remove from loaded plugins
            if (isset($this->loadedPlugins[$plugin['name']])) {
                unset($this->loadedPlugins[$plugin['name']]);
            }
            
            return ['success' => true, 'message' => 'Plugin uninstalled successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error uninstalling plugin: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all plugins
     */
    public function getAllPlugins() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       COUNT(upp.user_id) as user_count
                FROM plugins p
                LEFT JOIN user_plugin_preferences upp ON p.id = upp.plugin_id
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get user's enabled plugins
     */
    public function getUserPlugins($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, upp.is_enabled, upp.user_config
                FROM plugins p
                LEFT JOIN user_plugin_preferences upp ON p.id = upp.plugin_id AND upp.user_id = ?
                WHERE p.is_active = 1
                ORDER BY p.name
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update user plugin preferences
     */
    public function updateUserPluginPreferences($userId, $pluginId, $isEnabled, $userConfig = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_plugin_preferences 
                (user_id, plugin_id, is_enabled, user_config) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                is_enabled = VALUES(is_enabled),
                user_config = VALUES(user_config),
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$userId, $pluginId, $isEnabled, json_encode($userConfig)]);
            
            return ['success' => true, 'message' => 'Plugin preferences updated'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating preferences: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get plugin configuration
     */
    public function getPluginConfig($pluginId) {
        try {
            $stmt = $this->db->prepare("SELECT config FROM plugins WHERE id = ?");
            $stmt->execute([$pluginId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? json_decode($result['config'], true) : [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update plugin configuration
     */
    public function updatePluginConfig($pluginId, $config) {
        try {
            $stmt = $this->db->prepare("
                UPDATE plugins 
                SET config = ? 
                WHERE id = ?
            ");
            $stmt->execute([json_encode($config), $pluginId]);
            
            return ['success' => true, 'message' => 'Plugin configuration updated'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating configuration: ' . $e->getMessage()];
        }
    }
    
    /**
     * Create a new plugin
     */
    public function createPlugin($pluginData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO plugins 
                (name, version, description, author, plugin_type, config, dependencies) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $pluginData['name'],
                $pluginData['version'],
                $pluginData['description'],
                $pluginData['author'],
                $pluginData['plugin_type'],
                json_encode($pluginData['config'] ?? []),
                json_encode($pluginData['dependencies'] ?? [])
            ]);
            
            return [
                'success' => true, 
                'message' => 'Plugin created successfully',
                'plugin_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating plugin: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get plugin hooks documentation
     */
    public function getAvailableHooks() {
        return [
            'note_created' => 'Called when a new note is created',
            'note_updated' => 'Called when a note is updated',
            'note_deleted' => 'Called when a note is deleted',
            'task_created' => 'Called when a new task is created',
            'task_updated' => 'Called when a task is updated',
            'task_completed' => 'Called when a task is completed',
            'user_login' => 'Called when a user logs in',
            'user_logout' => 'Called when a user logs out',
            'dashboard_render' => 'Called when dashboard is rendered',
            'note_render' => 'Called when note view is rendered',
            'task_render' => 'Called when task view is rendered',
            'api_request' => 'Called before API request processing',
            'api_response' => 'Called after API response generation'
        ];
    }
}
