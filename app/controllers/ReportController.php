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
            'overdue_tasks_by_project' => $this->reportModel->overdueTasksByProject(),
            'throughput_by_stage' => $this->reportModel->throughputByStage(),
            'avg_time_in_status' => $this->reportModel->avgTimeInStatus(),
            'projects_with_most_tasks' => $this->reportModel->projectsWithMostTasks(10),
            'users_with_most_tasks' => $this->reportModel->usersWithMostTasks(10),
            'tasks_by_priority' => $this->reportModel->tasksByPriority(),
            'tasks_by_sprint' => $this->reportModel->tasksBySprint(),
            'overall_stats' => $this->reportModel->overallStats()
        ];
        
        $this->view("report/index", ['reports' => $reports]);
    }
    
    public function export() {
        require_once "../app/core/PDFReport.php";
        
        $reports = [
            'tasks_by_status' => $this->reportModel->tasksByStatus(),
            'tasks_by_project' => $this->reportModel->tasksByProject(),
            'workload_by_person' => $this->reportModel->workloadByPerson(),
            'overdue_tasks' => $this->reportModel->overdueTasks(),
            'overdue_tasks_by_project' => $this->reportModel->overdueTasksByProject(),
            'throughput_by_stage' => $this->reportModel->throughputByStage(),
            'avg_time_in_status' => $this->reportModel->avgTimeInStatus(),
            'projects_with_most_tasks' => $this->reportModel->projectsWithMostTasks(10),
            'users_with_most_tasks' => $this->reportModel->usersWithMostTasks(10),
            'tasks_by_priority' => $this->reportModel->tasksByPriority(),
            'tasks_by_sprint' => $this->reportModel->tasksBySprint(),
            'overall_stats' => $this->reportModel->overallStats()
        ];
        
        $pdf = new PDFReport(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Projify');
        $pdf->SetAuthor('Projify');
        $pdf->SetTitle('Справки - Projify');
        $pdf->SetSubject('Справки');
        $pdf->SetKeywords('справки, отчети, проекти, задачи');
        
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        
        if (isset($reports['overall_stats']) && $reports['overall_stats']) {
            $stats = $reports['overall_stats'];
            $pdf->addSection('Обща статистика');
            $pdf->Cell(0, 6, 'Проекти: ' . $stats['total_projects'], 0, 1);
            $pdf->Cell(0, 6, 'Задачи: ' . $stats['total_tasks'], 0, 1);
            $pdf->Cell(0, 6, 'Потребители: ' . $stats['total_users'], 0, 1);
            $pdf->Cell(0, 6, 'Просрочени задачи: ' . $stats['overdue_tasks'], 0, 1);
            $pdf->Cell(0, 6, 'Завършени задачи: ' . $stats['completed_tasks'], 0, 1);
            $pdf->Cell(0, 6, 'Средна продължителност: ' . round($stats['avg_task_duration'] ?? 0, 1) . ' дни', 0, 1);
            $pdf->Ln(5);
        }
        
        $pdf->addSection('Задачи по статус');
        $headers = ['Статус', 'Брой задачи'];
        $data = [];
        foreach ($reports['tasks_by_status'] as $row) {
            $data[] = [$row['status_name'] ?? 'N/A', $row['task_count']];
        }
        $pdf->addTable($headers, $data, [95, 95]);
        
        $pdf->addSection('Задачи по проект');
        $headers = ['Проект', 'Брой задачи'];
        $data = [];
        foreach ($reports['tasks_by_project'] as $row) {
            $data[] = [$row['project_name'] ?? 'N/A', $row['task_count']];
        }
        $pdf->addTable($headers, $data, [95, 95]);
        
        $pdf->addSection('Натовареност по човек');
        $headers = ['Потребител', 'Брой задачи'];
        $data = [];
        foreach ($reports['workload_by_person'] as $row) {
            $name = ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '');
            $data[] = [$name ?: 'N/A', $row['task_count']];
        }
        $pdf->addTable($headers, $data, [95, 95]);
        
        if (!empty($reports['overdue_tasks'])) {
            $pdf->addSection('Просрочени задачи');
            $headers = ['Заглавие', 'Проект', 'Назначен', 'Крайна дата', 'Статус'];
            $data = [];
            foreach ($reports['overdue_tasks'] as $task) {
                $name = ($task['first_name'] ?? '') . ' ' . ($task['last_name'] ?? '');
                $dueDate = $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : 'N/A';
                $data[] = [
                    $task['title'] ?? '',
                    $task['project_name'] ?? 'N/A',
                    $name ?: 'N/A',
                    $dueDate,
                    $task['status_name'] ?? 'N/A'
                ];
            }
            $pdf->addTable($headers, $data, [38, 38, 38, 38, 38]);
        }
        
        $pdf->addSection('Задачи по приоритет');
        $headers = ['Приоритет', 'Брой задачи', 'Средна продължителност (дни)'];
        $data = [];
        foreach ($reports['tasks_by_priority'] as $row) {
            $data[] = [
                $row['priority_name'] ?? 'N/A',
                $row['task_count'],
                round($row['avg_duration'] ?? 0, 1)
            ];
        }
        $pdf->addTable($headers, $data, [63, 63, 64]);
        
        $pdf->addSection('Throughput по етапи');
        $headers = ['Статус', 'Брой задачи', 'Средна продължителност (дни)'];
        $data = [];
        foreach ($reports['throughput_by_stage'] as $row) {
            $data[] = [
                $row['status_name'] ?? 'N/A',
                $row['task_count'],
                round($row['avg_duration'] ?? 0, 1)
            ];
        }
        $pdf->addTable($headers, $data, [63, 63, 64]);
        
        $pdf->addSection('Проекти с най-много задачи');
        $headers = ['Проект', 'Общо задачи', 'Завършени'];
        $data = [];
        foreach ($reports['projects_with_most_tasks'] as $row) {
            $data[] = [
                $row['project_name'] ?? 'N/A',
                $row['task_count'],
                $row['completed_tasks']
            ];
        }
        $pdf->addTable($headers, $data, [63, 63, 64]);
        
        $pdf->addSection('Потребители с най-много задачи');
        $headers = ['Потребител', 'Email', 'Общо задачи', 'Завършени'];
        $data = [];
        foreach ($reports['users_with_most_tasks'] as $row) {
            $name = ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '');
            $data[] = [
                $name ?: 'N/A',
                $row['email'] ?? '',
                $row['task_count'],
                $row['completed_tasks']
            ];
        }
        $pdf->addTable($headers, $data, [47, 47, 48, 48]);

        if (!empty($reports['overdue_tasks_by_project'])) {
            $pdf->addSection('Просрочени задачи по проекти');
            $headers = ['Проект', 'Брой просрочени', 'Най-ранна крайна дата', 'Най-късна крайна дата'];
            $data = [];
            foreach ($reports['overdue_tasks_by_project'] as $row) {
                $earliest = $row['earliest_due_date'] ? date('d.m.Y', strtotime($row['earliest_due_date'])) : 'N/A';
                $latest = $row['latest_due_date'] ? date('d.m.Y', strtotime($row['latest_due_date'])) : 'N/A';
                $data[] = [
                    $row['project_name'] ?? 'N/A',
                    $row['overdue_count'],
                    $earliest,
                    $latest
                ];
            }
            $pdf->addTable($headers, $data, [48, 48, 47, 47]);
        }

        if (!empty($reports['tasks_by_sprint'])) {
            $pdf->addSection('Задачи по спринтове');
            $headers = ['Спринт', 'Проект', 'Брой задачи', 'Начална дата', 'Крайна дата'];
            $data = [];
            foreach ($reports['tasks_by_sprint'] as $row) {
                $start = $row['start_date'] ? date('d.m.Y', strtotime($row['start_date'])) : 'N/A';
                $end = $row['end_date'] ? date('d.m.Y', strtotime($row['end_date'])) : 'N/A';
                $data[] = [
                    $row['sprint_name'] ?? 'N/A',
                    $row['project_name'] ?? 'N/A',
                    $row['task_count'],
                    $start,
                    $end
                ];
            }
            $pdf->addTable($headers, $data, [38, 38, 28, 38, 38]);
        }
        
        $filename = 'reports_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
}

