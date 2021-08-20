<?php

namespace Src\Controllers\JWT;

use Src\Models\JWTModel;
use Src\Models\ClientModel;
use Src\Models\AttendantModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
    private $user;
    private $data;

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
        $this->data =  UtilitiesModel::filterParams($params);
        $this->result = [];
        $this->result['result'] = false;

        if (!empty($this->data['public']) && $this->data['public'] === JWT_PUBLIC) {

            if (!empty($this->data['uuid']) && !empty($this->data['type'])) {

                if ($this->data['type'] === "attendant") {
                    $this->checkAttendant();
                } else {
                    $this->checkClient();
                }
            } else {
                $this->result['error']['msg'] = "Informe todos os campos obrigatórios!";
            }
        } else {
            $this->result['error']['msg'] = "Chave pública inválida!";
        }

        $response->getBody()->write(json_encode($this->result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Verificar e Gerar token JWT para um atendente
     *   
     * @return void
     */
    private function checkAttendant(): void
    {
        $this->user = $this->attendant_model->getUserUUID($this->data['uuid']);

        if ($this->user) {
            $this->createTokenAttendant();
        } else {
            $this->user = $this->attendant_model->getUserCPF($this->data['uuid']);

            if ($this->user) {
                $this->createTokenAttendant();
            } else {
                $this->result['result'] = false;
                $this->result['error']['msg'] = "O usuário não existe!";
            }
        }
    }

    /**
     * Criar token para um atendente
     *
     * @return void
     */
    private function createTokenAttendant():void
    {
        $this->jwt->createToken([
            "uuid" => $this->user->attendant_uuid,
            "name" => $this->user->attendant_name,
            "type" => "attendant"
        ], 43200); //12hs

        if ($this->jwt->getResult()) {

            $this->result['result'] = $this->jwt->getResult();
            $this->result['error'] = $this->jwt->getError();           
        } else {
            $this->result['result'] = $this->jwt->getResult();
            $this->result['error'] = $this->jwt->getError();
        }
    }

    /**
     * Verificar se o cliente existe para cadastra-lo e obter o uuid do mesmo
     *   
     * @return void
     */
    private function checkClient(): void
    {
        $this->user = $this->client_model->getUserUUID($this->data['uuid']);

        if ($this->user) {
            $this->createTokenClient();
        } else {
            $this->result['result'] = false;

            if (UtilitiesModel::validateCPF($this->data['uuid'])) {
                $this->user = $this->client_model->getUserCPF($this->data['uuid']);

                if ($this->user) {
                    $this->createTokenClient();
                } else {
                    $this->data['cpf'] = $this->data['uuid'];
                    $this->client_model->createClient($this->data);

                    if ($this->client_model->getResult()) {
                        $this->user = $this->client_model->getUserUUID($this->client_model->getError()['data']['uuid']);
                        $this->createTokenClient();
                    } else {
                        $this->result['error']['msg'] = $this->client_model->getError()['msg'];
                    }
                }
            } else {
                $this->result['error']['msg'] = "O usuário não existe!";
            }
        }
    }

    /**
     * Verificar e Gerar token JWT para um cliente
     *  
     * @return void
     */
    private function createTokenClient(): void
    {
        $this->jwt->createToken([
            "uuid" => $this->user->client_uuid,
            "name" => $this->user->client_name,
            "type" => "client"
        ], 43200); //12hs

        if ($this->jwt->getResult()) {
            $this->result['result'] = $this->jwt->getResult();
            $this->result['error'] = $this->jwt->getError();           
        } else {
            $this->result['result'] = $this->jwt->getResult();
            $this->result['error'] = $this->jwt->getError();
        }
    }
}
