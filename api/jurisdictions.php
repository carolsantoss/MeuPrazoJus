<?php
header('Content-Type: application/json');

$file = __DIR__ . '/../data/jurisdictions.json';

if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo json_encode(['error' => 'Data file not found']);
}
