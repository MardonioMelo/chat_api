<?php

namespace Src\Models;

use Src\Models\DataBase\ChatClient;

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
     * Salva mensagem do chat no banco de dados    
     *   
     * @param String $client_name
     * @param String $client_lastname
     * @param String $client_avatar
     * @return void
     */
    public function saveAttendant(String $client_name, String $client_lastname, String $client_avatar = "/avatar"): void
    {
        $this->tab_chat_client->client_name = (string) $client_name;
        $this->tab_chat_client->client_lastname = (string) $client_lastname;
        $this->tab_chat_client->client_avatar = (string) $client_avatar;

        $this->saveCreate();
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
     * Consultar dados de um usuário
     *
     * @param integer $id
     * @return null|Object
     */
    public function getUser(int $id)
    {
        if ($this->tab_chat_client->findById($id)){
            return $this->tab_chat_client->findById($id)->data();
        }else{
            return false;
        }       
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
     * Salvar dados no banco de dados
     *
     * @return string
     */
    private function saveCreate(): void
    {
        $id = $this->tab_chat_client->save();

        if ((int)$id > 0) {
            $this->Result = $id;
            $this->Error = "Cadastro realizado com sucesso!";
        } else {
            $this->Result = $id;
            $this->Error = $this->tab_chat_client->fail()->getMessage();
        }
    }
}
