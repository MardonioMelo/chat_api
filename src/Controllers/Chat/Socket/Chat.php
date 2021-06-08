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

               
        if($data_user === 0){
            echo "\n User novo";

            $data_json = json_decode($msg);
            $this->session->set($this->key_session,  $data_json);   

        }else{
            echo "\n User já existe!";            
        } 

        echo "\n"; 
        $data_user = $this->session->get($this->key_session); 
        var_dump($data_user);       
        echo "\n";           


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
