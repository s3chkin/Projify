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
    
    public function exportUser() {
        require_once "../app/core/PDFReport.php";
        require_once "../app/models/User.php";
        require_once "../app/models/Project.php";
        require_once "../app/models/Task.php";
        
        $userId = (int)($_GET['user_id'] ?? 0);
        
        if (!$userId) {
            header('Location: index.php?url=admin/index');
            exit;
        }
        
        $userModel = new User();
        $user = $userModel->getById($userId);
        
        if (!$user) {
            header('Location: index.php?url=admin/index');
            exit;
        }
        
        $projects = $userModel->getProjectsByUser($userId);
        $tasks = $userModel->getTasksByUser($userId);
        
        $userReports = [
            'tasks_by_status' => $this->getUserTasksByStatus($userId),
            'tasks_by_project' => $this->getUserTasksByProject($userId),
            'overdue_tasks' => $this->getUserOverdueTasks($userId),
            'workload_stats' => $this->getUserWorkloadStats($userId)
        ];
        
        $pdf = new PDFReport(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Projify');
        $pdf->SetAuthor('Projify');
        $pdf->SetTitle('Справка за потребител - ' . $user['first_name'] . ' ' . $user['last_name']);
        $pdf->SetSubject('Справка за потребител');
        
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        
        $pdf->addSection('Информация за потребителя');
        $pdf->Cell(0, 6, 'Име: ' . $user['first_name'] . ' ' . $user['last_name'], 0, 1);
        $pdf->Cell(0, 6, 'Email: ' . $user['email'], 0, 1);
        $pdf->Cell(0, 6, 'Роля: ' . ucfirst($user['role']), 0, 1);
        $pdf->Ln(5);
        
        if (isset($userReports['workload_stats'])) {
            $stats = $userReports['workload_stats'];
            $pdf->addSection('Статистики за натовареност');
            $pdf->Cell(0, 6, 'Общо задачи: ' . ($stats['total_tasks'] ?? 0), 0, 1);
            $pdf->Cell(0, 6, 'Завършени задачи: ' . ($stats['completed_tasks'] ?? 0), 0, 1);
            $pdf->Cell(0, 6, 'Просрочени задачи: ' . ($stats['overdue_tasks'] ?? 0), 0, 1);
            $pdf->Cell(0, 6, 'Задачи с крайна дата днес: ' . ($stats['due_today'] ?? 0), 0, 1);
            $pdf->Ln(5);
        }
        
        $pdf->addSection('Проекти (' . count($projects) . ')');
        if (!empty($projects)) {
            $headers = ['Име на проект', 'Роля'];
            $data = [];
            foreach ($projects as $project) {
                $data[] = [
                    $project['name'],
                    ($project['role_type'] ?? 'member') === 'owner' ? 'Собственик' : 'Член'
                ];
            }
            $pdf->addTable($headers, $data, [95, 95]);
        } else {
            $pdf->Cell(0, 6, 'Няма проекти', 0, 1);
        }
        $pdf->Ln(5);
        
        if (!empty($userReports['tasks_by_status'])) {
            $pdf->addSection('Задачи по статус');
            $headers = ['Статус', 'Брой задачи'];
            $data = [];
            foreach ($userReports['tasks_by_status'] as $row) {
                $data[] = [$row['status_name'] ?? 'N/A', $row['task_count']];
            }
            $pdf->addTable($headers, $data, [95, 95]);
            $pdf->Ln(5);
        }
        
        if (!empty($userReports['tasks_by_project'])) {
            $pdf->addSection('Задачи по проект');
            $headers = ['Проект', 'Брой задачи'];
            $data = [];
            foreach ($userReports['tasks_by_project'] as $row) {
                $data[] = [$row['project_name'] ?? 'N/A', $row['task_count']];
            }
            $pdf->addTable($headers, $data, [95, 95]);
            $pdf->Ln(5);
        }
        
        if (!empty($userReports['overdue_tasks'])) {
            $pdf->addSection('Просрочени задачи (' . count($userReports['overdue_tasks']) . ')');
            $headers = ['Заглавие', 'Проект', 'Крайна дата'];
            $data = [];
            foreach ($userReports['overdue_tasks'] as $task) {
                $dueDate = $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : 'N/A';
                $data[] = [
                    $task['title'] ?? '',
                    $task['project_name'] ?? 'N/A',
                    $dueDate
                ];
            }
            $pdf->addTable($headers, $data, [63, 63, 64]);
        }
        
        $filename = 'user_report_' . $user['id'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    private function getUserTasksByStatus($userId) {
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
            $stmt = $this->reportModel->db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function getUserTasksByProject($userId) {
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
            $stmt = $this->reportModel->db->prepare($sql);
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
        
        require_once "../app/models/User.php";
        $userModel = new User();
        $userProjects = $userModel->getProjectsByUser($userId);
        $userProjectIds = array_map(function($p) { return $p['id']; }, $userProjects);
        
        return array_filter($allOverdue, function($task) use ($userProjectIds) {
            return in_array($task['project_id'], $userProjectIds);
        });
    }
    
    private function getUserWorkloadStats($userId) {
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
            $stmt = $this->reportModel->db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['total_tasks' => 0, 'completed_tasks' => 0, 'overdue_tasks' => 0, 'due_today' => 0];
        }
    }
}

