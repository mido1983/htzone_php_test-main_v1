<?php
namespace App\Processor;

use App\Config\Config;

abstract class BaseProcessor {
    protected function validateUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL format: $url");
        }
        
        $host = parse_url($url, PHP_URL_HOST);
        $allowedHosts = array_merge(
            Config::get('api.allowed_hosts', []),
            [
                'storeapi.htzone.co.il',
                $_SERVER['HTTP_HOST'],
                'localhost',
                '127.0.0.1'
            ]
        );
        
        if (!in_array($host, $allowedHosts)) {
            throw new \InvalidArgumentException("Host not allowed: $host");
        }
        
        return true;
    }
    
    protected function formatResponse($data, $message = '', $success = true) {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
    }
    
    protected function logError($message, $context = []) {
        error_log(sprintf(
            "[%s] Error: %s Context: %s",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context)
        ));
    }
} 