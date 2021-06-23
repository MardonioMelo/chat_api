<?php

require './vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Src\Controllers\Chat\Socket\Chat;
use Src\Controllers\Chat\Socket\MyChat;



$app = new Ratchet\App('localhost', 8081);
$app->route('/api/attendant', new Chat(true), array('*'));
$app->route('/api/client', new Chat(true), array('*'));
$app->run();

// $server = IoServer::factory(
//     new HttpServer(
//         new WsServer(
//             new Chat(true)
//         )
//     ),
//     SERVER_CHAT_PORT
// );

// $server->run();
