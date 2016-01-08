-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tempo de Geração: Jan 21, 2011 as 12:42 AM
-- Versão do Servidor: 5.1.54
-- Versão do PHP: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Banco de Dados: `bilderguia`
--

-- --------------------------------------------------------
-- ALTER TABLE `guia_language_translate` ADD `ttt` INT( 12 ) NOT NULL 
--
-- Estrutura da tabela `guia_config`
--

CREATE TABLE IF NOT EXISTS `guia_config` (
  `item` varchar(10) NOT NULL DEFAULT '',
  `valor` tinytext
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='configurações principais do site';

--
-- Extraindo dados da tabela `guia_config`
--

INSERT INTO `guia_config` (`item`, `valor`) VALUES
('cookie', 'guiadefault'),
('cookieleng', '60'),
('prefix', 'guia_'),
('email', 'renato.innocenti@gmail.com'),
('manutencao', '0'),
('site_name', 'Guia Default'),
('agree', '"aqui vem o texto de acordo"'),
('regagree', '0'),
('forcelogin', '0'),
('antiflood', '0'),
('epass', '0'),
('emailnotif', '0'),
('emailwell', '0'),
('separate', '<atr_sep>'),
('index', 'index.php'),
('sajax_requ', 'POST'),
('sajax_debu', '0');
