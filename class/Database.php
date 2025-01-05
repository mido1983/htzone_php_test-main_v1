<?php

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            // Enable error reporting for debugging
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->conn = new mysqli(
                'localhost',     // host
                'root',         // username
                '',            // password
                'htzone_php_test'  // database name
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS categories (
                category_id INT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                parent_title VARCHAR(255),
                parent_id INT,
                top_id INT,
                group_title VARCHAR(255),
                items_api_url TEXT,
                sub_category_api_url TEXT
            )",
            
            "CREATE TABLE IF NOT EXISTS items (
                id INT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                category_id INT,
                image_url TEXT,
                FOREIGN KEY (category_id) REFERENCES categories(category_id)
            )"
        ];
        
        foreach ($queries as $query) {
            if (!$this->conn->query($query)) {
                throw new Exception("Table creation failed: " . $this->conn->error);
            }
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
} 