<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Models\BotModel;

# **********************
# Cadastrar Novos Dados
# **********************

$BotModel = new BotModel;

# Limpar tabela
$BotModel->clearTable("chatbot", "app_bot");

//Função para perguntar o qual parâmetro executar
function inputMetodo()
{
    $handle = fopen("php://stdin", "r");
    do {
        $line = fgets($handle);
    } while ($line == '');
    fclose($handle);
    return $line;
};

echo "\nEscolha um método para executar:";
echo "\n 1 - Buscar dados de um arquivo json e salva no banco de dados";
echo "\n 2 - Buscar dados de um arquivo json e salva em arquivos de txt na pasta treino";
echo "\n 3 - Buscar dados de arquivos txt dentro de uma pasta";
echo "\n 0 - Cancelar";
echo "\n\nMétodo: ";

$metodo = inputMetodo();

# Inicio
echo "\nInício --- " . date("H:i:s") . "\n";

switch ($metodo) {

    case 0:
        # Cancelar
        echo "\nCancelado!";
        break;
    case 1:
        # Buscar dados de um arquivo json e salva no banco de dados
        $BotModel->createExemplesJsonFile('app_bot.json', true, "db");
        break;
    case 2:
        # Buscar dados de um arquivo json e salva em arquivos de txt na pasta 'treino'
        $BotModel->createExemplesJsonFile('app_bot.json', true, "txt");
        break;
    case 3:
        # Buscar dados de arquivos txt dentro de uma pasta
        $BotModel->createExemplesFolderTxt('treino/', true);
        break;
    default:
        echo "\nEssa opção não exite, tente novamente";
        break;
}

# Logs dos registros
echo "\n" . $BotModel->getError() . "\n";

# Fim
echo "\nFim --- " . date("H:i:s") . "\n\n";

exit;
