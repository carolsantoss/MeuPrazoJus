<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - MeuPrazoJus</title>
    <meta name="description" content="Termos de Uso do MeuPrazoJus. Regras e avisos legais sobre a utilização da calculadora de prazos processuais.">
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

    <main class="max-w-7xl">
        <div class="legal-content">
            <h1>Termos de Uso</h1>
            <p><strong>Última atualização: <?php echo date('d/m/Y'); ?></strong></p>
            
            <h2>1. Aceitação dos Termos</h2>
            <p>Ao acessar e utilizar a aplicação <strong>MeuPrazoJus</strong>, o usuário declara concordar integralmente com as condições estipuladas por meio destes Termos de Uso e pela nossa Política de Privacidade. Caso não concorde com tais regras, não deve utilizar o serviço.</p>

            <h2>2. Descrição do Serviço</h2>
            <p>O <strong>MeuPrazoJus</strong> é uma ferramenta computacional gratuita ou premium destinada a auxiliar advogados, estudantes de Direito e a comunidade jurídica no geral na <strong>simulação e conferência</strong> de prazos processuais com base em feriados nacionais, calendários oficiais do judiciário e legislação vigente (Novo CPC, CLT, CPP, etc.).</p>

            <h2>3. Isenção de Responsabilidade (Disclaimer Legal)</h2>
            <p style="background:rgba(255,165,0,0.1); padding:1rem; border-left:4px solid #ffa500; border-radius:4px; color:#fff;">
                ⚠ <strong>Atenção:</strong> As informações e cálculos fornecidos por esta plataforma têm caráter <strong>meramente INFORMATIVO e ESTIMATIVO</strong>. Em hipótese alguma os resultados eximem ou substituem a análise profissional de um advogado mediante consulta direta aos sistemas do processo judicial (PJe, e-SAJ, Projudi, E-Proc, etc.).<br><br>
                <strong>O MeuPrazoJus, seus desenvolvedores ou detentores (FC Technology) não se responsabilizam parcial ou totalmente por eventuais perdas de prazo processual, danos morais e/ou prejuízos decorrentes do uso exclusivo desta ferramenta.</strong> Toda responsabilidade por peticionamento tempestivo cabe exclusivamente ao operador do Direito, que deve <strong>SEMPRE conferir os prazos e publicações</strong>.
            </p>

            <h2>4. Propriedade Intelectual</h2>
            <p>Todo o código fonte, layout, logotipo "MeuPrazoJus", algoritmo de cálculo e base de dados de feriados são protegidos pela lei de direitos autorais (Lei nº 9.610/98) sob licenciamento aplicável da FC Technology.</p>

            <h2>5. Limitação de Responsabilidade</h2>
            <p>Esforçamo-nos para manter a base de feriados locais atualizada. Contudo, devido à natureza mutável dos expedientes forenses – inclusive portarias estaduais de suspensão não previstas na lei federal – podemos não ter precisão imediata sobre adiamentos regionais súbitos.</p>

            <h2>6. Condições para Usuários Cadastrados / Premium</h2>
            <p>As facilidades de armazenamento na nuvem e honorários são fornecidos no estado em que se encontram. Garantimos melhores esforços de disponibilidade, mas manutenções podem ser necessárias.</p>

            <h2>7. Legislação Aplicável e Foro</h2>
            <p>Estes termos são regidos pelas leis da República Federativa do Brasil. As partes elegem o foro da Comarca do desenvolvedor, em detrimento de qualquer outro, por mais privilegiado que seja.</p>
        </div>
    </main>

    <?php include 'src/footer.php'; ?>
</body>
</html>
