<?php
session_start();
require_once "conexao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

// --- AÇÃO 1: ESTAGIÁRIO ABRE SOLICITAÇÃO ---
if ($acao === 'solicitar_estagio' && $_SESSION['perfil'] === 'estagiario') {
    $estagiario_id = $_SESSION['id_usuario'];
    $concedente_id = intval($_POST['concedente_id']);
    $supervisor_id = intval($_POST['supervisor_id']);
    $data_inicio   = $_POST['data_inicio'];
    $data_fim      = $_POST['data_fim'];

    // Define um orientador genérico ou nulo inicialmente para o Admin atribuir depois
    $orientador_id = null; 

    $sql = "INSERT INTO estagios (estagiario_id, orientador_id, supervisor_id, concedente_id, data_inicio, data_fim, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pendente')";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiss", $estagiario_id, $orientador_id, $supervisor_id, $concedente_id, $data_inicio, $data_fim);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Pedido de estágio iniciado com sucesso! Aguardando validação.']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao salvar dados no banco.']);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno: ' . $e->getMessage()]);
    }
    exit;
}

// --- AÇÃO 2: ORIENTADOR APROVA PLANO (Muda status para Ativo) ---
if ($acao === 'aprovar_plano' && $_SESSION['perfil'] === 'orientador') {
    $id_estagio = intval($_POST['id_estagio']);
    $orientador_id = $_SESSION['id_usuario'];

    // Update seguro garantindo que o orientador logado é o responsável por este estágio
    $sql = "UPDATE estagios SET status = 'Ativo' WHERE id_estagio = ? AND orientador_id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_estagio, $orientador_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Plano aprovado com sucesso! O estágio agora está ATIVO.']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível alterar este estágio ou você não é o orientador dele.']);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao processar atualização: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não autorizada ou desconhecida.']);
