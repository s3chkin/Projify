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
        $sql = "DELETE FROM projects WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

