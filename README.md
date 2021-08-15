# API para Chat de Atendimento
API para chat de suporte e atendimento, ainda em desenvolvimento.

## Front-end
Este projeto não inclui front-end. Uma versão do front-end está sendo desenvolvida em Vue3 em outro repositório. Assim, você fica livre para desenvolver seu próprio front-end em qualquer tecnologia.

## Entidades
No momento, essa API tem duas entidades sendo uma o Atendente e a outra o Cliente. A identificação de ambos dentro da API será através do UUID gerado automaticamente no momento do cadastro do user.

## Instalação
Após clonar este repositório, siga as instruções abaixo.
### Instar dependências
> 
    composer install
>
### Configurações
Renomeie o arquivo src/config/exemples.app.php para "app.php" e defina as configurações a seguir.

Defina o HOST da HOME
>
    define("HOME", "http://localhost"); 
>

Crie um banco de dados com nome "db_chat" ou de sua escolha. Importe para o db os scripts de tabelas em sql que estão na pasta src/db.

Informe os dados para conexão com banco de dados.
>
    define("DATA_LAYER_CONFIG", [
        "driver" => "mysql",
        "host" => "localhost",
        "port" => "3306",
        "dbname" => "db_chat",
        "username" => "root",
        "passwd" => "",
        "options" => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
    ]);
>

Defina a porta do servidor WS do chat
>
    define("SERVER_CHAT_PORT", "81");   
>

Gere a chave pública e privada com o comando
>
    php newkey
>

Informe a chave publica e privada respectivamente.
>  
    define("JWT_PUBLIC", "ffc6wwq2eb25f5asasf11a7f1b7546cb3ca"); // Chave publica
    define("JWT_SECRET", "ffc68a2eb25f5a1f11a7f1b7546cb3ca"); // Chave privada 
>
### Iniciar servidor WebSocket
Recomenda-se rodar essa API em duas portas ou dois HOST diferentes se preferir. Sendo um para o servidor HTTP e outra para servidor WS (WebSocket). 

Se o projeto for instalado em http://localhost você deve definir uma porta para o servidor WS diferente de 80 e 443 ou que esteja sendo utilizada, essa porta pode ser 81 ou 8080 por exemplo. A porta 81 por exemplo, deve ser definida no SERVER_CHAT_PORT no arquivo src/config/app.php.

Iniciar servidor WS
>
    php run
>
# Rotas do Servidor HTTP

Para gerar o token de autorização, o usuário deverá previamente ser cadastrado no db do chat e a aplicação front-end deverá acessar primeiramente a rota de geração de token e informar os dados obrigatórios. Apos a obtenção do token, a aplicação front-end deverá informa-lo no cabeçalho para acesso as demais rotas da API.

<b>Gerar Token</b>

Exemplo de envio:   
- POST: localhost/chatbot_api/api/token
>   
    Content-Type: multipart/form-data
    Request:     
        uuid: string              
        type: string    
        public: string
        name: string 
        avatar: string
        lastname: string

    Response: 
    Type: application/json
    {
        "result": bool,
        "error": {
            "header": string,
            "token": string,
            "msg": string
        }
    } 
> 

O name | lastname | avatar: são opcionais porque só serão utilizados quando a API tiver que cadastrar um usuário que não existe no db e que informou um CPF válido no campo uuid, nesse caso o name e lastname passam a ser obrigatórios. 

O cadastro automático do usuário na solicitação do token se aplica apenas para clientes que informarem um CPF valido. Por segurança, não é permitido cadastrar atendentes dessa forma.

<b>Cadastrar Atendente</b>

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
    Request:   
        cpf: string    
        name: string  
        lastname: string     
        avatar: string - opcional          

    Response: 
    Type: application/json
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

<b>Consultar um Cadastro</b>

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

<b>Consultar Todos Cadastros</b>

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

<b>Atualizar Atendente</b>

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

<b>Deletar Atendente</b>

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

Cadastrar atendentes e clientes pelo terminal.
>
    php user
>

<b>CRUD de Clientes</b>

O cadastro, consulta, atualização e delete de clientes segue o mesmo fluxo e método do cadastro de atendentes apenas substituindo na rota o  <i>"attendant"</i> por <i>"client"</i>.

# Rotas do Servidor WebSocket
A conexão é aberta assim que a url é acessada. O cabeçalho da requisição de conexão deverá ter o token de autorização valido ou a conexão será fechada. 

<b>Rotas do servidor WebSocket:</b>

>
    ws://localhost:SERVER_CHAT_PORT/api/attendant //rota do atendente
    Ex.: localhost:81/api/attendan

    ws://localhost:SERVER_CHAT_PORT/api/client    //rota do cliente   
    Ex.: localhost:81/api/client

    SERVER_CHAT_PORT = porta de conexão com o servidor websocket. Essa porta pode ser configurada no arquivo src\config\app.php

    - Header: Deve ser informado no cabeçalho da requisição no campo Authorization um token JWT valido obtido anteriormente ex.: Authorization:  Bearer ...token...

    No cabeçalho também deve está definido o tipo dos dados enviado.
    Type: application/json   

    Response em caso de erro
    Type: application/json  
    {
        "result": false,
        "error": {
            "msg": string,
            "data": {
                "cmd": "connection"
            }
        }
    }
>

Apos a conexão bem sucedida com o servidor de chat, já será possível enviar e receber informações conforme estrutura dos dados e cmd informado.

Obs.: Caso o processo do servidor de chat caia, as calls - atendimentos em aberto com status 1 e 2 serão reestabelecidos ao reiniciar o servidor.

<b> Quantidade de clientes na fila de espera</b>

>
    Request:
    Type: application/json
    {  
        "cmd": "cmd_n_waiting_line"  //comando       
    }   

    Response:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_n_waiting_line",
                "row": int
            }
        }
    }
>

<b>Dados dos clientes na fila de espera</b>
<br>Os clientes não tem permissão para este comando.

>
    Request:
    Type: application/json
    {  
        "cmd": "cmd_call_data_clients"  //comando         
    }   

    Response para todos os atendentes:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_data_clients",
                "clients": {  //dados dos clientes em espera
                    "call_00": { //00 - corresponde ao id da call.
                        "user": { //dados do cliente
                            "id": int, //id do cliente
                            "cpf": int,
                            "uuid": string,
                            "name": string,
                            "lastname": string,
                            "avatar": string,
                            "updated_at": string,
                            "created_at": string,
                            "url": string
                        },
                        "call": { //dados da call
                            "call_id": int,
                            "call_client_uuid": string,
                            "call_attendant_uuid": "string,
                            "call_objective": string,
                            "call_status": int,
                            "call_start": string,
                            "call_end": string,
                            "call_evaluation": int,
                            "call_update": string
                        }                        
                    } ...
                }                
            }
        }
    }
>

<b>Criar Call</b>
<br>Os atendentes não tem permissão para este comando.

> 
    Request:
    Type: application/json
    {  
        "cmd": "cmd_call_create", //comando    
        "objective": string //assunto  
    }

    Response:
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_create",
                "call": int|string //id da call         
            }
        }
    }

    Response para todos os clientes:
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "row": int, //quantidade de clientes na fila + 1
                "cmd": "cmd_n_waiting_line"
            }
        }
    }
    
    Response para todos os atendentes:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_data_clients",
                "clients": {  //dados dos clientes em espera
                    "call_00": { //00 - corresponde ao id da call.
                        "user": { //dados do cliente
                            "id": int, //id do cliente
                            "cpf": int,
                            "uuid": string,
                            "name": string,
                            "lastname": string,
                            "avatar": string,
                            "updated_at": string,
                            "created_at": string,
                            "url": string
                        },
                        "call": { //dados da call
                            "call_id": int,
                            "call_client_uuid": string,
                            "call_attendant_uuid": "string,
                            "call_objective": string,
                            "call_status": int,
                            "call_start": string,
                            "call_end": string,
                            "call_evaluation": int,
                            "call_update": string
                        }                        
                    }...
                }                
            }
        }
    }
> 

<b>Cancelar Call</b><br>

> 
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_call_cancel", //comando    
        "call": int //id da call 
    }  

    Response: 
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "call": int, //id da call
                "cmd": "cmd_call_cancel"
            }
        }
    }

    Response para todos os clientes:
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "row": int,
                "cmd": "cmd_n_waiting_line"
            }
        }
    }

    Response para todos os atendentes:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_data_clients",
                "clients": {  //dados dos clientes em espera
                    "call_00": { //00 - corresponde ao id da call.
                        "user": { //dados do cliente
                            "id": int, //id do cliente
                            "cpf": int,
                            "uuid": string,
                            "name": string,
                            "lastname": string,
                            "avatar": string,
                            "updated_at": string,
                            "created_at": string,
                            "url": string
                        },
                        "call": { //dados da call
                            "call_id": int,
                            "call_client_uuid": string,
                            "call_attendant_uuid": "string,
                            "call_objective": string,
                            "call_status": int,
                            "call_start": string,
                            "call_end": string,
                            "call_evaluation": int,
                            "call_update": string
                        }                        
                    }...
                }                
            }
        }
    }
> 

<b>Iniciar Call</b>
<br>Os clientes não tem permissão para este comando.
<br>No momento, não é permitido entrar mais de um atendente por sala de call. 
<br>Caso, um atendente inicie a call, o mesmo não poderá sair até finalizar o atendimento. 

> 
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_call_start", //comando        
        "call": int //id da call  
    }   
    
    Response:
    Type: application/json  
    {
        "result": true,
        "error": {
            "msg": string,
            "data": {
                "call": int, //id da call
                "cmd": "cmd_call_start",
                "client_uuid": string, //uuid do cliente
            }
        }
    }

    Response para o cliente em questão
    Type: application/json
    {
        "result": true,
        "error": {
            "msg": string,
            "data": {
                "call": int,
                "cmd": "cmd_call_start"
            }
        }
    }    
    
    Response para todos os atendentes:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_data_clients",
                "clients": {  //dados dos clientes em espera
                    "call_00": { //00 - corresponde ao id da call.
                        "user": { //dados do cliente
                            "id": int, //id do cliente
                            "cpf": int,
                            "uuid": string,
                            "name": string,
                            "lastname": string,
                            "avatar": string,
                            "updated_at": string,
                            "created_at": string,
                            "url": string
                        },
                        "call": { //dados da call
                            "call_id": int,
                            "call_client_uuid": string,
                            "call_attendant_uuid": "string,
                            "call_objective": string,
                            "call_status": int,
                            "call_start": string,
                            "call_end": string,
                            "call_evaluation": int,
                            "call_update": string
                        }                        
                    }...
                }                
            }
        }
    }
>

<b>Troca de Mensagens</b>
<br>As mensagens são enviadas para os outros usuários que estão na mesma sala da call.

>      
    Request:
    Type: application/json  
    {  
        "cmd": "cmd_call_msg",  //comando          
        "call": int,  //id da call
        "text": string, //mensagem        
    }  

    Response: caso o destinatário esteja offline
    Type: application/json  
    {
        "result": false
        "error": {
            "msg": "call_msg",
            "data": {
                "cmd": "cmd_call_msg"
            }
        }
    }

    Response enviados ao destinatário:
    Type: application/json  
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_msg",
                "text": string,
                "call": int,
                "type": string //attendant ou client
            }
        }
    }
         
>

<b>Finalizar Call</b>
<br>Os clientes não tem permissão para este comando.
<br>Outro atendente que não esteja na call, também pode finalizar.

> 
    Request
    Type: application/json     
    {  
        "cmd": "cmd_call_end", //comando         
        "call": int //id da call  
    }   

    Response: 
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "call": int, //id da call
                "cmd": "cmd_call_end",
                "client_uuid": string //uuid do cliente
            }
        }
    }

    Response enviado ao cliente atendido:
    Type: application/json  
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "call": int,
                "cmd": "cmd_call_end"
            }
        }
    } 

    Response para todos os clientes:
    Type: application/json 
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "row": int, //quantidade de clientes na fila + 1
                "cmd": "cmd_n_waiting_line"
            }
        }
    }
    
    Response para todos os atendentes:
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_data_clients",
                "clients": {  //dados dos clientes em espera
                    "call_00": { //00 - corresponde ao id da call.
                        "user": { //dados do cliente
                            "id": int, //id do cliente
                            "cpf": int,
                            "uuid": string,
                            "name": string,
                            "lastname": string,
                            "avatar": string,
                            "updated_at": string,
                            "created_at": string,
                            "url": string
                        },
                        "call": { //dados da call
                            "call_id": int,
                            "call_client_uuid": string,
                            "call_attendant_uuid": "string,
                            "call_objective": string,
                            "call_status": int,
                            "call_start": string,
                            "call_end": string,
                            "call_evaluation": int,
                            "call_update": string
                        }                        
                    }...
                }                
            }
        }
    }
>

<b>Avaliação da Call</b><br>

> 
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_call_evaluation", //comando         
        "call": int, //id da call  
        "evaluation": int //nota
    }   

    Response: 
    Type: application/json   
    {
        "result": true,
        "error": {
            "msg": string,
            "data": {
                "call": int, //id da call
                "cmd": "cmd_call_evaluation"               
            }
        }
    }
>

<b>Consultar mensagens de uma call</b>

>     
    Request:
    Type: application/json  
    {  
        "cmd": "cmd_call_history", //comando
        "call": int, //id da call
        "limit": int, //limite por pagina
        "offset": int //offset da consulta
    }   

    Response:
    Type: application/json   
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "chat": [
                    {
                        "id": int,
                        "call": int,
                        "origin": string, //uuid do user de origem
                        "destiny": string, //uuid do user de destino
                        "text": string, //mensagem
                        "type": string, //text
                        "date": string //data e hora
                        "url": string //url do registro
                    }
                ],
                "count": 18,
                "next": string|null, //url da próxima pagina
                "previous": string|null, //url da pagina anterior
                "cmd": "cmd_call_history"
            }
        }
    }
> 

<b>Verificar se existem atendimentos em aberto para um usuário</b>
<br>Se for um cliente, o resultado caso tenha call aberta, será sempre de uma call.
<br>Se for um atendente, o resultado poderá ser mais de uma call.

> 
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_call_check_open" //comando            
    }   

    Response: 
    Type: application/json
    {
    "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_call_check_open", //comando
                "data": [ //dados da call
                    {
                        "call": int,
                        "client_uuid": string,
                        "attendant_uuid": "string,
                        "objective": string,
                        "status": int,
                        "start": string,
                        "end": string,
                        "evaluation": int,
                        "update": string
                    }   
                    ...  
                ]
            }
        }
    }
>

<b>Verificar se um usuário está online</b><br>

> 
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_check_user_on", //comando  
        "check_on_uuid": string, //uuid do usuário a ser verificado       
    }   

    Response: 
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": string,
            "data": {
                "cmd": "cmd_check_user_on",
                "online": bool //true = online, false = offline
            }
        }
    }

>

<b>Quantidade de usuários online (atendentes + clientes)</b>
<br>Os clientes não tem permissão para este comando.

>    
    Request:
    Type: application/json     
    {  
        "cmd": "cmd_on_n" //comando       
    }   

    Response: 
    Type: application/json
    {
        "result": bool,
        "error": {
            "msg": "Sucesso!",
            "data": {
                "cmd": "cmd_on_n",
                "qtd": int
            }
        }
    }
> 

<b>Quantidade de clientes online</b>
<br>Os clientes não tem permissão para este comando.

>     
    Request:
    Type: application/json  
    {  
        "cmd": "cmd_clients_on_n", //comando
    }   

    Response:
    Type: application/json   
    {
        "result": bool,
        "error": {
            "msg": "Sucesso!",
            "data": {
                "cmd": "cmd_clients_on_n",
                "qtd": int
            }
        }
    }
> 

<b>Quantidade de atendentes online</b>
<br>Os clientes não tem permissão para este comando.

>     
    Request:
    Type: application/json  
    {  
        "cmd": "cmd_attendants_on_n", //comando
    }   

    Response:
    Type: application/json   
    {
        "result": bool,
        "error": {
            "msg": "Sucesso!",
            "data": {
                "cmd": "cmd_attendants_on_n",
                "qtd": int
            }
        }
    }
> 

# Testes Automatizados - PHPUnit
- Execute o comando para os testes automatizados com PHPUnit
- Os testes automatizados ainda não foram implementados
> 
    php vendor/bin/phpunit --testdox --color tests
>

