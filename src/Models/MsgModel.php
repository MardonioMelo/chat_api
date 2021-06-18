<?php

namespace Src\Models;

use PHPUnit\Util\Json;
use Src\Models\DataBase\ChatMsg;

/**
 * Class responsável por gerenciar as mensagens do chat no banco de dados
 */
class  MsgModel
{

    private $tab_chat_msg;
    private $Error;
    private $Result;

    /**
     * Declara a classe ChatMsg na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_msg = new ChatMsg();
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
    public function saveMsg(Int $user_id, Int $user_dest_id, String $text, String $drive = "web", String $type = "text", String $attachment = null): void
    {
        $this->tab_chat_msg->chat_user_id = (int) $user_id;
        $this->tab_chat_msg->chat_user_dest_id = (int) $user_dest_id;
        $this->tab_chat_msg->chat_text = (string) $text;
        $this->tab_chat_msg->chat_drive = (string) $drive;
        $this->tab_chat_msg->chat_type = (string) $type;
        $this->tab_chat_msg->chat_attachment = (string)$attachment;    

        $this->saveCreate();
    }

    /**
     * Consultar Histórico de mensagens de um intervalo de tempo.
     *
     * @param integer $user_id
     * @param integer $user_dest_id
     * @param string $dt_start = data e hora inicio
     * @param string $dt_end = data e hora fim
     * @return object
     */
    public function readHistory(int $user_id, int $user_dest_id, string $dt_start, string $dt_end)
    {       
        $query_col = "(chat_user_id = :a AND chat_user_dest_id = :b OR chat_user_id = :c AND chat_user_dest_id = :d) AND chat_date BETWEEN :e AND :f";
        $query_value = "a={$user_id}&b={$user_dest_id}&c={$user_id}&d={$user_dest_id}&e={$dt_start}&f={$dt_end}";       
        $this->tab_chat_msg->readCol($query_col, $query_value);

        return $this->tab_chat_msg->getResult();
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
     * Organizar dados do histórico para envio  
     *
     * @param Object $obj
     * @return array
     */
    public function passeAllDataArrayHistory($obj): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['chat_user_id'] = $arr->data()->chat_user_id;
                $result[$key]['chat_user_dest_id'] = $arr->data()->chat_user_dest_id;
                $result[$key]['chat_text'] = $arr->data()->chat_text;
                $result[$key]['chat_type'] = $arr->data()->chat_type;             
                $result[$key]['chat_date'] = $arr->data()->chat_date;
                $result[$key]['chat_attachment'] = $arr->data()->chat_attachment;
            }
        }
        return $result;
    }

    /**
     * Salvar dados da mensagem no banco de dados
     *
     * @return string
     */
    private function saveCreate(): void
    {
        $this->tab_chat_msg->chat_date = date("Y-m-d h:i:s");
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
