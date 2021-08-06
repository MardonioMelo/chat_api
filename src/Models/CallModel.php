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
     * Declara a classe ChatCall na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_call = new ChatCall();
        $this->client_model = new ClientModel();
    }

    /**
     * Salva dados do atendimento no banco de dados
     *
     * @param Array $data ["user_uuid" => "", "objective" => ""]
     * @param string $cmd 
     * @return void
     */
    public function createCall($data, $cmd): void
    {
        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_call->call_id = "";
            $this->tab_chat_call->call_user_uuid = $this->inputs['user_uuid'];
            $this->tab_chat_call->call_objective = $this->inputs['objective'];            
            $this->saveData();            
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Registrar o inicio da call
     *  
     * @param Array $params ["user_uuid" => ""]
     * @return void
     */
    public function startCall($data): void
    {
        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_call->call_id = $data[''];
            $this->tab_chat_call->call_user_dest_uuid = $this->inputs['call_user_dest_uuid'];          
            $this->tab_chat_call->call_date_start = $this->inputs['call_date_start'];                 
            $this->saveData();
        }
    }

    /**
     * Registrar o fim da call
     *  
     * @param Array $params ["user_uuid" => ""]
     * @return void
     */
    public function endCall($data): void
    {
        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_call->call_id = $data[''];
            $this->tab_chat_call->call_user_dest_uuid = $this->inputs['call_user_dest_uuid'];
            $this->tab_chat_call->call_status = $this->inputs['call_status'];         
            $this->tab_chat_call->call_date_end = $this->inputs['call_date_end'];         
            $this->saveData();
        }
    }

    /**
     * Registrar avaliação da call
     *  
     * @param Array $params ["user_uuid" => ""]
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
     * Atualizar status do atendimento.
     *
     * @param array $data
     * @return void
     */
    public function updateStatus(array $data): void
    {
        if (!empty($data['id']) && !empty($data['status'])) {
            $this->tab_chat_call->call_id = (int) $data['id'];
            $this->tab_chat_call->call_status = (int) $data['status'];
            $this->saveData();
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
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
        if (!empty($data['user_uuid']) && !empty($data['objective'])) {

            if ($this->client_model->getUserUUID($data['user_uuid'])) {
                $this->inputs['user_uuid'] = $data['user_uuid'];
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
     * @param integer $user_uuid
     * @param integer $user_dest_uuid
     * @param string $dt_start = data e hora inicio
     * @param string $dt_end = data e hora fim
     * @return object
     */
    public function readHistory(int $user_uuid, int $user_dest_uuid, string $dt_start, string $dt_end)
    {
        $query_col = "(call_user_uuid = :a AND call_user_dest_uuid = :b OR call_user_uuid = :c AND call_user_dest_uuid = :d) AND chat_date_start BETWEEN :e AND :f";
        $query_value = "a={$user_uuid}&b={$user_dest_uuid}&c={$user_uuid}&d={$user_dest_uuid}&e={$dt_start}&f={$dt_end}";
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
                $result[$key]['call_user_uuid'] = $arr->data()->call_user_uuid;
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
