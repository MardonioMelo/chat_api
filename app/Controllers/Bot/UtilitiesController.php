<?php

namespace App\Controllers\Bot;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

/**
 * Classe de utilidades do Bot
 */
class UtilitiesController
{

    /** @var BotMan */
    private $botman;

    public function __construct(BotMan $bot)
    {
        $this->botman = $bot;
    }

    /**
     * Envio de imagens pelo bot
     * @param $url
     * @param $text
     * @param null $title
     */
    public function img($url, $text, $title = null)
    {
        // Create attachment
        $attachment = new Image($url, [
            'custom_payload' => true,
        ]);

        $attachment->title($title);

        // Build message object
        $message = OutgoingMessage::create($text)->withAttachment($attachment);

        // Reply message object
        $this->botman->reply($message);
    }

    /**
     * Envio de audio pelo bot
     * @param $url
     * @param $text
     */
    public function audio($url, $text)
    {
        // Create attachment
        $attachment = new Audio($url, [
            'custom_payload' => true,
        ]);

        // Build message object
        $message = OutgoingMessage::create($text)->withAttachment($attachment);

        // Reply message object
        $this->botman->reply($message);
    }
}