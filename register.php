<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - MeuPrazoJus</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include 'src/google_adsense.php'; ?>
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <a href="index.php" class="logo" style="text-decoration: none;">MeuPrazoJus</a>
                <div>
                    <a href="index.php" class="btn btn-ghost">Voltar</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="card" style="max-width: 400px; width: 100%;">
            <h2 style="text-align: center; color: white; margin-bottom: 2rem;">Criar Conta</h2>
            
            <form id="reg-form">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" id="name" name="name" required placeholder="Seu nome">
                </div>
                
                <div class="form-group">
                    <label>WhatsApp / Telefone</label>
                    <input type="text" id="phone" name="phone" required placeholder="(00) 00000-0000">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" id="toggle-password">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Confirmar Cadastro</button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
                Já tem conta? <a href="login.php" style="color: var(--primary); text-decoration: none;">Entrar</a>
            </p>
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

        const phoneInput = document.getElementById('phone');
        
        phoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 11) value = value.slice(0, 11); 
            
            if (value.length > 10) {
                // (xx) xxxxx-xxxx
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
            } else if (value.length > 6) {
                // (xx) xxxx...
                value = value.replace(/^(\d{2})(\d{4,5})(\d{0,4}).*/, "($1) $2-$3");
            } else if (value.length > 2) {
                // (xx) ...
                value = value.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
            } else {
                // (x...
                value = value.replace(/^(\d*)/, "($1");
            }
            
            e.target.value = value;
        });

        document.getElementById('reg-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Carregando...';
            btn.disabled = true;

            try {
                const formData = new FormData(e.target);
                const res = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await res.text();
                try {
                   const data = JSON.parse(text);
                   if(data.success) {
                       window.location.href = 'index.php';
                   } else {
                       alert(data.error || 'Erro ao criar conta');
                   }
                } catch(e) {
                   console.error('Server response was not JSON:', text);
                   alert('Erro no servidor: O banco de dados pode estar fora do ar ou mal configurado.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro de conexão ou sistema.');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    </script>
    <?php include 'src/footer.php'; ?>
</body>
</html>
