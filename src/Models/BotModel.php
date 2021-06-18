<?php

namespace Src\Models;

use Src\Models\DataBase\ChatBot;
use Requests;
use CoffeeCode\DataLayer\Connect;


/**
 * Class BotModel
 * @package App\Models
 */
class BotModel
{

    private $Error;
    private $Result;
    /** @var ChatBot */
    private $chatbot;


    public function __construct()
    {
        $this->chatbot = new ChatBot();
    }

    /**
     * Bom turno
     * @return string
     */
    public function cumprimento($nameUser)
    {
        $hr = date(" H ");
        if ($hr >= 12 && $hr < 18) {
            $resp = "Boa tarde! <small style='font-size:20px'>&#128515;</small>";
        } else if ($hr >= 0 && $hr < 12) {
            $resp = "Bom dia! <small style='font-size:20px'>&#128515;</small>";
        } else {
            $resp = "Boa noite {$nameUser}! <small style='font-size:20px'>&#128564;</small>";
        }

        return $resp;
    }

    /**
     * Faz uma conta simples tipo 2 + 2
     * @param $text
     * @param $nameUser
     * @return string
     */
    public function conta($text, $nameUser)
    {
        $cal = array_values(array_filter(array_map(function ($t) {
            $t = str_replace(" ", "", $t);
            if ((float)$t > 0 || $t === "+" || $t === "-" || $t === "*" || $t === "/") {
                return $t;
            } else {
                return "";
            }
        }, explode(" ", $text))));

        $v1 = empty($cal[0]) ? 1 : str_replace(",", ".", $cal[0]);
        $op = empty($cal[1]) ? "" : $cal[1];
        $v2 = empty($cal[2]) ? 1 : str_replace(",", ".", $cal[2]);
        $invalid = empty($cal[3]) ? true : false;

        $result = $nameUser . ", <small style='font-size:20px'>&#128517;</small> eu sei calcular apenas dois números por vez, tente assim: 2 + 2";

        if ($invalid) {
            if ($op === "+") :
                $c = (float)$v1 + (float)$v2;
                $result = "A adição é " . (string)str_replace(".", ",", $c) .
                    " <small style='font-size:20px'>&#129488;</small>";
            elseif ($op === "-") :
                $c = (float)$v1 - (float)$v2;
                $result = "A subtração é " . (string)str_replace(".", ",", $c) .
                    " <small style='font-size:20px'>&#129488;</small>";
            elseif ($op === "/") :
                $c = (float)$v1 / (float)$v2;
                $result = "A divisão é " . (string)str_replace(".", ",", $c) .
                    " <small style='font-size:20px'>&#129488;</small>";
            elseif ($op === "*") :
                $c = (float)$v1 * (float)$v2;
                $result = "A multiplicação é " . (string)str_replace(".", ",", $c) .
                    " <small style='font-size:20px'>&#129488;</small>";
            else :
                $result = $nameUser . " você esqueceu de informar uma operação entre os dois números.<br>Exemplo: 10 / 2";
            endif;
        }

        return $result;
    }

    /**
     * Enviar dados para uma api interna ou externa via método POST
     * @param $url
     * @param $data
     * @param array $options
     */
    public function postRequest($url, $data, $options = [])
    {
        $headers = array('Accept' => 'application/json');
        $request = Requests::post($url, $headers, $data, $options);

        if ($request->status_code === 200) {
            $this->Result = json_decode($request->body);
            $this->Error = false;
        } else {
            $this->Result = "Erro: código " . $request->status_code;
            $this->Error = true;
        }
    }

    /**
     * @param array $data = array com strings a seres pesquisadas
     */
    public function exeReadArray($data = [])
    {
        $this->loopColsSearch($data);
    }

    /**
     * Executar consulta de todos de todos os exemplos cadastrados
     *
     * @return void
     */
    public function exeReadAllExemples()
    {
        $this->readAllExemples();
    }

    /**
     * Função para cadastrar novo exemplo para treinamento
     *
     * @param string $bot_intent = Intenção do exemplo
     * @param string $bot_entitie = Entidades do exemplo
     * @param array $bot_exemples = Exemplos com essa estrutura ["ok1","ok2","ok3"]
     * @param string $bot_reply = Resposta ou Ação
     * @return void
     */
    public function createExemple($bot_intent, $bot_entitie, $bot_exemples, $bot_reply)
    {
        if (!empty($bot_intent) && !empty($bot_entitie) && !empty($bot_exemples) && !empty($bot_reply)) {

            $create = new ChatBot;
            $create->bot_intent = $bot_intent;
            $create->bot_entitie = $bot_entitie;
            $create->bot_exemples = json_encode($bot_exemples);
            $create->bot_reply = $bot_reply;

            $result = $create->save();

            if (!$result) {
                $this->Result = false;
                $this->Error = $create->fail()->getMessage();
            } else {
                $this->Result = true;
                $this->Error = "Cadastro realizado com sucesso!";
            }
        } else {
            $this->Result = false;
            $this->Error = "Não foi possível cadastrar, informe todos os parâmetros!";
        }
    }

    /**
     * Cadastra dados no banco de dados obtidos de um arquivo json  
     * 
     * @param string $url = URL onde está o arquivo json com os dados na estrutura predefinida.
     * @param bool $log = Opcional - informe true para imprimir os log de cada cadastro.
     * @param string $destino = informe txt para salvar em aquivo de texto ou db para cadastrar no banco de dados
     * @return void
     */
    public function createExemplesJsonFile($url, $log = false, $destino)
    {
        if (is_file($url)) {

            //Consultar todos de um arquivo json
            $getJson = json_decode(file_get_contents($url));

            if ($destino === 'db') {
                //Consultar registros
                foreach ($getJson->data as $item) {
                    $this->createExemple(
                        $item->bot_intent,
                        $item->bot_entitie,
                        $item->bot_exemples,
                        $item->bot_reply
                    );

                    if ($log) {
                        echo "\n" . $this->getError();
                    }
                };
            } else {
                //Consultar registros
                foreach ($getJson->data as $item) {

                    $texto =  "|1 \n";
                    $texto .= $item->bot_intent;
                    $texto .= "\n|2\n";
                    $texto .= $item->bot_entitie;
                    $texto .= "\n|3\n";
                    $texto .= implode("\n", $item->bot_exemples);
                    $texto .= "\n|4\n";
                    $texto .= implode("\n", explode("|", $item->bot_reply));
                    $texto .= "\n|5";

                    //criamos o arquivo
                    $arquivo = fopen('treino/' . $item->bot_intent . '.txt', 'w');
                    //verificamos se foi criado
                    if ($arquivo == false) die('Não foi possível criar o arquivo.');
                    //escrevemos no arquivo                   
                    fwrite($arquivo, $texto);
                    //Fechamos o arquivo após escrever nele
                    fclose($arquivo);

                    if ($log) {
                        echo "\nArquivo de treino criado: " .  $item->bot_intent, ".txt";
                    };
                };
            };

            $this->Result = false;
            $this->Error = "Sucesso!";
        } else {
            $this->Result = false;
            $this->Error = "O arquivo json não existe ou o caminho está errado!";
        }
    }

    /**
     * Cadastra dados no banco de dados obtidos de uma pasta com arquivos txt nomeados
     * 
     * @param string $url = URL da pasta onde estão os arquivos txt com os dados na estrutura predefinida para este formato.
     * @param bool $log = Opcional - informe true para imprimir os log de cada cadastro.
     * @return void
     */
    public function createExemplesFolderTxt($url, $log = false)
    {
        if (is_dir($url)) {

            //Consultar todos de um arquivo 
            $getDir = dir($url);
            $erros = 0;
            $n_treinos = 0;

            //Consultar registros
            while ($file = $getDir->read()) {

                if (substr($file, -3) === 'txt' && $file !== 'exemplo.txt') {

                    $lines = file($url . DIRECTORY_SEPARATOR . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $input = '';
                    $bot_intent = '';
                    $bot_entitie = '';
                    $bot_exemples = [];
                    $bot_reply = [];

                    foreach ($lines as $line) {

                        switch (trim($line)) {

                            case '|1':
                                $input = 'intent';
                                $line = '';
                                break;
                            case '|2':
                                $input = 'entitie';
                                $line = '';
                                break;
                            case '|3':
                                $input = 'exemples';
                                $line = '';
                                break;
                            case '|4':
                                $input = 'reply';
                                $line = '';
                                break;
                            case '|5':
                                $input = 'send';
                                $line = '';
                                break;
                        };

                        switch ($input) {

                            case 'intent':
                                $bot_intent = trim($line);
                                break;
                            case 'entitie':
                                $bot_entitie = trim($line);
                                break;
                            case 'exemples':
                                if (!empty($line)) {
                                    $bot_exemples[] = trim($line);
                                };
                                break;
                            case 'reply':
                                if (!empty($line)) {
                                    $bot_reply[] = trim($line);
                                }
                                break;
                            case 'send':
                                if (count($bot_reply) > 1) {
                                    $reply =  implode("|", $bot_reply);
                                } else {
                                    $reply = $bot_reply[0];
                                };

                                $this->createExemple($bot_intent, $bot_entitie, $bot_exemples, $reply);

                                if ($this->getResult()) {
                                    $result = "Sucesso!";
                                    $n_treinos += 1;
                                } else {
                                    $result = "Erro!";
                                    $erros += 1;
                                }

                                if ($log) {
                                    echo "\nTreino: " . $bot_intent . " - " . $result;
                                };

                                $input = 'end';
                                $bot_intent = '';
                                $bot_entitie = '';
                                $bot_exemples = [];
                                $bot_reply = [];
                                break;
                        };
                    };

                    if ($input !== 'end') {
                        echo "\n" . $bot_intent . " - não foi cadastrado porque faltou a instrução |5.";
                    };
                };
            };
            $getDir->close();

            $this->Result = false;
            $this->Error = "\nErros: " . $erros . "\nTreinos: " . $n_treinos;
        } else {
            $this->Result = false;
            $this->Error = "O arquivo json não existe ou o caminho está errado!";
        }
    }

    /**
     * Cadastra dados no banco de dados obtidos de uma pasta com arquivos txt nomeados
     * 
     * @param string $url = URL da pasta onde estão os arquivos txt com os dados na estrutura predefinida para este formato.
     * @param bool $log = Opcional - informe true para imprimir os log de cada cadastro.
     * @return void
     */
    public function renameIntentExemples($url, $log = false)
    {
        if (is_dir($url)) {

            //Consultar todos de um arquivo 
            $getDir = dir($url);
            $erros = 0;
            $n_treinos = 0;

            //Consultar registros
            while ($file = $getDir->read()) {
                if ($file !== '.' && $file !== '..' && $file !== 'exemplo.txt') {

                    $arquivo = $url . DIRECTORY_SEPARATOR . $file;
                    $lines = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $input = '';
                    $bot_intent = '';
                    $bot_entitie = '';
                    $bot_exemples = [];
                    $bot_reply = [];
                    $texto = '';
                    $n_intent = 1;

                    foreach ($lines as $line) {

                        switch (trim($line)) {

                            case '|1':
                                $input = 'intent';
                                $line = '';
                                break;
                            case '|2':
                                $input = 'entitie';
                                $line = '';
                                break;
                            case '|3':
                                $input = 'exemples';
                                $line = '';
                                break;
                            case '|4':
                                $input = 'reply';
                                $line = '';
                                break;
                            case '|5':
                                $input = 'send';
                                $line = '';
                                break;
                        };

                        switch ($input) {

                            case 'intent':
                                $bot_intent = trim($line);
                                break;
                            case 'entitie':
                                $bot_entitie = trim($line);
                                break;
                            case 'exemples':
                                if (!empty($line)) {
                                    $bot_exemples[] = trim($line);
                                };
                                break;
                            case 'reply':
                                if (!empty($line)) {
                                    $bot_reply[] = trim($line);
                                }
                                break;
                            case 'send':
                                if (count($bot_reply) > 1) {
                                    $reply =  implode("|", $bot_reply);
                                } else {
                                    $reply = $bot_reply[0];
                                };

                                $name_file = explode(".", $file)[0];

                                $texto .= "\n|1\n" . $name_file . ($n_intent === 1 ? '' : "_" . $n_intent);
                                $texto .= "\n|2\n" . $bot_entitie;
                                $texto .= "\n|3\n" . implode("\n", $bot_exemples);
                                $texto .= "\n|4\n" . str_replace("|", "\n", $reply);
                                $texto .= "\n|5\n";

                                $result = "Sucesso!";
                                $n_treinos += 1;

                                if ($log) {
                                    echo "\nTreino: " . $bot_intent . " - " . $result;
                                };

                                $n_intent += 1;
                                $input = 'end';
                                $bot_intent = '';
                                $bot_entitie = '';
                                $bot_exemples = [];
                                $bot_reply = [];
                                break;
                        };
                    };

                    if ($input !== 'end') {
                        echo "\n" . $bot_intent . " - não foi cadastrado porque faltou a instrução |5.";
                    } else {
                        $this->writeToFile($arquivo, $texto);
                    };
                };
            };
            $getDir->close();

            $this->Result = false;
            $this->Error = "\nErros: " . $erros . "\nTreinos: " . $n_treinos;
        } else {
            $this->Result = false;
            $this->Error = "O arquivo json não existe ou o caminho está errado!";
        }
    }

    /**
     * Função que recebe um texto e salva em um caminho de arquivo informado.
     *
     * @param string $arquivo = caminho do arquivo com o nome e extensão.
     * @param string $texto = conteúdo a ser gravado no arquivo.
     * 
     * @return void
     */
    public function writeToFile($arquivo, $texto)
    {
        //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
        $fp = fopen($arquivo, "w+");

        //Escreve no arquivo aberto.
        fwrite($fp, $texto);

        //Fecha o arquivo.
        fclose($fp);
    }


    /**
     * Limpar dados de uma tabela   
     *
     * @param string $db
     * @param string $tb
     * @return void
     */
    public function clearTable(string $db, string $tb): void
    {
        /*
        * GET PDO instance AND errors
        */
        $connect = Connect::getInstance();
        $error = Connect::getError();

        /*
        * CHECK connection/errors
        */
        if ($error) {
            echo $error->getMessage();
        } else {
            echo "\nTRUNCATE realizado com sucesso na tabela: " . $db . "." . $tb;
        };

        $connect->query("TRUNCATE {$db}.{$tb}");
    }

    /**
     * <b>Verificar Ação:</b> Retorna TRUE se ação for efetuada ou FALSE se não. Para verificar erros
     * execute um getError();
     * @return BOOL $Var = True(com os dados) or False
     */
    public function getResult()
    {
        return $this->Result;
    }

    /**
     * <b>Obter Erro:</b> Retorna um string com um erro e um tipo.
     * @return string $Error = String com o erro
     */
    public function getError()
    {
        return $this->Error;
    }

    /*
     * ***************************************
     * **********  PRIVATE METHODS  **********
     * ***************************************
     */

    /**
     * <b>Loop:</b> Para formar Query de pesquisa nas colunas informadas
     * @param $data
     */
    private function loopColsSearch($data)
    {
        $col = "bot_exemples";
        $end = end($data);
        $Query['search'] = '';
        $Query['col'] = '(';
        foreach ($data as $key => $value) {
            $Query['col'] .= $end === $value ? $col . " LIKE '%' :link" . $key . " '%' " : $col . " LIKE '%' :link" . $key . " '%' OR ";
            $Query['search'] .= $end === $value ? "link" . $key . "=" . $value : "link" . $key . "=" . $value . "&";
        }
        $Query['col'] .= ')';

        $this->chatbot->readCol($Query['col'], $Query['search']);
        $this->Result = $this->chatbot->getResult();
        $this->Error = $this->chatbot->getError();
    }

    /**
     * Consultar todos os dados de exemplos cadastrados
     *
     * @return void
     */
    private function readAllExemples()
    {
        $this->chatbot->readAll();
        $this->Result = $this->chatbot->getResult();
        $this->Error = $this->chatbot->getError();
    }
}
