<?php

namespace Src\Controllers\User;

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
     * Cadastrar cliente
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createClient(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendet") {

            $this->client_model->saveClient($params);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para cadastrar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
