-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03-Ago-2021 às 19:33
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
-- Estrutura da tabela `chat_call`
--

CREATE TABLE `chat_call` (
  `call_id` int(11) NOT NULL,
  `call_user_uuid` varchar(40) NOT NULL COMMENT 'UUID do atendente.',
  `call_user_dest_uuid` varchar(40) NOT NULL COMMENT 'UUID do cliente.',
  `call_objective` varchar(255) DEFAULT NULL COMMENT 'Assunto do atendimento.',
  `call_status` int(2) NOT NULL DEFAULT 1 COMMENT 'Status do atendimento.',
  `call_date_start` timestamp NULL DEFAULT NULL COMMENT 'Data e hora UTC do inicio do atendimento.',
  `call_date_end` timestamp NULL DEFAULT NULL COMMENT 'Data e hora UTC do fim do atendimento.',
  `call_evaluation` int(2) DEFAULT NULL COMMENT 'Nota de avaliação do chamado.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de atendimentos';

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `chat_call`
--
ALTER TABLE `chat_call`
  ADD PRIMARY KEY (`call_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chat_call`
--
ALTER TABLE `chat_call`
  MODIFY `call_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
