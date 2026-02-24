<?php
session_start();
header('Content-Type: application/json');

require_once '../src/FeeManager.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    if (ob_get_length()) ob_clean();
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$manager = new FeeManager();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    
    try {
        $result = $manager->save($userId, $input);
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true, 'data' => $result]);
    } catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }

} else if ($method === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    try {
        $data = $manager->getByUser($userId, $page, $limit);
        if (ob_get_length()) ob_clean();
        echo json_encode($data);
    } catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
