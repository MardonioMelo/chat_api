<?php

namespace App\View\Chatbot;

use App\View\DefaultView\DefaultView;

/**
 * Class para administrar view do chatbot
 */
class ChatbotWidgetView extends DefaultView
{
    
    /**
     * Inicia constantenses
     */
    public function __construct()
    {
        $this->setDirTpl();
    }

    /**
     * Método para povoar e retornar conteúdo da página
     *
     * @param array $data
     * @return string
     */
    public function tplView(array  $data = [1, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG]): string
    {
        $data['url'] = SERVER_CHAT_URL;
        $data_var = [
            "{{userid}}",
            "{{username}}",
            "{{userimg}}",
            "{{botname}}",
            "{{botimg}}",
            "{{url}}"
        ];      

        $this->setDataName($data_var);
        $this->setData($data);
        $this->setTplHtml("chatbot/index");  
        return $this->write();       
    }
}
