<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../class/HtzoneApi.php';
require_once '../class/Category.php';

try {
    echo "Updating categories table...\n";
    
    // 1. Truncate the existing table
    $category = new Category();
    $category->truncate();
    
    // 2. Fetch and store new data
    $api = new HtzoneApi();
    $result = $api->fetchAndStoreCategories();
    
    echo "Successfully updated " . count($result['data']) . " categories!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 