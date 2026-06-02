<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE (Professor Orientador)</title>
    <link rel="stylesheet" href="professor.orin.STG.css">
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Estágios</h2></div>
        <ul class="nav-links">
            <li class="section-title">PEDAGÓGICO</li>
            <li onclick="navegar('view-professor')" style="cursor:pointer">Monitorar Estagiários</li>
            <li onclick="navegar('view-atividades')" style="cursor:pointer">Enviar Atividades</li>
            
            <li class="section-title">SISTEMA</li>
            <li onclick="navegar('view-validar')" style="cursor:pointer">Gerenciar Fluxo</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <img src="if.png" alt="logo">
            <h1 id="main-title">Professor Orientador</h1>
        </header>

        <section id="view-professor" class="view-section active">
            <div class="table-section">
                <h2>Painel de Orientação</h2>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Estagiário</th>
                            <th>Empresa</th>
                            <th>Status Atual</th>
                            <th>Documentos</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-prof-alunos"></tbody>
                </table>
            </div>
        </section>

        <section id="view-atividades" class="view-section">
            <div class="table-section">
                <h2>Enviar Atividade para o Aluno</h2>
                <div style="margin-top: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                    <label style="display:block; margin-bottom: 8px; font-weight: bold;">Título da Atividade:</label>
                    <input type="text" id="tit-atividade" placeholder="Ex: Relatório de Fevereiro" style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ccc;">
                    
                    <label style="display:block; margin-bottom: 8px; font-weight: bold;">Descrição/Instruções:</label>
                    <textarea id="desc-atividade" rows="4" placeholder="Descreva o que o aluno deve fazer..." style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ccc; font-family: sans-serif;"></textarea>
                    
                    <button class="btn-primary" onclick="enviarAtividade()" style="background: #05c46b; padding: 10px 20px;">Publicar no Portal do Aluno</button>
                </div>

                <h3 style="margin-top: 30px;">Atividades Enviadas</h3>
                <table style="margin-top: 10px;">
                    <thead>
                        <tr><th>Data</th><th>Título</th><th>Ação</th></tr>
                    </thead>
                    <tbody id="historico-atividades"></tbody>
                </table>
            </div>
        </section>

        <section id="view-validar" class="view-section">
            <div class="table-section">
                <h2>Gerenciar Status do Fluxo</h2>
                <table>
                    <thead>
                        <tr><th>Nome</th><th>Empresa</th><th>Status</th><th>Resp.</th><th>Ação</th></tr>
                    </thead>
                    <tbody id="tabela-estagios"></tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const estiloBtn = "padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; margin-right: 5px; font-size: 12px; color: white;";
        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');

        // Menu Logic
        sb.style.transition = "all 0.3s ease";
        ct.style.transition = "all 0.3s ease";
        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => { sb.style.left = "0"; ct.style.marginLeft = "250px"; });
        sb.addEventListener('mouseleave', () => { sb.style.left = "-250px"; ct.style.marginLeft = "0"; });

        function navegar(id) {
            document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            if(id === 'view-professor') carregarProfessor();
            if(id === 'view-validar') carregar();
            if(id === 'view-atividades') carregarHistoricoAtividades();
        }

        // --- FUNÇÃO DE ENVIAR ATIVIDADE ---
        function enviarAtividade() {
            const titulo = document.getElementById('tit-atividade').value;
            const desc = document.getElementById('desc-atividade').value;
            const data = new Date().toLocaleDateString('pt-BR');

            if(!titulo || !desc) return alert("Preencha o título e a descrição!");

            const atividades = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            atividades.push({ data, titulo, desc });
            localStorage.setItem('sge_atividades', JSON.stringify(atividades));

            alert("Atividade enviada com sucesso!");
            document.getElementById('tit-atividade').value = '';
            document.getElementById('desc-atividade').value = '';
            carregarHistoricoAtividades();
        }

        function carregarHistoricoAtividades() {
            const lista = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            const tbody = document.getElementById('historico-atividades');
            tbody.innerHTML = '';
            lista.forEach((at, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${at.data}</td>
                    <td>${at.titulo}</td>
                    <td><button onclick="removerAtividade(${index})" style="${estiloBtn} background:#e74c3c">Excluir</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function removerAtividade(index) {
            const lista = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            lista.splice(index, 1);
            localStorage.setItem('sge_atividades', JSON.stringify(lista));
            carregarHistoricoAtividades();
        }

        // Funções de Gerenciamento de Estágios (Sincronizadas)
        function salvar() {
            const dados = [];
            document.querySelectorAll('#tabela-estagios tr').forEach(tr => {
                dados.push({
                    nome: tr.cells[0].innerText,
                    empresa: tr.cells[1].innerText,
                    status: tr.querySelector('.badge').innerText,
                    badgeCls: tr.querySelector('.badge').className,
                    resp: tr.cells[3].innerText
                });
            });
            localStorage.setItem('sge_data', JSON.stringify(dados));
        }

        function carregar() {
            const dados = JSON.parse(localStorage.getItem('sge_data') || '[]');
            const tbody = document.getElementById('tabela-estagios');
            tbody.innerHTML = '';
            dados.forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${d.nome}</td><td>${d.empresa}</td><td><span class="${d.badgeCls}">${d.status}</span></td><td>${d.resp}</td><td class="acoes"></td>`;
                const acoes = tr.querySelector('.acoes');
                const btnNext = document.createElement('button');
                btnNext.innerText = "Avançar";
                btnNext.style.cssText = estiloBtn + "background: #007bff;";
                btnNext.onclick = () => {
                    if(d.status === "Abertura") { d.status = "Em andamento"; d.badgeCls = "badge status-plano"; }
                    else if(d.status === "Em andamento") { d.status = "Concluído"; d.badgeCls = "badge status-manutencao"; }
                    salvar(); carregar();
                };
                acoes.appendChild(btnNext);
                tbody.appendChild(tr);
            });
        }

        function carregarProfessor() {
            const dados = JSON.parse(localStorage.getItem('sge_data') || '[]');
            const tbody = document.getElementById('tabela-prof-alunos');
            tbody.innerHTML = '';
            dados.filter(x => x.status !== "Abertura").forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${d.nome}</strong></td>
                    <td>${d.empresa}</td>
                    <td><span class="badge ${d.badgeCls}">${d.status}</span></td>
                    <td>📄 Plano.pdf</td>
                    <td><button class="btn-primary" onclick="alert('Validado!')">Validar</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        window.onload = () => { 
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
            carregar(); carregarProfessor(); carregarHistoricoAtividades();
        };
    </script>
</body>
</html>