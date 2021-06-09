<?php

namespace Src\Controllers\Chat\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Chat implements MessageComponentInterface
{
    protected $clients;
    private $session;
    private $key_session;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->session = new Session();
        $this->session->start();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Armazene a nova conexão para enviar mensagens mais tarde      
        $this->clients->attach($conn);
        $this->key_session = 'resourceId_' . $conn->resourceId;
        $this->session->set($this->key_session, 0);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // $numRecv = count($this->clients) - 1;
        // echo "Connection $from->resourceId sending message '$msg' to $numRecv other connection" . ($numRecv == 1 ? '' : 's');

        $data_user = $this->session->get($this->key_session);
        $msg_arr = json_decode($msg);

        if ($data_user === 0) {

            //Salvar dados na sessão              
            $this->session->set($this->key_session, $msg_arr->userId);

            echo "\n User novo";
        } else {
            echo "\n User já existe!";
        }

        $result = false;
        foreach ($this->clients as $client) {

            if ($from !== $client) {

                $destinations = $this->session->get('resourceId_' . $client->resourceId);

                var_dump($destinations);

                if (!empty($destinations)) {
                    if ($msg_arr->userDestId === $destinations['userId']) {

                        // O remetente não é o destinatário 
                        // O destinatária corresponde ao USER_ID informado
                        // Envie para o cliente correspondente
                        $client->send($msg);
                        $result = true;
                    }
                }
            }
        }

        if ($result === false) {
            $msg_arr->text = "A mensagem foi enviada mas o usuário está offline.";
            $from->send(json_encode($msg_arr));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // A conexão foi encerrada, remova-a, pois não podemos mais enviar mensagens para ela
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
