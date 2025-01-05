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
                (category_id, title, parent_title, parent_id, top_id, group_title, items_api_url, sub_category_api_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $successCount = 0;
            foreach ($categories['data'] as $category) {
                // Debug each category
                error_log("Processing category: " . print_r($category, true));
                
                try {
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

                    if ($stmt->execute()) {
                        $successCount++;
                        error_log("Successfully inserted category ID: " . $category['category_id']);
                    } else {
                        error_log("Error inserting category: " . $stmt->error);
                    }
                } catch (Exception $e) {
                    error_log("Error processing category: " . $e->getMessage());
                    error_log("Category data: " . print_r($category, true));
                }
            }

            $stmt->close();
            
            // Verify insertion
            $result = $this->db->query("SELECT COUNT(*) as count FROM categories");
            $count = $result->fetch_assoc()['count'];
            error_log("Total categories in database after insertion: " . $count);
            
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
                throw new Exception("Invalid items data format");
            }

            $stmt = $this->db->prepare('
                INSERT INTO items 
                (id, title, description, price, category_id, image_url) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                title=VALUES(title),
                description=VALUES(description),
                price=VALUES(price),
                category_id=VALUES(category_id),
                image_url=VALUES(image_url)
            ');

            $successCount = 0;
            foreach ($items['data'] as $index => $item) {
                try {
                    // Debug item data
                    error_log("Processing item #{$index}: " . json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    
                    // Validate required fields
                    if (!isset($item['id']) || !isset($item['title'])) {
                        error_log("Skipping item - missing required fields");
                        continue;
                    }

                    // Store values in variables first
                    $id = (int)$item['id'];
                    $title = (string)$item['title'];
                    
                    // Handle description - convert array to string if needed
                    $description = '';
                    if (isset($item['description'])) {
                        if (is_array($item['description'])) {
                            // If description is an array, join its values
                            $description = implode("\n", array_filter($item['description']));
                        } else {
                            $description = (string)$item['description'];
                        }
                    }
                    
                    $price = isset($item['price']) ? (float)$item['price'] : 0.0;
                    $image = isset($item['image_url']) ? (string)$item['image_url'] : '';
                    
                    // Debug values
                    error_log(sprintf(
                        "Prepared values - ID: %d, Title: %s, Price: %.2f, Description length: %d",
                        $id,
                        $title,
                        $price,
                        strlen($description)
                    ));
                    
                    $stmt->bind_param('issdis',
                        $id,
                        $title,
                        $description,
                        $price,
                        $categoryId,
                        $image
                    );

                    if ($stmt->execute()) {
                        $successCount++;
                        error_log(sprintf("Successfully inserted item #%d: %s", $id, $title));
                    } else {
                        error_log(sprintf("Error inserting item #%d: %s", $id, $stmt->error));
                    }
                } catch (Exception $e) {
                    error_log(sprintf(
                        "Error processing item #%d: %s. Data: %s",
                        $index,
                        $e->getMessage(),
                        json_encode($item, JSON_UNESCAPED_UNICODE)
                    ));
                }
            }

            $stmt->close();
            
            // Log final count
            error_log(sprintf("Successfully stored %d items for category %d", $successCount, $categoryId));
            
            return $successCount;
        } catch (Exception $e) {
            error_log("Store items error: " . $e->getMessage());
            throw $e;
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
