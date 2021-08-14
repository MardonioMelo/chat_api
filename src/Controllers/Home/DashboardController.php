<?php

namespace Src\Controllers\Home;

use Src\Models\MsgModel;
use Src\Models\UtilitiesModel;
use Src\View\PainelChat\PainelChatView;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\JWTModel;

/**
 * Classe controller principal da API
 */
class DashboardController
{

    private $msg_model;
    private $painel_view;

    public function __construct()
    {
        $this->painel_view = new PainelChatView();
        $this->msg_model = new MsgModel();
        $this->jwt = new JWTModel();
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
        $id =  trim(strip_tags($args['id']));
        $payload = $this->painel_view->tplPainelView(["Painel de Chat", $id, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG]);
        $response->getBody()->write($payload);
        return $response;
    }   
}
