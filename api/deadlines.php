<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../src/DeadlineManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$manager = new DeadlineManager();
$userId = $_SESSION['user_id'];

$pending = array_values($manager->getPending($userId));
$finalized = array_values($manager->getFinalized($userId));

$stats = [
    'total' => count($pending) + count($finalized),
    'pending' => count($pending),
    'finalized' => count($finalized)
];

echo json_encode([
    'pending' => $pending,
    'finalized' => $finalized,
    'stats' => $stats
]);
