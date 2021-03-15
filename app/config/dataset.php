<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Models\BotModel;

# **********************
# Cadastrar Novos Dados
# **********************

$BotModel = new BotModel;

# Limpar tabela
$BotModel->clearTable("chatbot", "app_bot");

# Inicio
echo "\nInício --- " . date("H:i:s") . "\n";
//sleep(1);

# Buscar dados de um arquivo json
# $BotModel->createExemplesJsonFile('app_bot.json', true);

# Buscar dados de arquivos txt dentro de uma pasta
$BotModel->createExemplesFolderTxt('treino/', true);
//sleep(1);

# Logs dos registros
echo "\n" . $BotModel->getError() . "\n";
//sleep(1);

# Fim
echo "\nFim --- " . date("H:i:s") . "\n\n";

exit;