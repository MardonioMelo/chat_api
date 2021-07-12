<?php

use Src\Controllers\Bot\BotController;
use Src\Controllers\JWT\JWTController;
use Src\Controllers\JWT\JWTMiddleware;
use Slim\Exception\HttpNotFoundException;
use Src\Controllers\User\ClientController;
use Src\Controllers\Home\DashboardController;
use Src\Controllers\User\AttendantController;


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

//Configuração do CORS
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// --------------------------+
// Inicio das rotas
// --------------------------+


//Rotas GET
$app->get('/home/{id}', DashboardController::class . ":home")->add(new JWTMiddleware());
$app->get('/bot', BotController::class . ":widget")->add(new JWTMiddleware());

// Rotas POST
$app->post('/bot', BotController::class . ":chatBot")->add(new JWTMiddleware());
$app->post(API_VERSION . '/create/token', JWTController::class . ":createToken");
$app->post(API_VERSION . '/create/attendant', AttendantController::class . ":createAttendant")->add(new JWTMiddleware());
$app->post(API_VERSION . '/create/client', ClientController::class . ":createClient")->add(new JWTMiddleware());
$app->post(API_VERSION . '/history/read', DashboardController::class . ":msgHistory")->add(new JWTMiddleware());


// --------------------------+
// Fim da rotas
// --------------------------+

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});


//Resposta padrão em caso de erro
try {
    $app->run();
} catch (Exception $e) {

    $result = array(
        "result" => false,
        "error" => array("msg" => "Erro 404 - Not Found!")
    );

    header('Content-Type', 'application/json');
    echo json_encode($result);
    die();
}
