<?php
// classes/Notification.php

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('notifications', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT n.*, u.full_name as created_by_name
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT n.*, u.full_name as created_by_name
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['is_active'])) {
            $sql .= " AND n.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        if (!empty($filters['notification_type'])) {
            $sql .= " AND n.notification_type = :notification_type";
            $params['notification_type'] = $filters['notification_type'];
        }
        
        $sql .= " ORDER BY n.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUserNotifications($userId, $userRole, $unit = null) {
        $sql = "SELECT n.*, 
                       CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END as is_read,
                       u.full_name as created_by_name
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = :user_id
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.is_active = 1
                AND (n.target_role = 'all' OR n.target_role = :user_role)";
        
        $params = [
            'user_id' => $userId,
            'user_role' => $userRole
        ];
        
        if ($unit) {
            $sql .= " AND (n.target_unit IS NULL OR n.target_unit = :unit)";
            $params['unit'] = $unit;
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT 50";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUnreadCount($userId, $userRole, $unit = null) {
        $sql = "SELECT COUNT(*) as count
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = :user_id
                WHERE n.is_active = 1
                AND nr.id IS NULL
                AND (n.target_role = 'all' OR n.target_role = :user_role)";
        
        $params = [
            'user_id' => $userId,
            'user_role' => $userRole
        ];
        
        if ($unit) {
            $sql .= " AND (n.target_unit IS NULL OR n.target_unit = :unit)";
            $params['unit'] = $unit;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'];
    }
    
    public function markAsRead($notificationId, $userId) {
        try {
            return $this->db->insert('notification_reads', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            // Already read, ignore duplicate key error
            return true;
        }
    }
    
    public function markAllAsRead($userId) {
        $notifications = $this->getUserNotifications($userId, $_SESSION['user_role']);
        foreach ($notifications as $notification) {
            if (!$notification['is_read']) {
                $this->markAsRead($notification['id'], $userId);
            }
        }
        return true;
    }
    
    public function update($id, $data) {
        return $this->db->update('notifications', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('notifications', 'id = :id', ['id' => $id]);
    }
}