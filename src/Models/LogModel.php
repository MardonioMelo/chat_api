<?php

namespace Src\Models;


class LogModel
{

    private $log;
    private $history;

    /**
     * Init log
     *
     * @param boolean $on = true para ligar os logs
     * @param boolean $history = para mostrar histórico
     */
    public function __construct(bool $on = true, bool $history = true)
    {
        $this->on_log = $on;
        $this->history = $history;
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
        if ($this->history) {
            $history === true ?: popen('cls', 'w');
        }       

        if ($this->on_log) {
            $in = "\n---------" . date("d/m/Y H:i:s") . "------------\n";
            $out = "\n----------------------------------------\n";
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
