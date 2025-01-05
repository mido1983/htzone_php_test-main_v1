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
            $categoryId = $_GET['categoryId'] ?? null;
            if (!$categoryId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Category ID is required'
                ]);
                exit;
            }

            try {
                $items = $api->getItems($categoryId);
                echo json_encode([
                    'success' => true,
                    'data' => $items
                ]);
            } catch (Exception $e) {
                error_log("Error in getItems: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
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
