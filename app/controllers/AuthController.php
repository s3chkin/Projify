<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/Auth.php";

class AuthController extends Controller {
    
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
        Session::start();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $this->auth->login($email, $password);
            
            if ($user) {
                Session::set('user_id', $user['id']);
                Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                Session::set('user_email', $user['email']);
                
                header('Location: index.php?url=home/index');
                exit;
            } else {
                $error = "Грешен email или парола!";
                $this->view("auth/login", ['error' => $error]);
            }
        } else {
            $this->view("auth/login");
        }
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $error = "Всички полета са задължителни!";
                $this->view("auth/register", ['error' => $error]);
                return;
            }
            
            if ($password !== $confirmPassword) {
                $error = "Паролите не съвпадат!";
                $this->view("auth/register", ['error' => $error]);
                return;
            }
            
            $existingUser = $this->auth->getUserByEmail($email);
            if ($existingUser) {
                $error = "Email адресът вече се използва!";
                $this->view("auth/register", ['error' => $error]);
                return;
            }
            
            $userId = $this->auth->register($firstName, $lastName, $email, $password);
            
            if ($userId) {
                $user = $this->auth->getUserById($userId);
                if ($user) {
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                    Session::set('user_email', $user['email']);
                    Session::set('user_role', $user['role'] ?? 'user');
                    
                    header('Location: index.php?url=home/index');
                    exit;
                }
            }
            
            $error = "Грешка при регистрация! Може email-ът вече да съществува.";
            $this->view("auth/register", ['error' => $error]);
        } else {
            $this->view("auth/register");
        }
    }
    
    public function logout() {
        Session::destroy();
                header('Location: index.php?url=auth/login');
                exit;
    }
}

