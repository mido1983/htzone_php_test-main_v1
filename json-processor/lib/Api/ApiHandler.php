<?php
namespace App\Api;

use App\Processor\JsonFetcher;
use App\Processor\JsonAnalyzer;
use App\Processor\JsonCleaner;
use App\Processor\JsonComparator;
use App\Logger\Logger;

class ApiHandler {
    private $request;
    private $logger;
    
    public function __construct() {
        $this->request = $_POST;
        $this->logger = Logger::getInstance();
        
        // Debug: Log construction
        error_log("ApiHandler constructed with request: " . print_r($this->request, true));
    }
    
    public function handleRequest() {
        try {
            // Debug: Log start of handling
            error_log("Starting request handling");
            
            $this->validateRequest();
            
            // Debug: Log after validation
            error_log("Request validated successfully");
            
            // Fetch JSON from both URLs
            $fetcher1 = new JsonFetcher($this->request['url1']);
            $fetcher2 = new JsonFetcher($this->request['url2']);
            
            // Debug: Log before fetching
            error_log("Attempting to fetch URL1: " . $this->request['url1']);
            $data1 = $fetcher1->fetch();
            error_log("Data1 fetched: " . print_r($data1, true));
            
            error_log("Attempting to fetch URL2: " . $this->request['url2']);
            $data2 = $fetcher2->fetch();
            error_log("Data2 fetched: " . print_r($data2, true));
            
            // Analyze both datasets
            $analyzer1 = new JsonAnalyzer($data1['data']);
            $analyzer2 = new JsonAnalyzer($data2['data']);
            
            $result = [
                'original' => [
                    'data1' => $data1['data'],
                    'data2' => $data2['data']
                ],
                'analysis' => [
                    'data1' => $analyzer1->analyze()['data'],
                    'data2' => $analyzer2->analyze()['data']
                ]
            ];
            
            // If improvements were requested
            if (!empty($this->request['improve'])) {
                $result['improvements'] = $this->processImprovements($data1['data'], $data2['data']);
            }
            
            // Debug: Log successful completion
            error_log("Request handled successfully. Result: " . print_r($result, true));
            
            return new ApiResponse($result);
            
        } catch (\Throwable $e) {
            // Debug: Log error details
            error_log("Error in handleRequest: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            throw $e;
        }
    }
    
    private function validateRequest() {
        // Debug: Log validation start
        error_log("Validating request: " . print_r($this->request, true));
        
        if (empty($this->request['url1']) || empty($this->request['url2'])) {
            throw new \InvalidArgumentException('Both URLs are required');
        }
        
        if (!filter_var($this->request['url1'], FILTER_VALIDATE_URL) || 
            !filter_var($this->request['url2'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }
        
        // Debug: Log successful validation
        error_log("Request validation successful");
    }
    
    private function processImprovements($data1, $data2) {
        $comparator = new JsonComparator($data1, $data2);
        $suggestions = $comparator->suggestImprovements();
        
        $improvements = [
            'suggestions' => $suggestions,
            'improved_data' => null
        ];
        
        if (!empty($this->request['improvements'])) {
            $cleaner1 = new JsonCleaner($data1);
            $cleaner2 = new JsonCleaner($data2);
            
            $improved1 = $data1;
            $improved2 = $data2;
            
            foreach ($this->request['improvements'] as $improvement) {
                switch ($improvement) {
                    case 'duplicates':
                        $improved1 = $cleaner1->removeDuplicates();
                        $improved2 = $cleaner2->removeDuplicates();
                        break;
                    case 'format':
                        $improved1 = $cleaner1->formatKeys('snake');
                        $improved2 = $cleaner2->formatKeys('snake');
                        break;
                }
            }
            
            $improvements['improved_data'] = [
                'data1' => $improved1,
                'data2' => $improved2
            ];
        }
        
        return $improvements;
    }
} 