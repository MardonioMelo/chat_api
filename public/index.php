<?php

require __DIR__ . '/../vendor/autoload.php';

session_start();

use Slim\Factory\AppFactory;

# Iniciar App
$app = AppFactory::create();

# Desativar erros em produção
#$app->addErrorMiddleware(false, true, true);

# Define o caminho base
$app->setBasePath("/chatbot_api");

//$chatbot = new \App\Controllers\Bot\BotController();
//$chatbot->widget();

# Rotas do App
require '../app/routes/api.php';
