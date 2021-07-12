<?php

namespace Src\Models\DataBase;

use CoffeeCode\DataLayer\DataLayer;

/**
 * Class responsável pela tabela chat_attendant
 */
class ChatAttendant extends DataLayer
{
  private $Error;
  private $Result;

  /**
   * Constructor.
   */
  public function __construct()
  {
    //string "TABLE_NAME", array ["REQUIRED_FIELD_1", "REQUIRED_FIELD_2"], string "PRIMARY_KEY", bool "TIMESTAMPS"
    parent::__construct(
      "chat_attendant",
      [
        "attendant_uuid",
        "attendant_cpf",
        "attendant_name",
        "attendant_lastname",
        "attendant_avatar"
      ],
      "attendant_id",
      true
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
   * Consulta todos os dados da tabela
   *
   * @return void
   */
  public function readAll()
  {
    $read = $this->find()->fetch(true);

    if ($read) {
      $this->Result = $read;
      $this->Error = "Sucesso!";
    } else {
      $this->Result = false;
      $this->Error = "Não foi possível consultar!";
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
   * @return string|array|Object 
   */
  public function getError()
  {
    return $this->Error;
  }
}
