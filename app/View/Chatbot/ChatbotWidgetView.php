<?php

namespace App\View\Chatbot;

/**
 * Class para administrar view do chatbot
 */
class ChatbotWidgetView
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
    public function tplView(array  $data = [1, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG]): string
    {
        $data_var = [
            "{{userid}}",
            "{{username}}",
            "{{userimg}}",
            "{{botname}}",
            "{{botimg}}"
        ];
        $tpl = file_get_contents($this->dirtlp . "/chatbot/index.html");
        return str_replace($data_var, $data, $tpl);
    }
}
