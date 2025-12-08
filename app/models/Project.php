<?php

require_once "../app/core/Model.php";

class Project extends Model {
    
    // CREATE - Създаване на проект
    public function create($name, $ownerId) {
        $sql = "INSERT INTO projects (name, owner_id) VALUES (?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name, $ownerId]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // READ - Вземане на всички проекти
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
    
    // READ - Вземане на проекти по owner_id
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
    
    // READ - Вземане на един проект по ID
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
    
    // UPDATE - Обновяване на проект
    public function update($id, $name) {
        $sql = "UPDATE projects SET name = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // DELETE - Изтриване на проект
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

