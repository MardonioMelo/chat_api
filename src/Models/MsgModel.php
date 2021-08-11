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
     * Consultar Histórico de mensagens de um intervalo de tempo.
     * Informe o id do remetente, id do destinatário, data de inicio e fim da troca de mensagens.
     * Você pode definir o limit e o offset também
     *
     * @param array $data 
     * @param string $uri
     * @return void
     */
    public function getHistory(array $data, string $uri): void
    {
        $this->setParams($data);

        if ($this->getResult()) {

            $history = $this->readHistory(
                $this->params['ori'],
                $this->params['des'],
                $this->params['sta_usa'],
                $this->params['end_usa'],
                $this->params['limit'],
                $this->params['offset']
            );

            $count = $this->tab_chat_msg->find()->count();
            $other = "&ori=" . $this->params['ori'] . "&des=" . $this->params['des'] . "&sta=" . $this->params['sta'] . "&end=" . $this->params['end'];
            $links = UtilitiesModel::paginationLink(HOME . $uri, $this->params['limit'], $this->params['offset'], $count, $other);

            if ($history) {
                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data'] = $this->passeAllDataArrayHistory($history);
                $this->Error['count'] =  $count;
                $this->Error['next'] = $links['next'];
                $this->Error['previous'] = $links['previous'];
            } else {
                $this->Result = false;
                $this->Error['data'] = [];
                $this->Error['msg'] = "Não existem cadastros para os parâmetros informados!";
            }
        }
    }

    /**
     * Verificar e validar os parâmetros
     *
     * @param array $data  
     * @return void
     */
    public function setParams(array $data)
    {
        if (!empty($data['ori']) && !empty($data['des']) && !empty($data['sta']) && !empty($data['end'])) {

            $sta = UtilitiesModel::validDateBrForUSA($data['sta']);
            $end = UtilitiesModel::validDateBrForUSA($data['end']);

            if ($sta && $end) {
                $this->params['ori'] = (int) $data['ori'];
                $this->params['des'] = (int) $data['des'];
                $this->params['sta'] = $data['sta'];
                $this->params['end'] = $data['end'];
                $this->params['sta_usa'] = $sta;
                $this->params['end_usa'] = $end;
                $this->params['limit'] = empty($data['limit']) ? 100 : (int) $data['limit'];
                $this->params['offset'] = empty($data['offset']) ? 0 : (int) $data['offset'];
                $this->Result = true;
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! Data invalida, informe uma data válida.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe todos os parâmetros obrigatórios.";
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
     * @return array
     */
    public function passeAllDataArrayHistory($obj): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['call'] = $arr->data()->chat_call_id;
                $result[$key]['origin'] = $arr->data()->chat_user_uuid;
                $result[$key]['destiny'] = $arr->data()->chat_user_dest_uuid;
                $result[$key]['text'] = $arr->data()->chat_text;
                $result[$key]['type'] = $arr->data()->chat_type;
                $result[$key]['date'] = $arr->data()->chat_date;
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


    /**
     * Consultar Histórico de mensagens de um intervalo de tempo.
     *   
     * @param string $user_uuid
     * @param string $user_dest_uuid
     * @param string $dt_start
     * @param string $dt_end
     * @param int $limit
     * @param int $offset
     * @param int $call_id
     * @return void
     */
    private function readHistory(string $user_uuid, string $user_dest_uuid, string $dt_start, string $dt_end, int $limit = 100, int $offset = 0, int $call_id = 0)
    {
        $query_col = "(chat_user_uuid = :a AND chat_user_dest_uuid = :b OR chat_user_uuid = :c AND chat_user_dest_uuid = :d) AND chat_date BETWEEN :e AND :f";
        $query_value = "a={$user_uuid}&b={$user_dest_uuid}&c={$user_uuid}&d={$user_dest_uuid}&e={$dt_start}&f={$dt_end}";

        if ($call_id !== 0) {
            $query_col .= " AND chat_call_id = :g ";
            $query_value .= "&g={$call_id}";
        }

        return $this->tab_chat_msg->find($query_col, $query_value)->limit($limit)->offset($offset)->fetch("chat_id ASC");
    }
}
