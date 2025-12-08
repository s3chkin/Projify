<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Project.php";

class ProjectController extends Controller {
    
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
        Session::start();
        
        // Проверка дали потребителят е логнат
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    // READ - Показване на всички проекти (GET)
    public function index() {
        $userId = Session::get('user_id');
        $projects = $this->projectModel->getByOwner($userId);
        $this->view("project/index", ['projects' => $projects]);
    }
    
    // READ - Показване на един проект (GET)
    public function show() {
        $id = $_GET['id'] ?? 0;
        $project = $this->projectModel->getById($id);
        
        if (!$project) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        $this->view("project/show", ['project' => $project]);
    }
    
    // CREATE - Показване на форма за създаване (GET)
    public function create() {
        $this->view("project/create");
    }
    
    // CREATE - Обработка на форма за създаване (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $ownerId = Session::get('user_id');
            
            // Валидация
            if (empty($name)) {
                $error = "Името на проекта е задължително!";
                $this->view("project/create", ['error' => $error]);
                return;
            }
            
            $id = $this->projectModel->create($name, $ownerId);
            
            if ($id) {
                header('Location: index.php?url=project/index');
                exit;
            } else {
                $error = "Грешка при създаване на проекта!";
                $this->view("project/create", ['error' => $error]);
            }
        } else {
            header('Location: index.php?url=project/create');
            exit;
        }
    }
    
    // UPDATE - Показване на форма за редактиране (GET)
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $project = $this->projectModel->getById($id);
        
        if (!$project) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        // Проверка дали потребителят е собственик
        if ($project['owner_id'] != Session::get('user_id')) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        $this->view("project/edit", ['project' => $project]);
    }
    
    // UPDATE - Обработка на форма за редактиране (POST)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            // Първо проверяваме дали проектът съществува и е собственост на потребителя
            $project = $this->projectModel->getById($id);
            if (!$project || $project['owner_id'] != Session::get('user_id')) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            // След това правим валидация
            if (empty($name)) {
                $error = "Името на проекта е задължително!";
                $this->view("project/edit", ['project' => $project, 'error' => $error]);
                return;
            }
            
            if ($this->projectModel->update($id, $name)) {
                header('Location: index.php?url=project/index');
                exit;
            } else {
                $error = "Грешка при обновяване на проекта!";
                $this->view("project/edit", ['project' => $project, 'error' => $error]);
            }
        } else {
            header('Location: index.php?url=project/index');
            exit;
        }
    }
    
    // DELETE - Изтриване на проект (GET)
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        // Проверка дали проектът съществува и е собственост на потребителя
        $project = $this->projectModel->getById($id);
        if (!$project || $project['owner_id'] != Session::get('user_id')) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        if ($this->projectModel->delete($id)) {
            header('Location: index.php?url=project/index');
            exit;
        } else {
            $error = "Грешка при изтриване на проекта!";
            $projects = $this->projectModel->getByOwner(Session::get('user_id'));
            $this->view("project/index", ['projects' => $projects, 'error' => $error]);
        }
    }
}

