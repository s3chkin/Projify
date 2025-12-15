<?php

require_once "../app/core/Model.php";

class Status extends Model {
    
    public function getAll() {
        $sql = "SELECT * FROM statuses ORDER BY order_index ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM statuses WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function isValidTransition($fromStatusId, $toStatusId) {
        $fromStatus = $this->getById($fromStatusId);
        $toStatus = $this->getById($toStatusId);
        
        if (!$fromStatus || !$toStatus) {
            return false;
        }
        
        $fromOrder = $fromStatus['order_index'];
        $toOrder = $toStatus['order_index'];
        
        return abs($toOrder - $fromOrder) <= 1;
    }
    
    public function getByName($name) {
        $sql = "SELECT * FROM statuses WHERE name = ? LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}

