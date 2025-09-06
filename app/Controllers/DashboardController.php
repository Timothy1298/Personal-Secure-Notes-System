<?php
namespace App\Controllers;

use Core\Session;

class DashboardController {
    public function index() {
        if (!Session::get('user_id')) {
            header("Location: /login");
            exit;
        }
        include __DIR__ . '/../Views/dashboard.php';
    }
}
