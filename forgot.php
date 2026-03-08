<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha - MeuPrazoJus</title>
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
                    <a href="login" class="btn btn-ghost">Voltar ao Login</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="auth-card">
            <h2 class="auth-title">Recuperar Senha</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: var(--text-muted);">
                Digite seu e-mail abaixo e enviaremos um link para você redefinir sua senha.
            </p>
            <form id="forgot-form">
                <div class="form-group">
                    <label for="email">E-mail Cadastrado</label>
                    <input type="email" id="email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Enviar Link</button>
                <div id="msg" style="margin-top:1rem; text-align:center;"></div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('forgot-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            const email = document.getElementById('email').value;
            const msg = document.getElementById('msg');
            
            btn.innerText = 'Enviando...';
            btn.disabled = true;
            msg.style.color = '#10b981';
            msg.innerText = 'Aguarde...';

            try {
                const res = await fetch('/api/auth.php?action=forgot_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email})
                });
                
                const data = await res.json();
                
                if(data.success) {
                    msg.style.color = '#10b981';
                    msg.innerText = 'Link de recuperação enviado para o seu e-mail.';
                } else {
                    msg.style.color = '#10b981';
                    msg.innerText = data.error || 'Link de recuperação enviado se o e-mail estiver cadastrado.';
                }
            } catch (e) {
                console.error(e);
                msg.style.color = '#f87171';
                msg.innerText = 'Erro de conexão ou sistema.';
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    </script>
    <?php include 'src/footer.php'; ?>
</body>
</html>
