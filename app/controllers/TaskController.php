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
            if (!$project || (!$this->isAdmin() && $project['owner_id'] != $userId && !$this->isProjectMember($projectId, $userId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
        }
        
        $isAdmin = $this->isAdmin();
        
        if ($search) {
            $tasks = $this->taskModel->search($search, $projectId, $statusId, $assigneeId, $userId, $isAdmin);
            $totalTasks = count($tasks);
            $totalPages = 1;
        } else {
            $tasks = $this->taskModel->getPaginated($projectId, $page, $perPage, $statusId, $userId, $isAdmin);
            $totalTasks = $this->taskModel->getCount($projectId, $statusId, $userId, $isAdmin);
            $totalPages = ceil($totalTasks / $perPage);
        }
        
        $statuses = $this->statusModel->getAll();
        
        if ($this->isAdmin()) {
            $projects = $this->projectModel->getAll();
        } else {
            require_once "../app/models/User.php";
            $userModel = new User();
            $projects = $userModel->getProjectsByUser($userId);
        }
        
        $canCreate = false;
        if ($projectId) {
            $canCreate = $this->canAccessProject($projectId, $userId);
        }
        
        // Добавяме информация за права на редактиране за всяка задача
        $tasksWithPermissions = [];
        foreach ($tasks as $task) {
            $projectForTask = $this->projectModel->getById($task['project_id']);
            $canEditTask = $this->canAccessProject($task['project_id'], $userId) && 
                          ($this->isAdmin() || 
                           ($projectForTask && $projectForTask['owner_id'] == $userId) || 
                           $task['assignee_id'] != $userId);
            $task['canEdit'] = $canEditTask;
            $tasksWithPermissions[] = $task;
        }
        
        $this->view("task/index", [
            'tasks' => $tasksWithPermissions,
            'statuses' => $statuses,
            'projects' => $projects,
            'selectedProjectId' => $projectId,
            'search' => $search,
            'selectedStatusId' => $statusId,
            'selectedAssigneeId' => $assigneeId,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalTasks' => $totalTasks,
            'canCreate' => $canCreate
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
        
        $canAccess = $this->canAccessProject($task['project_id'], $userId);
        
        // Проверка: назначеният на задачата НЕ може да я редактира (освен ако е собственик или админ)
        $canEdit = $canAccess && ($this->isAdmin() || $project['owner_id'] == $userId || $task['assignee_id'] != $userId);
        
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
            'allLabels' => $allLabels,
            'canAccess' => $canAccess,
            'canEdit' => $canEdit
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
        
        if (!$project) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        if (!$this->canAccessProject($projectId, $userId)) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        $statuses = $this->statusModel->getAll();
        $members = $this->getProjectMembers($projectId);
        
        $this->view("task/create", [
            'project' => $project,
            'statuses' => $statuses,
            'members' => $members
        ]);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $projectId = (int)($_POST['project_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $statusId = !empty($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
            $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = !empty($_POST['priority']) && $_POST['priority'] !== '' ? (int)$_POST['priority'] : null;
            
            if (empty($description)) {
                $description = null;
            }
            
            $userId = Session::get('user_id');
            $assigneeId = $_POST['assignee_id'] ?? null;
            if ($assigneeId === '' || $assigneeId === '0' || $assigneeId === 0 || empty($assigneeId)) {
                $assigneeId = null;
            } else {
                $assigneeId = (int)$assigneeId;
            }
            
            if ($assigneeId !== null) {
                $members = $this->getProjectMembers($projectId);
                $memberIds = array_map(function($m) { return $m['id']; }, $members);
                if (!in_array($assigneeId, $memberIds)) {
                    $error = "Можеш да назначиш задача само на участник в проекта!";
                    $project = $this->projectModel->getById($projectId);
                    $statuses = $this->statusModel->getAll();
                    $members = $this->getProjectMembers($projectId);
                    $this->view("task/create", [
                        'project' => $project,
                        'statuses' => $statuses,
                        'members' => $members,
                        'error' => $error
                    ]);
                    return;
                }
            }
            
            if (empty($startDate)) {
                $startDate = null;
            }
            
            if (empty($dueDate)) {
                $dueDate = null;
            }
            
            // Валидация
            if (empty($title) || empty($statusId)) {
                $error = "Заглавието и статусът са задължителни!";
                $project = $this->projectModel->getById($projectId);
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            // Валидация на дати
            if ($startDate && $dueDate && $dueDate < $startDate) {
                $error = "Крайната дата не може да е преди началната дата!";
                $project = $this->projectModel->getById($projectId);
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            // Проверка за достъп
            $project = $this->projectModel->getById($projectId);
            $userId = Session::get('user_id');
            
            if (!$project) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            if (!$this->canAccessProject($projectId, $userId)) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            // Валидация на priority
            if ($priority !== null && ($priority < 1 || $priority > 4)) {
                $error = "Приоритетът трябва да е между 1 и 4!";
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            if (empty($statusId) || $statusId == 0) {
                $error = "Моля изберете статус!";
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            error_log("DEBUG: Creating task - projectId=$projectId, statusId=$statusId, title=" . substr($title, 0, 30));
            
            $id = $this->taskModel->create($projectId, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority, $userId);
            
            if ($id) {
                header('Location: index.php?url=task/index&project_id=' . $projectId);
                exit;
            } else {
                $error = "Грешка при създаване на задачата! Проверете error_log файла в XAMPP за детайли. Уверете се че статусът е избран и проектът съществува.";
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($projectId);
                $this->view("task/create", [
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
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
        
        if (!$project) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        if (!$this->canAccessProject($task['project_id'], $userId)) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        // Проверка: назначеният на задачата НЕ може да я редактира (освен ако е собственик или админ)
        if (!$this->isAdmin() && $task['assignee_id'] == $userId && $project['owner_id'] != $userId) {
            $_SESSION['error'] = "Не можете да редактирате задача, която е назначена на вас!";
            header('Location: index.php?url=task/show&id=' . $id);
            exit;
        }
        
        $statuses = $this->statusModel->getAll();
        $members = $this->getProjectMembers($task['project_id']);
        
        $this->view("task/edit", [
            'task' => $task,
            'project' => $project,
            'statuses' => $statuses,
            'members' => $members
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
            if ($assigneeId === '' || $assigneeId === '0' || $assigneeId === 0 || empty($assigneeId)) {
                $assigneeId = null;
            } else {
                $assigneeId = (int)$assigneeId;
            }
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
            
            // Проверка: назначеният на задачата НЕ може да я редактира (освен ако е собственик или админ)
            if (!$this->isAdmin() && $task['assignee_id'] == $userId && $project['owner_id'] != $userId) {
                $_SESSION['error'] = "Не можете да редактирате задача, която е назначена на вас!";
                header('Location: index.php?url=task/show&id=' . $id);
                exit;
            }
            
            // Валидация
            if (empty($title) || empty($statusId)) {
                $error = "Заглавието и статусът са задължителни!";
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            // Валидация на дати
            if ($startDate && $dueDate && $dueDate < $startDate) {
                $error = "Крайната дата не може да е преди началната дата!";
                $statuses = $this->statusModel->getAll();
                $members = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
                    'error' => $error
                ]);
                return;
            }
            
            if (!$this->statusModel->isValidTransition($task['status_id'], $statusId)) {
                $error = "Невалиден преход на статус!";
                $statuses = $this->statusModel->getAll();
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
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
                $members = $this->getProjectMembers($task['project_id']);
                $this->view("task/edit", [
                    'task' => $task,
                    'project' => $project,
                    'statuses' => $statuses,
                    'members' => $members,
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
        
        if (!$project) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        if (!$this->canAccessProject($task['project_id'], $userId)) {
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
        
        if (!$project) {
            header('Location: index.php?url=task/index');
            exit;
        }
        
        if (!$this->canAccessProject($task['project_id'], $userId)) {
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
    
    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = (int)($_POST['id'] ?? 0);
            $task = $this->taskModel->getById($id);
            
            if (!$task) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $userId = Session::get('user_id');
            if (!$this->canAccessProject($task['project_id'], $userId)) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            if ($this->taskModel->completeTask($id, $userId)) {
                $_SESSION['success'] = "Задачата е завършена успешно!";
            } else {
                $_SESSION['error'] = "Грешка при завършване на задачата!";
            }
            
            $redirectUrl = $_POST['redirect'] ?? 'index.php?url=task/index&project_id=' . $task['project_id'];
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        header('Location: index.php?url=task/index');
        exit;
    }
    
    // Helper методи
    private function isProjectMember($projectId, $userId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        return $projectMemberModel->isMember($projectId, $userId);
    }
    
    private function isAdmin() {
        return Session::get('user_role') === 'admin';
    }
    
    private function canAccessProject($projectId, $userId) {
        if ($this->isAdmin()) {
            return true;
        }
        
        $project = $this->projectModel->getById($projectId);
        if (!$project) {
            return false;
        }
        
        if ($project['owner_id'] == $userId) {
            return true;
        }
        
        return $this->isProjectMember($projectId, $userId);
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

