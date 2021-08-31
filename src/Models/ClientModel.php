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
    private $inputs;

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
    public function createClient(array $data): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($data));
        if ($this->Result) {
            $this->tab_chat_client->client_uuid = $this->inputs['uuid'];
            $this->tab_chat_client->client_cpf = $this->inputs['cpf'];
            $this->tab_chat_client->client_name = $this->inputs['name'];
            $this->tab_chat_client->client_lastname = $this->inputs['lastname'];
            $this->tab_chat_client->client_avatar =  $this->inputs['avatar'];
            $this->saveData();
        }
    }

    /**
     * Consultar um cadastro 
     *   
     * @param int $id
     * @return void
     */
    public function readClient(int $id): void
    {
        $client = $this->getUser($id);
        if ($id > 0 && $client) {
            $this->Result = true;
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['id'] = $client->client_id;
            $this->Error['data']['cpf'] = $client->client_cpf;
            $this->Error['data']['uuid'] = $client->client_uuid;
            $this->Error['data']['name'] = $client->client_name;
            $this->Error['data']['lastname'] = $client->client_lastname;
            $this->Error['data']['avatar'] = $client->client_avatar;
            $this->Error['data']['created_at'] = date("d/m/Y", strtotime($client->created_at));
            $this->Error['data']['updated_at'] = date("d/m/Y", strtotime($client->updated_at));
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! O ID informado não existe ou o cliente foi excluído.";
        }
    }

    /**
     * Consultar um perfil 
     *   
     * @param string $uuid
     * @return void
     */
    public function perfilClient(string $uuid): void
    {
        $client = $this->getUserUUID($uuid);
        if ($client) {
            $this->Result = true;
            $this->Error['msg'] = "Sucesso!";
            $this->Error['data']['id'] = $client->client_id;
            $this->Error['data']['cpf'] = $client->client_cpf;
            $this->Error['data']['uuid'] = $client->client_uuid;
            $this->Error['data']['name'] = $client->client_name;
            $this->Error['data']['lastname'] = $client->client_lastname;
            $this->Error['data']['avatar'] = $client->client_avatar;
            $this->Error['data']['created_at'] = date("d/m/Y", strtotime($client->created_at));
            $this->Error['data']['updated_at'] = date("d/m/Y", strtotime($client->updated_at));
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! O perfil informado não existe ou foi excluído.";
        }
    }

    /**
     * Atualizar todos os dados de um cadastro
     *   
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateClient(int $id, array $data): void
    {
        $this->checkInputs(UtilitiesModel::filterParams($data), $id);
        if ($this->Result) {
            $client = $this->getUser($id);
            if ($id > 0 && $client) {
                $this->tab_chat_client->client_id = $client->client_id;
                $this->tab_chat_client->client_uuid = $client->client_uuid;
                $this->tab_chat_client->client_cpf = $this->inputs['cpf'];
                $this->tab_chat_client->client_name = $this->inputs['name'];
                $this->tab_chat_client->client_lastname = $this->inputs['lastname'];
                $this->tab_chat_client->client_avatar =  $this->inputs['avatar'];
                $this->updateData();
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! O ID informado não existe ou o cliente foi excluído.";
            }
        }
    }

    /**
     * Consultar um cadastro 
     *   
     * @param int $id
     * @return void
     */
    public function deleteClient(int $id): void
    {
        $client = $this->getUser($id);
        if ($id > 0 && $client) {
            $this->deleteData($id);
            $this->Result = true;
            $this->Error['msg'] = "Cadastro excluído com sucesso!";
            $this->Error['data']['id'] = $client->client_id;
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! O cadastro não existe ou já foi excluído.";
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
     * @return void
     */
    public function readAllClientFind(string $find_name, string $find_value, int $limit = 10, int $offset = 0, $uri = ""): void
    {
        if ($limit == 0) {
            $this->Result = false;
            $this->Error['msg'] = "O limite deve ser maior que 0 (zero), tente novamente!";
        } else {

            $clients = $this->tab_chat_client->find($find_name, $find_value)->limit($limit)->offset($offset)->fetch("client_id ASC");

            if ($clients) {

                $count = $this->tab_chat_client->find($find_name, $find_value)->count();
                $links = UtilitiesModel::paginationLink(HOME . $uri, $limit, $offset, $count);

                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data'] = $this->passeAllDataArray($clients, HOME . $uri);
                $this->Error['count'] =  $count;
                $this->Error['next'] = $links['next'];
                $this->Error['previous'] = $links['previous'];
            } else {
                $clients = $this->tab_chat_client->find($find_name, $find_value)->limit(10)->offset(0)->fetch("client_id ASC");

                if ($clients) {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros no limite e deslocamento informados, tente outra margem de consulta!";
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem clientes cadastrados para os parâmetros informados!";
                }
            }
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
    public function readAllClient(int $limit = 10, int $offset = 0, $uri = ""): void
    {
        if ($limit == 0) {
            $this->Result = false;
            $this->Error['msg'] = "O limite deve ser maior que 0 (zero), tente novamente!";
        } else {

            $clients = $this->tab_chat_client->find()->limit($limit)->offset($offset)->fetch("client_id ASC");

            if ($clients) {

                $count = $this->tab_chat_client->find()->count();
                $links = UtilitiesModel::paginationLink(HOME . $uri, $limit, $offset, $count);

                $this->Result = true;
                $this->Error['msg'] = "Sucesso!";
                $this->Error['data'] = $this->passeAllDataArray($clients, HOME . $uri);
                $this->Error['count'] =  $count;
                $this->Error['next'] = $links['next'];
                $this->Error['previous'] = $links['previous'];
            } else {
                $clients = $this->tab_chat_client->find()->limit(10)->offset(0)->fetch("client_id ASC");

                if ($clients) {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem cadastros no limite e deslocamento informados, tente outra margem de consulta!";
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Não existem clientes cadastrados!";
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
                $result[$key]['id'] = $arr->data()->client_id;
                $result[$key]['cpf'] = $arr->data()->client_cpf;
                $result[$key]['uuid'] = $arr->data()->client_uuid;
                $result[$key]['name'] = $arr->data()->client_name;
                $result[$key]['lastname'] = $arr->data()->client_lastname;
                $result[$key]['avatar'] = $arr->data()->client_avatar;
                $result[$key]['updated_at'] = date("d/m/Y", strtotime($arr->data()->updated_at));
                $result[$key]['created_at'] = date("d/m/Y", strtotime($arr->data()->created_at));
                if ($url) {
                    $result[$key]['url'] = $url . '/' .  $arr->data()->client_id;
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
        $cpf = UtilitiesModel::numCPF($cpf);
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
     * @param integer $uuid
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
     * @param array $data
     * @param int|null $id
     * @return void
     */
    public function checkInputs(array $data, $id = null)
    {
        if (!empty($data['cpf']) && !empty($data['name']) && !empty($data['lastname'])) {

            if (UtilitiesModel::validateCPF($data['cpf'])) {

                if ($id) {
                    $client = $this->tab_chat_client->find("client_id <> :id AND client_cpf = :cpf", "id=$id&cpf=" . $data['cpf'])->fetch();
                } else {
                    $client = $this->getUserCPF($data['cpf']);
                }

                if (!$client) {
                    $this->inputs['cpf'] = UtilitiesModel::numCPF($data['cpf']);
                    $this->inputs['name'] = $data['name'];
                    $this->inputs['lastname'] = $data['lastname'];
                    $this->inputs['avatar'] = empty($data['avatar']) ? "assets/img/user.png" : $data['avatar'];
                    $this->inputs['uuid'] = UUIDModel::v4();
                    $this->Result = true;
                } else {
                    $this->Result = false;
                    $this->Error['msg'] = "Opss! O CPF informado já foi cadastrado para outro cliente.";
                }
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Opss! CPF inválido!";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Opss! Informe os campos obrigatórios para realizar o cadastro.";
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

    /**
     * Atualizar dados no banco de dados
     *
     * @return string
     */
    private function updateData(): void
    {
        $result = $this->tab_chat_client->save();

        if ($result) {
            $this->Result = true;
            $this->Error['msg'] = "Atualização realizada com sucesso!";
            $this->Error['data']['id'] = $this->tab_chat_client->client_id;
            $this->Error['data']['updated_at'] = date("d/m/Y", strtotime($this->tab_chat_client->updated_at));
        } else {
            $this->Result = false;
            $this->Error['msg'] = $this->tab_chat_client->fail()->getMessage();
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
        $user = $this->tab_chat_client->find("client_id = :id", "id=$id")->fetch();
        $user->destroy();
    }
}
