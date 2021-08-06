<?php

namespace Src\Models\DataBase;

use CoffeeCode\DataLayer\DataLayer;

/**
 * Class responsável pela tabela chat_call
 */
class ChatCall extends DataLayer
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        //string "TABLE_NAME", array ["REQUIRED_FIELD_1", "REQUIRED_FIELD_2"], string "PRIMARY_KEY", bool "TIMESTAMPS"
        parent::__construct(
            "chat_call",
            [
                "call_user_uuid"                  
            ],
            "call_id",
            false
        );
    }

    /**
     * Consulta com busca na tabela por coluna
     *
     * @param string $col
     * @param string $search
     * @return void
     */
    public function readCol($col, $search)
    {
        $read = $this->find($col, $search)->fetch(true);

        if ($read) {
            $this->Result = $read;
            $this->Error = "Sucesso!";
        } else {
            $this->Result = false;
            $this->Error = "Não foi possível consultar!";
        }
    }

    /**
     * <b>Verificar Ação:</b> Retorna TRUE se ação for efetuada ou FALSE se não. Para verificar erros
     * execute um getError();
     * @return BOOL|array|object $Var = True(com os dados) or False
     */
    public function getResult()
    {
        return $this->Result;
    }

    /**
     * <b>Obter Erro:</b> Retorna um string com um erro e um tipo.
     * @return string $Error = String com o erro
     */
    public function getError()
    {
        return $this->Error;
    }
}
