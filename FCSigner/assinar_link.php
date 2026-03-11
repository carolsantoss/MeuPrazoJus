<?php
session_start();

$doc_hash = $_GET['hash'] ?? '';
$nome_pre_preenchido = $_GET['contratado'] ?? ''; 

if (!$doc_hash) {
    die("Link inválido.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $celular = $_POST['celular'] ?? '';
    $contratante = $_GET['contratante'] ?? 'Contratante';
    $contratado = $nome_pre_preenchido;

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
        $celular
    );

    require_once __DIR__ . '/../src/Database.php';
    $pdo = Database::getInstance()->getConnection();
    
    $stmtId = $pdo->prepare("SELECT id FROM documents WHERE document_hash = ?");
    $stmtId->execute([$doc_hash]);
    $docData = $stmtId->fetch();
    
    if ($docData) {
        $stmtUpdate = $pdo->prepare("UPDATE documents SET status = 'Assinado', file_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtUpdate->execute(['/uploads/' . $nomeArquivoFinal, $docData['id']]);
        
        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (document_id, action_type, actor_name, actor_cpf, actor_phone, ip_address, geolocation) VALUES (?, 'Assinou', ?, ?, ?, ?, 'Sistema')");
        $stmtLog->execute([$docData['id'], $contratado, $cpf, $celular, $_SERVER['REMOTE_ADDR']]);
    }

    header("Location: index.php?novo_doc=" . urlencode("uploads/" . $nomeArquivoFinal));
    exit();
}
?>
<?php require_once __DIR__ . '/app/Views/assinar_link.view.php'; ?>