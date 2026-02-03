<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - PrazoLegal</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="max-w-7xl">
            <nav>
                <div class="logo">PrazoLegal</div>
                <div>
                   <a href="index.php" class="btn btn-ghost">Voltar</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="auth-container">
            <div class="auth-card">
                <h2 class="auth-title">Criar Conta</h2>
                <form id="register-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Confirmar Cadastro</button>
                    <div id="msg" style="margin-top:1rem; text-align:center; color: #f87171;"></div>
                </form>
                <span class="link-text">Já tem conta? <a href="login.php">Entrar</a></span>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            const res = await fetch('api/auth.php?action=register', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, password})
            });
            const data = await res.json();
            if(data.success) {
                alert('Conta criada!');
                window.location.href = 'index.php'; // or login
            } else {
                document.getElementById('msg').innerText = data.error;
            }
        });
    </script>
</body>
</html>
