<?php

require_once "../app/core/Model.php";

class Label extends Model {
    
    public function create($name) {
        $sql = "INSERT INTO labels (name) VALUES (?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAll() {
        $sql = "SELECT * FROM labels ORDER BY name ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM labels WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getByTask($taskId) {
        $sql = "SELECT l.* FROM labels l
                INNER JOIN task_labels tl ON l.id = tl.label_id
                WHERE tl.task_id = ?
                ORDER BY l.name ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function update($id, $name) {
        $sql = "UPDATE labels SET name = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            $deleteTaskLabelsSql = "DELETE FROM task_labels WHERE label_id = ?";
            $deleteTaskLabelsStmt = $this->db->prepare($deleteTaskLabelsSql);
            $deleteTaskLabelsStmt->execute([$id]);
            
            $sql = "DELETE FROM labels WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function addToTask($taskId, $labelId) {
        $sql = "INSERT INTO task_labels (task_id, label_id) VALUES (?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId, $labelId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function removeFromTask($taskId, $labelId) {
        $sql = "DELETE FROM task_labels WHERE task_id = ? AND label_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$taskId, $labelId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

