<?php

namespace Src\Controllers\Chat\Socket;

use Src\Models\UtilitiesModel;

/**
 * Class com os comandos disponíveis no chat
 */
class CommandController
{
    /**
     * Set commands
     *
     * @param string $cmd
     * @return void
     */
    public function setCommand(string $cmd):void
    {
        $this->$cmd();
    }
}
