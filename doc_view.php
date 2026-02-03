<?php
include 'src/auth.php';
$id = $_GET['id'] ?? '';
$dataFile = 'data/requests.json';
$requests = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if (!isset($requests[$id])) {
    die("Solicitação não encontrada.");
}
$req = $requests[$id];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Arquivos: <?= htmlspecialchars($req['client_name']) ?> | MeuPrazoJus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <div class="logo">MeuPrazoJus</div>
                <div>
                    <a href="subscription.php" class="btn btn-ghost">Planos</a>
                    <a href="#" class="btn btn-primary" onclick="logout()">Sair</a>
                </div>
            </nav>
        </div>
    </header>

    <script>
     async function logout() {
        await fetch('api/auth.php?action=logout');
        window.location.href = 'login.php';
     }
    </script>
    
    <main>
        <div class="dashboard-container">
            <aside class="sidebar">
                <div class="user-info">
                    <h3>Bem-vindo!</h3>
                    <p>Gerencie seus prazos.</p>
                </div>
                <nav class="side-nav">
                    <a href="index.php" class="nav-item">📊 Prazos</a>
                    <a href="fees.php" class="nav-item">💰 Honorários</a>
                    <a href="doc_requests.php" class="nav-item active">📂 Documentos</a>
                    <a href="subscription.php" class="nav-item">⭐ Assinatura</a>
                </nav>
            </aside>
        
            <div class="dash-content centered">
                <header class="top-bar">
                    <div style="text-align: center;">
                        <a href="doc_requests.php" style="text-decoration:none; color:var(--text-muted); font-size: 0.9rem;">&larr; Voltar para solicitações</a>
                        <h1 style="text-align: center; margin-top: 0.5rem;">Arquivos de <?= htmlspecialchars($req['client_name']) ?></h1>
                    </div>
                </header>
        
                <div class="content-wrapper">
                    <div class="card">
                        <h3>Arquivos Recebidos</h3>
                        <?php if (empty($req['uploads'])): ?>
                            <p class="text-muted">Nenhum arquivo enviado pelo cliente ainda.</p>
                        <?php else: ?>
                            <ul class="file-list" style="list-style: none; margin-top: 1rem;">
                            <?php foreach ($req['uploads'] as $up): ?>
                                <li style="padding: 1.25rem 0; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="color: white;"><?= htmlspecialchars($up['doc_type']) ?></strong>
                                        <br><small class="text-muted">Enviado em <?= date('d/m/Y H:i', strtotime($up['uploaded_at'])) ?></small>
                                    </div>
                                    <a href="uploads/<?= $id ?>/<?= $up['filename'] ?>" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--glass-border);">Baixar</a>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top:2rem;" class="card">
                        <h4 style="color: white;">Status: <?= $req['status'] == 'completed' ? '✅ Tudo entregue' : '⏳ Aguardando documentos' ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
