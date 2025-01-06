<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../class/Database.php';
require_once '../class/Category.php';
require_once '../class/HtzoneApi.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $category = new Category();
    $api = new HtzoneApi();
    
    switch ($action) {
        case 'getCategories':
            // First try to fetch from API and store
            try {
                $api->fetchAndStoreCategories();
            } catch (Exception $e) {
                error_log("API fetch failed: " . $e->getMessage());
            }
            
            // Then return from database
            $categories = $category->read();
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            break;
            
        // Add other cases here
        
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
