<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Auth.php";

class AuthController extends Controller {
    
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
        Session::start();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Проста валидация
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
            
            $result = $this->auth->register($firstName, $lastName, $email, $password);
            
            // Проверяваме дали е масив с грешка
            if (is_array($result) && isset($result['error'])) {
                $error = "Грешка при регистрация: " . $result['error'];
                $this->view("auth/register", ['error' => $error]);
                return;
            }
            
            // Ако е успешно, $result е ID на потребителя
            if ($result && is_numeric($result)) {
                // Автоматично логване след регистрация
                $user = $this->auth->getUserById($result);
                if ($user) {
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                    Session::set('user_email', $user['email']);
                    
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

