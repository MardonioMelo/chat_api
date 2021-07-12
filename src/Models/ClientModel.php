<?php

namespace Src\Models;

use Src\Models\UUIDModel;
use Src\Models\DataBase\ChatClient;
use Src\Models\UtilitiesModel;

/**
 * Class responsável por gerenciar os clientes do chat no banco de dados
 */
class  ClientModel
{

    private $tab_chat_client;
    private $Error;
    private $Result;

    /**
     * Declara a classe ChatClient na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_client = new ChatClient();
    }

    /**
     * Salvar dados no banco de dados    
     *   
     * @param Array $params ["name" => "", "lastname" => "", "avatar" => "", "cpf" => ""]
     * @return void
     */
    public function saveClient(array $params): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($params));
        if ($this->Result) {
            $this->tab_chat_client->client_uuid = $this->inputs['uuid'];
            $this->tab_chat_client->client_cpf = $this->inputs['cpf'];
            $this->tab_chat_client->client_name = $this->inputs['name'];
            $this->tab_chat_client->client_lastname = $this->inputs['lastname'];
            $this->tab_chat_client->client_avatar =  $this->inputs['avatar'];           
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
                $result[$key]['client_id'] = $arr->data()->client_id;
                $result[$key]['client_uuid'] = $arr->data()->client_uuid;
                $result[$key]['client_cpf'] = $arr->data()->client_cpf;
                $result[$key]['client_name'] = $arr->data()->client_name;
                $result[$key]['client_lastname'] = $arr->data()->client_lastname;
                $result[$key]['client_avatar'] = $arr->data()->client_avatar;
                $result[$key]['client_updated_at'] = $arr->data()->client_updated_at;
                $result[$key]['client_created_at'] = $arr->data()->client_created_at;
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
        $client = $this->tab_chat_client->find("client_uuid = :uuid", "uuid=$uuid")->fetch();

        if ($client) {
            return $client->data();
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
        $client = $this->tab_chat_client->find("client_cpf = :cpf", "cpf=$cpf")->fetch();

        if ($client) {
            return $client->data();
        } else {
            return false;
        }
    }

    /**
     * Consultar dados de um usuário pelo ID
     *
     * @param integer $id
     * @return null|Object
     */
    public function getUser(int $id)
    {
        if ($this->tab_chat_client->findById($id)) {
            return $this->tab_chat_client->findById($id)->data();
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
        $result = $this->tab_chat_client->save();

        if ($result) {
            $this->Result = true;
            $this->Error['msg'] = "Cadastro realizado com sucesso!";
            $this->Error['data']['id'] = $this->tab_chat_client->client_id;
            $this->Error['data']['uuid'] = $this->tab_chat_client->client_uuid;
        } else {
            $this->Result = false;
            $this->Error['msg'] = $this->tab_chat_client->fail()->getMessage();
            $this->Error['data'] = null;
        }
    }
}
