<?php
// classes/User.php

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username AND status = 'active' LIMIT 1";
        $user = $this->db->fetchOne($sql, ['username' => $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->insert('users', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT id, username, email, full_name, role, status, created_at FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->update('users', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('users', 'id = :id', ['id' => $id]);
    }
    
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('users', ['password' => $hashedPassword], 'id = :id', ['id' => $id]);
    }
}