<?php

namespace Src\Controllers\Chat\Socket;

use Src\Models\LogModel;
use Src\Models\MsgModel;
use Src\Models\CallModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Ratchet\ConnectionInterface;
use Src\Models\JWTModel;
use Ratchet\MessageComponentInterface;
use Src\Controllers\Chat\Socket\SessionRoomController;



class AppChatController implements MessageComponentInterface
{
    protected $clients;
    private $session_model;
    private $msg_model;
    private $call_model;
    private $log_model;
    private $msg_obj;
    private $attendant_model;
    private $client_model;
    private $jwt;

    /**
     * Set class - informe true para exibir os logs no terminal
     *
     * @param boolean $on_log
     */
    public function __construct(bool $on_log = false)
    {
        $this->log_model = new LogModel($on_log);
        $this->clients = new \SplObjectStorage;
        $this->session_model = new SessionRoomController();
        $this->call_model = new CallModel();
        $this->attendant_model = new AttendantModel();
        $this->client_model = new ClientModel();
        $this->jwt = new JWTModel();
    }

    /**
     * Abrir e armazenar a nova conexão para enviar mensagens mais tarde   
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->log_model->resetLog();
        $this->jwt->checkToken($conn->httpRequest);
        
        if ($this->jwt->getResult()) {
            $rota = strip_tags($conn->httpRequest->getRequestTarget());
            $user_token = $this->jwt->getError()['data'];

            switch ($rota) {

                case '/api/attendant':                 

                    $user = $this->attendant_model->getUserUUID($user_token->uuid);                  
                    if ($user) {
                        $this->newConnection($conn, $user_token->uuid, "attendant", $user_token->name);
                    } else {
                        $conn->close();
                        $this->log_model->setLog("Opss! Usuário invalido.\n");
                    }
                    break;

                case "/api/client":

                    $user = $this->client_model->getUserUUID($user_token->uuid);
                    if ($user) {
                        $this->newConnection($conn, $user_token->uuid, "client", $user_token->name);
                    } else {
                        $conn->close();
                        $this->log_model->setLog("Opss! Usuário invalido.\n");
                    }                   
                    break;

                default:
                    $conn->close();
                    $this->log_model->setLog("Opss! URL invalida.\n" . $rota);
                    break;
            }

            $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");
            $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
            $this->log_model->setLog("Total Atendentes: " . count($this->session_model->getUsersRoom("attendant")) . "\n");
            $this->log_model->setLog("Total Clientes: " . count($this->session_model->getUsersRoom("client")) . "\n");
        } else {
            $conn->send(json_encode(["Result" => false, "Error" => $this->jwt->getError()]));
            $this->log_model->setLog($this->jwt->getError() . "\n");
            $conn->close();
        }

        $this->log_model->printLog();
    }

    /**
     * Ouvir mensagens e redireciona-las
     *
     * @param ConnectionInterface $from
     * @param string $msg
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $this->log_model->resetLog();
        $this->msg_obj = json_decode($msg);

        switch ($this->msg_obj->cmd) {

            case 'request_call':
                //Check session               
                $this->log_model->setLog($this->session_model->checkUserSession($from->resourceId, $this->msg_obj->user_uuid));
                $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
                $this->log_model->setLog("Origem user: " . $this->msg_obj->user_uuid . " | Destino user: " . $this->msg_obj->user_dest_uuid . " \n");
                //Send msg
                $this->searchUserSendMsg($from, "client");
                break;
            case 'msg':
                //Check session               
                $this->log_model->setLog($this->session_model->checkUserSession($from->resourceId, $this->msg_obj->user_uuid));
                $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
                $this->log_model->setLog("Origem user: " . $this->msg_obj->user_uuid . " | Destino user: " . $this->msg_obj->user_dest_uuid . " \n");
                //Send msg
                $this->searchUserSendMsg($from, $this->msg_obj->user_dest_type);
                break;         
            case 'n_on':
                $this->msg_obj->qtd = $this->qtdUsersServer();
                $from->send(json_encode($this->msg_obj));
                $this->log_model->setLog("Total Online: {$this->msg_obj->qtd} \n");
                break;
            case 'n_on_clients':
                $this->msg_obj->qtd = count($this->session_model->getUsersRoom("client"));
                $from->send(json_encode($this->msg_obj));
                $this->log_model->setLog("Total Clientes: {$this->msg_obj->qtd} \n");
                break;
            case 'n_on_attendants':              
                $this->msg_obj->qtd = count($this->session_model->getUsersRoom("attendant"));
                $from->send(json_encode($this->msg_obj));
                $this->log_model->setLog("Total Atendentes: {$this->msg_obj->qtd} \n");
                break;
            default:
                $from->send('{"text":"Comando não reconhecido!"}');
                $this->log_model->setLog("Comando não reconhecido!\n");
                break;
        }

        $this->log_model->printLog();
    }

    /**
     * Fechar conexão e remover user das salas 
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->log_model->resetLog();
        $this->clients->detach($conn);
        $this->session_model->removeUserAllRoom($conn->resourceId);
        $this->log_model->setLog("A conexão {$conn->resourceId} foi desconectada.\n" . "Sessão:\n" . print_r($_SESSION["_sf2_attributes"], true) . "\n");
        $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
        $this->log_model->setLog("Total Atendentes: " . count($this->session_model->getUsersRoom("attendant")) . "\n");
        $this->log_model->setLog("Total Clientes: " . count($this->session_model->getUsersRoom("client")) . "\n");
        $this->log_model->printLog();
    }

    /**
     * Erro inesperado, fechar conexão e remover sessão
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->log_model->setLog("Ocorreu um erro: {$e->getMessage()}\n");
        $this->session_model->removeUserList($conn->resourceId);
        $conn->close();
        $this->log_model->printLog();
    }

    /**
     * Quantidade de usuários online
     *  
     * @return int
     */
    public function qtdUsersServer(): int
    {
        return count($this->clients); //Qtd de usuários online;           
    }

    /**
     * Procurar destinatária na memoria e enviar a mensagem ao mesmo
     *
     * @param ConnectionInterface $from
     * @param string $room
     * @return void
     */
    public function searchUserSendMsg(ConnectionInterface $from, string $room): void
    {       
        $status_msg = $this->saveMsgDB();
        $result = false;
        foreach ($this->clients as $client) { //Liste os users alocados na memória e procure o destinatário

            if ($from !== $client) {   // O remetente não é o destinatário                
               $destiny_id = $this->session_model->getUserId($client->resourceId, $room);              
               
                if ($this->msg_obj->user_dest_uuid === $destiny_id) {  // O destinatária corresponde ao id informado do destinatário

                    $client->send(json_encode($this->msg_obj));  // Envie msg para o destinatário   
                    $result = true;
                    $this->log_model->setLog("Origem resourceId " . $from->resourceId . " | Destino resourceId: " . $client->resourceId  . "\n");
                }               
            }
        }

        //Resposta caso o destinatário esteja offline
        if ($result === false) {
            $this->msg_obj->text = "A mensagem foi enviada mas o usuário está offline.";
            $from->send(json_encode($this->msg_obj));
            $this->log_model->setLog("User offline\n");
        }

        $this->log_model->setLog("Mensagem: " . $this->msg_obj->text . "\n");
        $this->log_model->setLog("Status: " . $status_msg);
    }

    /**
     * Salvar mensagem no banco de dados
     *
     * @return string
     */
    public function saveMsgDB()
    {
        $this->msg_model = new MsgModel();
        $this->msg_model->saveMsg($this->msg_obj->user_uuid, $this->msg_obj->user_dest_uuid, $this->msg_obj->text);
        return $this->msg_model->getError();
    }

    /**
     * Armazene nova conexão para enviar mensagens mais tarde     
     *
     * @param ConnectionInterface $conn
     * @param string $user_uuid
     * @return void
     */
    public function newConnection(ConnectionInterface  $conn, string $user_uuid, string $name_room, string $user_name): void
    {
        $this->log_model->resetLog();
        $this->clients->attach($conn);
        $this->session_model->addUserList($conn->resourceId, $user_uuid);
        $this->session_model->addUserRoom($conn->resourceId, $user_uuid, $name_room);
        $this->log_model->setLog("New Connection ({$conn->resourceId}) | id ({$user_uuid}) | name ({$user_name}).\n");
    }
}
