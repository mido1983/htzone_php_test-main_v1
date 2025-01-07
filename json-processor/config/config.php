<?php
// Define allowed hosts
if (!defined('ALLOWED_HOSTS')) {
    define('ALLOWED_HOSTS', [
        'storeapi.htzone.co.il',
        'localhost',
        '127.0.0.1',
        $_SERVER['HTTP_HOST'] ?? '', // Add current host
        // Add any other allowed hosts here
    ]);
}

return [
    'app' => [
        'name' => 'JSON Processor',
        'version' => '1.0.0',
        'debug' => true
    ],
    'api' => [
        'allowed_hosts' => ALLOWED_HOSTS,
        'timeout' => 30,
        'verify_ssl' => false
    ],
    'json' => [
        'max_depth' => 512,
        'max_size' => 10 * 1024 * 1024 // 10MB
    ],
    'improvements' => [
        'key_formats' => ['snake', 'camel'],
        'default_format' => 'snake'
    ]
];