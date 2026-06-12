<?php

header("Content-Type: application/json");

include("conexao.php");

// Recebe JSON
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    http_response_code(400);

    echo json_encode([
        "mensagem" => "Dados inválidos."
    ]);

    exit;
}

$nome = trim($dados['fullName'] ?? '');
$cpf = trim($dados['regId'] ?? '');
$email = trim($dados['email'] ?? '');
$perfil = trim($dados['profileType'] ?? '');
$senhaDigitada = $dados['password'] ?? '';

// Validação básica
if (
    empty($nome) ||
    empty($cpf) ||
    empty($email) ||
    empty($perfil) ||
    empty($senhaDigitada)
) {
    http_response_code(400);

    echo json_encode([
        "mensagem" => "Preencha todos os campos."
    ]);

    exit;
}

// Remove máscara do CPF
$cpf = preg_replace('/\D/', '', $cpf);

if (strlen($cpf) !== 11) {
    http_response_code(400);

    echo json_encode([
        "mensagem" => "CPF inválido."
    ]);

    exit;
}

// Valida email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        "mensagem" => "E-mail inválido."
    ]);

    exit;
}

// Conversão dos perfis do formulário
if ($perfil === "aluno") {
    $perfil = "estagiario";
}

if ($perfil === "concedente") {
    $perfil = "supervisor";
}

// Perfis permitidos no banco
$perfisPermitidos = [
    "estagiario",
    "orientador",
    "supervisor"
];

if (!in_array($perfil, $perfisPermitidos)) {
    http_response_code(400);

    echo json_encode([
        "mensagem" => "Perfil inválido."
    ]);

    exit;
}

// Gera hash da senha
$senhaHash = password_hash($senhaDigitada, PASSWORD_DEFAULT);

// Verifica usuário existente
$sqlVerifica = "
SELECT id_usuario
FROM usuarios
WHERE email = ?
OR cpf = ?
";

$stmt = $conn->prepare($sqlVerifica);
$stmt->bind_param("ss", $email, $cpf);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {

    $stmt->close();

    http_response_code(409);

    echo json_encode([
        "mensagem" => "Usuário já cadastrado."
    ]);

    exit;
}

$stmt->close();

// Insere usuário
$sql = "
INSERT INTO usuarios
(
    nome,
    email,
    senha_hash,
    cpf,
    perfil
)
VALUES
(
    ?, ?, ?, ?, ?
)
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssss",
    $nome,
    $email,
    $senhaHash,
    $cpf,
    $perfil
);

if ($stmt->execute()) {

    echo json_encode([
        "mensagem" => "Cadastro realizado com sucesso!"
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "mensagem" => "Erro ao cadastrar usuário."
    ]);
}

$stmt->close();
$conn->close();

?>