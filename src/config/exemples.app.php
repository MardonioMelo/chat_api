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
    "dbname" => "db_chat",
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
define("SERVER_CHAT_PORT", "81");
define("JWT_PUBLIC", "28ca067230b119148dbedbdea1762e5c"); // Chave publica
define("JWT_SECRET", "188f54f9ce1af48eb6a0774e0e9dcd5a"); // Chave privada/secreta
