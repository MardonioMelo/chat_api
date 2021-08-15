<?php

# hora local
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set("Brazil/East");

# Definições padrão do sistema
define("HOME", "http://localhost:81"); 
define("API_VERSION", "/api"); 

# Definições para conexão com banco de dados
define("DATA_LAYER_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "chatbot",
    "username" => "root",
    "passwd" => "",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

# WebSocket
define("SERVER_CHAT_PORT", "8081");
define("SERVER_CHAT_URL", "ws://localhost:" . SERVER_CHAT_PORT . API_VERSION);
define("JWT_PUBLIC", "ffc6wwq2eb25f5asasf11a7f1b7546cb3ca"); // Chave publica gerada com md5(uniqid(rand(),true))); para o 1º token
define("JWT_SECRET", "ffc68a2eb25f5a1f11a7f1b7546cb3ca"); // Chave privada gerada com md5(uniqid(rand(),true))); para o 2º token
