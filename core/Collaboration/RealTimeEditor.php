<?php
namespace Core\Collaboration;

use PDO;
use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class RealTimeEditor implements MessageComponentInterface {
    private $db;
    private $clients;
    private $editingSessions;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->clients = new \SplObjectStorage;
        $this->editingSessions = [];
    }
    
    /**
     * Handle new WebSocket connection
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    /**
     * Handle incoming WebSocket message
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                return;
            }
            
            switch ($data['type']) {
                case 'join_editing_session':
                    $this->handleJoinEditingSession($from, $data);
                    break;
                    
                case 'leave_editing_session':
                    $this->handleLeaveEditingSession($from, $data);
                    break;
                    
                case 'text_change':
                    $this->handleTextChange($from, $data);
                    break;
                    
                case 'cursor_position':
                    $this->handleCursorPosition($from, $data);
                    break;
                    
                case 'selection_change':
                    $this->handleSelectionChange($from, $data);
                    break;
                    
                case 'save_content':
                    $this->handleSaveContent($from, $data);
                    break;
                    
                case 'ping':
                    $this->handlePing($from);
                    break;
            }
            
        } catch (Exception $e) {
            error_log("WebSocket message error: " . $e->getMessage());
        }
    }
    
    /**
     * Handle WebSocket connection close
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove user from all editing sessions
        foreach ($this->editingSessions as $sessionId => $session) {
            if (isset($session['users'][$conn->resourceId])) {
                unset($this->editingSessions[$sessionId]['users'][$conn->resourceId]);
                
                // Notify other users
                $this->broadcastToSession($sessionId, [
                    'type' => 'user_left',
                    'user_id' => $conn->resourceId,
                    'session_id' => $sessionId
                ], $conn);
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    /**
     * Handle WebSocket error
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Handle join editing session
     */
    private function handleJoinEditingSession(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $resourceType = $data['resource_type'] ?? null;
        $resourceId = $data['resource_id'] ?? null;
        
        if (!$sessionId || !$userId || !$resourceType || !$resourceId) {
            return;
        }
        
        // Initialize session if it doesn't exist
        if (!isset($this->editingSessions[$sessionId])) {
            $this->editingSessions[$sessionId] = [
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'users' => [],
                'content' => $this->getResourceContent($resourceType, $resourceId),
                'last_modified' => time()
            ];
        }
        
        // Add user to session
        $this->editingSessions[$sessionId]['users'][$conn->resourceId] = [
            'user_id' => $userId,
            'username' => $this->getUsername($userId),
            'cursor_position' => 0,
            'selection_start' => 0,
            'selection_end' => 0,
            'joined_at' => time()
        ];
        
        // Send current content to new user
        $conn->send(json_encode([
            'type' => 'content_loaded',
            'content' => $this->editingSessions[$sessionId]['content'],
            'session_id' => $sessionId
        ]));
        
        // Notify other users
        $this->broadcastToSession($sessionId, [
            'type' => 'user_joined',
            'user_id' => $userId,
            'username' => $this->getUsername($userId),
            'session_id' => $sessionId
        ], $conn);
        
        // Send current users list
        $this->sendUsersList($sessionId);
    }
    
    /**
     * Handle leave editing session
     */
    private function handleLeaveEditingSession(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        
        if (!$sessionId || !isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        $userId = $this->editingSessions[$sessionId]['users'][$conn->resourceId]['user_id'] ?? null;
        
        if (isset($this->editingSessions[$sessionId]['users'][$conn->resourceId])) {
            unset($this->editingSessions[$sessionId]['users'][$conn->resourceId]);
            
            // Notify other users
            $this->broadcastToSession($sessionId, [
                'type' => 'user_left',
                'user_id' => $userId,
                'session_id' => $sessionId
            ], $conn);
            
            // Send updated users list
            $this->sendUsersList($sessionId);
        }
    }
    
    /**
     * Handle text change
     */
    private function handleTextChange(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        $change = $data['change'] ?? null;
        
        if (!$sessionId || !$change || !isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        // Apply change to content
        $this->applyTextChange($sessionId, $change);
        
        // Update last modified time
        $this->editingSessions[$sessionId]['last_modified'] = time();
        
        // Broadcast change to other users
        $this->broadcastToSession($sessionId, [
            'type' => 'text_change',
            'change' => $change,
            'session_id' => $sessionId,
            'timestamp' => time()
        ], $conn);
    }
    
    /**
     * Handle cursor position change
     */
    private function handleCursorPosition(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        $position = $data['position'] ?? null;
        
        if (!$sessionId || !isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        // Update user's cursor position
        if (isset($this->editingSessions[$sessionId]['users'][$conn->resourceId])) {
            $this->editingSessions[$sessionId]['users'][$conn->resourceId]['cursor_position'] = $position;
        }
        
        // Broadcast cursor position to other users
        $this->broadcastToSession($sessionId, [
            'type' => 'cursor_position',
            'user_id' => $conn->resourceId,
            'position' => $position,
            'session_id' => $sessionId
        ], $conn);
    }
    
    /**
     * Handle selection change
     */
    private function handleSelectionChange(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        $selection = $data['selection'] ?? null;
        
        if (!$sessionId || !$selection || !isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        // Update user's selection
        if (isset($this->editingSessions[$sessionId]['users'][$conn->resourceId])) {
            $this->editingSessions[$sessionId]['users'][$conn->resourceId]['selection_start'] = $selection['start'];
            $this->editingSessions[$sessionId]['users'][$conn->resourceId]['selection_end'] = $selection['end'];
        }
        
        // Broadcast selection to other users
        $this->broadcastToSession($sessionId, [
            'type' => 'selection_change',
            'user_id' => $conn->resourceId,
            'selection' => $selection,
            'session_id' => $sessionId
        ], $conn);
    }
    
    /**
     * Handle save content
     */
    private function handleSaveContent(ConnectionInterface $conn, array $data) {
        $sessionId = $data['session_id'] ?? null;
        $content = $data['content'] ?? null;
        
        if (!$sessionId || !$content || !isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        $session = $this->editingSessions[$sessionId];
        
        // Save content to database
        $this->saveResourceContent($session['resource_type'], $session['resource_id'], $content);
        
        // Update session content
        $this->editingSessions[$sessionId]['content'] = $content;
        $this->editingSessions[$sessionId]['last_modified'] = time();
        
        // Broadcast save confirmation
        $this->broadcastToSession($sessionId, [
            'type' => 'content_saved',
            'session_id' => $sessionId,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Handle ping
     */
    private function handlePing(ConnectionInterface $conn) {
        $conn->send(json_encode([
            'type' => 'pong',
            'timestamp' => time()
        ]));
    }
    
    /**
     * Broadcast message to all users in a session
     */
    private function broadcastToSession(string $sessionId, array $message, ConnectionInterface $exclude = null) {
        if (!isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        $messageJson = json_encode($message);
        
        foreach ($this->clients as $client) {
            if ($client !== $exclude && isset($this->editingSessions[$sessionId]['users'][$client->resourceId])) {
                $client->send($messageJson);
            }
        }
    }
    
    /**
     * Send users list to all users in a session
     */
    private function sendUsersList(string $sessionId) {
        if (!isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        $users = [];
        foreach ($this->editingSessions[$sessionId]['users'] as $userId => $userData) {
            $users[] = [
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'cursor_position' => $userData['cursor_position']
            ];
        }
        
        $this->broadcastToSession($sessionId, [
            'type' => 'users_list',
            'users' => $users,
            'session_id' => $sessionId
        ]);
    }
    
    /**
     * Apply text change to content
     */
    private function applyTextChange(string $sessionId, array $change) {
        if (!isset($this->editingSessions[$sessionId])) {
            return;
        }
        
        $content = &$this->editingSessions[$sessionId]['content'];
        
        switch ($change['type']) {
            case 'insert':
                $content = substr_replace($content, $change['text'], $change['position'], 0);
                break;
                
            case 'delete':
                $content = substr_replace($content, '', $change['position'], $change['length']);
                break;
                
            case 'replace':
                $content = substr_replace($content, $change['text'], $change['position'], $change['length']);
                break;
        }
    }
    
    /**
     * Get resource content from database
     */
    private function getResourceContent(string $resourceType, int $resourceId): string {
        try {
            $table = $resourceType === 'note' ? 'notes' : 'tasks';
            $column = $resourceType === 'note' ? 'content' : 'description';
            
            $stmt = $this->db->prepare("SELECT {$column} FROM {$table} WHERE id = ?");
            $stmt->execute([$resourceId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result[$column] ?? '';
            
        } catch (Exception $e) {
            error_log("Error getting resource content: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Save resource content to database
     */
    private function saveResourceContent(string $resourceType, int $resourceId, string $content): bool {
        try {
            $table = $resourceType === 'note' ? 'notes' : 'tasks';
            $column = $resourceType === 'note' ? 'content' : 'description';
            
            $stmt = $this->db->prepare("UPDATE {$table} SET {$column} = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$content, $resourceId]);
            
        } catch (Exception $e) {
            error_log("Error saving resource content: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get username by user ID
     */
    private function getUsername(int $userId): string {
        try {
            $stmt = $this->db->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['username'] ?? 'Unknown';
            
        } catch (Exception $e) {
            error_log("Error getting username: " . $e->getMessage());
            return 'Unknown';
        }
    }
    
    /**
     * Get active editing sessions
     */
    public function getActiveSessions(): array {
        $sessions = [];
        
        foreach ($this->editingSessions as $sessionId => $session) {
            $sessions[] = [
                'session_id' => $sessionId,
                'resource_type' => $session['resource_type'],
                'resource_id' => $session['resource_id'],
                'user_count' => count($session['users']),
                'last_modified' => $session['last_modified']
            ];
        }
        
        return $sessions;
    }
    
    /**
     * Clean up inactive sessions
     */
    public function cleanupInactiveSessions(int $timeout = 3600): int {
        $cleaned = 0;
        $currentTime = time();
        
        foreach ($this->editingSessions as $sessionId => $session) {
            if ($currentTime - $session['last_modified'] > $timeout) {
                unset($this->editingSessions[$sessionId]);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
