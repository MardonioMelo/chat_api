<?php

namespace App\View\PainelChat;

use App\View\DefaultView\DefaultView;

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
    public function tplPainelView(array $data): string
    {
        $name = [
            "{{title}}"   
        ];

        $this->setDataName($name);
        $this->setData($data);
        $this->setTplHtml("painelchat/painel");  
        return $this->write();       
    }

}
