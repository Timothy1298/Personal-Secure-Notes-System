<?php
namespace Core\Collaboration;

use PDO;
use Exception;

class TeamManager {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createTeam(string $name, string $description, int $ownerId): array {
        try {
            $this->db->beginTransaction();

            // Create team
            $stmt = $this->db->prepare("
                INSERT INTO teams (name, description, created_by, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $description, $ownerId]);
            $teamId = $this->db->lastInsertId();

            // Add owner as admin
            $stmt = $this->db->prepare("
                INSERT INTO team_members (team_id, user_id, role, joined_at)
                VALUES (?, ?, 'admin', NOW())
            ");
            $stmt->execute([$teamId, $ownerId]);

            $this->db->commit();

            return [
                'success' => true,
                'team_id' => $teamId,
                'message' => 'Team created successfully'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create team: ' . $e->getMessage()
            ];
        }
    }

    public function getUserTeams(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, tm.role, tm.joined_at
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.user_id = ?
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user teams: " . $e->getMessage());
            return [];
        }
    }

    public function getTeamMembers(int $teamId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, u.email, u.first_name, u.last_name, tm.role, tm.joined_at
                FROM team_members tm
                JOIN users u ON tm.user_id = u.id
                WHERE tm.team_id = ?
                ORDER BY tm.joined_at ASC
            ");
            $stmt->execute([$teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting team members: " . $e->getMessage());
            return [];
        }
    }

    public function addTeamMember(int $teamId, int $userId, string $role = 'member'): array {
        try {
            // Check if user is already a member
            $stmt = $this->db->prepare("SELECT id FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$teamId, $userId]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'User is already a member of this team'
                ];
            }

            $stmt = $this->db->prepare("
                INSERT INTO team_members (team_id, user_id, role, joined_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$teamId, $userId, $role]);

            return [
                'success' => true,
                'message' => 'Member added successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add member: ' . $e->getMessage()
            ];
        }
    }

    public function removeTeamMember(int $teamId, int $userId): array {
        try {
            $stmt = $this->db->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$teamId, $userId]);

            return [
                'success' => true,
                'message' => 'Member removed successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove member: ' . $e->getMessage()
            ];
        }
    }

    public function updateMemberRole(int $teamId, int $userId, string $role): array {
        try {
            $stmt = $this->db->prepare("
                UPDATE team_members 
                SET role = ? 
                WHERE team_id = ? AND user_id = ?
            ");
            $stmt->execute([$role, $teamId, $userId]);

            return [
                'success' => true,
                'message' => 'Member role updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update member role: ' . $e->getMessage()
            ];
        }
    }

    public function shareNoteWithTeam(int $noteId, int $teamId, string $permission = 'read'): array {
        try {
            // Check if already shared
            $stmt = $this->db->prepare("SELECT id FROM team_shared_notes WHERE note_id = ? AND team_id = ?");
            $stmt->execute([$noteId, $teamId]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Note is already shared with this team'
                ];
            }

            $stmt = $this->db->prepare("
                INSERT INTO team_shared_notes (note_id, team_id, permission, shared_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$noteId, $teamId, $permission]);

            return [
                'success' => true,
                'message' => 'Note shared with team successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to share note: ' . $e->getMessage()
            ];
        }
    }

    public function shareTaskWithTeam(int $taskId, int $teamId, string $permission = 'read'): array {
        try {
            // Check if already shared
            $stmt = $this->db->prepare("SELECT id FROM team_shared_tasks WHERE task_id = ? AND team_id = ?");
            $stmt->execute([$taskId, $teamId]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Task is already shared with this team'
                ];
            }

            $stmt = $this->db->prepare("
                INSERT INTO team_shared_tasks (task_id, team_id, permission, shared_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$taskId, $teamId, $permission]);

            return [
                'success' => true,
                'message' => 'Task shared with team successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to share task: ' . $e->getMessage()
            ];
        }
    }

    public function getTeamSharedNotes(int $teamId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, tsn.permission, tsn.shared_at
                FROM team_shared_notes tsn
                JOIN notes n ON tsn.note_id = n.id
                WHERE tsn.team_id = ?
                ORDER BY tsn.shared_at DESC
            ");
            $stmt->execute([$teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting team shared notes: " . $e->getMessage());
            return [];
        }
    }

    public function getTeamSharedTasks(int $teamId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, tst.permission, tst.shared_at
                FROM team_shared_tasks tst
                JOIN tasks t ON tst.task_id = t.id
                WHERE tst.team_id = ?
                ORDER BY tst.shared_at DESC
            ");
            $stmt->execute([$teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting team shared tasks: " . $e->getMessage());
            return [];
        }
    }

    public function getTeamActivity(int $teamId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 'note_shared' as type, n.title as title, u.username as user, tsn.shared_at as created_at
                FROM team_shared_notes tsn
                JOIN notes n ON tsn.note_id = n.id
                JOIN users u ON n.user_id = u.id
                WHERE tsn.team_id = ?
                
                UNION ALL
                
                SELECT 'task_shared' as type, t.title as title, u.username as user, tst.shared_at as created_at
                FROM team_shared_tasks tst
                JOIN tasks t ON tst.task_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE tst.team_id = ?
                
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$teamId, $teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting team activity: " . $e->getMessage());
            return [];
        }
    }

    public function deleteTeam(int $teamId, int $userId): array {
        try {
            // Check if user is admin
            $stmt = $this->db->prepare("SELECT role FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$teamId, $userId]);
            $membership = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$membership || $membership['role'] !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Only team admins can delete teams'
                ];
            }

            $this->db->beginTransaction();

            // Delete team shared content
            $this->db->prepare("DELETE FROM team_shared_notes WHERE team_id = ?")->execute([$teamId]);
            $this->db->prepare("DELETE FROM team_shared_tasks WHERE team_id = ?")->execute([$teamId]);

            // Delete team members
            $this->db->prepare("DELETE FROM team_members WHERE team_id = ?")->execute([$teamId]);

            // Delete team
            $this->db->prepare("DELETE FROM teams WHERE id = ?")->execute([$teamId]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Team deleted successfully'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete team: ' . $e->getMessage()
            ];
        }
    }
}