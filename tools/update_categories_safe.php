<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../class/HtzoneApi.php';
require_once '../class/Category.php';

try {
    echo "Updating categories table...\n";
    
    $api = new HtzoneApi();
    $category = new Category();
    
    // 1. Get current categories from DB
    $existingCategories = $category->getAllCategoryIds();
    
    // 2. Fetch new data
    $result = $api->makeApiRequest('/categories');
    
    // 3. Update or insert each category
    foreach ($result['data'] as $data) {
        $categoryData = [
            'category_id' => $data['category_id'],
            'parent_id' => $data['parent_id'],
            'title' => $data['title'],
            'level' => empty($data['parent_id']) ? 1 : 2,
            'type_id' => $data['top_id'],
            'parent_title' => $data['parent_title'],
            'top_id' => $data['top_id'],
            'group_title' => $data['group_title'],
            'items_api_url' => $data['items_api_url'],
            'sub_category_api_url' => $data['sub_category_api_url']
        ];
        
        if (in_array($data['category_id'], $existingCategories)) {
            $category->update($data['category_id'], $categoryData);
        } else {
            $category->create($categoryData);
        }
    }
    
    echo "Successfully updated categories!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 