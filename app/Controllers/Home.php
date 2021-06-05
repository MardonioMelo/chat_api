<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BotModel;
use App\View\PainelChat\PainelChatView;

/**
 * Classe controller principal da API
 */
class Home
{

    private $BotModel;
    private $PainelChatView;

    public function __construct()
    {       
        $this->BotModel = new BotModel();
        $this->PainelChatView = new PainelChatView();
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

        $payload = $this->PainelChatView->tplPainelView(["Painel de Chat"]);
        $response->getBody()->write($payload);
        return $response;
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
            "Problemas - Não consigo completar alguma ação no sistema" => "problema_completar_action",
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
