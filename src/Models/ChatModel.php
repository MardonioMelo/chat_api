<?php

namespace Src\Models;

use Src\Models\DataBase\AppChat;

/**
 * Class responsável por gerenciar as mensagens do chat no banco de dados
 */
class  ChatModel
{

    private $tab_app_chat;
    private $Error;
    private $Result;

    /**
     * Declara a classe AppChat na inicialização
     */
    public function __construct()
    {
        $this->tab_app_chat = new AppChat();
    }

    /**
     * Salva mensagem do chat no banco de dados
     *
     * @param Int $user_id
     * @param Int $user_dest_id
     * @param String $text
     * @param String $drive
     * @param String $chat_type
     * @param String $attachment
     * @return void
     */
    public function saveMsg(Int $user_id, Int $user_dest_id, String $text, String $drive = "web", String $chat_type = "text", String $attachment = null): void
    {
        $this->tab_app_chat->chat_user_id = (int) $user_id;
        $this->tab_app_chat->chat_user_dest_id = (int) $user_dest_id;
        $this->tab_app_chat->chat_text = (string) $text;
        $this->tab_app_chat->chat_drive = (string) $drive;
        $this->tab_app_chat->chat_chat_type = (string) $chat_type;
        $this->tab_app_chat->chat_attachment = (string)$attachment;

        $this->saveCreate();
    }

    /**
     * <b>Verificar Ação:</b> Retorna TRUE se ação for efetuada ou FALSE se não. Para verificar erros
     * execute um getError();
     * @return bool|int $Var = True(com o id) or False
     */
    public function getResult(): bool
    {
        return $this->Result;
    }

    /**
     * <b>Obter Erro:</b> Retorna um string com o erro.
     * @return string $Error = String com o erro
     */
    public function getError(): string
    {
        return $this->Error;
    }

    /**
     * Salvar dados da mensagem no banco de dados
     *
     * @return string
     */
    private function saveCreate(): void
    {
        $this->tab_app_chat->chat_date = date("Y:m:d h:i:s");
        $id = $this->tab_app_chat->save();

        if ($id > 0) {
            $this->Result = $id;
            $this->Error = "Cadastro realizado com sucesso!";
        } else {
            $this->Result = false;
            $this->Error = $this->tab_app_chat->fail();
        }
    }
}
