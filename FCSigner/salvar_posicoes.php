<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$hash = $data['hash'] ?? '';
$positions = $data['positions'] ?? [];

if (!$hash) {
    echo json_encode(['success' => false, 'error' => 'Hash inválido.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, user_id FROM documents WHERE document_hash = ? AND status = 'Pendente'");
$stmt->execute([$hash]);
$doc = $stmt->fetch();

if (!$doc || $doc['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Documento não encontrado ou sem permissão.']);
    exit;
}

$positionsJson = json_encode($positions);

$updateStmt = $pdo->prepare("UPDATE documents SET signature_positions = ? WHERE id = ?");
$updateStmt->execute([$positionsJson, $doc['id']]);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname($_SERVER['REQUEST_URI']), '/');

$linkSimulado = $protocol . "://" . $host . $base_dir . "/assinar_link.php?hash=" . $hash;

echo json_encode([
    'success' => true,
    'redirect' => "index.php?link_assinatura=" . urlencode($linkSimulado)
]);
