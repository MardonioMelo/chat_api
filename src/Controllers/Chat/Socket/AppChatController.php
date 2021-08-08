<?php

namespace Src\Controllers\Chat\Socket;

use Src\Models\JWTModel;
use Src\Models\LogModel;
use Src\Models\MsgModel;
use Src\Models\CallModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Src\Models\UtilitiesModel;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Src\Controllers\Chat\Socket\CommandController;
use Src\Controllers\Chat\Socket\SessionRoomController;

class AppChatController extends CommandController implements MessageComponentInterface
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
                        $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! Usuário invalido.", ['cmd' => "connection"]));
                        $conn->close();
                    }
                    break;

                case "/api/client":

                    $user = $this->client_model->getUserUUID($user_token->uuid);
                    if ($user) {
                        $this->newConnection($conn, $user_token->uuid, "client", $user_token->name);
                    } else {
                        $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! Usuário invalido.", ['cmd' => "connection"]));
                        $conn->close();
                    }
                    break;

                default:
                    $conn->send(UtilitiesModel::dataFormatForSend(false, "Opss! URL invalida. " . $rota, ['cmd' => "connection"]));
                    $conn->close();
                    break;
            }

            $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");
            $this->log_model->setLog("Total Online: {$this->qtdUsersServer()} \n");
            $this->log_model->setLog("Total Atendentes: " . count($this->session_model->getUsersRoom("attendant")) . "\n");
            $this->log_model->setLog("Total Clientes: " . count($this->session_model->getUsersRoom("client")) . "\n");
        } else {
            $conn->send(UtilitiesModel::dataFormatForSend(false, $this->jwt->getError()["msg"], ['cmd' => "connection", 'data' => $this->jwt->getError()["data"]]));
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
        $this->msg_obj->user_uuid = $this->jwt->getError()['data']->uuid;

        try {
            switch ($this->msg_obj->cmd) {

                case 'msg': // Enviar mensagem para um destinatário

                    $orig_type = $this->jwt->getError()['data']->type;
                    $dest_type = $this->msg_obj->user_dest_type;

                    if ($orig_type == "client" && $dest_type == "client" || $dest_type != "client" && $dest_type != "attendant") {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Não é permitido um cliente enviar mensagem para outro cliente.", ["cmd" => $this->msg_obj->cmd]));
                    } else {
                        $this->searchUserSendMsg($from);
                    }
                    break;

                case 'call_create': // Cadastrar nova call pelo cliente

                    $this->call_model->callCreate(json_decode($msg, true), $this->msg_obj->cmd);

                    if ($this->call_model->getResult()) {
                        $this->session_model->addUserRoomCall($this->call_model->getError()['data']['id'], $this->msg_obj->user_uuid, "client");
                        $this->sendMsgAllUsers("client", 'n_waiting_line', ["n_waiting_line" => count($this->session_model->getUsersRoom("client"))]);
                        $this->sendMsgAllUsers("attendant", 'clients_on_n', ["clients_on_n" => count($this->session_model->getUsersRoom("client"))]);
                        $this->callDataClientsSendAll();
                    }

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'n_waiting_line': // Enviar status da fila de clientes

                    $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'n_waiting_line' => count($this->session_model->getUsersRoom("client"))]));
                    break;

                case 'call_cancel': // Cancelar call pelo cliente.                                  

                    $this->call_model->callCancel(json_decode($msg, true), $this->msg_obj->cmd);

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'call_start': // Iniciar o atendimento pelo atendente.

                    $this->call_model->callStart(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type, $this->jwt->getError()['data']->uuid);

                    if ($this->call_model->getResult()) {
                        $this->session_model->addUserRoomCall($this->msg_obj->call, $this->msg_obj->user_uuid, "attendant");
                    }

                    $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'call_evaluation': // Avaliação do atendimento pelo cliente.

                    $this->call_model->callEvaluation(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type);

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'call_end': // Finalizar o atendimento pelo atendente.

                    $this->call_model->callEnd(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type);

                    if ($this->call_model->getResult()) {
                        $this->session_model->removeUserRoomCall($this->msg_obj->call);
                    }

                    $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'call_data_clients': // Dados dos clientes na espera do atendimento

                    $this->callDataClientsFrom($from);

                    break;

                case 'n_on': // Total de usuários online

                    if ($this->jwt->getError()['data']->type == "attendant") {
                        $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'n_on' => $this->qtdUsersServer()]));
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'clients_on_n': // Total de clientes online

                    if ($this->jwt->getError()['data']->type == "attendant") {
                        $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'clients_on_n' => count($this->session_model->getUsersRoom("client"))]));
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'attendants_on_n': // Total de atendentes online

                    if ($this->jwt->getError()['data']->type == "attendant") {
                        $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'attendants_on_n' => count($this->session_model->getUsersRoom("attendant"))]));
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                default: //erro
                    $from->send($from->send(UtilitiesModel::dataFormatForSend(
                        false,
                        "Comando não reconhecido. Verifique se todos os campos e dados foram informados corretamente!",
                        ["cmd" => "error"]
                    )));
                    break;
            }
        } catch (\Throwable $e) {
            $this->log_model->setLog($e->getMessage() . "\n");
            $from->send(UtilitiesModel::dataFormatForSend(
                false,
                "Ops! Algo de inesperado aconteceu, verifique se os dados enviados estão corretos e tente novamente mais tarde.",
                ["cmd" => "error"]
            ));
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
            $client->send(UtilitiesModel::dataFormatForSend(false, "A mensagem foi enviada, mas o usuário está offline!", ["cmd" => $this->msg_obj->cmd]));
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

    /**
     * Enviar mensagem para todos os usuários de um tipo
     * @param string $type
     * @param string $cmd
     * @param array $msg    
     * @return void
     */
    public function sendMsgAllUsers(string $type, string $cmd, array $data): void
    {
        $data['cmd'] = $cmd;

        foreach ($this->clients as $client) {
            if ($this->session_model->getUser($client->resourceId, $type) == true) {
                $client->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", $data));
            }
        }
    }

    /**
     * Enviar dados dos clientes em espera par um atendente
     *
     * @param  $from
     * @return void
     */
    public function callDataClientsFrom($from)
    {
        if ($this->jwt->getError()['data']->type == "attendant") {
            $calls = $this->session_model->getUsersRoom("call");
            $uuids = "";

            if (!empty($calls)) {
                foreach ($calls as $value) {
                    $flip = array_flip($value);

                    if ($uuids == "") {
                        $uuids .= $flip['client'];
                    } else {
                        $uuids .= "," . $flip['client'];
                    }
                }
            }

            $uri = strip_tags($from->httpRequest->getRequestTarget());
            $this->client_model->readAllClientFind("client_uuid IN(:uuids)", "uuids=$uuids", 1000, 0, $uri);
            $data = $this->client_model->getResult() ? $this->client_model->getError()['data'] : [];

            $from->send(UtilitiesModel::dataFormatForSend(
                $this->client_model->getResult(),
                $this->client_model->getError()['msg'],
                [
                    "cmd" => $this->msg_obj->cmd,
                    'call_data_clients' => $data
                ]
            ));
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Enviar dados dos clientes em espera para todos os atendentes
     *
     * @return void
     */
    public function callDataClientsSendAll(): void
    {
        $calls = $this->session_model->getUsersRoom("call");
        $uuids = "";

        if (!empty($calls)) {
            foreach ($calls as $value) {
                $flip = array_flip($value);

                if ($uuids == "") {
                    $uuids .= $flip['client'];
                } else {
                    $uuids .= "," . $flip['client'];
                }
            }
        }

        $this->client_model->readAllClientFind("client_uuid IN(:uuids)", "uuids=$uuids", 1000, 0);
        $data = $this->client_model->getResult() ? $this->client_model->getError()['data'] : [];
        $this->sendMsgAllUsers("attendant", 'clients_on_data', $data);
    }
}
