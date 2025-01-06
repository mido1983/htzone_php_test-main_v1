<?php
class HtzoneApi {
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    private function makeApiRequest($endpoint) {
        try {
            $url = $this->base_url . $endpoint;
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
            if (curl_errno($ch)) {
                throw new Exception("Curl error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("API returned HTTP code: " . $httpCode);
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            error_log("API Request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function fetchAndStoreCategories() {
        $categories = $this->makeApiRequest('/categories');
        
        foreach ($categories['data'] as $category) {
            $categoryData = [
                'category_id' => $category['category_id'],
                'parent_id' => $category['parent_id'],
                'title' => $category['title'],
                'level' => empty($category['parent_id']) ? 1 : 2,
                'type_id' => $category['top_id']
            ];
            
            $this->categoryModel->create($categoryData);
        }
        
        return $categories;
    }
    
    // Add other API-related methods here
}
