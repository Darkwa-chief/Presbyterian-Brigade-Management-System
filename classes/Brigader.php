<?php
// classes/Brigader.php

class Brigader {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function generateBrigadeId() {
        $year   = date('Y');
        $sql    = "SELECT COUNT(*) as count FROM brigaders WHERE brigade_id LIKE :pattern";
        $result = $this->db->fetchOne($sql, ['pattern' => "BRG{$year}%"]);
        $count  = $result['count'] + 1;
        return sprintf("BRG%s%04d", $year, $count);
    }
    
    public function create($data) {
        if (empty($data['brigade_id'])) {
            $data['brigade_id'] = $this->generateBrigadeId();
        }
        return $this->db->insert('brigaders', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT b.*, r.rank_name, r.rank_level, u.full_name as created_by_name
                FROM brigaders b
                LEFT JOIN ranks r ON b.rank_id = r.id
                LEFT JOIN users u ON b.created_by = u.id
                WHERE b.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql    = "SELECT b.*, r.rank_name, r.rank_level
                   FROM brigaders b
                   LEFT JOIN ranks r ON b.rank_id = r.id
                   WHERE 1=1";

        // ── Build params for filters only (NOT limit/offset) ────
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND b.unit = :unit";
            $params['unit'] = $filters['unit'];
        }

        if (!empty($filters['rank_id'])) {
            $sql .= " AND b.rank_id = :rank_id";
            $params['rank_id'] = $filters['rank_id'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND b.gender = :gender";
            $params['gender'] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.first_name LIKE :search
                        OR b.last_name  LIKE :search
                        OR b.brigade_id LIKE :search
                        OR b.email      LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY b.last_name, b.first_name";

        // ── Append LIMIT/OFFSET as integers directly in SQL ─────
        // This avoids PDO type-binding issues on strict production
        // servers where LIMIT must be a true integer not a string
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        // ── Only bind the filter params — no limit/offset params ─
        $stmt = $this->db->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count($filters = []) {
        $sql    = "SELECT COUNT(*) as count FROM brigaders b WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND b.unit = :unit";
            $params['unit'] = $filters['unit'];
        }

        if (!empty($filters['rank_id'])) {
            $sql .= " AND b.rank_id = :rank_id";
            $params['rank_id'] = $filters['rank_id'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND b.gender = :gender";
            $params['gender'] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.first_name LIKE :search
                        OR b.last_name  LIKE :search
                        OR b.brigade_id LIKE :search
                        OR b.email      LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }
    
    public function update($id, $data) {
        return $this->db->update('brigaders', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('brigaders', 'id = :id', ['id' => $id]);
    }
    
    public function getPromotionHistory($id) {
        $sql = "SELECT p.*,
                       r1.rank_name as from_rank_name,
                       r2.rank_name as to_rank_name,
                       u.full_name  as approved_by_name
                FROM promotions p
                LEFT JOIN ranks r1 ON p.from_rank_id = r1.id
                LEFT JOIN ranks r2 ON p.to_rank_id   = r2.id
                LEFT JOIN users u  ON p.approved_by  = u.id
                WHERE p.brigader_id = :id
                ORDER BY p.promotion_date DESC";
        return $this->db->fetchAll($sql, ['id' => $id]);
    }
    
    public function getAttendanceHistory($id, $limit = 10) {
        $sql = "SELECT a.*, e.event_name, e.event_date, e.event_type
                FROM attendance a
                JOIN events e ON a.event_id = e.id
                WHERE a.brigader_id = :id
                ORDER BY e.event_date DESC
                LIMIT " . (int)$limit;

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function uploadPassport($id, $file) {
        $allowedTypes = UPLOAD_ALLOWED_TYPES;
        $maxSize      = UPLOAD_MAX_SIZE;

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds maximum limit of 5MB.');
        }

        $uploadDir = UPLOAD_PATH . 'passports/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename  = 'passport_' . $id . '_' . time() . '.' . $extension;
        $filepath  = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = 'uploads/passports/' . $filename;
            $this->update($id, ['passport_photo' => $relativePath]);
            return $relativePath;
        }

        throw new Exception('Failed to upload file.');
    }
}