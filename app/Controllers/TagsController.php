<?php
namespace App\Controllers;

use Core\Session;
use PDO;

class TagsController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        $userId = Session::get('user_id');

        $stmt = $this->db->prepare("SELECT * FROM tags WHERE user_id = :uid ORDER BY name ASC");
        $stmt->execute([':uid' => $userId]);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/tags.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                $_SESSION['errors'] = ["Tag name is required."];
                header("Location: /tags");
                exit;
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO tags (user_id, name)
                    VALUES (:user_id, :name)
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':name' => $name
                ]);

                $_SESSION['success'] = "Tag added successfully!";
            } catch (\PDOException $e) {
                if ($e->getCode() == '23000') {
                    // This is the error code for unique constraint violation
                    $_SESSION['errors'] = ["Tag '{$name}' already exists."];
                } else {
                    $_SESSION['errors'] = ["An error occurred: " . $e->getMessage()];
                }
            }

            header("Location: /tags");
            exit;
        }
    }
}