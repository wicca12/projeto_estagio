<?php
session_start();
require_once "conexao.php";

// Proteção: Garante que apenas administradores acessem esta página
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: login.html");
    exit;
}

// 1. Consultar Contadores para os Cards de Estatística
$totalAbertura = $conn->query("SELECT COUNT(*) as qtd FROM estagios WHERE status = 'Pendente'")->fetch_assoc()['qtd'];
$totalAndamento = $conn->query("SELECT COUNT(*) as qtd FROM estagios WHERE status = 'Ativo'")->fetch_assoc()['qtd'];
$totalConcluido = $conn->query("SELECT COUNT(*) as qtd FROM estagios WHERE status = 'Concluído'")->fetch_assoc()['qtd'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE (Administração)</title>
    <link rel="stylesheet" href="admn.STG.css">
    <style>
        /* Ajuste simples para manter compatibilidade com classes inseridas */
        .status-pendente { background: #f1c40f; color: #fff; }
        .status-ativo { background: #3498db; color: #fff; }
        .status-concluido { background: #27ae60; color: #fff; }
        .btn-php-acao { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <button id=\"btn-toggle-menu\" class=\"btn-menu-trigger\">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Estágios</h2></div>
        <ul class="nav-links">
            <li class="section-title">ADMINISTRAÇÃO</li>
            <li onclick="window.location.href='gerenciar_usuarios.php'" style="cursor:pointer">Cadastrar Cursos/Usuários</li>
            <li class="active">Validar Novos Estágios</li>
            <li class="section-title">SISTEMA</li>
            <li onclick="window.location.href='logout.php'" style="cursor:pointer">Sair</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <img src="if.png" alt="logo">
            <h1>IFSertãoPE - SGE (Painel do Admin)</h1>
        </header>
        
        <section class="stats-container">
            <div class="card border-blue">
                <h3>Abertura</h3>
                <p id="count-abertura"><?= $totalAbertura ?> Pendentes</p>
            </div>
            <div class="card border-green">
                <h3>Em Andamento</h3>
                <p id="count-manutencao"><?= $totalAndamento ?> Ativos</p>
            </div>
            <div class="card border-purple">
                <h3>Finalizados</h3>
                <p id="count-finalizacao"><?= $totalConcluido ?> Concluídos</p>
            </div>
        </section>

        <section class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Pedidos de Estágio no Sistema</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Estagiário</th>
                        <th>Empresa Concedente</th>
                        <th>Etapa / Status</th>
                        <th>Período</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query buscando dados reais unindo tabelas de usuários e concedentes
                    $sql = "SELECT e.id_estagio, e.status, e.data_inicio, e.data_fim, 
                                   u_aluno.nome AS nome_aluno, c.nome_instituicao
                            FROM estagios e
                            JOIN usuarios u_aluno ON e.estagiario_id = u_aluno.id_usuario
                            JOIN concedentes c ON e.concedente_id = c.id_concedente
                            ORDER BY e.id_estagio DESC";
                    
                    $result = $conn->query($sql);
                    if ($result->num_rows === 0) {
                        echo "<tr><td colspan='5' style='text-align:center; color:#888;'>Nenhum estágio registrado no momento.</td></tr>";
                    }

                    while ($estagio = $result->fetch_assoc()):
                        // Mapeia classes css dinâmicas baseado no ENUM do banco
                        $classeBadge = 'status-pendente';
                        if ($estagio['status'] === 'Ativo') $classeBadge = 'status-ativo';
                        if ($estagio['status'] === 'Concluído') $classeBadge = 'status-concluido';
                    ?>
                        <tr id="linha-estagio-<?= $estagio['id_estagio'] ?>">
                            <td><strong><?= htmlspecialchars($estagio['nome_aluno']) ?></strong></td>
                            <td><?= htmlspecialchars($estagio['nome_instituicao']) ?></td>
                            <td><span class="badge <?= $classeBadge ?>"><?= $estagio['status'] ?></span></td>
                            <td><?= date('d/m/Y', strtotime($estagio['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($estagio['data_fim'])) ?></td>
                            <td>
                                <?php if ($estagio['status'] === 'Pendente'): ?>
                                    <button class="btn-php-acao" style="background:#27ae60; color:white;" onclick="atualizarStatusEstagio(<?= $estagio['id_estagio'] ?>, 'Ativo')">Aprovar e Ativar</button>
                                <?php elseif ($estagio['status'] === 'Ativo'): ?>
                                    <button class="btn-php-acao" style="background:#8e44ad; color:white;" onclick="atualizarStatusEstagio(<?= $estagio['id_estagio'] ?>, 'Concluído')">Concluir Estágio</button>
                                <?php else: ?>
                                    <span style="color:#7f8c8d; font-size:12px;">Sem ações pendentes</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Função Ajax utilizando Fetch API para processar mudanças de status em lote sem recarregar a tela
        function atualizarStatusEstagio(idEstagio, novoStatus) {
            if (!confirm(`Deseja alterar o status do estágio #${idEstagio} para '${novoStatus}'?`)) return;

            const formData = new FormData();
            formData.append('acao', 'atualizar_status_admin');
            formData.append('id_estagio', idEstagio);
            formData.append('status', novoStatus);

            fetch('processar_estagio.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.mensagem);
                if (data.sucesso) {
                    location.reload(); // Recarrega para computar os novos contadores PHP do topo
                }
            })
            .catch(err => console.error("Erro na operação:", err));
        }

        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');
        document.getElementById('btn-toggle-menu').onclick = () => {
            if(sb.style.left === "-250px" || sb.style.left === "") {
                sb.style.left = "0px";
                ct.style.marginLeft = "250px";
            } else {
                sb.style.left = "-250px";
                ct.style.marginLeft = "0";
            }
        };
    </script>
</body>
</html>