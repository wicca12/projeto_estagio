<?php
session_start();
require_once "conexao.php";

// Proteção simples de login (exemplo)
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['perfil'])) {
    // Para testes, você pode mockar a sessão descomentando as linhas abaixo:
    // $_SESSION['id_usuario'] = 1; 
    // $_SESSION['perfil'] = 'estagiario'; 
    die("Acesso negado. Por favor, faça login.");
}

$perfil = $_SESSION['perfil'];
$id_usuario = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Estágios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Sistema de Estágios</span>
        <span class="navbar-text text-white">Perfil: <strong><?= ucfirst($perfil) ?></strong></span>
    </div>
</nav>

<div class="container">

    <?php if ($perfil === 'estagiario'): ?>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Iniciar Pedido de Estágio</div>
                    <div class="card-body">
                        <form id="formNovoEstagio">
                            <div class="mb-3">
                                <label class="form-label">Instituição Concedente (Empresa/Escola)</label>
                                <select class="form-select" name="concedente_id" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $res = $conn->query("SELECT id_concedente, nome_instituicao FROM concedentes WHERE convenio_ativo = 1");
                                    while($row = $res->fetch_assoc()) {
                                        echo "<option value='{$row['id_concedente']}'>{$row['nome_instituicao']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Supervisor da Empresa</label>
                                <select class="form-select" name="supervisor_id" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $res = $conn->query("SELECT id_usuario, nome FROM usuarios WHERE perfil = 'supervisor'");
                                    while($row = $res->fetch_assoc()) {
                                        echo "<option value='{$row['id_usuario']}'>{$row['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col mb-3">
                                    <label class="form-label">Data de Início</label>
                                    <input type="date" class="form-control" name="data_inicio" required>
                                </div>
                                <div class="col mb-3">
                                    <label class="form-label">Data de Fim</label>
                                    <input type="date" class="form-control" name="data_fim" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Solicitar Abertura</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Enviar Documentação</div>
                    <div class="card-body">
                        <form id="formDocumento" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Selecione o Estágio Vinculado</label>
                                <select class="form-select" name="id_estagio" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $stmt = $conn->prepare("SELECT id_estagio, status FROM estagios WHERE estagiario_id = ?");
                                    $stmt->bind_param("i", $id_usuario);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while($row = $res->fetch_assoc()) {
                                        echo "<option value='{$row['id_estagio']}'>Estágio #{$row['id_estagio']} ({$row['status']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Documento</label>
                                <select class="form-select" id="tipo_documento" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Termo de Compromisso de Estágio Obrigatório">TCE / Plano de Desenvolvimento (PDE)</option>
                                    <option value="Relatorio Parcial">Relatório Parcial</option>
                                    <option value="Relatorio Final">Relatório Final</option>
                                </select>
                            </div>

                            <div id="campoHoras" class="mb-3 d-none">
                                <label class="form-label">Horas Acumuladas no Mês/Período</label>
                                <input type="number" class="form-control" name="horas_acumuladas" placeholder="Ex: 60">
                            </div>
                            <div id="campoNota" class="mb-3 d-none">
                                <div class="alert alert-warning py-2">O campo de notas e parecer final será habilitado para avaliação do orientador após o envio.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Arquivo (PDF)</label>
                                <input type="file" class="form-control" name="arquivo" accept=".pdf" required>
                            </div>
                            <button type="submit" class="btn btn-secondary w-100">Enviar Documento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($perfil === 'orientador'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">Estágios Sob sua Orientação (Aprovação de Planos)</div>
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estagiário</th>
                            <th>Empresa/Escola</th>
                            <th>Período</th>
                            <th>Status Atual</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT e.id_estagio, e.status, e.data_inicio, e.data_fim, u.nome AS estagiario, c.nome_instituicao 
                                FROM estagios e 
                                JOIN usuarios u ON e.estagiario_id = u.id_usuario
                                JOIN concedentes c ON e.concedente_id = c.id_concedente
                                WHERE e.orientador_id = ? AND e.status = 'Pendente'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        
                        if ($res->num_rows === 0) echo "<tr><td colspan='6' class='text-muted text-center'>Nenhum plano pendente de aprovação.</td></tr>";

                        while($row = $res->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $row['id_estagio'] ?></td>
                                <td><?= $row['estagiario'] ?></td>
                                <td><?= $row['nome_instituicao'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($row['data_fim'])) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= $row['status'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-success btn-aprovar" data-id="<?= $row['id_estagio'] ?>">Aprovar Plano</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($perfil === 'admin'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">Painel de Controle - Administração Geral</div>
            <div class="card-body">
                <p>Aqui o Administrador realiza o cadastro de cursos, empresas/escolas e faz a alocação de orientadores.</p>
                <div class="btn-group">
                    <button class="btn btn-outline-primary">Cadastrar Curso</button>
                    <button class="btn btn-outline-primary">Cadastrar Concedente</button>
                    <button class="btn btn-outline-primary">Gerenciar Usuários</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. Lógica Dinâmica de exibição de campos com base no tipo de relatório (Estagiário)
    const selectTipo = document.getElementById('tipo_documento');
    const campoHoras = document.getElementById('campoHoras');
    const campoNota = document.getElementById('campoNota');

    if (selectTipo) {
        selectTipo.addEventListener('change', function() {
            if (this.value === 'Relatorio Parcial') {
                campoHoras.classList.remove('d-none');
                campoNota.classList.add('d-none');
            } else if (this.value === 'Relatorio Final') {
                campoNota.classList.remove('d-none');
                campoHoras.classList.add('d-none');
            } else {
                campoHoras.classList.add('d-none');
                campoNota.classList.add('d-none');
            }
        });
    }

    // 2. Envio do Formulário de Abertura de Estágio via AJAX
    const formNovoEstagio = document.getElementById('formNovoEstagio');
    if (formNovoEstagio) {
        formNovoEstagio.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('acao', 'solicitar_estagio');

            fetch('processar_estagio.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.mensagem);
                if (data.sucesso) location.reload();
            });
        });
    }

    // 3. Ação do Orientador para "Aprovar Plano" e rodar o UPDATE no banco
    document.querySelectorAll('.btn-aprovar').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEstagio = this.getAttribute('data-id');
            if (confirm(`Confirmar a aprovação do plano de atividades do Estágio #${idEstagio}?`)) {
                const formData = new FormData();
                formData.append('acao', 'aprovar_plano');
                formData.append('id_estagio', idEstagio);

                fetch('processar_estagio.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensagem);
                    if (data.sucesso) location.reload();
                });
            }
        });
    });
});
</script>
</body>
</html>