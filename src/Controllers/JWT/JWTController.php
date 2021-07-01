<?php

namespace Src\Controllers\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\JWTModel;

/**
 * Class para controle de geração e autentificação de token JWT
 */
class JWTController
{
    private $jwt;

    public function __construct()
    {
        $this->jwt = new JWTModel();
    }

    /**
     * Cadastrar usuário
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createToken(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $data = [
            "uuid" => strip_tags((string)$params['uuid']),
            "name" => strip_tags((string)$params['name']),
            "type" => strip_tags((string)$params['type'])
        ];

        $this->jwt->createTokenUser($data, 43200);

        $result = [];
        $result['result'] = $this->jwt->getResult();
        $result['error'] = $this->jwt->getError();

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}
