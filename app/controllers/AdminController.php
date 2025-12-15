<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/User.php";
require_once "../app/models/Project.php";
require_once "../app/models/Task.php";

class AdminController extends Controller {
    
    private $userModel;
    private $projectModel;
    private $taskModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
        
        if (Session::get('user_role') !== 'admin') {
            header('Location: index.php?url=home/index');
            exit;
        }
    }
    
    public function index() {
        $users = $this->userModel->getAll();
        
        $usersWithDetails = [];
        foreach ($users as $user) {
            $projects = $this->userModel->getProjectsByUser($user['id']);
            $tasks = $this->userModel->getTasksByUser($user['id']);
            
            $usersWithDetails[] = [
                'user' => $user,
                'projects' => $projects,
                'tasks' => $tasks,
                'projects_count' => count($projects),
                'tasks_count' => count($tasks)
            ];
        }
        
        $this->view("admin/index", [
            'users' => $usersWithDetails
        ]);
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once "../app/core/CSRF.php";
            $this->validateCSRF();
            
            $userId = (int)($_POST['user_id'] ?? 0);
            $currentUserId = Session::get('user_id');
            
            if (!$userId) {
                header('Location: index.php?url=admin/index');
                exit;
            }
            
            if ($userId == $currentUserId) {
                $_SESSION['error'] = "Не можете да изтриете собствения си акаунт!";
                header('Location: index.php?url=admin/index');
                exit;
            }
            
            $user = $this->userModel->getById($userId);
            if (!$user) {
                $_SESSION['error'] = "Потребителят не съществува!";
                header('Location: index.php?url=admin/index');
                exit;
            }
            
            if ($user['role'] === 'admin') {
                $_SESSION['error'] = "Не можете да изтриете друг администратор!";
                header('Location: index.php?url=admin/index');
                exit;
            }
            
            require_once "../app/models/Project.php";
            require_once "../app/models/ProjectMember.php";
            require_once "../app/models/Task.php";
            
            $projectModel = new Project();
            $projectMemberModel = new ProjectMember();
            $taskModel = new Task();
            
            require_once "../app/core/Database.php";
            $db = Database::getConnection();
            
            try {
                $db->beginTransaction();
                
                $userProjects = $projectModel->getByOwner($userId);
                foreach ($userProjects as $project) {
                    $projectModel->delete($project['id']);
                }
                
                $allProjects = $projectModel->getAll();
                foreach ($allProjects as $project) {
                    $projectMemberModel->remove($project['id'], $userId);
                }
                
                $userTasks = $taskModel->getByAssignee($userId);
                foreach ($userTasks as $task) {
                    $taskModel->update($task['id'], $task['title'], $task['description'], $task['status_id'], null, $task['start_date'], $task['due_date'], $task['priority']);
                }
                
                $deleteSql = "DELETE FROM users WHERE id = ?";
                $stmt = $db->prepare($deleteSql);
                $stmt->execute([$userId]);
                
                $db->commit();
                $_SESSION['success'] = "Потребителят е изтрит успешно!";
            } catch (PDOException $e) {
                $db->rollBack();
                $_SESSION['error'] = "Грешка при изтриване на потребителя: " . $e->getMessage();
            }
        }
        
        header('Location: index.php?url=admin/index');
        exit;
    }
}

