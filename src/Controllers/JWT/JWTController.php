<?php

namespace Src\Controllers\JWT;

use Src\Models\JWTModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Controllers\Bot\UtilitiesController;
use Src\Models\UtilitiesModel;

/**
 * Class controle para geração de token JWT
 */
class JWTController
{
    private $jwt;
    private $attendant_model;
    private $client_model;
    private $result;

    /**
     * Set class  jwt, attendant e client
     */
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
        $data =  UtilitiesModel::filterParams($params);

        if (!empty($data['uuid']) && !empty($data['type']) &&  !empty($data['public']) && $data['public'] === JWT_PUBLIC) {

            if ($data['type'] === "attendant") {
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
     * Cadastro de atendentes
     *
     * @param string $uuid
     * @return void
     */
    private function createTokenAttendant(string $uuid): void
    {
        $user = $this->attendant_model->getUserUUID($uuid);

        if ($user) {
            $this->jwt->createToken([
                "uuid" => $user->attendant_uuid,
                "name" => $user->attendant_name,
                "type" => "attendant"
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

    /**
     * Cadastro de clientes
     *
     * @param string $uuid
     * @return void
     */
    private function createTokenClient(string $uuid): void
    {
        $user = $this->client_model->getUserUUID($uuid);

        if ($user) {
            $this->jwt->createToken([
                "uuid" => $user->client_uuid,
                "name" => $user->client_name,
                "type" => "client"
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
