<?php

namespace Src\Controllers\Chat\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Src\Models\MsgModel;
use Src\Models\CallModel;
use Src\Models\LogModel;
use Src\Models\SessionModel;

class Chat implements MessageComponentInterface
{
    protected $clients;
    private $session_model;
    private $msg_model;
    private $call_model;
    private $log_model;
    private $msg_obj;
   
    /**
     * Construct - informe true na declaração para imprimir os logs no terminal do servidor websocket.
     *
     * @param boolean $on_log
     */
    public function __construct(bool $on_log = false)
    {
        $this->log_model = new LogModel($on_log);
        $this->clients = new \SplObjectStorage;
        $this->session_model = new SessionModel();
        $this->session_model->setRoom();
    }

    /**
     * Abrir conexão
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {      
        $this->log_model->resetLog();
        $params = explode("/", $conn->httpRequest->getRequestTarget());       

        if (empty($params[2]) || (int) $params[2] === 0 || $params[0] !== "api") {
            $conn->close();
            $this->log_model->setLog("Opss! URI invalida.\n");           
        } else {

            if ($params[2] === "attendant") {
            
            } elseif($params[2] === "client") {
    
            }

            //Armazene a nova conexão para enviar mensagens mais tarde      
            $this->newConnection($conn, $params[3]);
            $this->session_model->addUserRoom($conn->resourceId, $params[3]);
        }
        //Log
        $this->log_model->setLog("Total Online: {$this->qtdUsersOn()} \n");
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
        $this->msg_obj = json_decode($msg);
        $this->log_model->resetLog();

        switch ($this->msg_obj->cmd) {
            case 'msg':
                //Check session               
                $this->log_model->setLog($this->session_model->checkUserSession($from->resourceId, $this->msg_obj->userId));
                $this->log_model->setLog("Total Online: {$this->qtdUsersOn()} \n");
                $this->log_model->setLog("Origem user: " . $this->msg_obj->userId . " | Destino user: " . $this->msg_obj->userDestId . " \n");
                //Send msg
                $this->searchUserSendMsg($from);
                break;
            case 'n_on':
                $this->msg_obj->qtd = $this->qtdUsersOn();
                $from->send(json_encode($this->msg_obj));
                $this->log_model->setLog("Total Online: {$this->qtdUsersOn()} \n");
                break;
            default:
                $from->send('{"text":"Comando não reconhecido!"}');
                $this->log_model->setLog("Comando não reconhecido!\n");
                break;
        }

        //Log      
        $this->log_model->printLog();
    }

    /**
     * Fechar conexão
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->log_model->resetLog();
        // A conexão foi encerrada, remova-a, pois não podemos mais enviar mensagens para ela
        $this->clients->detach($conn);
        $this->session_model->removeUserSession($conn->resourceId);
        $this->log_model->setLog("A conexão {$conn->resourceId} foi desconectada.\n" . "Sessão:\n" . print_r($_SESSION, true) . "\n");
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
        $this->log_model->resetLog();
        $this->log_model->setLog("Ocorreu um erro: {$e->getMessage()}\n");
        $this->session_model->removeUserSession($conn->resourceId);
        $conn->close();
        $this->log_model->printLog();
    }

    /**
     * Quantidade de usuários online
     *
     * @param int $sub informe uma quantidade para subtrair do total
     * @return int
     */
    public function qtdUsersOn(int $sub = 1): int
    {
        return count($this->clients) - $sub; //Qtd de usuários online;           
    }

    /**
     * Procurar destinatária na memoria e enviar a mensagem ao mesmo
     *
     * @param ConnectionInterface $from
     * @return void
     */
    public function searchUserSendMsg(ConnectionInterface $from): void
    {
        $status_save = $this->saveMsgDB();

        //Liste os users alocados na memória e procure o destinatário
        $result = false;
        foreach ($this->clients as $client) {

            // O remetente não é o destinatário 
            if ($from !== $client) {
                $destiny_id = $this->session_model->getUserId($client->resourceId) + 0;

                // O destinatária corresponde ao id informado do destinatário
                if ((int) $this->msg_obj->userDestId === $destiny_id) {

                    // Envie msg para o destinatário   
                    $client->send(json_encode($this->msg_obj));
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
        $this->log_model->setLog("Status: " . $status_save);
    }

    /**
     * Salvar mensagem no banco de dados
     *
     * @return string
     */
    public function saveMsgDB(): string
    {
        $this->msg_model = new MsgModel();
        $this->msg_model->saveMsg($this->msg_obj->userId, $this->msg_obj->userDestId, $this->msg_obj->text);
        return $this->msg_model->getError();
    }

    /**
     * Armazene nova conexão para enviar mensagens mais tarde 
     *
     * @param ConnectionInterface $conn
     * @param int $user_id
     * @return void
     */
    public function newConnection(ConnectionInterface  $conn, int $user_id): void
    {
        $this->clients->attach($conn);
        $this->session_model->addUserSession($conn->resourceId, $user_id);
        $this->log_model->setLog("New Connection ({$conn->resourceId}) user_id ({$user_id}).\n");
    }
}
