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
        $this->chat_model = new ChatModel();
        $this->session = new Session();
        $this->session->start();
        $this->log = "";
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
        $user_id = (int) str_replace("/", "", $conn->httpRequest->getRequestTarget());

        if (empty($user_id) || $user_id === 0) {

            $this->setLog("Opss! O ID do user não foi informado ou ID inválido.\n");
            $conn->close();
        } else {

            // Armazene a nova conexão para enviar mensagens mais tarde      
            $this->clients->attach($conn);
            $this->key_session = 'resourceId_' . $conn->resourceId;
            $this->session->set($this->key_session, $user_id);
            $this->setLog("Conexão ({$conn->resourceId}) user_id ({$user_id}).\n");
        }

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
        // $numRecv = count($this->clients) - 1 //Qtd de usuários online;        

        $data_user = $this->session->get($this->key_session);
        $msg_arr = json_decode($msg, true);

        if ($data_user === 0) {
            //Salvar dados na sessão        
            $this->session->remove($this->key_session);
            $this->session->set($this->key_session, $msg_arr['userId']);

            $this->setLog("\nUser novo\n");
        } else {
            $this->setLog("\nUser existe!\n");
        }

        $result = false;
        $this->setLog("Origem user: " . $msg_arr['userId'] . " | Destino user: " . $msg_arr['userDestId'] . " \n");
        $this->setLog("User Online: \n");

        foreach ($this->clients as $client) {

            if ($from !== $client) {
                $destiny_id = (int) $this->session->get('resourceId_' . $client->resourceId) + 0;

                if ((int) $msg_arr['userDestId'] === $destiny_id) {

                    // O remetente não é o destinatário 
                    // O destinatária corresponde ao USER_ID informado em USER_DEST_ID
                    // Envie para o cliente correspondente
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
       
        $this->setLog($this->chat_model->getError());
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
            echo $in . $this->log . $out;
        }
    }
}
