<?php

require_once "../app/core/Model.php";

class Report extends Model {
    
    public function tasksByStatus() {
        $sql = "SELECT s.name as status_name, COUNT(t.id) as task_count
                FROM statuses s
                LEFT JOIN tasks t ON s.id = t.status_id
                GROUP BY s.id, s.name
                ORDER BY s.order_index";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function tasksByProject() {
        $sql = "SELECT p.name as project_name, COUNT(t.id) as task_count
                FROM projects p
                LEFT JOIN tasks t ON p.id = t.project_id
                GROUP BY p.id, p.name
                ORDER BY task_count DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function workloadByPerson() {
        $sql = "SELECT u.first_name, u.last_name, COUNT(t.id) as task_count
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assignee_id
                GROUP BY u.id, u.first_name, u.last_name
                ORDER BY task_count DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function overdueTasks() {
        $sql = "SELECT t.id, t.title, t.due_date, p.name as project_name, 
                u.first_name, u.last_name, s.name as status_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                LEFT JOIN statuses s ON t.status_id = s.id
                WHERE t.due_date < CURDATE() 
                AND s.name != 'Done'
                ORDER BY t.due_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function throughputByStage() {
        $sql = "SELECT s.name as status_name, 
                COUNT(t.id) as task_count,
                AVG(DATEDIFF(COALESCE(t.due_date, CURDATE()), COALESCE(t.start_date, CURDATE()))) as avg_duration
                FROM statuses s
                LEFT JOIN tasks t ON s.id = t.status_id
                GROUP BY s.id, s.name
                ORDER BY s.order_index";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function avgTimeInStatus() {
        $sql = "SELECT s.name as status_name,
                AVG(DATEDIFF(COALESCE(t.due_date, CURDATE()), COALESCE(t.start_date, CURDATE()))) as avg_days
                FROM statuses s
                LEFT JOIN tasks t ON s.id = t.status_id
                WHERE t.start_date IS NOT NULL
                GROUP BY s.id, s.name
                ORDER BY s.order_index";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function projectsWithMostTasks($limit = 10) {
        $sql = "SELECT p.name as project_name, 
                COUNT(t.id) as task_count,
                COUNT(CASE WHEN s.name = 'Done' THEN 1 END) as completed_tasks
                FROM projects p
                LEFT JOIN tasks t ON p.id = t.project_id
                LEFT JOIN statuses s ON t.status_id = s.id
                GROUP BY p.id, p.name
                ORDER BY task_count DESC
                LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function usersWithMostTasks($limit = 10) {
        $sql = "SELECT u.first_name, u.last_name, u.email,
                COUNT(t.id) as task_count,
                COUNT(CASE WHEN s.name = 'Done' THEN 1 END) as completed_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assignee_id
                LEFT JOIN statuses s ON t.status_id = s.id
                GROUP BY u.id, u.first_name, u.last_name, u.email
                ORDER BY task_count DESC
                LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function tasksByPriority() {
        $sql = "SELECT 
                CASE priority
                    WHEN 1 THEN 'Много висок'
                    WHEN 2 THEN 'Висок'
                    WHEN 3 THEN 'Среден'
                    WHEN 4 THEN 'Нисък'
                    ELSE 'Не зададен'
                END as priority_name,
                COUNT(id) as task_count,
                AVG(DATEDIFF(COALESCE(due_date, CURDATE()), COALESCE(start_date, CURDATE()))) as avg_duration
                FROM tasks
                GROUP BY priority
                ORDER BY priority ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function overallStats() {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM projects) as total_projects,
                (SELECT COUNT(*) FROM tasks) as total_tasks,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM tasks WHERE due_date < CURDATE() AND status_id != (SELECT id FROM statuses WHERE name = 'Done' LIMIT 1)) as overdue_tasks,
                (SELECT COUNT(*) FROM tasks WHERE status_id = (SELECT id FROM statuses WHERE name = 'Done' LIMIT 1)) as completed_tasks,
                (SELECT AVG(DATEDIFF(COALESCE(due_date, CURDATE()), COALESCE(start_date, CURDATE()))) FROM tasks WHERE start_date IS NOT NULL) as avg_task_duration";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}

