<?php

require_once "../app/core/Model.php";

class Sprint extends Model {
    
    public function create($projectId, $name, $startDate, $endDate) {
        $sql = "INSERT INTO sprints (project_id, name, start_date, end_date) VALUES (?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $name, $startDate, $endDate]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getByProject($projectId) {
        $sql = "SELECT * FROM sprints WHERE project_id = ? ORDER BY start_date DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT s.*, p.name as project_name 
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                WHERE s.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $name, $startDate, $endDate) {
        $sql = "UPDATE sprints SET name = ?, start_date = ?, end_date = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $startDate, $endDate, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            $updateTasksSql = "UPDATE tasks SET sprint_id = NULL WHERE sprint_id = ?";
            $updateTasksStmt = $this->db->prepare($updateTasksSql);
            $updateTasksStmt->execute([$id]);
            
            $sql = "DELETE FROM sprints WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getTasks($sprintId) {
        $sql = "SELECT t.*, s.name as status_name, u.first_name, u.last_name 
                FROM tasks t
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN users u ON t.assignee_id = u.id
                WHERE t.sprint_id = ?
                ORDER BY t.priority ASC, t.id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sprintId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

