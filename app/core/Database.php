<?php

class Database {
    // Статично свойство 
    private static $conn = null;
    
    public static function getConnection() {
        // Ако все още нямаме връзка, създаваме я
        if (self::$conn === null) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "Projify";
            
            try {
                // Създаваме PDO връзката
                $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                self::$conn = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        
        // Връщаме същата връзка всеки път (не създаваме нова)
        return self::$conn;
    }
}

