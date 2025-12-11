<?php

require_once "../app/core/Model.php";

class Comment extends Model {
    
    public function create($taskId, $authorId, $text) {
        $sql = "INSERT INTO comments (task_id, author_id, text) VALUES (?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId, $authorId, $text]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getByTask($taskId) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email
                FROM comments c
                LEFT JOIN users u ON c.author_id = u.id
                WHERE c.task_id = ?
                ORDER BY c.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email
                FROM comments c
                LEFT JOIN users u ON c.author_id = u.id
                WHERE c.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $text) {
        $sql = "UPDATE comments SET text = ? WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$text, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

