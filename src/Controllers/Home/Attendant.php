<?php

namespace Src\Controllers\Home;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\AttendantModel;

/**
 * Classe controller dos atendentes
 */
class Attendant
{

    private $bot_model;

    public function __construct()
    {        
        $this->bot_model = new AttendantModel();
    }

    /**
     * Cadastrar usuário
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createAttendant(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $this->bot_model->saveAttendant(
            (string)$params['attendant_name'],
            (string)$params['attendant_lastname'],
            (string)$params['attendant_avatar']           
        );

        $response->getBody()->write($this->bot_model->getResult());
        return $response;
    }



    
}
