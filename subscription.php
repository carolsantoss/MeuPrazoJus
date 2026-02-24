<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css">
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
                   <?php if(isset($_SESSION['user_id'])): ?>
                       <a href="#" class="btn btn-ghost" onclick="logout()">Sair</a>
                   <?php else: ?>
                       <a href="login" class="btn btn-ghost">Entrar</a>
                   <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <h1>Escolha seu Plano</h1>
        <p class="subtitle">Desbloqueie cálculos ilimitados e integração total.</p>

        <div class="pricing-grid">
            <!-- Free -->
            <div class="pricing-card">
                <h3>Trial</h3>
                <div class="price">R$ 0</div>
                <div class="period">para testar</div>
                <ul class="features-list">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Cálculos Ilimitados</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Contagem Novo CPC</li>
                </ul>
                <?php if(isset($_SESSION['is_subscribed']) && $_SESSION['is_subscribed']): ?>
                    <button class="btn btn-ghost btn-block" disabled style="opacity: 0.5; cursor: not-allowed; border: 1px solid var(--glass-border)">Inativo</button>
                <?php else: ?>
                    <a href="index" class="btn btn-ghost btn-block" style="border: 1px solid var(--glass-border)">Começar Agora</a>
                <?php endif; ?>
            </div>

            <!-- Premium -->
            <div class="pricing-card featured">
                <h3>Anual</h3>
                <div class="price">R$ 50</div>
                <div class="period">por ano</div>
                <ul class="features-list">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Cálculos Ilimitados</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Integração Google Agenda</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Acesso a Honorários</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Suporte Prioritário</li>
                </ul>
                <?php if(isset($_SESSION['is_subscribed']) && $_SESSION['is_subscribed']): ?>
                    <button class="btn btn-secondary btn-block" disabled style="opacity: 0.7; cursor: not-allowed;">Plano Ativo</button>
                <?php else: ?>
                    <button id="sub-btn" class="btn btn-primary btn-block">Assinar Agora</button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('sub-btn').addEventListener('click', async () => {
            if(!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                window.location.href = 'login';
                return;
            }
            // Redirect to dedicated checkout page
            window.location.href = 'checkout';
        });

        async function logout() {
            await fetch('api/auth.php?action=logout');
            window.location.reload();
        }
    </script>
    <?php include 'src/footer.php'; ?>
</body>
</html>
