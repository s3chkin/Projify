<?php

require_once "Session.php";

class CSRF {
    
    // Генерира CSRF token
    public static function generateToken() {
        Session::start();
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }
    
    // Взема текущия CSRF token
    public static function getToken() {
        Session::start();
        if (!Session::has('csrf_token')) {
            return self::generateToken();
        }
        return Session::get('csrf_token');
    }
    
    // Валидира CSRF token
    public static function validateToken($token) {
        Session::start();
        $sessionToken = Session::get('csrf_token');
        
        if (!$sessionToken || !$token) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    // Генерира HTML input за CSRF token
    public static function getTokenField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

