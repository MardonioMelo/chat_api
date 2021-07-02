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
     * Check token
     * A aplicação front-end deverá criar um token previamente
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createToken(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if ($this->jwt->getResult()) {

            $user_token = $this->jwt->getError()['data'];
            $params = (array)$request->getParsedBody();
            $data = [
                "uuid" => strip_tags((string)$params['uuid']),
                "name" => strip_tags((string)$params['name']),
                "type" => strip_tags((string)$params['type'])
            ];

            if ($user_token->uuid === $data['uuid'] && $user_token->name === $data['name'] && $user_token->name === $data['type']) {

            }else{

            }

            $this->jwt->createTokenWebSocket($data, 43200);
        } else {
            $result = [];
            $result['result'] = $this->jwt->getResult();
            $result['error'] = $this->jwt->getError();
        }       

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
