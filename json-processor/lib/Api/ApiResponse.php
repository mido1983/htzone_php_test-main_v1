<?php
namespace App\Api;

class ApiResponse {
    private $data;
    private $message;
    private $success;
    private $statusCode;
    
    public function __construct($data = null, $message = '', $success = true, $statusCode = 200) {
        $this->data = $data;
        $this->message = $message;
        $this->success = $success;
        $this->statusCode = $statusCode;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function isSuccess() {
        return $this->success;
    }
    
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    public function send() {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data
        ]);
        exit;
    }
    
    public static function success($data = null, $message = '') {
        return new self($data, $message, true, 200);
    }
    
    public static function error($message, $statusCode = 400) {
        return new self(null, $message, false, $statusCode);
    }
    
    public function toArray() {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data
        ];
    }
} 