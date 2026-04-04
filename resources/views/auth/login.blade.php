<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
        }

        .card {
            border-radius: 15px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="card shadow p-4" style="width: 360px;">
    <h3 class="text-center mb-4">🔐 Login</h3>

    <div id="erro" class="alert alert-danger d-none"></div>

    <form id="formLogin">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Senha</label>
            <input type="password" id="senha" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Entrar
        </button>
    </form>
</div>

<script>
document.getElementById('formLogin').addEventListener('submit', async function(e) {
    e.preventDefault();

    const erroDiv = document.getElementById('erro');
    erroDiv.classList.add('d-none');

    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, senha })
        });

        const data = await response.json();

        if (!response.ok) {
            erroDiv.innerText = data.message || 'Erro ao logar';
            erroDiv.classList.remove('d-none');
            return;
        }

        // 🔐 salva token
        localStorage.setItem('token', data.access_token);

        // 👉 redireciona
        window.location.href = '/dashboard';

    } catch (error) {
        erroDiv.innerText = 'Erro na conexão com o servidor';
        erroDiv.classList.remove('d-none');
    }
});
</script>

</body>
</html>