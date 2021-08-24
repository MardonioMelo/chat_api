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
        $data = UtilitiesModel::filterParams($data);

        if (!empty($data['client_uuid']) && !empty($data['objective'])) {
            $this->client_model = new ClientModel();

            if ($this->client_model->getUserUUID($data['client_uuid'])) {
                $this->tab_chat_call->call_id = "";
                $this->tab_chat_call->call_client_uuid =  $data['client_uuid'];
                $this->tab_chat_call->call_objective = $data['objective'];
                $this->saveData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! O UUID do cliente informado não existe.";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para realizar o cadastro.";
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
            $get_call = $this->getCall((int)$data['call']);

            if ($get_call) {
                $this->tab_chat_call->call_id = (int) $data['call'];
                $this->tab_chat_call->call_status = 4;
                $this->saveData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "O número da call informada não existe ou foi excluída!";
            }
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
                $get_call = $this->getCall((int)$data['call']);

                if ($get_call) {
                    $this->tab_chat_call->call_id = (int) $data['call'];
                    $this->tab_chat_call->call_attendant_uuid = $attendant_uuid;
                    $this->tab_chat_call->call_start = date("Y-m-d H:i:s");
                    $this->tab_chat_call->call_status = 2;
                    $this->saveData();
                    $this->Error['data']['client_uuid'] =  $get_call->call_client_uuid;
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "O número da call informada não existe ou foi excluída!";
                }
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
                $get_call = $this->getCall((int)$data['call']);

                if ($get_call) {
                    $this->tab_chat_call->call_id = (int) $data['call'];
                    $this->tab_chat_call->call_end = date("Y-m-d H:i:s");
                    $this->tab_chat_call->call_status = 3;
                    $this->saveData();
                    $this->Error['data']['client_uuid'] =  $get_call->call_client_uuid;
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "O número da call informada não existe ou foi excluída!";
                }
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
                $get_call = $this->getCall((int)$data['call']);

                if ($get_call) {
                    $this->tab_chat_call->call_id = (int) $data['call'];
                    $this->tab_chat_call->call_evaluation = (int) $data['evaluation'];
                    $this->saveData();
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "O número da call informada não existe ou foi excluída!";
                }
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
    public function readAllCallFind(string $find_name, string $find_value, int $limit = 10, int $offset = 0, $uri = "", bool $formatted = true): void
    {
        $this->tab_chat_call = new ChatCall();

        if ($limit == 0) {
            $this->Result = false;
            $this->Error['msg'] = "O limite deve ser maior que 0 (zero), tente novamente!";
        } else {          

            $calls = $this->tab_chat_call->find($find_name, $find_value)->limit($limit)->offset($offset)->fetch("call_id ASC");

            if ($calls) {

                $count = $this->tab_chat_call->find($find_name, $find_value)->count();
                $links = UtilitiesModel::paginationLink(HOME . $uri, $limit, $offset, $count);

                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data'] = $formatted ? $this->passeAllDataArray($calls, HOME . $uri) : $calls;
                $this->Error['count'] =  $count;
                $this->Error['next'] = $links['next'];
                $this->Error['previous'] = $links['previous'];
            } else {
                $calls = $this->tab_chat_call->find($find_name, $find_value)->limit(10)->offset(0)->fetch("call_id ASC");

                if ($calls) {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros no limite e deslocamento informados, tente outra margem de consulta!";
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros para os parâmetros informados!";
                }
            }
        }
    }

    /**
     * Organizar dados para envio  
     *
     * @param Object $obj
     * @param null|string $url
     * @return array
     */
    public function passeAllDataArray($obj, $url = null): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['call'] = $arr->data()->call_id;
                $result[$key]['client_uuid'] = $arr->data()->call_client_uuid;
                $result[$key]['attendant_uuid'] = $arr->data()->call_attendant_uuid;
                $result[$key]['objective'] = $arr->data()->call_objective;
                $result[$key]['status'] = $arr->data()->call_status;
                $result[$key]['start'] = date("d/m/Y H:i:s", strtotime($arr->data()->call_start));
                $result[$key]['end'] = date("d/m/Y H:i:s", strtotime($arr->data()->call_end));
                $result[$key]['evaluation'] = $arr->data()->call_evaluation;
                $result[$key]['update'] = date("d/m/Y H:i:s", strtotime($arr->data()->call_update));
                if ($url) {
                    $result[$key]['url'] = $url . '/' .  $arr->data()->call_id;
                }
            }
        }
        return $result;
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
            $this->Error = [];
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['call'] = $this->tab_chat_call->call_id;
        } else {
            $this->Result = false;
            $this->Error = [];
            $this->Error['msg'] = $this->tab_chat_call->fail()->getMessage();
            $this->Error['data'] = null;
        }
    }
}
