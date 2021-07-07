<?php

namespace Src\Controllers\Home;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\BotModel;
use Src\View\PainelChat\PainelChatView;
use Src\Models\MsgModel;

/**
 * Classe controller principal da API
 */
class Dashboard
{

    private $bot_model;
    private $msg_model;

    public function __construct()
    {
        $this->bot_model = new BotModel();
        $this->PainelChatView = new PainelChatView();
        $this->msg_model = new MsgModel();
    }

    /**
     * Executa pagina index
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function home(Request $request, Response $response, array $args)
    {
        $payload = $this->PainelChatView->tplPainelView();
        $response->getBody()->write($payload);
        return $response;
    }

    /**
     * Consulta e retorna histórico de mensagens em um intervalo de data
     * Informe o id do remetente, id do destinatário, data de inicio e fim da troca de mensagens.
     * As datas devem está no formato a americano ex.: 2021-06-16
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function msgHistory(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $payload = $this->msg_model->readHistory(
            (int)$params['user_id'],
            (int)$params['user_dest_id'],
            (string)$params['dt_start'],
            (string)$params['dt_end']
        );

        $response->getBody()->write(json_encode($this->msg_model->passeAllDataArrayHistory($payload)));
        return $response->withHeader('Content-Type', 'application/json');    
    }
    
}