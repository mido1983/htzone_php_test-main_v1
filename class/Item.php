<?php
require_once __DIR__ . '/Database.php';

class Item {
    private $db;
    private $api_base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getItemDetails($itemApiId) {
        try {
            // First try to get from database
            $item = $this->getFromDatabase($itemApiId);
            
            // If not found or data is old, fetch from API
            if (!$item || $this->isDataStale($item)) {
                error_log("Fetching item {$itemApiId} from API");
                $item = $this->getFromApi($itemApiId);
            } else {
                error_log("Using cached item {$itemApiId} from database");
            }
            
            // Format the response
            return [
                'title' => $item['title'] ?? '',
                'price' => $item['price'] ?? 0,
                'description' => isset($item['description_json']) ? 
                    json_decode($item['description_json'], true) : 
                    ['description' => $item['description'] ?? []],
                'images' => isset($item['img_arr']) ? array_values($item['img_arr']) : [],
                'features' => $item['features'] ?? [],
                'brand' => $item['brand'] ?? '',
                'brief' => $item['brief'] ?? '',
                'sub_title' => $item['sub_title'] ?? '',
                'delivery_info' => $item['delivery_info'] ?? '',
                'warrenty_info' => $item['warrenty_info'] ?? ''
            ];
        } catch (Exception $e) {
            error_log("Error getting item details: " . $e->getMessage());
            throw $e;
        }
    }

    private function getFromDatabase($itemApiId) {
        $stmt = $this->db->prepare('
            SELECT i.*, 
                   GROUP_CONCAT(DISTINCT f.feature_key, ":", f.feature_value) as features,
                   GROUP_CONCAT(DISTINCT im.img_url) as images
            FROM items i
            LEFT JOIN item_features f ON i.item_id = f.item_id
            LEFT JOIN item_images im ON i.item_id = im.item_id
            WHERE i.item_api_id = ?
            GROUP BY i.item_id
            LIMIT 1
        ');
        
        $stmt->bind_param('s', $itemApiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if ($item) {
            // Format features and images
            $item['features'] = $this->parseFeatures($item['features']);
            $item['images'] = $item['images'] ? explode(',', $item['images']) : [];
        }

        return $item;
    }

    private function getFromApi($itemApiId) {
        // Make API request
        $ch = curl_init($this->api_base_url . "/item/" . $itemApiId);
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
        
        curl_close($ch);
        
        $data = json_decode($response, true);
        if (!isset($data['data'])) {
            throw new Exception("Invalid API response");
        }

        // Store in database
        $this->storeItemData($data['data']);

        return $data['data'];
    }

    private function storeItemData($itemData) {
        try {
            // Store main item data
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

            // Prepare data
            $itemApiId = $itemData['id'];
            $categoryId = $itemData['category_id'];
            $active = 1;
            $title = $itemData['title'];
            $subTitle = $itemData['sub_title'] ?? null;
            $brandTitle = $itemData['brand'] ?? null;
            $price = (float)$itemData['price'];
            $priceBeforeDiscount = (float)($itemData['price_before_discount'] ?? $price);
            $brief = $itemData['brief'] ?? null;
            
            // Prepare description JSON
            $descriptionData = [
                'description' => $itemData['description'] ?? [],
                'features' => $itemData['features'] ?? [],
                'delivery_info' => $itemData['delivery_info'] ?? []
            ];
            $descriptionJson = json_encode($descriptionData, JSON_UNESCAPED_UNICODE);

            // Execute the statement
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

            $stmt->execute();
            $itemId = $stmt->insert_id ?: $this->getItemIdByApiId($itemApiId);
            $stmt->close();

            // Store related data
            if (isset($itemData['features'])) {
                $this->storeFeatures($itemId, $itemData['features']);
            }
            if (isset($itemData['img_arr'])) {
                $this->storeImages($itemId, $itemData['img_arr']);
            }

            return $itemId;
        } catch (Exception $e) {
            error_log("Error storing item data: " . $e->getMessage());
            throw $e;
        }
    }

    private function parseFeatures($featuresString) {
        if (!$featuresString) return [];
        
        $features = [];
        $pairs = explode(',', $featuresString);
        foreach ($pairs as $pair) {
            list($key, $value) = explode(':', $pair);
            $features[$key] = $value;
        }
        return $features;
    }

    private function isDataStale($item) {
        // Check if data is older than 24 hours
        $updated = strtotime($item['updated_at']);
        return (time() - $updated) > (24 * 60 * 60);
    }

    private function storeFeatures($itemId, $features) {
        try {
            // First delete existing features
            $deleteStmt = $this->db->prepare('DELETE FROM item_features WHERE item_id = ?');
            $deleteStmt->bind_param('i', $itemId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Prepare insert statement
            $stmt = $this->db->prepare('
                INSERT INTO item_features (item_id, feature_key, feature_value) 
                VALUES (?, ?, ?)
            ');

            // Store each feature
            foreach ($features as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $featureKey = $key . '_' . $subKey;
                        $featureValue = is_array($subValue) ? json_encode($subValue, JSON_UNESCAPED_UNICODE) : (string)$subValue;
                        
                        $stmt->bind_param('iss', $itemId, $featureKey, $featureValue);
                        $stmt->execute();
                    }
                } else {
                    $featureValue = (string)$value;
                    $stmt->bind_param('iss', $itemId, $key, $featureValue);
                    $stmt->execute();
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error storing features: " . $e->getMessage());
        }
    }

    private function storeImages($itemId, $images) {
        try {
            // First delete existing images
            $deleteStmt = $this->db->prepare('DELETE FROM item_images WHERE item_id = ?');
            $deleteStmt->bind_param('i', $itemId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Prepare insert statement
            $stmt = $this->db->prepare('
                INSERT INTO item_images (item_id, img_url, sort_order) 
                VALUES (?, ?, ?)
            ');

            // Store each image
            $sortOrder = 0;
            foreach ($images as $imageUrl) {
                $stmt->bind_param('isi', $itemId, $imageUrl, $sortOrder);
                $stmt->execute();
                $sortOrder++;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error storing images: " . $e->getMessage());
        }
    }

    private function getItemIdByApiId($apiId) {
        $stmt = $this->db->prepare('SELECT item_id FROM items WHERE item_api_id = ?');
        $stmt->bind_param('s', $apiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['item_id'] : null;
    }
}
