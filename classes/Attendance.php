<?php
// classes/Attendance.php

class Attendance {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function markAttendance($eventId, $brigaderId, $status, $markedBy, $notes = '') {
        // Check if attendance already exists
        $existing = $this->getAttendance($eventId, $brigaderId);
        
        if ($existing) {
            // Update existing attendance
            return $this->db->update('attendance', [
                'status' => $status,
                'notes' => $notes,
                'marked_by' => $markedBy,
                'marked_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            // Create new attendance
            return $this->db->insert('attendance', [
                'event_id' => $eventId,
                'brigader_id' => $brigaderId,
                'status' => $status,
                'notes' => $notes,
                'marked_by' => $markedBy
            ]);
        }
    }
    
    public function getAttendance($eventId, $brigaderId) {
        $sql = "SELECT * FROM attendance WHERE event_id = :event_id AND brigader_id = :brigader_id";
        return $this->db->fetchOne($sql, [
            'event_id' => $eventId,
            'brigader_id' => $brigaderId
        ]);
    }
    
    public function getEventAttendance($eventId) {
        $sql = "SELECT a.*, b.first_name, b.last_name, b.brigade_id, b.unit,
                       r.rank_name, u.full_name as marked_by_name
                FROM attendance a
                JOIN brigaders b ON a.brigader_id = b.id
                LEFT JOIN ranks r ON b.rank_id = r.id
                LEFT JOIN users u ON a.marked_by = u.id
                WHERE a.event_id = :event_id
                ORDER BY b.last_name, b.first_name";
        return $this->db->fetchAll($sql, ['event_id' => $eventId]);
    }
    
    public function getBrigaderAttendance($brigaderId, $filters = []) {
        $sql = "SELECT a.*, e.event_name, e.event_date, e.event_type
                FROM attendance a
                JOIN events e ON a.event_id = e.id
                WHERE a.brigader_id = :brigader_id";
        $params = ['brigader_id' => $brigaderId];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.event_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND e.event_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY e.event_date DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getAttendanceStats($eventId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                FROM attendance
                WHERE event_id = :event_id";
        return $this->db->fetchOne($sql, ['event_id' => $eventId]);
    }
    
    public function delete($id) {
        return $this->db->delete('attendance', 'id = :id', ['id' => $id]);
    }
}