<?php

namespace Src\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\ClientModel;
use Src\Models\JWTModel;
use Src\Models\UtilitiesModel;

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
     * @return static
     */
    public function createClient(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $data = (array)$request->getParsedBody();

            $this->client_model->createClient($data);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para cadastrar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Consultar um cadastro 
     *
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return static
     */
    public function readClient(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];

            $this->client_model->readClient($id);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para consultar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Atualizar cadastro completo 
     *
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return static
     */
    public function updateClient(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];
            $data = UtilitiesModel::filterParams(UtilitiesModel::getPUT());

            $this->client_model->updateClient($id, $data);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para atualizar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Deletar um cadastro 
     *
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return static
     */
    public function deleteClient(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];          
            $this->client_model->deleteClient($id);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para deletar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Consultar todos os cadastros
     *
     * @param Request $request
     * @param Response $response   
     * @return static
     */
    public function readAllClient(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {
        
            $params = UtilitiesModel::filterParams($request->getQueryParams());        
            $limit = empty($params['limit'])? 0 : (int) $params['limit'];
            $offset =  empty($params['offset'])? 0 : (int) $params['offset'];
            $uri = $request->getUri()->getPath();

            $this->client_model->readAllClient($limit, $offset, $uri);
            $result['result'] = $this->client_model->getResult();
            $result['error'] = $this->client_model->getError();           
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para consultar clientes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
