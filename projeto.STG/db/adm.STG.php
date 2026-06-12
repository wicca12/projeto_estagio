<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE (Administração)</title>
    <link rel="stylesheet" href="adm.STG.css">
</head>
<body>

    <button id="btn-toggle-menu" class="btn-menu-trigger">☰</button>

    <nav class="sidebar" id="sidebar">
        <div class="logo"><h2>SGE Estágios</h2></div>
        <ul class="nav-links">
            <li class="section-title">ADMINISTRAÇÃO</li>
            <li>Cadastrar Cursos/Empresas</li>
            <li>Validar Novos Estágios</li>
            <li class="section-title">PEDAGÓGICO</li>
            <li onclick="window.location.href='professor.orin.STG.html'" style="cursor:pointer">Avaliar Planos (Orientador)</li>
        </ul>
    </nav>

    <main class="content" id="main-content">
        <header class="top-bar">
            <img src="if.png" alt="logo">
            <h1>IFSertãoPE - SGE</h1>
        </header>
        
        <section class="stats-container">
            <div class="card border-blue"><h3>Abertura</h3><p id="count-abertura">0 Pendentes</p></div>
            <div class="card border-yellow"><h3>Em Andamento</h3><p id="count-andamento">0 Aguardando</p></div>
            <div class="card border-green"><h3>Concluídos</h3><p id="count-concluido">0 Finalizados</p></div>
            <div class="card border-red"><h3>Total</h3><p id="count-total">0 Registros</p></div>
        </section>

        <section class="table-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Fluxo de Estágios em Andamento</h2>
                <button id="btn-adicionar" style="background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">+ Adicionar Card</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Estagiário / Tipo</th>
                        <th>Empresa</th>
                        <th>Status do Fluxo</th>
                        <th>Responsável Atual</th>
                        <th>Ação Próxima</th>
                    </tr>
                </thead>
                <tbody id="tabela-estagios"></tbody>
            </table>
        </section>
    </main>

    <script>
        const estiloBotoes = "width: 100px; height: 35px; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; margin-right: 5px;";
        const tabela = document.getElementById('tabela-estagios');
        const sb = document.getElementById('sidebar');
        const ct = document.getElementById('main-content');

        // --- LÓGICA DO MENU (SIDEBAR) ---
        // Abre o menu ao passar o mouse no botão
        document.getElementById('btn-toggle-menu').addEventListener('mouseenter', () => {
            sb.style.left = "0";
            ct.style.marginLeft = "250px";
        });

        // Fecha o menu ao tirar o mouse da barra lateral
        sb.addEventListener('mouseleave', () => {
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
        });

        // --- LÓGICA DE DADOS ---
        function salvarDados() {
            const linhas = [];
            tabela.querySelectorAll('tr').forEach(linha => {
                linhas.push({
                    nome: linha.dataset.nome,
                    empresa: linha.cells[1].innerText,
                    status: linha.querySelector('.badge').innerText,
                    badgeClass: linha.querySelector('.badge').className,
                    responsavel: linha.cells[3].innerText,
                    tipo: linha.dataset.tipo
                });
            });
            localStorage.setItem('sge_data', JSON.stringify(linhas));
            atualizarCards();
        }

        function carregarDados() {
            const dados = JSON.parse(localStorage.getItem('sge_data') || "[]");
            tabela.innerHTML = "";
            dados.forEach(item => {
                adicionarLinhaNaTabela(item.nome, item.empresa, item.status, item.badgeClass, item.responsavel, item.tipo);
            });
            atualizarCards();
        }

        function atualizarCards() {
            const linhas = tabela.querySelectorAll('tr');
            let abertura = 0, andamento = 0, concluido = 0;
            linhas.forEach(linha => {
                const badge = linha.querySelector('.badge');
                if (badge) {
                    const status = badge.innerText.toLowerCase();
                    if (status === "em andamento") andamento++;
                    else if (status === "concluído") concluido++;
                    else abertura++;
                }
            });
            document.getElementById('count-abertura').innerText = `${abertura} Pendentes`;
            document.getElementById('count-andamento').innerText = `${andamento} Aguardando`;
            document.getElementById('count-concluido').innerText = `${concluido} Finalizados`;
            document.getElementById('count-total').innerText = `${linhas.length} Registros`;
        }

        function adicionarLinhaNaTabela(nome, empresa, statusTxt = "Abertura", badgeClass = "badge status-abertura", responsavel = "Admin", tipo = "Obrigatório") {
            const tr = document.createElement('tr');
            tr.dataset.nome = nome;
            tr.dataset.tipo = tipo;
            tr.innerHTML = `
                <td><strong>${nome}</strong><br><small style="color:gray">${tipo}</small></td>
                <td>${empresa}</td>
                <td><span class="${badgeClass}">${statusTxt}</span></td>
                <td>${responsavel}</td>
                <td class="acoes"></td>
            `;
            renderizarBotoes(tr, statusTxt);
            tabela.appendChild(tr);
        }

        function renderizarBotoes(linha, status) {
            const celulaAcao = linha.querySelector('.acoes');
            const badge = linha.querySelector('.badge');
            celulaAcao.innerHTML = "";

            const btnExcluir = document.createElement('button');
            btnExcluir.innerText = "Excluir";
            btnExcluir.style.cssText = estiloBotoes + "background: #e74c3c; color: white;";
            btnExcluir.onclick = () => { if(confirm("Excluir?")) { linha.remove(); salvarDados(); } };

            if (status === "Abertura") {
                const btnAprovar = document.createElement('button');
                btnAprovar.innerText = "Aprovar";
                btnAprovar.style.cssText = estiloBotoes + "background: #007bff; color: white;";
                btnAprovar.onclick = () => {
                    badge.innerText = "Em andamento";
                    badge.className = "badge status-plano";
                    renderizarBotoes(linha, "Em andamento");
                    salvarDados();
                };
                celulaAcao.appendChild(btnAprovar);
            } else if (status === "Em andamento") {
                const btnFeito = document.createElement('button');
                btnFeito.innerText = "Concluir";
                btnFeito.style.cssText = estiloBotoes + "background: #27ae60; color: white;";
                btnFeito.onclick = () => {
                    badge.innerText = "Concluído";
                    badge.className = "badge status-manutencao";
                    renderizarBotoes(linha, "Concluído");
                    salvarDados();
                };
                celulaAcao.appendChild(btnFeito);
            }
            celulaAcao.appendChild(btnExcluir);
        }

        document.getElementById('btn-adicionar').addEventListener('click', () => {
            const nome = prompt("Nome do Estagiário:");
            const empresa = prompt("Nome da Empresa:");
            const tipo = prompt("Tipo de Estágio (Obrigatório / Não Obrigatório):", "Obrigatório");
            if (nome && empresa) {
                adicionarLinhaNaTabela(nome, empresa, "Abertura", "badge status-abertura", "Admin", tipo);
                salvarDados();
            }
        });

        // Inicializa com menu fechado e carrega dados
        window.onload = () => {
            sb.style.left = "-250px";
            ct.style.marginLeft = "0";
            carregarDados();
        };
    </script>
</body>
</html>