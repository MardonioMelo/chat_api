<?php

namespace Src\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\AttendantModel;
use Src\Models\JWTModel;

/**
 * Classe controller dos atendentes
 */
class AttendantController
{

    private $attendant_model;
    private $jwt;

    public function __construct()
    {
        $this->attendant_model = new AttendantModel();
        $this->jwt = new JWTModel();
    }

    /**
     * Cadastrar atendente 
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createAttendant(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendat") {

            $this->attendant_model->saveAttendant($params);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para cadastrar atendentes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
