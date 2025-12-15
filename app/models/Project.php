<?php

require_once "../app/core/Model.php";

class Project extends Model {
    
    public function create($name, $ownerId) {
        $checkSql = "SELECT id FROM projects WHERE name = ? AND owner_id = ?";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([$name, $ownerId]);
        if ($checkStmt->fetch()) {
            return false;
        }
        
        $sql = "INSERT INTO projects (name, owner_id) VALUES (?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name, $ownerId]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAll() {
        $sql = "SELECT * FROM projects ORDER BY id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getByOwner($ownerId) {
        $sql = "SELECT * FROM projects WHERE owner_id = ? ORDER BY id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getPaginatedByOwner($ownerId, $page = 1, $perPage = 9) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM projects WHERE owner_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ownerId, $perPage, $offset]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getCountByOwner($ownerId) {
        $sql = "SELECT COUNT(*) as total FROM projects WHERE owner_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ownerId]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM projects WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $name) {
        $project = $this->getById($id);
        if (!$project) {
            return false;
        }
        
        $checkSql = "SELECT id FROM projects WHERE name = ? AND owner_id = ? AND id != ?";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([$name, $project['owner_id'], $id]);
        if ($checkStmt->fetch()) {
            return false;
        }
        
        $sql = "UPDATE projects SET name = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id) {
        try {
            // ВАЖНО: Изключваме foreign key проверките ПРЕДИ транзакцията
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $this->db->beginTransaction();
            
            // 1. Извличаме всички task IDs за този проект
            $getTasksSql = "SELECT id FROM tasks WHERE project_id = ?";
            $getTasksStmt = $this->db->prepare($getTasksSql);
            $getTasksStmt->execute([$id]);
            $taskIds = $getTasksStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 2. Изтриваме task_labels за всички задачи
            if (!empty($taskIds)) {
                foreach ($taskIds as $taskId) {
                    $deleteTaskLabelsSql = "DELETE FROM task_labels WHERE task_id = ?";
                    $deleteTaskLabelsStmt = $this->db->prepare($deleteTaskLabelsSql);
                    $deleteTaskLabelsStmt->execute([$taskId]);
                }
            }

            // 3. Изтриваме comments за всички задачи
            if (!empty($taskIds)) {
                foreach ($taskIds as $taskId) {
                    $deleteCommentsSql = "DELETE FROM comments WHERE task_id = ?";
                    $deleteCommentsStmt = $this->db->prepare($deleteCommentsSql);
                    $deleteCommentsStmt->execute([$taskId]);
                }
            }

            // 4. Изтриваме audit_logs за задачите
            if (!empty($taskIds)) {
                foreach ($taskIds as $taskId) {
                    try {
                        $deleteTaskAuditSql = "DELETE FROM audit_logs WHERE entity = 'task' AND entity_id = ?";
                        $deleteTaskAuditStmt = $this->db->prepare($deleteTaskAuditSql);
                        $deleteTaskAuditStmt->execute([$taskId]);
                    } catch (PDOException $e) {
                        error_log("Warning: Could not delete audit log for task $taskId: " . $e->getMessage());
                    }
                }
            }

            // 5. Изтриваме задачите към проекта
            $deleteTasksSql = "DELETE FROM tasks WHERE project_id = ?";
            $deleteTasksStmt = $this->db->prepare($deleteTasksSql);
            $deleteTasksStmt->execute([$id]);

            // 6. Изтриваме спринтовете към проекта (ако таблицата съществува)
            try {
                $deleteSprintsSql = "DELETE FROM sprints WHERE project_id = ?";
                $deleteSprintsStmt = $this->db->prepare($deleteSprintsSql);
                $deleteSprintsStmt->execute([$id]);
            } catch (PDOException $e) {
                // Ако таблицата не съществува, просто пропускаме
                if (strpos($e->getMessage(), "doesn't exist") === false) {
                    throw $e;
                }
            }

            // 7. Премахваме членствата в проекта
            $deleteMembersSql = "DELETE FROM project_members WHERE project_id = ?";
            $deleteMembersStmt = $this->db->prepare($deleteMembersSql);
            $deleteMembersStmt->execute([$id]);

            // 8. Изтриваме audit_logs за проекта
            try {
                $deleteProjectAuditSql = "DELETE FROM audit_logs WHERE entity = 'project' AND entity_id = ?";
                $deleteProjectAuditStmt = $this->db->prepare($deleteProjectAuditSql);
                $deleteProjectAuditStmt->execute([$id]);
            } catch (PDOException $e) {
                error_log("Warning: Could not delete audit logs for project: " . $e->getMessage());
            }

            // 9. Накрая изтриваме самия проект
            $sql = "DELETE FROM projects WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if (!$result) {
                throw new PDOException("Failed to delete project with id: $id");
            }

            $this->db->commit();
            
            // Включваме отново foreign key проверките
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $errorMsg = "Error deleting project $id: " . $e->getMessage();
            $errorMsg .= " | SQL State: " . $e->getCode();
            $errorMsg .= " | Error Code: " . ($e->errorInfo[1] ?? 'N/A');
            $errorMsg .= " | Error Info: " . json_encode($e->errorInfo ?? []);
            error_log($errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $errorMsg = "Unexpected error deleting project $id: " . $e->getMessage();
            error_log($errorMsg);
            return false;
        }
    }
    
    public function canBeCompleted($id) {
        require_once "../app/models/Task.php";
        $taskModel = new Task();
        return $taskModel->areAllTasksDone($id);
    }
    
    public function complete($id) {
        $project = $this->getById($id);
        if (!$project) {
            return false;
        }
        
        if ($project['status'] === 'completed') {
            return true;
        }
        
        if (!$this->canBeCompleted($id)) {
            return false;
        }
        
        $sql = "UPDATE projects SET status = 'completed' WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error completing project $id: " . $e->getMessage());
            return false;
        }
    }
    
    public function reopen($id) {
        $sql = "UPDATE projects SET status = 'active' WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error reopening project $id: " . $e->getMessage());
            return false;
        }
    }
}
