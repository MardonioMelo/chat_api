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
