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

    /**
     * Consulta e retorna histórico de mensagens em um intervalo de data
     *  [ori]: int - id do remetente |
     *  [des]: int - id do destinatário |
     *  [sta]: string - data de inicio |
     *  [end]: string - data de fim |
     *
     * @param Request $request
     * @param Response $response    
     * @return void
     */
    public function msgHistory(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $params = UtilitiesModel::filterParams($request->getQueryParams());
            $this->msg_model->getHistory($params);

            $result['result'] = $this->msg_model->getResult();
            $result['error'] = $this->msg_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para consultar atendentes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
