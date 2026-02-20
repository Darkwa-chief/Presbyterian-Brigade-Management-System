<?php
// classes/Rank.php

class Rank {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        return $this->db->insert('ranks', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM ranks WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getAll() {
        $sql = "SELECT * FROM ranks ORDER BY rank_level ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function update($id, $data) {
        return $this->db->update('ranks', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('ranks', 'id = :id', ['id' => $id]);
    }
    
    public function canPromote($currentRankId, $targetRankId) {
        $current = $this->getById($currentRankId);
        $target = $this->getById($targetRankId);
        
        if (!$current || !$target) {
            return false;
        }
        
        return $target['rank_level'] > $current['rank_level'];
    }
    
    public function promoteBrigader($brigaderId, $toRankId, $approvedBy, $notes = '') {
        $brigader = new Brigader();
        $brigaderData = $brigader->getById($brigaderId);
        
        if (!$brigaderData) {
            throw new Exception('Brigader not found');
        }
        
        $fromRankId = $brigaderData['rank_id'];
        
        if ($fromRankId && !$this->canPromote($fromRankId, $toRankId)) {
            throw new Exception('Invalid promotion: Target rank must be higher than current rank');
        }
        
        // Create promotion record
        $promotionData = [
            'brigader_id' => $brigaderId,
            'from_rank_id' => $fromRankId,
            'to_rank_id' => $toRankId,
            'promotion_date' => date('Y-m-d'),
            'approved_by' => $approvedBy,
            'notes' => $notes
        ];
        
        $promotionId = $this->db->insert('promotions', $promotionData);
        
        // Update brigader rank
        $brigader->update($brigaderId, ['rank_id' => $toRankId]);
        
        return $promotionId;
    }
}