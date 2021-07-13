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
     * Organizar dados ara envio  
     *
     * @param Object $obj
     * @return array
     */
    public function passeAllDataArray($obj): array
    {
        $result = [];

        if ($obj) {
            foreach ($obj as $key => $arr) {
                $result[$key]['attendant_id'] = $arr->data()->attendant_id;
                $result[$key]['attendant_uuid'] = $arr->data()->attendant_uuid;
                $result[$key]['attendant_cpf'] = $arr->data()->attendant_cpf;
                $result[$key]['attendant_name'] = $arr->data()->attendant_name;
                $result[$key]['attendant_lastname'] = $arr->data()->attendant_lastname;
                $result[$key]['attendant_avatar'] = $arr->data()->attendant_avatar;
                $result[$key]['attendant_updated_at'] = $arr->data()->attendant_updated_at;
                $result[$key]['attendant_created_at'] = $arr->data()->attendant_created_at;
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
                $this->inputs['cpf'] = UtilitiesModel::numCPF($inputs['cpf']);
                $this->inputs['name'] = $inputs['name'];
                $this->inputs['lastname'] = $inputs['lastname'];
                $this->inputs['avatar'] = empty($inputs['avatar']) ? "assets/img/user.png" : $inputs['avatar'];
                $this->inputs['uuid'] = UUIDModel::v4();
                $this->Result = true;
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! CPF inválido!";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios";
        }
    }

    /**
     * <b>Verificar Ação:</b> 
     * 
     * @return bool 
     */
    public function getResult(): bool
    {
        return $this->Result;
    }

    /**
     * <b>Obter Erro:</b> 
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
