<?php
session_start();
include 'blog-data.php';


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - MeuPrazoJus</title>
    <meta name="description" content="Artigos jurídicos sobre cálculo de prazos processuais, dicas de direito e atualizações sobre calendários de tribunais (TJ).">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
    <style>
        .blog-hero {
            text-align: center;
            padding: 4rem 1rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .blog-hero h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        .blog-hero p {
            font-size: 1.15rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
        }
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 5rem;
            padding: 0 1.5rem;
        }
        .post-card {
            background: rgba(30, 30, 46, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .post-card:hover {
            transform: translateY(-5px);
            border-color: rgba(197, 160, 89, 0.4);
            background: rgba(197, 160, 89, 0.05);
        }
        .post-category {
            display: inline-block;
            background: rgba(197, 160, 89, 0.15);
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            align-self: flex-start;
        }
        .post-title {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .post-excerpt {
            color: #aaa;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex: 1;
        }
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1rem;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .read-more {
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .post-card:hover .read-more {
            color: #fff;
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
        <div class="blog-hero">
            <h1>Blog Jurídico</h1>
            <p>Dicas práticas, guias de utilização da nossa calculadora de prazos processuais e atualizações sobre calendários dos tribunais brasileiros.</p>
        </div>

        <div class="blog-grid">
            <?php foreach($posts as $idx => $post): ?>
            <a href="post?id=<?= $post['id'] ?>" class="post-card">
                <span class="post-category"><?= $post['category'] ?></span>
                <h2 class="post-title"><?= $post['title'] ?></h2>
                <p class="post-excerpt"><?= $post['excerpt'] ?></p>
                <div class="post-footer">
                    <span>📅 <?= $post['date'] ?></span>
                    <span class="read-more">Ler Artigo →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'src/footer.php'; ?>
</body>
</html>
