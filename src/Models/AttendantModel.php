<?php

namespace Src\Models;

use Src\Models\DataBase\ChatAttendant;

/**
 * Class responsável por gerenciar os atendentes do chat no banco de dados
 */
class  AttendantModel
{

    private $tab_chat_attendant;
    private $Error;
    private $Result;

    /**
     * Declara a classe ChatMsg na inicialização
     */
    public function __construct()
    {
        $this->tab_chat_attendant = new ChatAttendant();
    }

    /**
     * Salva mensagem do chat no banco de dados
     * 
     * Parei aqui..........
     *   
     * @param String $attendant_name
     * @param String $attendant_lastname
     * @param String $attendant_avatar
     * @return void
     */
    public function saveAttendant(String $attendant_name, String $attendant_lastname, String $attendant_avatar = "/avatar"): void
    {
        $this->tab_chat_attendant->attendant_name = (string) $attendant_name;
        $this->tab_chat_attendant->attendant_lastname = (string) $attendant_lastname;
        $this->tab_chat_attendant->attendant_avatar = (string) $attendant_avatar;
       
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
                $result[$key]['attendant_id'] = $arr->data()->attendant_id;
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
        $id = $this->tab_chat_attendant->save();

        if ((int)$id > 0) {
            $this->Result = $id;
            $this->Error = "Cadastro realizado com sucesso!";
        } else {
            $this->Result = $id;
            $this->Error = $this->tab_chat_attendant->fail()->getMessage();
        }
    }
    
}
