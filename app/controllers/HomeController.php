<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Project.php";
require_once "../app/models/Task.php";
require_once "../app/models/Report.php";

class HomeController extends Controller {

    public function index() {
        Session::start();
        
        if (!Session::has('user_id')) {
            $this->view("home/index");
            return;
        }
        
        $userId = Session::get('user_id');
        $projectModel = new Project();
        $taskModel = new Task();
        $reportModel = new Report();
        
        $projects = $projectModel->getByOwner($userId);
        $myTasks = $taskModel->getByAssignee($userId);
        $overdueTasks = $taskModel->getOverdue();
        $myOverdueTasks = array_filter($overdueTasks, function($task) use ($userId) {
            return $task['assignee_id'] == $userId;
        });
        
        $today = date('Y-m-d');
        $todayTasks = array_filter($myTasks, function($task) use ($today) {
            return $task['due_date'] == $today && $task['status_name'] != 'Done';
        });
        
        $stats = [
            'total_projects' => count($projects),
            'total_tasks' => count($myTasks),
            'overdue_tasks' => count($myOverdueTasks),
            'today_tasks' => count($todayTasks),
            'completed_tasks' => count(array_filter($myTasks, function($task) {
                return $task['status_name'] == 'Done';
            }))
        ];
        
        $recentProjects = array_slice($projects, 0, 5);
        $recentTasks = array_slice($myTasks, 0, 5);
        
        $this->view("home/index", [
            'projects' => $recentProjects,
            'allProjects' => $projects,
            'tasks' => $recentTasks,
            'overdueTasks' => array_slice($myOverdueTasks, 0, 5),
            'todayTasks' => array_slice($todayTasks, 0, 5),
            'stats' => $stats
        ]);
    }
}
