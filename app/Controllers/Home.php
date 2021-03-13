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

    public function __construct()
    {
        set_time_limit(3600); // 60 minutos de execução máxima
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
        //$payload = $this->processData();

        $payload = "<br> API para chatbot de atendimento!";
        $response->getBody()->write($payload);
        return $response;
    }

    //Processar dados
    public function processData()
    {
        $urlJson = '../app/config/refinar_dados/help_chamados.json';

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
                            $cham_bot[$key]["bot_intent"] = $this->processIntent($cham->cham_assunto);
                            $cham_bot[$key]["bot_entitie"] = $this->processEntities($this->processIntent($cham->cham_assunto));
                            $cham_bot[$key]["bot_exemples"] = [trim(strip_tags($cham->inter_historico))];
                            $cham_bot[$key]["bot_reply"] = "";
                        }
                    } else {

                        //Msg funcionário
                        if (empty($cham_bot[$key_anterior]["bot_reply"]) && !empty($cham_bot[$key_anterior]["bot_exemples"])) {

                            $cham_bot[$key_anterior]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                        } else {
                            $cham_bot[$key]["bot_intent"] = $this->processIntent($cham->cham_assunto);
                            $cham_bot[$key]["bot_entitie"] = $this->processEntities($this->processIntent($cham->cham_assunto));
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
                    $cham_bot[$key]["bot_intent"] = $this->processIntent($cham->cham_assunto);
                    $cham_bot[$key]["bot_entitie"] = $this->processEntities($this->processIntent($cham->cham_assunto));
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
        // die("Se vc deseja cadastrar novos dados nesse loop, remova essa linha para continuar!");

        foreach ($arr as $key => $item) {

            $this->BotModel->createExemple(
                $item["bot_intent"] . "_" . $key,
                $item["bot_entitie"],
                $item["bot_exemples"],
                $item["bot_reply"]
            );

            echo  $key . " - " . $this->BotModel->getError() . "<br>";
        };
    }

    /**
     * Função para obter a intenção conforme o assunto informado
     *
     * @param string $assunto
     * @return void
     */
    public function processIntent($assunto)
    {
        $intent = [
            "Dúvidas - Como utilizar algum recurso do sistema" => "duvida_utilizar_recurso",
            "Dúvidas - Já efetuei o pagamento da mensalidade do Sistema, mas ele não desaparece" => "duvida_pagamento",
            "Dúvidas - Não consigo completar alguma ação no sistema" => "duvida_completar_action",
            "Dúvidas - O sistema dá mensagem de mensalidade em atraso" => "duvida_mensalidade_atrasada",
            "Dúvidas - O sistema está com informações erradas" => "duvida_info_erradas",
            "Dúvidas - O valor da mensalidade do sistema está errado" => "duvida_valor_mensalidade",
            "Dúvidas - Outro..." => "duvida_outro",
            "Dúvidas - Sugestão de recurso" => "duvida_dica_recurso",
            "Problemas - Como utilizar algum recurso do sistema" => "problema_utilizar_recurso",
            "Problemas - Estou dando uma informação mas ela não é salva" => "problema_salvar_dados",
            "Problemas - Já efetuei o pagamento da mensalidade do Sistema, mas ele não desaparece" => "problema_pag_mensalidade",
            "Problemas - Não consigo completar alguma ação no sistema" => "problema_completar_actionINSERT INTO app_bot (
                bot_id,
                bot_intent,
                bot_entitie,
                bot_exemples,
                bot_reply
              )
            VALUES (
                bot_id:int,
                'bot_intent:varchar',
                'bot_entitie:varchar',
                'bot_exemples:longtext',
                'bot_reply:text'
              );",
            "Problemas - Não estou achando uma página do sistema" => "problema_localizar_page",
            "Problemas - O sistema dá mensagem de mensalidade em atraso" => "problema_mensalidade_atrasada",
            "Problemas - O sistema está com informações erradas" => "problema_info_erradas",
            "Problemas - O sistema mostra um erro ao tentar acessar uma página" => "problema_acessar_page",
            "Problemas - O valor da mensalidade do sistema está errado" => "problema_valor_mensalidade",
            "Problemas - Outro..." => "problema_outro",
            "Problemas - Quando tento preencher uma informação, ela é salva errada" => "problema_dados_errados",
            "Problemas - Sugestão de recurso" => "problema_dica_recurso"
        ];

        return $intent[$assunto];
    }

    /**
     * Função para obter as entidades conforme o assunto informado
     * Futuramente as entidades devem ser obtidas baseadas nos exemplos de treinamento
     *
     * @param string $assunto
     * @return void
     */
    public function processEntities($intent)
    {
        $entitie = [
            "duvida_utilizar_recurso" => "recurso do sistema",
            "duvida_pagamento" =>  "pagamento de mensalidade",
            "duvida_completar_action" => "completar ação",
            "duvida_mensalidade_atrasada" => "mensalidade em atraso",
            "duvida_info_erradas" => "informações erradas",
            "duvida_valor_mensalidade" =>  "valor mensalidade errada",
            "duvida_outro" => "outra dúvida",
            "duvida_dica_recurso" => "sugestão de recurso",
            "problema_utilizar_recurso" => "utilizar recurso",
            "problema_salvar_dados" => "informação não salva",
            "problema_pag_mensalidade" => "pagamento da mensalidade",
            "problema_completar_action" =>  "completar ação",
            "problema_localizar_page" => "achar uma página",
            "problema_mensalidade_atrasada" => "mensalidade em atraso",
            "problema_info_erradas" => "informações erradas",
            "problema_acessar_page" => "erro ao acessar página",
            "problema_valor_mensalidade" => "valor da mensalidade errado",
            "problema_outro" => "outro problema",
            "problema_dados_errados" => "informação salva errada",
            "problema_dica_recurso" => "sugestão de recurso"
        ];

        return $entitie[$intent];
    }
}
