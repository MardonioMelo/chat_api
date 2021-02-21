<?php
/**
 * Copyright (c) 2020.  Mardônio M. Filho STARTMELO DESENVOLVIMENTO WEB.
 */

namespace App\Controllers\Bot;

use BotMan\BotMan\BotMan;
use App\Models\BotModel;

/**
 * Class CommandController
 * @package App\Controllers\Bot
 */
class CommandController
{
    /** @var BotMan */
    private $botman;
    /** @var */
    private $botModel;
    protected $nameUser;
    protected $tenant;

    /**
     * CommandController constructor.
     * @param $botman
     */
    public function __construct($botman)
    {
        $this->botman = $botman;
        $this->nameUser = USER_NAME; //pegar na sessão
        $this->tenant =  USER_ID; //id do user
        $this->botModel = new BotModel();
    }

    /**
     * Comandos
     * @return string
     */
    public function commands()
    {
        $CONTACT_WHATSAPP = "62000000000";
        $r = "<li>Stop - para cancelar;</li>";
        $r .= "<li>Cadastrar cliente;</li>";
        $r .= "<li>Cálculos simples ex.: <br>2 + 2 ou 2 - 2 ou 2 * 2 ou 2 / 2;</li>";
        $r .= "<li>Consultar CEP;</li>";
        $r .= "<li>Cálcular frete e prazo;</li>";
        $r .= "<li>Rastreio de encomendas.</li>";
        $r .= "<p class='text-center'> - - - - - - - - - - - - - - - - - - - - - - -  - - - - - - - - - - </p>"
            . "Dúvidas ou sugestões?<br>" . '<a href="https://api.whatsapp.com/send?phone=' . $CONTACT_WHATSAPP . '"'
            . 'target="_blank" class="btn btn-flat bg-green">'
            . '<i class="fa fa-whatsapp"></i> WhatsApp'
            . '</a>';
        return $r;
    }

    /**
     * Set comandos
     */
    public function setCommand()
    {
        $command = $this->botman->getMessage()->getExtras('apiIntent');
        $reply = (string)$this->botman->getMessage()->getExtras('apiReply');

        if ($reply === "function") {
            $this->$command();
        } else {
            $this->botman->reply($reply);
        }
    }

    /**
     * Msg após cancelar conversar
     */
    public function stop()
    {
        $this->botman->reply("Cancelado! <small style='font-size:20px'>&#129296;</small>");
    }

    /**
     * Msg padrão quando o bot não entendi
     */
    public function notUnderstand()
    {
        $this->botman->reply("Desculpe, não entendi esses comandos. <small style='font-size:20px'>&#129300;</small>
         Aqui está uma lista de comandos que eu entendo: <small style='font-size:20px'>&#129299;</small> {$this->commands()}");
    }

    /**
     * Lista de comandos
     */
    public function listCommands()
    {
        $this->botman->reply("Lista de comandos que eu entendo: <small style='font-size:20px'>&#129299;</small> {$this->commands()}");
    }

    /**
     * Meu nome
     */
    public function myName()
    {
        $text = (string)$this->botman->getMessage()->getExtras('text');
        $name = strrchr($text, ' ');
        $this->botman->reply("Lindo nome {$name} <small style='font-size:20px'>&#129488;</small>");
    }

    /**
     * Teste NLP ou Stop conversa
     */
    public function nlp()
    {
        if ($this->botman->getMessage()->getExtras('text') === "nlp") {
            $this->stop();
        } else {
            $this->debug();
        }
    }

    /**
     * Cadastrar cliente
     */
    public function createCliente()
    {
        $this->botman->startConversation(new ConversationController(
            'question', [
            "question" => "Qual o <b>nome</b> do cliente?<br> ex.: Maria José Beltrana",
            "name" => "cliente_title",
            "fallback" => [
                "question" => "Qual o nome da <b>cidade</b>?<br> ex.: Anápolis",
                "name" => "cliente_cidade",
                "fallback" => [
                    "question" => "Qual o nome do <b>estado</b>?<br> ex.: Goiás",
                    "name" => "cliente_uf",
                    "fallback" => [
                        "question" => "Qual é o número do <b>WhatsApp</b>?<br> ex.: 62000000000",
                        "name" => "cliente_tel2",
                        "fallback" => [
                            "question" => "Responda 'Sim' se os dados estiverem corretos ou 'Não' se estiverem errados!",
                            "name" => "confirmation",
                            "fallback" => ""
                        ]
                    ]
                ]
            ],
            "tenant" => $this->tenant,
            "pattern" => "App\Models\BotModel",
            "pattern_metod" => "createCliente"
        ],
            "pattern"
        ));
    }

    /**
     * Faz uma conta simples
     */
    public function conta()
    {
        $this->botman->reply($this->botModel->conta(
            (string)$this->botman->getMessage()->getExtras('text'),
            $this->nameUser)
        );
    }

    /**
     * Consulta de endereço pelo CEP
     */
    public function consultCEP()
    {
        $this->botman->startConversation(new ConversationController(
            'question', [
            "question" => "Qual o número do <b>CEP</b>?<br> ex.: 12345678",
            "name" => "cep",
            "fallback" => [],
            "tenant" => $this->tenant,
            "pattern" => "App\Models\BotModel",
            "pattern_metod" => "consultCEP"
        ],
            "pattern"
        ));
    }

    /**
     * Cálculo de frete e prazo de entrega
     */
    public function fretePrecoPrazo()
    {
        $p = "<img src='/public/assets/img/caixa.jpg' />";
        $c = "<img src='/public/assets/img/cilindro.jpg' />";
        $e = "<img src='/public/assets/img/envelope.jpg' />";

        $this->botman->startConversation(new ConversationController(
            'question', [
            "question" => "Qual a <b>Forma de Envio</b>?<br>Informe apenas o número:<br>1 - para PAC<br>2 - para SEDEX<br>3 - para SEDEX_10<br>4 - para SEDEX_HOJE<br>5 - para SEDEX_A_COBRAR<br>ex.: 2",
            "name" => "codigo",
            "fallback" => [
                "question" => "Qual o <b>Formato da Embalagem</b>?<br>Informe apenas o número:<br>1 - para {$p}<br>2 - para {$c}<br>3 - para {$e}<br> ex.: 1",
                "name" => "embalagem",
                "fallback" => [
                    "question" => "Qual o <b>CEP de Origem</b>?<br> ex.: 12345678",
                    "name" => "cep_origem",
                    "fallback" => [
                        "question" => "Qual o <b>CEP de Destino</b>?<br> ex.: 12345678",
                        "name" => "cep_destino",
                        "fallback" => [
                            "question" => "Qual o <b>Comprimento do Pacote</b> em centímentros?<br> ex.: 30",
                            "name" => "comprimento",
                            "fallback" => [
                                "question" => "Qual a <b>Altura do Pacote</b> em centímentros?<br> ex.: 30",
                                "name" => "altura",
                                "fallback" => [
                                    "question" => "Qual a <b>Largura do Pacote</b> em centímentros?<br> ex.: 30",
                                    "name" => "largura",
                                    "fallback" => [
                                        "question" => "Qual o <b>Diametro do Pacote</b> em centímentros?<br> ex.: 30",
                                        "name" => "diametro",
                                        "fallback" => [
                                            "question" => "Qual o <b>Peso do Pacote</b> em kg (quilograma)?<br> ex.: 0,3",
                                            "name" => "peso",
                                            "fallback" => [
                                                "question" => "Responda 'Sim' se os dados estiverem corretos ou 'Não' se estiverem errados!",
                                                "name" => "confirmation",
                                                "fallback" => ""
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "tenant" => $this->tenant,
            "pattern" => "App\Models\BotModel",
            "pattern_metod" => "fretePrecoPrazo"
        ],
            "pattern"
        ));
    }

    /**
     * Fazendo o rastreio de encomendas online
     */
    public function rastreamento()
    {
        $this->botman->startConversation(new ConversationController(
            'question', [
            "question" => "Qual o <b>Código de Rastreio</b>?<br> ex.: SQ458226057BR",
            "name" => "codRestreio",
            "fallback" => [],
            "tenant" => $this->tenant,
            "pattern" => "App\Models\BotModel",
            "pattern_metod" => "rastreamento"
        ],
            "pattern"
        ));
    }

    /**
     * Enviar imagem
     */
    public function sendImg()
    {
        $img = new UtilitiesController($this->botman);
        $img->img(
            'http://sisgesatec/public/uploads/uuid5e38d6cf31a78/2020/02/logo.png',
            "veja essa imagem",
            "Foto"
        );
    }

    /**
     * Enviar imagem
     */
    public function sendAudio()
    {
        $audio = new UtilitiesController($this->botman);
        $audio->audio(
            'http://sisgesatec/public/assets/audio/ola.mp3',
            "veja essa imagem"
        );
    }

    /**
     * Debugar teste
     */
    public function debug()
    {
        $this->botman->reply('Texto do user: ' . $this->botman->getMessage()->getExtras('text'));
        $this->botman->reply('Resposta: ' . $this->botman->getMessage()->getExtras('apiReply'));
        $this->botman->reply('Entidade: ' . $this->botman->getMessage()->getExtras('apiEntitie'));
        $this->botman->reply('Intenção: ' . $this->botman->getMessage()->getExtras('apiIntent'));
        $this->botman->reply('Precisão: ' . $this->botman->getMessage()->getExtras('apiAcuracy'));
    }
}