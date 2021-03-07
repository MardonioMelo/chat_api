<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Models\BotModel;


$BotModel = new BotModel;

echo "\n --- Início --- \n";
$BotModel->createExemplesJsonFile('app_bot.json', true);
echo "\n" . $BotModel->getError() . "\n";
echo "\n --- Fim --- \n\n";
die();