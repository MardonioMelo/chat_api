<?php

namespace Src\Controllers\Chat\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Src\Models\ChatModel;


class Chat implements MessageComponentInterface
{
    protected $clients;
    private $session;
    private $key_session;
    private $chat_model;
    private $log;
    private $on_log;
    private $msg_obj;

    /**
     * Construct - informe true na declaração para imprimir os logs no terminal do servidor websocket.
     *
     * @param boolean $on_log
     */
    public function __construct(bool $on_log = false)
    {
        $this->clients = new \SplObjectStorage;
        $this->session = new Session();
        $this->session->start();
        $this->on_log = (bool) $on_log;
    }

    /**
     * Abrir conexão
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->log = "";
        $user_id = (int) str_replace("/", "", $conn->httpRequest->getRequestTarget());

        if (empty($user_id) || $user_id === 0) {
            $this->setLog("Opss! O ID do user não foi informado ou ID inválido.\n");
            $conn->close();
        } else {
            //Armazene a nova conexão para enviar mensagens mais tarde      
            $this->newConnection($conn, $user_id);
        }
        //Log
        $this->setLog("Total Online: {$this->qtdUsersOn()} \n");
        $this->printLog();
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
        $this->log = "";
        $this->msg_obj = json_decode($msg);

        switch ($this->msg_obj->cmd) {
            case 'msg':
                //Check session
                $this->checkUserSession();
                //Log
                $this->setLog("Total Online: {$this->qtdUsersOn()} \n");
                $this->setLog("Origem user: " . $this->msg_obj->userId . " | Destino user: " . $this->msg_obj->userDestId . " \n");
                //Send msg
                $this->searchUserSendMsg($from);
                break;
            case 'n_on':
                $this->msg_obj->qtd = $this->qtdUsersOn();
                $from->send(json_encode($this->msg_obj));
                $this->setLog("Total Online: {$this->qtdUsersOn()} \n");
                break;
            case 'n_':

                break;
            default:
                $from->send("{Comando não reconhecido!}");
                break;
        }

        //Log      
        $this->printLog();
    }

    /**
     * Fechar conexão
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->log = "";
        // A conexão foi encerrada, remova-a, pois não podemos mais enviar mensagens para ela
        $this->clients->detach($conn);
        $this->session->remove('resourceId_' . $conn->resourceId);
        $this->setLog("A conexão {$conn->resourceId} foi desconectada.\n" . "Sessão:\n" . print_r($_SESSION, true) . "\n");
        $this->printLog();
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
        $this->log = "";
        $this->setLog("Ocorreu um erro: {$e->getMessage()}\n");
        $this->session->remove('resourceId_' . $conn->resourceId);
        $conn->close();
        $this->printLog();
    }

    /**
     * Inclui os dados na memória para serem exibidos ou salvos em db 
     *
     * @param string $log
     * @return void
     */
    public function setLog(string $log): void
    {
        $this->log .= $log;
    }

    /**
     * Imprimi os logs na tela
     *
     * @return void
     */
    public function printLog(): void
    {
        $in = "\n---------" . date("d/m/Y h:i:s") . "------------\n";
        $out = "\n----------------------------------------\n";

        if ($this->on_log) {
            echo $in . $this->log . "\nMemory: " . memory_get_usage() . " bytes" . $out;
        }
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
                $destiny_id = (int) $this->session->get('resourceId_' . $client->resourceId) + 0;

                // O destinatária corresponde ao id informado do destinatário
                if ((int) $this->msg_obj->userDestId === $destiny_id) {

                    // Envie msg para o destinatário   
                    $client->send(json_encode($this->msg_obj));
                    $result = true;
                    $this->setLog("Origem resourceId " . $from->resourceId . " | Destino resourceId: " . $client->resourceId  . "\n");
                }
            }
        }

        //Resposta caso o destinatário esteja offline
        if ($result === false) {
            $this->msg_obj->text = "A mensagem foi enviada mas o usuário está offline.";
            $from->send(json_encode($this->msg_obj));
            $this->setLog("User offline\n");
        }

        //Log
        $this->setLog("Mensagem: " . $this->msg_obj->text . "\n");
        $this->setLog("Status: " . $status_save);
    }

    /**
     * Salvar mensagem no banco de dados
     *
     * @return string
     */
    public function saveMsgDB(): string
    {
        //Salvar msg no banco de dados   
        $this->chat_model = new ChatModel();
        $this->chat_model->saveMsg($this->msg_obj->userId, $this->msg_obj->userDestId, $this->msg_obj->text);
        return $this->chat_model->getError();
    }

    /**
     * Verificar se o user está na sessão, se não estiver, será adicionado.
     *
     * @return void
     */
    public function checkUserSession(): void
    {
        if ($this->session->get($this->key_session) === 0) {
            //Salvar dados na sessão        
            $this->session->remove($this->key_session);
            $this->session->set($this->key_session, $this->msg_obj->userId);
            //Log
            $this->setLog("\nNew user logged in!\n");
        } else {
            //Log
            $this->setLog("\nLogged in user!\n");
        }
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
        $this->key_session = 'resourceId_' . $conn->resourceId;
        $this->session->set($this->key_session, $user_id);
        $this->setLog("New Connection ({$conn->resourceId}) user_id ({$user_id}).\n");
    }
}
