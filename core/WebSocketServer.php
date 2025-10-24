<?php
namespace Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use PDO;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $db;
    protected $sessions;

    public function __construct(PDO $db) {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;
        $this->sessions = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'join_session':
                $this->handleJoinSession($from, $data);
                break;
            case 'leave_session':
                $this->handleLeaveSession($from, $data);
                break;
            case 'edit_content':
                $this->handleEditContent($from, $data);
                break;
            case 'cursor_position':
                $this->handleCursorPosition($from, $data);
                break;
            case 'typing':
                $this->handleTyping($from, $data);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove from all sessions
        foreach ($this->sessions as $sessionId => $session) {
            if (isset($session['clients'][$conn->resourceId])) {
                unset($this->sessions[$sessionId]['clients'][$conn->resourceId]);
                
                // Notify other clients
                $this->broadcastToSession($sessionId, [
                    'type' => 'user_left',
                    'user_id' => $session['clients'][$conn->resourceId]['user_id'],
                    'timestamp' => time()
                ], $conn);
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function handleJoinSession(ConnectionInterface $conn, $data) {
        $sessionId = $data['session_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $userName = $data['user_name'] ?? 'Anonymous';
        
        if (!$sessionId || !$userId) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Session ID and User ID required'
            ]));
            return;
        }

        // Verify session exists and user has access
        if (!$this->verifySessionAccess($sessionId, $userId)) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Access denied to session'
            ]));
            return;
        }

        // Initialize session if not exists
        if (!isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId] = [
                'clients' => [],
                'content' => '',
                'last_modified' => time()
            ];
        }

        // Add client to session
        $this->sessions[$sessionId]['clients'][$conn->resourceId] = [
            'user_id' => $userId,
            'user_name' => $userName,
            'cursor_position' => 0,
            'is_typing' => false,
            'joined_at' => time()
        ];

        // Send current content to new client
        $conn->send(json_encode([
            'type' => 'session_joined',
            'session_id' => $sessionId,
            'content' => $this->sessions[$sessionId]['content'],
            'participants' => array_values($this->sessions[$sessionId]['clients'])
        ]));

        // Notify other clients
        $this->broadcastToSession($sessionId, [
            'type' => 'user_joined',
            'user_id' => $userId,
            'user_name' => $userName,
            'timestamp' => time()
        ], $conn);
    }

    private function handleLeaveSession(ConnectionInterface $conn, $data) {
        $sessionId = $data['session_id'] ?? null;
        
        if ($sessionId && isset($this->sessions[$sessionId]['clients'][$conn->resourceId])) {
            $userId = $this->sessions[$sessionId]['clients'][$conn->resourceId]['user_id'];
            unset($this->sessions[$sessionId]['clients'][$conn->resourceId]);
            
            // Notify other clients
            $this->broadcastToSession($sessionId, [
                'type' => 'user_left',
                'user_id' => $userId,
                'timestamp' => time()
            ], $conn);
        }
    }

    private function handleEditContent(ConnectionInterface $conn, $data) {
        $sessionId = $data['session_id'] ?? null;
        $content = $data['content'] ?? '';
        $userId = $data['user_id'] ?? null;
        
        if (!$sessionId || !isset($this->sessions[$sessionId])) {
            return;
        }

        // Update session content
        $this->sessions[$sessionId]['content'] = $content;
        $this->sessions[$sessionId]['last_modified'] = time();

        // Broadcast to all clients in session except sender
        $this->broadcastToSession($sessionId, [
            'type' => 'content_updated',
            'content' => $content,
            'user_id' => $userId,
            'timestamp' => time()
        ], $conn);

        // Save to database
        $this->saveContentToDatabase($sessionId, $content, $userId);
    }

    private function handleCursorPosition(ConnectionInterface $conn, $data) {
        $sessionId = $data['session_id'] ?? null;
        $position = $data['position'] ?? 0;
        $userId = $data['user_id'] ?? null;
        
        if (!$sessionId || !isset($this->sessions[$sessionId]['clients'][$conn->resourceId])) {
            return;
        }

        // Update cursor position
        $this->sessions[$sessionId]['clients'][$conn->resourceId]['cursor_position'] = $position;

        // Broadcast to other clients
        $this->broadcastToSession($sessionId, [
            'type' => 'cursor_moved',
            'user_id' => $userId,
            'position' => $position,
            'timestamp' => time()
        ], $conn);
    }

    private function handleTyping(ConnectionInterface $conn, $data) {
        $sessionId = $data['session_id'] ?? null;
        $isTyping = $data['is_typing'] ?? false;
        $userId = $data['user_id'] ?? null;
        
        if (!$sessionId || !isset($this->sessions[$sessionId]['clients'][$conn->resourceId])) {
            return;
        }

        // Update typing status
        $this->sessions[$sessionId]['clients'][$conn->resourceId]['is_typing'] = $isTyping;

        // Broadcast to other clients
        $this->broadcastToSession($sessionId, [
            'type' => 'typing_status',
            'user_id' => $userId,
            'is_typing' => $isTyping,
            'timestamp' => time()
        ], $conn);
    }

    private function broadcastToSession($sessionId, $message, $exclude = null) {
        if (!isset($this->sessions[$sessionId])) {
            return;
        }

        foreach ($this->sessions[$sessionId]['clients'] as $resourceId => $client) {
            foreach ($this->clients as $client) {
                if ($client->resourceId === $resourceId && $client !== $exclude) {
                    $client->send(json_encode($message));
                }
            }
        }
    }

    private function verifySessionAccess($sessionId, $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT cs.*, u.username 
                FROM collaboration_sessions cs
                JOIN users u ON cs.owner_id = u.id
                WHERE cs.session_id = ? AND cs.is_active = 1
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                return false;
            }

            // Check if user is owner or participant
            $participants = json_decode($session['participants'], true) ?? [];
            return $session['owner_id'] == $userId || in_array($userId, $participants);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function saveContentToDatabase($sessionId, $content, $userId) {
        try {
            // Get session details
            $stmt = $this->db->prepare("
                SELECT resource_type, resource_id 
                FROM collaboration_sessions 
                WHERE session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                return;
            }

            // Update the resource content
            if ($session['resource_type'] === 'note') {
                $stmt = $this->db->prepare("
                    UPDATE notes 
                    SET content = ?, updated_at = NOW() 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$content, $session['resource_id'], $userId]);
            } elseif ($session['resource_type'] === 'task') {
                $stmt = $this->db->prepare("
                    UPDATE tasks 
                    SET description = ?, updated_at = NOW() 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$content, $session['resource_id'], $userId]);
            }

            // Log collaboration event
            $stmt = $this->db->prepare("
                INSERT INTO collaboration_events 
                (session_id, user_id, event_type, event_data) 
                VALUES (?, ?, 'content_edit', ?)
            ");
            $stmt->execute([
                $sessionId, 
                $userId, 
                json_encode(['content_length' => strlen($content)])
            ]);
        } catch (\Exception $e) {
            error_log("Error saving collaboration content: " . $e->getMessage());
        }
    }
}
