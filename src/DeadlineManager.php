<?php
// src/DeadlineManager.php

class DeadlineManager {
    private $file;
    private $deadlines = [];

    public function __construct() {
        $this->file = __DIR__ . '/../data/deadlines.json';
        if (file_exists($this->file)) {
            $this->deadlines = json_decode(file_get_contents($this->file), true) ?? [];
        }
    }

    private function saveFile() {
        file_put_contents($this->file, json_encode($this->deadlines, JSON_PRETTY_PRINT));
    }

    public function save($userId, $data) {
        // $data includes: startDate, days, type, result_date, description, created_at
        $id = uniqid('dl_');
        $record = array_merge([
            'id' => $id,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'active' // active, archived
        ], $data);
        
        $this->deadlines[] = $record;
        $this->saveFile();
        return $record;
    }

    public function getByUser($userId) {
        $userDeadlines = [];
        foreach ($this->deadlines as $d) {
            if ($d['user_id'] === $userId) {
                $userDeadlines[] = $d;
            }
        }
        
        // Sort by End Date (Ascending)
        usort($userDeadlines, function($a, $b) {
            return strtotime($a['end_date']) - strtotime($b['end_date']);
        });

        return $userDeadlines;
    }

    public function getPending($userId) {
        $today = date('Y-m-d');
        $all = $this->getByUser($userId);
        return array_filter($all, function($d) use ($today) {
            return $d['end_date'] >= $today;
        });
    }

    public function getFinalized($userId) {
        $today = date('Y-m-d');
        $all = $this->getByUser($userId);
        return array_filter($all, function($d) use ($today) {
            return $d['end_date'] < $today;
        });
    }
}
