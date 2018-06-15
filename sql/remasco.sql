-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  mer. 06 déc. 2017 à 16:50
-- Version du serveur :  5.7.19
-- Version de PHP :  5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `remasco`
--

-- --------------------------------------------------------

--
-- Structure de la table `Historic`
--

DROP TABLE IF EXISTS `Historic`;
CREATE TABLE IF NOT EXISTS `Historic` (
  `idHistoric` int(11) NOT NULL AUTO_INCREMENT,
  `query` varchar(50) NOT NULL,
  `idTheme` int(11) DEFAULT NULL,
  `idUser` int(11) NOT NULL,
  `XMLfile` longtext NOT NULL,
  `sysDate` datetime NOT NULL,
  PRIMARY KEY (`idHistoric`),
  KEY `idTheme` (`idTheme`),
  KEY `idUser` (`idUser`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=93 ;


--
-- Structure de la table `Theme`
--

DROP TABLE IF EXISTS `Theme`;
CREATE TABLE IF NOT EXISTS `Theme` (
  `idTheme` int(11) NOT NULL AUTO_INCREMENT,
  `themeName` varchar(20) NOT NULL,
  `idUser` int(11) NOT NULL,
  `sysDate` datetime NOT NULL,
  PRIMARY KEY (`idTheme`),
  KEY `idUser` (`idUser`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;


--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(20) NOT NULL,
  `lastName` varchar(20) NOT NULL,
  `firstName` varchar(20) NOT NULL,
  `e-mail` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `teacher` tinyint(1) NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`idUser`, `userName`, `lastName`, `firstName`, `e-mail`, `password`, `teacher`) VALUES
(1, 'admin', 'administrateur', 'REMASCO', 'admin@domain.tld', '21232f297a57a5a743894a0e4a801fc3', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
