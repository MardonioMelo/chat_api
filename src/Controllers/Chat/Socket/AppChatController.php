<?php

namespace Src\Controllers\Chat\Socket;

use Src\Models\JWTModel;
use Src\Models\LogModel;
use Src\Models\CallModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Src\Models\UtilitiesModel;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Src\Controllers\Chat\Socket\CommandController;
use Src\Controllers\Chat\Socket\SessionRoomController;

/**
 * Undocumented class
 */
class AppChatController extends CommandController implements MessageComponentInterface
{
    protected $clients;
    protected $session_model;
    protected $call_model;
    protected $log_model;
    protected $msg_obj;
    protected $attendant_model;
    protected $client_model;
    protected $jwt;
    protected $text_error;

    /**
     * Set class - informe true para exibir os logs no terminal  
     */
    public function __construct()
    {
        $this->log_model = new LogModel();
        $this->clients = new \SplObjectStorage;
        $this->session_model = new SessionRoomController();
        $this->call_model = new CallModel();
        $this->attendant_model = new AttendantModel();
        $this->client_model = new ClientModel();
        $this->jwt = new JWTModel();
        $this->importCalls();
        $this->text_error = "Algo de inesperado aconteceu, verifique se os dados enviados estão corretos e tente novamente!";
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

        try {
            $this->jwt->checkTokenMethodGET($conn);

            if ($this->jwt->getResult()) {
                if (!$this->session_model->checkOn($this->jwt->getError()['data']->uuid)) {
                    $this->cmd_connection($conn);
                    $this->statusServidor();
                    $this->log_model->printLog();
                } else {
                    $conn->send(UtilitiesModel::dataFormatForSend(false, "Você já conectou por outra máquina, desconecte-se e tente novamente!", ['cmd' => "cmd_connection", 'data' => $this->jwt->getError()["data"]]));
                    $conn->close();
                }
            } else {
                $conn->send(UtilitiesModel::dataFormatForSend(false, $this->jwt->getError()["msg"], ['cmd' => "cmd_connection"]));
                $conn->close();
            }
        } catch (\Throwable $e) {

            $this->log_model->setLog($e->getMessage() . "\n");
            $conn->send(UtilitiesModel::dataFormatForSend(
                false,
                $this->text_error,
                ["cmd" => "cmd_connection"]
            ));
            $conn->close();
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
        $this->log_model->resetLog();

        try {
            $this->jwt->checkToken($from->httpRequest);
            $autor = json_decode($msg, true);
            $autor['user_uuid'] = $this->jwt->getError()['data']->uuid;
            $this->msg_obj = (object) $autor;
            $object = $this->msg_obj->cmd;

            if (method_exists($this, $object)) {
                $this->$object($from, $msg);
            } else {
                $from->send(UtilitiesModel::dataFormatForSend(
                    false,
                    "Comando não reconhecido. Verifique se todos os campos e dados foram informados corretamente!",
                    ["cmd" => "error"]
                ));
            }
        } catch (\Throwable $e) {
            $this->log_model->setLog("Ocorreu um Error \n");
            $this->log_model->setLog("Arquivo: {$e->getFile()} \n");
            $this->log_model->setLog("Linha: {$e->getLine()} \n");
            $this->log_model->setLog("Mensagem: {$e->getMessage()} \n");
            $from->send(UtilitiesModel::dataFormatForSend(
                false,
                $this->text_error,
                ["cmd" => "error"]
            ));
        }

        $this->statusServidor();
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
        $this->statusServidor();
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
        $this->session_model->removeUserList($conn->resourceId);
        $this->log_model->setLog("\nOcorreu um erro: {$e->getMessage()}\n");
        $conn->close();
    }
}
