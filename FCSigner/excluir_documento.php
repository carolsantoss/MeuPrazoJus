<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php?msg=ID inválido');
    exit;
}

$user_id = $_SESSION['user_id'];
$doc_id = (int)$_GET['id'];

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

// Verifica se o documento pertence ao usuário
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->execute([$doc_id, $user_id]);
$doc = $stmt->fetch();

if ($doc) {
    // Apagar o arquivo físico PDF se existir na pasta uploads
    if (!empty($doc['document_hash'])) {
        $caminhoOriginal = __DIR__ . "/uploads/original_" . $doc['document_hash'] . ".pdf";
        if (file_exists($caminhoOriginal)) {
            @unlink($caminhoOriginal);
        }
        
        $caminhoAssinado = __DIR__ . "/uploads/documento_" . $doc['document_hash'] . ".pdf";
        if (file_exists($caminhoAssinado)) {
            @unlink($caminhoAssinado);
        }
    }

    // Apagar logs do banco de dados atrelados ao documento (se houver chave estrangeira em cascata, isso pode ser redundante)
    $stmtLogs = $pdo->prepare("DELETE FROM audit_logs WHERE document_id = ?");
    $stmtLogs->execute([$doc_id]);

    // Apagar o documento do banco de dados
    $stmtDelete = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmtDelete->execute([$doc_id]);

    header('Location: index.php?msg=Documento excluído com sucesso');
} else {
    header('Location: index.php?msg=Documento não encontrado ou sem permissão');
}
exit;
