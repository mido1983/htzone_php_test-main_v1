<?php
class HtzoneApi {
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    public function makeApiRequest($endpoint) {
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
                'type_id' => $category['top_id'],
                'parent_title' => $category['parent_title'],
                'top_id' => $category['top_id'],
                'group_title' => $category['group_title'],
                'items_api_url' => $category['items_api_url'],
                'sub_category_api_url' => $category['sub_category_api_url']
            ];
            
            $this->categoryModel->create($categoryData);
        }
        
        return $categories;
    }
    
    public function getBaseUrl() {
        return $this->base_url;
    }
    
    // Add other API-related methods here
}
