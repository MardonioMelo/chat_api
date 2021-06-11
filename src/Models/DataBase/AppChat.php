<?php

namespace Src\Models\DataBase;

use CoffeeCode\DataLayer\DataLayer;

/**
   * Class responsável pela tabela app_chat
   */
class AppChat extends DataLayer
{

  /**
   * Constructor.
   */
  public function __construct()
  {
    //string "TABLE_NAME", array ["REQUIRED_FIELD_1", "REQUIRED_FIELD_2"], string "PRIMARY_KEY", bool "TIMESTAMPS"
    parent::__construct(
      "app_chat",
      [       
        "chat_user_id",
        "chat_user_dest_id",
        "chat_text",
        "chat_drive",
        "chat_type",
        "chat_date",
        "chat_attachment"
      ],
      "chat_id",
      false
    );
  }

}
