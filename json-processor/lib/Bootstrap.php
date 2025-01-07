<?php
namespace App;

use App\Config\Config;
use App\Logger\Logger;

class Bootstrap {
    public static function init() {
        // Load configuration
        Config::load();
        
        // Set error handling
        self::setupErrorHandling();
        
        // Set timezone
        date_default_timezone_set('UTC');
        
        // Initialize other services as needed
    }
    
    private static function setupErrorHandling() {
        error_reporting(E_ALL);
        ini_set('display_errors', Config::get('app.debug', false) ? '1' : '0');
        
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        set_exception_handler(function($exception) {
            Logger::getInstance()->error($exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            if (Config::get('app.debug', false)) {
                throw $exception;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'An error occurred'
                ]);
            }
        });
    }
} 