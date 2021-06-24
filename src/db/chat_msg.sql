-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24-Jun-2021 às 19:28
-- Versão do servidor: 10.4.19-MariaDB
-- versão do PHP: 7.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `chatbot`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `chat_msg`
--

CREATE TABLE `chat_msg` (
  `chat_id` int(20) NOT NULL,
  `chat_user_id` int(11) NOT NULL COMMENT 'Id do usuário que enviou a mensagem.',
  `chat_user_dest_id` int(11) NOT NULL COMMENT 'Id do usuário que recebeu a mensagem.',
  `chat_text` text NOT NULL COMMENT 'Mensagem ou texto do conteúdo enviado',
  `chat_drive` varchar(10) NOT NULL DEFAULT 'web' COMMENT 'Meio de comunicação para o componente BotMan.',
  `chat_type` varchar(10) NOT NULL DEFAULT 'text' COMMENT 'Tipo de mensagem.',
  `chat_date` timestamp NULL DEFAULT current_timestamp() COMMENT 'Data e hora da mensagem.',
  `chat_attachment` text DEFAULT NULL COMMENT 'Parâmetro para o Bot quando um arquivo é enviado.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabela de mensagens do chat';

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `chat_msg`
--
ALTER TABLE `chat_msg`
  ADD PRIMARY KEY (`chat_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chat_msg`
--
ALTER TABLE `chat_msg`
  MODIFY `chat_id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
