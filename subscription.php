<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                   <a href="index" class="btn btn-ghost">Calculadora</a>
                   <?php if (isset($_SESSION['user_id'])): ?>
                       <a href="#" class="btn btn-ghost" onclick="logout()">Sair</a>
                   <?php
else: ?>
                       <a href="login" class="btn btn-ghost">Entrar</a>
                   <?php
endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <h1>Escolha seu Plano</h1>
        <p class="subtitle">Desbloqueie cálculos ilimitados e integração total.</p>

        <div class="pricing-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); max-width: 900px; margin: 0 auto;">
            <!-- Trial -->
            <div class="pricing-card">
                <h3>Trial</h3>
                <div class="price">R$ 0</div>
                <div class="period">para testar</div>
                <ul class="features-list">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Cálculos Ilimitados</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Contagem Novo CPC</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Integração Google Agenda</li>
                </ul>
                <?php if (isset($_SESSION['is_subscribed']) && $_SESSION['is_subscribed']): ?>
                    <button class="btn btn-ghost btn-block" disabled style="opacity: 0.5; cursor: not-allowed; border: 1px solid var(--glass-border)">Inativo</button>
                <?php
else: ?>
                    <a href="index" class="btn btn-ghost btn-block" style="border: 1px solid var(--glass-border)">Começar Agora</a>
                <?php
endif; ?>
            </div>

            <!-- Mensal -->
            <div class="pricing-card">
                <h3>Mensal</h3>
                <div class="price">R$ 19<span style="font-size:1.2rem;font-weight:600">,99</span></div>
                <div class="period">por mês</div>
                <ul class="features-list">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Cálculos Ilimitados</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Integração Google Agenda</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Acesso a Honorários</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Assinatura de Documentos (FCSigner)</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Suporte Prioritário</li>
                </ul>
                <?php
$user_plan = $_SESSION['subscription_plan'] ?? '';
$is_subscribed = isset($_SESSION['is_subscribed']) && $_SESSION['is_subscribed'];

// Fallback: se for assinado mas sem plano definido, assume anual
if ($is_subscribed && empty($user_plan)) {
    $user_plan = 'anual';
}

$is_mensal_active = $is_subscribed && $user_plan === 'mensal';
?>
                <?php if ($is_mensal_active): ?>
                    <button class="btn btn-secondary btn-block" disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 15px;">Plano Ativo</button>
                    <button id="cancel-sub-btn-mensal" class="btn btn-ghost btn-block" style="border: 1px solid var(--glass-border); color: #f87171;">Cancelar Assinatura</button>
                <?php
elseif ($is_subscribed): ?>
                    <button class="btn btn-ghost btn-block" disabled style="opacity: 0.4; cursor: not-allowed; border: 1px solid var(--glass-border)">Assinar Mensal</button>
                <?php
else: ?>
                    <button id="sub-btn-mensal" class="btn btn-ghost btn-block" style="border: 1px solid var(--glass-border);" onclick="subscribePlan('mensal')">Assinar Mensal</button>
                <?php
endif; ?>
            </div>

            <!-- Anual -->
            <div class="pricing-card featured" style="position: relative;">
                <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 0.75rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; white-space: nowrap; letter-spacing: 0.5px;">🏷️ ECONOMIZE 10%</div>
                <h3>Anual</h3>
                <div class="price">R$ 215<span style="font-size:1.2rem;font-weight:600">,89</span></div>
                <div class="period">por ano <span style="color: #10b981; font-size: 0.8rem;">(≈ R$ 17,99/mês)</span></div>
                <ul class="features-list">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Cálculos Ilimitados</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Integração Google Agenda</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Acesso a Honorários</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Assinatura de Documentos (FCSigner)</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Suporte Prioritário</li>
                </ul>
                <?php
$is_anual_active = $is_subscribed && $user_plan === 'anual';
?>
                <?php if ($is_anual_active): ?>
                    <button class="btn btn-secondary btn-block" disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 15px;">Plano Ativo</button>
                    <button id="cancel-sub-btn" class="btn btn-ghost btn-block" style="border: 1px solid var(--glass-border); color: #f87171;">Cancelar Assinatura</button>
                <?php
elseif ($is_subscribed): ?>
                    <button class="btn btn-primary btn-block" disabled style="opacity: 0.4; cursor: not-allowed;">Assinar Anual</button>
                <?php
else: ?>
                    <button id="sub-btn" class="btn btn-primary btn-block" onclick="subscribePlan('anual')">Assinar Anual</button>
                <?php
endif; ?>
            </div>
        </div>
    </main>

    <script>
        function subscribePlan(plan) {
            if(!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                window.location.href = 'login';
                return;
            }
            window.location.href = 'checkout?plano=' + plan;
        }

        const cancelBtn = document.getElementById('cancel-sub-btn');
        const cancelBtnMensal = document.getElementById('cancel-sub-btn-mensal');
        async function doCancelSubscription(btn) {
            if(confirm("Tem certeza que deseja cancelar sua assinatura? Você perderá acesso aos recursos premium imediatamente.")) {
                btn.innerText = "Cancelando...";
                btn.disabled = true;
                try {
                    const res = await fetch('/api/auth.php?action=cancel_subscription', {
                        method: 'POST',
                        credentials: 'same-origin'
                    });
                    
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error("Resposta não-JSON:", text);
                        throw new Error("Resposta inválida do servidor.");
                    }

                    if (data.success) {
                        alert("Assinatura cancelada com sucesso!");
                        window.location.reload();
                    } else {
                        alert("Erro ao cancelar: " + (data.error || "Desconhecido"));
                        btn.innerText = "Cancelar Assinatura";
                        btn.disabled = false;
                    }
                } catch (e) {
                    console.error("Erro no cancelamento:", e);
                    alert("Erro de sistema: " + e.message + ". Verifique o console para detalhes.");
                    btn.innerText = "Cancelar Assinatura";
                    btn.disabled = false;
                }
            }
        }

        if (cancelBtn) cancelBtn.addEventListener('click', () => doCancelSubscription(cancelBtn));
        if (cancelBtnMensal) cancelBtnMensal.addEventListener('click', () => doCancelSubscription(cancelBtnMensal));

        async function logout() {
            await fetch('/api/auth.php?action=logout', { method: 'POST' });
            window.location.reload();
        }
    </script>
    <?php include 'src/footer.php'; ?>
</body>
</html>
