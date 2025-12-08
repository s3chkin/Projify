<?php

require_once "../app/core/Model.php";

class Auth extends Model {
    
    public function register($firstName, $lastName, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password) 
                VALUES (?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$firstName, $lastName, $email, $passwordHash]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Връщаме грешката за да видим какво се случва
            error_log("Registration error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
        } catch (PDOException $e) {
            return false;
        }
        
        return false;
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}

