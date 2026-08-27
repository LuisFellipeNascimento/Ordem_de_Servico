-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 27/08/2026 às 12:40
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ordem_servicos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE IF NOT EXISTS `servicos` (
  `id_service` bigint NOT NULL AUTO_INCREMENT,
  `description` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(11,3) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Pendente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `commission_user` decimal(11,3) DEFAULT NULL,
  `user_id_user` bigint NOT NULL,
  PRIMARY KEY (`id_service`),
  KEY `user_id_user` (`user_id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id_service`, `description`, `price`, `status`, `created_at`, `update_at`, `finished_at`, `commission_user`, `user_id_user`) VALUES
(1, 'Formatação', 50.000, 'Finalizado', '2026-08-26 14:50:16', '2026-08-26 15:04:28', '2026-08-26 15:04:28', NULL, 1),
(2, 'Troca da fonte', 300.000, 'Finalizado', '2026-08-26 14:50:33', '2026-08-26 15:30:34', '2026-08-26 15:30:34', 15.000, 1),
(6, 'Concerto de Monitor', 500.000, 'Finalizado', '2026-08-26 15:17:26', '2026-08-26 16:02:49', '2026-08-26 16:02:49', 25.000, 1),
(7, 'Conserto de TV', 980.000, 'Finalizado', '2026-08-26 16:03:25', '2026-08-26 16:15:16', '2026-08-26 16:03:29', 49.000, 1),
(8, 'Conserto de notebook', 400.000, 'Finalizado', '2026-08-26 16:08:35', '2026-08-26 16:08:40', '2026-08-26 16:08:40', 20.000, 1),
(9, 'Formatação de TV Box', 120.000, 'Finalizado', '2026-08-26 16:13:20', '2026-08-26 16:14:02', '2026-08-26 16:14:02', 6.000, 1),
(10, 'Troca da bateria da bios', 80.000, 'Pendente', '2026-08-27 09:38:35', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_user` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_user`, `name`, `email`, `password`, `created_at`, `update_at`, `ativo`) VALUES
(1, 'Luis ', 'luisfellipe_rj@hotmail.com', '123', '2026-08-26 14:46:37', NULL, 1);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `servicos`
--
ALTER TABLE `servicos`
  ADD CONSTRAINT `servicos_ibfk_1` FOREIGN KEY (`user_id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
