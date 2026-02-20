<?php
// classes/Recital.php

class Recital {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('recitals', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT r.*, u.full_name as created_by_name
                FROM recitals r
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT r.*, u.full_name as created_by_name
                FROM recitals r
                LEFT JOIN users u ON r.created_by = u.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND r.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND r.category = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['difficulty_level'])) {
            $sql .= " AND r.difficulty_level = :difficulty_level";
            $params['difficulty_level'] = $filters['difficulty_level'];
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getByCategory($category, $activeOnly = true) {
        $sql = "SELECT * FROM recitals WHERE category = :category";
        $params = ['category' => $category];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY difficulty_level, title";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getCategoryCounts() {
        $sql = "SELECT category, COUNT(*) as count
                FROM recitals
                WHERE is_active = 1
                GROUP BY category";
        return $this->db->fetchAll($sql);
    }
    
    public function update($id, $data) {
        return $this->db->update('recitals', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('recitals', 'id = :id', ['id' => $id]);
    }
}