<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../class/HtzoneApi.php';
require_once '../class/Category.php';

try {
    $category = new Category();
    $api = new HtzoneApi();

    // Get all categories with their sub-category URLs
    $categories = $category->read();

    echo "Fetching sub-categories for each category..\n";
    
    foreach ($categories as $cat) {
        if (empty($cat['sub_category_api_url'])) {
            continue;
        }

        echo "\nProcessing category: {$cat['title']} (ID: {$cat['category_id']})\n";
        
        // Extract endpoint from full URL
        $endpoint = str_replace($api->getBaseUrl(), '', $cat['sub_category_api_url']);
        
        // Fetch and print sub-categories
        $subCategories = $api->makeApiRequest($endpoint);
        echo "<pre>";
        print_r($subCategories);
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 