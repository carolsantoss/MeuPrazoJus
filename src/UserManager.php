<?php
require_once __DIR__ . '/Database.php';

class UserManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($email, $password, $name = '', $phone = '') {
        $id = uniqid('usr_');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare("INSERT INTO users (id, email, password, name, phone, subscription_status, calculations_count) VALUES (?, ?, ?, ?, ?, 'free', 0)");
            $stmt->execute([$id, $email, $hash, $name, $phone]);
            return [
                'id' => $id, 
                'email' => $email, 
                'name' => $name, 
                'phone' => $phone,
                'subscription_status' => 'free',
                'calculations_count' => 0
            ];
        } catch (PDOException $e) {
            // Likely duplicate email
            return false;
        }
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check expiration
            if ($user['subscription_status'] === 'premium' && !empty($user['subscription_end'])) {
                $endDate = new DateTime($user['subscription_end']);
                $now = new DateTime();
                if ($now > $endDate) {
                    $this->setSubscription($user['id'], 'free', null);
                    $user['subscription_status'] = 'free';
                    $user['subscription_end'] = null;
                }
            }
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && $user['subscription_status'] === 'premium' && !empty($user['subscription_end'])) {
            $endDate = new DateTime($user['subscription_end']);
            $now = new DateTime();
            if ($now > $endDate) {
                $this->setSubscription($id, 'free', null);
                $user['subscription_status'] = 'free';
                $user['subscription_end'] = null;
            }
        }
        return $user;
    }

    public function incrementUsage($id) {
        $stmt = $this->db->prepare("UPDATE users SET calculations_count = calculations_count + 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        $stmt = $this->db->prepare("SELECT calculations_count FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function setSubscription($id, $status, $endDate = null) {
        $stmt = $this->db->prepare("UPDATE users SET subscription_status = ?, subscription_end = ? WHERE id = ?");
        return $stmt->execute([$status, $endDate, $id]);
    }
}
