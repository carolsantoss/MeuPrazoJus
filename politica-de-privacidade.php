<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - MeuPrazoJus</title>
    <meta name="description"
        content="Política de Privacidade do MeuPrazoJus. Entenda como coletamos, usamos e protegemos seus dados.">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .legal-content {
            max-width: 800px;
            width: 100%;
            margin: 4rem auto;
            padding: 2.5rem;
            background: rgba(30, 30, 46, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            color: #ccc;
            line-height: 1.6;
        }

        .legal-content h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2.5rem;
            color: var(--primary, #dfc690);
        }

        .legal-content h2 {
            color: #fff;
            margin-bottom: 1rem;
            margin-top: 2rem;
            font-size: 1.3rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
        }

        .legal-content p,
        .legal-content li {
            margin-bottom: 1rem;
        }

        .legal-content ul {
            padding-left: 1.5rem;
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
                        <style>
                            @media(min-width: 768px) {
                                #top-nav-links {
                                    display: flex !important;
                                }
                            }
                        </style>
                        <div style="border-left: 1px solid rgba(255,255,255,0.2); height: 24px;" class="hide-mobile"></div>
                        <a href="login" class="btn btn-ghost hide-mobile">Entrar</a>
                        <a href="register" class="btn btn-primary">Criar Conta</a>
                        <style>
                            @media(max-width: 768px) {
                                .hide-mobile {
                                    display: none !important;
                                }
                            }
                        </style>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl">
        <div class="legal-content">
            <h1>Política de Privacidade</h1>
            <p><strong>Última atualização: <?php echo date('d/m/Y'); ?></strong></p>

            <p>O <strong>MeuPrazoJus</strong> reconhece a importância da privacidade e está comprometido com a segurança
                dos dados pessoais de seus usuários. Esta Política de Privacidade descreve como coletamos e usamos
                informações.</p>

            <h2>1. Informações Coletadas</h2>
            <ul>
                <li><strong>Dados fornecidos pelo próprio usuário:</strong> e-mail e informações essenciais ao criar uma
                    conta.</li>
                <li><strong>Dados de navegação:</strong> endereço IP, tipo de navegador, sistema operacional e páginas
                    visitadas.</li>
                <li><strong>Cookies:</strong> cookies de primeira e de terceira parte para melhorar a experiência do
                    usuário e otimizar recursos técnicos.</li>
            </ul>

            <h2>2. Uso de Cookies e Publicidade de Terceiros</h2>
            <p>
                Este site pode exibir anúncios e faz uso de cookies para personalizar o conteúdo. Em especial para
                cumprimento às regras de publicidade:
            </p>
            <p
                style="background:rgba(255,255,255,0.05); padding:1rem; border-left:4px solid var(--primary); border-radius:4px;">
                "Terceiros, incluindo o Google (Google AdSense), usam cookies para veicular anúncios com base nas
                visitas anteriores do usuário. O uso de cookies de publicidade pelo Google permite que ele e seus
                parceiros veiculem anúncios baseados na visita feita aos seus sites e/ou a outros na Internet. Os
                usuários podem desativar a publicidade personalizada acessando as <a
                    href="https://myadcenter.google.com/" target="_blank" style="color:var(--primary);">Configurações de
                    anúncios do Google</a>."
            </p>

            <h2>3. Compartilhamento de Dados</h2>
            <ul>
                <li>Não vendemos seus dados pessoais de forma alguma a terceiros.</li>
                <li>Dados estatísticos ou relatórios de publicidade podem ser gerados e compartilhados de forma agregada
                    com parceiros.</li>
            </ul>

            <h2>4. Disposições da LGPD (Lei nº 13.709/2018)</h2>
            <p>Na qualidade de usuário, você tem direito a solicitar acesso aos seus dados, além da correção, alteração
                e exclusão das informações em nosso banco de dados. Para exercer esses direitos ou falar com nosso
                encarregado de dados (DPO), por favor, entre em contato através do e-mail oficial (abaixo).</p>

            <h2>5. Contato e Responsável</h2>
            <p>Para suporte, esclarecimento de dúvidas sobre esta Política de Privacidade, ou exclusão de dados de
                usuário, utilize:</p>
            <p><strong>E-mail:</strong> fc.contato@outlook.com.br</p>
        </div>
    </main>

    <?php include 'src/footer.php'; ?>
</body>

</html>