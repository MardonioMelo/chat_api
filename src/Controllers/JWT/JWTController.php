<?php

namespace Src\Controllers\JWT;

use Src\Models\JWTModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class para controle de geração e autentificação de token JWT
 */
class JWTController
{
    private $jwt;
    private $attendant_model;
    private $client_model;
    private $result;

    public function __construct()
    {
        $this->jwt = new JWTModel();
        $this->attendant_model = new AttendantModel();
        $this->client_model = new ClientModel();
    }

    /**
     * Gerar o token 
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createToken(Request $request, Response $response)
    {
        $params = (array)$request->getParsedBody();
        $data = $this->filterParams($params);

        if (!empty($data['uuid']) && !empty($data['type']) && $data['public'] === JWT_PUBLIC) {

            if ($data['type'] === "attendat") {
                $this->createTokenAttendant($data['uuid']);
            } else {
                $this->createTokenClient($data['uuid']);                
            }
        } else {
            $this->result = [];
            $this->result['result'] = false;
            $this->result['error'] = "Chave pública inválida!";
        }

        $response->getBody()->write(json_encode($this->result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Limpar parâmetros de tags e espaços
     *
     * @param array $params
     * @return void
     */
    public function filterParams($params = []): array
    {
        return array_filter($params, function ($str) {
            return trim(strip_tags($str));
        });
    }


    private function createTokenAttendant($uuid){

        $user = $this->attendant_model->getUserUUID($uuid);

        if ($user) {
            $this->jwt->createToken([
                "uuid" => $user->attendant_uuid,
                "name" => $user->attendant_name,
                "type" => "attendat"
            ], 43200);

            if ($this->jwt->getResult()) {

                $this->result['result'] = $this->jwt->getResult();
                $this->result['error'] = $this->jwt->getError();
                unset($this->result['error']['data']);
            } else {
                $this->result['result'] = $this->jwt->getResult();
                $this->result['error'] = $this->jwt->getError();
            }
        } else {
            $this->result['result'] = false;
            $this->result['error'] = "O usuário não existe!";
        }
    }


    private function createTokenClient($uuid){

        $user = $this->client_model->getUserUUID($uuid);

        if ($user) {
            $this->jwt->createToken([
                "uuid" => $user->client_uuid,
                "name" => $user->client_name,
                "type" => "attendat"
            ], 43200);

            if ($this->jwt->getResult()) {

                $this->result['result'] = $this->jwt->getResult();
                $this->result['error'] = $this->jwt->getError();
                unset($this->result['error']['data']);
            } else {
                $this->result['result'] = $this->jwt->getResult();
                $this->result['error'] = $this->jwt->getError();
            }
        } else {
            $this->result['result'] = false;
            $this->result['error'] = "O usuário não existe!";
        }
    }
}
