<?php
namespace Core;

use PDO;
use Exception;

class KeyboardShortcuts {
    private $db;
    private $shortcuts = [];
    private $defaultShortcuts = [
        'global' => [
            'ctrl+n' => ['action' => 'create_note', 'description' => 'Create new note'],
            'ctrl+t' => ['action' => 'create_task', 'description' => 'Create new task'],
            'ctrl+k' => ['action' => 'quick_search', 'description' => 'Open quick search'],
            'ctrl+/' => ['action' => 'show_shortcuts', 'description' => 'Show keyboard shortcuts'],
            'ctrl+,' => ['action' => 'open_settings', 'description' => 'Open settings'],
            'ctrl+h' => ['action' => 'go_home', 'description' => 'Go to dashboard'],
            'ctrl+s' => ['action' => 'save', 'description' => 'Save current item'],
            'ctrl+z' => ['action' => 'undo', 'description' => 'Undo last action'],
            'ctrl+y' => ['action' => 'redo', 'description' => 'Redo last action'],
            'ctrl+f' => ['action' => 'find', 'description' => 'Find in current page'],
            'escape' => ['action' => 'close_modal', 'description' => 'Close modal/dialog'],
            'ctrl+shift+d' => ['action' => 'toggle_dark_mode', 'description' => 'Toggle dark mode']
        ],
        'notes' => [
            'ctrl+n' => ['action' => 'create_note', 'description' => 'Create new note'],
            'ctrl+e' => ['action' => 'edit_note', 'description' => 'Edit selected note'],
            'ctrl+d' => ['action' => 'delete_note', 'description' => 'Delete selected note'],
            'ctrl+a' => ['action' => 'archive_note', 'description' => 'Archive selected note'],
            'ctrl+p' => ['action' => 'pin_note', 'description' => 'Pin/unpin note'],
            'ctrl+shift+c' => ['action' => 'copy_note', 'description' => 'Copy note content'],
            'ctrl+shift+v' => ['action' => 'paste_note', 'description' => 'Paste note content'],
            'ctrl+shift+f' => ['action' => 'format_note', 'description' => 'Format note text'],
            'ctrl+shift+b' => ['action' => 'bold_text', 'description' => 'Bold selected text'],
            'ctrl+shift+i' => ['action' => 'italic_text', 'description' => 'Italic selected text'],
            'ctrl+shift+u' => ['action' => 'underline_text', 'description' => 'Underline selected text']
        ],
        'tasks' => [
            'ctrl+t' => ['action' => 'create_task', 'description' => 'Create new task'],
            'ctrl+enter' => ['action' => 'complete_task', 'description' => 'Complete selected task'],
            'ctrl+shift+enter' => ['action' => 'uncomplete_task', 'description' => 'Uncomplete selected task'],
            'ctrl+shift+p' => ['action' => 'set_priority', 'description' => 'Set task priority'],
            'ctrl+shift+d' => ['action' => 'set_due_date', 'description' => 'Set due date'],
            'ctrl+shift+s' => ['action' => 'add_subtask', 'description' => 'Add subtask'],
            'ctrl+shift+t' => ['action' => 'add_tag', 'description' => 'Add tag to task'],
            'ctrl+shift+r' => ['action' => 'repeat_task', 'description' => 'Repeat task'],
            'ctrl+shift+a' => ['action' => 'archive_task', 'description' => 'Archive task']
        ],
        'navigation' => [
            'ctrl+1' => ['action' => 'go_dashboard', 'description' => 'Go to dashboard'],
            'ctrl+2' => ['action' => 'go_notes', 'description' => 'Go to notes'],
            'ctrl+3' => ['action' => 'go_tasks', 'description' => 'Go to tasks'],
            'ctrl+4' => ['action' => 'go_tags', 'description' => 'Go to tags'],
            'ctrl+5' => ['action' => 'go_archived', 'description' => 'Go to archived'],
            'ctrl+6' => ['action' => 'go_trash', 'description' => 'Go to trash'],
            'ctrl+7' => ['action' => 'go_settings', 'description' => 'Go to settings'],
            'ctrl+8' => ['action' => 'go_search', 'description' => 'Go to search'],
            'ctrl+9' => ['action' => 'go_analytics', 'description' => 'Go to analytics'],
            'ctrl+0' => ['action' => 'go_profile', 'description' => 'Go to profile']
        ]
    ];
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->initializeShortcuts();
    }
    
    /**
     * Initialize shortcuts
     */
    private function initializeShortcuts() {
        $this->shortcuts = $this->defaultShortcuts;
    }
    
    /**
     * Get shortcuts for context
     */
    public function getShortcuts($context = 'global') {
        return $this->shortcuts[$context] ?? [];
    }
    
    /**
     * Get all shortcuts
     */
    public function getAllShortcuts() {
        return $this->shortcuts;
    }
    
    /**
     * Get user custom shortcuts
     */
    public function getUserShortcuts($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT preference_value 
                FROM user_preferences 
                WHERE user_id = ? AND preference_key = 'keyboard_shortcuts'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $userShortcuts = json_decode($result['preference_value'], true);
                return $this->mergeShortcuts($this->shortcuts, $userShortcuts);
            }
            
            return $this->shortcuts;
        } catch (Exception $e) {
            error_log("Get user shortcuts failed: " . $e->getMessage());
            return $this->shortcuts;
        }
    }
    
    /**
     * Set user custom shortcuts
     */
    public function setUserShortcuts($userId, $shortcuts) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                VALUES (?, 'keyboard_shortcuts', ?, NOW())
                ON DUPLICATE KEY UPDATE 
                preference_value = VALUES(preference_value),
                updated_at = NOW()
            ");
            
            return $stmt->execute([$userId, json_encode($shortcuts)]);
        } catch (Exception $e) {
            error_log("Set user shortcuts failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add custom shortcut
     */
    public function addShortcut($userId, $context, $key, $action, $description) {
        $userShortcuts = $this->getUserShortcuts($userId);
        
        if (!isset($userShortcuts[$context])) {
            $userShortcuts[$context] = [];
        }
        
        $userShortcuts[$context][$key] = [
            'action' => $action,
            'description' => $description
        ];
        
        return $this->setUserShortcuts($userId, $userShortcuts);
    }
    
    /**
     * Remove shortcut
     */
    public function removeShortcut($userId, $context, $key) {
        $userShortcuts = $this->getUserShortcuts($userId);
        
        if (isset($userShortcuts[$context][$key])) {
            unset($userShortcuts[$context][$key]);
            return $this->setUserShortcuts($userId, $userShortcuts);
        }
        
        return false;
    }
    
    /**
     * Reset shortcuts to default
     */
    public function resetShortcuts($userId) {
        return $this->setUserShortcuts($userId, []);
    }
    
    /**
     * Merge default and user shortcuts
     */
    private function mergeShortcuts($default, $user) {
        if (empty($user)) {
            return $default;
        }
        
        $merged = $default;
        
        foreach ($user as $context => $shortcuts) {
            if (!isset($merged[$context])) {
                $merged[$context] = [];
            }
            
            foreach ($shortcuts as $key => $shortcut) {
                $merged[$context][$key] = $shortcut;
            }
        }
        
        return $merged;
    }
    
    /**
     * Generate JavaScript for shortcuts
     */
    public function generateJavaScript($userId = null) {
        $shortcuts = $userId ? $this->getUserShortcuts($userId) : $this->shortcuts;
        
        $js = "
        class KeyboardShortcuts {
            constructor() {
                this.shortcuts = " . json_encode($shortcuts) . ";
                this.context = 'global';
                this.init();
            }
            
            init() {
                document.addEventListener('keydown', (e) => this.handleKeydown(e));
                this.updateContext();
            }
            
            handleKeydown(e) {
                const key = this.getKeyString(e);
                const contextShortcuts = this.shortcuts[this.context] || {};
                const globalShortcuts = this.shortcuts['global'] || {};
                
                // Check context-specific shortcuts first
                if (contextShortcuts[key]) {
                    e.preventDefault();
                    this.executeAction(contextShortcuts[key].action, e);
                    return;
                }
                
                // Check global shortcuts
                if (globalShortcuts[key]) {
                    e.preventDefault();
                    this.executeAction(globalShortcuts[key].action, e);
                    return;
                }
            }
            
            getKeyString(e) {
                let key = '';
                
                if (e.ctrlKey) key += 'ctrl+';
                if (e.shiftKey) key += 'shift+';
                if (e.altKey) key += 'alt+';
                if (e.metaKey) key += 'meta+';
                
                key += e.key.toLowerCase();
                
                return key;
            }
            
            executeAction(action, e) {
                switch(action) {
                    case 'create_note':
                        this.createNote();
                        break;
                    case 'create_task':
                        this.createTask();
                        break;
                    case 'quick_search':
                        this.openQuickSearch();
                        break;
                    case 'show_shortcuts':
                        this.showShortcuts();
                        break;
                    case 'open_settings':
                        this.openSettings();
                        break;
                    case 'go_home':
                        this.goHome();
                        break;
                    case 'save':
                        this.save();
                        break;
                    case 'close_modal':
                        this.closeModal();
                        break;
                    case 'toggle_dark_mode':
                        this.toggleDarkMode();
                        break;
                    case 'edit_note':
                        this.editNote();
                        break;
                    case 'delete_note':
                        this.deleteNote();
                        break;
                    case 'complete_task':
                        this.completeTask();
                        break;
                    case 'go_dashboard':
                        this.navigate('/dashboard');
                        break;
                    case 'go_notes':
                        this.navigate('/notes');
                        break;
                    case 'go_tasks':
                        this.navigate('/tasks');
                        break;
                    case 'go_tags':
                        this.navigate('/tags');
                        break;
                    case 'go_settings':
                        this.navigate('/settings');
                        break;
                    default:
                        console.log('Shortcut action not implemented:', action);
                }
            }
            
            updateContext() {
                const path = window.location.pathname;
                if (path.includes('/notes')) {
                    this.context = 'notes';
                } else if (path.includes('/tasks')) {
                    this.context = 'tasks';
                } else {
                    this.context = 'global';
                }
            }
            
            // Action implementations
            createNote() {
                if (typeof window.createNote === 'function') {
                    window.createNote();
                } else {
                    window.location.href = '/notes/create';
                }
            }
            
            createTask() {
                if (typeof window.createTask === 'function') {
                    window.createTask();
                } else {
                    window.location.href = '/tasks/create';
                }
            }
            
            openQuickSearch() {
                if (typeof window.openQuickSearch === 'function') {
                    window.openQuickSearch();
                }
            }
            
            showShortcuts() {
                if (typeof window.showShortcuts === 'function') {
                    window.showShortcuts();
                }
            }
            
            openSettings() {
                window.location.href = '/settings';
            }
            
            goHome() {
                window.location.href = '/dashboard';
            }
            
            save() {
                if (typeof window.saveCurrent === 'function') {
                    window.saveCurrent();
                }
            }
            
            closeModal() {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                    }
                });
            }
            
            toggleDarkMode() {
                if (typeof window.toggleDarkMode === 'function') {
                    window.toggleDarkMode();
                }
            }
            
            editNote() {
                if (typeof window.editSelectedNote === 'function') {
                    window.editSelectedNote();
                }
            }
            
            deleteNote() {
                if (typeof window.deleteSelectedNote === 'function') {
                    window.deleteSelectedNote();
                }
            }
            
            completeTask() {
                if (typeof window.completeSelectedTask === 'function') {
                    window.completeSelectedTask();
                }
            }
            
            navigate(path) {
                window.location.href = path;
            }
        }
        
        // Initialize shortcuts when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.keyboardShortcuts = new KeyboardShortcuts();
        });
        ";
        
        return $js;
    }
    
    /**
     * Get shortcuts help HTML
     */
    public function getShortcutsHelp($userId = null) {
        $shortcuts = $userId ? $this->getUserShortcuts($userId) : $this->shortcuts;
        
        $html = '<div class="keyboard-shortcuts-help">';
        
        foreach ($shortcuts as $context => $contextShortcuts) {
            if (empty($contextShortcuts)) continue;
            
            $html .= '<div class="shortcut-context">';
            $html .= '<h3>' . ucfirst($context) . ' Shortcuts</h3>';
            $html .= '<div class="shortcut-list">';
            
            foreach ($contextShortcuts as $key => $shortcut) {
                $html .= '<div class="shortcut-item">';
                $html .= '<kbd>' . htmlspecialchars($key) . '</kbd>';
                $html .= '<span>' . htmlspecialchars($shortcut['description']) . '</span>';
                $html .= '</div>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
