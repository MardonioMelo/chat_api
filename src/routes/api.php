<?php

use Src\Controllers\Bot\BotController;
use Src\Controllers\JWT\JWTController;
use Src\Controllers\Middleware\JWTMiddleware;
use Slim\Exception\HttpNotFoundException;
use Src\Controllers\User\ClientController;
use Src\Controllers\Home\DashboardController;
use Src\Controllers\User\AttendantController;


# Define o caminho base
$app->setBasePath("/chat_api");

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

// JWT
$app->post(API_VERSION . '/token', JWTController::class . ":createToken");
// CRUD Atendente
$app->post(API_VERSION . '/attendant', AttendantController::class . ":createAttendant")->add(new JWTMiddleware());
$app->get(API_VERSION . '/attendant', AttendantController::class . ":readAllAttendant")->add(new JWTMiddleware());
$app->get(API_VERSION . '/attendant/{id}', AttendantController::class . ":readAttendant")->add(new JWTMiddleware());
$app->put(API_VERSION . '/attendant/{id}', AttendantController::class . ":updateAttendant")->add(new JWTMiddleware());
$app->delete(API_VERSION . '/attendant/{id}', AttendantController::class . ":deleteAttendant")->add(new JWTMiddleware());
// CRUD Cliente
$app->post(API_VERSION . '/client', ClientController::class . ":createClient")->add(new JWTMiddleware());
$app->get(API_VERSION . '/client', ClientController::class . ":readAllClient")->add(new JWTMiddleware());
$app->get(API_VERSION . '/client/{id}', ClientController::class . ":readClient")->add(new JWTMiddleware());
$app->put(API_VERSION . '/client/{id}', ClientController::class . ":updateClient")->add(new JWTMiddleware());
$app->delete(API_VERSION . '/client/{id}', ClientController::class . ":deleteClient")->add(new JWTMiddleware());

// --------------------------+
// Fim da rotas
// --------------------------+

/**
 * Rota pega-tudo para exibir uma página 404 não encontrada se nenhuma das rotas corresponder
 * NOTA: certifique-se de que esta rota seja definida por último
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});


//Resposta padrão em caso de erro
try {
    $app->run();
} catch (Exception $e) {

    if ($e->getMessage() === "Expired token") {
        $msg = "Token expirado";
    } elseif ($e->getMessage() === "Malformed UTF-8 characters") {
        $msg = "Caracteres UTF-8 malformados";
    } else {
        $msg = "Erro 404 - Não encontrado!";
    }

    $result = array(
        "result" => false,
        "error" => array("msg" => $msg)
    );

    header('Content-Type', 'application/json');
    echo json_encode($result);
    die();
}
