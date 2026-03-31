<?php
session_start();
include 'blog-data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;

foreach ($posts as $p) {
    if ($p['id'] === $id) {
        $post = $p;
        break;
    }
}

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    require '404.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - MeuPrazoJus</title>
    <meta name="description" content="<?= htmlspecialchars($post['excerpt']) ?>">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
    <style>
        .post-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 0 1.5rem;
        }
        .post-header {
            margin-bottom: 3rem;
            text-align: center;
        }
        .post-category {
            display: inline-block;
            background: rgba(197, 160, 89, 0.15);
            color: var(--primary);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2rem;
        }
        .post-header h1 {
            font-size: 2.8rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: #fff;
        }
        .post-meta {
            color: var(--text-muted);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        .article-body {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #ccc;
        }
        .article-body h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 0.5rem;
        }
        .article-body p {
            margin-bottom: 1.5rem;
        }
        .article-body ul, .article-body ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }
        .article-body li {
            margin-bottom: 0.5rem;
        }
        .article-body strong {
            color: #fff;
        }
        .auth-cta {
            background: linear-gradient(135deg, rgba(30, 30, 46, 0.8), rgba(4, 9, 20, 0.9));
            border: 1px solid rgba(197, 160, 89, 0.2);
            border-radius: 1rem;
            padding: 2.5rem;
            text-align: center;
            margin-top: 5rem;
            margin-bottom: 2rem;
        }
        .auth-cta h3 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .auth-cta p {
            color: #ccc;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .post-header h1 {
                font-size: 2rem;
            }
            .article-body {
                font-size: 1.05rem;
            }
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
                            <a href="blog" style="color:#dfc690; text-decoration:none; font-weight:500;">Blog</a>
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

    <main style="padding: 0;">
        <div class="max-w-7xl" style="padding: 1.5rem 1.5rem 0;">
            <a href="blog" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.95rem;">
                ← Voltar para o Blog
            </a>
        </div>

        <article class="post-container">
            <div class="post-header">
                <span class="post-category"><?= htmlspecialchars($post['category']) ?></span>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <span>📅 Publicado em <?= htmlspecialchars($post['date']) ?></span>
                    <span>⏱️ Leitura: 3 min</span>
                </div>
            </div>

            <div class="article-body">
                <?= $post['content'] ?>
                
                <div class="auth-cta">
                    <h3>Gostou das dicas? Proteja seus prazos!</h3>
                    <p>Milhares de advogados já confiam no <strong>MeuPrazoJus</strong> para não perder suas datas fatais.</p>
                    <a href="index" class="btn btn-primary">Calcular Meu Primeiro Prazo</a>
                </div>
            </div>
        </article>
    </main>

    <?php include 'src/footer.php'; ?>
</body>
</html>
