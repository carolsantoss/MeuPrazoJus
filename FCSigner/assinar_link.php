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

$caminhoPdf = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
$pdf_base64 = '';
if (file_exists($caminhoPdf)) {
    $pdf_base64 = base64_encode(file_get_contents($caminhoPdf));
}
else {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $contratado = $_POST['nome_signatario'] ?? 'Signatário';
    $assinatura_base64 = $_POST['signature_image'] ?? null;
    
    // O BunkerWeb/Cloudflare barra POSTs com strings muito longas. 
    // Em vez de enviar o Base64 enorme todo pelo form hidden, 
    // vamos criar um mecanismo pro backend pegar de um temp post futuro se o body estourar,
    // ou garantir que ele aceite o base64 (que agora é jpeg e 90% menor).
    if ($assinatura_base64 && strpos($assinatura_base64, 'data:') !== 0) {
        $assinatura_base64 = 'data:image/jpeg;base64,' . $assinatura_base64;
    }

    require 'vendor/autoload.php';
    require_once 'app/Services/DocumentService.php';

    try {
        $caminhoOriginal = __DIR__ . "/uploads/original_" . $doc_hash . ".pdf";
        if (!file_exists($caminhoOriginal)) {
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
    } catch (\Throwable $e) {
        http_response_code(500);
        die("ERRO FATAL PHP: " . $e->getMessage() . " na linha " . $e->getLine() . " de " . $e->getFile());
    }
}
?>
<?php require_once __DIR__ . '/app/Views/assinar_link.view.php'; ?>