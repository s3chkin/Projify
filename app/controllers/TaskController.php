<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/Task.php";
require_once "../app/models/Project.php";
require_once "../app/models/Status.php";
require_once "../app/models/User.php";
require_once "../app/models/Comment.php";
require_once "../app/models/Label.php";

class TaskController extends Controller {
    
    private $taskModel;
    private $projectModel;
    private $statusModel;
    
    public function __construct() {
        $this->taskModel = new Task();
        $this->projectModel = new Project();
        $this->statusModel = new Status();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function index() {
        $userId = Session::get('user_id');
        $projectId = $_GET['project_id'] ?? null;
        $search = $_GET['search'] ?? '';
        $statusId = $_GET['status_id'] ?? null;
        $assigneeId = $_GET['assignee_id'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        if ($projectId) {
            $project = $this->projectModel->getById($projectId);
            if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($projectId, $userId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
        }
        
        if ($search) {
            $tasks = $this->taskModel->search($search, $projectId, $statusId, $assigneeId);
            $totalTasks = count($tasks);
            $totalPages = 1;
        } else {
            $tasks = $this->taskModel->getPaginated($projectId, $page, $perPage);
            $totalTasks = $this->taskModel->getCount($projectId);
            $totalPages = ceil($totalTasks / $perPage);
        }
        
        $statuses = $this->statusModel->getAll();
        $projects = $this->projectModel->getByOwner($userId);
        
        $this->view("task/index", [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'projects' => $projects,
            'selectedProjectId' => $projectId,
            'search' => $search,
            'selectedStatusId' => $statusId,
            'selectedAssigneeId' => $assigneeId,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalTasks' => $totalTasks
        ]);
    }
    
    public function show() {
        $id = $_GET['id'] ?? 0;
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($task['project_id']);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $commentModel = new Comment();
        $labelModel = new Label();
        $statuses = $this->statusModel->getAll();
        $comments = $commentModel->getByTask($id);
        $taskLabels = $labelModel->getByTask($id);
        $allLabels = $labelModel->getAll();
        
        $this->view("task/show", [
            'task' => $task, 
            'statuses' => $statuses,
            'comments' => $comments,
            'taskLabels' => $taskLabels,
            'allLabels' => $allLabels
        ]);
    }
    
    public function create() {
        $projectId = $_GET['project_id'] ?? 0;
        
        if (!$projectId) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($projectId);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($projectId, $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $statuses = $this->statusModel->getAll();
        $users = $this->getProjectMembers($projectId);
        
        $this->view("task/create", [
            'project' => $project,
            'statuses' => $statuses,
            'users' => $users
        ]);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $projectId = $_POST['project_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $statusId = $_POST['status_id'] ?? 0;
            $assigneeId = $_POST['assignee_id'] ?? null;
            $startDate = $_POST['start_date'] ?? null;
            $dueDate = $_POST['due_date'] ?? null;
            $priority = $_POST['priority'] ?? 0;
            
            // Валидация
            if (empty($title) || empty($statusId)) {
                $error = "Заглавието и статусът са задължителни!";
                $project = $this->projectModel->getById($projectId);
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
                return;
            }
            
            // Валидация на дати
            if ($startDate && $dueDate && $dueDate < $startDate) {
                $error = "Крайната дата не може да е преди началната дата!";
                $project = $this->projectModel->getById($projectId);
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
                return;
            }
            
            // Проверка за достъп
            $project = $this->projectModel->getById($projectId);
            $userId = Session::get('user_id');
            if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($projectId, $userId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $userId = Session::get('user_id');
            $id = $this->taskModel->create($projectId, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority, $userId);
            
            if ($id) {
                header('Location: index.php?url=task/index&project_id=' . $projectId);
                exit;
            } else {
                $error = "Грешка при създаване на задачата!";
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
            }
        } else {
            header('Location: index.php?url=task/index');
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($task['project_id']);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $statuses = $this->statusModel->getAll();
        $users = $this->getProjectMembers($task['project_id']);
        
        $this->view("task/edit", [
            'task' => $task,
            'project' => $project,
            'statuses' => $statuses,
            'users' => $users
        ]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $statusId = $_POST['status_id'] ?? 0;
            $assigneeId = $_POST['assignee_id'] ?? null;
            $startDate = $_POST['start_date'] ?? null;
            $dueDate = $_POST['due_date'] ?? null;
            $priority = $_POST['priority'] ?? 0;
            
            // Първо проверяваме дали задачата съществува и имаме достъп
            $task = $this->taskModel->getById($id);
            if (!$task) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $project = $this->projectModel->getById($task['project_id']);
            $userId = Session::get('user_id');
            if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            // Валидация
            if (empty($title) || empty($statusId)) {
                $error = "Заглавието и статусът са задължителни!";
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
                return;
            }
            
            // Валидация на дати
            if ($startDate && $dueDate && $dueDate < $startDate) {
                $error = "Крайната дата не може да е преди началната дата!";
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
                return;
            }
            
            if (!$this->statusModel->isValidTransition($task['status_id'], $statusId)) {
                $error = "Невалиден преход на статус!";
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
                return;
            }
            
            if ($this->taskModel->update($id, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority)) {
                header('Location: index.php?url=task/index&project_id=' . $task['project_id']);
                exit;
            } else {
                $error = "Грешка при обновяване на задачата!";
                $statuses = $this->statusModel->getAll();
                $users = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'users' => $users,
                    'error' => $error
                ]);
            }
        } else {
            header('Location: index.php?url=task/index');
            exit;
        }
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        $task = $this->taskModel->getById($id);
        if (!$task) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($task['project_id']);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $userId = Session::get('user_id');
        if ($this->taskModel->delete($id, $userId)) {
            header('Location: index.php?url=task/index&project_id=' . $task['project_id']);
            exit;
        } else {
            $error = "Грешка при изтриване на задачата!";
            $tasks = $this->taskModel->getByProject($task['project_id']);
            $statuses = $this->statusModel->getAll();
            $projects = $this->projectModel->getByOwner($userId);
            $this->view("task/index", [
                'tasks' => $tasks,
                'statuses' => $statuses,
                'projects' => $projects,
                'error' => $error
            ]);
        }
    }
    
    public function updateStatus() {
        $id = $_GET['id'] ?? 0;
        $statusId = $_GET['status_id'] ?? 0;
        
        $task = $this->taskModel->getById($id);
        if (!$task || !$statusId) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($task['project_id']);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        // Валидация на статус преход
        if (!$this->statusModel->isValidTransition($task['status_id'], $statusId)) {
            header('Location: index.php?url=task/index&project_id=' . $task['project_id'] . '&error=invalid_transition');
            exit;
        }
        
        if ($this->taskModel->updateStatus($id, $statusId)) {
            header('Location: index.php?url=task/index&project_id=' . $task['project_id']);
            exit;
        } else {
            header('Location: index.php?url=task/index&project_id=' . $task['project_id'] . '&error=update_failed');
            exit;
        }
    }
    
    // Helper методи
    private function isProjectMember($projectId, $userId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        return $projectMemberModel->isMember($projectId, $userId);
    }
    
    private function getProjectMembers($projectId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        $members = $projectMemberModel->getByProject($projectId);
        
        // Ако няма членове, добавяме поне собственика
        if (empty($members)) {
            $project = $this->projectModel->getById($projectId);
            if ($project) {
                require_once "../app/models/User.php";
                $userModel = new User();
                $owner = $userModel->getById($project['owner_id']);
                if ($owner) {
                    $owner['actual_role'] = 'owner';
                    $members = [$owner];
                }
            }
        }
        
        return $members;
    }
    
    public function addLabel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $taskId = $_POST['task_id'] ?? 0;
            $labelId = $_POST['label_id'] ?? 0;
            
            $task = $this->taskModel->getById($taskId);
            if (!$task || !$labelId) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $project = $this->projectModel->getById($task['project_id']);
            $userId = Session::get('user_id');
            if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $labelModel = new Label();
            if ($labelModel->addToTask($taskId, $labelId)) {
                header('Location: index.php?url=task/show&id=' . $taskId);
                exit;
            }
        }
        
        header('Location: index.php?url=task/index');
        exit;
    }
    
    public function removeLabel() {
        $taskId = $_GET['task_id'] ?? 0;
        $labelId = $_GET['label_id'] ?? 0;
        
        $task = $this->taskModel->getById($taskId);
        if (!$task || !$labelId) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $project = $this->projectModel->getById($task['project_id']);
        $userId = Session::get('user_id');
        if (!$project || ($project['owner_id'] != $userId && !$this->isProjectMember($task['project_id'], $userId))) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $labelModel = new Label();
        if ($labelModel->removeFromTask($taskId, $labelId)) {
            header('Location: index.php?url=task/show&id=' . $taskId);
            exit;
        }
        
        header('Location: index.php?url=task/index');
        exit;
    }
}

