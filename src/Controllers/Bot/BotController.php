<?php

namespace Src\Controllers\Bot;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;
use BotMan\BotMan\Cache\SymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Src\View\Chatbot\ChatbotWidgetView;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


/**
 * Class BotController
 * @package App\Controllers\Bot
 */
class BotController
{
    private $config;
    /** @var BotMan */
    protected $botman;
    /** @var CustomMiddleware */
    protected $middleware;

    /**
     * BotController constructor.
     */
    public function __construct()
    {
        // $this->authentication();

        DriverManager::loadDriver(WebDriver::class);
        $this->setConfigWeb();
        $cache = new FilesystemAdapter();
        $this->botman = BotManFactory::create($this->config, new SymfonyCache($cache));
        $this->middleware = new CustomMiddleware();
        $this->botman->middleware->received($this->middleware);
        $this->botman->middleware->captured($this->middleware);
        
    }

    /*
    * ***************************************
    * **********       WEB         **********
    * ***************************************
    */

    /**
     * Widget do chat do bot
     */
    public function widget(Request $request, Response $response, array $args)
    {     
        $chatbotWidget = new ChatbotWidgetView();   
        $payload = $chatbotWidget->tplView();
        $response->getBody()->write($payload);
        return $response;
    }


    /*
    * ***************************************
    * **********       API         **********
    * ***************************************
    */

    /**
     * API
     */
    public function chatBot(Request $request, Response $response, array $args)
    {
        //NLP
        $this->botman->hears('nlp', 'App\Controllers\Bot\CommandController@setCommand');

        //Parar conversa
        $this->botman->hears('stop|cancelar', function (BotMan $bot) {
            $bot->reply('Cancelado!');
        })->stopsConversation();

        //Msg padrão
        $this->botman->fallback(function (BotMan $bot) {
            $bot->reply('Desculpe, não entendi esse comando.');
        });

        //Escuta ou responde
        $this->botman->listen();

        //Executa Slim F.
        $response->getBody();
        return $response;
    }


    /*
    * ***************************************
    * **********  PRIVATE METHODS  **********
    * ***************************************
    */

    /**
     * Configurações para web
     */
    private function setConfigWeb()
    {
        $this->config = [
            'matchingData' => [
                'driver' => 'web',
            ],
            'botman' => [
                'conversation_cache_time' => 60
            ]
        ];
    }    
}
