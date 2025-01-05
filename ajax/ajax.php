<?php
require_once '../class/Database.php';
require_once '../class/Item.php';
require_once '../class/Category.php';

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

if (!isset($_POST['act'])) {
    $response['message'] = 'No action specified';
    echo json_encode($response);
    exit;
}

$item = new Item();
$category = new Category();

error_reporting(E_ALL);
ini_set('display_errors', 1);

switch ($_POST['act']) {
    case 'getItems':
        try {
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $filters = [
                'category' => $_POST['category'] ?? '',
                'price_min' => $_POST['price_min'] ?? '',
                'price_max' => $_POST['price_max'] ?? '',
                'brand' => $_POST['brand'] ?? ''
            ];
            
            $sort = [
                'field' => $_POST['sort_field'] ?? 'name',
                'direction' => $_POST['sort_direction'] ?? 'ASC'
            ];

            $data = $item->getItems($page, $limit, $filters, $sort);
            $response['status'] = 'success';
            $response['data'] = $data;
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        break;

    case 'getCategories':
        $data = $category->getCategories();
        $response['status'] = 'success';
        $response['data'] = $data;
        break;

    case 'getCarouselItems':
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
        
        if ($category_id <= 0) {
            $response['message'] = 'Invalid category ID';
            break;
        }
        
        $data = $item->getCarouselItems($category_id, $limit);
        $response['status'] = 'success';
        $response['data'] = $data;
        break;

    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response);
