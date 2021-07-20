<?php

namespace Src\Controllers\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\AttendantModel;
use Src\Models\JWTModel;
use Src\Models\UtilitiesModel;

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
     * Cadastrar um atendente 
     *
     * @param Request $request
     * @param Response $response
     * @return static
     */
    public function createAttendant(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $data = (array)$request->getParsedBody();

            $this->attendant_model->createAttendant($data);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para cadastrar atendentes!";
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
    public function readAttendant(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];

            $this->attendant_model->readAttendant($id);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para consultar atendentes!";
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
    public function updateAttendant(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];
            $data = UtilitiesModel::filterParams(UtilitiesModel::getPUT());

            $this->attendant_model->updateAttendant($id, $data);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para atualizar atendentes!";
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
    public function deleteAttendant(Request $request, Response $response, array $params)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {

            $id = (int) UtilitiesModel::filterParams($params)['id'];          
            $this->attendant_model->deleteAttendant($id);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para deletar atendentes!";
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
    public function readAllAttendant(Request $request, Response $response)
    {
        $this->jwt->checkToken($request);

        if (!empty($this->jwt->getError()['data']->type) && $this->jwt->getError()['data']->type === "attendant") {
        
            $params = UtilitiesModel::filterParams($request->getQueryParams());        
            $limit = empty($params['limit'])? 0 : (int) $params['limit'];
            $offset =  empty($params['offset'])? 0 : (int) $params['offset'];
            $uri = $request->getUri()->getPath();

            $this->attendant_model->readAllAttendant($limit, $offset, $uri);
            $result['result'] = $this->attendant_model->getResult();
            $result['error'] = $this->attendant_model->getError();           
        } else {
            $result['result'] = false;
            $result['error'] = "Você não tem permissão para consultar atendentes!";
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
