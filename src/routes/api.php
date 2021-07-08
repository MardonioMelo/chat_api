<?php

use Src\Controllers\Home\AttendantController;
use Src\Controllers\Home\DashboardController;
use Src\Controllers\Bot\BotController;
use Slim\Exception\HttpNotFoundException;
use Src\Controllers\JWT\JWTController;

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

//$app->get(getenv('API_VERSION'). '/home/{id}', Api::class . ":home"); 
$app->get('/', Dashboard::class . ":home");
$app->get('/bot', BotController::class . ":widget");
$app->get('/widget', Dashboard::class . ":widget");


// Rotas POST
$app->post('/bot', BotController::class . ":chatBot");
//Chat
$app->post(API_VERSION . '/history/read', DashboardController::class . ":msgHistory");
$app->post(API_VERSION . '/create/attendant', AttendantController::class . ":createAttendant");
$app->post(API_VERSION . '/create/client', AttendantController::class . ":createClient");
$app->post(API_VERSION . '/create/token', JWTController::class . ":createToken");


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
        "error" => "Esta ação não é permitida!"
    ];

    die(json_encode($arr));
}
