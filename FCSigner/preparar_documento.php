<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$doc_hash = $_GET['hash'] ?? '';
if (!$doc_hash) {
    die("Hash não fornecido.");
}

$stmtId = $pdo->prepare("SELECT id, document_hash, status, user_id FROM documents WHERE document_hash = ?");
$stmtId->execute([$doc_hash]);
$docData = $stmtId->fetch();

if (!$docData || $docData['user_id'] != $_SESSION['user_id']) {
    die("Documento não encontrado ou sem permissão.");
}

if ($docData['status'] !== 'Pendente') {
    die("Este documento não está mais pendente.");
}

$caminhoPdf = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
$pdf_base64 = '';
if (file_exists($caminhoPdf)) {
    $pdf_base64 = base64_encode(file_get_contents($caminhoPdf));
} else {
    die("Arquivo PDF não encontrado no servidor.");
}

require_once __DIR__ . '/app/Views/preparar_documento.view.php';
