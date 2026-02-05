<?php
session_start();
header('Content-Type: application/json');

require_once '../src/FeeManager.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$manager = new FeeManager();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    
    $result = $manager->save($userId, $input);
    echo json_encode(['success' => true, 'data' => $result]);

} else if ($method === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    $data = $manager->getByUser($userId, $page, $limit);
    echo json_encode($data);
}
