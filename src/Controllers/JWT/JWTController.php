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

        if ($data['public'] === JWT_PUBLIC) {                 

           // if ($user_token->uuid === $data['uuid'] && $user_token->type === $data['type']) {
                $this->jwt->createTokenWebSocket($data, 43200);  

                if ($this->jwt->getResult()) {       
                                          
                    $result['result'] = $this->jwt->getResult();
                    $result['error'] = $this->jwt->getError();
                    unset($result['error']['data']);                    
                } else {
                    $result['result'] = $this->jwt->getResult();
                    $result['error'] = $this->jwt->getError();
                }
         //   } else {
          //      $result['result'] = false;
         //       $result['error'] = "O usuário não existe!";
          //  }
        } else {
            $result = [];
            $result['result'] = false;
            $result['error'] = "Chave pública inválida!";
        }

        $response->getBody()->write(json_encode($result));
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
}
