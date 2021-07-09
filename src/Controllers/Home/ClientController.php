<?php

namespace Src\Controllers\Home;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\ClientModel;
use Src\Models\JWTModel;

/**
 * Classe controller dos clientes
 */
class ClientController
{

    private $client_model;
    private $jwt;

    public function __construct()
    {
        $this->client_model = new ClientModel();
        $this->jwt = new JWTModel();
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
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendat") {

            $this->client_model->saveAttendant($params);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para cadastrar atendentes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
