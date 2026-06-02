<?php
session_start();
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    die("Acesso restrito ao Administrador.");
}
require_once '../config/conexao.php';
$pdo = Database::getConexao();

// Contadores para os Cards dinâmicos baseados no MySQL
$abertura = $pdo->query("SELECT COUNT(*) FROM estagios WHERE status = 'Abertura'")->fetchColumn();
$andamento = $pdo->query("SELECT COUNT(*) FROM estagios WHERE status = 'Em andamento'")->fetchColumn();
$concluido = $pdo->query("SELECT COUNT(*) FROM estagios WHERE status = 'Concluído'")->fetchColumn();
$total = $abertura + $andamento + $concluido;

// Busca os dados relacionando o Estágio com o Nome do Aluno
$sql = "SELECT e.*, u.nome FROM estagios e INNER JOIN usuarios u ON e.id_aluno = u.id_usuario ORDER BY e.id_estagio DESC";
$listaEstagios = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE (Administração)</title>
    <link rel="stylesheet" href="../adm.STG.css">
    <style>
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; color: white; display: inline-block; }
        .status-abertura { background-color: #007bff; }
        .status-plano { background-color: #f1c40f; color: #000; }
        .status-manutencao { background-color: #27ae60; }
        .btn-php { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white; margin-right: 5px; }
    </style>
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Estágios</h2></div>
        <ul class="nav-links">
            <li class="section-title">ADMINISTRAÇÃO</li>
            <li><a href="../listar/listar.php" style="color:white; text-decoration:none; font-weight:bold;">Fluxo de Estágios</a></li>
            <li class="section-title">AÇÕES</li>
            <li><a href="../cadastro/cadastrar.php" style="color:#27ae60; text-decoration:none; font-weight:bold;">+ Novo Cadastro</a></li>
            <li><a href="../logout.php" style="color:#e74c3c; text-decoration:none;">Sair</a></li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <img src="../if.png" alt="logo">
            <h1>IFSertãoPE - SGE</h1>
        </header>
        
        <section class="stats-container">
            <div class="card border-blue"><h3>Abertura</h3><p><?=$abertura?> Pendentes</p></div>
            <div class="card border-yellow"><h3>Em Andamento</h3><p><?=$andamento?> Aguardando</p></div>
            <div class="card border-green"><h3>Concluídos</h3><p><?=$concluido?> Finalizados</p></div>
            <div class="card border-red"><h3>Total</h3><p><?=$total?> Registros</p></div>
        </section>

        <section class="table-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Fluxo de Estágios em Andamento (Dados do MySQL)</h2>
                <a href="../cadastro/cadastrar.php" style="background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; text-decoration:none; font-weight:bold;">+ Adicionar Registro</a>
            </div>
            
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Estagiário / Tipo</th>
                        <th>Empresa</th>
                        <th>Status do Fluxo</th>
                        <th>Responsável Atual</th>
                        <th>Ações Crud</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($listaEstagios) > 0): ?>
                        <?php foreach($listaEstagios as $estagio): 
                            $badgeClass = ($estagio['status'] === 'Abertura') ? 'status-abertura' : (($estagio['status'] === 'Em andamento') ? 'status-plano' : 'status-manutencao');
                        ?>
                        <tr>
                            <td><strong><?=htmlspecialchars($estagio['nome'])?></strong><br><small style="color:gray"><?=$estagio['tipo']?></small></td>
                            <td><?=htmlspecialchars($estagio['empresa'])?></td>
                            <td><span class="badge <?=$badgeClass?>"><?=$estagio['status']?></span></td>
                            <td><?=htmlspecialchars($estagio['responsavel'])?></td>
                            <td>
                                <?php if($estagio['status'] !== 'Concluído'): ?>
                                    <a href="../editar/editar.php?id=<?=$estagio['id_estagio']?>&status_atual=<?=$estagio['status']?>" class="btn-php" style="background: #007bff;">Avançar</a>
                                <?php endif; ?>
                                <a href="../excluir/excluir.php?id=<?=$estagio['id_estagio']?>" class="btn-php" style="background: #e74c3c;" onclick="return confirm('Deseja realmente deletar do MySQL?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:gray;">Nenhum estágio cadastrado no banco de dados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');
        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => { sb.style.left = "0"; ct.style.marginLeft = "250px"; });
        sb.addEventListener('mouseleave', () => { sb.style.left = "-250px"; ct.style.marginLeft = "0"; });
        window.onload = () => { sb.style.left = "-250px"; ct.style.marginLeft = "0"; };
    </script>
</body>
</html>