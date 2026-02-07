<?php 
session_start(); 
if (isset($_SESSION['user_id'])) {
    require_once 'src/UserManager.php';
    $um = new UserManager();
    $u = $um->getUserById($_SESSION['user_id']);
    if ($u) {
        $_SESSION['calculations'] = $u['calculations_count'];
        $_SESSION['is_subscribed'] = ($u['subscription_status'] === 'premium');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeuPrazoJus - Calculadora de Prazos Processuais</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index.php" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="subscription.php" class="btn btn-ghost">Planos</a>
                        <a href="#" class="btn btn-primary" onclick="logout()">Sair</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-ghost">Entrar</a>
                        <a href="subscription.php" class="btn btn-primary">Assinar Agora</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <script>
     async function logout() {
        await fetch('api/auth.php?action=logout');
        window.location.reload();
     }
    </script>

    <main>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <div class="landing-hero">
                <h1>Domine seus Prazos</h1>
                <p class="subtitle">Simule prazos rapidamente ou faça login para gerenciar seus processos com segurança.</p>
                
                <div class="features-row">
                    <div class="feature-box">
                        <h3>📅 Novo CPC</h3>
                        <p>Contagem em dias úteis com suspensão automática no recesso.</p>
                    </div>
                     <div class="feature-box">
                        <h3>⚖️ Dias Corridos</h3>
                        <p>Opção para prazos penais e materiais.</p>
                    </div>
                     <div class="feature-box">
                        <h3>🔒 Segurança</h3>
                        <p>Histórico completo e seguro na nuvem (apenas usuários logados).</p>
                    </div>
                </div>

                <div class="calculator-card">
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

                        <div class="form-group" id="vara-group">
                            <label for="vara">Vara / Juízo</label>
                            <select id="vara" name="vara">
                                <option value="">Geral</option>
                            </select>
                        </div>

                        <div class="form-group" id="deadline-type-group" style="display:none;">
                            <label for="deadline-type">Tipo de Prazo</label>
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

                        <div id="deadline-disclaimer" style="display:none; background: rgba(255,255,255,0.1); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #cc9900; font-size: 0.85rem; color: #eee;">
                            ⚠️ <strong>Aviso:</strong> Confira se o prazo está correto. A estimativa foi baseada na lei federal.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Calcular Prazo</button>
                    </form>

                    <div class="limit-alert"></div>

                    <div id="results-area">
                        <div class="result-main">
                            <div class="result-label">Prazo Final</div>
                            <div class="result-date" id="result-date">...</div>
                        </div>
                        
                        <!-- Warning for Guest Users -->
                        <div id="guest-warning" style="display:none; background: #fffbeb; color: #92400e; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fcd34d; text-align: center;">
                            <p style="margin-bottom: 0.5rem; font-weight: 600;">⚠️ Atenção: Este cálculo não foi salvo!</p>
                            <p style="font-size: 0.9rem; margin-bottom: 1rem;">Para salvar seu histórico e gerenciar prazos com segurança, crie sua conta agora.</p>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Criar Conta Grátis</a>
                                <a href="login.php" class="btn btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.9rem; color: #92400e; border-color: #92400e;">Entrar</a>
                            </div>
                        </div>

                        <div class="log-container" id="log-details">
                        </div>
                        
                         <a href="#" target="_blank" id="gcal-link" class="btn gcal-btn">
                            Adicionar ao Google Agenda
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- DASHBOARD (Logged In) -->
            <div class="dashboard-container">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <div class="user-info">
                        <?php 
                            $fullName = $_SESSION['user_name'] ?? 'Usuário';
                            $firstName = explode(' ', trim($fullName))[0];
                        ?>
                        <h3>Olá, <?= htmlspecialchars($firstName) ?>!</h3>
                        <p>Gerencie seus prazos.</p>
                    </div>
                    <nav class="side-nav">
                        <?php 
                            $isPremium = $_SESSION['is_subscribed'] ?? false;
                            $calcCount = $_SESSION['calculations'] ?? 0;
                            $limit = 5;
                        ?>

                        <?php if (!$isPremium): ?>
                            <div class="usage-counter" style="margin: 0 0.5rem 1rem 0.5rem; background: rgba(255,255,255,0.05); padding: 0.5rem; border-radius: 6px; font-size: 0.85rem; color: #aaa;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                                    <span>Consultas</span>
                                    <span><?= $calcCount ?>/<?= $limit ?></span>
                                </div>
                                <div style="width:100%; height:4px; background:#444; border-radius:2px;">
                                    <div style="width: <?= min(100, ($calcCount/$limit)*100) ?>%; height:100%; background: var(--primary); border-radius:2px;"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button class="nav-item active" onclick="showSection('dashboard')">📊 Prazos</button>
                        <button class="nav-item" onclick="showSection('new-deadline')">➕ Novo Prazo</button>
                        <button class="nav-item" onclick="showSection('history')">📜 Histórico</button>
                        
                        <?php if ($isPremium): ?>
                            <a href="fees.php" class="nav-item">💰 Honorários</a>
                        <?php else: ?>
                            <a href="#" class="nav-item disabled-link" title="Assine para ter acesso" onclick="return false;">🔒 Honorários</a>
                        <?php endif; ?>

                        <a href="subscription.php" class="nav-item">⭐ Assinatura</a>
                    </nav>
                </aside>

                <!-- Main Dashboard Area -->
                <div class="dash-content">
                    
                    <!-- Section: Overview -->
                    <div id="section-dashboard" class="dash-section">
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

                    <!-- Section: History -->
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
                                        <!-- Populated by JS -->
                                        <tr><td colspan="5" style="text-align:center;">Carregando...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section: New Deadline (The Calculator) -->
                    <div id="section-new-deadline" class="dash-section" style="display:none">
                        <h2 style="text-align: center;">Cadastrar Novo Prazo</h2>
                        <div class="calculator-card" style="margin: 0 auto;">
                            <form id="calc-form-dash">
                                <!-- Jurisdiction Fields -->
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

                                <div class="form-group" id="vara-group-dash">
                                    <label for="vara-dash">Vara / Juízo</label>
                                    <select id="vara-dash" name="vara">
                                        <option value="">Geral</option>
                                    </select>
                                </div>

                                <div class="form-group" id="deadline-type-group-dash" style="display:none;">
                                    <label for="deadline-type-dash">Tipo de Prazo</label>
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

                                <div id="deadline-disclaimer-dash" style="display:none; background: rgba(255,255,255,0.1); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #cc9900; font-size: 0.85rem; color: #eee;">
                                    ⚠️ <strong>Aviso:</strong> Confira se o prazo está correto. A estimativa foi baseada na lei federal.
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
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'src/footer.php'; ?>
    <script src="assets/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
