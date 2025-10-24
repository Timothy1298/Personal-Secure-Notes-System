<?php
namespace Plugins\WordCount\WordCount;

use PDO;

class WordCount {
    private $db;
    private $pluginData;
    
    public function __construct(PDO $db, $pluginData) {
        $this->db = $db;
        $this->pluginData = $pluginData;
    }
    
    /**
     * Get plugin hooks
     */
    public function getHooks() {
        return [
            'note_created' => 'onNoteCreated',
            'note_updated' => 'onNoteUpdated',
            'note_render' => 'onNoteRender'
        ];
    }
    
    /**
     * Handle note creation
     */
    public function onNoteCreated($data) {
        if (isset($data['content'])) {
            $wordCount = str_word_count($data['content']);
            $data['word_count'] = $wordCount;
            $data['read_time'] = $this->calculateReadTime($wordCount);
        }
        return $data;
    }
    
    /**
     * Handle note update
     */
    public function onNoteUpdated($data) {
        if (isset($data['content'])) {
            $wordCount = str_word_count($data['content']);
            $data['word_count'] = $wordCount;
            $data['read_time'] = $this->calculateReadTime($wordCount);
        }
        return $data;
    }
    
    /**
     * Handle note rendering
     */
    public function onNoteRender($data) {
        if (isset($data['content'])) {
            $wordCount = str_word_count($data['content']);
            $readTime = $this->calculateReadTime($wordCount);
            
            $data['word_count_display'] = "
                <div class='word-count-plugin'>
                    <small class='text-gray-500'>
                        <i class='fas fa-font'></i> {$wordCount} words 
                        <i class='fas fa-clock'></i> {$readTime} min read
                    </small>
                </div>
            ";
        }
        return $data;
    }
    
    /**
     * Calculate reading time
     */
    private function calculateReadTime($wordCount) {
        $wordsPerMinute = 200; // Average reading speed
        $minutes = ceil($wordCount / $wordsPerMinute);
        return max(1, $minutes);
    }
    
    /**
     * Get plugin information
     */
    public function getInfo() {
        return [
            'name' => 'Word Count',
            'version' => '1.0.0',
            'description' => 'Adds word count and reading time to notes',
            'author' => 'Personal Notes System',
            'hooks' => ['note_created', 'note_updated', 'note_render']
        ];
    }
}
