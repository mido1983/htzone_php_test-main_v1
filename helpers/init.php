<?php
require_once 'class/HtzoneApi.php';

try {
    $api = new HtzoneApi();
    
    // Initialize database
    $api->initDatabase();
    
    // Fetch and store categories
    echo "Fetching categories...\n";
    $api->fetchAndStoreCategories();
    
    // Fetch and store items
    echo "Fetching items...\n";
    $api->fetchAndStoreItems();
    
    echo "Initialization completed successfully!\n";
} catch (Exception $e) {
    echo "Error during initialization: " . $e->getMessage() . "\n";
} 