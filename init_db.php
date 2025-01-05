<?php
require_once 'class/HtzoneApi.php';

try {
    $api = new HtzoneApi();
    
    // Initialize database tables
    $api->initDatabase();
    
    // Fetch and store initial data
    $api->fetchAndStoreCategories();
    $api->fetchAndStoreItems();
    
    echo "Database initialized successfully!\n";
} catch (Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
} 