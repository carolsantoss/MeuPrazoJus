<?php 
include 'src/auth.php'; 
$dataFile = 'data/requests.json';
$requests = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizador de Provas | MeuPrazoJus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
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
                    <h1 style="text-align: center;">Solicitação de Documentos</h1>
                    <p class="subtitle" style="text-align: center;">Organize a coleta de provas com seus clientes.</p>
                </header>
        
                <div class="content-wrapper">
                    <!-- New Request Form -->
                    <div class="card">
                        <h3>Nova Solicitação</h3>
                        <form id="new-request-form">
                            <div class="form-group">
                                <label>Nome do Cliente</label>
                                <input type="text" id="client_name" required placeholder="Ex: Maria da Silva">
                            </div>
                            
                            <div class="form-group">
                                <label>Documentos Necessários</label>
                                <div class="input-group-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="RG/CPF"> RG/CPF</label>
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="Comp. Residência"> Comp. Residência</label>
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="Carteira de Trabalho"> Carteira de Trabalho</label>
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="Extrato Bancário"> Extrato Bancário</label>
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="Certidão de Casamento"> Certidão de Casamento</label>
                                    <label class="glass-checkbox-card"><input type="checkbox" name="docs[]" value="Boletim de Ocorrência"> Boletim de Ocorrência</label>
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-primary">Gerar Link de Solicitação</button>
                        </form>
                    </div>
        
                    <!-- List -->
                    <div style="margin-top: 2rem;">
                        <h3>Solicitações Ativas</h3>
                        <div id="requests-list">
                            <?php if (empty($requests)): ?>
                                <p class="text-muted">Nenhuma solicitação criada ainda.</p>
                            <?php else: ?>
                                <?php foreach (array_reverse($requests) as $id => $req): ?>
                                    <div class="request-item-glass">
                                        <div>
                                            <strong style="color: white;"><?= htmlspecialchars($req['client_name']) ?></strong><br>
                                            <small class="text-muted"><?= count($req['docs']) ?> documentos solicitados</small>
                                        </div>
                                        <div style="display:flex; gap:10px; align-items:center;">
                                            <span class="req-status status-<?= $req['status'] ?>">
                                                <?= $req['status'] == 'pending' ? 'Pendente' : 'Concluído' ?>
                                            </span>
                                            <button onclick="copyLink('<?= $id ?>')" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--glass-border);">Copiar Link</button>
                                            <a href="doc_view.php?id=<?= $id ?>" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--glass-border);">Ver Arquivos</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script>
document.getElementById('new-request-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    btn.innerHTML = 'Gerando...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    // Convert checkbox docs[] to array
    const data = {
        client_name: formData.get('client_name'),
        docs: []
    };
    formData.getAll('docs[]').forEach(d => data.docs.push(d));

    try {
        const res = await fetch('api/save_request.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert("Solicitação criada! O link é: " + result.link);
            window.location.reload();
        } else {
            alert("Erro ao salvar.");
        }
    } catch(err) {
        console.error(err);
        alert("Erro de conexão.");
    } finally {
        btn.innerHTML = 'Gerar Link de Solicitação';
        btn.disabled = false;
    }
});

function copyLink(id) {
    // Assuming the site is hosted at root or similar. 
    // Construct full URL dynamically
    const url = window.location.origin + window.location.pathname.replace('doc_requests.php', 'doc_upload.php?id=' + id);
    navigator.clipboard.writeText(url).then(() => alert("Link copiado: " + url));
}
</script>
</body>
</html>
