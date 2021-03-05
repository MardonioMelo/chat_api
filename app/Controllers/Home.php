<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BotModel;

/**
 * Classe controller principal da API
 */
class Home
{
    private $BotModel;
    private $linkImg;

    public function __construct()
    {
        $this->BotModel = new BotModel();
    }

    /**
     * Executa pagina index
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function home(Request $request, Response $response, array $args)
    {
        $payload = $this->processData();

        //$payload = "API para consulta para chatbot de atendimento";
        $response->getBody()->write($payload);
        return $response;
    }

    //Processar dados
    public function processData()
    {
        $urlJson = '../app/config/refinar_dados/teste.json';

        if (is_file($urlJson)) {
            $payload = "Sucesso!";

            //Consultar todos os chamados em um arquivo json
            $getJson = json_decode(file_get_contents($urlJson));

            $id_anterior = 0;
            $cham_bot = [];

            //Consultar registros de um chamado
            foreach ($getJson->data as $key => $cham) {

                if ((int) $id_anterior === (int) $cham->cham_id) {

                    $key_anterior = $key - 1;

                    if ((int) $cham->inter_cli_id > 0) {

                        //Msg cliente       
                        if (empty($cham_bot[$key_anterior]["bot_exemples"]) && !empty($cham_bot[$key_anterior]["bot_reply"])) {

                            $cham_bot[$key_anterior]["bot_exemples"] = [trim(strip_tags($cham->inter_historico))];
                        } else {
                            $cham_bot[$key]["bot_intent"] = "teste";
                            $cham_bot[$key]["bot_entitie"] = "teste";
                            $cham_bot[$key]["bot_exemples"] = [trim(strip_tags($cham->inter_historico))];
                            $cham_bot[$key]["bot_reply"] = "";
                        }
                    } else {

                        //Msg funcionário
                        if (empty($cham_bot[$key_anterior]["bot_reply"]) && !empty($cham_bot[$key_anterior]["bot_exemples"])) {

                            $cham_bot[$key_anterior]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                        } else {
                            $cham_bot[$key]["bot_intent"] = "teste";
                            $cham_bot[$key]["bot_entitie"] = "teste";
                            $cham_bot[$key]["bot_exemples"] = "";
                            $cham_bot[$key]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                        }
                    }
                } else {

                    //Excluir conversa anterior que não casou o exemplo e a resposta
                    if (empty($cham_bot[$key - 1]["bot_exemples"]) || empty($cham_bot[$key - 1]["bot_reply"])) {
                        unset($cham_bot[$key - 1]);
                    }

                    //Abertura do chamado - cliente e funcionário
                    $cham_bot[$key]["bot_intent"] = "teste";
                    $cham_bot[$key]["bot_entitie"] = "teste";
                    $cham_bot[$key]["bot_exemples"] = [trim(strip_tags($cham->cham_historico))];
                    $cham_bot[$key]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                }

                $id_anterior = $cham->cham_id;
            };

            //Excluir ultima conversa que não casou o exemplo e a resposta
            if (empty($cham_bot[$key]["bot_exemples"]) || empty($cham_bot[$key]["bot_reply"])) {
                unset($cham_bot[$key]);
            }
        } else {
            $payload = "O arquivo json não existe ou o caminho está errado!";
        }

        //Salvar os dados processados
        $this->saveProcessData($cham_bot);

        return $payload;
    }

    /**
     * Salvar dados no banco
     * Essa é a estrutura dentro de cada chave no array
     * array [
     *  ["bot_intent"] => "string unica"
     *  ["bot_entitie"] => "string"   
     *  ["bot_exemples"]=> array["ok1","ok2","ok3"]
     *  ["bot_reply"]=> "string"
     * ]
     * 
     * @param array $arr
     * @return void
     */
    public function saveProcessData($arr)
    {
        foreach ($arr as $item) {

            $this->BotModel->createExemple(
                $item["bot_intent"],
                $item["bot_entitie"],
                $item["bot_exemples"],
                $item["bot_reply"]
            );
        };
    }
}
