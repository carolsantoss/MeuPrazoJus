<?php
require_once __DIR__ . '/Database.php';

class DeadlineManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save($userId, $data) {
        $id = uniqid('dl_');
        $stmt = $this->db->prepare("INSERT INTO deadlines (
            id, user_id, start_date, end_date, days, type, state, city, cityName, matter, vara, deadlineType, description, location
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $id, 
            $userId, 
            $data['start_date'], 
            $data['end_date'], 
            $data['days'], 
            $data['type'], 
            $data['state'] ?? null, 
            $data['city'] ?? null, 
            $data['cityName'] ?? null, 
            $data['matter'] ?? null, 
            $data['vara'] ?? null, 
            $data['deadlineType'] ?? null, 
            $data['description'] ?? null, 
            $data['location'] ?? null
        ]);
        
        return array_merge(['id' => $id], $data);
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM deadlines WHERE user_id = ? ORDER BY end_date ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getPending($userId) {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM deadlines WHERE user_id = ? AND end_date >= ? ORDER BY end_date ASC");
        $stmt->execute([$userId, $today]);
        return $stmt->fetchAll();
    }

    public function getFinalized($userId) {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM deadlines WHERE user_id = ? AND end_date < ? ORDER BY end_date ASC");
        $stmt->execute([$userId, $today]);
        return $stmt->fetchAll();
    }
}
