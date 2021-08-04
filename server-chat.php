<?php

require './vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Src\Controllers\Chat\Socket\AppChatController;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new AppChatController()
        )
    ),
    SERVER_CHAT_PORT
);

$server->run();
