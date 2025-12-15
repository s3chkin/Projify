<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Project.php";

class ProjectController extends Controller {
    
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function index() {
        $userId = Session::get('user_id');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 9;
        
        $projects = $this->projectModel->getPaginatedByOwner($userId, $page, $perPage);
        $totalProjects = $this->projectModel->getCountByOwner($userId);
        $totalPages = ceil($totalProjects / $perPage);
        
        $this->view("project/index", [
            'projects' => $projects,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalProjects' => $totalProjects
        ]);
    }
    
    public function show() {
        $id = $_GET['id'] ?? 0;
        $project = $this->projectModel->getById($id);
        
        if (!$project) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        $this->view("project/show", ['project' => $project]);
    }
    
    public function create() {
        $this->view("project/create");
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $name = $_POST['name'] ?? '';
            $ownerId = Session::get('user_id');
            
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
                $error = "Грешка при създаване на проекта! Може да има проект с това име вече.";
                $this->view("project/create", ['error' => $error]);
            }
        } else {
            header('Location: index.php?url=project/create');
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $project = $this->projectModel->getById($id);
        
        if (!$project) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        if ($project['owner_id'] != Session::get('user_id')) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        $this->view("project/edit", ['project' => $project]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            $project = $this->projectModel->getById($id);
            if (!$project || $project['owner_id'] != Session::get('user_id')) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            if (empty($name)) {
                $error = "Името на проекта е задължително!";
                $this->view("project/edit", ['project' => $project, 'error' => $error]);
                return;
            }
            
            if ($this->projectModel->update($id, $name)) {
                header('Location: index.php?url=project/index');
                exit;
            } else {
                $error = "Грешка при обновяване на проекта! Може да има проект с това име вече.";
                $this->view("project/edit", ['project' => $project, 'error' => $error]);
            }
        } else {
            header('Location: index.php?url=project/index');
            exit;
        }
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
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

