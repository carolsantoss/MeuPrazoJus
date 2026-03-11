<?php
session_start();

require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getInstance()->getConnection();

$doc_hash = $_GET['hash'] ?? '';
if (!$doc_hash) {
    die("Link inválido.");
}

$stmtId = $pdo->prepare("SELECT d.id, d.status, u.name as contratante FROM documents d JOIN users u ON d.user_id = u.id WHERE d.document_hash = ?");
$stmtId->execute([$doc_hash]);
$docData = $stmtId->fetch();

if (!$docData) {
    die("Documento não encontrado.");
}
if ($docData['status'] == 'Assinado') {
    die("Este documento já foi assinado.");
}

$contratante = $docData['contratante'] ?? 'Titular da Conta';

if (isset($_GET['view_pdf']) && $_GET['view_pdf'] == '1') {
    $caminhoPdf = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
    if (file_exists($caminhoPdf)) {
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($caminhoPdf));
        header('Content-Disposition: inline; filename="documento.pdf"');
        readfile($caminhoPdf);
        exit;
    } else {
        http_response_code(404);
        die("Documento original não encontrado no servidor.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $contratado = $_POST['nome_signatario'] ?? 'Signatário';
    $assinatura_base64 = $_POST['signature_image'] ?? null;

    require 'vendor/autoload.php';
    require_once 'app/Services/DocumentService.php';

    $caminhoOriginal = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
    if(!file_exists($caminhoOriginal)) {
        die("Arquivo original não encontrado para gerar a assinatura.");
    }
    
    $documentService = new \App\Services\DocumentService();
    $nomeArquivoFinal = $documentService->assinarDocumento(
        $caminhoOriginal, 
        $doc_hash, 
        $contratante, 
        $contratado, 
        $cpf, 
        $celular,
        $assinatura_base64
    );

    $stmtUpdate = $pdo->prepare("UPDATE documents SET status = 'Assinado', file_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmtUpdate->execute(['/uploads/' . $nomeArquivoFinal, $docData['id']]);
    
    $stmtLog = $pdo->prepare("INSERT INTO audit_logs (document_id, action_type, actor_name, actor_cpf, actor_phone, ip_address, geolocation) VALUES (?, 'Assinou', ?, ?, ?, ?, 'Sistema')");
    $stmtLog->execute([$docData['id'], $contratado, $cpf, $celular, $_SERVER['REMOTE_ADDR']]);

    header("Location: index.php?novo_doc=" . urlencode("uploads/" . $nomeArquivoFinal));
    exit();
}
?>
<?php require_once __DIR__ . '/app/Views/assinar_link.view.php'; ?>