<?php

namespace App\View\PainelChat;

/**
 * Class para administrar view do chatbot
 */
class PainelChatView
{
    private $dirtlp;

    /**
     * Inicia constantenses
     */
    public function __construct()
    {
        $this->dirtlp = "../app/resources";
    }

    /**
     * Método para povoar e retornar conteúdo da página
     *
     * @param array $data
     * @return string
     */
    public function tplPainelView(array  $data): string
    {
        $data_var = [
            "{{title}}"   
        ];
        $tpl = file_get_contents($this->dirtlp . "/painelchat/painel.html");
        return str_replace($data_var, $data, $tpl);
    }




}
