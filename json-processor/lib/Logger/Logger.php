<?php
namespace App\Logger;

class Logger {
    private $logFile;
    private static $instance = null;
    
    private function __construct() {
        $this->logFile = dirname(dirname(__DIR__)) . '/logs/app.log';
        $this->ensureLogDirectoryExists();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($level, $message, array $context = []) {
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function error($message, array $context = []) {
        $this->log('error', $message, $context);
    }
    
    public function info($message, array $context = []) {
        $this->log('info', $message, $context);
    }
    
    public function debug($message, array $context = []) {
        $this->log('debug', $message, $context);
    }
    
    private function ensureLogDirectoryExists() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
} 