<?php
namespace Core\AI;

use PDO;
use Exception;

class ContentGenerator {
    private $db;
    private $openaiApiKey;
    private $googleApiKey;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        $this->googleApiKey = $_ENV['GOOGLE_AI_API_KEY'] ?? null;
    }
    
    /**
     * Generate content based on prompt
     */
    public function generateContent($prompt, $type = 'note', $options = []) {
        try {
            if ($this->openaiApiKey) {
                return $this->generateWithOpenAI($prompt, $type, $options);
            }
            
            // Fallback to template-based generation
            return $this->generateWithTemplates($prompt, $type, $options);
        } catch (Exception $e) {
            error_log("Content generation failed: " . $e->getMessage());
            return $this->generateWithTemplates($prompt, $type, $options);
        }
    }
    
    /**
     * Generate note content
     */
    public function generateNote($topic, $style = 'informative', $length = 'medium') {
        $prompt = $this->buildNotePrompt($topic, $style, $length);
        return $this->generateContent($prompt, 'note');
    }
    
    /**
     * Generate task content
     */
    public function generateTask($description, $context = '') {
        $prompt = $this->buildTaskPrompt($description, $context);
        return $this->generateContent($prompt, 'task');
    }
    
    /**
     * Generate meeting notes
     */
    public function generateMeetingNotes($meetingTitle, $participants = [], $agenda = []) {
        $prompt = $this->buildMeetingPrompt($meetingTitle, $participants, $agenda);
        return $this->generateContent($prompt, 'meeting');
    }
    
    /**
     * Generate project plan
     */
    public function generateProjectPlan($projectName, $description, $timeline = '') {
        $prompt = $this->buildProjectPrompt($projectName, $description, $timeline);
        return $this->generateContent($prompt, 'project');
    }
    
    /**
     * Generate study notes
     */
    public function generateStudyNotes($subject, $topic, $level = 'intermediate') {
        $prompt = $this->buildStudyPrompt($subject, $topic, $level);
        return $this->generateContent($prompt, 'study');
    }
    
    /**
     * Generate creative writing
     */
    public function generateCreativeWriting($genre, $prompt, $style = '') {
        $fullPrompt = $this->buildCreativePrompt($genre, $prompt, $style);
        return $this->generateContent($fullPrompt, 'creative');
    }
    
    /**
     * Generate with OpenAI
     */
    private function generateWithOpenAI($prompt, $type, $options) {
        $model = $options['model'] ?? 'gpt-3.5-turbo';
        $maxTokens = $options['max_tokens'] ?? $this->getMaxTokensForType($type);
        $temperature = $options['temperature'] ?? $this->getTemperatureForType($type);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $this->getSystemPrompt($type)],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
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
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            return [
                'success' => true,
                'content' => trim($content),
                'model' => $model,
                'tokens_used' => $data['usage']['total_tokens'] ?? 0
            ];
        }
        
        throw new Exception('OpenAI API request failed');
    }
    
    /**
     * Generate with templates
     */
    private function generateWithTemplates($prompt, $type, $options) {
        $templates = $this->getTemplatesForType($type);
        $template = $templates[array_rand($templates)];
        
        $content = $this->fillTemplate($template, $prompt, $options);
        
        return [
            'success' => true,
            'content' => $content,
            'model' => 'template',
            'tokens_used' => 0
        ];
    }
    
    /**
     * Build note prompt
     */
    private function buildNotePrompt($topic, $style, $length) {
        $lengthInstructions = [
            'short' => 'Write a brief note (100-200 words)',
            'medium' => 'Write a comprehensive note (300-500 words)',
            'long' => 'Write a detailed note (500-1000 words)'
        ];
        
        $styleInstructions = [
            'informative' => 'in an informative and educational style',
            'casual' => 'in a casual and conversational style',
            'formal' => 'in a formal and professional style',
            'creative' => 'in a creative and engaging style'
        ];
        
        $lengthInstruction = $lengthInstructions[$length] ?? $lengthInstructions['medium'];
        $styleInstruction = $styleInstructions[$style] ?? $styleInstructions['informative'];
        
        return "{$lengthInstruction} about '{$topic}' {$styleInstruction}. Include key points, examples, and actionable insights.";
    }
    
    /**
     * Build task prompt
     */
    private function buildTaskPrompt($description, $context) {
        $contextText = $context ? "Context: {$context}\n\n" : '';
        return "{$contextText}Create a detailed task breakdown for: {$description}\n\nInclude:\n- Clear objectives\n- Specific steps\n- Required resources\n- Estimated time\n- Success criteria";
    }
    
    /**
     * Build meeting prompt
     */
    private function buildMeetingPrompt($title, $participants, $agenda) {
        $participantsText = !empty($participants) ? "Participants: " . implode(', ', $participants) . "\n" : '';
        $agendaText = !empty($agenda) ? "Agenda: " . implode(', ', $agenda) . "\n" : '';
        
        return "Generate comprehensive meeting notes for: {$title}\n\n{$participantsText}{$agendaText}\n\nInclude:\n- Meeting summary\n- Key discussion points\n- Decisions made\n- Action items\n- Next steps";
    }
    
    /**
     * Build project prompt
     */
    private function buildProjectPrompt($name, $description, $timeline) {
        $timelineText = $timeline ? "Timeline: {$timeline}\n" : '';
        return "Create a detailed project plan for: {$name}\n\nDescription: {$description}\n{$timelineText}\n\nInclude:\n- Project objectives\n- Key milestones\n- Task breakdown\n- Resource requirements\n- Risk assessment\n- Timeline";
    }
    
    /**
     * Build study prompt
     */
    private function buildStudyPrompt($subject, $topic, $level) {
        return "Create comprehensive study notes for {$subject}: {$topic}\n\nLevel: {$level}\n\nInclude:\n- Key concepts and definitions\n- Important examples\n- Practice questions\n- Summary points\n- Further reading suggestions";
    }
    
    /**
     * Build creative prompt
     */
    private function buildCreativePrompt($genre, $prompt, $style) {
        $styleText = $style ? " in a {$style} style" : '';
        return "Write a {$genre} piece{$styleText} based on: {$prompt}\n\nMake it engaging, creative, and well-structured.";
    }
    
    /**
     * Get system prompt for type
     */
    private function getSystemPrompt($type) {
        $prompts = [
            'note' => 'You are a helpful assistant that creates well-structured, informative notes. Focus on clarity, organization, and actionable insights.',
            'task' => 'You are a project management expert that creates detailed, actionable task breakdowns. Focus on clarity, specificity, and practical steps.',
            'meeting' => 'You are a professional meeting facilitator that creates comprehensive meeting notes. Focus on capturing key points, decisions, and action items.',
            'project' => 'You are a project management expert that creates detailed project plans. Focus on structure, feasibility, and comprehensive coverage.',
            'study' => 'You are an educational expert that creates effective study materials. Focus on clarity, comprehension, and learning objectives.',
            'creative' => 'You are a creative writing assistant that produces engaging, well-crafted content. Focus on creativity, style, and narrative flow.'
        ];
        
        return $prompts[$type] ?? $prompts['note'];
    }
    
    /**
     * Get max tokens for type
     */
    private function getMaxTokensForType($type) {
        $tokens = [
            'note' => 800,
            'task' => 600,
            'meeting' => 1000,
            'project' => 1200,
            'study' => 1000,
            'creative' => 1000
        ];
        
        return $tokens[$type] ?? 800;
    }
    
    /**
     * Get temperature for type
     */
    private function getTemperatureForType($type) {
        $temperatures = [
            'note' => 0.7,
            'task' => 0.3,
            'meeting' => 0.5,
            'project' => 0.4,
            'study' => 0.6,
            'creative' => 0.9
        ];
        
        return $temperatures[$type] ?? 0.7;
    }
    
    /**
     * Get templates for type
     */
    private function getTemplatesForType($type) {
        $templates = [
            'note' => [
                "# {TOPIC}\n\n## Overview\n{CONTENT}\n\n## Key Points\n- Point 1\n- Point 2\n- Point 3\n\n## Summary\n{SUMMARY}",
                "# {TOPIC}\n\n## Introduction\n{CONTENT}\n\n## Main Content\n{MAIN_CONTENT}\n\n## Conclusion\n{CONCLUSION}",
                "# {TOPIC}\n\n{CONTENT}\n\n## Important Notes\n- Note 1\n- Note 2\n\n## Related Topics\n- Topic 1\n- Topic 2"
            ],
            'task' => [
                "# {TASK_TITLE}\n\n## Objective\n{OBJECTIVE}\n\n## Steps\n1. Step 1\n2. Step 2\n3. Step 3\n\n## Resources Needed\n- Resource 1\n- Resource 2\n\n## Timeline\n- Start: {START_DATE}\n- End: {END_DATE}",
                "## Task: {TASK_TITLE}\n\n**Description:** {DESCRIPTION}\n\n**Priority:** {PRIORITY}\n\n**Steps:**\n1. {STEP_1}\n2. {STEP_2}\n3. {STEP_3}\n\n**Success Criteria:**\n- {CRITERIA_1}\n- {CRITERIA_2}"
            ],
            'meeting' => [
                "# Meeting Notes: {TITLE}\n\n**Date:** {DATE}\n**Participants:** {PARTICIPANTS}\n\n## Agenda\n{AGENDA}\n\n## Discussion Points\n{DISCUSSION}\n\n## Decisions Made\n{DECISIONS}\n\n## Action Items\n{ACTION_ITEMS}",
                "# {TITLE} - Meeting Summary\n\n## Key Topics Discussed\n{TOPICS}\n\n## Outcomes\n{OUTCOMES}\n\n## Next Steps\n{NEXT_STEPS}\n\n## Follow-up Required\n{FOLLOW_UP}"
            ]
        ];
        
        return $templates[$type] ?? $templates['note'];
    }
    
    /**
     * Fill template with content
     */
    private function fillTemplate($template, $prompt, $options) {
        $placeholders = [
            '{TOPIC}' => $this->extractTopic($prompt),
            '{CONTENT}' => $this->generateBasicContent($prompt),
            '{SUMMARY}' => $this->generateSummary($prompt),
            '{TASK_TITLE}' => $this->extractTaskTitle($prompt),
            '{OBJECTIVE}' => $this->extractObjective($prompt),
            '{DESCRIPTION}' => $this->extractDescription($prompt),
            '{TITLE}' => $this->extractTitle($prompt),
            '{DATE}' => date('Y-m-d'),
            '{PARTICIPANTS}' => 'TBD',
            '{AGENDA}' => 'To be discussed',
            '{DISCUSSION}' => 'Key points discussed',
            '{DECISIONS}' => 'Decisions made',
            '{ACTION_ITEMS}' => 'Action items identified',
            '{TOPICS}' => 'Main topics covered',
            '{OUTCOMES}' => 'Meeting outcomes',
            '{NEXT_STEPS}' => 'Next steps identified',
            '{FOLLOW_UP}' => 'Follow-up required'
        ];
        
        $content = $template;
        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Extract topic from prompt
     */
    private function extractTopic($prompt) {
        // Simple topic extraction - take first few words
        $words = explode(' ', $prompt);
        return implode(' ', array_slice($words, 0, 5));
    }
    
    /**
     * Generate basic content
     */
    private function generateBasicContent($prompt) {
        return "This is a generated note about: {$prompt}\n\nKey information and insights will be added here based on the topic and context provided.";
    }
    
    /**
     * Generate summary
     */
    private function generateSummary($prompt) {
        return "Summary of the main points discussed regarding: {$prompt}";
    }
    
    /**
     * Extract task title
     */
    private function extractTaskTitle($prompt) {
        return $this->extractTopic($prompt);
    }
    
    /**
     * Extract objective
     */
    private function extractObjective($prompt) {
        return "Complete the task: {$prompt}";
    }
    
    /**
     * Extract description
     */
    private function extractDescription($prompt) {
        return $prompt;
    }
    
    /**
     * Extract title
     */
    private function extractTitle($prompt) {
        return $this->extractTopic($prompt);
    }
    
    /**
     * Save generated content
     */
    public function saveGeneratedContent($userId, $type, $prompt, $content, $metadata = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO generated_content 
                (user_id, content_type, prompt, generated_content, metadata, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $userId,
                $type,
                $prompt,
                $content,
                json_encode($metadata)
            ]);
        } catch (Exception $e) {
            error_log("Save generated content failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get generation history
     */
    public function getGenerationHistory($userId, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, content_type, prompt, generated_content, metadata, created_at
                FROM generated_content 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get generation history failed: " . $e->getMessage());
            return [];
        }
    }
}
