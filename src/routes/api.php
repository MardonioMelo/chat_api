<?php

use Src\Controllers\Bot\BotController;
use Src\Controllers\JWT\JWTController;
use Src\Controllers\JWT\JWTMiddleware;
use Slim\Exception\HttpNotFoundException;
use Src\Controllers\Home\AttendantController;
use Src\Controllers\Home\DashboardController;


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
//$app->get(API_VERSION. '/home/{id}', Api::class . ":home"); 
$app->get('/home/{id}', DashboardController::class . ":home")->add(new JWTMiddleware());
$app->get('/bot', BotController::class . ":widget")->add(new JWTMiddleware());


// Rotas POST
$app->post('/bot', BotController::class . ":chatBot")->add(new JWTMiddleware());
// 
$app->post(API_VERSION . '/create/token', JWTController::class . ":createToken");
$app->post(API_VERSION . '/create/attendant', AttendantController::class . ":createAttendant")->add(new JWTMiddleware());
$app->post(API_VERSION . '/create/client', AttendantController::class . ":createClient")->add(new JWTMiddleware());
$app->post(API_VERSION . '/history/read', DashboardController::class . ":msgHistory")->add(new JWTMiddleware());


// --------------------------+
// Fim rotas a partir daqui
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

    $arr = [
        "success" => false,
        "error" => "Erro 404 - Not Found!"
    ];

    die(json_encode($arr));
}
