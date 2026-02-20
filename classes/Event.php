<?php
// classes/Event.php

class Event {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('events', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT e.*, u.full_name as created_by_name
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                WHERE e.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT e.*, u.full_name as created_by_name,
                COUNT(DISTINCT a.id) as total_attendance
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN attendance a ON e.id = a.event_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['event_type'])) {
            $sql .= " AND e.event_type = :event_type";
            $params['event_type'] = $filters['event_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.event_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND e.event_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.event_date DESC, e.event_time DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        return $this->db->update('events', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('events', 'id = :id', ['id' => $id]);
    }
    
    public function getUpcoming($limit = 5) {
        $sql = "SELECT * FROM events 
                WHERE event_date >= CURDATE() AND status = 'scheduled'
                ORDER BY event_date ASC, event_time ASC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}