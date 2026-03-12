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

$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->execute([$doc_id, $user_id]);
$doc = $stmt->fetch();

if ($doc) {
    if (!empty($doc['document_hash'])) {
        $hash = $doc['document_hash'];

        $caminhoOriginal = __DIR__ . "/uploads/original_" . $hash . ".pdf";
        if (file_exists($caminhoOriginal))
            @unlink($caminhoOriginal);

        $caminhoQR = __DIR__ . "/uploads/qr_" . $hash . ".png";
        if (file_exists($caminhoQR))
            @unlink($caminhoQR);

        $caminhoLegado = __DIR__ . "/uploads/documento_" . $hash . ".pdf";
        if (file_exists($caminhoLegado))
            @unlink($caminhoLegado);

        $dirAssinado = __DIR__ . "/uploads/" . $hash;
        if (is_dir($dirAssinado)) {
            $files = glob($dirAssinado . '/*');
            foreach ($files as $file) {
                if (is_file($file))
                    @unlink($file);
            }
            @rmdir($dirAssinado);
        }
    }

    $stmtLogs = $pdo->prepare("DELETE FROM audit_logs WHERE document_id = ?");
    $stmtLogs->execute([$doc_id]);

    $stmtDelete = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmtDelete->execute([$doc_id]);

    header('Location: index.php?msg=Documento excluído com sucesso');
}
else {
    header('Location: index.php?msg=Documento não encontrado ou sem permissão');
}
exit;
