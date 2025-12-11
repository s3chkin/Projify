<?php

require_once "../app/core/Controller.php";
require_once "../app/core/Session.php";
require_once "../app/models/Report.php";

class ReportController extends Controller {
    
    private $reportModel;
    
    public function __construct() {
        $this->reportModel = new Report();
        Session::start();
        
        if (!Session::has('user_id')) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }
    
    public function index() {
        $reports = [
            'tasks_by_status' => $this->reportModel->tasksByStatus(),
            'tasks_by_project' => $this->reportModel->tasksByProject(),
            'workload_by_person' => $this->reportModel->workloadByPerson(),
            'overdue_tasks' => $this->reportModel->overdueTasks(),
            'throughput_by_stage' => $this->reportModel->throughputByStage(),
            'avg_time_in_status' => $this->reportModel->avgTimeInStatus(),
            'projects_with_most_tasks' => $this->reportModel->projectsWithMostTasks(10),
            'users_with_most_tasks' => $this->reportModel->usersWithMostTasks(10),
            'tasks_by_priority' => $this->reportModel->tasksByPriority(),
            'overall_stats' => $this->reportModel->overallStats()
        ];
        
        $this->view("report/index", ['reports' => $reports]);
    }
}

