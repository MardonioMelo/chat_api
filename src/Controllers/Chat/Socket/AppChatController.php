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
use Src\Models\UtilitiesModel;

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
    public function __construct()
    {
        $this->log_model = new LogModel(true, false);
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
                        $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! Usuário invalido.")); 
                        $conn->close();
                    }
                    break;

                case "/api/client":

                    $user = $this->client_model->getUserUUID($user_token->uuid);
                    if ($user) {
                        $this->newConnection($conn, $user_token->uuid, "client", $user_token->name);
                    } else {
                        $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! Usuário invalido."));                       
                        $conn->close();
                    }
                    break;

                default:           
                    $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! URL invalida. " . $rota));    
                    $conn->close();
                    break;
            }

            $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");
            $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
            $this->log_model->setLog("Total Atendentes: " . count($this->session_model->getUsersRoom("attendant")) . "\n");
            $this->log_model->setLog("Total Clientes: " . count($this->session_model->getUsersRoom("client")) . "\n");
        } else {       
            $conn->send(UtilitiesModel::dataFormatForSend(false, $this->jwt->getError()["msg"], $this->jwt->getError()["data"]));           
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
        $this->jwt->checkToken($from->httpRequest);
        $msg_error = "Comando não reconhecido. Verifique se todos os campos e dados foram informados corretamente!";

        try {
            switch ($this->msg_obj->cmd) {

                case 'msg':
                    $orig_type = $this->jwt->getError()['data']->type;
                    $dest_type = $this->msg_obj->user_dest_type;

                    if ($orig_type == "client" && $dest_type == "client" || $dest_type != "client" && $dest_type != "attendant") {
                        $msg = "Não é permitido um cliente enviar mensagem para outro cliente.";
                        $this->log_model->setLog($msg . "\n");
                        $from->send(UtilitiesModel::dataFormatForSend(false, $msg));
                    } else {
                        $this->searchUserSendMsg($from);
                    }
                    break;

                case 'request_call':
                    // Cadastrar solicitação de abertura de atendimento.
                    break;

                case 'n_on':
                    $this->log_model->setLog("Comando n_on - sucesso! \n");
                    $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ['n_on' => $this->qtdUsersServer()]));
                    break;

                case 'n_on_clients':
                    $this->log_model->setLog("Comando n_on_clients - sucesso! \n");
                    $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ['n_on_clients' => count($this->session_model->getUsersRoom("client"))]));
                    break;

                case 'n_on_attendants':
                    $this->log_model->setLog("Comando n_on_attendants - sucesso! \n");
                    $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ['n_on_attendants' => count($this->session_model->getUsersRoom("attendant"))]));
                    break;

                default:
                    $this->log_model->setLog($msg_error . "\n");
                    $from->send($from->send(UtilitiesModel::dataFormatForSend(false, $msg_error)));
                    break;
            }
        } catch (\Throwable $e) {
            $from->send(UtilitiesModel::dataFormatForSend(false, $msg_error));
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
     * @return void
     */
    public function searchUserSendMsg(ConnectionInterface $from): void
    {
        $status_msg = $this->saveMsgDB();      

        $online = false;
        foreach ($this->clients as $client) { //Liste os users alocados na memória e procure o destinatário

            if ($from !== $client) {   // O remetente não é o destinatário                
                $destiny_uuid = $this->session_model->getUser($client->resourceId, $this->msg_obj->user_dest_type);

                if ($this->msg_obj->user_dest_uuid == $destiny_uuid) {  // O destinatária corresponde ao uuid informado do destinatário   
                    $this->log_model->setLog(
                        "Total Online: {$this->qtdUsersServer()} \n"
                            . "Origem user: " . $this->msg_obj->user_uuid . "\nDestino user: " . $this->msg_obj->user_dest_uuid . " \n"
                            . "Origem resourceId " . $from->resourceId . "\nDestino resourceId: " . $client->resourceId  . "\n"
                            . "Mensagem: " . $this->msg_obj->text . "\n"
                            . "Status mensagem: " . $status_msg . "\n"
                    );
                    $client->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", [
                        "cmd" => $this->msg_obj->cmd,
                        "driver" => $this->msg_obj->driver,
                        "user_uuid" => $this->msg_obj->user_uuid,
                        "user_type" => $this->jwt->getError()['data']->type,
                        "text" => $this->msg_obj->text,
                        "type" => $this->msg_obj->type,
                        "time" => $this->msg_obj->time,
                        "attachment" => $this->msg_obj->attachment,
                    ]));
                    $online = true;
                }
            }
        }

        //Resposta caso o destinatário esteja offline       
        if ($online === false) {
            $this->log_model->setLog("Status do user: Offline\n");
            $client->send(UtilitiesModel::dataFormatForSend(false, "A mensagem foi enviada, mas o usuário está offline!"));
        }
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
