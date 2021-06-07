<?php

namespace Src\Controllers\Home;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Models\BotModel;
use Src\View\PainelChat\PainelChatView;

/**
 * Classe controller principal da API
 */
class Dashboard
{

    private $BotModel;

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
        $payload = $this->PainelChatView->tplPainelView(["Painel de Chat"]);
        $response->getBody()->write($payload);
        return $response;
    }

    /**
     * Paragrafo
     *
     * @param string $text
     * @return string
     */
    public function printContent(string $text): string
    {
        return "<p>{$text}</p>";
    }

    /**
     * Cabeçalho da página
     *
     * @param string $text
     * @return string
     */
    public function printHead(string $text): string
    {
        return "<p>{$text}</p>";
    }

    /**
     * Corpo da página
     *
     * @param string $text
     * @return string
     */
    public function printBody(string $text): string
    {
        return "<p>{$text}</p>";
    }

    /**
     * Rodapé da página
     *
     * @param string $text
     * @return string
     */
    public function printFooter(string $text): string
    {
        return "<p>{$text}</p>";
    }
}
