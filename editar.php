<?php

header("Content-Type: application/json");
include("conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);

$id = $dados['id_usuario'] ?? null;
$nome = $dados['nome'] ?? null;
$email = $dados['email'] ?? null;
$perfil = $dados['perfil'] ?? null;
$senha = $dados['senha'] ?? null;

if (!$id || !$nome || !$email || !$perfil) {
    http_response_code(400);
    echo json_encode(["mensagem" => "Dados incompletos."]);
    exit;
}

if ($senha) {

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios 
            SET nome = ?, email = ?, perfil = ?, senha_hash = ?
            WHERE id_usuario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nome, $email, $perfil, $senhaHash, $id);

} else {

    $sql = "UPDATE usuarios 
            SET nome = ?, email = ?, perfil = ?
            WHERE id_usuario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome, $email, $perfil, $id);
}

if ($stmt->execute()) {
    echo json_encode(["mensagem" => "Usuário atualizado com sucesso."]);
} else {
    http_response_code(500);
    echo json_encode(["mensagem" => "Erro ao atualizar usuário."]);
}
?>