<?php

class UserManager {
    private $file;
    private $users = [];

    public function __construct() {
        $this->file = __DIR__ . '/../data/users.json';
        if (file_exists($this->file)) {
            $this->users = json_decode(file_get_contents($this->file), true) ?? [];
        }
    }

    private function save() {
        file_put_contents($this->file, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function register($email, $password, $name = '', $phone = '') {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) {
                return false; // Exists
            }
        }
        
        $newUser = [
            'id' => uniqid(),
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'subscription_status' => 'free',
            'calculations_count' => 0
        ];
        
        $this->users[] = $newUser;
        $this->save();
        return $newUser;
    }

    public function login($email, $password) {
        foreach ($this->users as &$u) {
            if ($u['email'] === $email) {
                if (password_verify($password, $u['password'])) {
                    return $u;
                }
            }
        }
        return false;
    }

    public function getUser($id) {
        foreach ($this->users as $u) {
            if ($u['id'] === $id) return $u;
        }
        return null;
    }

    public function incrementUsage($id) {
        foreach ($this->users as &$u) {
            if ($u['id'] === $id) {
                if (!isset($u['calculations_count'])) $u['calculations_count'] = 0;
                $u['calculations_count']++;
                $this->save();
                return $u['calculations_count'];
            }
        }
        return 0;
    }

    public function setSubscription($id, $status) {
        foreach ($this->users as &$u) {
            if ($u['id'] === $id) {
                $u['subscription_status'] = $status;
                $this->save();
                return true;
            }
        }
        return false;
    }
}
