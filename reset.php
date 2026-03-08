<?php
$token = $_GET['token'] ?? '';
if (!$token) {
    header("Location: login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - MeuPrazoJus</title>
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
                    <a href="login" class="btn btn-ghost">Cancelar</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="auth-card">
            <h2 class="auth-title">Nova Senha</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: var(--text-muted);">
                Digite abaixo a sua nova senha.
            </p>
            <form id="reset-form">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="password">Nova Senha</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" required>
                        <span class="password-toggle" id="toggle-password">
                            <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Salvar Senha</button>
                <div id="msg" style="margin-top:1rem; text-align:center;"></div>
            </form>
        </div>
    </main>

    <script>
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');

        toggleBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            } else {
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
            }
        });

        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            const password = document.getElementById('password').value;
            const token = document.getElementById('token').value;
            const msg = document.getElementById('msg');
            
            btn.innerText = 'Salvando...';
            btn.disabled = true;
            msg.style.color = '#10b981'; // Green
            msg.innerText = 'Aguarde...';

            try {
                const res = await fetch('/api/auth.php?action=reset_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({token, password})
                });
                
                const data = await res.json();
                
                if(data.success) {
                    msg.style.color = '#10b981';
                    msg.innerText = 'Senha alterada com sucesso! Redirecionando...';
                    setTimeout(() => {
                        window.location.href = 'login';
                    }, 2000);
                } else {
                    msg.style.color = '#f87171'; // Red
                    msg.innerText = data.error || 'Não foi possível alterar a senha.';
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
