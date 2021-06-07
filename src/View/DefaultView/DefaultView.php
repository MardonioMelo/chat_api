<?php

namespace Src\View\DefaultView;

/**
 * Class para administrar view do chatbot
 */
class DefaultView
{
    private $dir_tlp;
    private $tpl;
    private $data;
    private $data_var;

    /**
     * Set diretório principal dos templates
     *
     * @param string $dir_tlp
     * @return void
     */
    public function setDirTpl(string $dir_tlp = ""): void
    {
        if (empty($dir_tlp)) {
            $this->dir_tlp = "../src/resources/";
        } else {
            $this->dir_tlp = $dir_tlp;
        }
    }

    /**
     * Método para escrever conteúdo povoado da página
     *
     * @return string
     */
    public function getWrite(): string
    {
        return str_replace($this->data_var, $this->data, $this->tpl);
    }

    /**
     * Método para escrever tpl com conteúdo povoado
     *
     * @return void
     */
    public function write(): void
    {
       echo str_replace($this->data_var, $this->data, $this->tpl);
    }

    /**
     * Set dados da tpl passados em um array
     *
     * @param array $arr
     * @return void
     */
    public function setData(array $arr): void
    {
        $this->data = $arr;
    }

    /**
     * Set nome das variáveis da tpl a serem substituídas
     *
     * @param array $arr
     * @return void
     */
    public function setDataName(array $arr): void
    {
        $this->data_var = $arr;
    }

    /**
     * Set template Html - Informe o nome do template e a extensão caso não seja um tpl html
     *
     * @param string $tpl
     * @param string $ext
     * @return void
     */
    public function setTplHtml($tpl, $ext = ".html"): void
    {              
        $this->tpl = file_get_contents($this->dir_tlp . $tpl . $ext);
    }   

}
