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
    public function createAttendant(array $data): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_attendant->attendant_uuid = $this->inputs['uuid'];
            $this->tab_chat_attendant->attendant_cpf = $this->inputs['cpf'];
            $this->tab_chat_attendant->attendant_name = $this->inputs['name'];
            $this->tab_chat_attendant->attendant_lastname = $this->inputs['lastname'];
            $this->tab_chat_attendant->attendant_avatar =  $this->inputs['avatar'];
            $this->saveData();
        }
    }

    /**
     * Consultar um cadastro 
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
            $this->Error['data']['created_at'] = date("d/m/Y", strtotime($attendant->created_at));
            $this->Error['data']['updated_at'] = date("d/m/Y", strtotime($attendant->updated_at));
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! O ID informado não existe ou o atendente foi excluído.";
        }
    }

    /**
     * Atualizar todos os dados de um cadastro
     *   
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateAttendant(int $id, array $data): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($data), $id);
        if ($this->Result) {
            $attendant = $this->getUser($id);        
            if ($id > 0 && $attendant) {
                $this->tab_chat_attendant->attendant_id = $attendant->attendant_id;
                $this->tab_chat_attendant->attendant_uuid = $attendant->attendant_uuid;
                $this->tab_chat_attendant->attendant_cpf = $this->inputs['cpf'];
                $this->tab_chat_attendant->attendant_name = $this->inputs['name'];
                $this->tab_chat_attendant->attendant_lastname = $this->inputs['lastname'];
                $this->tab_chat_attendant->attendant_avatar =  $this->inputs['avatar'];
                $this->updateData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! O ID informado não existe ou o atendente foi excluído.";
            }
        }
    }

    /**
     * Consultar um cadastro 
     *   
     * @param int $id
     * @return void
     */
    public function deleteAttendant(int $id): void
    {
        $attendant = $this->getUser($id);
        if ($id > 0 && $attendant) {
            $this->deleteData($id);
            $this->Result = true;
            $this->Error['msg'] = "Cadastro excluído com sucesso!";
            $this->Error['data']['id'] = $attendant->attendant_id;
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! O cadastro não existe ou já foi excluído.";
        }
    }

    /**
     * Consultar todos os cadastros
     *
     * @param integer $limit
     * @param integer $offset
     * @param string $uri
     * @return void
     */
    public function readAllAttendant(int $limit = 10, int $offset = 0, $uri = ""): void
    {
        if ($limit == 0) {
            $this->Result = false;
            $this->Error['msg'] = "O limite deve ser maior que 0 (zero), tente novamente!";
        } else {

            $attendants = $this->tab_chat_attendant->find()->limit($limit)->offset($offset)->fetch("attendant_id ASC");

            if ($attendants) {

                $count = $this->tab_chat_attendant->find()->count();
                $links = UtilitiesModel::paginationLink(HOME . $uri, $limit, $offset, $count);

                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data'] = $this->passeAllDataArray($attendants, HOME . $uri);
                $this->Error['count'] =  $count;
                $this->Error['next'] = $links['next'];
                $this->Error['previous'] = $links['previous'];
            } else {
                $attendants = $this->tab_chat_attendant->find()->limit(10)->offset(0)->fetch("attendant_id ASC");

                if ($attendants) {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros no limite e deslocamento informados, tente outra margem de consulta!";
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem atendentes cadastrados!";
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
                $result[$key]['id'] = $arr->data()->attendant_id;
                $result[$key]['cpf'] = $arr->data()->attendant_cpf;
                $result[$key]['name'] = $arr->data()->attendant_name;
                $result[$key]['lastname'] = $arr->data()->attendant_lastname;
                $result[$key]['avatar'] = $arr->data()->attendant_avatar;
                $result[$key]['updated_at'] = date("d/m/Y", strtotime($arr->data()->updated_at));
                $result[$key]['created_at'] = date("d/m/Y", strtotime($arr->data()->created_at));
                if ($url) {
                    $result[$key]['url'] = $url . '/' .  $arr->data()->attendant_id;
                }
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
     * @param array $data
     * @param int|null $id
     * @return void
     */
    public function checkInputs(array $data, $id = null)
    {
        if (!empty($data['cpf']) && !empty($data['name']) && !empty($data['lastname'])) {

            if (UtilitiesModel::validateCPF($data['cpf'])) {

                if ($id) {
                    $attendant = $this->tab_chat_attendant->find("attendant_id <> :id AND attendant_cpf = :cpf", "id=$id&cpf=".$data['cpf'])->fetch();
                } else {
                    $attendant = $this->getUserCPF($data['cpf']);
                }               

                if (!$attendant) {
                    $this->inputs['cpf'] = UtilitiesModel::numCPF($data['cpf']);
                    $this->inputs['name'] = $data['name'];
                    $this->inputs['lastname'] = $data['lastname'];
                    $this->inputs['avatar'] = empty($data['avatar']) ? "assets/img/user.png" : $data['avatar'];
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
    private function saveData(): void
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

    /**
     * Atualizar dados no banco de dados
     *
     * @return string
     */
    private function updateData(): void
    {
        $result = $this->tab_chat_attendant->save();

        if ($result) {
            $this->Result = true;
            $this->Error['msg'] = "Atualização realizada com sucesso!";
            $this->Error['data']['id'] = $this->tab_chat_attendant->attendant_id;
            $this->Error['data']['updated_at'] = date("d/m/Y", strtotime($this->tab_chat_attendant->updated_at));
        } else {
            $this->Result = false;
            $this->Error['msg'] = $this->tab_chat_attendant->fail()->getMessage();
            $this->Error['data'] = null;
        }
    }

    /**
     * Deletar cadastro de um usuário pelo ID
     *
     * @param integer $uuid
     * @return void
     */
    public function deleteData(int $id)
    {
        $user = $this->tab_chat_attendant->find("attendant_id = :id", "id=$id")->fetch();
        $user->destroy();
    }
}
