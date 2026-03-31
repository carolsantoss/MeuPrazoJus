<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada (404) - MeuPrazoJus</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body { overflow: hidden !important; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .error-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 4rem 1.5rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, #fff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
            text-shadow: 0 10px 30px rgba(197, 160, 89, 0.2);
        }
        .error-title {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 1rem;
        }
        .error-desc {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 500px;
            margin-bottom: 3rem;
        }
        .back-btn {
            background: var(--primary);
            color: #040914;
            padding: 0.8rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px -3px rgba(197, 160, 89, 0.3);
        }
        .back-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            box-shadow: 0 8px 20px -4px rgba(197, 160, 89, 0.4);
        }
    </style>
</head>
<body>
    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div style="display:flex; align-items:center; gap:1.5rem;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="subscription" class="btn btn-ghost">Planos</a>
                        <a href="index" class="btn btn-primary">Painel</a>
                    <?php else: ?>
                        <div class="nav-links" style="display:none; gap:1.5rem; color:#fff;" id="top-nav-links">
                            <a href="index" style="color:white; text-decoration:none; opacity:0.8;">Calculadora</a>
                            <a href="blog" style="color:white; text-decoration:none; opacity:0.8;">Blog</a>
                            <a href="sobre" style="color:white; text-decoration:none; opacity:0.8;">Sobre</a>
                            <a href="contato" style="color:white; text-decoration:none; opacity:0.8;">Contato</a>
                        </div>
                        <style>@media(min-width: 768px){ #top-nav-links { display:flex !important; } }</style>
                        <div style="border-left: 1px solid rgba(255,255,255,0.2); height: 24px;" class="hide-mobile"></div>
                        <a href="login" class="btn btn-ghost hide-mobile">Entrar</a>
                        <a href="register" class="btn btn-primary">Criar Conta</a>
                        <style>@media(max-width: 768px){ .hide-mobile { display:none !important; } }</style>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">Página Não Encontrada</h1>
        <p class="error-desc">Parece que o prazo processual ou a página que você está procurando extraviou-se ou não existe mais.</p>
        <a href="index" class="back-btn">← Voltar para a Calculadora</a>
    </div>

    <?php include 'src/footer.php'; ?>
</body>
</html>
