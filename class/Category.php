<?php
require_once __DIR__ . '/CRUD.php';

class Category extends CRUD {
    public function create($data) {
        $stmt = $this->db->prepare('
            INSERT INTO categories (category_id, parent_id, title, level, type_id) 
            VALUES (:category_id, :parent_id, :title, :level, :type_id)
        ');
        
        return $stmt->execute([
            ':category_id' => $data['category_id'],
            ':parent_id' => $data['parent_id'],
            ':title' => $data['title'],
            ':level' => $data['level'],
            ':type_id' => $data['type_id']
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
        $stmt = $this->db->prepare('
            UPDATE categories 
            SET parent_id = :parent_id,
                title = :title,
                level = :level,
                type_id = :type_id
            WHERE category_id = :id
        ');
        
        return $stmt->execute([
            ':id' => $id,
            ':parent_id' => $data['parent_id'],
            ':title' => $data['title'],
            ':level' => $data['level'],
            ':type_id' => $data['type_id']
        ]);
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
}
