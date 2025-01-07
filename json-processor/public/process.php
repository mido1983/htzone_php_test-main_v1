<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

use App\Api\ApiHandler;
use App\Api\ApiResponse;
use App\Bootstrap;
use App\Logger\Logger;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize the application
Bootstrap::init();

header('Content-Type: application/json');

try {
    // Debug: Print incoming request
    error_log("Incoming request: " . print_r($_POST, true));
    
    // Validate request data
    if (empty($_POST['url1']) || empty($_POST['url2'])) {
        throw new \InvalidArgumentException('Both URLs are required');
    }
    
    // Debug: Print URLs
    error_log("URL1: " . $_POST['url1']);
    error_log("URL2: " . $_POST['url2']);
    
    // Create API handler instance
    $handler = new ApiHandler();
    
    // Handle the request and get response
    $response = $handler->handleRequest();
    
    // Debug: Print response
    error_log("Response: " . print_r($response, true));
    
    // Send the response
    if ($response instanceof ApiResponse) {
        $response->send();
    } else {
        echo json_encode([
            'success' => true,
            'data' => $response,
            'message' => ''
        ]);
    }
    
} catch (\Throwable $e) {
    // Debug: Print error details
    error_log("Error occurred: " . $e->getMessage());
    error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Create error response
    $errorResponse = ApiResponse::error($e->getMessage());
    
    // Add debug information in development environment
    $responseData = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ];
    
    echo json_encode($responseData);
}