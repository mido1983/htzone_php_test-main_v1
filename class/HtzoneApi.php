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
        try {
            if (!isset($categories['data']) || !is_array($categories['data'])) {
                throw new Exception("Invalid categories data format");
            }

            $stmt = $this->db->prepare('
                INSERT INTO categories 
                (category_id, parent_id, title, level, type_id) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                parent_id=VALUES(parent_id),
                title=VALUES(title),
                level=VALUES(level),
                type_id=VALUES(type_id)
            ');

            $successCount = 0;
            foreach ($categories['data'] as $category) {
                try {
                    // Calculate level based on parent_id
                    $level = empty($category['parent_id']) ? 1 : 2;
                    
                    $stmt->bind_param('iisii',
                        $category['category_id'],
                        $category['parent_id'],
                        $category['title'],
                        $level,
                        $category['top_id']  // Using top_id as type_id
                    );

                    if ($stmt->execute()) {
                        $successCount++;
                        error_log(sprintf("Successfully inserted category #%d: %s", 
                            $category['category_id'], 
                            $category['title']
                        ));
                    } else {
                        error_log(sprintf("Error inserting category #%d: %s", 
                            $category['category_id'], 
                            $stmt->error
                        ));
                    }
                } catch (Exception $e) {
                    error_log(sprintf(
                        "Error processing category: %s. Data: %s",
                        $e->getMessage(),
                        json_encode($category, JSON_UNESCAPED_UNICODE)
                    ));
                }
            }

            $stmt->close();
            return $successCount;
        } catch (Exception $e) {
            error_log("Store categories error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCategories() {
        try {
            // Get categories from API
            $categories = $this->makeApiRequest('/categories');
            
            // Store categories in database
            $storedCount = $this->storeCategories($categories);
            error_log("Stored {$storedCount} categories in database");
            
            return [
                'api_data' => $categories,
                'stored_count' => $storedCount
            ];
        } catch (Exception $e) {
            error_log("Category fetch/store error: " . $e->getMessage());
            throw $e;
        }
    }

    public function storeItems($items, $categoryId) {
        try {
            if (!isset($items['data']) || !is_array($items['data'])) {
                error_log("Invalid items data format: " . json_encode($items));
                return 0;
            }

            // If data is a single item (not an array of items), wrap it in an array
            if (isset($items['data']['id']) && !isset($items['data'][0])) {
                $items['data'] = [$items['data']];
            }

            // Update the SQL to match the table structure
            $stmt = $this->db->prepare('
                INSERT INTO items 
                (item_api_id, category_id, active, title, sub_title, 
                 brand_title, price, price_before_discount, brief, description_json) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CAST(? AS JSON))
                ON DUPLICATE KEY UPDATE 
                category_id = VALUES(category_id),
                active = VALUES(active),
                title = VALUES(title),
                sub_title = VALUES(sub_title),
                brand_title = VALUES(brand_title),
                price = VALUES(price),
                price_before_discount = VALUES(price_before_discount),
                brief = VALUES(brief),
                description_json = VALUES(description_json)
            ');

            $successCount = 0;
            foreach ($items['data'] as $index => $item) {
                try {
                    error_log("Processing item: " . json_encode($item, JSON_UNESCAPED_UNICODE));

                    // Prepare item data
                    $itemApiId = (string)$item['id'];
                    $active = 1;
                    $title = (string)$item['title'];
                    $subTitle = isset($item['sub_title']) ? (string)$item['sub_title'] : null;
                    $brandTitle = isset($item['brand']) ? (string)$item['brand'] : null;
                    $price = isset($item['price']) ? (float)$item['price'] : 0.0;
                    $priceBeforeDiscount = isset($item['price_before_discount']) ? (float)$item['price_before_discount'] : $price;
                    $brief = isset($item['brief']) ? (string)$item['brief'] : null;
                    
                    // Prepare description JSON
                    $descriptionData = [
                        'description' => isset($item['description']) ? 
                            (is_array($item['description']) ? $item['description'] : [$item['description']]) : [],
                        'features' => isset($item['features']) ? $item['features'] : [],
                        'delivery_info' => isset($item['delivery_info']) ? $item['delivery_info'] : []
                    ];
                    $descriptionJson = json_encode($descriptionData, JSON_UNESCAPED_UNICODE);

                    error_log("Binding parameters for item {$itemApiId}");
                    $stmt->bind_param('sissssddss',
                        $itemApiId,
                        $categoryId,
                        $active,
                        $title,
                        $subTitle,
                        $brandTitle,
                        $price,
                        $priceBeforeDiscount,
                        $brief,
                        $descriptionJson
                    );

                    if ($stmt->execute()) {
                        $successCount++;
                        error_log("Successfully inserted item {$itemApiId}: {$title}");

                        // Get the inserted/updated item_id
                        $itemId = $stmt->insert_id ?: $this->getItemIdByApiId($itemApiId);

                        // Store images if available
                        if (isset($item['images']) && is_array($item['images'])) {
                            $this->storeItemImages($itemId, $item['images']);
                        }

                        // Store features if available
                        if (isset($item['features']) && is_array($item['features'])) {
                            $this->storeItemFeatures($itemId, $item['features']);
                        }
                    } else {
                        error_log("Error inserting item {$itemApiId}: " . $stmt->error);
                    }
                } catch (Exception $e) {
                    error_log("Error processing item {$itemApiId}: " . $e->getMessage());
                    error_log("Item data: " . json_encode($item, JSON_UNESCAPED_UNICODE));
                }
            }

            $stmt->close();
            return $successCount;
        } catch (Exception $e) {
            error_log("Store items error: " . $e->getMessage());
            throw $e;
        }
    }

    // Helper function to get item_id by item_api_id
    private function getItemIdByApiId($apiId) {
        $stmt = $this->db->prepare('SELECT item_id FROM items WHERE item_api_id = ?');
        $stmt->bind_param('s', $apiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['item_id'] : null;
    }

    private function storeItemImages($itemId, $images) {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO item_images (item_id, img_url, sort_order) 
                VALUES (?, ?, ?)
            ');
            
            foreach ($images as $index => $imageUrl) {
                // Store values in variables first
                $imgUrl = (string)$imageUrl;
                $sortOrder = (int)$index;
                
                // Bind parameters using variables
                $stmt->bind_param('isi', 
                    $itemId, 
                    $imgUrl,
                    $sortOrder
                );
                
                if (!$stmt->execute()) {
                    error_log("Error storing image: " . $stmt->error);
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error in storeItemImages: " . $e->getMessage());
            // Continue execution even if image storage fails
        }
    }

    private function storeItemFeatures($itemId, $features) {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO item_features (item_id, feature_key, feature_value) 
                VALUES (?, ?, ?)
            ');
            
            foreach ($features as $key => $value) {
                // Store values in variables first
                $featureKey = (string)$key;
                $featureValue = is_array($value) ? 
                    json_encode($value, JSON_UNESCAPED_UNICODE) : 
                    (string)$value;
                
                // Bind parameters using variables
                $stmt->bind_param('iss', 
                    $itemId, 
                    $featureKey,
                    $featureValue
                );
                
                if (!$stmt->execute()) {
                    error_log("Error storing feature: " . $stmt->error);
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error in storeItemFeatures: " . $e->getMessage());
            // Continue execution even if feature storage fails
        }
    }

    public function getItems($categoryId) {
        try {
            // Get items from API
            $items = $this->makeApiRequest("/items/{$categoryId}");
            
            // Store items in database
            $storedCount = $this->storeItems($items, $categoryId);
            error_log("Stored {$storedCount} items for category {$categoryId}");
            
            return [
                'api_data' => $items,
                'stored_count' => $storedCount
            ];
        } catch (Exception $e) {
            error_log("Item fetch/store error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSubCategory($categoryId) {
        try {
            // Get items from API
            $subCategory = $this->makeApiRequest("/sub_category/{$categoryId}");

            // Check if we got a category instead of items
            if (isset($subCategory['data']['id'])) {
                // This is a single category response, not items
                error_log("Sub-category endpoint returned category data instead of items");
                return [
                    'api_data' => ['data' => []],  // Return empty items array
                    'stored_count' => 0
                ];
            }

            // Store items in database
            $storedCount = $this->storeItems($subCategory, $categoryId);
            error_log(sprintf("Stored %d items for category %d (sub-category)", $storedCount, $categoryId));

            return [
                'api_data' => $subCategory,
                'stored_count' => $storedCount
            ];
        } catch (Exception $e) {
            error_log("Sub-category fetch/store error: " . $e->getMessage());
            throw $e;
        }
    }
}
