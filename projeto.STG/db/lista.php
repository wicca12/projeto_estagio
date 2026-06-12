<?php

header("Content-Type: application/json");
include("conexao.php");

$sql = "SELECT id_usuario, nome, email, cpf, perfil FROM usuarios";
$result = $conn->query($sql);

$usuarios = [];

while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
?>