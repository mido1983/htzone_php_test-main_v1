<?php
namespace App\Config;

class Config {
    private static $config = [];
    
    public static function load() {
        $configFile = dirname(dirname(__DIR__)) . '/config/config.php';
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }
    }
    
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    public static function set($key, $value) {
        self::$config[$key] = $value;
    }
} 