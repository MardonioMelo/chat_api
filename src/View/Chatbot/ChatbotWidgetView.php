<?php

namespace Src\View\Chatbot;

use Src\View\DefaultView\DefaultView;

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
    public function tplView(array  $data = [USER_ID, USER_DEST_ID, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG]): string
    {
        $data['url'] = SERVER_CHAT_URL;
        $data_var = [
            "{{userid}}",
            "{{userdestid}}",
            "{{username}}",
            "{{userimg}}",
            "{{botname}}",
            "{{botimg}}",
            "{{url}}"
        ];      

        $this->setDataName($data_var);
        $this->setData($data);
        $this->setTplHtml("chatbot/index");  
        return $this->getWrite();       
    }
}
