<?php 
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Embedder-Policy: require-corp");

session_start(); 
if (isset($_SESSION['user_id'])) {
    require_once 'src/UserManager.php';
    $um = new UserManager();
    $u = $um->getUserById($_SESSION['user_id']);
    if ($u) {
        $_SESSION['calculations'] = $u['calculations_count'];
        $_SESSION['is_subscribed'] = ($u['subscription_status'] === 'premium');
        $_SESSION['subscription_end'] = $u['subscription_end'] ?? null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeuPrazoJus - Calculadora de Prazos Processuais</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js" defer></script>
    <?php include 'src/google_adsense.php'; ?>
</head>
<body>
    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="subscription" class="btn btn-ghost">Planos</a>
                        <a href="#" class="btn btn-primary" onclick="logout()">Sair</a>
                    <?php else: ?>
                        <a href="login" class="btn btn-ghost">Entrar</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    


    <main>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <div class="landing-hero">
                <h1>Domine seus Prazos</h1>
                <p class="subtitle">Simule prazos rapidamente ou faça login para gerenciar seus processos com segurança.</p>
                
                <div class="calculator-card" style="margin-bottom: 4rem;">
                    <h3 style="text-align:center; color:white; margin-bottom:1rem;">Faça uma simulação gratuita</h3>
                    <form id="calc-form">
                        <div class="form-group">
                            <label for="state">Estado (UF)</label>
                            <select id="state" name="state">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="city-group" style="display:none;">
                            <label for="city">Município (Feriados Locais)</label>
                            <select id="city" name="city">
                                <option value="">Selecione o Estado primeiro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="matter">Matéria / Área</label>
                            <select id="matter" name="matter">
                                <option value="">Geral</option>
                            </select>
                        </div>

                        <div class="form-group" id="court-group" style="display:none;">
                            <label for="court">Tribunal / Regional</label>
                            <select id="court" name="court">
                                <option value="">Selecione o Estado primeiro</option>
                            </select>
                        </div>

                        <div class="form-group" id="vara-group">
                            <label for="vara">Vara / Juízo</label>
                            <select id="vara" name="vara">
                                <option value="">Geral</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="process-type">Processo</label>
                            <select id="process-type" name="process-type">
                                <option value="electronic">Eletrônico</option>
                                <option value="physical">Físico</option>
                            </select>
                        </div>

                        <div class="form-group" id="deadline-type-group" style="display:none;">
                            <label for="deadline-type">Tipo de Prazo Legal / Lei</label>
                            <select id="deadline-type" name="deadline-type">
                                <option value="">Selecione...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Data da Publicação / Intimação</label>
                            <input type="date" id="start_date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="days">Prazo (em dias)</label>
                            <input type="number" id="days" name="days" placeholder="Ex: 5, 10, 15" required min="1">
                        </div>

                        <div class="form-group" style="text-align: center;">
                            <label>Tipo de Contagem</label>
                            <div style="display: flex; gap: 1rem; margin-top: 0.5rem; justify-content: center;">
                                <label style="color: white; cursor: pointer;">
                                    <input type="radio" name="type" value="working" id="type-working" checked> 
                                    Dias Úteis (Novo CPC)
                                </label>
                                <label style="color: white; cursor: pointer;">
                                    <input type="radio" name="type" value="calendar" id="type-calendar"> 
                                    Dias Corridos
                                </label>
                            </div>
                        </div>

                        <div id="deadline-disclaimer" style="display:none; background: rgba(197, 160, 89, 0.05); padding: 0.85rem; border-radius: 0.5rem; margin-bottom: 1.25rem; border-left: 4px solid var(--primary); font-size: 0.9rem; color: var(--text-muted);">
                            ⚠️ <strong style="color: var(--text-main);">Aviso:</strong> Confira se o prazo está correto. A estimativa foi baseada na lei federal.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Calcular Prazo</button>
                    </form>

                    <div class="limit-alert"></div>

                    <div id="results-area">
                        <div class="result-main">
                            <div class="result-label">Prazo Final</div>
                            <div class="result-date" id="result-date">...</div>
                        </div>
                        
                        <div id="guest-warning" style="display:none; background: rgba(197, 160, 89, 0.1); color: #dfc690; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid rgba(197, 160, 89, 0.3); text-align: center;">
                            <p style="margin-bottom: 0.5rem; font-weight: 600;">⚠️ Atenção: Este cálculo não foi salvo!</p>
                            <p style="font-size: 0.95rem; margin-bottom: 1.25rem;">Para salvar seu histórico e gerenciar prazos com segurança, crie sua conta agora.</p>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <a href="register" class="btn btn-primary">Criar Conta Grátis</a>
                                <a href="login" class="btn btn-ghost" style="color: #dfc690; border: 1px solid rgba(197, 160, 89, 0.3);">Entrar</a>
                            </div>
                        </div>

                        <div class="log-container" id="log-details">
                        </div>
                        
                         <a href="#" target="_blank" id="gcal-link" class="btn gcal-btn">
                            Adicionar ao Google Agenda
                        </a>
                         <button id="btn-pdf" class="btn btn-secondary" style="width:100%; margin-top:10px;">📄 Baixar PDF Detalhado</button>
                    </div>
                </div>

                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--text-main); font-size: 2.25rem;">Benefícios de ser um Assinante</h2>
                <div class="features-row">
                    <div class="feature-box">
                        <h3>☁️ Histórico na Nuvem</h3>
                        <p>Salve e gerencie todos os seus prazos com segurança de qualquer lugar do mundo.</p>
                    </div>
                     <div class="feature-box">
                        <h3>💰 Módulo de Honorários</h3>
                        <p>Calcule cobranças de forma simples e faça a gestão financeira da sua advocacia.</p>
                    </div>
                     <div class="feature-box">
                        <h3>🚀 Ferramentas Exclusivas</h3>
                        <p>Integração com Google Agenda e conversor inteligente de PDF e áudio para texto.</p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="dashboard-container">
                <aside class="sidebar">
                    <div class="user-info">
                        <?php 
                            $fullName = $_SESSION['user_name'] ?? 'Usuário';
                            $firstName = explode(' ', trim($fullName))[0];
                            $isPremium = $_SESSION['is_subscribed'] ?? false;
                        ?>
                        <h3>Olá, <?= htmlspecialchars($firstName) ?>!</h3>
                        <p>Gerencie seus prazos.</p>
                        <?php if ($isPremium && !empty($_SESSION['subscription_end'])): ?>
                            <div style="font-size: 0.8rem; color: #aaa; margin-top: 15px;">
                                <?php 
                                    $endDate = new DateTime($_SESSION['subscription_end']);
                                    echo "Vence em: " . $endDate->format('d/m/Y');
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <nav class="side-nav">
                        <?php 
                            $calcCount = $_SESSION['calculations'] ?? 0;
                            $limit = 5;
                        ?>

                        <a href="#" class="nav-item active" onclick="showSection('dashboard'); return false;">📊 Prazos</a>
                        <a href="#" class="nav-item" onclick="showSection('new-deadline'); return false;">➕ Novo Prazo</a>
                        <a href="#" class="nav-item" onclick="showSection('history'); return false;">📜 Histórico</a>
                        
                        <?php if ($isPremium): ?>
                            <a href="#" class="nav-item" onclick="showSection('fees'); return false;">💰 Honorários</a>
                            <a href="#" class="nav-item" onclick="showSection('converter'); return false;">🔄 Conversor PDF/Áudio</a>
                        <?php else: ?>
                            <a href="#" class="nav-item disabled-link" title="Assine para ter acesso" onclick="return false;">🔒 Honorários</a>
                            <a href="#" class="nav-item disabled-link" title="Assine para ter acesso" onclick="return false;">🔒 Conversor</a>
                        <?php endif; ?>

                        <a href="subscription" class="nav-item">⭐ Assinatura</a>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.05);">
                            <a href="#" class="nav-item" onclick="showSection('profile'); return false;">👤 Editar Perfil</a>
                        </div>
                    </nav>
                </aside>

                <div class="dash-content">
                    
                    <div id="section-dashboard" class="dash-section">
                        <?php
                        if ($isPremium && !empty($_SESSION['subscription_end'])) {
                            $endDate = new DateTime($_SESSION['subscription_end']);
                            $now = new DateTime();
                            $daysRemaining = $now->diff($endDate)->days;
                            $invert = $now->diff($endDate)->invert;

                            if (!$invert && $daysRemaining <= 15) {
                                echo '<div style="background: rgba(197, 160, 89, 0.1); border: 1px solid rgba(197, 160, 89, 0.3); color: #dfc690; padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                    <span style="font-size: 1.5rem;">⚠️</span>
                                    <div>
                                        <strong style="color: var(--text-main);">Sua assinatura vence em ' . $daysRemaining . ' dias!</strong><br>
                                        <a href="subscription" style="color: var(--primary); text-decoration: none; font-weight: 500; font-size: 0.95rem;">Renove agora para não perder o acesso &rarr;</a>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                        <h2>Meus Prazos</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3>Pendentes</h3>
                                <span class="stat-value" id="count-pending">0</span>
                            </div>
                            <div class="stat-card">
                                <h3>Finalizados</h3>
                                <span class="stat-value" id="count-finalized">0</span>
                            </div>
                        </div>

                        <div class="lists-grid">
                            <div class="list-card">
                                <h3>⏳ Pendentes</h3>
                                <ul class="deadline-list" id="list-pending">
                                    <li>Carregando...</li>
                                </ul>
                            </div>
                            <div class="list-card">
                                <h3>✅ Finalizados</h3>
                                <ul class="deadline-list" id="list-finalized">
                                    <li>Carregando...</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="section-profile" class="dash-section" style="display:none">
                        <h2 style="text-align: center;">Editar Perfil</h2>
                        <div class="calculator-card" style="margin: 0 auto; max-width: 500px; width: 100%;">
                            <p style="text-align: center; color: var(--text-muted); margin-bottom: 20px; font-size: 0.95rem;">
                                Atualize os dados do seu cadastro. O nome só pode ser alterado uma vez a cada 15 dias.
                            </p>
                            <form id="profile-form">
                                <div class="form-group">
                                    <label for="profile-name">Nome Completo</label>
                                    <input type="text" id="profile-name" name="name" required value="<?php echo htmlspecialchars($u['name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="profile-phone">WhatsApp / Telefone</label>
                                    <input type="text" id="profile-phone" name="phone" required value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="profile-email">Email</label>
                                    <input type="email" id="profile-email" name="email" required value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="profile-password">Nova Senha (deixe em branco para não alterar)</label>
                                    <input type="password" id="profile-password" name="password" placeholder="Sua nova senha">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Salvar Alterações</button>
                            </form>
                        </div>
                    </div>

                    <div id="section-fees" class="dash-section" style="display:none;">
                        <h2 style="text-align: center;">Calculadora de Honorários</h2>
                        <div class="calculator-card" style="margin: 0 auto; max-width: 800px; width: 100%;">
                            <p style="text-align: center; margin-bottom: 2.5rem; color: var(--text-muted); font-size: 0.95rem;">Organize o recebimento e divisão de valores.</p>

                            <form id="fee-form">
                                <div class="form-row" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                                    <div class="form-group" style="flex: 1;">
                                        <label>Valor Total</label>
                                        <input type="text" id="fee-total" required placeholder="R$ 0,00">
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                        <label>Nº Parcelas</label>
                                        <input type="number" id="fee-installments" min="1" value="1" required>
                                    </div>
                                </div>
                                
                                <div class="form-row" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                                    <div class="form-group" style="flex: 1;">
                                        <label>Data 1ª Parcela</label>
                                        <input type="date" id="fee-start-date" required>
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label style="display: flex; justify-content: space-between; align-items: center;">
                                        Advogados Participantes
                                        <button type="button" class="btn btn-ghost" id="add-lawyer-btn" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; color: var(--primary); border: 1px solid var(--glass-border);">+ Adicionar Advogado</button>
                                    </label>
                                    <div id="lawyers-list" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                        <div class="lawyer-input-group" style="display: flex; gap: 0.5rem;">
                                            <input type="text" class="lawyer-name" placeholder="Nome do Advogado" required style="flex: 2;">
                                            <input type="number" class="lawyer-percent" placeholder="%" min="0" max="100" required style="width: 80px;">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Calcular Parcelas</button>
                            </form>
                        </div>

                        <div id="fee-results" style="display:none; margin: 2rem auto; max-width: 800px; width: 100%;" class="calculator-card">
                            <h3 style="margin-bottom: 1.5rem; color: white; display: block;">Planejamento de Recebimento</h3>
                            <div class="table-responsive">
                                <table class="glass-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Data</th>
                                            <th>Valor Total</th>
                                            <th>Por Advogado</th>
                                            <th>Agenda</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fee-table-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="fee-total" id="fee-summary" style="margin-top: 1.5rem; text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);"></div>
                        </div>

                        <div id="fee-history-container" class="calculator-card" style="margin: 2rem auto; max-width: 800px; width: 100%;">
                            <h3 style="margin-bottom: 1.5rem; color: white;">Histórico de Cálculos</h3>
                            <div class="table-responsive">
                                <table class="glass-table">
                                    <thead>
                                        <tr>
                                            <th>Data Criado</th>
                                            <th>Valor Total</th>
                                            <th>Parcelas</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fee-history-table-body">
                                        <tr><td colspan="4" style="text-align:center">Carregando histórico...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="pagination-controls" style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
                            </div>
                        </div>
                    </div>

                    <div id="section-history" class="dash-section" style="display:none">
                        <h2>Histórico de Cálculos</h2>
                        <div class="list-card">
                            <div class="table-responsive">
                                <table class="glass-table">
                                    <thead>
                                        <tr>
                                            <th>Data Final</th>
                                            <th>Descrição</th>
                                            <th>Dias</th>
                                            <th>Local</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="history-table-body">
                                        <tr><td colspan="5" style="text-align:center;">Carregando...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="section-new-deadline" class="dash-section" style="display:none">
                        <h2 style="text-align: center;">Cadastrar Novo Prazo</h2>
                        <div class="calculator-card" style="margin: 0 auto;">
                            <form id="calc-form-dash">
                                <div class="form-group">
                                    <label for="state-dash">Estado (UF)</label>
                                    <select id="state-dash" name="state">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="city-group-dash" style="display:none;">
                                    <label for="city-dash">Município (Feriados Locais)</label>
                                    <select id="city-dash" name="city">
                                        <option value="">Selecione o Estado primeiro</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="matter-dash">Matéria / Área</label>
                                    <select id="matter-dash" name="matter">
                                        <option value="">Geral</option>
                                    </select>
                                </div>

                                <div class="form-group" id="court-group-dash" style="display:none;">
                                    <label for="court-dash">Tribunal / Regional</label>
                                    <select id="court-dash" name="court">
                                        <option value="">Selecione o Estado primeiro</option>
                                    </select>
                                </div>

                                <div class="form-group" id="vara-group-dash">
                                    <label for="vara-dash">Vara / Juízo</label>
                                    <select id="vara-dash" name="vara">
                                        <option value="">Geral</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="process-type-dash">Processo</label>
                                    <select id="process-type-dash" name="process-type">
                                        <option value="electronic">Eletrônico</option>
                                        <option value="physical">Físico</option>
                                    </select>
                                </div>

                                <div class="form-group" id="deadline-type-group-dash" style="display:none;">
                                    <label for="deadline-type-dash">Tipo de Prazo Legal / Lei</label>
                                    <select id="deadline-type-dash" name="deadline-type">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label for="start_date-dash">Data da Publicação / Intimação</label>
                                    <input type="date" id="start_date-dash" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="days-dash">Prazo (em dias)</label>
                                    <input type="number" id="days-dash" name="days" placeholder="Ex: 5, 10, 15" required min="1">
                                </div>
                                <div class="form-group" style="text-align: center;">
                                    <label>Tipo de Contagem</label>
                                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem; justify-content: center;">
                                        <label style="color: white; cursor: pointer;">
                                            <input type="radio" name="type-dash" value="working" id="type-working-dash" checked> 
                                            Dias Úteis
                                        </label>
                                        <label style="color: white; cursor: pointer;">
                                            <input type="radio" name="type-dash" value="calendar" id="type-calendar-dash"> 
                                            Dias Corridos
                                        </label>
                                    </div>
                                </div>

                                <div id="deadline-disclaimer-dash" style="display:none; background: rgba(197, 160, 89, 0.05); padding: 0.85rem; border-radius: 0.5rem; margin-bottom: 1.25rem; border-left: 4px solid var(--primary); font-size: 0.9rem; color: var(--text-muted);">
                                    ⚠️ <strong style="color: var(--text-main);">Aviso:</strong> Confira se o prazo está correto. A estimativa foi baseada na lei federal.
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Calcular e Salvar</button>
                            </form>
                            <div id="results-area-dash" style="display:none;">
                                <div class="result-main">
                                    <div class="result-label">Prazo Final</div>
                                    <div class="result-date" id="result-date-dash">...</div>
                                </div>
                                <div class="log-container" id="log-details-dash"></div>
                                <a href="#" target="_blank" id="gcal-link-dash" class="btn gcal-btn">Adicionar ao Google Agenda</a>
                                <button id="btn-pdf-dash" class="btn btn-secondary" style="width:100%; margin-top:10px;">📄 Baixar PDF Detalhado</button>
                            </div>
                        </div>
                    </div>
                

                    <div id="section-converter" class="dash-section" style="display:none; flex: 1; flex-direction: column; justify-content: center; align-items: center; min-height: 70vh;">
                        <h2 style="text-align: center;">Conversor de Arquivos</h2>
                        <div class="calculator-card" style="margin: 0 auto; max-width: 600px; width: 100%;">
                            <p class="subtitle" style="margin-bottom: 20px;">Converta imagens, áudios e vídeos rapidamente.</p>
                            
                            <div style="display: flex; gap: 10px; margin-bottom: 20px; justify-content: center;">
                                <button type="button" class="btn btn-primary" id="btn-tab-pdf" onclick="switchConverterTab('pdf')">Imagem p/ PDF</button>
                                <button type="button" class="btn btn-ghost" id="btn-tab-audio" onclick="switchConverterTab('audio')">Áudio (MP3)</button>
                                <button type="button" class="btn btn-ghost" id="btn-tab-video" onclick="switchConverterTab('video')">Vídeo (MP4)</button>
                            </div>

                            <div id="converter-pdf-panel">
                                <label class="upload-zone" id="drop-zone-pdf" for="input-images" style="display: block; cursor: pointer;">
                                    <p>Arraste imagens (JPG, PNG) aqui ou clique para selecionar</p>
                                    <input type="file" id="input-images" accept="image/jpeg, image/png" multiple style="display:none">
                                </label>
                                <div id="preview-list" style="margin: 15px 0;"></div>
                                <button id="btn-convert-pdf" class="btn btn-primary btn-block" disabled>Gerar PDF</button>
                            </div>

                            <div id="converter-audio-panel" style="display:none;">
                                <label class="upload-zone" id="drop-zone-audio" for="input-audio" style="display: block; cursor: pointer;">
                                    <p>Selecione áudio (OGG, OPUS, WAV) para converter em MP3</p>
                                    <input type="file" id="input-audio" accept=".ogg,.opus,.wav" style="display:none">
                                </label>
                                <div id="audio-file-info" style="margin: 15px 0; color: #aaa;"></div>
                                <div class="alert-box" style="margin-top: 15px; background: rgba(255,193,7,0.1); color: #ffca2c; padding: 10px; border-radius: 5px; font-size: 0.9rem;">
                                    ⚠️ A conversão de áudio pode levar alguns instantes.
                                </div>
                                 <button id="btn-convert-audio" class="btn btn-primary btn-block" disabled>Converter para MP3</button>
                            </div>

                            <div id="converter-video-panel" style="display:none;">
                                <label class="upload-zone" id="drop-zone-video" for="input-video" style="display: block; cursor: pointer;">
                                    <p>Selecione vídeo (MOV, AVI, WEBM) para converter em MP4</p>
                                    <input type="file" id="input-video" accept=".mov,.avi,.webm" style="display:none">
                                </label>
                                <div id="video-file-info" style="margin: 15px 0; color: #aaa;"></div>
                                <button id="btn-convert-video" class="btn btn-primary btn-block" disabled>Converter para MP4</button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'src/footer.php'; ?>
    <script src="assets/script.js?v=<?php echo filemtime('assets/script.js'); ?>"></script>
</body>
</html>
