<?php
session_start();
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    die("Acesso negado.");
}
require_once '../config/conexao.php';
$pdo = Database::getConexao();

// Carrega os estagiários cadastrados no sistema para preencher o Select do HTML
$estagiarios = $pdo->query("SELECT id_usuario, nome FROM usuarios WHERE perfil = 'estagiario'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aluno = $_POST['id_aluno'];
    $empresa = trim($_POST['empresa']);
    $tipo = $_POST['tipo'];

    if (!empty($id_aluno) && !empty($empresa)) {
        $sql = "INSERT INTO estagios (id_aluno, empresa, tipo, status, responsavel) VALUES (:id_aluno, :empresa, :tipo, 'Abertura', 'Admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_aluno' => $id_aluno,
            ':empresa' => $empresa,
            ':tipo' => $tipo
        ]);
        header("Location: ../listar/listar.php?msg=cadastrado");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGE | Cadastrar Estágio</title>
    <link rel="stylesheet" href="../adm.STG.css">
</head>
<body style="padding: 40px; background: #f4f6f9;">
    <div style="max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2>Cadastrar Novo Estágio</h2>
        <form action="cadastrar.php" method="POST" style="margin-top: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Selecione o Aluno:</label>
                <select name="id_aluno" required style="width: 100%; padding: 10px;">
                    <option value="">-- Escolha um Estudante --</option>
                    <?php foreach($estagiarios as $est): ?>
                        <option value="<?=$est['id_usuario']?>"><?=htmlspecialchars($est['nome'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Empresa Concedente:</label>
                <input type="text" name="empresa" required style="width: 95%; padding: 10px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px;">Tipo de Estágio:</label>
                <select name="tipo" style="width: 100%; padding: 10px;">
                    <option value="Obrigatório">Obrigatório</option>
                    <option value="Não Obrigatório">Não Obrigatório</option>
                </select>
            </div>
            <button type="submit" style="background:#27ae60; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; width:100%;">Salvar no Banco</button>
        </form>
        <br>
        <a href="../listar/listar.php" style="color: gray; text-decoration: none; display: block; text-align: center;">Voltar para a lista</a>
    </div>
</body>
</html>