CREATE DATABASE IF NOT EXISTS sge_estagios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sge_estagios;

-- 1. TABELA DE USUÁRIOS
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'orientador', 'supervisor', 'estagiario') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABELA DE ESTÁGIOS
CREATE TABLE estagios (
    id_estagio INT AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT NOT NULL,
    empresa VARCHAR(100) NOT NULL,
    tipo ENUM('Obrigatório', 'Não Obrigatório') DEFAULT 'Obrigatório',
    status ENUM('Abertura', 'Em andamento', 'Concluído') DEFAULT 'Abertura',
    responsavel VARCHAR(50) DEFAULT 'Admin',
    FOREIGN KEY (id_aluno) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- 3. TABELA DE ATIVIDADES (Mural do Professor)
CREATE TABLE atividades (
    id_atividade INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    data_publicacao DATE NOT NULL,
    id_orientador INT NOT NULL,
    FOREIGN KEY (id_orientador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- INSERÇÕES DE TESTE (A senha para todos é: 123456)
INSERT INTO usuarios (cpf, nome, email, senha_hash, perfil) VALUES
('111.111.111-11', 'Administrador Central', 'admin@if.edu.br', '$2y$10$i29S26CGrX5CqG1u5iB1SubUv130DqscS1AaswL2LhWj7jBclPqg.', 'admin'),
('222.222.222-22', 'Prof. Alberto Carlos', 'alberto@if.edu.br', '$2y$10$i29S26CGrX5CqG1u5iB1SubUv130DqscS1AaswL2LhWj7jBclPqg.', 'orientador'),
('333.333.333-33', 'João Silva Santos', 'joao@estudante.if.edu.br', '$2y$10$i29S26CGrX5CqG1u5iB1SubUv130DqscS1AaswL2LhWj7jBclPqg.', 'estagiario');