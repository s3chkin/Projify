<?php

require_once "Session.php";
require_once "CSRF.php";

class Controller {

    public function view($view, $data = []) {
        extract($data); // прави $data достъпно като променливи
        require "../app/views/layout/header.php";
        require "../app/views/" . $view . ".php";
        require "../app/views/layout/footer.php";
    }
    
    // Проверка на CSRF token
    protected function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!CSRF::validateToken($token)) {
                die("CSRF token validation failed!");
            }
        }
    }
}
