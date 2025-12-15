<?php

require_once "../app/core/Model.php";

class User extends Model {
    
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAll() {
        $sql = "SELECT * FROM users ORDER BY first_name, last_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getProjectsByUser($userId) {
        $sql = "SELECT p.*, 'owner' as role_type
                FROM projects p
                WHERE p.owner_id = ?
                UNION
                SELECT p.*, pm.role as role_type
                FROM projects p
                INNER JOIN project_members pm ON p.id = pm.project_id
                WHERE pm.user_id = ?
                ORDER BY id DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getTasksByUser($userId) {
        $sql = "SELECT DISTINCT t.*, p.name as project_name, s.name as status_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
                WHERE (p.owner_id = ? OR pm.user_id = ?)
                ORDER BY t.due_date ASC, t.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
