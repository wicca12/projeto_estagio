<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Sistema de Estágios - Admin</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Voltar ao Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white" id="formCardHeader">
                    Cadastrar Novo Usuário
                </div>
                <div class="card-body">
                    <form id="formUsuario">
                        <input type="hidden" id="id_usuario" name="id_usuario">

                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="fullName" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CPF</label>
                            <input type="text" class="form-control" id="regId" placeholder="Apenas números ou formatado" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Perfil do Usuário</label>
                            <select class="form-select" id="profileType" required>
                                <option value="">Selecione...</option>
                                <option value="aluno">Estagiário (Aluno)</option>
                                <option value="orientador">Orientador (Professor)</option>
                                <option value="concedente">Supervisor (Empresa)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" id="labelSenha">Senha</label>
                            <input type="password" class="form-control" id="password" required>
                            <small class="text-muted d-none" id="helpSenha">Deixe em branco para manter a senha atual.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="btnSalvar">Salvar Usuário</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2 d-none" id="btnCancelarEdicao">Cancelar Edição</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Usuários Cadastrados</span>
                    <button class="btn btn-sm btn-light" onclick="carregarUsuarios()">🔄 Atualizar Lista</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaUsuarios">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    carregarUsuarios();

    const form = document.getElementById("formUsuario");
    const btnCancelar = document.getElementById("btnCancelarEdicao");

    // SUBMIT: Cadastrar ou Editar
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const id = document.getElementById("id_usuario").value;
        const modoEdicao = id !== "";

        // Monta o payload baseado no arquivo que vai receber
        let url = modoEdicao ? 'editar.php' : 'cadastro.php';
        let dados = {};

        if (modoEdicao) {
            // Estrutura esperada por editar.php
            dados = {
                id_usuario: id,
                nome: document.getElementById("fullName").value,
                email: document.getElementById("email").value,
                perfil: document.getElementById("profileType").value, // nota: editar.php espera o perfil final bruto ('estagiario', etc.)
                senha: document.getElementById("password").value || null
            };
            // Tratamento caso venha os mapeamentos do select do cadastro
            if(dados.perfil === "aluno") dados.perfil = "estagiario";
            if(dados.perfil === "concedente") dados.perfil = "supervisor";
        } else {
            // Estrutura mapeada por cadastro.php
            dados = {
                fullName: document.getElementById("fullName").value,
                regId: document.getElementById("regId").value,
                email: document.getElementById("email").value,
                profileType: document.getElementById("profileType").value,
                password: document.getElementById("password").value
            };
        }

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(dados)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.mensagem || "Erro na requisição");
            return data;
        })
        .then(data => {
            alert(data.mensagem);
            resetarFormulario();
            carregarUsuarios();
        })
        .catch(err => alert("Erro: " + err.message));
    });

    // Cancelar Edição
    btnCancelar.addEventListener("click", resetarFormulario);
});

// FUNÇÃO: Buscar dados da lista.php e renderizar
function carregarUsuarios() {
    fetch("lista.php")
    .then(res => res.json())
    .then(usuarios => {
        const tbody = document.getElementById("tabelaUsuarios");
        tbody.innerHTML = "";

        if(usuarios.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>`;
            return;
        }

        usuarios.forEach(user => {
            // Bagde colorida por tipo de perfil
            let badgeColor = "bg-secondary";
            if(user.perfil === 'admin') badgeColor = "bg-danger";
            if(user.perfil === 'orientador') badgeColor = "bg-success";
            if(user.perfil === 'estagiario') badgeColor = "bg-info text-dark";

            tbody.innerHTML += `
                <tr>
                    <td><strong>${user.nome}</strong></td>
                    <td>${user.cpf}</td>
                    <td>${user.email}</td>
                    <td><span class="badge ${badgeColor}">${user.perfil}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning me-1" onclick='prepararEdicao(${JSON.stringify(user)})'>Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="excluirUsuario(${user.id_usuario})">Excluir</button>
                    </td>
                </tr>
            `;
        });
    })
    .catch(err => console.error("Erro ao listar:", err));
}

// FUNÇÃO: Jogar os dados da tabela de volta pro formulário para Editar
function prepararEdicao(user) {
    document.getElementById("formCardHeader").innerText = "Editar Usuário";
    document.getElementById("formCardHeader").className = "card-header bg-warning text-dark";
    
    document.getElementById("id_usuario").value = user.id_usuario;
    document.getElementById("fullName").value = user.nome;
    document.getElementById("email").value = user.email;
    
    // Bloqueia CPF na edição para evitar quebra de regras
    document.getElementById("regId").value = user.cpf;
    document.getElementById("regId").disabled = true;

    document.getElementById("profileType").value = user.perfil;

    // Ajustes do campo de senha para edição
    document.getElementById("password").required = false;
    document.getElementById("helpSenha").classList.remove("d-none");
    document.getElementById("labelSenha").innerText = "Nova Senha (Opcional)";

    document.getElementById("btnCancelarEdicao").classList.remove("d-none");
    document.getElementById("btnSalvar").className = "btn btn-warning w-100 text-dark";
}

// FUNÇÃO: Disparar requisição para excluir.php
function excluirUsuario(id) {
    if (confirm("Tem certeza absoluta que deseja excluir este usuário?")) {
        fetch("excluir.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_usuario: id })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensagem);
            carregarUsuarios();
        })
        .catch(err => console.error("Erro ao deletar:", err));
    }
}

// FUNÇÃO: Limpar campos e voltar o formulário ao estado original de Cadastro
function resetarFormulario() {
    document.getElementById("formCardHeader").innerText = "Cadastrar Novo Usuário";
    document.getElementById("formCardHeader").className = "card-header bg-primary text-white";
    
    document.getElementById("formUsuario").reset();
    document.getElementById("id_usuario").value = "";
    
    document.getElementById("regId").disabled = false;
    document.getElementById("password").required = true;
    document.getElementById("helpSenha").add("d-none");
    document.getElementById("labelSenha").innerText = "Senha";

    document.getElementById("btnCancelarEdicao").classList.add("d-none");
    document.getElementById("btnSalvar").className = "btn btn-primary w-100";
}
</script>
</body>
</html>