-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12-Jul-2021 às 19:50
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
-- Estrutura da tabela `chat_client`
--

CREATE TABLE `chat_client` (
  `client_id` int(11) NOT NULL,
  `client_uuid` varchar(255) NOT NULL COMMENT 'id único gerado pelo sistema',
  `client_cpf` varchar(11) NOT NULL COMMENT 'CPF sem pontuação',
  `client_name` varchar(50) NOT NULL COMMENT 'Nome do usuário',
  `client_lastname` varchar(50) NOT NULL COMMENT 'Sobrenome',
  `client_avatar` varchar(255) NOT NULL COMMENT 'link de uma imagem do usuário',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `chat_client`
--
ALTER TABLE `chat_client`
  ADD PRIMARY KEY (`client_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chat_client`
--
ALTER TABLE `chat_client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
