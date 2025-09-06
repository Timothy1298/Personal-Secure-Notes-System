<?php
namespace App\Controllers;

use Core\Session;
use PDO;

class CategoriesController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        $userId = Session::get('user_id');

        $stmt = $this->db->prepare("SELECT * FROM categories WHERE user_id = :uid ORDER BY name ASC");
        $stmt->execute([':uid' => $userId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/categories.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $name = trim($_POST['name'] ?? '');
            $color = trim($_POST['color'] ?? '');

            if (!$name) {
                $_SESSION['errors'] = ["Category name is required."];
                header("Location: /categories");
                exit;
            }

            $stmt = $this->db->prepare("
                INSERT INTO categories (user_id, name, color)
                VALUES (:user_id, :name, :color)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $name,
                ':color' => !empty($color) ? $color : '#6b7280'
            ]);

            $_SESSION['success'] = "Category added successfully!";
            header("Location: /categories");
            exit;
        }
    }
}