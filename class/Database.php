<?php

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Debug connection parameters
            error_log("Attempting to connect to database with parameters:");
            error_log("Host: localhost");
            error_log("User: root");
            error_log("Database: htzone_php_test-main_v1");
            
            // TO_DO: create .env file and use it, create PDO connection 
            $this->conn = new mysqli(
                'localhost',     // host
                'root',         // username
                '',            // password
                'htzone_php_test-main_v1'  // database name
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            error_log("Database connection successful");
            
            $this->conn->set_charset("utf8mb4");

        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Add method to verify data
    public function verifyData() {
        try {
            $result = $this->conn->query("SELECT COUNT(*) as count FROM categories");
            $count = $result->fetch_assoc()['count'];
            error_log("Categories in database: " . $count);
            
            if ($count > 0) {
                $sample = $this->conn->query("SELECT * FROM categories LIMIT 1");
                error_log("Sample category data: " . print_r($sample->fetch_assoc(), true));
            }
            
            return $count;
        } catch (Exception $e) {
            error_log("Verify Data Error: " . $e->getMessage());
            throw $e;
        }
    }
} 