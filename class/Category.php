<?php
require_once __DIR__ . '/CRUD.php';

class Category extends CRUD {
    public function create($data) {
        $stmt = $this->db->prepare('
            INSERT INTO categories (
                category_id, 
                parent_id, 
                title, 
                level, 
                type_id,
                parent_title,
                top_id,
                group_title,
                items_api_url,
                sub_category_api_url
            ) 
            VALUES (
                :category_id, 
                :parent_id, 
                :title, 
                :level, 
                :type_id,
                :parent_title,
                :top_id,
                :group_title,
                :items_api_url,
                :sub_category_api_url
            )
        ');
        
        return $stmt->execute([
            ':category_id' => $data['category_id'],
            ':parent_id' => $data['parent_id'],
            ':title' => $data['title'],
            ':level' => $data['level'],
            ':type_id' => $data['type_id'],
            ':parent_title' => $data['parent_title'],
            ':top_id' => $data['top_id'],
            ':group_title' => $data['group_title'],
            ':items_api_url' => $data['items_api_url'],
            ':sub_category_api_url' => $data['sub_category_api_url']
        ]);
    }
    
    public function read($id = null) {
        if ($id) {
            $stmt = $this->db->prepare('SELECT * FROM categories WHERE category_id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY title');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE categories SET " . implode(', ', $sets) . 
               " WHERE category_id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE category_id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    // Additional methods specific to categories
    public function getCategoryItems($categoryId) {
        $stmt = $this->db->prepare('
            SELECT i.*, im.img_url 
            FROM items i
            LEFT JOIN item_images im ON i.item_id = im.item_id 
            WHERE i.category_id = :category_id 
            AND (im.sort_order = 0 OR im.sort_order IS NULL)
        ');
        
        $stmt->execute([':category_id' => $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function truncate() {
        $sql = "TRUNCATE TABLE categories";
        $this->db->query($sql);
    }
    
    public function getAllCategoryIds() {
        $stmt = $this->db->query("SELECT category_id FROM categories");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}
