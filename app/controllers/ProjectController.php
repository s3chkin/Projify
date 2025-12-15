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
        
        try {
            $result = $this->projectModel->delete($id);
            if ($result) {
                $_SESSION['success'] = "Проектът е изтрит успешно!";
                header('Location: index.php?url=project/index');
                exit;
            } else {
                $errorDetails = "Грешка при изтриване на проекта!";
                error_log("Project deletion failed for project ID: $id");
                $_SESSION['error'] = $errorDetails . " Проверете error_log файла за повече детайли.";
                header('Location: index.php?url=project/index');
                exit;
            }
        } catch (Exception $e) {
            error_log("Exception in ProjectController::delete: " . $e->getMessage());
            $_SESSION['error'] = "Грешка при изтриване на проекта: " . htmlspecialchars($e->getMessage());
            header('Location: index.php?url=project/index');
            exit;
        }
    }
    
    public function members() {
        $id = $_GET['id'] ?? 0;
        $project = $this->projectModel->getById($id);
        $userId = Session::get('user_id');
        
        if (!$project) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        if ($project['owner_id'] != $userId && Session::get('user_role') !== 'admin') {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        require_once "../app/models/ProjectMember.php";
        require_once "../app/models/User.php";
        
        $projectMemberModel = new ProjectMember();
        $userModel = new User();
        
        $members = $projectMemberModel->getByProject($id);
        $allUsers = $userModel->getAll();
        
        $existingMemberIds = array_map(function($m) { return $m['id']; }, $members);
        $availableUsers = array_filter($allUsers, function($u) use ($existingMemberIds, $project) {
            return !in_array($u['id'], $existingMemberIds) && $u['id'] != $project['owner_id'];
        });
        
        $this->view("project/members", [
            'project' => $project,
            'members' => $members,
            'availableUsers' => $availableUsers
        ]);
    }
    
    public function addMember() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $projectId = (int)($_POST['project_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? 0);
            
            $project = $this->projectModel->getById($projectId);
            $currentUserId = Session::get('user_id');
            
            if (!$project || ($project['owner_id'] != $currentUserId && Session::get('user_role') !== 'admin')) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            if ($userId == $project['owner_id']) {
                $_SESSION['error'] = "Собственикът вече е част от проекта!";
                header('Location: index.php?url=project/members&id=' . $projectId);
                exit;
            }
            
            require_once "../app/models/ProjectMember.php";
            $projectMemberModel = new ProjectMember();
            
            if ($projectMemberModel->add($projectId, $userId, 'member')) {
                $_SESSION['success'] = "Потребителят е добавен успешно към проекта!";
            } else {
                $_SESSION['error'] = "Грешка при добавяне на потребителя! Може вече да е член.";
            }
            
            header('Location: index.php?url=project/members&id=' . $projectId);
            exit;
        }
        
        header('Location: index.php?url=project/index');
        exit;
    }
    
    public function removeMember() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $projectId = (int)($_POST['project_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? 0);
            
            $project = $this->projectModel->getById($projectId);
            $currentUserId = Session::get('user_id');
            
            if (!$project || ($project['owner_id'] != $currentUserId && Session::get('user_role') !== 'admin')) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            if ($userId == $project['owner_id']) {
                $_SESSION['error'] = "Не можете да премахнете собственика на проекта!";
                header('Location: index.php?url=project/members&id=' . $projectId);
                exit;
            }
            
            require_once "../app/models/ProjectMember.php";
            $projectMemberModel = new ProjectMember();
            
            if ($projectMemberModel->remove($projectId, $userId)) {
                $_SESSION['success'] = "Потребителят е премахнат успешно от проекта!";
            } else {
                $_SESSION['error'] = "Грешка при премахване на потребителя!";
            }
            
            header('Location: index.php?url=project/members&id=' . $projectId);
            exit;
        }
        
        header('Location: index.php?url=project/index');
        exit;
    }
    
    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = (int)($_POST['id'] ?? 0);
            $project = $this->projectModel->getById($id);
            $userId = Session::get('user_id');
            
            if (!$project) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            if ($project['owner_id'] != $userId && Session::get('user_role') !== 'admin') {
                $_SESSION['error'] = "Нямате права да завършите този проект!";
                header('Location: index.php?url=project/show&id=' . $id);
                exit;
            }
            
            if (!$this->projectModel->canBeCompleted($id)) {
                $_SESSION['error'] = "Проектът не може да бъде завършен! Всички задачи трябва да са завършени (Done).";
                header('Location: index.php?url=project/show&id=' . $id);
                exit;
            }
            
            if ($this->projectModel->complete($id)) {
                $_SESSION['success'] = "Проектът е завършен успешно!";
            } else {
                $_SESSION['error'] = "Грешка при завършване на проекта!";
            }
            
            header('Location: index.php?url=project/show&id=' . $id);
            exit;
        }
        
        header('Location: index.php?url=project/index');
        exit;
    }
    
    public function reopen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = (int)($_POST['id'] ?? 0);
            $project = $this->projectModel->getById($id);
            $userId = Session::get('user_id');
            
            if (!$project) {
                header('Location: index.php?url=project/index');
                exit;
            }
            
            if ($project['owner_id'] != $userId && Session::get('user_role') !== 'admin') {
                $_SESSION['error'] = "Нямате права да отворите отново този проект!";
                header('Location: index.php?url=project/show&id=' . $id);
                exit;
            }
            
            if ($this->projectModel->reopen($id)) {
                $_SESSION['success'] = "Проектът е отворен отново!";
            } else {
                $_SESSION['error'] = "Грешка при отваряне на проекта!";
            }
            
            header('Location: index.php?url=project/show&id=' . $id);
            exit;
        }
        
        header('Location: index.php?url=project/index');
        exit;
    }
}

