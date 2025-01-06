<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../class/HtzoneApi.php';
require_once '../class/Category.php';

/**
 * Helper function to print category tree
 */
function printCategoryTree($categories) {
    echo "<pre>";
    print_r($categories);
    echo "</pre>";

 
    
    // Print API field structure for reference
    echo "\nAPI Fields Reference:\n";
    echo str_repeat("─", 100) . "\n";
    if (!empty($categories['data'][0])) {
        foreach ($categories['data'][0] as $key => $value) {
            echo "├─ {$key}: {$value}\n";
        }
    }

}
try {
    echo "Fetching categories from API...\n";
    
    $api = new HtzoneApi();
    $categories = $api->makeApiRequest('/categories');
    
    if (!isset($categories['data']) || !is_array($categories['data'])) {
        throw new Exception("Invalid API response format");
    }
    
    printCategoryTree($categories);
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 