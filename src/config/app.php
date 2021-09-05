<?php

use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Session\Session;

# hora local
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set("Brazil/East");

#Iniciar sessão
(new Session())->start();

#Set dotenv
$dotenv = Dotenv::createImmutable(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
$dotenv->load();

# Definições padrão do sistema
define("HOME",  $_ENV['HOME']);
define("API_VERSION",  $_ENV['API_VERSION']);

# Definições para conexão com banco de dados
define("DATA_LAYER_CONFIG", [
    "driver" => $_ENV['DB_DRIVE'],
    "host" => $_ENV['DB_HOST'],
    "port" => $_ENV['DB_PORT'],
    "dbname" => $_ENV['DB_NAME'],
    "username" => $_ENV['DB_USER'],
    "passwd" => $_ENV['DB_PASS'],
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

# WebSocket
define("SERVER_CHAT_HOST", $_ENV['CHAT_HOST']);
define("SERVER_CHAT_PORT", $_ENV['CHAT_PORT']);
define("JWT_EXP", $_ENV['JWT_EXP']); //12hs - Tempo de expiração do token em segundos
define("JWT_PUBLIC", $_ENV['JWT_PUBLIC']); // Chave publica
define("JWT_SECRET", $_ENV['JWT_SECRET']); // Chave privada/secreta