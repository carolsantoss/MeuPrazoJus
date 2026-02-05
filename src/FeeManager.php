<?php
require_once __DIR__ . '/Database.php';

class FeeManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save($userId, $data) {
        $id = uniqid('fee_');
        $stmt = $this->db->prepare("INSERT INTO fees (id, user_id, total, installments, startDate, lawyers) VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $id,
            $userId,
            $data['total'],
            $data['installments'],
            $data['startDate'],
            json_encode($data['lawyers'])
        ]);
        
        return array_merge(['id' => $id, 'created_at' => date('Y-m-d H:i:s')], $data);
    }

    public function getByUser($userId, $page = 1, $limit = 10) {
        // Count total
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM fees WHERE user_id = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetchColumn();

        // Get items with pagination
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM fees WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            $item['lawyers'] = json_decode($item['lawyers'], true);
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => (int)$page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
}
