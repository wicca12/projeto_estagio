<?php

header("Content-Type: application/json");
include("conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);

$identificador = $dados['identifier'] ?? '';

if (empty($identificador)) {
    http_response_code(400);
    echo json_encode([
        "mensagem" => "Informe CPF ou e-mail."
    ]);
    exit;
}

// Busca por CPF ou email
$sql = "SELECT * FROM usuarios WHERE cpf = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identificador, $identificador);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {

    $user = $resultado->fetch_assoc();

    // ⚠️ versão simplificada (sem e-mail ainda)
    // Aqui você pode gerar token de reset depois

    echo json_encode([
        "mensagem" => "Usuário encontrado. Em breve você receberá instruções para redefinir a senha."
    ]);

} else {

    http_response_code(404);
    echo json_encode([
        "mensagem" => "Usuário não encontrado."
    ]);
}
?>