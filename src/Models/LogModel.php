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
    public function __construct(bool $on = true, bool $history = false)
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
    public function printLog(): void
    {
        if ($this->history) {
            popen('cls', 'w');
        }

        if ($this->on_log) {
            $this->headerLog();
            $in = "\n+--------" . date("d/m/Y H:i:s") . "-----------+\n";
            $out = "\n----------------------------------------\n";
            print_r($in . $this->log .  "\nMemória: " . memory_get_usage() . " bytes\nPID: " . getmypid() . $out);
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

    /**
     * Cabeçalho do log
     *
     * @return void
     */
    public function headerLog()
    {
        print_r("     *>>> STATUS DO SERVIDOR <<<*");
        print_r("\n|--------------------------------------|");
        print_r("\n      ╭═══════════════════════╮");
        print_r("\n        Servidor em Operação");
        print_r("\n      ╰═══════════════════════╯");
        
    }
}
