<?php
require_once __DIR__ . '/Database.php';

class HtzoneApi {
    private $db;
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function makeApiRequest($endpoint) {
        try {
            $url = $this->base_url . $endpoint;
            error_log("Making API request to: " . $url);
            
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
            error_log("Response Body: " . $response);
            
            if (curl_errno($ch)) {
                throw new Exception("Curl error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("API returned HTTP code: " . $httpCode . " Response: " . $response);
            }
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("API Request error: " . $e->getMessage());
            throw $e;
        }
    }

    public function storeCategories($categories) {
        if (!isset($categories['data']) || !is_array($categories['data'])) {
            throw new Exception("Invalid categories data format");
        }

        $stmt = $this->db->prepare('
            INSERT INTO categories 
            (category_id, title, parent_title, parent_id, top_id, group_title, items_api_url, sub_category_api_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            title=VALUES(title),
            parent_title=VALUES(parent_title),
            parent_id=VALUES(parent_id),
            top_id=VALUES(top_id),
            group_title=VALUES(group_title),
            items_api_url=VALUES(items_api_url),
            sub_category_api_url=VALUES(sub_category_api_url)
        ');

        foreach ($categories['data'] as $category) {
            $stmt->bind_param('issiisss',
                $category['category_id'],
                $category['title'],
                $category['parent_title'],
                $category['parent_id'],
                $category['top_id'],
                $category['group_title'],
                $category['items_api_url'],
                $category['sub_category_api_url']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error storing category: " . $stmt->error);
            }
        }

        $stmt->close();
        return true;
    }

    public function getCategories() {
        try {
            // Get categories from API
            $categories = $this->makeApiRequest('/categories');
            
            // Store categories in database
            $this->storeCategories($categories);
            
            return $categories;
        } catch (Exception $e) {
            error_log("Category fetch/store error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getItems($categoryId) {
        try {
            // According to docs: GET /items/[category_id]
            return $this->makeApiRequest("/items/{$categoryId}");
        } catch (Exception $e) {
            error_log("Item fetch error: " . $e->getMessage());
            throw $e;
        }
    }
}
