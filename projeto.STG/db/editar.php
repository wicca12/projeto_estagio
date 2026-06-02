<?php
session_start();
if (!isset($_SESSION['usuario_perfil']) || !in_array($_SESSION['usuario_perfil'], ['admin', 'orientador'])) {
    die("Acesso negado.");
}
require_once '../config/conexao.php';
$pdo = Database::getConexao();

if (isset($_GET['id']) && isset($_GET['status_atual'])) {
    $id = (int)$_GET['id'];
    $statusAtual = $_GET['status_atual'];
    
    // Define a próxima etapa do Fluxo
    $novoStatus = 'Abertura';
    if ($statusAtual === 'Abertura') {
        $novoStatus = 'Em andamento';
    } elseif ($statusAtual === 'Em andamento') {
        $novoStatus = 'Concluído';
    } else {
        $novoStatus = 'Concluído';
    }

    $stmt = $pdo->prepare("UPDATE estagios SET status = :status WHERE id_estagio = :id");
    $stmt->execute([':status' => $novoStatus, ':id' => $id]);
}

header("Location: ../listar/listar.php?msg=atualizado");
exit;