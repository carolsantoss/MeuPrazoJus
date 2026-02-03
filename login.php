<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <div class="logo">MeuPrazoJus</div>
                <div>
                   <a href="index.php" class="btn btn-ghost">Voltar</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; color: white; margin-bottom: 2rem;">Entrar</h2>
            <form id="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" required>
                        <span class="password-toggle" id="toggle-password">
                            <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Acessar</button>
                <div id="msg" style="margin-top:1rem; text-align:center; color: #f87171;"></div>
            </form>
            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
                Não tem conta? <a href="register.php" style="color: var(--primary); text-decoration: none;">Cadastre-se</a>
            </p>
        </div>
    </main>

    <script>
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');

        toggleBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle Icon
            if (type === 'text') {
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            } else {
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
            }
        });

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            const res = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, password})
            });
            const data = await res.json();
            if(data.success) {
                window.location.href = 'index.php';
            } else {
                document.getElementById('msg').innerText = data.error;
            }
        });
    </script>
    <?php include 'src/footer.php'; ?>
</body>
</html>
