<?php
session_start();
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $success = true;
    } else {
        $error = "Preencha todos os campos obrigatórios com e-mail válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - MeuPrazoJus</title>
    <meta name="description"
        content="Entre em contato com a equipe MeuPrazoJus para sugestões, suporte ou feedback sobre nossos serviços.">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .contact-box {
            max-width: 600px;
            width: 100%;
            margin: 4rem auto;
            padding: 2.5rem;
            background: rgba(30, 30, 46, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            color: #ccc;
        }

        .contact-box h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 0.5rem;
            color: var(--primary, #dfc690);
        }

        .contact-box p.subtitle {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            color: #999;
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
                            <a href="contato" style="color:#dfc690; text-decoration:none; font-weight:500;">Contato</a>
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
        <div class="contact-box">
            <h1>Fale Conosco</h1>
            <p class="subtitle">Dúvidas, suporte, ou sugestões empresariais? Envie sua mensagem.</p>

            <?php if ($success): ?>
                <div
                    style="background: rgba(40, 167, 69, 0.1); border-left: 4px solid #28a745; padding: 1rem; border-radius: 4px; color: #fff; margin-bottom: 1.5rem;">
                    ✅ Mensagem registrada com sucesso! Em breve um de nossos consultores responderá através do e-mail.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div
                    style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 1rem; border-radius: 4px; color: #fff; margin-bottom: 1.5rem;">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="contato">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="name" style="display:block; margin-bottom:0.5rem; color:#fff;">Seu Nome</label>
                    <input type="text" id="name" name="name" required
                        style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; color:#fff;"
                        placeholder="João Silva">
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="email" style="display:block; margin-bottom:0.5rem; color:#fff;">E-mail para
                        resposta</label>
                    <input type="email" id="email" name="email" required
                        style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; color:#fff;"
                        placeholder="joao.adv@email.com">
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="subject" style="display:block; margin-bottom:0.5rem; color:#fff;">Assunto</label>
                    <select id="subject" name="subject" required
                        style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; color:#fff;">
                        <option value="Suporte">1. Dúvida / Suporte a Erros</option>
                        <option value="Assinatura">2. Assinatura Premium/Financeiro</option>
                        <option value="Privacidade">3. Privacidade e Exclusão de Dados</option>
                        <option value="Sugestao">4. Sugestão ou Parceria</option>
                        <option value="Outros">5. Outros assuntos</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="message" style="display:block; margin-bottom:0.5rem; color:#fff;">Mensagem</label>
                    <textarea id="message" name="message" required rows="5"
                        style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; color:#fff; font-family:inherit; resize:vertical;"
                        placeholder="Como podemos te ajudar hoje?"></textarea>
                </div>

                <div style="font-size: 0.85rem; color: #888; margin-bottom: 1.5rem;">
                    🔒 Ao enviar, você concorda com nossa <a href="politica-de-privacidade"
                        style="color:var(--primary);">Política de Privacidade</a>. Os dados inseridos acima serão usados
                    apenas para entrarmos em contato com o retorno da demanda.
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:1rem; font-size:1.05rem;">Enviar Mensagem</button>
            </form>

            <div
                style="margin-top:2.5rem; text-align:center; padding-top:2rem; border-top:1px solid rgba(255,255,255,0.05);">
                <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Canal Direto</h3>
                <p><strong>E-mail:</strong> fc.contato@outlook.com.br</p>
                <p><strong>Horário de Atendimento:</strong> Segunda a Sexta, 8h às 18h</p>
            </div>
        </div>
    </main>

    <?php include 'src/footer.php'; ?>
</body>

</html>