<?php

namespace Src\Models;

use Src\Models\DataBase\ChatAttendant;

/**
 * Class responsável por gerenciar os atendentes do chat no banco de dados
 */
class  AttendantModel
{

    private $tab_chat_attendant;
    private $Error;
    private $Result;

    /**
     * Declara a classe ChatMsg na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_attendant = new ChatAttendant();
    }

    /**
     * Salva mensagem do chat no banco de dados
     * 
     * Parei aqui..........
     *
     * @param Int $attendant_id  
     * @param String $attendant_name
     * @param String $attendant_lastname
     * @param String $attendant_avatar
     * @return void
     */
    public function saveMsg(Int $user_id, Int $user_dest_id, String $text, String $drive = "web", String $type = "text", String $attachment = null): void
    {
        $this->tab_chat_msg->chat_user_id = (int) $user_id;
        $this->tab_chat_msg->chat_user_dest_id = (int) $user_dest_id;
        $this->tab_chat_msg->chat_text = (string) trim(strip_tags($text));
        $this->tab_chat_msg->chat_drive = (string) $drive;
        $this->tab_chat_msg->chat_type = (string) $type;
        $this->tab_chat_msg->chat_attachment = (string)$attachment;    

        $this->saveCreate();
    }

    /**
     * <b>Verificar Ação:</b> Retorna TRUE se ação for efetuada ou FALSE se não. Para verificar erros
     * execute um getError();
     * @return string $Var = True(com o id) or False
     */
    public function getResult(): string
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
        $this->tab_chat_msg->chat_date = date("Y-m-d H:i:s");
        $id = $this->tab_chat_msg->save();

        if ((int)$id > 0) {
            $this->Result = $id;
            $this->Error = "Cadastro realizado com sucesso!";
        } else {
            $this->Result = $id;
            $this->Error = $this->tab_chat_msg->fail()->getMessage();
        }
    }
    
}
