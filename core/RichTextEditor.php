<?php
namespace Core;

use PDO;
use Exception;

class RichTextEditor {
    private $db;
    private $editorConfig = [
        'toolbar' => [
            'bold', 'italic', 'underline', 'strikethrough',
            '|', 'heading1', 'heading2', 'heading3',
            '|', 'bulletList', 'orderedList', 'blockquote',
            '|', 'code', 'codeBlock', 'link', 'image',
            '|', 'table', 'horizontalRule',
            '|', 'undo', 'redo', 'clearFormat'
        ],
        'extensions' => [
            'markdown', 'codeHighlight', 'math', 'table', 'image'
        ],
        'features' => [
            'autoSave' => true,
            'collaboration' => true,
            'versionHistory' => true,
            'export' => ['html', 'markdown', 'pdf', 'docx'],
            'import' => ['html', 'markdown', 'docx', 'txt']
        ]
    ];
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get editor configuration
     */
    public function getEditorConfig($userId = null) {
        $config = $this->editorConfig;
        
        if ($userId) {
            $userConfig = $this->getUserEditorConfig($userId);
            if ($userConfig) {
                $config = array_merge($config, $userConfig);
            }
        }
        
        return $config;
    }
    
    /**
     * Get user editor preferences
     */
    public function getUserEditorConfig($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT preference_value 
                FROM user_preferences 
                WHERE user_id = ? AND preference_key = 'editor_config'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? json_decode($result['preference_value'], true) : null;
        } catch (Exception $e) {
            error_log("Get user editor config failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set user editor preferences
     */
    public function setUserEditorConfig($userId, $config) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                VALUES (?, 'editor_config', ?, NOW())
                ON DUPLICATE KEY UPDATE 
                preference_value = VALUES(preference_value),
                updated_at = NOW()
            ");
            
            return $stmt->execute([$userId, json_encode($config)]);
        } catch (Exception $e) {
            error_log("Set user editor config failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert HTML to Markdown
     */
    public function htmlToMarkdown($html) {
        // Basic HTML to Markdown conversion
        $markdown = $html;
        
        // Headers
        $markdown = preg_replace('/<h1[^>]*>(.*?)<\/h1>/i', '# $1', $markdown);
        $markdown = preg_replace('/<h2[^>]*>(.*?)<\/h2>/i', '## $1', $markdown);
        $markdown = preg_replace('/<h3[^>]*>(.*?)<\/h3>/i', '### $1', $markdown);
        
        // Bold and italic
        $markdown = preg_replace('/<strong[^>]*>(.*?)<\/strong>/i', '**$1**', $markdown);
        $markdown = preg_replace('/<b[^>]*>(.*?)<\/b>/i', '**$1**', $markdown);
        $markdown = preg_replace('/<em[^>]*>(.*?)<\/em>/i', '*$1*', $markdown);
        $markdown = preg_replace('/<i[^>]*>(.*?)<\/i>/i', '*$1*', $markdown);
        
        // Links
        $markdown = preg_replace('/<a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/i', '[$2]($1)', $markdown);
        
        // Images
        $markdown = preg_replace('/<img[^>]*src=["\']([^"\']*)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*>/i', '![$2]($1)', $markdown);
        
        // Lists
        $markdown = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', '$1', $markdown);
        $markdown = preg_replace('/<ol[^>]*>(.*?)<\/ol>/is', '$1', $markdown);
        $markdown = preg_replace('/<li[^>]*>(.*?)<\/li>/i', '- $1', $markdown);
        
        // Code blocks
        $markdown = preg_replace('/<pre[^>]*><code[^>]*>(.*?)<\/code><\/pre>/is', '```\n$1\n```', $markdown);
        $markdown = preg_replace('/<code[^>]*>(.*?)<\/code>/i', '`$1`', $markdown);
        
        // Blockquotes
        $markdown = preg_replace('/<blockquote[^>]*>(.*?)<\/blockquote>/is', '> $1', $markdown);
        
        // Horizontal rules
        $markdown = preg_replace('/<hr[^>]*>/i', '---', $markdown);
        
        // Remove remaining HTML tags
        $markdown = strip_tags($markdown);
        
        // Clean up whitespace
        $markdown = preg_replace('/\n\s*\n\s*\n/', "\n\n", $markdown);
        $markdown = trim($markdown);
        
        return $markdown;
    }
    
    /**
     * Convert Markdown to HTML
     */
    public function markdownToHtml($markdown) {
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Images
        $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $html);
        
        // Code blocks
        $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
        $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);
        
        // Lists
        $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Blockquotes
        $html = preg_replace('/^> (.*$)/m', '<blockquote>$1</blockquote>', $html);
        
        // Horizontal rules
        $html = preg_replace('/^---$/m', '<hr>', $html);
        
        // Line breaks
        $html = nl2br($html);
        
        return $html;
    }
    
    /**
     * Generate editor HTML
     */
    public function generateEditorHTML($content = '', $noteId = null, $userId = null) {
        $config = $this->getEditorConfig($userId);
        
        $html = '
        <div class="rich-text-editor" data-note-id="' . $noteId . '">
            <div class="editor-toolbar">
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="bold" title="Bold (Ctrl+B)">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="italic" title="Italic (Ctrl+I)">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="underline" title="Underline (Ctrl+U)">
                        <i class="fas fa-underline"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="strikethrough" title="Strikethrough">
                        <i class="fas fa-strikethrough"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <select class="toolbar-select" data-action="heading">
                        <option value="">Normal</option>
                        <option value="h1">Heading 1</option>
                        <option value="h2">Heading 2</option>
                        <option value="h3">Heading 3</option>
                    </select>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="bulletList" title="Bullet List">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="orderedList" title="Numbered List">
                        <i class="fas fa-list-ol"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="blockquote" title="Quote">
                        <i class="fas fa-quote-left"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="code" title="Inline Code">
                        <i class="fas fa-code"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="codeBlock" title="Code Block">
                        <i class="fas fa-terminal"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="link" title="Insert Link">
                        <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="image" title="Insert Image">
                        <i class="fas fa-image"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="table" title="Insert Table">
                        <i class="fas fa-table"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="horizontalRule" title="Horizontal Line">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="undo" title="Undo (Ctrl+Z)">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="redo" title="Redo (Ctrl+Y)">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="clearFormat" title="Clear Formatting">
                        <i class="fas fa-remove-format"></i>
                    </button>
                </div>
                
                <div class="toolbar-spacer"></div>
                
                <div class="toolbar-group">
                    <button type="button" class="toolbar-btn" data-action="preview" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-action="fullscreen" title="Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            
            <div class="editor-content">
                <div class="editor-textarea" contenteditable="true" data-placeholder="Start writing your note...">
                    ' . htmlspecialchars($content) . '
                </div>
            </div>
            
            <div class="editor-status">
                <span class="word-count">0 words</span>
                <span class="char-count">0 characters</span>
                <span class="auto-save-status">Auto-saved</span>
            </div>
        </div>
        
        <style>
        .rich-text-editor {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .editor-toolbar {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
            gap: 4px;
        }
        
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        
        .toolbar-separator {
            width: 1px;
            height: 24px;
            background: #e2e8f0;
            margin: 0 8px;
        }
        
        .toolbar-spacer {
            flex: 1;
        }
        
        .toolbar-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .toolbar-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .toolbar-btn.active {
            background: #3b82f6;
            color: white;
        }
        
        .toolbar-select {
            padding: 4px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background: white;
            font-size: 14px;
        }
        
        .editor-content {
            min-height: 300px;
        }
        
        .editor-textarea {
            padding: 16px;
            min-height: 300px;
            outline: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #1e293b;
        }
        
        .editor-textarea:empty:before {
            content: attr(data-placeholder);
            color: #94a3b8;
            pointer-events: none;
        }
        
        .editor-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 16px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
        
        .auto-save-status {
            color: #10b981;
        }
        
        .auto-save-status.saving {
            color: #f59e0b;
        }
        
        .auto-save-status.error {
            color: #ef4444;
        }
        </style>
        ';
        
        return $html;
    }
    
    /**
     * Generate editor JavaScript
     */
    public function generateEditorJS() {
        return '
        <script>
        class RichTextEditor {
            constructor(container) {
                this.container = container;
                this.textarea = container.querySelector(".editor-textarea");
                this.toolbar = container.querySelector(".editor-toolbar");
                this.wordCount = container.querySelector(".word-count");
                this.charCount = container.querySelector(".char-count");
                this.autoSaveStatus = container.querySelector(".auto-save-status");
                
                this.init();
            }
            
            init() {
                this.setupToolbar();
                this.setupAutoSave();
                this.setupWordCount();
                this.setupKeyboardShortcuts();
            }
            
            setupToolbar() {
                this.toolbar.addEventListener("click", (e) => {
                    const btn = e.target.closest(".toolbar-btn");
                    if (btn) {
                        const action = btn.dataset.action;
                        this.executeAction(action);
                    }
                });
                
                this.toolbar.addEventListener("change", (e) => {
                    if (e.target.classList.contains("toolbar-select")) {
                        const action = e.target.dataset.action;
                        const value = e.target.value;
                        this.executeAction(action, value);
                    }
                });
            }
            
            executeAction(action, value = null) {
                document.execCommand(action, false, value);
                this.textarea.focus();
                this.updateWordCount();
            }
            
            setupAutoSave() {
                let saveTimeout;
                
                this.textarea.addEventListener("input", () => {
                    clearTimeout(saveTimeout);
                    this.autoSaveStatus.textContent = "Saving...";
                    this.autoSaveStatus.className = "auto-save-status saving";
                    
                    saveTimeout = setTimeout(() => {
                        this.saveContent();
                    }, 2000);
                });
            }
            
            saveContent() {
                const content = this.textarea.innerHTML;
                const noteId = this.container.dataset.noteId;
                
                if (!noteId) return;
                
                fetch("/notes/auto-save", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({
                        note_id: noteId,
                        content: content,
                        csrf_token: document.querySelector("input[name=\'csrf_token\']").value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.autoSaveStatus.textContent = "Auto-saved";
                        this.autoSaveStatus.className = "auto-save-status";
                    } else {
                        this.autoSaveStatus.textContent = "Save failed";
                        this.autoSaveStatus.className = "auto-save-status error";
                    }
                })
                .catch(error => {
                    this.autoSaveStatus.textContent = "Save failed";
                    this.autoSaveStatus.className = "auto-save-status error";
                });
            }
            
            setupWordCount() {
                this.textarea.addEventListener("input", () => {
                    this.updateWordCount();
                });
                
                this.updateWordCount();
            }
            
            updateWordCount() {
                const text = this.textarea.textContent || this.textarea.innerText || "";
                const words = text.trim() ? text.trim().split(/\s+/).length : 0;
                const chars = text.length;
                
                this.wordCount.textContent = `${words} words`;
                this.charCount.textContent = `${chars} characters`;
            }
            
            setupKeyboardShortcuts() {
                this.textarea.addEventListener("keydown", (e) => {
                    if (e.ctrlKey || e.metaKey) {
                        switch(e.key) {
                            case "b":
                                e.preventDefault();
                                this.executeAction("bold");
                                break;
                            case "i":
                                e.preventDefault();
                                this.executeAction("italic");
                                break;
                            case "u":
                                e.preventDefault();
                                this.executeAction("underline");
                                break;
                            case "z":
                                if (e.shiftKey) {
                                    e.preventDefault();
                                    this.executeAction("redo");
                                } else {
                                    e.preventDefault();
                                    this.executeAction("undo");
                                }
                                break;
                            case "y":
                                e.preventDefault();
                                this.executeAction("redo");
                                break;
                        }
                    }
                });
            }
            
            getContent() {
                return this.textarea.innerHTML;
            }
            
            setContent(content) {
                this.textarea.innerHTML = content;
                this.updateWordCount();
            }
            
            focus() {
                this.textarea.focus();
            }
        }
        
        // Initialize all rich text editors
        document.addEventListener("DOMContentLoaded", () => {
            const editors = document.querySelectorAll(".rich-text-editor");
            editors.forEach(editor => {
                new RichTextEditor(editor);
            });
        });
        </script>
        ';
    }
}
