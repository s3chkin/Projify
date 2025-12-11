<?php

require_once "../app/core/Model.php";

class ProjectMember extends Model {
    
    public function add($projectId, $userId, $role = 'member') {
        $sql = "INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $userId, $role]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function remove($projectId, $userId) {
        $sql = "DELETE FROM project_members WHERE project_id = ? AND user_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$projectId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateRole($projectId, $userId, $role) {
        $sql = "UPDATE project_members SET role = ? WHERE project_id = ? AND user_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$role, $projectId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function isMember($projectId, $userId) {
        $sql = "SELECT * FROM project_members WHERE project_id = ? AND user_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $userId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getRole($projectId, $userId) {
        $projectSql = "SELECT owner_id FROM projects WHERE id = ?";
        $projectStmt = $this->db->prepare($projectSql);
        $projectStmt->execute([$projectId]);
        $project = $projectStmt->fetch();
        
        if ($project && $project['owner_id'] == $userId) {
            return 'owner';
        }
        
        $sql = "SELECT role FROM project_members WHERE project_id = ? AND user_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $userId]);
            $member = $stmt->fetch();
            return $member ? $member['role'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getByProject($projectId) {
        $sql = "SELECT u.*, pm.role, 
                CASE WHEN p.owner_id = u.id THEN 'owner' ELSE pm.role END as actual_role
                FROM users u
                LEFT JOIN project_members pm ON u.id = pm.user_id AND pm.project_id = ?
                LEFT JOIN projects p ON p.id = ? AND p.owner_id = u.id
                WHERE pm.user_id IS NOT NULL OR p.owner_id = u.id
                GROUP BY u.id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId, $projectId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

