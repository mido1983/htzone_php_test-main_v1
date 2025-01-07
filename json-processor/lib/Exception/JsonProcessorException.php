<?php
namespace App\Exception;

class JsonProcessorException extends \Exception {
    protected $context;
    
    public function __construct($message, $context = [], $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    public function getContext() {
        return $this->context;
    }
} 