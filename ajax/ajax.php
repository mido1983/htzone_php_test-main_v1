<?php
require_once '../class/HtzoneApi.php';
require_once '../class/Item.php';

header('Content-Type: application/json');

try {
    $api = new HtzoneApi();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'getCategories':
            $result = $api->getCategories();
            echo json_encode([
                'success' => true,
                'data' => $result['api_data']['data']
            ]);
            break;
            
        case 'getItems':
            $categoryId = isset($_GET['categoryId']) ? (int)$_GET['categoryId'] : 0;
            if (!$categoryId) {
                throw new Exception('Category ID is required');
            }
            
            $items = $api->getItems($categoryId);
            $subCategory = $api->getSubCategory($categoryId);
            
            // Combine items from both responses
            $allItems = array_merge(
                isset($items['api_data']['data']) && is_array($items['api_data']['data']) ? $items['api_data']['data'] : [],
                isset($subCategory['api_data']['data']) && is_array($subCategory['api_data']['data']) ? $subCategory['api_data']['data'] : []
            );
            
            echo json_encode([
                'success' => true,
                'data' => $allItems
            ]);
            break;
            
        case 'getItemDetails':
            $itemApiId = isset($_GET['itemApiId']) ? $_GET['itemApiId'] : null;
            if (!$itemApiId) {
                throw new Exception('Item API ID is required');
            }
            
            $item = new Item();
            $details = $item->getItemDetails($itemApiId);
            
            echo json_encode([
                'success' => true,
                'data' => $details
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
