<?php
require_once 'class/Database.php';

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Read and execute SQL file
    $sql = file_get_contents('helpers/db.sql');
    
    // Split SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!$db->query($query)) {
                throw new Exception("Error executing query: " . $db->error);
            }
        }
    }
    
    echo "Database initialized successfully!\n";
    
} catch (Exception $e) {
    die("Error initializing database: " . $e->getMessage() . "\n");
} 