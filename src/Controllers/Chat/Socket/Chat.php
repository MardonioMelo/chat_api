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

            // Armazene a nova conexão para enviar mensagens mais tarde      
            $this->clients->attach($conn);
            $this->key_session = 'resourceId_' . $conn->resourceId;
            $this->session->set($this->key_session, $user_id);
            $this->setLog("New Connection ({$conn->resourceId}) user_id ({$user_id}).\n");
        }
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
        $this->chat_model = new ChatModel();
        $this->log = "";

        $data_user = $this->session->get($this->key_session);
        $msg_arr = json_decode($msg, true);

        if ($data_user === 0) {
            //Salvar dados na sessão        
            $this->session->remove($this->key_session);
            $this->session->set($this->key_session, $msg_arr['userId']);

            $this->setLog("\nNew user logged in!\n");
        } else {
            $this->setLog("\nLogged in user!\n");
        }

        $this->setLog("Total Online: {$this->qtdUsersOn()} \n");

        $result = false;
        $this->setLog("Origem user: " . $msg_arr['userId'] . " | Destino user: " . $msg_arr['userDestId'] . " \n");

        if ($msg_arr['action'] = "history") {
            //Histórico de mensagens
            $dt_start = '2021-06-15 02:00:00';
            $dt_end = '2021-06-15 03:00:00';
            $this->chat_model->hitoryMsg($msg_arr['userId'], $msg_arr['userDestId'], $dt_start, $dt_end);
            // $history[''] = [];
            // $history['result'] = json_decode($this->chat_model->getResult());
            // $history['error'] = json_decode($this->chat_model->getResult());
        }

        foreach ($this->clients as $client) {

            if ($from !== $client) {
                $destiny_id = (int) $this->session->get('resourceId_' . $client->resourceId) + 0;

                if ((int) $msg_arr['userDestId'] === $destiny_id) {

                    // O remetente não é o destinatário 
                    // O destinatária corresponde ao USER_ID informado em USER_DEST_ID
                    // Envie para o cliente correspondente
                    if ($msg_arr['action'] === "history") {
                        $client->send(json_encode($this->chat_model->getResult()));
                    }
                    $client->send($msg);
                    $result = true;
                    $this->setLog("Origem resourceId " . $from->resourceId . " | Destino resourceId: " . $client->resourceId  . "\n");
                }
            }
        }

        $this->chat_model->saveMsg($msg_arr['userId'], $msg_arr['userDestId'], $msg_arr['text']);

        if ($result === false) {
            $msg_arr['text'] = "A mensagem foi enviada mas o usuário está offline.";
            $from->send(json_encode($msg_arr));
            $this->setLog("User offline\n");
        }

        $this->setLog("Mensagem: " . $msg_arr['text'] . "\n");
        $this->setLog("Status da Mensagem: " . $this->chat_model->getError());
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
}
