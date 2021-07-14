<?php

namespace Src\Models;

use Src\Models\UUIDModel;
use Src\Models\DataBase\ChatAttendant;
use Src\Models\UtilitiesModel;

/**
 * Class responsável por gerenciar os atendentes do chat no banco de dados
 */
class  AttendantModel
{

    private $tab_chat_attendant;
    private $Error;
    private $Result;
    private $inputs;

    /**
     * Declara a classe ChatAttendant na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_attendant = new ChatAttendant();
    }

    /**
     * Salvar dados no banco de dados    
     *   
     * @param Array $params ["name" => "", "lastname" => "", "avatar" => "", "cpf" => ""]
     * @return void
     */
    public function saveAttendant(array $params): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($params));
        if ($this->Result) {
            $this->tab_chat_attendant->attendant_uuid = $this->inputs['uuid'];
            $this->tab_chat_attendant->attendant_cpf = $this->inputs['cpf'];
            $this->tab_chat_attendant->attendant_name = $this->inputs['name'];
            $this->tab_chat_attendant->attendant_lastname = $this->inputs['lastname'];
            $this->tab_chat_attendant->attendant_avatar =  $this->inputs['avatar'];
            $this->saveCreate();
        }
    }

    /**
     * Consultar cadastro de um cliente   
     *   
     * @param int $id
     * @return void
     */
    public function readAttendant(int $id): void
    {   
        $attendant = $this->getUser($id);
        if ($id > 0 && $attendant) {
            $this->Result = true;
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['id'] = $attendant->attendant_id;
            $this->Error['data']['cpf'] = $attendant->attendant_cpf;
            $this->Error['data']['name'] = $attendant->attendant_name;
            $this->Error['data']['lastname'] = $attendant->attendant_lastname;
            $this->Error['data']['avatar'] = $attendant->attendant_avatar;
            $this->Error['data']['created_at'] = $attendant->created_at;
            $this->Error['data']['updated_at'] = $attendant->updated_at;           
        }else{
            $this->Result = false;
            $this->Error['msg'] = "Opss! O ID informado não existe ou o atendente foi excluído.";           
        }
    }

    /**
     * Consultar cadastro de um cliente   
     *   
     * @param int $id
     * @return void
     */
    public function updateAttendant(int $id): void
    {   
        $attendant = $this->getUser($id);
        if ($id > 0 && $attendant) {
            $this->Result = true;
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['id'] = $attendant->attendant_id;
            $this->Error['data']['cpf'] = $attendant->attendant_cpf;
            $this->Error['data']['name'] = $attendant->attendant_name;
            $this->Error['data']['lastname'] = $attendant->attendant_lastname;
            $this->Error['data']['avatar'] = $attendant->attendant_avatar;
            $this->Error['data']['created_at'] = $attendant->created_at;
            $this->Error['data']['updated_at'] = $attendant->updated_at;           
        }else{
            $this->Result = false;
            $this->Error['msg'] = "Opss! O ID informado não existe ou o atendente foi excluído.";           
        }
    }

    /**
     * Organizar dados para envio  
     *
     * @param Object $obj
     * @return array
     */
    public function passeAllDataArray($obj): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['id'] = $arr->data()->attendant_id;
                $result[$key]['uuid'] = $arr->data()->attendant_uuid;
                $result[$key]['cpf'] = $arr->data()->attendant_cpf;
                $result[$key]['name'] = $arr->data()->attendant_name;
                $result[$key]['lastname'] = $arr->data()->attendant_lastname;
                $result[$key]['avatar'] = $arr->data()->attendant_avatar;
                $result[$key]['updated_at'] = $arr->data()->updated_at;
                $result[$key]['created_at'] = $arr->data()->created_at;
            }
        }
        return $result;
    }

    /**
     * Consultar dados de um usuário pelo UUID
     *
     * @param string $uuid
     * @return null|Object
     */
    public function getUserUUID(string $uuid)
    {
        $attendant = $this->tab_chat_attendant->find("attendant_uuid = :uuid", "uuid=$uuid")->fetch();

        if ($attendant) {
            return $attendant->data();
        } else {
            return false;
        }
    }

    /**
     * Consultar dados de um usuário pelo CPF
     *
     * @param string $cpf
     * @return null|Object
     */
    public function getUserCPF(string $cpf)
    {
        $cpf = UtilitiesModel::numCPF($cpf);
        $attendant = $this->tab_chat_attendant->find("attendant_cpf = :cpf", "cpf=$cpf")->fetch();

        if ($attendant) {
            return $attendant->data();
        } else {
            return false;
        }
    }

    /**
     * Consultar dados de um usuário pelo ID
     *
     * @param integer $uuid
     * @return null|Object
     */
    public function getUser(int $id)
    {
        if ($this->tab_chat_attendant->findById($id)) {
            return $this->tab_chat_attendant->findById($id)->data();
        } else {
            return false;
        }
    }

    /**
     * Verificar e validar os dados para cadastro
     *
     * @param array $inputs
     * @return void
     */
    public function checkInputs(array $inputs)
    {
        if (!empty($inputs['cpf']) && !empty($inputs['name']) && !empty($inputs['lastname'])) {

            if (UtilitiesModel::validateCPF($inputs['cpf'])) {

                if (!$this->getUserCPF($inputs['cpf'])) {
                    $this->inputs['cpf'] = UtilitiesModel::numCPF($inputs['cpf']);
                    $this->inputs['name'] = $inputs['name'];
                    $this->inputs['lastname'] = $inputs['lastname'];
                    $this->inputs['avatar'] = empty($inputs['avatar']) ? "assets/img/user.png" : $inputs['avatar'];
                    $this->inputs['uuid'] = UUIDModel::v4();
                    $this->Result = true;
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Opss! O CPF informado já foi cadastrado para outro atendente.";
                }
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! CPF inválido!";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios.";
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
    private function saveCreate(): void
    {
        $result = $this->tab_chat_attendant->save();

        if ($result) {
            $this->Result = true;
            $this->Error['msg'] = "Cadastro realizado com sucesso!";
            $this->Error['data']['id'] = $this->tab_chat_attendant->attendant_id;
            $this->Error['data']['uuid'] = $this->tab_chat_attendant->attendant_uuid;
        } else {
            $this->Result = false;
            $this->Error['msg'] = $this->tab_chat_attendant->fail()->getMessage();
            $this->Error['data'] = null;
        }
    }
}
