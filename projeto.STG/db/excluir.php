<?php
session_start();
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
    die("Acesso negado.");
}
require_once '../config/conexao.php';
$pdo = Database::getConexao();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM estagios WHERE id_estagio = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: ../listar/listar.php?msg=excluido");
exit;