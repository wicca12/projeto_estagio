<?php

session_start();
header("Content-Type: application/json");

include("conexao.php");

// Recebe JSON
$dados = json_decode(file_get_contents("php://input"), true);

$usuario = $dados['username'] ?? '';
$senha   = $dados['password'] ?? '';

// Validação básica
if (empty($usuario) || empty($senha)) {
    http_response_code(400);
    echo json_encode([
        "mensagem" => "Preencha usuário e senha."
    ]);
    exit;
}

// Busca usuário pelo CPF
$sql = "SELECT * FROM usuarios WHERE cpf = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {

    $user = $resultado->fetch_assoc();

    // Verifica senha
    if (password_verify($senha, $user['senha_hash'])) {

        // 🔐 CRIA SESSÃO SEGURA
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nome']       = $user['nome'];
        $_SESSION['perfil']     = $user['perfil'];

        echo json_encode([
            "mensagem" => "Login realizado com sucesso!",
            "usuario" => [
                "id" => $user['id_usuario'],
                "nome" => $user['nome'],
                "perfil" => $user['perfil']
            ]
        ]);

    } else {

        http_response_code(401);
        echo json_encode([
            "mensagem" => "Senha incorreta."
        ]);
    }

} else {

    http_response_code(404);
    echo json_encode([
        "mensagem" => "Usuário não encontrado."
    ]);
}
?>