<?php

namespace Src\View\PainelChat;

use Src\View\DefaultView\DefaultView;

/**
 * Class para administrar view do chatbot
 */
class PainelChatView extends DefaultView
{  
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
    public function tplPainelView(array $data = []): string
    {
        $data = ["Painel de Chat", 1, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG];
        $name = [
            "{{title}}",
            "{{userid}}",
            "{{username}}",
            "{{userimg}}",
            "{{botname}}",
            "{{botimg}}",
            "{{url}}"
        ];

        $this->setDataName($name);
        $this->setData($data);
        $this->setTplHtml("painelchat/painel");  
        return $this->getWrite();       
    }

}
