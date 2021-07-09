<?php

namespace Src\Controllers\Home;

use Src\Models\MsgModel;
use Src\View\PainelChat\PainelChatView;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


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
     * Informe o id do remetente, id do destinatário, data de inicio e fim da troca de mensagens.
     * As datas devem está no formato a americano ex.: 2021-06-16
     *
     * @param Request $request
     * @param Response $response    
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
