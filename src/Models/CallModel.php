<?php

namespace Src\Models;

use Src\Models\DataBase\ChatCall;

/**
 * Class responsável por gerenciar os registro de atendimento no banco de dados
 */
class  CallModel
{

    private $tab_chat_call;
    private $Error;
    private $Result;

    /**
     * Declara a classe ChatCall na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_call = new ChatCall();
    }

    /**
     * Salva dados do atendimento no banco de dados
     *
     * @param String $user_id 
     * @param String $objective
     * @param Int $status  
     * @return void
     */
    public function createCall(string $user_uuid, String $objective, Int $status = 1): void
    {
        $this->tab_chat_call->call_user_uuid = (int) $user_uuid;   
        $this->tab_chat_call->call_objective = (string) $objective;
        $this->setStatus($status); 

        $this->saveCreate();
    }

    /**
     * Salva dados do atendimento no banco de dados
     *
     * @param Int $user_id
     * @param Int $user_dest_id
     * @param String $objective
     * @param Int $status
     * @param String $date_start
     * @return void
     */
    public function updateCall(Int $user_id, Int $user_dest_id, String $objective, Int $status = 1, String $date_start): void
    {
        $this->tab_chat_call->call_user_id = (int) $user_id;
        $this->tab_chat_call->call_user_dest_id = (int) $user_dest_id;
        $this->tab_chat_call->call_objective = (string) $objective;
        $this->setStatus($status);
        $this->tab_chat_call->call_date_start = (string) $date_start;

        $this->saveCreate();
    }

    /**
     * Set status do atendimento.
     *
     * @param integer $status
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->tab_chat_call->call_status = (int) $status;
    }

    /**
     * Set data e hora UTC do inicio do atendimento.
     *
     * @param string $date
     * @return void
     */
    public function setDateStart(string $date): void
    {
        $this->tab_chat_call->call_date_start = (string) $date;
    }

    /**
     * Set data e hora UTC do fim do atendimento.
     *
     * @param string $date
     * @return void
     */
    public function setDateEnd(string $date): void
    {
        $this->tab_chat_call->call_date_end = (string) $date;
    }

    /**
     * Set nota de avaliação do chamado.
     *
     * @param string $note
     * @return void
     */
    public function setEvaluation(string $note): void
    {
        $this->tab_chat_call->call_evaluation = (string) $note;
    }


    /**
     * Consultar Histórico de atendimentos de um intervalo de tempo.
     *
     * @param integer $user_id
     * @param integer $user_dest_id
     * @param string $dt_start = data e hora inicio
     * @param string $dt_end = data e hora fim
     * @return object
     */
    public function readHistory(int $user_id, int $user_dest_id, string $dt_start, string $dt_end)
    {
        $query_col = "(call_user_id = :a AND call_user_dest_id = :b OR call_user_id = :c AND call_user_dest_id = :d) AND chat_date_start BETWEEN :e AND :f";
        $query_value = "a={$user_id}&b={$user_dest_id}&c={$user_id}&d={$user_dest_id}&e={$dt_start}&f={$dt_end}";
        $this->tab_chat_call->readCol($query_col, $query_value);

        return $this->tab_chat_call->getResult();
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
                $result[$key]['call_id'] = $arr->data()->call_id;
                $result[$key]['call_user_id'] = $arr->data()->call_user_id;
                $result[$key]['call_user_dest_id'] = $arr->data()->call_user_dest_id;
                $result[$key]['call_objective'] = $arr->data()->call_objective;
                $result[$key]['call_status'] = $arr->data()->call_status;
                $result[$key]['call_date_start'] = $arr->data()->call_date_start;
                $result[$key]['call_date_end'] = $arr->data()->call_date_end;
                $result[$key]['call_evaluation'] = $arr->data()->call_evaluation;
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
        $id = $this->tab_chat_call->save();

        if ((int)$id > 0) {
            $this->Result = $id;
            $this->Error = "Cadastro realizado com sucesso!";
        } else {
            $this->Result = $id;
            $this->Error = $this->tab_chat_call->fail()->getMessage();
        }
    }
}
