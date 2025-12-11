<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/core/CSRF.php";
require_once "../app/models/Comment.php";
require_once "../app/models/Task.php";
require_once "../app/models/Project.php";

class CommentController extends Controller {
    
    private $commentModel;
    private $taskModel;
    private $projectModel;
    
    public function __construct() {
        $this->commentModel = new Comment();
        $this->taskModel = new Task();
        $this->projectModel = new Project();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $taskId = $_POST['task_id'] ?? 0;
            $text = $_POST['text'] ?? '';
            $authorId = Session::get('user_id');
            
            if (empty($text)) {
                header('Location: index.php?url=task/show&id=' . $taskId . '&error=empty_comment');
                exit;
            }
            
            $task = $this->taskModel->getById($taskId);
            if (!$task) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            $project = $this->projectModel->getById($task['project_id']);
            if (!$project || ($project['owner_id'] != $authorId && !$this->isProjectMember($task['project_id'], $authorId))) {
                header('Location: index.php?url=task/index');
                exit;
            }
            
            if ($this->commentModel->create($taskId, $authorId, $text)) {
                header('Location: index.php?url=task/show&id=' . $taskId);
                exit;
            } else {
                header('Location: index.php?url=task/show&id=' . $taskId . '&error=comment_error');
                exit;
            }
        } else {
            header('Location: index.php?url=task/index');
            exit;
        }
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            
            $id = $_POST['id'] ?? 0;
            $text = $_POST['text'] ?? '';
            $taskId = $_POST['task_id'] ?? 0;
            $userId = Session::get('user_id');
            
            $comment = $this->commentModel->getById($id);
            if (!$comment || $comment['author_id'] != $userId) {
                header('Location: index.php?url=task/show&id=' . $taskId);
                exit;
            }
            
            if (empty($text)) {
                header('Location: index.php?url=task/show&id=' . $taskId . '&error=empty_comment');
                exit;
            }
            
            if ($this->commentModel->update($id, $text)) {
                header('Location: index.php?url=task/show&id=' . $taskId);
                exit;
            } else {
                header('Location: index.php?url=task/show&id=' . $taskId . '&error=update_error');
                exit;
            }
        } else {
            header('Location: index.php?url=task/index');
            exit;
        }
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $taskId = $_GET['task_id'] ?? 0;
        $userId = Session::get('user_id');
        
        $comment = $this->commentModel->getById($id);
        if (!$comment || ($comment['author_id'] != $userId && Session::get('user_role') != 'admin')) {
            header('Location: index.php?url=task/show&id=' . $taskId);
            exit;
        }
        
        if ($this->commentModel->delete($id)) {
            header('Location: index.php?url=task/show&id=' . $taskId);
            exit;
        } else {
            header('Location: index.php?url=task/show&id=' . $taskId . '&error=delete_error');
            exit;
        }
    }
    
    private function isProjectMember($projectId, $userId) {
        require_once "../app/models/ProjectMember.php";
        $projectMemberModel = new ProjectMember();
        return $projectMemberModel->isMember($projectId, $userId);
    }
}

