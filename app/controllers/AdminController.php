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
        
        $usersWithCounts = [];
        foreach ($users as $user) {
            $projects = $this->userModel->getProjectsByUser($user['id']);
            $tasks = $this->userModel->getTasksByUser($user['id']);
            
            $usersWithCounts[] = [
                'user' => $user,
                'projects_count' => count($projects),
                'tasks_count' => count($tasks)
            ];
        }
        
        $this->view("admin/index", [
            'users' => $usersWithCounts
        ]);
    }
    
    public function user() {
        $userId = (int)($_GET['id'] ?? 0);
        
        if (!$userId) {
            header('Location: index.php?url=admin/index');
            exit;
        }
        
        $user = $this->userModel->getById($userId);
        if (!$user) {
            header('Location: index.php?url=admin/index');
            exit;
        }
        
        $projects = $this->userModel->getProjectsByUser($userId);
        $tasks = $this->userModel->getTasksByUser($userId);
        
        require_once "../app/models/Report.php";
        $reportModel = new Report();
        
        $userReports = [
            'tasks_by_status' => $this->getUserTasksByStatus($userId),
            'tasks_by_project' => $this->getUserTasksByProject($userId),
            'overdue_tasks' => $this->getUserOverdueTasks($userId),
            'completed_tasks' => $this->getUserCompletedTasks($userId),
            'workload_stats' => $this->getUserWorkloadStats($userId)
        ];
        
        $this->view("admin/user", [
            'user' => $user,
            'projects' => $projects,
            'tasks' => $tasks,
            'reports' => $userReports
        ]);
    }
    
    private function getUserTasksByStatus($userId) {
        require_once "../app/core/Database.php";
        $db = Database::getConnection();
        
        $sql = "SELECT s.name as status_name, COUNT(t.id) as task_count
                FROM statuses s
                LEFT JOIN tasks t ON s.id = t.status_id 
                    AND t.project_id IN (
                        SELECT p.id FROM projects p 
                        WHERE p.owner_id = ? 
                        UNION 
                        SELECT pm.project_id FROM project_members pm 
                        WHERE pm.user_id = ?
                    )
                GROUP BY s.id, s.name
                ORDER BY s.order_index";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function getUserTasksByProject($userId) {
        require_once "../app/core/Database.php";
        $db = Database::getConnection();
        
        $sql = "SELECT p.name as project_name, COUNT(t.id) as task_count
                FROM projects p
                INNER JOIN (
                    SELECT id FROM projects WHERE owner_id = ?
                    UNION
                    SELECT project_id as id FROM project_members WHERE user_id = ?
                ) user_projects ON p.id = user_projects.id
                LEFT JOIN tasks t ON p.id = t.project_id
                GROUP BY p.id, p.name
                ORDER BY task_count DESC";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function getUserOverdueTasks($userId) {
        require_once "../app/models/Task.php";
        $taskModel = new Task();
        $allOverdue = $taskModel->getOverdue();
        
        $userProjectIds = [];
        $userProjects = $this->userModel->getProjectsByUser($userId);
        foreach ($userProjects as $project) {
            $userProjectIds[] = $project['id'];
        }
        
        return array_filter($allOverdue, function($task) use ($userProjectIds) {
            return in_array($task['project_id'], $userProjectIds);
        });
    }
    
    private function getUserCompletedTasks($userId) {
        require_once "../app/core/Database.php";
        $db = Database::getConnection();
        
        $sql = "SELECT COUNT(*) as count
                FROM tasks t
                INNER JOIN statuses s ON t.status_id = s.id
                WHERE s.name = 'Done'
                AND t.project_id IN (
                    SELECT p.id FROM projects p WHERE p.owner_id = ?
                    UNION
                    SELECT pm.project_id FROM project_members pm WHERE pm.user_id = ?
                )";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getUserWorkloadStats($userId) {
        require_once "../app/core/Database.php";
        $db = Database::getConnection();
        
        $sql = "SELECT 
                    COUNT(t.id) as total_tasks,
                    COUNT(CASE WHEN s.name = 'Done' THEN 1 END) as completed_tasks,
                    COUNT(CASE WHEN t.due_date < CURDATE() AND s.name != 'Done' THEN 1 END) as overdue_tasks,
                    COUNT(CASE WHEN t.due_date = CURDATE() AND s.name != 'Done' THEN 1 END) as due_today
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                WHERE t.project_id IN (
                    SELECT p.id FROM projects p WHERE p.owner_id = ?
                    UNION
                    SELECT pm.project_id FROM project_members pm WHERE pm.user_id = ?
                )";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['total_tasks' => 0, 'completed_tasks' => 0, 'overdue_tasks' => 0, 'due_today' => 0];
        }
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

