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
            <li onclick="navegar('view-atividades')" style="cursor:pointer">Lançar Atividades</li>
            
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
            <div class="table-section" style="margin-bottom: 20px; display: flex; align-items: center; gap: 20px; border-left: 8px solid #05c46b;">
                <div style="width: 70px; height: 70px; background: #2c3e50; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">PO</div>
                <div>
                    <h2>Painel de Orientação</h2>
                    <p>Visualize o progresso e valide os documentos dos seus alunos.</p>
                </div>
            </div>

            <div class="table-section">
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Estagiário</th>
                            <th>Empresa</th>
                            <th>Status Atual</th>
                            <th>Documentos Enviados</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-prof-alunos"></tbody>
                </table>
            </div>
        </section>

        <section id="view-atividades" class="view-section">
            <div class="table-section">
                <h2>Lançar Nova Atividade/Aviso</h2>
                <p style="color: gray; margin-bottom: 15px;">A atividade enviada aparecerá instantaneamente no portal do aluno.</p>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                    <input type="text" id="at-titulo" placeholder="Título da Atividade (ex: Entrega de Relatório Mensal)" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <textarea id="at-desc" placeholder="Instruções para o aluno..." style="width: 100%; padding: 10px; height: 80px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: sans-serif;"></textarea>
                    <button class="btn-primary" onclick="enviarAtividade()" style="background: #05c46b; width: 200px;">Enviar para Alunos</button>
                </div>

                <h3 style="margin-top: 30px;">Atividades Enviadas</h3>
                <table style="margin-top: 10px;">
                    <thead>
                        <tr><th>Data</th><th>Título</th><th>Ação</th></tr>
                    </thead>
                    <tbody id="tabela-atividades-historico"></tbody>
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

        // Sidebar Animation
        sb.style.transition = "all 0.3s ease";
        ct.style.transition = "all 0.3s ease";
        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => { sb.style.left = "0"; ct.style.marginLeft = "250px"; });
        sb.addEventListener('mouseleave', () => { sb.style.left = "-250px"; ct.style.marginLeft = "0"; });

        function navegar(id) {
            document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            
            const titulos = {
                'view-professor': 'Professor Orientador',
                'view-validar': 'Gerenciar Fluxo',
                'view-atividades': 'Lançar Atividades'
            };
            document.getElementById('main-title').innerText = titulos[id] || "SGE";
            
            if(id === 'view-professor') carregarProfessor();
            if(id === 'view-validar') carregar();
            if(id === 'view-atividades') carregarAtividadesProfessor();
        }

        // --- LÓGICA DE ATIVIDADES ---
        function enviarAtividade() {
            const titulo = document.getElementById('at-titulo').value;
            const desc = document.getElementById('at-desc').value;
            if(!titulo || !desc) return alert("Preencha todos os campos!");

            const novaAt = {
                id: Date.now(),
                data: new Date().toLocaleDateString('pt-BR'),
                titulo: titulo,
                desc: desc
            };

            const lista = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            lista.push(novaAt);
            localStorage.setItem('sge_atividades', JSON.stringify(lista));

            alert("Atividade enviada com sucesso!");
            document.getElementById('at-titulo').value = "";
            document.getElementById('at-desc').value = "";
            carregarAtividadesProfessor();
        }

        function carregarAtividadesProfessor() {
            const lista = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            const tbody = document.getElementById('tabela-atividades-historico');
            tbody.innerHTML = '';
            lista.reverse().forEach(at => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${at.data}</td>
                    <td><strong>${at.titulo}</strong></td>
                    <td><button onclick="removerAtividade(${at.id})" style="${estiloBtn} background: #e74c3c;">Excluir</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function removerAtividade(id) {
            if(!confirm("Deseja apagar esta atividade?")) return;
            let lista = JSON.parse(localStorage.getItem('sge_atividades') || '[]');
            lista = lista.filter(at => at.id !== id);
            localStorage.setItem('sge_atividades', JSON.stringify(lista));
            carregarAtividadesProfessor();
        }

        // --- LÓGICA DE ESTÁGIOS ---
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
                    <td><span class="badge ${d.badgeCls}">${d.status === "Em andamento" ? "Aguardando Relatórios" : "Estágio Concluído"}</span></td>
                    <td>📄 Plano.pdf</td>
                    <td><button class="btn-primary" onclick="alert('Validado!')">Validar</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        window.onload = () => { 
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
            carregar(); 
            carregarProfessor(); 
        };
    </script>
</body>
</html>