-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03-Ago-2021 às 19:32
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
-- Estrutura da tabela `chat_bot`
--

CREATE TABLE `chat_bot` (
  `bot_id` int(11) NOT NULL,
  `bot_intent` varchar(255) NOT NULL COMMENT 'Intenção - nome da intenção em uma palavra',
  `bot_entitie` varchar(255) NOT NULL COMMENT 'Entidade - elementos de texto conhecidos',
  `bot_exemples` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Exemplos de textos do usuário' CHECK (json_valid(`bot_exemples`)),
  `bot_reply` text NOT NULL COMMENT 'Resposta ao user ou nome de uma função'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `chat_bot`
--
ALTER TABLE `chat_bot`
  ADD PRIMARY KEY (`bot_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chat_bot`
--
ALTER TABLE `chat_bot`
  MODIFY `bot_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
