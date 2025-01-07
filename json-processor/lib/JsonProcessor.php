<?php
class JsonProcessor {
    private $url1;
    private $url2;
    
    public function __construct($url1 = '', $url2 = '') {
        $this->url1 = $url1;
        $this->url2 = $url2;
    }
    
    public function fetchData() {
        $data1 = $this->fetchUrl($this->url1);
        $data2 = $this->fetchUrl($this->url2);
        
        return [
            'data1' => $data1,
            'data2' => $data2
        ];
    }
    
    private function fetchUrl($url) {
        if (empty($url)) {
            return null;
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP code: " . $httpCode);
        }
        
        return json_decode($response, true);
    }
}