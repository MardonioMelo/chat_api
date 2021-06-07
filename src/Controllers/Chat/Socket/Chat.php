<?php

namespace Src\Controllers\Chat\Socket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Armazene a nova conexão para enviar mensagens mais tarde
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";       
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // $numRecv = count($this->clients) - 1;
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // O remetente não é o destinatário, envie para cada cliente conectado
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // A conexão foi encerrada, remova-a, pois não podemos mais enviar mensagens para ela
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
