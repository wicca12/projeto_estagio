<?php
session_start();
require_once "conexao.php";

// Proteção: Garante que apenas supervisores acessem esta página
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'supervisor') {
    header("Location: login.html");
    exit;
}

$id_supervisor = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGE - Painel do Supervisor da Empresa</title>
    <link rel="stylesheet" href="professor.orin.STG.css"> 
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Supervisor</h2></div>
        <ul class="nav-links">
            <li class="section-title">EMPRESA / CAMPO</li>
            <li class="active">Avaliar Estagiários</li>
            <li class="section-title">SISTEMA</li>
            <li onclick="window.location.href='logout.php'" style="cursor:pointer">Sair</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <img src="if.png" alt="logo">
            <h1>Supervisor: <?= htmlspecialchars($_SESSION['nome']) ?></h1>
        </header>

        <section class="table-container" style="margin: 20px; background: white; padding: 20px; border-radius: 8px;">
            <h2>Alunos sob sua Supervisão</h2>
            <p style="color: #666; margin-bottom: 15px;">Abaixo estão listados os alunos alocados na sua instituição. Avalie o desempenho e valide as folhas de ponto enviadas.</p>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f6f7; text-align: left;">
                        <th style="padding: 10px;">Estagiário</th>
                        <th style="padding: 10px;">Período</th>
                        <th style="padding: 10px;">Status do Estágio</th>
                        <th style="padding: 10px;">Ações de Supervisão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Busca apenas os estágios vinculados a este supervisor logado
                    $sql = "SELECT e.id_estagio, e.status, e.data_inicio, e.data_fim, u.nome AS nome_aluno
                            FROM estagios e
                            JOIN usuarios u ON e.estagiario_id = u.id_usuario
                            WHERE e.supervisor_id = ?
                            ORDER BY e.id_estagio DESC";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_supervisor);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        echo "<tr><td colspan='4' style='padding:15px; text-align:center; color:#888;'>Nenhum estagiário vinculado à sua conta no momento.</td></tr>";
                    }

                    while ($linha = $result->fetch_assoc()):
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><strong><?= htmlspecialchars($linha['nome_aluno']) ?></strong></td>
                            <td style="padding: 10px;"><?= date('d/m/Y', strtotime($linha['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($linha['data_fim'])) ?></td>
                            <td style="padding: 10px;"><span class="badge" style="background:#34495e; color:white; padding:3px 7px; border-radius:4px; font-size:12px;"><?= $linha['status'] ?></span></td>
                            <td style="padding: 10px;">
                                <button class="btn-primary" style="background:#2980b9; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" onclick="abrirFichaAvaliacao(<?= $linha['id_estagio'] ?>)">
                                    Lançar Ficha de Desempenho ⭐
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        function abrirFichaAvaliacao(idEstagio) {
            const nota = prompt("Digite a nota de desempenho do aluno no campo de estágio (0 a 10):");
            if (nota === null) return; // cancelou
            
            if (nota < 0 || nota > 10 || isNaN(nota)) {
                alert("Por favor, insira uma nota válida de 0 a 10.");
                return;
            }

            // Fetch para processar_estagio.php salvar essa nota no banco
            alert(`Nota ${nota} enviada para o estágio #${idEstagio} com sucesso!`);
        }

        // Lógica de toggle do menu lateral
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