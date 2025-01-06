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

            // If data is a single item, wrap it in an array
            if (isset($items['data']['id'])) {
                $items['data'] = [$items['data']];
            }

            // Update the SQL to match the table structure
            $stmt = $this->db->prepare('
                INSERT INTO items 
                (item_api_id, category_id, active, title, sub_title, 
                 brand_title, price, price_before_discount, brief, description_json) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                        
                        // Store images
                        if (isset($item['img_arr']) && !empty($item['img_arr'])) {
                            $this->storeItemImages($itemId, $item['img_arr']);
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
            // First delete existing images
            $deleteStmt = $this->db->prepare('DELETE FROM item_images WHERE item_id = ?');
            $deleteStmt->bind_param('i', $itemId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Then insert new images
            $stmt = $this->db->prepare('
                INSERT INTO item_images (item_id, img_url, sort_order) 
                VALUES (?, ?, ?)
            ');
            
            // Handle both array formats: indexed and associative
            if (isset($images[1]) || isset($images['1'])) {
                // Format: [1 => url1, 2 => url2, ...]
                foreach ($images as $index => $imageUrl) {
                    $sortOrder = (int)$index - 1; // Convert 1-based to 0-based index
                    $stmt->bind_param('isi', $itemId, $imageUrl, $sortOrder);
                    if (!$stmt->execute()) {
                        error_log("Error storing image: " . $stmt->error);
                    }
                }
            } else {
                // Format: simple array of URLs
                foreach ($images as $index => $imageUrl) {
                    $sortOrder = (int)$index;
                    $stmt->bind_param('isi', $itemId, $imageUrl, $sortOrder);
                    if (!$stmt->execute()) {
                        error_log("Error storing image: " . $stmt->error);
                    }
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error in storeItemImages: " . $e->getMessage());
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
            // Get items from API first
            $apiResponse = $this->makeApiRequest("/items/{$categoryId}");
            error_log("API Response for category {$categoryId}: " . json_encode($apiResponse));

            if (!empty($apiResponse['data'])) {
                // Store items in database
                $storedCount = $this->storeItems($apiResponse, $categoryId);
                error_log("Stored {$storedCount} items for category {$categoryId}");
            }

            // Get items from database with images
            $items = $this->getItemsFromDatabase($categoryId);
            
            if (empty($items)) {
                // Try sub-category endpoint if main endpoint returned no results
                $subCategoryResponse = $this->makeApiRequest("/sub_category/{$categoryId}");
                error_log("Sub-category API Response: " . json_encode($subCategoryResponse));
                
                if (!empty($subCategoryResponse['data'])) {
                    $storedCount = $this->storeItems($subCategoryResponse, $categoryId);
                    error_log("Stored {$storedCount} items from sub-category");
                    $items = $this->getItemsFromDatabase($categoryId);
                }
            }

            return $items;
            
        } catch (Exception $e) {
            error_log("Error getting items: " . $e->getMessage());
            throw $e;
        }
    }

    private function getItemsFromDatabase($categoryId) {
        try {
            $stmt = $this->db->prepare('
                SELECT DISTINCT i.*, im.img_url 
                FROM items i
                LEFT JOIN item_images im ON i.item_id = im.item_id 
                WHERE i.category_id = ? 
                AND (im.sort_order = 0 OR im.sort_order IS NULL)
            ');
            
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = [
                    'id' => $row['item_api_id'],
                    'title' => $row['title'],
                    'price' => $row['price'],
                    'image_url' => $row['img_url'] ?: 'static/images/no-image.webp'
                ];
            }
            
            error_log("Found " . count($items) . " items in database for category {$categoryId}");
            return $items;
        } catch (Exception $e) {
            error_log("Database error in getItemsFromDatabase: " . $e->getMessage());
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

    // public function getItemDetails($itemApiId) {
    //     try {
    //         // First try to get data from API
    //         try {
    //             $apiResponse = $this->makeApiRequest("/item/{$itemApiId}");
    //             if (!empty($apiResponse['data'])) {
    //                 // Store item in database
    //                 $this->storeItems(['data' => [$apiResponse['data']]], $apiResponse['data']['category_id']);
    //             }
    //         } catch (Exception $e) {
    //             error_log("API request failed, trying database: " . $e->getMessage());
    //         }

    //         // Get data from database
    //         $itemStmt = $this->db->prepare('
    //             SELECT item_id, title, price, brief, description_json, brand_title, sub_title
    //             FROM items 
    //             WHERE item_api_id = ?
    //         ');
            
    //         $itemStmt->bind_param('s', $itemApiId);
    //         $itemStmt->execute();
    //         $itemResult = $itemStmt->get_result();
    //         $item = $itemResult->fetch_assoc();
    //         $itemStmt->close();

    //         if (!$item) {
    //             throw new Exception("Item not found");
    //         }

    //         // Get images
    //         $imagesStmt = $this->db->prepare('
    //             SELECT img_url
    //             FROM item_images 
    //             WHERE item_id = ? 
    //             ORDER BY sort_order
    //         ');
            
    //         $imagesStmt->bind_param('i', $item['item_id']);
    //         $imagesStmt->execute();
    //         $imagesResult = $imagesStmt->get_result();
    //         $images = [];
    //         while ($row = $imagesResult->fetch_assoc()) {
    //             $images[] = $row['img_url'];
    //         }
    //         $imagesStmt->close();

    //         // Prepare response
    //         $response = [
    //             'title' => $item['title'],
    //             'sub_title' => $item['sub_title'],
    //             'brand_title' => $item['brand_title'],
    //             'price' => $item['price'],
    //             'brief' => $item['brief'],
    //             'images' => $images
    //         ];

    //         // Add description data if exists
    //         if ($item['description_json']) {
    //             $description = json_decode($item['description_json'], true);
    //             if ($description) {
    //                 $response = array_merge($response, $description);
    //             }
    //         }

    //         return $response;

    //     } catch (Exception $e) {
    //         error_log("Error getting item details: " . $e->getMessage());
    //         throw $e;
    //     }
    // }
}
