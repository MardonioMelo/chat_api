<?php

namespace Src\Controllers\JWT;

use Slim\Psr7\Response;
use Src\Models\JWTModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class middleware invokable para verificar autentificação JWT
 */
class JWTMiddleware
{
    /**
     * Middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $existingContent = (string) $response->getBody();

        $response = new Response();
        $jwt = new JWTModel();
        $jwt->checkToken($request);

        if ($jwt->getResult()) {
            $response->getBody()->write($existingContent);
        } else {
            $result = array(
                "result" => false,
                "error" => array("msg" => "Acesso Negado!")
            );

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        };

        return $response;
    }
}
