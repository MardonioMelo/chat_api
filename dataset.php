<?php
require __DIR__ . './vendor/autoload.php';

use Src\Models\BotModel;

# **********************
# Cadastrar Novos Dados
# **********************

$BotModel = new BotModel;

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

//Limpar terminal
function clearTerminal(){    
    popen('cls', 'w');
};

//log Inicio
function logInicio(){    
    echo "\nInício --- " . date("H:i:s") . "\n";
}

//log Inicio
function logFim(){    
    echo "\n\nFim --- " . date("H:i:s") . "\n";
}

echo "\nEscolha um método para executar:";
echo "\n 1 - Buscar dados de um arquivo json e salva no db.";
echo "\n 2 - Buscar dados de um arquivo json e salva em arquivos de txt na pasta treino.";
echo "\n 3 - Buscar dados de arquivos txt dentro de uma pasta e salvar no db.";
echo "\n 4 - Renomear intent conforme nome dos arquivos quentão na pasta de treino;";
echo "\n 5 - Limpar todos os dados da tabela chat_bot.";
echo "\n 6 - Realizar teste do NLP.";
echo "\n 0 - Sair.";
echo "\n\nObs 1: os arquivos são consultados e salvos na pasta 'treino'.";
echo "\nObs 2: os dados são salvos no db chatbot e tabela chat_bot conforme arquivo src/db/chat_bot.sql";
echo "\n\nMétodo: ";

$metodo = inputMetodo();

switch (trim($metodo)) {

    case 0:
        # Cancelar
        clearTerminal();
        echo "\nCancelado!";
        break;
    case 1:
        clearTerminal();
        logInicio();
        # Buscar dados de um arquivo json e salva no banco de dados            
        $BotModel->createExemplesJsonFile('treino/treino.json', true, "db");
        logFim();
        break;
    case 2:
        clearTerminal();
        logInicio();
        # Buscar dados de um arquivo json e salva em arquivos de txt na pasta 'treino'     
        $BotModel->createExemplesJsonFile('treino/treino.json', true, "txt");
        logFim();
        break;
    case 3:
        clearTerminal();
        logInicio();
        # Buscar dados de arquivos txt dentro de uma pasta      
        $BotModel->createExemplesFolderTxt('treino/', true);
        logFim();
        break;
    case 4:
        clearTerminal();
        logInicio();
        # Renomear intent conforme nome do arquivo
        $BotModel->renameIntentExemples('treino/', true);
        logFim();
        break;
    case 5:
        clearTerminal();
        logInicio();
        # Limpar tabela
        $BotModel->clearTable("chatbot", "chat_bot");
        logFim();
        break;
    case 6:
        # Request na API do bot para teste
        echo "\nInforme outra rota da API do Chatbot caso seja diferente de 'http://localhost/chatbot_api/bot':\n";
        echo "=> ";
        $input_url = inputMetodo();
        $url = empty(trim($input_url)) ? "http://localhost/chatbot_api/bot" : $input_url;

        clearTerminal();
        logInicio();

        $form = ["driver" => "Web", "userId" => 1, "message" => "nlp"];
        $BotModel->postRequest($url, $form, []);
        $result = json_decode(json_encode($BotModel->getResult()), true);
     
        echo "\nResultado do Teste:\n------------------\n";
        echo $result["messages"][0]["text"];
        logFim();
        break;
    default:
        echo "\nEssa opção não exite, tente novamente";
        break;
}

# Logs dos registros
echo "\n" . $BotModel->getError() . "\n";

exit;
