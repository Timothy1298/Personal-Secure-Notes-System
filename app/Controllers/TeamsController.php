<?php
namespace App\Controllers;

use Core\Session;
use Core\Database;
use Core\Collaboration\TeamManager;
use Exception;

class TeamsController {
    private $db;
    private $teamManager;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->teamManager = new TeamManager($this->db);
    }

    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        $teams = $this->teamManager->getUserTeams($userId);
        
        include __DIR__ . '/../Views/teams.php';
    }

    public function create() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Team name is required']);
            exit;
        }

        try {
            $result = $this->teamManager->createTeam($name, $description, $userId);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function view($teamId) {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }

        $userId = Session::get('user_id');
        
        // Get team details
        $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$team) {
            header("Location: /teams");
            exit;
        }

        // Check if user is member
        $stmt = $this->db->prepare("SELECT role FROM team_members WHERE team_id = ? AND user_id = ?");
        $stmt->execute([$teamId, $userId]);
        $membership = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$membership) {
            header("Location: /teams");
            exit;
        }

        // Get team data
        $members = $this->teamManager->getTeamMembers($teamId);
        $sharedNotes = $this->teamManager->getTeamSharedNotes($teamId);
        $sharedTasks = $this->teamManager->getTeamSharedTasks($teamId);
        $activity = $this->teamManager->getTeamActivity($teamId);

        include __DIR__ . '/../Views/team_detail.php';
    }

    public function addMember() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $teamId = $_POST['team_id'] ?? null;
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'member';

        if (!$teamId || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Team ID and email are required']);
            exit;
        }

        try {
            // Find user by email
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            $result = $this->teamManager->addTeamMember($teamId, $user['id'], $role);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function removeMember() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $teamId = $_POST['team_id'] ?? null;
        $userId = $_POST['user_id'] ?? null;

        if (!$teamId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'Team ID and User ID are required']);
            exit;
        }

        try {
            $result = $this->teamManager->removeTeamMember($teamId, $userId);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function updateMemberRole() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $teamId = $_POST['team_id'] ?? null;
        $userId = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? 'member';

        if (!$teamId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'Team ID and User ID are required']);
            exit;
        }

        try {
            $result = $this->teamManager->updateMemberRole($teamId, $userId, $role);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function shareNote() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $noteId = $_POST['note_id'] ?? null;
        $teamId = $_POST['team_id'] ?? null;
        $permission = $_POST['permission'] ?? 'read';

        if (!$noteId || !$teamId) {
            echo json_encode(['success' => false, 'message' => 'Note ID and Team ID are required']);
            exit;
        }

        try {
            $result = $this->teamManager->shareNoteWithTeam($noteId, $teamId, $permission);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function shareTask() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? null;
        $teamId = $_POST['team_id'] ?? null;
        $permission = $_POST['permission'] ?? 'read';

        if (!$taskId || !$teamId) {
            echo json_encode(['success' => false, 'message' => 'Task ID and Team ID are required']);
            exit;
        }

        try {
            $result = $this->teamManager->shareTaskWithTeam($taskId, $teamId, $permission);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function delete() {
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $teamId = $_POST['team_id'] ?? null;

        if (!$teamId) {
            echo json_encode(['success' => false, 'message' => 'Team ID is required']);
            exit;
        }

        try {
            $result = $this->teamManager->deleteTeam($teamId, $userId);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
