<?php
require __DIR__ . './vendor/autoload.php';

use Src\Models\AttendantModel;
use Src\Models\ClientModel;

# **********************
# Cadastrar Usuário
# **********************

$client = new ClientModel();
$attendant = new AttendantModel();

//Função para perguntar
function inputResp()
{
    $handle = fopen("php://stdin", "r");
    do {
        $line = fgets($handle);
    } while ($line == '');
    fclose($handle);
    return $line;
};

//Limpar terminal
function clearTerminal()
{
    popen('cls', 'w');
};

//log Inicio
function logInicio()
{
    echo "\nInício --- " . date("H:i:s") . "\n";
}

//log Inicio
function logFim()
{
    echo "\n\nFim --- " . date("H:i:s") . "\n";
}

//remover quebra de linha
function removerQuebraLinha($str){   
    $str = str_replace("\n", "", $str);
    $str = str_replace("\r", "", $str);
    $str = preg_replace('/\s/',' ',$str);
    return $str;
}

echo "\nEscolha um tipo de usuário para cadastrar:";
echo "\n 1 - Atendente.";
echo "\n 2 - Cliente";
echo "\n 0 - Sair.";
echo "\n\nObs.: Apenas os atendentes tem autorização para cadastrar outro atendente ou cliente.";
echo "\n\nTipo: ";
$resp = inputResp();

switch (trim($resp)) {

    case 0:
        # Cancelar
        clearTerminal();
        echo "\nCancelado!";
        break;
    case 1:
        clearTerminal();
        logInicio();
        echo "\n\nInforme os dados do novo usuário.";

        echo "\n\nNome: ";
        $resp = removerQuebraLinha(inputResp());
        $params["name"] = $resp;

        echo "\nSobrenome: ";
        $resp = removerQuebraLinha(inputResp());
        $params["lastname"] = $resp;

        echo "\nCPF: ";
        $resp = removerQuebraLinha(inputResp());
        $params["cpf"] = $resp;

        echo "\nAvatar (link da imagem): ";
        $resp = removerQuebraLinha(inputResp());
        $params["avatar"] = $resp;

        echo "\nInforme a chave secreta: ";
        $resp = removerQuebraLinha(inputResp());
       
        if(trim($resp) === JWT_SECRET){
            $attendant->createAttendant($params);
            echo "\n\n" . $attendant->getError()['msg'];
        }else{
            echo "\n\nChave secreta inválida, tente novamente!";
        }       

        logFim();
        break;
    case 2:
        clearTerminal();
        logInicio();
        echo "\n\nInforme os dados do novo usuário.";

        echo "\n\nNome: ";
        $resp = removerQuebraLinha(inputResp());
        $params["name"] = $resp;

        echo "\nSobrenome: ";
        $resp = removerQuebraLinha(inputResp());
        $params["lastname"] = $resp;

        echo "\nCPF: ";
        $resp = removerQuebraLinha(inputResp());
        $params["cpf"] = $resp;

        echo "\nAvatar (link da imagem): ";
        $resp = removerQuebraLinha(inputResp());
        $params["avatar"] = $resp;

        echo "\nInforme a chave secreta: ";
        $resp = removerQuebraLinha(inputResp());
       
        if(trim($resp) === JWT_SECRET){
            $client->saveClient($params);
            echo "\n\n" . $client->getError()['msg'];
        }else{
            echo "\n\nChave secreta inválida, tente novamente!";
        }       

        logFim();
        break;
    default:
        echo "\nEssa opção não exite, tente novamente";
        break;
}

exit;
