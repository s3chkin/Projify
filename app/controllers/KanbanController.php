<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Task.php";
require_once "../app/models/Project.php";
require_once "../app/models/Status.php";

class KanbanController extends Controller {
    
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
        
        $statuses = $this->statusModel->getAll();
        $tasks = $this->taskModel->getByProject($projectId);
        $projects = $this->projectModel->getByOwner($userId);
        
        $tasksByStatus = [];
        foreach ($statuses as $status) {
            $tasksByStatus[$status['id']] = [
                'status' => $status,
                'tasks' => array_filter($tasks, function($task) use ($status) {
                    return $task['status_id'] == $status['id'];
                })
            ];
        }
        
        $this->view("kanban/index", [
            'project' => $project,
            'projects' => $projects,
            'statuses' => $statuses,
            'tasksByStatus' => $tasksByStatus
        ]);
    }
    
    private function isProjectMember($projectId, $userId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        return $projectMemberModel->isMember($projectId, $userId);
    }
}

