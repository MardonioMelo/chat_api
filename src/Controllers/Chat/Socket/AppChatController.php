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
        try {

            $this->log_model->resetLog();
            $this->msg_obj = json_decode($msg);
            $this->jwt->checkToken($from->httpRequest);
            $this->msg_obj->user_uuid = $this->jwt->getError()['data']->uuid;

            switch ($this->msg_obj->cmd) {                

                case 'n_waiting_line': // Número de clientes na fila de espera

                    $this->nWaitingLine($from);
                    break;

                case 'customer_list_data': // Dados dos clientes na fila de espera

                    if ($this->jwt->getError()['data']->type == "attendant") {
                        $this->customerListData($from);
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'call_create': // Cadastrar nova call pelo cliente

                    if ($this->jwt->getError()['data']->type == "client") {
                        $check_call = $this->session_model->existsCallInSession($this->msg_obj->client_uuid);

                        if (empty($check_call)) {
                            $this->call_model->callCreate(json_decode($msg, true), $this->msg_obj->cmd);

                            if ($this->call_model->getResult()) {
                                $this->session_model->addUserRoomCall($this->call_model->getError()['data']['id'], $this->msg_obj->client_uuid, "client");
                                $this->nWaitingLine();
                                $this->customerListData();
                            }

                            $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                            $from->send(UtilitiesModel::dataFormatForSend(
                                $this->call_model->getResult(),
                                $this->call_model->getError()['msg'],
                                $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                            ));
                        } else {
                            $this->nWaitingLine();
                            $this->customerListData();

                            $from->send(UtilitiesModel::dataFormatForSend(
                                true,
                                "Já existe uma sala de espera criada para você, aguarde o atendimento!",
                                ["cmd" => $this->msg_obj->cmd, "id" => (int) explode("_", $check_call)[1]]
                            ));
                        }
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'call_cancel': // Cancelar call pelo cliente.                                  

                    $this->call_model->callCancel(json_decode($msg, true), $this->msg_obj->cmd);

                    if ($this->call_model->getResult()) {
                        $this->session_model->removeUserRoomCall($this->msg_obj->call);
                        $this->nWaitingLine();
                        $this->customerListData();
                    }

                    $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'call_start': // Iniciar o atendimento pelo atendente.

                    $calls = $this->session_model->getUsersRoom("call");                  

                    if (!in_array("attendant", $calls['call_' . $this->msg_obj->user_uuid])) {
                        $this->call_model->callStart(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type, $this->jwt->getError()['data']->uuid);

                        if ($this->call_model->getResult()) {
                            $this->session_model->addUserRoomCall($this->msg_obj->call, $this->msg_obj->user_uuid, "attendant");
                            $this->nWaitingLine();
                            $this->customerListData();
                        }

                        $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                        $from->send(UtilitiesModel::dataFormatForSend(
                            $this->call_model->getResult(),
                            $this->call_model->getError()['msg'],
                            $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                        ));
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Já existe um atendente nessa call.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'call_msg': // Enviar mensagem para todos os participantes de uma call               

                    $this->sendMsgCall($from);
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

                case 'call_evaluation': // Avaliação do atendimento pelo cliente.

                    $this->call_model->callEvaluation(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type);

                    $from->send(UtilitiesModel::dataFormatForSend(
                        $this->call_model->getResult(),
                        $this->call_model->getError()['msg'],
                        $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
                    ));
                    break;

                case 'check_user_on': // Verificar se um usuário especifico está online

                    if (!empty($this->msg_obj->check_on_uuid)) {
                        $from->send(UtilitiesModel::dataFormatForSend(
                            true,
                            "Sucesso!",
                            ["cmd" => $this->msg_obj->cmd, 'check_user_on' => $this->session_model->checkOn($this->msg_obj->check_on_uuid)]
                        ));
                    } else {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Informe os campos obrigatórios.", ["cmd" => $this->msg_obj->cmd]));
                    }
                    break;

                case 'on_n': // Total de usuários online

                    if ($this->jwt->getError()['data']->type == "attendant") {
                        $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'on_n' => $this->qtdUsersServer()]));
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

        $this->msg_model = new MsgModel();
        $this->msg_model->saveMsgCall(0, $this->msg_obj->user_uuid, $this->msg_obj->user_dest_uuid, $this->msg_obj->text);

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
                            . "Status mensagem: " . $this->msg_model->getError() . "\n"
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
     * Enviar dados para todos os usuários de um tipo
     * @param string $type
     * @param string $cmd
     * @param string $msg   
     * @param array $data 
     * @return void
     */
    public function sendDataAllUsers(string $type, string $cmd, string $msg, array $data): void
    {
        $data['cmd'] = $cmd;

        foreach ($this->clients as $client) {
            if ($this->session_model->getUser($client->resourceId, $type) == true) {
                $client->send(UtilitiesModel::dataFormatForSend(true, $msg, $data));
            }
        }
    }

    /**
     * Enviar número da fila de espera para um ou todos os clientes
     *
     * @param object $from
     * @return void
     */
    public function nWaitingLine(object $from = null): void
    {
        $calls = $this->session_model->getUsersRoom("call");
        $row = count($calls) + 1;
        $msg = "Atualização da fila de espera.";

        if ($from) {
            $from->send(UtilitiesModel::dataFormatForSend(true, $msg, ["cmd" => 'n_waiting_line', 'row' => $row]));
        } else {
            $this->sendDataAllUsers("client", "n_waiting_line", $msg, ['row' => $row]);
        }
    }

    /**
     * Enviar dados dos clientes em espera para um ou todos os atendentes
     *
     * @param object $from
     * @return void
     */
    public function customerListData(object $from = null): void
    {
        $calls = $this->session_model->getUsersRoom("call");
        $find_name = "";
        $find_value = "";
        $data = [];

        if (!empty($calls)) {
            foreach ($calls as $key => $value) {
                $flip = array_flip($value);

                if ($find_value == "") {
                    $find_value .= http_build_query([$key => $flip['client']]);
                    $find_name .= "client_uuid = :" . $key;
                } else {
                    $find_value .= "&" . http_build_query([$key => $flip['client']]);
                    $find_name .= " OR client_uuid = :" . $key;
                }
            }
        }

        $this->client_model->readAllClientFind($find_name, $find_value, 1000, 0);

        if ($this->client_model->getResult()) {
            $users = $this->client_model->getError()['data'];
            $msg = $this->client_model->getError()['msg'];

            foreach ($calls as $key => $value) {
                $flip = array_flip($value);

                foreach ($users as $user) {
                    if ($user['uuid'] == $flip['client']) {
                        $data[$key]['user'] = $user;
                        $data[$key]['call'] = $this->call_model->getCall((int) explode("_", $key)[1]);
                    }
                }
            }
        } else {
            $msg = "Não existem clientes na fila!";
        }

        if ($from) {
            $from->send(UtilitiesModel::dataFormatForSend($this->client_model->getResult(), $msg, ["cmd" => "call_data_clients", "clients" => $data]));
        } else {
            $this->sendDataAllUsers("attendant", 'call_data_clients', "Atualização da fila de clientes.", ["clients" => $data]);
        }
    }

    /**
     * Enviar mensagem para todos os usuários de uma call
     * 
     * @param object $from
     * @return void
     */
    public function sendMsgCall(object $from = null): void
    {
        if (!empty($this->msg_obj->call) && !empty($this->msg_obj->text)) {

            $list_uuid = $this->session_model->getUsersRoom("list");
            $calls = $this->session_model->getUsersRoom("call");

            if (!empty($calls['call_' . $this->msg_obj->call])) {

                $data['cmd'] = $this->msg_obj->cmd;
                $data['text'] = $this->msg_obj->text;
                $data['call'] = $this->msg_obj->call;
                $call = $calls['call_' . $this->msg_obj->call];
                $list_flip = array_flip($list_uuid);
                $call_flip = array_flip($call);
                $online = false;

                if (in_array($this->msg_obj->user_uuid, $call_flip)) {
                    foreach ($call as $uuid => $value) {

                        foreach ($this->clients as $client) {
                            if ($list_flip[$uuid] == "resourceId_$client->resourceId" && $this->msg_obj->user_uuid != $uuid) {

                                $this->msg_model = (new MsgModel())->saveMsgCall(
                                    $this->msg_obj->call,
                                    $this->msg_obj->user_uuid,
                                    $uuid,
                                    $this->msg_obj->text
                                );
                                $online = true;
                                $data["type"] = $value;
                                $client->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", $data));
                            }
                        }
                    }
                    //Resposta caso o destinatário esteja offline       
                    if ($online === false) {
                        $from->send(UtilitiesModel::dataFormatForSend(false, "A mensagem foi enviada, mas o usuário está offline!", ["cmd" => $this->msg_obj->cmd]));
                    }
                } else {
                    $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não está na sala da Call informada.", ["cmd" => $this->msg_obj->cmd]));
                }
            } else {
                $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! A sala da Call não existe ou já foi encerrada.", ["cmd" => $this->msg_obj->cmd]));
            }
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Informe os dados obrigatórios para enviar mensagem.", ["cmd" => $this->msg_obj->cmd]));
        }
    }
}
