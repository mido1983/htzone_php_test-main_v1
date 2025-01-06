<?php
require_once 'class/Database.php';

try {
    echo "<h1>Testing Database Connection</h1>";
    
    $db = Database::getInstance();
    echo "<p>Database connection successful</p>";
    
    $conn = $db->getConnection();
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    echo "<h2>Tables in Database:</h2>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Check categories table
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $count = $result->fetch_assoc()['count'];
    echo "<h2>Categories Count: " . $count . "</h2>";
    
    if ($count > 0) {
        $result = $conn->query("SELECT * FROM categories LIMIT 5");
        echo "<h2>Sample Categories:</h2>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} 