# Chatbot_api (Em desenvolvimento...)
API para chat e chatbot de suporte, ainda em desenvolvimento.


<b>Andamento do Chat:</b>

- [x] Criar servidor WebSocket para chat.
- [x] Criar tabela para salvar as conversas do chat/chatbot.
- [x] Estabelecer conexão reservada e troca de mensagens entre o atendente o cliente.
- [x] Consulta do histórico de conversas por atendente/cliente/data e hora de inicio e fim.
- [x] Consultar quantidade de usuários online no total (atendentes + clientes)
- [ ] Receber e salvar dados da abertura do atendimento no db.
- [x] Criar sala de espera dos clientes para atendimento e sala para os atendentes.
- [ ] Listar clientes da sala de espera por ordem de chegada.
- [ ] Retirar cliente da sala de espera ao iniciar o atendimento.
- [ ] Receber dados de avaliação do atendimento, salvar e finalizar a sessão do cliente.
- [ ] Consultar dados dos clientes.
- [ ] Mudar status do atendimento.
- [ ] Criar span de envio para o cliente da posição dele na fila de espera.
- [x] Criar tabela de atendimento (atendente,cliente,status,assunto,avaliação,data-hora-inicio,data-hora-fim).
- [x] Criar tabela de usuários (usuário,nome,imagem,instituição,email).
- [x] Criar tabela de atendentes.
- [x] Autentificação JWT.
- [x] Rota para criação do 1º token JWT
- [x] Rota para criação do 2º token JWT 


<i><b>E o andamento do bot?</b> Algumas coisas do bot já foram feitas/iniciadas como a implementação das lib's PHP nlp-tools e botman, por hora, essa parte está aguardando o desenvolvimento do chat para dar continuidade o desenvolvimento do bot.</i>

## Etapas do Desenvolvimento
O projeto foi separado entre o front-end e o back-end.<br>
Este repositório contém apenas o back-end de todo o projeto. O front-end está em outro repositório sendo desenvolvimento em VueJs.

A primeira etapa do back-end será o desenvolvimento do chat e a segunda será o desenvolvimento do chatbot com NLP. 

## Dinâmica e Regras de Negócio
Essa aplicação terá dois ambientes sendo o <b>Panel Chat</b> dos atendentes e o <b>Box Chat</b> dos clientes.<br>
<p><b>Atenção:</b> Os clientes já devem estar cadastrados previamente assim como os atendentes, pois os dados de identificação do cliente serão consultados a partir de um ID informado.</p>

## WebSocket
A conexão WebSocket com o servidor de chat será aberta apenas quando o cliente enviar o fomulário de abertura de atendimento e será encerrada nas seguintes situações:
- Cliente ou atendente fechou o navegador - será informado com uma mensagem padrão que o outro perdeu a conexão, isto também pode ocorrer se não houver conexão com a internet.
- Atendente encerrou o atendimento - apenas a conexão do cliente e fechada.

### Box Chat
O cliente ao clicar para abrir a caixa de chat, será exibido um formulário da abertura do atendimento com os campos:
- Dúvida ou Problema?
- Descrição

Outras informações serão enviadas automaticamente como o id do cliente para consulta no banco de dados.

Quando o cliente enviar dos dados da abertura do atendimento o Bot previamente configurado deverá responder caso for uma dúvida do seu conhecimento, caso contrário, dever encaminhar para um atendente. <br>
Após o encaminhamento, o cliente ficará na fila de espera até um estiver com status de livre para atender, até lá, o cliente receberá em realtime no seu Box Chat o status da fila em número decrescente até chegar sua vez para ser atendido. Quando o atendente estiver com o status disponível/livre, poderá clicar em um dos clientes da lista de espera e iniciar o atendimento.

<b>Nota:</b> O número de espera minimo que aparecerá para o cliente será de "1", nunca será "0" mesmo que todos dos atendentes estejam com status de disponível/livre. Assim, os tendentes não precisarão mudar seu status de atendimento.

### Panel Chat
O Painel de Chat será a tela de atendimento, nela terá:
- Botão para abrir chamado a partir do atendimento.
- Botão para mudar o status do atendimento (Em atendimento, em espera, Encerrado).
- Visualização da área de espera - lista de clientes que estão na espera do atendimento por ordem de chegada.
- Lista de clientes com conversa finalizada por status, clientes, escola, assunto e data.
- Configuração dos horários de atendimento com respostas automáticas. Por ex.: Horário de Almoço.
- Alerta e contagem de novos clientes em espera.
- Botão de envio de avaliação

<p><b>Atenção:</b> Os atendentes devem estar cadastrados previamente. Essa necessidade será apenas para identificação no Painel de Chat através de um ID que vincula a configuração do perfíl, já para o cliente, a identificação do atendente não será mostrada.</p>

### Avaliação do Atendimento
Apos atendente encerrar o atendimento, será enviado por padrão, um formulário de avaliação contendo um campo com 5 opções de nota (1-5 obrigatório) e um campo para comentário (opcional).

## ChatBot
O chatbot deverá ser implementado no atendimento par respostas mais simples e solução de problemas comuns.

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


## Integração

### Rotas
As rotas são URL's para troca de dados e integração com outras aplicações front-end.

Fluxo de acesso ao WebSocket: A conexão é aberta assim que a url é acessada. O cabeçalho da requisição de conexão deverá ter o token de autorização valido ou a conexão será fechada. 

Para o usuário obter o token de autorização ele deverá estar previamente cadastrado no db do chat e a aplicação front-end deverá acessar primeiramente a rota de geração de token e informar os dados obrigatórios. Apos a obtenção do token, a aplicação fornt-end deverá informa-lo no cabeçalho para acesso as demais rotas da API.

<b>Criar token do usuário</b><br>

Exemplo de envio:   
- localhost:81/chatbot_api/api/create/token
>   
    Dados via POST:     
        uuid: "290b7b75-b949-4643-9e11-1cc2214a6882" ou o número do CPF              
        type: "client" ou "attendant"    
        public: "ffc6wwq2eb25f5asasf11a7f1b7546cb3ca"
        name: "Junior - opcional" 
        avatar: "link de uma imagem do usuário - opcional"
        lastname: "sobrenome do usuário - opcional"

    Dados de retorno: 
        {
            "result": true,
            "error": {
                "header": "Authorization",
                "token": "Bearer ...token...",
                "msg": "Token gerado com sucesso!"
            }
        } 
> 

O name | lastname | avatar: são opcionais porque só serão utilizados quando a API tiver que cadastrar um usuário que não existe no db e que informou um CPF válido no campo uuid, nesse caso o name e lastname passam a ser obrigatórios..

<b>Cadastro de Atendente</b><br>

Apenas os atendentes tem permissão para cadastrar outros atendentes ou clientes.

Exemplo de envio:   
- localhost/chatbot_api/api/create/attendant
>   
    Dados via POST:   
        cpf: 000.000.000-00    
        name: "João"  
        lastname: "Junior"     
        avatar: "http://sitedeimagem/imagem.png"          

    Dados de retorno: 
    {
        "result": true,
        "error": {
            "msg": "Cadastro realizado com sucesso!",
            "data": {
                "id": "9",
                "uuid": "290b7b75-b949-4643-9e11-1cc2214a6882"
            }
        }
    }         
> 

<b>Cadastro de Cliente</b><br>

Exemplo de envio:   
- localhost/chatbot_api/api/create/client
>   
    Dados via POST:   
        cpf: 000.000.000-00  
        name: "Maria"  
        lastname: "Oliveira"     
        avatar: "http://sitedeimagem/imagem.png"            

    Dados de retorno: 
    {
        "result": true,
        "error": {
            "msg": "Cadastro realizado com sucesso!",
            "data": {
                "id": "10",
                "uuid": "46b3a264-4ff1-464c-821f-aa9c389b620f"
            }
        }
    }            
> 

Caso o usuário não esteja cadastrado no banco de dados da chat, o mesmo poderá ser cadastrado via terminal também:

>
    php new-user.php
>

<b>Exemplo de implementação no cliente</b>

Criar um aquivo js e importa-lo na home da pagina após o login do usuário.<br>
Confira um exemplo de implementação pasta ./examples
<br><br>

<b>PORT do WebSocket</b><br>

SERVER_CHAT_PORT = porta de conexão com o servidor websocket. Essa porta pode ser configurada no arquivo src\config\app.php

Exemplos para troca de mensagens: 
>
    ws://localhost:SERVER_CHAT_PORT/api/... 
>

<b>Troca de Mensagens</b><br>

Exemplos para troca de mensagens: 
- ws://localhost:8081/api/attendant
- ws://localhost:8081/api/client

>    
    Dados via POST:     
    Dados de envio: {  
        "cmd": "msg",   
        "driver": "web",  
        "userId": 1,  
        "userDestId": 2,    
        "text": "ola 1",    
        "type": "text", 
        "time": "10:30",    
        "attachment":null  
    } 

    Dados de retorno: N/A.    
> 

<b>Quantidade Online</b><br>

Exemplos para consulta da quantidade online:   
- ws://localhost:8081/api/attendant 
- ws://localhost:8081/api/client 

>    
    Dados via POST:     
    Dados: {  
        "cmd": "n_on",          
        "userId": 1,          
        "qtd": ""    
    }   

    Dados de retorno: {  
        "cmd": "n_on",          
        "userId": 1,          
        "qtd": 16   
    }     
> 

Os dados de retorno seguem a mesma estrutura de envio caso o outro user esteja offline.

<b>Consultar Histórico de Mensagens</b><br>

Exemplo de envio:   
- localhost/api/history/read
>   
    Dados via POST:     
        user_id: 1  
        user_dest_id: 2     
        dt_start: "2021-06-15 06:00"  
        dt_end: "2021-06-16 18:00"  

    Dados de retorno: [
        {
            "chat_user_id":"1",
            "chat_user_dest_id":"2",
            "chat_text":"Como vai?",
            "chat_type":"text",
            "chat_date":"2021-06-17 01:13:45",
            "chat_attachment":""
        },
        {   "chat_user_id":"2",
            "chat_user_dest_id":"1",
            "chat_text":"Bem e vc?",
            "chat_type":"text",
            "chat_date":"2021-06-17 01:15:21",
            "chat_attachment":""
        },
        ...
    ]          
> 


## Comandos

### Iniciar servidor WebSocket do chat
> php run

### Testes Automatizados - PHPUnit
- Execute o comando para os testes automatizados com PHPUnit
> php vendor/bin/phpunit --testdox --color tests

