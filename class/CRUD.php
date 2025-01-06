<?php
require_once __DIR__ . '/Database.php';

abstract class CRUD {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    abstract public function create($data);
    abstract public function read($id = null);
    abstract public function update($id, $data);
    abstract public function delete($id);
} 