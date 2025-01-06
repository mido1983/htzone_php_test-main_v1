<?php

use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            // Load environment variables
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
            
            // Required environment variables
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
            
            // Log connection attempt
            error_log("Attempting to connect to database with parameters:");
            error_log("Host: " . $_ENV['DB_HOST']);
            error_log("Database: " . $_ENV['DB_NAME']);
            error_log("User: " . $_ENV['DB_USER']);
            
            // DSN configuration
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME'],
                $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            );
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // Create PDO instance
            $this->pdo = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'] ?? '',
                $options
            );
            
            error_log("Database connection successful");
            
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->pdo;
    }
    
    // Helper method for queries
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // Helper method for single row
    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    // Helper method for multiple rows
    public function queryAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }
    
    // Helper method for count
    public function count(string $sql, array $params = []): int {
        return (int) $this->queryOne($sql, $params)['count'];
    }
    
    // Verify database connection and data
    public function verifyData(): int {
        try {
            $count = $this->count("SELECT COUNT(*) as count FROM categories");
            error_log("Categories in database: " . $count);
            
            if ($count > 0) {
                $sample = $this->queryOne("SELECT * FROM categories LIMIT 1");
                error_log("Sample category data: " . print_r($sample, true));
            }
            
            return $count;
        } catch (Exception $e) {
            error_log("Verify Data Error: " . $e->getMessage());
            throw $e;
        }
    }
} 