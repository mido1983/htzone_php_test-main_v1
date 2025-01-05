<?php
require_once __DIR__ . '/Database.php';

class Item {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getItems($page = 1, $limit = 10, $filters = [], $sort = ['field' => 'name', 'direction' => 'ASC']) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['category'])) {
            $whereConditions[] = 'category_id = ?';
            $params[] = $filters['category'];
            $types .= 'i';
        }
        
        if (!empty($filters['price_min'])) {
            $whereConditions[] = 'price >= ?';
            $params[] = $filters['price_min'];
            $types .= 'd';
        }
        
        if (!empty($filters['price_max'])) {
            $whereConditions[] = 'price <= ?';
            $params[] = $filters['price_max'];
            $types .= 'd';
        }
        
        if (!empty($filters['brand'])) {
            $whereConditions[] = 'brand = ?';
            $params[] = $filters['brand'];
            $types .= 's';
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $allowedSortFields = ['name', 'price', 'brand'];
        $sortField = in_array($sort['field'], $allowedSortFields) ? $sort['field'] : 'name';
        $sortDirection = strtoupper($sort['direction']) === 'DESC' ? 'DESC' : 'ASC';
        
        $query = "SELECT * FROM items {$whereClause} 
                 ORDER BY {$sortField} {$sortDirection} 
                 LIMIT ?, ?";
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $params[] = $offset;
            $params[] = $limit;
            $types .= 'ii';
            
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('ii', $offset, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return [
            'items' => $items,
            'page' => $page,
            'limit' => $limit,
            'total' => $this->getTotalItems($whereConditions, $params, $types)
        ];
    }
    
    private function getTotalItems($whereConditions = [], $params = [], $types = '') {
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $query = "SELECT COUNT(*) as total FROM items {$whereClause}";
        
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    public function getCarouselItems($category_id, $limit = 10) {
        $stmt = $this->db->prepare('
            SELECT * FROM items 
            WHERE category_id = ? 
            ORDER BY RAND() 
            LIMIT ?
        ');
        
        $stmt->bind_param('ii', $category_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
}
