<?php
namespace App\Controllers;

use Core\Session;
use Core\CSRF;
use Core\Database;
use Core\DynamicContentService;
use Core\Cache;
use Core\ThemeManager;
use Core\KeyboardShortcuts;
use App\Models\NotesModel;
use App\Models\TasksModel;
use App\Models\User;
use Exception;
use PDO;

class DashboardController {
    private $db;
    private $notesModel;
    private $tasksModel;
    private $dynamicContentService;
    private $cache;
    private $themeManager;
    private $keyboardShortcuts;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->notesModel = new NotesModel($this->db);
        $this->tasksModel = new TasksModel($this->db);
        $this->dynamicContentService = new DynamicContentService($this->db);
        $this->cache = Cache::getInstance($this->db);
        $this->themeManager = new ThemeManager($this->db);
        $this->keyboardShortcuts = new KeyboardShortcuts($this->db);
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get user data
            $user = User::findById($userId);
            if (!$user) {
                // If user not found, redirect to login
                header("Location: /login");
                exit;
            }
            
            // Calculate dashboard statistics
            $notes = $this->notesModel->getNotesWithTagsByUserId($userId);
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            
            // Calculate stats
            $stats = [
                'total_notes' => count($notes),
                'active_tasks' => count(array_filter($tasks, function($task) {
                    return $task['status'] !== 'completed';
                })),
                'completed_tasks' => count(array_filter($tasks, function($task) {
                    return $task['status'] === 'completed';
                })),
                'overdue_tasks' => count(array_filter($tasks, function($task) {
                    return $task['due_date'] && 
                           $task['status'] !== 'completed' && 
                           strtotime($task['due_date']) < time();
                })),
                'notes_this_week' => count(array_filter($notes, function($note) {
                    return strtotime($note['created_at']) > strtotime('-7 days');
                })),
                'productivity_score' => $this->calculateProductivityScore($tasks),
                'daily_streak' => $this->calculateDailyStreak($tasks, $notes)
            ];
            
            // Get dynamic content
            $dynamicContent = $this->dynamicContentService->getDashboardContent($userId);
            
        } catch (Exception $e) {
            // Fallback stats if database fails
            $user = [
                'username' => 'User',
                'first_name' => null,
                'last_name' => null
            ];
            $stats = [
                'total_notes' => 0,
                'active_tasks' => 0,
                'completed_tasks' => 0,
                'overdue_tasks' => 0,
                'notes_this_week' => 0,
                'productivity_score' => 0,
                'daily_streak' => 0
            ];
            $dynamicContent = [
                'weather' => [
                    'location' => 'New York, NY',
                    'temperature' => 22.0,
                    'description' => 'Partly Cloudy',
                    'humidity' => 65,
                    'wind_speed' => 12.0
                ],
                'quote' => [
                    'text' => 'The way to get started is to quit talking and begin doing.',
                    'author' => 'Walt Disney',
                    'category' => 'motivation'
                ]
            ];
        }
        
        include __DIR__ . '/../Views/dashboard.php';
    }

    // API endpoint for activity data
    public function apiActivity() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            $activities = [];
            
            // Get recent audit logs for user activities
            $stmt = $this->db->prepare("
                SELECT al.*, 
                       CASE 
                           WHEN al.resource_type = 'note' THEN n.title
                           WHEN al.resource_type = 'task' THEN t.title
                           ELSE NULL
                       END as resource_title
                FROM audit_logs al
                LEFT JOIN notes n ON al.resource_type = 'note' AND al.resource_id = n.id AND n.user_id = ?
                LEFT JOIN tasks t ON al.resource_type = 'task' AND al.resource_id = t.id AND t.user_id = ?
                WHERE al.user_id = ? 
                AND al.action IN ('note_created', 'note_updated', 'note_deleted', 'task_created', 'task_updated', 'task_completed', 'task_deleted')
                ORDER BY al.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $auditLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($auditLogs as $log) {
                $activity = $this->formatActivityFromLog($log);
                if ($activity) {
                    $activities[] = $activity;
                }
            }
            
            // If no audit logs, fallback to recent notes and tasks
            if (empty($activities)) {
                $recentNotes = $this->notesModel->getNotesWithTagsByUserId($userId);
                $recentTasks = $this->tasksModel->getTasksByUserId($userId);
                
                // Add recent notes as activities
                foreach (array_slice($recentNotes, 0, 3) as $note) {
                    $activities[] = [
                        'id' => 'note_' . $note['id'],
                        'type' => 'note',
                        'action' => 'updated',
                        'title' => 'Note: ' . $note['title'],
                        'description' => substr($note['summary'] ?: $note['content'], 0, 50) . '...',
                        'time' => $this->timeAgo($note['updated_at']),
                        'icon' => 'fas fa-sticky-note',
                        'color' => 'bg-blue-500'
                    ];
                }
                
                // Add recent tasks as activities
                foreach (array_slice($recentTasks, 0, 3) as $task) {
                    $activities[] = [
                        'id' => 'task_' . $task['id'],
                        'type' => 'task',
                        'action' => $task['status'] === 'completed' ? 'completed' : 'updated',
                        'title' => 'Task: ' . $task['title'],
                        'description' => substr($task['description'], 0, 50) . '...',
                        'time' => $this->timeAgo($task['updated_at']),
                        'icon' => $task['status'] === 'completed' ? 'fas fa-check-circle' : 'fas fa-tasks',
                        'color' => 'bg-green-500'
                    ];
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'activities' => array_slice($activities, 0, 8)]);
            
        } catch (Exception $e) {
            error_log("Activity API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load activity']);
        }
    }

    // Format activity from audit log
    private function formatActivityFromLog($log) {
        $action = $log['action'];
        $resourceType = $log['resource_type'];
        $resourceTitle = $log['resource_title'] ?? 'Unknown';
        
        $activityMap = [
            'note_created' => [
                'title' => 'Created note: ' . $resourceTitle,
                'icon' => 'fas fa-plus',
                'color' => 'bg-blue-500'
            ],
            'note_updated' => [
                'title' => 'Updated note: ' . $resourceTitle,
                'icon' => 'fas fa-edit',
                'color' => 'bg-blue-500'
            ],
            'note_deleted' => [
                'title' => 'Deleted note: ' . $resourceTitle,
                'icon' => 'fas fa-trash',
                'color' => 'bg-red-500'
            ],
            'task_created' => [
                'title' => 'Created task: ' . $resourceTitle,
                'icon' => 'fas fa-plus-circle',
                'color' => 'bg-green-500'
            ],
            'task_updated' => [
                'title' => 'Updated task: ' . $resourceTitle,
                'icon' => 'fas fa-edit',
                'color' => 'bg-green-500'
            ],
            'task_completed' => [
                'title' => 'Completed task: ' . $resourceTitle,
                'icon' => 'fas fa-check-circle',
                'color' => 'bg-green-600'
            ],
            'task_deleted' => [
                'title' => 'Deleted task: ' . $resourceTitle,
                'icon' => 'fas fa-trash',
                'color' => 'bg-red-500'
            ]
        ];
        
        if (!isset($activityMap[$action])) {
            return null;
        }
        
        $activity = $activityMap[$action];
        
        return [
            'id' => $log['id'],
            'type' => $resourceType,
            'action' => str_replace($resourceType . '_', '', $action),
            'title' => $activity['title'],
            'description' => 'Activity logged',
            'time' => $this->timeAgo($log['created_at']),
            'icon' => $activity['icon'],
            'color' => $activity['color']
        ];
    }

    // API endpoint for today's focus
    public function apiTodaysFocus() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get today's high priority tasks
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            $today = date('Y-m-d');
            
            $focusItems = [];
            foreach ($tasks as $task) {
                // Focus on high priority tasks or tasks due today
                if ($task['priority'] === 'high' || $task['priority'] === 'urgent' || 
                    ($task['due_date'] && date('Y-m-d', strtotime($task['due_date'])) === $today)) {
                    $focusItems[] = [
                        'id' => $task['id'],
                        'title' => $task['title'],
                        'completed' => $task['status'] === 'completed',
                        'priority' => $task['priority']
                    ];
                }
            }
            
            // Limit to 5 items
            $focusItems = array_slice($focusItems, 0, 5);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'focusItems' => $focusItems]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load focus items']);
        }
    }

    // API endpoint for recent notes
    public function apiRecentNotes() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get recent notes from database
            $notes = $this->notesModel->getNotesWithTagsByUserId($userId);
            
            $recentNotes = [];
            foreach (array_slice($notes, 0, 5) as $note) {
                $recentNotes[] = [
                    'id' => $note['id'],
                    'title' => $note['title'],
                    'preview' => substr($note['summary'] ?: $note['content'], 0, 80) . '...',
                    'updated_at' => $this->timeAgo($note['updated_at']),
                    'category' => $note['priority'] // Using priority as category for now
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'notes' => $recentNotes]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load recent notes']);
        }
    }

    // API endpoint for upcoming tasks
    public function apiUpcomingTasks() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        
        try {
            // Get upcoming tasks from database
            $tasks = $this->tasksModel->getTasksByUserId($userId);
            $today = date('Y-m-d');
            $nextWeek = date('Y-m-d', strtotime('+7 days'));
            
            $upcomingTasks = [];
            foreach ($tasks as $task) {
                if ($task['due_date'] && $task['status'] !== 'completed') {
                    $dueDate = date('Y-m-d', strtotime($task['due_date']));
                    if ($dueDate >= $today && $dueDate <= $nextWeek) {
                        $upcomingTasks[] = [
                            'id' => $task['id'],
                            'title' => $task['title'],
                            'due_date' => $this->formatDueDate($task['due_date']),
                            'priority' => $task['priority']
                        ];
                    }
                }
            }
            
            // Sort by due date
            usort($upcomingTasks, function($a, $b) {
                return strtotime($a['due_date']) - strtotime($b['due_date']);
            });
            
            // Limit to 5 items
            $upcomingTasks = array_slice($upcomingTasks, 0, 5);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'tasks' => $upcomingTasks]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load upcoming tasks']);
        }
    }

    // API endpoint for toggling focus items
    public function apiToggleFocus() {
        if (!Session::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // CSRF Protection
        $input = json_decode(file_get_contents('php://input'), true);
        if (!CSRF::verify($input['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $itemId = $input['item_id'] ?? null;
        $completed = $input['completed'] ?? false;

        // In a real app, this would update the database
        // For now, just return success
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Focus item updated']);
    }

    // Helper method to format time ago
    private function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        if ($time < 31536000) return floor($time/2592000) . ' months ago';
        return floor($time/31536000) . ' years ago';
    }

    // Helper method to format due date
    private function formatDueDate($dueDate) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $due = date('Y-m-d', strtotime($dueDate));
        
        if ($due === $today) return 'Today';
        if ($due === $tomorrow) return 'Tomorrow';
        
        $daysDiff = (strtotime($due) - strtotime($today)) / (60 * 60 * 24);
        if ($daysDiff > 0 && $daysDiff <= 7) {
            return 'In ' . floor($daysDiff) . ' days';
        }
        
        return date('M j', strtotime($dueDate));
    }

    // Calculate productivity score based on task completion
    private function calculateProductivityScore($tasks) {
        if (empty($tasks)) return 0;
        
        $completedTasks = count(array_filter($tasks, function($task) {
            return $task['status'] === 'completed';
        }));
        
        $totalTasks = count($tasks);
        return round(($completedTasks / $totalTasks) * 100);
    }

    // Calculate daily streak based on recent activity
    private function calculateDailyStreak($tasks, $notes) {
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        // Check last 30 days for activity
        for ($i = 0; $i < 30; $i++) {
            $checkDate = date('Y-m-d', strtotime("-$i days"));
            $hasActivity = false;
            
            // Check if there was task activity on this date
            foreach ($tasks as $task) {
                if (date('Y-m-d', strtotime($task['created_at'])) === $checkDate ||
                    date('Y-m-d', strtotime($task['updated_at'])) === $checkDate) {
                    $hasActivity = true;
                    break;
                }
            }
            
            // Check if there was note activity on this date
            if (!$hasActivity) {
                foreach ($notes as $note) {
                    if (date('Y-m-d', strtotime($note['created_at'])) === $checkDate ||
                        date('Y-m-d', strtotime($note['updated_at'])) === $checkDate) {
                        $hasActivity = true;
                        break;
                    }
                }
            }
            
            if ($hasActivity) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }
}
