<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            position: fixed;
            background: #343a40;
            color: white;
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 12px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #495057;
        }

        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .card {
            border-radius: 12px;
        }

        .grafico-tempo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 12px;
            align-items: flex-end;
            padding-top: 16px;
        }

        .grafico-barra {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 12px 6px 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
        }

        .grafico-barra-fill {
            width: 100%;
            background: linear-gradient(180deg, #4f46e5 0%, #818cf8 100%);
            border-radius: 12px 12px 0 0;
            transition: height 0.3s ease;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4 class="text-center mt-3">Sistema</h4>
    <a href="#">📊 Dashboard</a>
    <a href="#">👤 Usuários</a>
    <a href="#">📦 Produtos</a>
    <a href="#">🛒 Vendas</a>
    <a href="#" onclick="logout()">🚪 Sair</a>
</div>

<!-- CONTEÚDO -->
<div class="content">

    <!-- TOPO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Dashboard</h2>
        <span id="usuarioNome">Olá</span>
    </div>

    <!-- FEEDBACK -->
    <div id="feedback" class="alert d-none"></div>

    <!-- CARDS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 shadow">
                <small>Usuários</small>
                <h3 id="usuarios">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <small>Produtos</small>
                <h3 id="produtos">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <small>Vendas</small>
                <h3 id="vendas">0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <small>Faturamento</small>
                <h3 id="faturamento">R$ 0</h3>
            </div>
        </div>
    </div>

    <!-- GRÁFICO DE TEMPO -->
    <div class="card shadow p-3 mb-4">
        <h5>Vendas por dia</h5>
        <div id="graficoVendasPorDia" class="grafico-tempo"></div>
    </div>

    <!-- TABELA -->
    <div class="card shadow p-3">
        <h5>Últimas vendas</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody id="tabelaVendas"></tbody>
        </table>
    </div>

</div>

<script>
function mostrarFeedback(msg, tipo = 'success') {
    const div = document.getElementById('feedback');
    div.className = `alert alert-${tipo}`;
    div.innerText = msg;
    div.classList.remove('d-none');

    setTimeout(() => div.classList.add('d-none'), 3000);
}

// LOGOUT
async function logout() {
    const token = localStorage.getItem('token');

    await fetch('/api/logout', {
        method: 'POST',
        headers: {
            Authorization: 'Bearer ' + token
        }
    });

    localStorage.removeItem('token');
    window.location.href = '/login';
}

// CARREGAR DASHBOARD
async function carregarDashboard() {
    const token = localStorage.getItem('token');

    if (!token) {
        mostrarFeedback('Faça login primeiro', 'danger');
        window.location.href = '/login';
        return;
    }

    try {
        mostrarFeedback('Carregando...', 'warning');

        const response = await fetch('/api/dashboard', {
            headers: {
                Authorization: 'Bearer ' + token
            }
        });

        if (!response.ok) throw new Error();

        const data = await response.json();

        // CARDS
        document.getElementById('usuarios').innerText = data.total_usuarios;
        document.getElementById('produtos').innerText = data.total_produtos;
        document.getElementById('vendas').innerText = data.total_vendas;
        document.getElementById('faturamento').innerText = 'R$ ' + data.faturamento;

        // TABELA
        const tabela = document.getElementById('tabelaVendas');
        tabela.innerHTML = '';

        data.ultimas_vendas.forEach(v => {
            tabela.innerHTML += `
                <tr>
                    <td>${v.id}</td>
                    <td>R$ ${v.total}</td>
                    <td>${v.created_at}</td>
                </tr>
            `;
        });

        // USUÁRIO
        const userRes = await fetch('/api/me', {
            headers: { Authorization: 'Bearer ' + token }
        });

        const user = await userRes.json();
        document.getElementById('usuarioNome').innerText = 'Olá, ' + user.nome;

        renderGraficoVendasPorDia(data.vendas_por_dia);
        mostrarFeedback('Dashboard carregado!');

    } catch {
        mostrarFeedback('Erro ao carregar dados', 'danger');
    }
}

function renderGraficoVendasPorDia(vendasPorDia) {
    const container = document.getElementById('graficoVendasPorDia');
    container.innerHTML = '';

    if (!Array.isArray(vendasPorDia) || vendasPorDia.length === 0) {
        container.innerHTML = '<div class="text-muted">Sem dados de vendas recentes</div>';
        return;
    }

    const maxValor = Math.max(...vendasPorDia.map(item => item.valor || 0), 1);

    vendasPorDia.forEach(item => {
        const altura = Math.max(16, Math.round((item.valor / maxValor) * 140));
        const valorFormatado = Number(item.valor).toFixed(2).replace('.', ',');

        container.innerHTML += `
            <div class="grafico-barra">
                <div class="grafico-barra-fill" style="height: ${altura}px"></div>
                <small>${item.dia}</small>
                <strong>R$ ${valorFormatado}</strong>
            </div>
        `;
    });
}

carregarDashboard();
</script>

</body>
</html>