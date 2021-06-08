<?php

namespace Src\Controllers\Chat\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Chat implements MessageComponentInterface
{
    protected $clients;
    private $session;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;

        $this->session = new Session();
        $this->session->start();

        // set and get session attributes
        //$this->session->set('name', 'Drak');
        //$this->session->get('name');        
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Armazene a nova conexão para enviar mensagens mais tarde      
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
        // $conn->send('Hello ' . $conn->Session->get('name'));
        $this->session->set('resourceId_' . $conn->resourceId, []);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo "Connection $from->resourceId sending message '$msg' to $numRecv other connection" . ($numRecv == 1 ? '' : 's');
        $this->session->get('resourceId_' . $from->resourceId)['test'] = 'Jesus';
        var_dump($this->session->get('resourceId_' . $from->resourceId));


        foreach ($this->clients as $client) {

            if ($from !== $client) {

                //var_dump($this->clients[$client]);
                // O remetente não é o destinatário, envie para cada cliente conectado
                $client->send($msg);
            }
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
