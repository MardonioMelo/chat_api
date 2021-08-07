<?php

namespace Src\Models;

use Src\Models\ClientModel;
use Src\Models\UtilitiesModel;
use Src\Models\DataBase\ChatCall;

/**
 * Class responsável por gerenciar os registro de atendimento no banco de dados
 */
class  CallModel
{

    private $tab_chat_call;
    private $Error;
    private $Result;
    private $inputs;
    private $client_model;   


    /**
     * Salva dados do atendimento no banco de dados
     *
     * @param Array $data ["client_uuid" => "", "objective" => ""]  
     * @param string $cmd
     * @param string $user_uuid = uuid do autor
     * @return void
     */
    public function callCreate(array $data, string $cmd, string $user_uuid): void
    {
        $this->tab_chat_call = new ChatCall();

        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {            
            $this->tab_chat_call->call_id = "";
            $this->tab_chat_call->call_client_uuid = $this->inputs['client_uuid'];
            $this->tab_chat_call->call_objective = $this->inputs['objective'];           
            $this->saveData();
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Cancelar atendimento.
     *
      * @param Array $params ["client_uuid" => "", "call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callCancel(array $data, string $cmd): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if (!empty($data['call']) && !empty($data['client_uuid'])) {
            $this->tab_chat_call->call_id = (int) $data['call'];
            $this->tab_chat_call->call_client_uuid = $data['client_uuid'];
            $this->tab_chat_call->call_status = 4;
            $this->saveData();
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Iniciar atendimento.
     *
      * @param Array $params ["client_uuid" => "", "call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callStart(array $data, string $cmd): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if (!empty($data['call']) && !empty($data['client_uuid']) && !empty($data['user_dest_uuid'])) {
            $this->tab_chat_call->call_id = (int) $data['call'];
            $this->tab_chat_call->call_client = $data['client_uuid'];
            $this->tab_chat_call->call_user_dest_uuid = $data['user_dest_uuid'];
            $this->tab_chat_call->call_start = date("Y-m-d H:i:s");
            $this->tab_chat_call->call_status = 2;
            $this->saveData();
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
        }
        $this->Error['data']['cmd'] = $cmd;
    }

     /**
     * Finalizar atendimento.
     *
      * @param Array $params ["client_uuid" => "", "call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callEnd(array $data, string $cmd): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if (!empty($data['call']) && !empty($data['client_uuid'])) {
            $this->tab_chat_call->call_id = (int) $data['call'];
            $this->tab_chat_call->call_client_uuid = $data['client_uuid'];
            $this->tab_chat_call->call_end = date("Y-m-d H:i:s");
            $this->tab_chat_call->call_status = 3;
            $this->saveData();
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Registrar avaliação da call
     *  
     * @param Array $params ["client_uuid" => ""]
     * @return void
     */
    public function avalCall($data): void
    {
        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_call->call_id = $data['id'];
            $this->tab_chat_call->call_evaluation = $this->inputs['call_evaluation'];
            $this->saveData();
        }
    }

    /**
     * Verificar e validar os dados para cadastro
     *
     * @param array $data   
     * @return void
     */
    public function checkInputsCreate(array $data)
    {
        if (!empty($data['client_uuid']) && !empty($data['objective'])) {

            $this->client_model = new ClientModel();
            if ($this->client_model->getUserUUID($data['client_uuid'])) {
                $this->inputs['client_uuid'] = $data['client_uuid'];
                $this->inputs['objective'] = $data['objective'];
                $this->Result = true;
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! O UUID do cliente informado não existe.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para realizar o cadastro.";
        }
    }

    /**
     * Consultar Histórico de atendimentos de um intervalo de tempo.
     *
     * @param integer $client_uuid
     * @param integer $user_dest_uuid
     * @param string $dt_start = data e hora inicio
     * @param string $dt_end = data e hora fim
     * @return object
     */
    public function readHistory(int $client_uuid, int $user_dest_uuid, string $dt_start, string $dt_end)
    {
        $query_col = "(call_client_uuid = :a AND call_user_dest_uuid = :b OR call_client_uuid = :c AND call_user_dest_uuid = :d) AND chat_date_start BETWEEN :e AND :f";
        $query_value = "a={$client_uuid}&b={$user_dest_uuid}&c={$client_uuid}&d={$user_dest_uuid}&e={$dt_start}&f={$dt_end}";
        $this->tab_chat_call->readCol($query_col, $query_value);

        return $this->tab_chat_call->getResult();
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
                $result[$key]['call_id'] = $arr->data()->call_id;
                $result[$key]['call_client_uuid'] = $arr->data()->call_client_uuid;
                $result[$key]['call_user_dest_uuid'] = $arr->data()->call_user_dest_uuid;
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
     * Salvar dados no banco de dados
     *
     * @return string
     */
    private function saveData(): void
    {
        $this->tab_chat_call->call_update = date("Y-m-d H:i:s");
        $result = $this->tab_chat_call->save();

        if ($result) {
            $this->Result = true;
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['id'] = $this->tab_chat_call->call_id;
        } else {
            $this->Result = false;
            $this->Error['msg'] = $this->tab_chat_call->fail()->getMessage();
            $this->Error['data'] = null;
        }
    }
}
