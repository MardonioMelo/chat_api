<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Models\BotModel;

$BotModel = new BotModel;

//Limpar tabela
$BotModel->clearTable("chatbot", "app_bot");

//Cadastrar novos dados
echo "\n --- Início --- \n";
$BotModel->createExemplesJsonFile('app_bot.json', true);
echo "\n" . $BotModel->getError() . "\n";
echo "\n --- Fim --- \n\n";

exit;
