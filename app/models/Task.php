<?php

require_once "../app/core/Model.php";

class Task extends Model {
    
    public function create($projectId, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority, $userId = null, $sprintId = null) {
        if ($startDate && $dueDate && $dueDate < $startDate) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO tasks (project_id, sprint_id, title, description, status_id, assignee_id, start_date, due_date, priority) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $sprintId, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority]);
            $taskId = $this->db->lastInsertId();
            
            if ($userId) {
                $auditSql = "INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'create', 'task', ?)";
                $auditStmt = $this->db->prepare($auditSql);
                $auditStmt->execute([$userId, $taskId]);
            }
            
            $this->db->commit();
            return $taskId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getByProject($projectId) {
        $sql = "SELECT t.*, s.name as status_name, s.order_index as status_order, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE t.project_id = ? 
                ORDER BY s.order_index ASC, t.priority ASC, t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getByAssignee($userId) {
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.assignee_id = ? 
                ORDER BY t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getByStatus($statusId) {
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE t.status_id = ? 
                ORDER BY t.due_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$statusId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE t.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority) {
        if ($startDate && $dueDate && $dueDate < $startDate) {
            return false;
        }
        
        $sql = "UPDATE tasks SET title = ?, description = ?, status_id = ?, assignee_id = ?, 
                start_date = ?, due_date = ?, priority = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateStatus($id, $statusId) {
        $sql = "UPDATE tasks SET status_id = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$statusId, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id, $userId = null) {
        try {
            $this->db->beginTransaction();
            
            $deleteLabelsSql = "DELETE FROM task_labels WHERE task_id = ?";
            $deleteLabelsStmt = $this->db->prepare($deleteLabelsSql);
            $deleteLabelsStmt->execute([$id]);
            
            $deleteCommentsSql = "DELETE FROM comments WHERE task_id = ?";
            $deleteCommentsStmt = $this->db->prepare($deleteCommentsSql);
            $deleteCommentsStmt->execute([$id]);
            
            if ($userId) {
                $auditSql = "INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'delete', 'task', ?)";
                $auditStmt = $this->db->prepare($auditSql);
                $auditStmt->execute([$userId, $id]);
            }
            
            $sql = "DELETE FROM tasks WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function search($query, $projectId = null, $statusId = null, $assigneeId = null) {
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE (t.title LIKE ? OR t.description LIKE ?)";
        
        $params = ["%$query%", "%$query%"];
        
        if ($projectId) {
            $sql .= " AND t.project_id = ?";
            $params[] = $projectId;
        }
        
        if ($statusId) {
            $sql .= " AND t.status_id = ?";
            $params[] = $statusId;
        }
        
        if ($assigneeId) {
            $sql .= " AND t.assignee_id = ?";
            $params[] = $assigneeId;
        }
        
        $sql .= " ORDER BY t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getPaginated($projectId = null, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id";
        
        $params = [];
        
        if ($projectId) {
            $sql .= " WHERE t.project_id = ?";
            $params[] = $projectId;
        }
        
        $sql .= " ORDER BY t.id DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getCount($projectId = null) {
        $sql = "SELECT COUNT(*) as total FROM tasks";
        $params = [];
        
        if ($projectId) {
            $sql .= " WHERE project_id = ?";
            $params[] = $projectId;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getOverdue($projectId = null) {
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE t.due_date < CURDATE() AND t.status_id != (SELECT id FROM statuses WHERE name = 'Done' LIMIT 1)";
        
        if ($projectId) {
            $sql .= " AND t.project_id = ?";
        }
        
        $sql .= " ORDER BY t.due_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            if ($projectId) {
                $stmt->execute([$projectId]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

