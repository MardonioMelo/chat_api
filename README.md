# Chatbot_api (Em desenvolvimento...)
API para chat e chatbot de suporte, ainda em desenvolvimento.


<b>Andamento do Chat:</b>

- [x] Criar servidor WebSocket para chat.
- [x] Criar tabela para salvar as conversas do chat/chatbot.
- [x] Estabelecer conexão reservada e troca de mensagens entre o atendente o cliente.
- [x] Consulta do histórico de conversas por atendente/cliente/data e hora de inicio e fim com limit e offset.
- [x] Consultar quantidade de usuários online no total (atendentes + clientes)
- [x] Criar tabela de atendimento (atendente,cliente,status,assunto,avaliação,data-hora-inicio,data-hora-fim).
- [x] Criar tabela de usuários (usuário,nome,imagem,instituição,email).
- [x] Criar tabela de atendentes.
- [x] Autentificação JWT.
- [x] Rota para gerar token JWT
- [x] Criar sala de espera dos clientes para atendimento e sala para os atendentes.
- [x] Consultar dados dos clientes e atendentes.
- [ ] Receber e salvar dados da abertura do atendimento no db.
- [ ] Listar clientes da sala de espera por ordem de chegada.
- [ ] Retirar cliente da sala de espera ao iniciar o atendimento.
- [ ] Receber dados de avaliação do atendimento, salvar e finalizar a sessão do cliente.
- [ ] Criar span de envio para o cliente da posição dele na fila de espera.
- [ ] Mudar status do atendimento.


<i><b>E o andamento do bot?</b> Algumas coisas do bot já foram feitas/iniciadas como a implementação das lib's PHP nlp-tools e botman, por hora, essa parte está aguardando o desenvolvimento do chat para dar continuidade o desenvolvimento do bot.</i>

## Etapas do Desenvolvimento
O projeto foi separado entre o front-end e o back-end.<br>
Este repositório contém apenas o back-end de todo o projeto. O front-end está em outro repositório sendo desenvolvimento em Vue3.

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

[Clique aqui](https://viewer.diagrams.net/?highlight=0000ff&edit=_blank&layers=1&nav=1&title=Diagrama%20do%20Chat#Uhttps%3A%2F%2Fdrive.google.com%2Fuc%3Fid%3D13BHcugWv8KVK3ha1CztGjqo_SD-VmPBF%26export%3Ddownload) para ver o Diagrama de geração do token

Exemplo de envio:   
- POST: localhost/chatbot_api/api/token
>   
    Content-Type: multipart/form-data
    Dados:     
        uuid: string              
        type: string    
        public: string
        name: string 
        avatar: string
        lastname: string

    Dados de retorno: 
        {
            "result": bool,
            "error": {
                "header": string,
                "token": string,
                "msg": string
            }
        } 
> 

O name | lastname | avatar: são opcionais porque só serão utilizados quando a API tiver que cadastrar um usuário que não existe no db e que informou um CPF válido no campo uuid, nesse caso o name e lastname passam a ser obrigatórios..

<b>Cadastrar Atendente</b><br>

[Clique aqui](https://viewer.diagrams.net/?highlight=0000ff&edit=_blank&layers=1&nav=1&page-id=psJkvXa7-np5yWx2_JBS&title=Diagrama%20do%20Chat#Uhttps%3A%2F%2Fdrive.google.com%2Fuc%3Fid%3D13BHcugWv8KVK3ha1CztGjqo_SD-VmPBF%26export%3Ddownload) para ver o Diagrama do CRUD dos atendentes.

Apenas os atendentes tem permissão para cadastrar outros atendentes ou clientes.

Descrição da requisição
- uuid: ID único gerado pelo sistema no momento do cadastro do usuário. Caso o usuário não tenha o UUID então deve informar o CPF no lugar.
- name: Nome do usuário.
type: tipo de usuário (client ou attendant).
- public: chave publica padrão definida pelo administrador do sistema.
name | lastname | avatar: são opcionais porque só serão utilizados quando a API tiver que cadastrar um usuário que não existe no db e que informou um CPF válido no campo uuid, nesse caso o name e lastname passam a ser obrigatórios.

Exemplo de envio:   
- POST: localhost/chatbot_api/api/attendant
>   
    Content-Type: multipart/form-data
    Dados:   
        cpf: string    
        name: string  
        lastname: string     
        avatar: string - opcional          

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "id": int,
                "uuid": string
            }
        }
    }         
> 

Descrição da resposta
- result: true ou false.
- error - header: Nome do campo no cabeçalho onde deverá informar o token nas requisições as demais rotas da API. Ex.: Authorization.
- error - token: Token que deverá informar no cabeçalho das requisições. Ex.: Bearer ...token...
- error - msg: Mensagem informativa do resultado. 

<b>Consultar um Cadastro </b><br>

Apenas os atendentes tem permissão para consultar o cadastro de outros atendentes ou clientes.

Descrição da requisição
- ID: id do atendente no final da rota.
- Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT valido obtido anteriormente ex.: Authorization:  Bearer ...token...
- Body: Deve ser informado no cabeçalho da requisição no campo Content-Type o valor "none"

Exemplo de envio:   
- GET: localhost/chatbot_api/api/attendant/{id}
>   
    Content-Type: none
    Informar o id do cadastro no final da rota            

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "id": 1,
                "cpf": int,
                "uuid": string,
                "name": string,
                "lastname": string,
                "avatar": string,
                "created_at": string
                "updated_at": string
            }
        }
    }           
> 

Descrição da resposta
- result: true ou false.
- error - data - id: ID do cadastro.
- error - data - cpf: CPF do atendente.
- error - data - uuid: UUID do atendente.
- error - data - name: Nome.
- error - data - lastname: Sobrenome.
- error - data - avatar: Link da imagem
- error - data - created_at: data de cadastro
- error - data - updated_at: data de atualização

<b>Consultar Todos Cadastros </b><br>

Apenas os atendentes tem permissão para consultar o cadastro de outros atendentes ou clientes.

Descrição da requisição
- limit: limite de registros que serão consultados
- offset: deslocamento, inicio da contagem dos registros a partir do primeiro registro cadastrado.
- Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT - valido obtido anteriormente
ex.: Authorization:  Bearer ...token...
- Body: Deve ser informado no cabeçalho da requisição no campo Content-Type o valor "none"

Exemplo de envio:   
- GET: localhost/chatbot_api/api/attendant?limit=10&offset=0
>   
    Content-Type: none
    Dados:
        limit: int
        offset: int         

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": [
                {
                    "id": ',               
                    "cpf": int,
                    "uuid": string,
                    "name": "string",
                    "lastname": "string",
                    "avatar": "string",
                    "updated_at": "string",
                    "created_at": "string"
                }, ...   
            ],
        "count": int,
        "next": "string",
        "previous": "string"
    }
}         
> 

Descrição da resposta
- result: true ou false.
- error - data[ ] - id: ID do cadastro
- error - data[ ] - cpf: CPF do atendente.
- error - data[ ] - uuid: UUID do atendente.
- error - data[ ] - name: Nome.
- error - data[ ] - lastname: Sobrenome.
- error - data[ ] - avatar: Link da imagem
- error - data[ ] - created_at: data de cadastro
- error - data[ ] - updated_at: data de atualização


<b>Atualizar Atendente</b><br>

Apenas os atendentes tem permissão para atualizar outros atendentes ou clientes.

Descrição da requisição
- ID: id do atendente no final da rota.
- cpf: CPF do atendente.
- nome: Nome do atendente.
- lastname: Sobrenome do atendente.
- avatar: link de uma imagem do atendente - opcional.
- Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT valido obtido anteriormente
ex.: Authorization:  Bearer ...token...
- Body: Deve ser informado no cabeçalho da requisição no campo Content-Type o valor "application/x-www-form-urlencoded".

Exemplo de envio:   
- PUT: localhost/chatbot_api/api/attendant/{id}
>      
    Informar o id do cadastro no final da rota 
    Content-Type: application/x-www-form-urlencoded. 
    Dados:
        cpf: string    
        name: string  
        lastname: string     
        avatar: string - opcional          

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "id": int,
                "updated_at":string
            }
        }
    }         
> 

Descrição da resposta
- result: true ou false.
- error - data - id: ID do cadastro.
- error - data - cpf: CPF do atendente.
- error - data - name: Nome.
- error - data - lastname: Sobrenome.
- error - data - avatar: Link da imagem
- error - data - created_at: data de cadastro
- error - data - updated_at: data de atualização

<b>Deletar Atendente</b><br>

Apenas os atendentes tem permissão para deletar outros atendentes ou clientes.

Descrição da requisição
- ID: id do atendente no final da rota.
- Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT valido obtido anteriormente
ex.: Authorization:  Bearer ...token...
- Body: Deve ser informado no cabeçalho da requisição no campo Content-Type o valor "none"

Exemplo de envio:   
- DELETE: localhost/chatbot_api/api/attendant/{id}
>      
    Informar o id do cadastro no final da rota 
    Content-Type: none.    

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "id": int              
            }
        }
    }         
> 

Descrição da resposta
- result: true ou false.
- error - data - id: ID do cadastro.

Recomenda-se que os usuários sejam cadastrados através das rotas citadas acima, porém o mesmo poderá ser cadastrado via terminal caso prefira. Esse recurso só deve ser usado para testes, quando ainda não há uma interface para cadastro do usuário ou quando não existem usuários do tipo atendente cadastrado.

>
    php new-user.php
>

<b>CRUD de Clientes</b><br>

O cadastro, consulta, atualização e delete de clientes segue o mesmo fluxo e método do cadastro de atendentes apenas substituindo na rota o  <i>"attendant"</i> por <i>"client"</i>.

[Clique aqui](https://viewer.diagrams.net/?highlight=0000ff&edit=_blank&layers=1&nav=1&page-id=7SM0Ji58Qv2cIDF6IUad&title=Diagrama%20do%20Chat#Uhttps%3A%2F%2Fdrive.google.com%2Fuc%3Fid%3D13BHcugWv8KVK3ha1CztGjqo_SD-VmPBF%26export%3Ddownload) para ver o diagrama do CRUD dos clientes

<b>Consultar Histórico de Mensagens</b><br>

Informe o id do remetente, id do destinatário, data de inicio e fim da troca de mensagens.

Exemplo de envio:   
- localhost:81/chatbot_api/api/history?ori={id_user_origem}&des={id_user_destino}&sta={dt_inicio}&end={dt_fim}&limit={limit}&offset={offset}
>   
    Dados via GET:     
        ori: int - id do remetente
        des: int - id do destinatário
        sta: string - data de inicio 
        end: string - data de fim 
        limit: int
        offset: int

    Dados de retorno: 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": [
                {
                    "origin": int,
                    "destiny": int,
                    "text": string,
                    "type": string,
                    "date": string                   
                }
            ],
            "count": int,
            "next": string,
            "previous": string
    }
}       
> 

<b>Exemplo de implementação no cliente</b>

Criar um aquivo js e importa-lo na home da pagina após o login do usuário.<br>
Confira um exemplo de implementação pasta ./examples
<br><br>

<b>WebSocket</b><br>

SERVER_CHAT_PORT = porta de conexão com o servidor websocket. Essa porta pode ser configurada no arquivo src\config\app.php

Exemplos para troca de mensagens: 
>
    ws://localhost:SERVER_CHAT_PORT/api/attendant
    ws://localhost:SERVER_CHAT_PORT/api/client

    - Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT valido obtido anteriormente ex.: Authorization:  Bearer ...token...
>

Apos a conexão bem sucedida com o servidor de chat, já será possível enviar mensagens ou requisições de informações conforme estrutura dos dados enviados.

<b>Troca de Mensagens</b><br>

Exemplos para troca de mensagens: 
>    
    - ws://localhost:8081/api/attendant
    - ws://localhost:8081/api/client
    
    Dados via POST:     
    {  
        "cmd": string,  //comando
        "driver": string, //web
        "user_uuid": string, //uuid do user de origem
        "user_uuid_dest": string, //uuid do user de destino  
        "text": string, //mensagem   
        "type": string, //text - tipo de mensagem 
        "time": string, //hora    
        "attachment": object|null //null - outros atributos do chatbot
    } 

    Dados de retorno: N/A.    
> 
Os dados de retorno seguem a mesma estrutura de envio caso o outro user esteja offline.

<b>Quantidade Online</b><br>

Exemplos para consulta da quantidade online:   
- ws://localhost:8081/api/attendant 
- ws://localhost:8081/api/client 

>    
    Dados via POST:     
    Dados: {  
        "cmd": "n_on",    
        "qtd": ""    
    }   

    Dados de retorno: {  
        "cmd": "n_on",     
        "qtd": 16   
    }     
> 

>    
    Dados via POST:     
    Dados: {  
        "cmd": "n_on_attendants",    
        "qtd": ""    
    }   

    Dados de retorno: {  
        "cmd": "n_on_attendants",     
        "qtd": 16   
    }     
> 

>    
    Dados via POST:     
    Dados: {  
        "cmd": "n_on_clients",    
        "qtd": ""    
    }   

    Dados de retorno: {  
        "cmd": "n_on_clients",     
        "qtd": 16   
    }     
> 



## Comandos

### Iniciar servidor WebSocket do chat
> php run

### Testes Automatizados - PHPUnit
- Execute o comando para os testes automatizados com PHPUnit
> php vendor/bin/phpunit --testdox --color tests

