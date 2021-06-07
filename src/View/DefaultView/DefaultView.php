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
    private $head;
    private $body;
    private $footer;

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

    /**
     * Motar HEAD da página
     *
     * @param string $title
     * @param string $other
     * @return void
     */
    public function mountHead(string $title, string $other = ""): void
    {
        $head = '<head>';
        $head .= '<meta charset="UTF-8">';
        $head .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        $head .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $head .= '<title>' . $title . '</title>';
        $head .= $other;
        $head .= '</head>';

        $this->head = $head;
    }

    /**
     * Motar BODY da página
     *
     * @param string $body
     * @param string $att
     * @return void
     */
    public function mountBody(string $body, string $att = ""): void
    {
        $this->body = '<body' . $att . '>' . $body . '</body>';
    }

    /**
     * Motar FOOTER da página
     *
     * @param [type] $footer
     * @param string $att
     * @return void
     */
    public function mountFooter($footer, $att = ""): void
    {
        $this->footer = '<footer ' . $att . '>' . $footer . '</footer>';
    }

    /**
     * Motar e retornar página HTML5 
     *
     * @param string $head
     * @param string $body
     * @param string $footer
     * @return string
     */
    public function mountPage(string $head = "", string $body ="", string $footer = ""): string
    {
        $head = empty($this->head) ? $head : $this->head;
        $body = empty($this->body) ? $body : $this->body;
        $footer = empty($this->footer) ? $footer : $this->footer;
        return '<!DOCTYPE html><html lang="pt-br">' . $head .$head . $footer . '</html>';
    }
}
