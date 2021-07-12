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
        $resp = inputResp();
        $params["name"] = $resp;

        echo "\nSobrenome: ";
        $resp = inputResp();
        $params["lastname"] = $resp;

        echo "\nCPF: ";
        $resp = inputResp();
        $params["cpf"] = $resp;

        echo "\nAvatar (link da imagem): ";
        $resp = inputResp();
        $params["avatar"] = $resp;

        echo "\nInforme a chave secreta: ";
        $resp = inputResp();
       
        if(trim($resp) === JWT_SECRET){
            $attendant->saveAttendant($params);
            echo "\n\n" . $attendant->getError()['msg'];
        }else{
            echo "\n\nChave secreta inválida, tente novamente!";
        }       

        logFim();
        break;
    case 2:
        clearTerminal();
        logInicio();

        logFim();
        break;
    default:
        echo "\nEssa opção não exite, tente novamente";
        break;
}

exit;
