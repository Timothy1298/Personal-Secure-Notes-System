<?php
namespace Core;

use PDO;
use Exception;

class NotificationService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new notification for a user.
     * @param int $userId The ID of the user to notify.
     * @param string $type The type of notification (e.g., 'task_reminder', 'security_alert', 'system_message').
     * @param string $title The title of the notification.
     * @param string $message The main message content of the notification.
     * @param array $data Optional: additional data to store as JSON.
     * @return int|false The ID of the new notification or false on failure.
     */
    public function createNotification(int $userId, string $type, string $title, string $message, array $data = []): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, data, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                json_encode($data)
            ]);

            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all unread notifications for a user.
     * @param int $userId The ID of the user.
     * @return array An array of unread notifications.
     */
    public function getUnreadNotifications(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get unread notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all notifications for a user (read and unread).
     * @param int $userId The ID of the user.
     * @param int $limit Optional: limit the number of notifications.
     * @param int $offset Optional: offset for pagination.
     * @return array An array of notifications.
     */
    public function getAllNotifications(int $userId, int $limit = 20, int $offset = 0): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?
            ");
            $stmt->bindParam(1, $userId, PDO::PARAM_INT);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get all notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark a notification as read.
     * @param int $notificationId The ID of the notification.
     * @param int $userId The ID of the user (for security check).
     * @return bool True on success, false on failure.
     */
    public function markAsRead(int $notificationId, int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            error_log("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications for a user as read.
     * @param int $userId The ID of the user.
     * @return bool True on success, false on failure.
     */
    public function markAllAsRead(int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0
            ");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Failed to mark all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a notification.
     * @param int $notificationId The ID of the notification.
     * @param int $userId The ID of the user (for security check).
     * @return bool True on success, false on failure.
     */
    public function deleteNotification(int $notificationId, int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            error_log("Failed to delete notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all notifications for a user.
     * @param int $userId The ID of the user.
     * @return bool True on success, false on failure.
     */
    public function clearAllNotifications(int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications WHERE user_id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Failed to clear all notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notification count for a user.
     * @param int $userId The ID of the user.
     * @param bool $unreadOnly Whether to count only unread notifications.
     * @return int The number of notifications.
     */
    public function getNotificationCount(int $userId, bool $unreadOnly = true): int {
        try {
            $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ?";
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Failed to get notification count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create a task reminder notification.
     * @param int $userId The ID of the user.
     * @param string $taskTitle The title of the task.
     * @param string $dueDate The due date of the task.
     * @return int|false The ID of the notification or false on failure.
     */
    public function createTaskReminder(int $userId, string $taskTitle, string $dueDate): int|false {
        return $this->createNotification(
            $userId,
            'task_reminder',
            'Task Reminder',
            "Don't forget: {$taskTitle} is due on {$dueDate}",
            ['task_title' => $taskTitle, 'due_date' => $dueDate]
        );
    }

    /**
     * Create a security alert notification.
     * @param int $userId The ID of the user.
     * @param string $title The title of the alert.
     * @param string $message The message content.
     * @param array $data Additional data.
     * @return int|false The ID of the notification or false on failure.
     */
    public function createSecurityAlert(int $userId, string $title, string $message, array $data = []): int|false {
        return $this->createNotification(
            $userId,
            'security_alert',
            $title,
            $message,
            $data
        );
    }

    /**
     * Create a system message notification.
     * @param int $userId The ID of the user.
     * @param string $title The title of the message.
     * @param string $message The message content.
     * @param array $data Additional data.
     * @return int|false The ID of the notification or false on failure.
     */
    public function createSystemMessage(int $userId, string $title, string $message, array $data = []): int|false {
        return $this->createNotification(
            $userId,
            'system_message',
            $title,
            $message,
            $data
        );
    }

    /**
     * Create a backup notification.
     * @param int $userId The ID of the user.
     * @param string $status The status of the backup (success, failed, etc.).
     * @param string $message The message content.
     * @param array $data Additional data.
     * @return int|false The ID of the notification or false on failure.
     */
    public function createBackupNotification(int $userId, string $status, string $message, array $data = []): int|false {
        return $this->createNotification(
            $userId,
            'backup_' . $status,
            'Backup ' . ucfirst($status),
            $message,
            $data
        );
    }

    /**
     * Clean up old notifications.
     * @param int $daysOld The age in days of notifications to clean up.
     * @return int The number of notifications cleaned up.
     */
    public function cleanupOldNotifications(int $daysOld = 30): int {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$daysOld]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Failed to cleanup old notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notification statistics for a user.
     * @param int $userId The ID of the user.
     * @return array Statistics about notifications.
     */
    public function getNotificationStats(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread,
                    COUNT(CASE WHEN type = 'task_reminder' THEN 1 END) as task_reminders,
                    COUNT(CASE WHEN type = 'security_alert' THEN 1 END) as security_alerts,
                    COUNT(CASE WHEN type = 'system_message' THEN 1 END) as system_messages,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_week
                FROM notifications 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get notification stats: " . $e->getMessage());
            return [
                'total' => 0,
                'unread' => 0,
                'task_reminders' => 0,
                'security_alerts' => 0,
                'system_messages' => 0,
                'last_week' => 0
            ];
        }
    }
}