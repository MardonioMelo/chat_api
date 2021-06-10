# Chatbot_api (Em desenvolvimento...)
API para chat e chatbot de suporte.

## Etapas do Desenvolvimento
O projeto foi separado entre o front-end e o back-end.<br>
Este repositório contém apenas o back-end de todo o projeto. O front-end está em outro repositório sendo desenvolvimento em VueJs.

A primeira etapa do back-end será o desenvolvimento do chat e a segunda será o desenvolvimento do chatbot com NLP. 

## Dinâmica e Regras de Negócio
<p>Essa aplicação terá dois ambientes sendo o <b>Panel Chat</b> dos atendentes e o <b>Box Chat</b> dos clientes.</p>
<p><b>Atenção:</b> Os clientes já devem estar cadastrados previamente, pois os dados de identificação do cliente serão consultados a partir de um ID informado.</p>

### Box Chat
O cliente ao clicar para abrir a caixa de chat, será exibido um formulário da abertura do atendimento com os campos:
- Dúvida ou Problema?
- Descrição

Outras informações serão enviadas automaticamente como o id do cliente para consulta no banco de dados.

Quando o cliente enviar dos dados da abertura do atendimento o Bot previamente configurado deverá responder caso for uma dúvida do seu conhecimento, caso contrário, dever encaminhar para um atendente. <br>
Após o encaminhamento, o cliente ficará na fila de espera até um estiver com status de livre para atender, até lá, o cliente receberá em realtime no seu Box Chat o status da fila em número decrescente até chegar sua vez para ser atendido.

Quando o atendente estiver com o status disponível/livre, poderá clicar em um dos clientes da lista de espera e iniciar o atendimento.

<b>Nota:</b> O número de espera minimo que aparecerá para o cliente será de "1", nunca será "0" mesmo que todos dos atendentes estejam com status de disponível/livre. Assim, os tendentes não precisarão mudar seu status de atendimento.

### Panel Chat
O Painel de Chat será a tela de atendimento, nela terá:
- Botão para abrir chamado a partir do atendimento.
- Botão para mudar o status do atendimento (Em atendimento, em espera, Encerrado).
- Visualização da área de espera - lista de clientes que estão na espera do atendimento por ordem de chegada.
- Lista de clientes com conversa finalizada por status, clientes, escola, assunto e data.
- Terá configuração dos horários de atendimento com respostas automáticas. Por ex.: Horário de Almoço.

O chatbot também deverá ser implementado no chat de atendimento par respostas mais simples e solução de problemas comuns.

Os atendentes devem estar cadastrados previamente de atendentes com nome genéricos como Atendente 01.

### Tela Config


## ChatBot
### Arquivos de Treino do Bot
Estilo do documento:

	1-instrução: |1
	2-instrução: Nome único desse arquivo sem caracteres especiais e inciado com letra ex: t_01_teste.
	3-instrução: |2
    4-instrução: Nome da intenção no máximo 3 palavras.
    5-instrução: |3
    6-instrução: O que o usuário pode dizer, no mínimo 3 exemplos, sendo cada exemplo em uma linha.
    7-instrução: |4
    8-instrução: Que bot vai responder, para resposta aleatórias escreva na próxima linha.
    9-instrução: |5

    * Use uma barra vertical '|' e um número de 1 a 5 nas instruções 1, 3, 5, 7 e 9. 
    * É possível incluir no mesmo arquivo mais de uma treinamento, basta reiniciar as instruções do n.1 a n.9.

### Teste NLP
- Informe no parâmetro message o texto "nlp" para modo de teste.
- O teste vai consultar os dados do arquivo treino/testing.json.
- Verifique o retorno da mensagem para analisar o resultado. 

## Comandos

### Iniciar servidor WebSocket do chat
> php server.php

### Testes Automatizados - PHPUnit
- Execute o comando para os testes automatizados com PHPUnit
> php vendor/bin/phpunit --testdox --color tests

