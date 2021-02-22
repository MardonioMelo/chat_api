<?php

/**
 * Copyright (c) 2020.  Mardônio M. Filho STARTMELO DESENVOLVIMENTO WEB.
 */

namespace App\Models;

use App\Models\DataBase\AppBot;
use Requests;


/**
 * Class BotModel
 * @package App\Models
 */
class BotModel
{
    private $Query;
    private $Error;
    private $Result;
    /** @var AppBot */
    private $appbot;


    public function __construct()
    {
        $this->appbot = new AppBot();
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
     * Enviar dados para uma api interna ou externa
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

        $this->appbot->readCol($Query['col'], $Query['search']);
        $this->Result = $this->appbot->getResult();
        $this->Error = $this->appbot->getError();
    }
}
