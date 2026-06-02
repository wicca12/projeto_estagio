<?php
// sge/index.php

// Inicia a sessão para verificar se o usuário já passou pelo login
session_start();

// Verifica se existe uma sessão ativa de usuário
if (isset($_SESSION['usuario_perfil'])) {
    
    // Redirecionamento inteligente baseado no nível de acesso
    switch ($_SESSION['usuario_perfil']) {
        case 'admin':
            header('Location: listar/listar.php');
            exit;
            
        case 'orientador':
            // Se no futuro você criar uma pasta para o professor, muda aqui
            header('Location: listar/listar.php'); 
            exit;
            
        case 'estagiario':
            // Se no futuro você criar uma pasta para o aluno, muda aqui
            header('Location: listar/listar.php');
            exit;
            
        default:
            // Caso aconteça algum erro, destrói a sessão e manda para o login
            session_destroy();
            header('Location: login.php');
            exit;
    }
} else {
    // Se não houver nenhuma sessão ativa, força o usuário a fazer login
    header('Location: login.php');
    exit;
}