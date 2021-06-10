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
            
            echo "Opss! O ID do user não foi informado ou ID inválido.\n";           
            $conn->close();
        } else {

            // Armazene a nova conexão para enviar mensagens mais tarde      
            $this->clients->attach($conn);
            $this->key_session = 'resourceId_' . $conn->resourceId;
            $this->session->set($this->key_session, $user_id);
            echo "New connection! ({$conn->resourceId}) user_id ({$user_id})\n";
        }
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
        $log = "";

        if ($data_user === 0) {
            //Salvar dados na sessão        
            $this->session->remove($this->key_session);
            $this->session->set($this->key_session, $msg_arr['userId']);

            $log .= "\nUser novo\n";
        } else {
            $log .= "\nUser existe!\n";
        }

        $result = false;
        $log .= "Origem user: " . $msg_arr['userId'] . " | Destino user: " . $msg_arr['userDestId'] . " \n";
        $log .= "User Online: \n";
        foreach ($this->clients as $client) {

            if ($from !== $client) {
                $destiny_id = (int) $this->session->get('resourceId_' . $client->resourceId) + 0;

                if ((int) $msg_arr['userDestId'] === $destiny_id) {

                    // O remetente não é o destinatário 
                    // O destinatária corresponde ao USER_ID informado em USER_DEST_ID
                    // Envie para o cliente correspondente
                    $client->send($msg);
                    $result = true;
                    $log .= "Origem resourceId " . $from->resourceId . " | Destino resourceId: " . $client->resourceId  . "\n";
                }
            }
        }

        if ($result === false) {
            $msg_arr['text'] = "A mensagem foi enviada mas o usuário está offline.";
            $from->send(json_encode($msg_arr));
            $log .= "User offline\n";
        }

        echo $log;
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

        print_r($_SESSION);
        echo "Connection {$conn->resourceId} has disconnected\n";
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
        echo "An error has occurred: {$e->getMessage()}\n";
        $this->session->remove('resourceId_' . $conn->resourceId);
        $conn->close();
    }
}
