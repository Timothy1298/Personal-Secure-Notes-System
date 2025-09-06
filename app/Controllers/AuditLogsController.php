<?php
namespace App\Controllers;

use Core\Session;
use PDO;
use PDOException;
use App\Views\View;

class AuditLogsController
{
    private $db;
    private $view;

    public function __construct(PDO $db, View $view)
    {
        $this->db = $db;
        $this->view = $view;
    }

    public function index()
    {
        $page   = (int) ($_GET['page'] ?? 1);
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $user_id = Session::get('user_id');

        try {
            // Count total logs
            $countStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM audit_logs WHERE user_id = :user_id"
            );
            $countStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $countStmt->execute();
            $totalLogs = $countStmt->fetchColumn();

            // Fetch logs with pagination
            $stmt = $this->db->prepare(
                "SELECT * FROM audit_logs 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->view->load('audit_logs', [
                'logs'        => $logs,
                'page'        => $page,
                'total_pages' => max(1, ceil($totalLogs / $limit)),
            ]);
        } catch (PDOException $e) {
            die("Error fetching audit logs: " . $e->getMessage());
        }
    }

    public function logEvent($user_id, $action, $ip = null, $user_agent = null)
    {
        $ip_address  = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent  = $user_agent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO audit_logs (user_id, action, ip_address, user_agent) 
                 VALUES (:user_id, :action, :ip, :ua)"
            );
            $stmt->execute([
                ':user_id' => $user_id,
                ':action'  => $action,
                ':ip'      => $ip_address,
                ':ua'      => $user_agent
            ]);
        } catch (PDOException $e) {
            error_log("Failed to log audit event: " . $e->getMessage());
        }
    }

    public function filterLogs()
    {
        $start_time = $_GET['start_time'] ?? null;
        $end_time   = $_GET['end_time'] ?? null;
        $user_id    = Session::get('user_id');

        $whereClause = "WHERE user_id = :user_id";
        $params      = [':user_id' => $user_id];

        if ($start_time) {
            $whereClause .= " AND created_at >= :start_time";
            $params[':start_time'] = $start_time;
        }

        if ($end_time) {
            $whereClause .= " AND created_at <= :end_time";
            $params[':end_time'] = $end_time;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM audit_logs 
                 $whereClause 
                 ORDER BY created_at DESC"
            );
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->view->load('audit_logs', [
                'logs'      => $logs,
                'isFiltered'=> true,
                'filters'   => [
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
            ]);
        } catch (PDOException $e) {
            die("Error fetching filtered audit logs: " . $e->getMessage());
        }
    }
}
