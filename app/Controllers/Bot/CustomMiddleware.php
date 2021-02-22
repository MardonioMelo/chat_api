<?php
/**
 * Copyright (c) 2020.  Mardônio M. Filho STARTMELO DESENVOLVIMENTO WEB.
 */

namespace App\Controllers\Bot;


use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Captured;
use BotMan\BotMan\Interfaces\Middleware\Heard;
use BotMan\BotMan\Interfaces\Middleware\Matching;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Interfaces\Middleware\Sending;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;


class CustomMiddleware implements Captured, Sending, Received, Heard, Matching
{

    /** @var NlpController */
    protected $nlp;
    protected $response;

    /**
     * @param $text
     * @return mixed
     */
    public function getResponse($text)
    {
        $this->nlp = new NlpController();
        $this->nlp->setQuery($text);

        if ($this->nlp->getQuery() === null) {
            $this->nlp->setResponse("function", "notUnderstand", "null");
            $result = json_decode($this->nlp->getResponse());

        } else {
            $this->nlp->start(); //Informe true para modo de teste ou deixe vazio pata produção 
            $result = json_decode($this->nlp->getResponse());
        }

        return $result;
    }

    /**
     * Manipular todas as solicitações de serviço de mensagens recebidas.
     * Por exemplo, isso pode ser usado para pré-processar os dados recebidos e
     * enviá-los para uma ferramenta de processamento de linguagem natural.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $this->response = $this->getResponse($message->getText());

        $reply = empty($this->response->reply) ? '' : $this->response->reply;
        $intent = empty($this->response->intent) ? '' : $this->response->intent;
        $entitie = empty($this->response->entitie) ? '' : $this->response->entitie;
        $acuracy = empty($this->response->acuracy) ? '' : $this->response->acuracy;

        $message->addExtras('text', trim($message->getText())); // msg do user
        $message->addExtras('apiReply', $reply); // resposta - vinda do db
        $message->addExtras('apiIntent', $intent); // intenção - classe
        $message->addExtras('apiEntitie', $entitie); // texto conhecido
        $message->addExtras('apiAcuracy', $acuracy); // acurácia - precisão
        $message->setText("nlp"); // padrão da esculta

        return $next($message);
    }

    /**
     * Processa as respostas recebidas quando o usuário atual está dentro de um fluxo de conversa.
     * Esse middleware será executado apenas quando o usuário estiver em uma conversa e responder
     * a uma de suas perguntas.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function captured(IncomingMessage $message, $next, BotMan $bot)
    {
        $text = strtolower(trim((string)$message->getExtras('text')));
        if ($text === "stop" || $text === "cancelar") {
            $message->setText("stop"); // parar conversa
        }

        return $next($message);
    }

    /**
     * Define como as mensagens serão correspondidas. Ele recebe o resultado da verificação
     * de expressão regular e também pode executar verificações adicionais. Este método é útil
     * ao testar mensagens recebidas em relação aos resultados do processamento de linguagem natural - como intenções.
     *
     * Pega a mensagem recebida e verifica não apenas a correspondência da expressão regular - mas também
     * verifica se a mensagem foi enviada de um usuário específico.
     *
     * @param IncomingMessage $message
     * @param string $pattern
     * @param bool $regexMatched Indicador se a expressão regular também foi correspondida
     * @return bool
     */
    public function matching(IncomingMessage $message, $pattern, $regexMatched)
    {
        return $regexMatched && $message->getSender() === 'user';
    }

    /**
     * Manipular uma mensagem que foi ouvida com sucesso, mas ainda não foi processada.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function heard(IncomingMessage $message, $next, BotMan $bot)
    {
        return $next($message);
    }

    /**
     * Lidar com uma carga útil de mensagens enviadas antes / depois
     * atinge o serviço de mensagens.
     *
     * @param mixed $payload
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function sending($payload, $next, BotMan $bot)
    {
        return $next($payload);
    }
}