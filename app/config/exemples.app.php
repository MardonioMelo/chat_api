<?php

# hora local
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set("Brazil/East");

# Definições padrão do sistema
define( "APP_CONFIG", [
    "home" => "http://localhost", # http://localhost
    "api_v" => "/api/v0", # /api/v0
 
]);

# Definições para conexão com banco de dados
define("DATA_LAYER_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "chatbot",
    "username" => "",
    "passwd" => "",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

define("TELEGRAM_TOKEN", "");

define("USER_ID", 1);
define("USER_NAME", "João");
define("USER_IMG", "");
define("BOT_NAME", "Zé");
define("BOT_IMG", "");

define("SERVER_CHAT_PORT", "8081");
define("SERVER_CHAT_URL", "ws://localhost:" . SERVER_CHAT_PORT);
