<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - MeuPrazoJus</title>
    <meta name="description" content="Conheça o MeuPrazoJus: plataforma gratuita de cálculo de prazos processuais criada por profissionais do Direito.">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
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
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 0.5rem;
        }
        .legal-content p, .legal-content li { margin-bottom: 1rem; }
        .legal-content ul { padding-left: 1.5rem; }
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
                            <a href="sobre" style="color:#dfc690; text-decoration:none; font-weight:500;">Sobre</a>
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

    <main class="max-w-7xl">
        <div class="legal-content">
            <h1>Sobre o MeuPrazoJus</h1>
            
            <h2>Quem Somos</h2>
            <p>O <strong>MeuPrazoJus</strong> é uma plataforma moderna e inovadora desenvolvida pela <strong>FC Technology</strong> para ser a solução confiável de advogados, estudantes de Direito e cidadãos no controle de prazos processuais e documentação.</p>

            <h2>Nossa Missão</h2>
            <p>Nossa missão é democratizar com alta confiabilidade e tecnologia o acesso à informação temporal no processo judicial. Proporcionamos uma ferramenta veloz para cálculo de prazos, agregando eficiência à rotina exaustiva de advogados do país inteiro através da precisão de nossa calculadora.</p>

            <h2>Como Funciona a Ferramenta</h2>
            <p>Nossa plataforma oferece cálculos embasados onde integramos:</p>
            <ul>
                <li><strong>Legislação processual vigente</strong>: Novo Código de Processo Civil (CPC), Leis Trabalhistas (CLT), entre outras legislações de prazos.</li>
                <li><strong>Calendário Oficial Brasileiro</strong>: A junção do Calendário Judiciário dos principais Tribunais (TJ) aos feriados estaduais, nacionais e recesso forense.</li>
                <li><strong>Regras Personalizadas</strong>: Atendem a peculiaridades para processos eletrônicos e físicos, abrangendo todos os tribunais aplicáveis.</li>
            </ul>

            <h2>O Nosso Compromisso (Padrão E-E-A-T)</h2>
            <p>No ramo jurídico exigimos de nós mesmos rigorosos padrões de Especialização, Experiência, Autoridade e Confiabilidade. Nossos cálculos baseiam-se diretamente nas leis federais (Lei nº 13.105/2015, etc.) e em metodologias sofisticadas, aliadas aos operadores da FC Technology para garantir o rigor técnico exigido.</p>

            <h2>Autoria e Organização</h2>
            <p>
                <strong>FC Technology — Futuro Conectado</strong><br>
                Sediada no Brasil, nós temos equipe multidisciplinar em desenvolvimento de sistemas jurídicos robustos e confiáveis.<br>
                <strong>E-mail:</strong> fc.contato@outlook.com.br
            </p>
        </div>
    </main>

    <?php include 'src/footer.php'; ?>
</body>
</html>
