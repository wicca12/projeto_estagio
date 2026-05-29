Linguagens de Programação: PHP, CSS(Bootstrap), HTML e JavaScript.

Perfis:
Admin(Setor de estágio) - Cadastra cursos , empresas/escolas e orientadores, valida a abertura de novos estágios e emite termos finais.
Orientador(Professor) - Avalia e aprova o Plano de Atividades, valida relatórios parciais/finais e atribui nota/parecer pedagógico.
Supervisor - Valida a folha de pontos/horas e preenche a ficha de avaliação de desempenho do estagiário.
Estagiário(Aluno) - Inicia pedido de estágio preenchendo dados da empresa, envia documentos (TCE, Relatórios) e visualiza o status do processo.


Fluxo do Sistema:
Abertura: O estagiário solicita o estágio preenchendo os dados da empresa e do supervisor. O admin revisa e aprova a conformidade legal. 
Plano de Trabalho: O estagiário envia o plano de atividades e o orientador aprova ou solicita ajustes. 
Manutenção: Mensalmente (ou a cada 3 meses), o Estagiário envia o relatório, o supervisor valida as horas e o Orientador dá o parecer. 
Finalização: O Estagiário envia o relatório final, o supervisor e orientador avaliam o admin encerra e gera a certidão. 


Banco de Dados:
Usuários: id_usuario(sesseion_start()), e mail, senha_hash, cpf, perfil(admin, orientador, supervisor, estagiário)(session_start()).
Empresas: id, razao_social, cnpj, convenio_ativo.
Estagios: id, estagiario_id, orientador_id, supervisor_id, empresa_id, data_inicio, data_fim, status (pendente, ativo, concluido, cancelado). 
Documentos: id, estagio_id, tipo (TCE, carta_aceite_orientador, PDE, Relatorio_Parcial, Relatorio_Final), url_arquivo, status_id($_FILES). Quando o orientador clica em "Aprovar Plano", o PHP roda um UPDATE estagios SET status = 'ativo' WHERE id = ... 
Dinamismo: Se o estagiário selecionar que o relatório é "Parcial", o JS pode mostrar um campo de "horas acumuladas". Se selecionar "Final", o JS esconde esse campo e mostra o campo de "Nota". 
