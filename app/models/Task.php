<?php

require_once "../app/core/Model.php";

class Task extends Model {
    
    public function create($projectId, $title, $description, $statusId, $assigneeId, $startDate, $dueDate, $priority, $userId = null, $sprintId = null) {
        if ($startDate && $dueDate && $dueDate < $startDate) {
            return false;
        }
        
        if ($priority !== null && ($priority < 1 || $priority > 4)) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO tasks (project_id, sprint_id, title, description, status_id, assignee_id, created_by, start_date, due_date, priority) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $params = [
                $projectId, 
                $sprintId ?: null, 
                $title, 
                empty($description) ? null : $description, 
                $statusId, 
                $assigneeId, 
                $userId,
                empty($startDate) ? null : $startDate, 
                empty($dueDate) ? null : $dueDate, 
                $priority
            ];
            
            $stmt->execute($params);
            $taskId = $this->db->lastInsertId();
            
            if ($userId) {
                try {
                    $auditSql = "INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'create', 'task', ?)";
                    $auditStmt = $this->db->prepare($auditSql);
                    $auditStmt->execute([$userId, $taskId]);
                } catch (PDOException $e) {
                    error_log("Audit log error (non-critical): " . $e->getMessage());
                }
            }
            
            $this->db->commit();
            return $taskId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $errorMsg = $e->getMessage();
            error_log("Task creation error: " . $errorMsg);
            error_log("Task data: projectId=$projectId, statusId=$statusId, assigneeId=" . ($assigneeId ?? 'NULL') . ", priority=" . ($priority ?? 'NULL') . ", title=" . substr($title, 0, 50));
            return false;
        }
    }
    
    public function getByProject($projectId) {
        $sql = "SELECT t.*, s.name as status_name, s.order_index as status_order, 
                u.first_name, u.last_name,
                creator.first_name as creator_first_name, creator.last_name as creator_last_name
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN users u ON t.assignee_id = u.id
                LEFT JOIN users creator ON t.created_by = creator.id
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
        $sql = "SELECT t.*, s.name as status_name, p.name as project_name, 
                u.first_name, u.last_name,
                creator.first_name as creator_first_name, creator.last_name as creator_last_name
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                LEFT JOIN users creator ON t.created_by = creator.id
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
    
    public function search($query, $projectId = null, $statusId = null, $assigneeId = null, $userId = null, $isAdmin = false) {
        $sql = "SELECT DISTINCT t.*, s.name as status_name, p.name as project_name, 
                u.first_name, u.last_name,
                creator.first_name as creator_first_name, creator.last_name as creator_last_name
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                LEFT JOIN users creator ON t.created_by = creator.id";
        
        $params = ["%$query%", "%$query%"];
        $whereConditions = ["(t.title LIKE ? OR t.description LIKE ?)"];
        
        // Филтриране по права на достъп (ако не е админ)
        if (!$isAdmin && $userId) {
            $sql .= " LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?";
            $params[] = $userId;
            $whereConditions[] = "(p.owner_id = ? OR pm.user_id IS NOT NULL)";
            $params[] = $userId;
        }
        
        if ($projectId) {
            $whereConditions[] = "t.project_id = ?";
            $params[] = $projectId;
        }
        
        if ($statusId) {
            $whereConditions[] = "t.status_id = ?";
            $params[] = $statusId;
        }
        
        if ($assigneeId) {
            $whereConditions[] = "t.assignee_id = ?";
            $params[] = $assigneeId;
        }
        
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
        $sql .= " ORDER BY t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getPaginated($projectId = null, $page = 1, $perPage = 10, $statusId = null, $userId = null, $isAdmin = false) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT DISTINCT t.*, s.name as status_name, p.name as project_name, 
                u.first_name, u.last_name,
                creator.first_name as creator_first_name, creator.last_name as creator_last_name
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN users u ON t.assignee_id = u.id
                LEFT JOIN users creator ON t.created_by = creator.id";
        
        $params = [];
        $whereConditions = [];
        
        // Филтриране по права на достъп (ако не е админ)
        if (!$isAdmin && $userId) {
            $sql .= " LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?";
            $params[] = $userId;
            $whereConditions[] = "(p.owner_id = ? OR pm.user_id IS NOT NULL)";
            $params[] = $userId;
        }
        
        if ($projectId) {
            $whereConditions[] = "t.project_id = ?";
            $params[] = $projectId;
        }
        
        if ($statusId) {
            $whereConditions[] = "t.status_id = ?";
            $params[] = $statusId;
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
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
    
    public function getCount($projectId = null, $statusId = null, $userId = null, $isAdmin = false) {
        $sql = "SELECT COUNT(DISTINCT t.id) as total 
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id";
        $params = [];
        $whereConditions = [];
        
        // Филтриране по права на достъп (ако не е админ)
        if (!$isAdmin && $userId) {
            $sql .= " LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?";
            $params[] = $userId;
            $whereConditions[] = "(p.owner_id = ? OR pm.user_id IS NOT NULL)";
            $params[] = $userId;
        }
        
        if ($projectId) {
            $whereConditions[] = "t.project_id = ?";
            $params[] = $projectId;
        }
        
        if ($statusId) {
            $whereConditions[] = "t.status_id = ?";
            $params[] = $statusId;
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
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
    
    public function areAllTasksDone($projectId) {
        $sql = "SELECT COUNT(*) as total, 
                       SUM(CASE WHEN s.name = 'Done' THEN 1 ELSE 0 END) as done_count
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                WHERE t.project_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            $result = $stmt->fetch();
            
            $total = (int)($result['total'] ?? 0);
            $doneCount = (int)($result['done_count'] ?? 0);
            
            return $total > 0 && $total == $doneCount;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function completeTask($id, $userId = null) {
        require_once "../app/models/Status.php";
        $statusModel = new Status();
        $doneStatus = $statusModel->getByName('Done');
        
        if (!$doneStatus) {
            return false;
        }
        
        $sql = "UPDATE tasks SET status_id = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$doneStatus['id'], $id]);
            
            if ($result && $userId) {
                try {
                    $auditSql = "INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'complete', 'task', ?)";
                    $auditStmt = $this->db->prepare($auditSql);
                    $auditStmt->execute([$userId, $id]);
                } catch (PDOException $e) {
                    error_log("Audit log error (non-critical): " . $e->getMessage());
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
}

