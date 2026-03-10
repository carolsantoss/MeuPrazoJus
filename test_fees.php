<?php
require_once __DIR__ . '/src/FeeManager.php';
session_start();
$_SESSION['user_id'] = 1;

try {
    $manager = new FeeManager();
    $data = $manager->getByUser(1, 1, 10);
    echo json_encode($data);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
