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
     * @return void
     */
    public function callCreate(array $data, string $cmd): void
    {
        $this->tab_chat_call = new ChatCall();

        $this->checkInputsCreate(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_call->call_id = "";
            $this->tab_chat_call->call_client_uuid =  $this->inputs['client_uuid'];
            $this->tab_chat_call->call_objective = $this->inputs['objective'];
            $this->saveData();
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Cancelar atendimento.
     *
     * @param Array $params ["call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callCancel(array $data, string $cmd): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if (!empty($data['call'])) {
            $this->tab_chat_call->call_id = (int) $data['call'];
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
     * @param Array $params ["call" => ""]   
     * @param string $cmd
     * @param string $type_autor
     * @param string $attendant_uuid
     * @return void
     */
    public function callStart(array $data, string $cmd, string $type_autor, string $attendant_uuid): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);       

        if ($type_autor == "attendant") {
            if (!empty($data['call'])) {
                
                $this->tab_chat_call->call_id = (int) $data['call'];
                $this->tab_chat_call->call_attendant_uuid = $attendant_uuid;
                $this->tab_chat_call->call_start = date("Y-m-d H:i:s");
                $this->tab_chat_call->call_status = 2;
                $this->saveData();

                $get_call = $this->getCall($this->tab_chat_call->call_id);
                $this->Error['data']['client_uuid'] =  $get_call->call_client_uuid;
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Você não tem permissão para executar essa ação.";
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Finalizar atendimento.
     *
     * @param Array $params ["call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callEnd(array $data, string $cmd, string $type_autor): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if ($type_autor == "attendant") {
            if (!empty($data['call'])) {
                $this->tab_chat_call->call_id = (int) $data['call'];                
                $this->tab_chat_call->call_end = date("Y-m-d H:i:s");
                $this->tab_chat_call->call_status = 3;
                $this->saveData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Você não tem permissão para executar essa ação.";
        }
        $this->Error['data']['cmd'] = $cmd;
    }

    /**
     * Avaliação do atendimento.
     *
     * @param Array $params ["call" => ""]   
     * @param string $cmd
     * @return void
     */
    public function callEvaluation(array $data, string $cmd, string $type_autor): void
    {
        $this->tab_chat_call = new ChatCall();
        $data = UtilitiesModel::filterParams($data);

        if ($type_autor == "client") {
            if (!empty($data['call']) && !empty($data['evaluation'])) {
                $this->tab_chat_call->call_id = (int) $data['call'];   
                $this->tab_chat_call->call_evaluation = (int) $data['evaluation'];                       
                $this->saveData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! Informe os campos obrigatórios para salvar.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Você não tem permissão para executar essa ação.";
        }
        $this->Error['data']['cmd'] = $cmd;
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
     * Consultar dados de uma call pelo ID
     *
     * @param integer $uuid
     * @return null|Object
     */
    public function getCall(int $id)
    {
        if ($this->tab_chat_call->findById($id)) {
            return $this->tab_chat_call->findById($id)->data();
        } else {
            return false;
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
