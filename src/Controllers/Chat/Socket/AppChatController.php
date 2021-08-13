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

/**
 * Undocumented class
 */
class AppChatController implements MessageComponentInterface
{
    protected $clients;
    private $session_model;  
    private $call_model;
    private $log_model;
    private $msg_obj;
    private $attendant_model;
    private $client_model;
    private $jwt;

    /**
     * Set class - informe true para exibir os logs no terminal  
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
        $this->importCalls();
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
            $this->jwt->checkToken($from->httpRequest);
            $autor = json_decode($msg, true);
            $autor['user_uuid'] = $this->jwt->getError()['data']->uuid;
            $this->msg_obj = json_decode(json_encode($autor));
            $object = 'cmd_' . $this->msg_obj->cmd;

            if (method_exists($this, $object)) {
                $this->$object($from, $msg);
            }else{
                $from->send($from->send(UtilitiesModel::dataFormatForSend(
                    false,
                    "Comando não reconhecido. Verifique se todos os campos e dados foram informados corretamente!",
                    ["cmd" => "error"]
                )));
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
     * 
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
            $from->send(UtilitiesModel::dataFormatForSend(true, $msg, ["cmd" => 'cmd_n_waiting_line', 'row' => $row]));
        } else {
            $this->sendDataAllUsers("client", "cmd_n_waiting_line", $msg, ['row' => $row]);
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
            $from->send(UtilitiesModel::dataFormatForSend($this->client_model->getResult(), $msg, ["cmd" => "cmd_call_data_clients", "clients" => $data]));
        } else {
            $this->sendDataAllUsers("attendant", 'cmd_call_data_clients', "Atualização da fila de clientes.", ["clients" => $data]);
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

                                $msg_model = (new MsgModel())->saveMsgCall($this->msg_obj->call, $this->msg_obj->text, $uuid, $this->msg_obj->user_uuid);
                                $online = true;
                                $data["type"] = $this->jwt->getError()['data']->type;
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

    /**
     * Enviar noticias para um usuário
     * 
     * @param array $arr_uuids lista de uuid de destino
     * @param string $type
     * @param string $cmd
     * @param string $msg   
     * @param array $data 
     * @return void
     */
    public function sendNoticeUser(array $arr_uuids, string $type, string $cmd, string $msg, array $data): void
    {
        $data['cmd'] = $cmd;

        foreach ($this->clients as $client) {

            if (in_array($this->session_model->getUser($client->resourceId, $type), $arr_uuids)) {
                $client->send(UtilitiesModel::dataFormatForSend(true, $msg, $data));
            }
        }
    }

    /**
     * Importar todas as solicitações de atendimento com status 1 e 2 salvas no db.
     * Este método deve ser executado apenas ao iniciar o servidor de chat
     *
     * @return void
     */
    public function importCalls(): void
    {
        $this->call_model->readAllCallFind("call_status <= :status", "status=2", 1000, 0, "", false);

        if ($this->call_model->getResult()) {
            foreach ($this->call_model->getError()['data'] as $call) {

                $this->session_model->addUserRoomCall($call->data()->call_id, $call->data()->call_client_uuid, "client");
                if (!empty($call->data()->call_attendant_uuid)) {
                    $this->session_model->addUserRoomCall($call->data()->call_id, $call->data()->call_attendant_uuid, "attendant");
                }
            }
        }
    }

    /**
     * Consultar call em aberto de um cliente
     *
     * @return void
     */
    public function checkCallOpenClient(object $from): void
    {
        $this->call_model->readAllCallFind("call_client_uuid = :uuid AND call_status <= :status", "uuid={$this->msg_obj->user_uuid}&status=2", 1, 0, "", true);

        $from->send(UtilitiesModel::dataFormatForSend(
            $this->call_model->getResult(),
            $this->call_model->getError()['msg'],
            ["cmd" => $this->msg_obj->cmd, "data" => ($this->call_model->getResult() ? $this->call_model->getError()['data'] : [])]
        ));
    }

    /**
     * Consultar call em aberto de um atendante
     *
     * @return void
     */
    public function checkCallOpenAttendant(object $from): void
    {
        $this->call_model->readAllCallFind("call_attendant_uuid = :uuid AND call_status <= :status", "uuid={$this->msg_obj->user_uuid}&status=2", 1000, 0, "", true);

        $from->send(UtilitiesModel::dataFormatForSend(
            $this->call_model->getResult(),
            $this->call_model->getError()['msg'],
            ["cmd" => $this->msg_obj->cmd, "data" => ($this->call_model->getResult() ? $this->call_model->getError()['data'] : [])]
        ));
    }


    ##############################################
    #             Métodos de Comando             #
    ##############################################

    /**
     * Número de clientes na fila de espera +1
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_n_waiting_line(object $from, string $msg = ""): void
    {
        $this->nWaitingLine($from);
    }

    /**
     * Dados dos clientes na fila de espera
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_data_clients(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "attendant") {
            $this->customerListData($from);
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Cadastrar nova call pelo cliente
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_create(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "client") {
            $check_call = $this->session_model->existsCallInSession($this->msg_obj->user_uuid);

            if (empty($check_call)) {
                $params = json_decode($msg, true);
                $params['client_uuid'] = $this->msg_obj->user_uuid;
                $this->call_model->callCreate($params, $this->msg_obj->cmd);

                if ($this->call_model->getResult()) {
                    $this->session_model->addUserRoomCall($this->call_model->getError()['data']['call'], $this->msg_obj->user_uuid, "client");
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
                    ["cmd" => $this->msg_obj->cmd, "call" => (int) explode("_", $check_call)[1]]
                ));
            }
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Cancelar call pelo cliente.  
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_cancel(object $from, string $msg = ""): void
    {
        $this->call_model->callCancel(json_decode($msg, true), $this->msg_obj->cmd);

        if ($this->call_model->getResult()) {
            $this->session_model->destroyRoomCall($this->msg_obj->call);
            $this->nWaitingLine();
            $this->customerListData();
        }

        $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

        $from->send(UtilitiesModel::dataFormatForSend(
            $this->call_model->getResult(),
            $this->call_model->getError()['msg'],
            $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
        ));
    }

    /**
     *  Iniciar o atendimento pelo atendente.
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_start(object $from, string $msg = ""): void
    {
        $calls = $this->session_model->getUsersRoom("call");

        if (!empty($calls['call_' . $this->msg_obj->call])) {

            $call_flip = array_flip($calls['call_' . $this->msg_obj->call]);

            if (!in_array("attendant", $calls['call_' . $this->msg_obj->call])) {
                $this->call_model->callStart(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type, $this->jwt->getError()['data']->uuid);

                if ($this->call_model->getResult()) {
                    $this->session_model->addUserRoomCall($this->msg_obj->call, $this->msg_obj->user_uuid, "attendant");
                    $this->customerListData();
                    $this->sendNoticeUser([$call_flip['client']], 'client', $this->msg_obj->cmd, "Chegou sua vez!", ['call' => $this->msg_obj->call]);
                }

                $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

                $data = $this->call_model->getResult() ? $this->call_model->getError()['data'] : [];
                $data['online'] =  $this->session_model->checkOn($call_flip['client']);
                $data['cmd'] = $this->msg_obj->cmd;

                $from->send(UtilitiesModel::dataFormatForSend($this->call_model->getResult(), $this->call_model->getError()['msg'], $data));
            } else {

                if ($call_flip['attendant'] == $this->msg_obj->user_uuid) {
                    $from->send(UtilitiesModel::dataFormatForSend(false, "Você já está nessa call!", ["cmd" => $this->msg_obj->cmd]));
                } else {
                    $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Já existe um atendente nessa call.", ["cmd" => $this->msg_obj->cmd]));
                }
            }
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! A sala da Call não existe ou já foi encerrada.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Enviar mensagem para todos os participantes de uma call.
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_msg(object $from, string $msg = ""): void
    {
        $this->sendMsgCall($from);
    }

    /**
     * Finalizar o atendimento pelo atendente.
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_end(object $from, string $msg = ""): void
    {
        $this->call_model->callEnd(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type);

        if ($this->call_model->getResult()) {
            $this->session_model->destroyRoomCall($this->msg_obj->call);
            $this->nWaitingLine();
            $this->customerListData();
            $this->sendNoticeUser(
                [$this->call_model->getError()['data']['client_uuid']],
                'client',
                $this->msg_obj->cmd,
                "Esperamos ter te ajudado, você poderia avaliar este atendimento?",
                ['call' => $this->msg_obj->call]
            );
        }

        $this->log_model->setLog("Sessão:\n" . print_r($_SESSION['_sf2_attributes'], true) . "\n");

        $from->send(UtilitiesModel::dataFormatForSend(
            $this->call_model->getResult(),
            $this->call_model->getError()['msg'],
            $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
        ));
    }

    /**
     * Avaliação do atendimento pelo cliente.
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_evaluation(object $from, string $msg = ""): void
    {
        $this->call_model->callEvaluation(json_decode($msg, true), $this->msg_obj->cmd, $this->jwt->getError()['data']->type);

        $from->send(UtilitiesModel::dataFormatForSend(
            $this->call_model->getResult(),
            $this->call_model->getError()['msg'],
            $this->call_model->getResult() ? $this->call_model->getError()['data'] : ['cmd' => $this->msg_obj->cmd]
        ));
    }

    /**
     * Checar se existe call aberta de um usuário com status 1 e 2
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_call_check_open(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "client") {
            $this->checkCallOpenClient($from);
        } else {
            $this->checkCallOpenAttendant($from);
        }
    }

    /**
     * Verificar se um usuário especifico está online
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_check_user_on(object $from, string $msg = ""): void
    {
        if (!empty($this->msg_obj->check_on_uuid)) {
            $from->send(UtilitiesModel::dataFormatForSend(
                true,
                "Sucesso!",
                ["cmd" => $this->msg_obj->cmd, 'online' => $this->session_model->checkOn($this->msg_obj->check_on_uuid)]
            ));
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Informe os campos obrigatórios.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Total de usuários online
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_on_n(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "attendant") {
            $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'qtd' => $this->qtdUsersServer()]));
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Total de clientes online
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_clients_on_n(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "attendant") {
            $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'qtd' => count($this->session_model->getUsersRoom("client"))]));
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }

    /**
     * Total de atendentes online
     *
     * @param object $from
     * @param string $msg
     * @return void
     */
    private function cmd_attendants_on_n(object $from, string $msg = ""): void
    {
        if ($this->jwt->getError()['data']->type == "attendant") {
            $from->send(UtilitiesModel::dataFormatForSend(true, "Sucesso!", ["cmd" => $this->msg_obj->cmd, 'qtd' => count($this->session_model->getUsersRoom("attendant"))]));
        } else {
            $from->send(UtilitiesModel::dataFormatForSend(false, "Opss! Você não tem permissão para executar essa ação.", ["cmd" => $this->msg_obj->cmd]));
        }
    }
}
