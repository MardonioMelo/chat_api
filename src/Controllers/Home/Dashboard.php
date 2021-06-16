<?php

namespace Src\Controllers\Home;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\BotModel;
use Src\View\PainelChat\PainelChatView;
use Src\Models\ChatModel;

/**
 * Classe controller principal da API
 */
class Dashboard
{

    private $bot_model;
    private $chat_model;

    public function __construct()
    {
        $this->BotModel = new BotModel();
        $this->PainelChatView = new PainelChatView();
        $this->chat_model = new ChatModel();
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
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function msgHistory(Request $request, Response $response, array $args)
    {       
        $payload = $this->bot_model->readHistory($user_id, $user_dest_id, $dt_start, $dt_end);
        $response->getBody()->write($payload);
        return $response;
    }
}
