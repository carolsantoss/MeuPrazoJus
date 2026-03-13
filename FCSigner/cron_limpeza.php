<?php
// cron_limpeza.php
// Este script pode ser rodado via cron (ex: 0 0 * * * php /var/www/html/FCSigner/cron_limpeza.php)
// para excluir os PDFs físicos que têm mais de 3 meses de assinatura, economizando espaço em disco.
// O registro do banco de dados e os hashes (trilha de auditoria) são mantidos para validação perpétua.

require_once __DIR__ . '/../src/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    $stmt = $pdo->prepare("SELECT id, file_path FROM documents WHERE status = 'Assinado' AND file_path IS NOT NULL AND file_path != '' AND updated_at < DATE_SUB(NOW(), INTERVAL 3 MONTH)");
    $stmt->execute();
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $excluidos = 0;
    foreach ($documentos as $doc) {
        $caminhoCompleto = __DIR__ . '/..' . $doc['file_path'];
        
        if (file_exists($caminhoCompleto)) {
            @unlink($caminhoCompleto);
        }
        
        $caminhoAlt = __DIR__ . str_replace('/uploads/', '/uploads/', $doc['file_path']);
        if (file_exists($caminhoAlt)) {
            @unlink($caminhoAlt);
        }

        $stmtUpdate = $pdo->prepare("UPDATE documents SET file_path = NULL WHERE id = ?");
        $stmtUpdate->execute([$doc['id']]);
        $excluidos++;
        $stmtUpdate = $pdo->prepare("UPDATE documents SET file_path = NULL WHERE id = ?");
        $stmtUpdate->execute([$doc['id']]);
        $excluidos++;
    }
    
    echo "Limpeza concluída. $excluidos PDF(s) com mais de 3 meses foram removidos fisicamente.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
