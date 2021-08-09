<?php

namespace Src\Controllers\Chat\Socket;

use Src\Models\MsgModel;
use Src\Models\UtilitiesModel;
use Ratchet\ConnectionInterface;

/**
 * Class com os comandos disponíveis no chat
 */
class CommandController
{

    /**
     * Set commands
     *
     * @param string $cmd   
     */
    public function setCommand(string $cmd)
    {
        return  $this->$cmd();
    }
}
