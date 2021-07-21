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
        $this->tab_chat_msg->chat_text = (string) trim(strip_tags($text));
        $this->tab_chat_msg->chat_drive = (string) $drive;
        $this->tab_chat_msg->chat_type = (string) $type;
        $this->tab_chat_msg->chat_attachment = (string)$attachment;

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
     * @param integer $user_id
     * @param integer $user_dest_id
     * @param string $dt_start = data e hora inicio
     * @param string $dt_end = data e hora fim
     * @return object
     */
    private function readHistory(int $user_id, int $user_dest_id, string $dt_start, string $dt_end, int $limit = 100, int $offset = 0)
    {

        $query_col = "(chat_user_id = :a AND chat_user_dest_id = :b OR chat_user_id = :c AND chat_user_dest_id = :d) AND chat_date BETWEEN :e AND :f";
        $query_value = "a={$user_id}&b={$user_dest_id}&c={$user_id}&d={$user_dest_id}&e={$dt_start}&f={$dt_end}";

        return $this->tab_chat_msg->find($query_col, $query_value)->limit($limit)->offset($offset)->fetch("client_id ASC");
    }
}
