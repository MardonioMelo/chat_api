<?php

namespace Src\Models;


class LogModel
{

    private $log;

    /**
     * Init log
     *
     * @param boolean $on
     */
    public function __construct(bool $on)
    {
        $this->on_log = $on;
        $this->log = "";
    }

    /**
     * Inclui os dados na memória para serem exibidos ou salvos em db 
     *
     * @param string $log
     * @return void
     */
    public function setLog(string $log): void
    {
        $this->log .= $log;
    }

    /**
     * Imprimi os logs na tela
     *
     * @param boolean $history
     * @return void
     */
    public function printLog($history = false): void
    {
        $history === true ?: popen('cls', 'w');

        $in = "\n---------" . date("d/m/Y H:i:s") . "------------\n";
        $out = "\n----------------------------------------\n";

        if ($this->on_log) {
            echo $in . $this->log . "\nMemory: " . memory_get_usage() . " bytes" . $out;
        }
    }

    /**
     * Reset logs
     *   
     * @return void
     */
    public function resetLog(): void
    {
        $this->log = "";
    }
}
