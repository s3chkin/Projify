<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/Sprint.php";
require_once "../app/models/Project.php";
require_once "../app/models/Task.php";

class SprintController extends Controller {
    
    private $sprintModel;
    private $projectModel;
    private $taskModel;
    
    public function __construct() {
        $this->sprintModel = new Sprint();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function index() {
        $projectId = $_GET['project_id'] ?? null;
        $userId = Session::get('user_id');
        
        if (!$projectId) {
            $projects = $this->projectModel->getByOwner($userId);
            if (empty($projects)) {
                header('Location: index.php?url=project/index');
                exit;
            }
            $projectId = $projects[0]['id'];
        }
        
        $project = $this->projectModel->getById($projectId);
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($projectId, $userId))) {
            header('Location: index.php?url=project/index');
            exit;
        }
        
        $sprints = $this->sprintModel->getByProject($projectId);
        $projects = $this->projectModel->getByOwner($userId);
        
        $this->view("sprint/index", [
            'project' => $project,
            'projects' => $projects,
            'sprints' => $sprints
        ]);
    }
    
    public function create() {
        $projectId = $_GET['project_id'] ?? 0;
        $project = $this->projectModel->getById($projectId);
        $userId = Session::get('user_id');
        
        if (!$project || $project['owner_id'] != $userId) {
            header('Location: index.php?url=sprint/index');
            exit;
        }
        
        $this->view("sprint/create", ['project' => $project]);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $projectId = $_POST['project_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            $project = $this->projectModel->getById($projectId);
            $userId = Session::get('user_id');
            
            if (!$project || $project['owner_id'] != $userId) {
                header('Location: index.php?url=sprint/index');
                exit;
            }
            
            if (empty($name) || empty($startDate) || empty($endDate)) {
                $error = "Всички полета са задължителни!";
                $this->view("sprint/create", ['project' => $project, 'error' => $error]);
                return;
            }
            
            if ($endDate < $startDate) {
                $error = "Крайната дата не може да е преди началната дата!";
                $this->view("sprint/create", ['project' => $project, 'error' => $error]);
                return;
            }
            
            $id = $this->sprintModel->create($projectId, $name, $startDate, $endDate);
            
            if ($id) {
                header('Location: index.php?url=sprint/index&project_id=' . $projectId);
                exit;
            } else {
                $error = "Грешка при създаване на sprint-а!";
                $this->view("sprint/create", ['project' => $project, 'error' => $error]);
            }
        } else {
            header('Location: index.php?url=sprint/index');
            exit;
        }
    }
    
    public function show() {
        $id = $_GET['id'] ?? 0;
        $sprint = $this->sprintModel->getById($id);
        
        if (!$sprint) {
            header('Location: index.php?url=sprint/index');
            exit;
        }
        
        $project = $this->projectModel->getById($sprint['project_id']);
        $userId = Session::get('user_id');
        
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($sprint['project_id'], $userId))) {
            header('Location: index.php?url=sprint/index');
            exit;
        }
        
        $tasks = $this->sprintModel->getTasks($id);
        $allTasks = $this->taskModel->getByProject($sprint['project_id']);
        
        $this->view("sprint/show", [
            'sprint' => $sprint,
            'project' => $project,
            'tasks' => $tasks,
            'allTasks' => $allTasks
        ]);
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $sprint = $this->sprintModel->getById($id);
        
        if (!$sprint) {
            header('Location: index.php?url=sprint/index');
            exit;
        }
        
        $project = $this->projectModel->getById($sprint['project_id']);
        $userId = Session::get('user_id');
        
        if (!$project || $project['owner_id'] != $userId) {
            header('Location: index.php?url=sprint/index');
            exit;
        }
        
        if ($this->sprintModel->delete($id)) {
            header('Location: index.php?url=sprint/index&project_id=' . $sprint['project_id']);
            exit;
        } else {
            $error = "Грешка при изтриване на sprint-а!";
            $sprints = $this->sprintModel->getByProject($sprint['project_id']);
            $projects = $this->projectModel->getByOwner($userId);
            $this->view("sprint/index", [
                'project' => $project,
                'projects' => $projects,
                'sprints' => $sprints,
                'error' => $error
            ]);
        }
    }
    
    private function isProjectMember($projectId, $userId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        return $projectMemberModel->isMember($projectId, $userId);
    }
}

