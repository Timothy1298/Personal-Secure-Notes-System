<?php
namespace Core\API;

use PDO;
use Exception;

class GraphQLServer {
    private $db;
    private $schema;
    private $resolvers;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->initializeSchema();
        $this->initializeResolvers();
    }
    
    /**
     * Initialize GraphQL schema
     */
    private function initializeSchema() {
        $this->schema = '
            type User {
                id: ID!
                username: String!
                email: String!
                created_at: String!
                updated_at: String!
                notes: [Note!]!
                tasks: [Task!]!
            }
            
            type Note {
                id: ID!
                title: String!
                content: String!
                user_id: ID!
                user: User!
                tags: [Tag!]!
                created_at: String!
                updated_at: String!
                is_pinned: Boolean!
                color: String
            }
            
            type Task {
                id: ID!
                title: String!
                description: String
                user_id: ID!
                user: User!
                status: String!
                priority: String!
                due_date: String
                created_at: String!
                updated_at: String!
            }
            
            type Tag {
                id: ID!
                name: String!
                color: String
                notes: [Note!]!
            }
            
            type Query {
                user(id: ID): User
                users: [User!]!
                note(id: ID!): Note
                notes(user_id: ID, limit: Int, offset: Int): [Note!]!
                task(id: ID!): Task
                tasks(user_id: ID, status: String, limit: Int, offset: Int): [Task!]!
                tag(id: ID!): Tag
                tags: [Tag!]!
                search(query: String!, type: String): [SearchResult!]!
            }
            
            type Mutation {
                createNote(input: NoteInput!): Note!
                updateNote(id: ID!, input: NoteInput!): Note!
                deleteNote(id: ID!): Boolean!
                createTask(input: TaskInput!): Task!
                updateTask(id: ID!, input: TaskInput!): Task!
                deleteTask(id: ID!): Boolean!
                createTag(input: TagInput!): Tag!
                updateTag(id: ID!, input: TagInput!): Tag!
                deleteTag(id: ID!): Boolean!
            }
            
            input NoteInput {
                title: String!
                content: String!
                tags: [String!]
                color: String
                is_pinned: Boolean
            }
            
            input TaskInput {
                title: String!
                description: String
                status: String
                priority: String
                due_date: String
            }
            
            input TagInput {
                name: String!
                color: String
            }
            
            type SearchResult {
                type: String!
                id: ID!
                title: String!
                content: String
                created_at: String!
            }
            
            schema {
                query: Query
                mutation: Mutation
            }
        ';
    }
    
    /**
     * Initialize resolvers
     */
    private function initializeResolvers() {
        $this->resolvers = [
            'Query' => [
                'user' => [$this, 'resolveUser'],
                'users' => [$this, 'resolveUsers'],
                'note' => [$this, 'resolveNote'],
                'notes' => [$this, 'resolveNotes'],
                'task' => [$this, 'resolveTask'],
                'tasks' => [$this, 'resolveTasks'],
                'tag' => [$this, 'resolveTag'],
                'tags' => [$this, 'resolveTags'],
                'search' => [$this, 'resolveSearch']
            ],
            'Mutation' => [
                'createNote' => [$this, 'resolveCreateNote'],
                'updateNote' => [$this, 'resolveUpdateNote'],
                'deleteNote' => [$this, 'resolveDeleteNote'],
                'createTask' => [$this, 'resolveCreateTask'],
                'updateTask' => [$this, 'resolveUpdateTask'],
                'deleteTask' => [$this, 'resolveDeleteTask'],
                'createTag' => [$this, 'resolveCreateTag'],
                'updateTag' => [$this, 'resolveUpdateTag'],
                'deleteTag' => [$this, 'resolveDeleteTag']
            ],
            'User' => [
                'notes' => [$this, 'resolveUserNotes'],
                'tasks' => [$this, 'resolveUserTasks']
            ],
            'Note' => [
                'user' => [$this, 'resolveNoteUser'],
                'tags' => [$this, 'resolveNoteTags']
            ],
            'Task' => [
                'user' => [$this, 'resolveTaskUser']
            ],
            'Tag' => [
                'notes' => [$this, 'resolveTagNotes']
            ]
        ];
    }
    
    /**
     * Execute GraphQL query
     */
    public function execute($query, $variables = [], $operationName = null) {
        try {
            // Parse query
            $parsedQuery = $this->parseQuery($query);
            
            // Execute query
            $result = $this->executeQuery($parsedQuery, $variables, $operationName);
            
            return [
                'data' => $result,
                'errors' => []
            ];
        } catch (Exception $e) {
            return [
                'data' => null,
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                        'locations' => [],
                        'path' => []
                    ]
                ]
            ];
        }
    }
    
    /**
     * Parse GraphQL query (simplified parser)
     */
    private function parseQuery($query) {
        // This is a simplified parser. In production, use a proper GraphQL parser library
        $query = trim($query);
        
        if (strpos($query, 'query') === 0) {
            return $this->parseOperation($query, 'query');
        } elseif (strpos($query, 'mutation') === 0) {
            return $this->parseOperation($query, 'mutation');
        }
        
        throw new Exception('Invalid GraphQL query');
    }
    
    /**
     * Parse operation
     */
    private function parseOperation($query, $type) {
        // Simplified operation parsing
        $operation = [
            'type' => $type,
            'name' => null,
            'selections' => []
        ];
        
        // Extract operation name and selections
        if (preg_match('/' . $type . '\s+(\w+)?\s*\{([^}]+)\}/', $query, $matches)) {
            $operation['name'] = $matches[1] ?? null;
            $operation['selections'] = $this->parseSelections($matches[2]);
        }
        
        return $operation;
    }
    
    /**
     * Parse selections
     */
    private function parseSelections($selections) {
        $parsed = [];
        $lines = explode("\n", $selections);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line === '{' || $line === '}') {
                continue;
            }
            
            if (strpos($line, '(') !== false) {
                // Field with arguments
                if (preg_match('/(\w+)\s*\(([^)]+)\)\s*\{([^}]+)\}/', $line, $matches)) {
                    $parsed[] = [
                        'type' => 'field',
                        'name' => $matches[1],
                        'arguments' => $this->parseArguments($matches[2]),
                        'selections' => $this->parseSelections($matches[3])
                    ];
                } elseif (preg_match('/(\w+)\s*\(([^)]+)\)/', $line, $matches)) {
                    $parsed[] = [
                        'type' => 'field',
                        'name' => $matches[1],
                        'arguments' => $this->parseArguments($matches[2]),
                        'selections' => []
                    ];
                }
            } else {
                // Simple field
                $parsed[] = [
                    'type' => 'field',
                    'name' => $line,
                    'arguments' => [],
                    'selections' => []
                ];
            }
        }
        
        return $parsed;
    }
    
    /**
     * Parse arguments
     */
    private function parseArguments($args) {
        $arguments = [];
        $pairs = explode(',', $args);
        
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (preg_match('/(\w+):\s*(.+)/', $pair, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);
                
                // Parse value
                if ($value === 'true' || $value === 'false') {
                    $arguments[$key] = $value === 'true';
                } elseif (is_numeric($value)) {
                    $arguments[$key] = (int)$value;
                } elseif (strpos($value, '"') === 0) {
                    $arguments[$key] = trim($value, '"');
                } else {
                    $arguments[$key] = $value;
                }
            }
        }
        
        return $arguments;
    }
    
    /**
     * Execute query
     */
    private function executeQuery($parsedQuery, $variables, $operationName) {
        $type = $parsedQuery['type'];
        $selections = $parsedQuery['selections'];
        
        $result = [];
        
        foreach ($selections as $selection) {
            if ($selection['type'] === 'field') {
                $fieldName = $selection['name'];
                $arguments = $selection['arguments'];
                $subSelections = $selection['selections'];
                
                if (isset($this->resolvers[$type][$fieldName])) {
                    $resolver = $this->resolvers[$type][$fieldName];
                    $fieldResult = call_user_func($resolver, $arguments, $subSelections);
                    
                    if (!empty($subSelections) && is_array($fieldResult)) {
                        $fieldResult = $this->resolveSubFields($fieldResult, $subSelections);
                    }
                    
                    $result[$fieldName] = $fieldResult;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Resolve sub-fields
     */
    private function resolveSubFields($data, $selections) {
        if (is_array($data) && isset($data[0])) {
            // Array of objects
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->resolveSubFields($item, $selections);
            }
            return $result;
        } elseif (is_array($data)) {
            // Single object
            $result = $data;
            foreach ($selections as $selection) {
                if ($selection['type'] === 'field') {
                    $fieldName = $selection['name'];
                    $subSelections = $selection['selections'];
                    
                    if (isset($this->resolvers['Note'][$fieldName])) {
                        $resolver = $this->resolvers['Note'][$fieldName];
                        $fieldResult = call_user_func($resolver, $data, $subSelections);
                        $result[$fieldName] = $fieldResult;
                    } elseif (isset($this->resolvers['Task'][$fieldName])) {
                        $resolver = $this->resolvers['Task'][$fieldName];
                        $fieldResult = call_user_func($resolver, $data, $subSelections);
                        $result[$fieldName] = $fieldResult;
                    } elseif (isset($this->resolvers['User'][$fieldName])) {
                        $resolver = $this->resolvers['User'][$fieldName];
                        $fieldResult = call_user_func($resolver, $data, $subSelections);
                        $result[$fieldName] = $fieldResult;
                    } elseif (isset($this->resolvers['Tag'][$fieldName])) {
                        $resolver = $this->resolvers['Tag'][$fieldName];
                        $fieldResult = call_user_func($resolver, $data, $subSelections);
                        $result[$fieldName] = $fieldResult;
                    }
                }
            }
            return $result;
        }
        
        return $data;
    }
    
    // Query Resolvers
    public function resolveUser($args, $selections) {
        $id = $args['id'] ?? null;
        
        if ($id) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
    
    public function resolveUsers($args, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveNote($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resolveNotes($args, $selections) {
        $userId = $args['user_id'] ?? null;
        $limit = $args['limit'] ?? 50;
        $offset = $args['offset'] ?? 0;
        
        $sql = "SELECT * FROM notes";
        $params = [];
        
        if ($userId) {
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveTask($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resolveTasks($args, $selections) {
        $userId = $args['user_id'] ?? null;
        $status = $args['status'] ?? null;
        $limit = $args['limit'] ?? 50;
        $offset = $args['offset'] ?? 0;
        
        $sql = "SELECT * FROM tasks";
        $params = [];
        $conditions = [];
        
        if ($userId) {
            $conditions[] = "user_id = ?";
            $params[] = $userId;
        }
        
        if ($status) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveTag($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resolveTags($args, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM tags ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveSearch($args, $selections) {
        $query = $args['query'];
        $type = $args['type'] ?? null;
        
        $results = [];
        
        if (!$type || $type === 'notes') {
            $stmt = $this->db->prepare("
                SELECT 'note' as type, id, title, content, created_at 
                FROM notes 
                WHERE title LIKE ? OR content LIKE ?
                ORDER BY created_at DESC
            ");
            $searchTerm = "%{$query}%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        if (!$type || $type === 'tasks') {
            $stmt = $this->db->prepare("
                SELECT 'task' as type, id, title, description as content, created_at 
                FROM tasks 
                WHERE title LIKE ? OR description LIKE ?
                ORDER BY created_at DESC
            ");
            $searchTerm = "%{$query}%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        return $results;
    }
    
    // Mutation Resolvers
    public function resolveCreateNote($args, $selections) {
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            INSERT INTO notes (title, content, user_id, is_pinned, color, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $input['title'],
            $input['content'],
            $_SESSION['user_id'] ?? 1, // In production, get from authentication
            $input['is_pinned'] ?? false,
            $input['color'] ?? null
        ]);
        
        $noteId = $this->db->lastInsertId();
        
        // Handle tags
        if (!empty($input['tags'])) {
            foreach ($input['tags'] as $tagName) {
                $this->addTagToNote($noteId, $tagName);
            }
        }
        
        return $this->resolveNote(['id' => $noteId], []);
    }
    
    public function resolveUpdateNote($args, $selections) {
        $id = $args['id'];
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            UPDATE notes 
            SET title = ?, content = ?, is_pinned = ?, color = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['title'],
            $input['content'],
            $input['is_pinned'] ?? false,
            $input['color'] ?? null,
            $id
        ]);
        
        return $this->resolveNote(['id' => $id], []);
    }
    
    public function resolveDeleteNote($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function resolveCreateTask($args, $selections) {
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            INSERT INTO tasks (title, description, user_id, status, priority, due_date, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $input['title'],
            $input['description'] ?? null,
            $_SESSION['user_id'] ?? 1, // In production, get from authentication
            $input['status'] ?? 'pending',
            $input['priority'] ?? 'medium',
            $input['due_date'] ?? null
        ]);
        
        $taskId = $this->db->lastInsertId();
        
        return $this->resolveTask(['id' => $taskId], []);
    }
    
    public function resolveUpdateTask($args, $selections) {
        $id = $args['id'];
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['title'],
            $input['description'] ?? null,
            $input['status'] ?? 'pending',
            $input['priority'] ?? 'medium',
            $input['due_date'] ?? null,
            $id
        ]);
        
        return $this->resolveTask(['id' => $id], []);
    }
    
    public function resolveDeleteTask($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function resolveCreateTag($args, $selections) {
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            INSERT INTO tags (name, color, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $input['name'],
            $input['color'] ?? null
        ]);
        
        $tagId = $this->db->lastInsertId();
        
        return $this->resolveTag(['id' => $tagId], []);
    }
    
    public function resolveUpdateTag($args, $selections) {
        $id = $args['id'];
        $input = $args['input'];
        
        $stmt = $this->db->prepare("
            UPDATE tags 
            SET name = ?, color = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['name'],
            $input['color'] ?? null,
            $id
        ]);
        
        return $this->resolveTag(['id' => $id], []);
    }
    
    public function resolveDeleteTag($args, $selections) {
        $id = $args['id'];
        
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Field Resolvers
    public function resolveUserNotes($user, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveUserTasks($user, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveNoteUser($note, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$note['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resolveNoteTags($note, $selections) {
        $stmt = $this->db->prepare("
            SELECT t.* FROM tags t
            JOIN note_tags nt ON t.id = nt.tag_id
            WHERE nt.note_id = ?
        ");
        $stmt->execute([$note['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function resolveTaskUser($task, $selections) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$task['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resolveTagNotes($tag, $selections) {
        $stmt = $this->db->prepare("
            SELECT n.* FROM notes n
            JOIN note_tags nt ON n.id = nt.note_id
            WHERE nt.tag_id = ?
        ");
        $stmt->execute([$tag['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add tag to note
     */
    private function addTagToNote($noteId, $tagName) {
        // Get or create tag
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
        $stmt->execute([$tagName]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tag) {
            $stmt = $this->db->prepare("INSERT INTO tags (name, created_at, updated_at) VALUES (?, NOW(), NOW())");
            $stmt->execute([$tagName]);
            $tagId = $this->db->lastInsertId();
        } else {
            $tagId = $tag['id'];
        }
        
        // Link tag to note
        $stmt = $this->db->prepare("INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?, ?)");
        $stmt->execute([$noteId, $tagId]);
    }
}
