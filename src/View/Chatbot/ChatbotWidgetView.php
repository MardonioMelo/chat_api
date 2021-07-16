<?php

namespace Src\View\Chatbot;

use Src\View\DefaultView\DefaultView;

use const Ratchet\VERSION;

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
     *  URL para abrir conexão (substitua o user_id pelo id do usuário): ws://localhost:8081/user_id
     *  Exemplar da estrutura dos dados para envio das mensagens:
     *   data : {
     *      "driver": "web",
     *      "userId": 2,
     *      "userDestId": 3,
     *      "text": "ola",
     *      "type": "text",
     *      "time": "10:30",
     *      "attachment":null
     *    }
     *
     * @param array $data
     * @return string
     */
    public function tplView(array  $data = [USER_ID, USER_DEST_ID, USER_NAME, USER_IMG, BOT_NAME, BOT_IMG]): string
    {
        $data['url'] = SERVER_CHAT_URL . '/' . USER_ID;
        $data['home'] = HOME . '/';
        $data_var = [
            "{{userid}}",
            "{{userdestid}}",
            "{{username}}",
            "{{userimg}}",
            "{{botname}}",
            "{{botimg}}",
            "{{url}}",
            "{{home}}"
        ];

        $this->setDataName($data_var);
        $this->setData($data);
        $this->setTplHtml("chatbot/index");
        return $this->getWrite();
    }
}
