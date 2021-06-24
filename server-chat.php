<?php

require './vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Src\Controllers\Chat\Socket\AppChat;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new AppChat(true)
        )
    ),
    SERVER_CHAT_PORT
);

$server->run();
