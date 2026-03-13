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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!empty($input['id'])) {
        $success = $manager->delete($input['id'], $userId);
        echo json_encode(['success' => $success]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'ID missing']);
    exit;
}

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
