<?php

require_once "../app/core/Model.php";

class Auth extends Model {
    
    public function register($firstName, $lastName, $email, $password, $role = 'user') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$firstName, $lastName, $email, $passwordHash, $role]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            error_log("Registration error: " . $errorMsg);
            error_log("Registration data: firstName=$firstName, lastName=$lastName, email=$email, role=$role");
            throw $e;
        }
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $storedHash = $user['password'];
                $hashLength = strlen($storedHash);
                
                if ($hashLength < 60) {
                    error_log("Invalid password hash length ($hashLength) for user: $email");
                }
                
                if (password_verify($password, $storedHash)) {
                    return $user;
                } else {
                    error_log("Password verification failed for user: $email (hash length: $hashLength)");
                }
            } else {
                error_log("User not found: $email");
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
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
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}

