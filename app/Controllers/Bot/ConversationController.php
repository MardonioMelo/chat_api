<?php
/**
 * Copyright (c) 2020.  Mardônio M. Filho STARTMELO DESENVOLVIMENTO WEB.
 */

namespace App\Controllers\Bot;

use App\Models\BotModel;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;


/**
 * Class ConversationController
 * @package App\Controllers\Bot
 */
class ConversationController extends Conversation
{
    protected $text;
    protected $data;
    protected $action;
    private $func;
    /** @var BotMan */
    private $botModel;

    /**
     * ConversationController constructor.
     * @param $nameFunc
     * @param array $data
     * @param $action
     */
    public function __construct($nameFunc, $data, $action)
    {
        $this->func = $nameFunc;
        $this->data = $data;
        $this->action = $action;
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function postApi($url, $data)
    {
        $this->botModel = new BotModel();
        $this->botModel->postRequest($url, $data);
        return $this->botModel->getResult();
    }

    /**
     * @param $pattern
     * @param $metod
     * @param $data
     * @return mixed
     */
    public function postController($pattern, $metod, $data)
    {
        $patt = new $pattern();
        return $patt->$metod($data);
    }

    /**
     * Faz uma pergunta, salva a respota na variável declarada e faz outra pergunta se tiver fallback
     * Informe o texto da pergunta, nome da resposta, fallback, função para nova conversa e a mensagem final
     * [question, name, fallback, endFunction, endMsg]
     */
    public function question()
    {
        $this->ask($this->data['question'], function (Answer $answer) {
            $name = empty($this->data['name']) ? "answer" : $this->data['name'];
            $this->data['answer'][$name] = $answer->getMessage()->getExtras('text');

            if (!empty($this->data['fallback'])) {
                $this->data['question'] = $this->data['fallback']['question'];
                $this->data['name'] = $this->data['fallback']['name'];
                $this->data['fallback'] = $this->data['fallback']['fallback'];

                $this->question();
            } else {

                $this->data['answer']['tenant'] = $this->data['tenant'];

                if ($this->action === "post") {
                    $result = $this->postApi(
                        $this->data['url'],
                        $this->data['answer']
                    );

                } else if ($this->action === "pattern") {
                    $result = $this->postController(
                        $this->data['pattern'],
                        $this->data['pattern_metod'],
                        $this->data['answer']
                    );

                } else {
                    $result = "Opss! Buguei :-X";
                }

                $this->say($result);
            }
        });
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $func
     * @param array $data
     */
    public function checkFunc($func, $data = [])
    {
        $data = empty($data) ? $this->data : $data;
        $this->$func($data);
    }

    /**
     * @return mixed|void
     */
    public function run()
    {
        $this->checkFunc($this->func);
    }
}