<?php
namespace App\Processor;

class JsonFetcher extends BaseProcessor {
    private $url;
    private $options;
    
    public function __construct($url, array $options = []) {
        $this->url = $url;
        $this->options = array_merge([
            'timeout' => 30,
            'verify_ssl' => false,
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ], $options);
    }
    
    public function fetch() {
        try {
            $this->validateUrl($this->url);
            
            $ch = curl_init($this->url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => $this->options['verify_ssl'],
                CURLOPT_TIMEOUT => $this->options['timeout'],
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => $this->options['headers']
            ]);
            
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception("Curl error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("API returned HTTP code: " . $httpCode);
            }
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            return $this->formatResponse($data);
            
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), ['url' => $this->url]);
            throw $e;
        }
    }
} 