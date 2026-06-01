<?php

header("Content-Type: application/json");
include("conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);

$id = $dados['id_usuario'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["mensagem" => "ID não informado."]);
    exit;
}

$sql = "DELETE FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["mensagem" => "Usuário excluído com sucesso."]);
} else {
    http_response_code(500);
    echo json_encode(["mensagem" => "Erro ao excluir usuário."]);
}
?>