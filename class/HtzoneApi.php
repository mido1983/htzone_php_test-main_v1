<?php
require_once __DIR__ . '/Database.php';

class HtzoneApi {
    private $db;
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function initDatabase() {
        // Tables are already created from the SQL dump
        return true;
    }

    public function fetchAndStoreCategories() {
        $ch = curl_init($this->base_url . '/categories');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        
        $categories = json_decode($response, true);
        
        $stmt = $this->db->prepare('INSERT INTO categories (id, name, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)');
        
        foreach ($categories as $category) {
            $stmt->bind_param('iss', 
                $category['id'],
                $category['name'],
                $category['description'] ?? ''
            );
            $stmt->execute();
        }
        $stmt->close();
    }

    public function fetchAndStoreItems() {
        $ch = curl_init($this->base_url . '/items');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        
        $items = json_decode($response, true);
        
        $stmt = $this->db->prepare('INSERT INTO items (id, name, description, price, brand, category_id, image_url, stock) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
            name=VALUES(name), description=VALUES(description), price=VALUES(price), 
            brand=VALUES(brand), category_id=VALUES(category_id), 
            image_url=VALUES(image_url), stock=VALUES(stock)');
        
        foreach ($items as $item) {
            $stmt->bind_param('issdsisd',
                $item['id'],
                $item['name'],
                $item['description'] ?? '',
                $item['price'],
                $item['brand'] ?? '',
                $item['category_id'],
                $item['image_url'] ?? '',
                $item['stock'] ?? 0
            );
            $stmt->execute();
        }
        $stmt->close();
    }
}
