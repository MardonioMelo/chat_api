<?php

namespace Src\Models;

use Src\Models\DataBase\ChatMsg;

/**
 * Class responsável por gerenciar as mensagens do chat no banco de dados
 */
class  MsgModel
{

    private $tab_chat_msg;
    private $Error;
    private $Result;
    private $params;

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
     * @param Int $call
     * @param String $text
     * @param String $user_id
     * @param String $user_dest_id   
     * @param String $drive
     * @param String $chat_type
     * @param String $attachment
     * @return void
     */
    public function saveMsgCall(int $call, String $text, string $user_uuid = "", string $user_dest_uuid = "", String $drive = "web", String $type = "text", String $attachment = null): void
    {
        $this->tab_chat_msg->chat_call_id = $call;
        $this->tab_chat_msg->chat_user_uuid = $user_uuid;
        $this->tab_chat_msg->chat_user_dest_uuid = $user_dest_uuid;
        $this->tab_chat_msg->chat_text = trim(strip_tags($text));
        $this->tab_chat_msg->chat_drive = $drive;
        $this->tab_chat_msg->chat_type = $type;
        $this->tab_chat_msg->chat_attachment = $attachment;

        $this->saveCreate();
    }

    /**
     * Consultar todos os cadastros conforme parâmetros passado no find
     *
     * @param string $find_name
     * @param string $find_value
     * @param integer $limit
     * @param integer $offset
     * @param string $uri    
     * @param bool $formatted
     * @return void
     */
    public function readAllMsgFind(string $find_name, string $find_value, int $limit = 10, int $offset = 0, $uri = false, bool $formatted = true): void
    {
        $this->tab_chat_msg = new ChatMsg();

        if ($limit == 0) {
            $this->Result = false;
            $this->Error['msg'] = "O limite deve ser maior que 0 (zero), tente novamente!";
        } else {

            $msgs = $this->tab_chat_msg->find($find_name, $find_value)->limit($limit)->offset($offset)->fetch("chat_id ASC");

            if ($msgs) {              

                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data']['chat'] = $formatted ? $this->passeAllDataArray($msgs, $uri) : $msgs;

                if ($uri) {
                    $count = $this->tab_chat_msg->find($find_name, $find_value)->count();
                    $links = UtilitiesModel::paginationLink(HOME . $uri, $limit, $offset, $count);                   
                   
                    $this->Error['data']['count'] =  $count;
                    $this->Error['data']['next'] = $links['next'];
                    $this->Error['data']['previous'] = $links['previous'];
                }
            } else {
                $msgs = $this->tab_chat_msg->find($find_name, $find_value)->limit(10)->offset(0)->fetch("call_id ASC");

                if ($msgs) {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros no limite e deslocamento informados, tente outra margem de consulta!";
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existe histórico de mensagens!";
                }
            }
        }
    }

    /**
     * Verificar Ação
     * 
     * @return bool 
     */
    public function getResult(): bool
    {
        return $this->Result;
    }

    /**
     * Obter Erro
     * 
     * @return array|Object 
     */
    public function getError()
    {
        return $this->Error;
    }

    /**
     * Organizar dados do histórico para envio  
     *
     * @param Object $obj
     * @param null|string $uri
     * @return array
     */
    public function passeAllDataArray($obj, $uri = null): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['id'] = $arr->data()->chat_id;
                $result[$key]['call'] = $arr->data()->chat_call_id;
                $result[$key]['origin'] = $arr->data()->chat_user_uuid;
                $result[$key]['destiny'] = $arr->data()->chat_user_dest_uuid;
                $result[$key]['text'] = $arr->data()->chat_text;
                $result[$key]['type'] = $arr->data()->chat_type;
                $result[$key]['date'] = date("d/m/Y H:i:s", strtotime($arr->data()->chat_date));
                if ($uri) {
                    $result[$key]['url'] = HOME . $uri . '/' .  $arr->data()->chat_id;
                }
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
