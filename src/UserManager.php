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
            return false;
        }
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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

    public function setSubscription($id, $status, $endDate = null, $plan = null) {
        $stmt = $this->db->prepare("UPDATE users SET subscription_status = ?, subscription_end = ?, subscription_plan = ? WHERE id = ?");
        return $stmt->execute([$status, $endDate, $plan, $id]);
    }

    public function updateProfile($id, $newName, $newPhone, $newEmail = null, $newPassword = null) {
        $stmt = $this->db->prepare("SELECT name, email, last_name_change FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) return ['success' => false, 'error' => 'Usuário não encontrado'];

        $updates = [];
        $params = [];

        if ($newName !== $user['name']) {
            if ($user['last_name_change']) {
                $lastChange = new DateTime($user['last_name_change']);
                $now = new DateTime();
                $diff = $now->diff($lastChange)->days;
                if ($diff < 15) {
                    return ['success' => false, 'error' => 'O nome só pode ser alterado uma vez a cada 15 dias. Faltam ' . (15 - $diff) . ' dias.'];
                }
            }
            $updates[] = 'name = ?';
            $params[] = $newName;
            $updates[] = 'last_name_change = ?';
            $params[] = date('Y-m-d H:i:s');
        }

        if ($newPhone !== null && $newPhone !== '') {
           $updates[] = 'phone = ?';
           $params[] = $newPhone;
        }

        if ($newEmail !== null && $newEmail !== $user['email']) {
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$newEmail, $id]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Este email já está em uso por outra conta.'];
            }
            $updates[] = 'email = ?';
            $params[] = $newEmail;
        }

        if (!empty($newPassword)) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updates[] = 'password = ?';
            $params[] = $hash;
        }

        if (count($updates) > 0) {
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            } catch (PDOException $e) {
                return ['success' => false, 'error' => 'Erro ao atualizar dados: ' . $e->getMessage()];
            }
        }

        return ['success' => true];
    }

    public function createPasswordReset($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) return false;

        $token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)); // 6 caracteres, ex: 9ASGQ7
        $createdAt = date('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $createdAt]);
        
        return $token;
    }

    public function resetPassword($email, $token, $newPassword) {
        $stmt = $this->db->prepare("SELECT email FROM password_resets WHERE email = ? AND token = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR) ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $token]);
        $reset = $stmt->fetch();
        
        if (!$reset) return ['success' => false, 'error' => 'Código inválido ou expirado.'];
        
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hash, $email]);
        
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        
        return ['success' => true];
    }
}
