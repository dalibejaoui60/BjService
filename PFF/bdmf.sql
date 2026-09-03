-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 02, 2026 at 08:33 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bdmf`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrateur`
--

DROP TABLE IF EXISTS `administrateur`;
CREATE TABLE IF NOT EXISTS `administrateur` (
  `idA` int NOT NULL AUTO_INCREMENT,
  `emailA` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `mdpA` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`idA`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `administrateur`
--

INSERT INTO `administrateur` (`idA`, `emailA`, `mdpA`) VALUES
(1, 'admin@bjservice.com', '$2y$10$kPfuZ0xYk6i.YB0OGzWYoOIlxMM7IBINmIW9YTULsJuD/0GsE4I5u');

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `numCde` int NOT NULL AUTO_INCREMENT,
  `dateECde` date NOT NULL,
  `dateLCde` date NOT NULL,
  `statutCde` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `detailCde` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `idPa` int NOT NULL,
  PRIMARY KEY (`numCde`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE IF NOT EXISTS `commentaire` (
  `idU` int NOT NULL,
  `idPub` int NOT NULL,
  `dateC` date NOT NULL,
  `msgC` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`idU`,`idPub`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `idMsg` int NOT NULL AUTO_INCREMENT,
  `idPa` int NOT NULL,
  `expediteur` enum('client','admin') NOT NULL,
  `contenu` text NOT NULL,
  `dateEnvoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `imagePath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idMsg`),
  KEY `idPa` (`idPa`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`idMsg`, `idPa`, `expediteur`, `contenu`, `dateEnvoi`, `imagePath`) VALUES
(41, 19, 'client', '[JEUX] Valorant — $10\naa', '2026-08-30 21:24:08', NULL),
(42, 20, 'client', '[JEUX] Free Fire — $10\n20 euro', '2026-09-01 09:43:49', NULL),
(43, 20, 'admin', '', '2026-09-01 09:44:07', 'uploads/chat/msg_6a969057341bd7.24945890.png'),
(44, 20, 'admin', 'marhba', '2026-09-01 09:44:10', NULL),
(45, 20, 'admin', 'just chway waqet w nadiw ay', '2026-09-01 09:44:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `panier`
--

DROP TABLE IF EXISTS `panier`;
CREATE TABLE IF NOT EXISTS `panier` (
  `idPa` int NOT NULL AUTO_INCREMENT,
  `dateCPa` date NOT NULL,
  `statutPa` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `idU` int NOT NULL,
  `prixFinal` double DEFAULT NULL,
  PRIMARY KEY (`idPa`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `panier`
--

INSERT INTO `panier` (`idPa`, `dateCPa`, `statutPa`, `idU`, `prixFinal`) VALUES
(19, '2026-08-30', 'annulé', 1, NULL),
(20, '2026-09-01', 'terminé', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

DROP TABLE IF EXISTS `promotion`;
CREATE TABLE IF NOT EXISTS `promotion` (
  `idpromo` int NOT NULL AUTO_INCREMENT,
  `datePromo` date NOT NULL,
  `dateFPromo` date NOT NULL,
  `reductionPromo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `idS` int NOT NULL,
  PRIMARY KEY (`idpromo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

DROP TABLE IF EXISTS `publication`;
CREATE TABLE IF NOT EXISTS `publication` (
  `idPub` int NOT NULL AUTO_INCREMENT,
  `titrePub` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `datePub` date NOT NULL,
  `TypePub` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `HeurePub` time NOT NULL,
  `DetailPub` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`idPub`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamation`
--

DROP TABLE IF EXISTS `reclamation`;
CREATE TABLE IF NOT EXISTS `reclamation` (
  `idR` int NOT NULL AUTO_INCREMENT,
  `nomClient` varchar(100) NOT NULL,
  `emailClient` varchar(100) NOT NULL,
  `sujet` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `statut` enum('nouvelle','en cours','traitée') NOT NULL DEFAULT 'nouvelle',
  `dateR` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idU` int DEFAULT NULL,
  `idPa` int DEFAULT NULL,
  PRIMARY KEY (`idR`),
  KEY `idU` (`idU`),
  KEY `idPa` (`idPa`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reclamation`
--

INSERT INTO `reclamation` (`idR`, `nomClient`, `emailClient`, `sujet`, `message`, `statut`, `dateR`, `idU`, `idPa`) VALUES
(5, 'bejaoui dali', 'dalibejaoui80@gmail.com', 'JEUX', 'saret mochkla fi coin naqsin', 'nouvelle', '2026-09-01 09:39:13', 1, 19);

-- --------------------------------------------------------

--
-- Table structure for table `regrouper`
--

DROP TABLE IF EXISTS `regrouper`;
CREATE TABLE IF NOT EXISTS `regrouper` (
  `idPa` int NOT NULL,
  `idS` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`idPa`,`idS`),
  KEY `idS` (`idS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `regrouper`
--

INSERT INTO `regrouper` (`idPa`, `idS`, `quantite`) VALUES
(19, 1, 1),
(20, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `idS` int NOT NULL AUTO_INCREMENT,
  `nomS` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `prixS` double NOT NULL,
  `imageS` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `typeS` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `descriptionS` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`idS`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`idS`, `nomS`, `prixS`, `imageS`, `typeS`, `descriptionS`) VALUES
(1, 'Valorant', 10, 'img/valo.png', 'JEUX', 'Top up Valorant Points'),
(2, 'EA Sports FC', 10, 'img/fc.png', 'JEUX', 'Top up EA Sports FC coins'),
(3, '8 Ball Pool', 10, 'img/8 ball p.png', 'JEUX', 'Top up 8 Ball Pool coins'),
(4, 'Free Fire', 10, 'img/free fire.png', 'JEUX', 'Top up Free Fire diamonds'),
(5, 'Logo Createur', 10, 'img/LOGO CRE.png', 'LOGO', 'Création de logo sur mesure'),
(6, 'Affiche Marketing', 10, 'img/AFICHE.webp', 'LOGO', 'Création affiche / visuel pub'),
(7, 'Ads Facebook', 10, 'img/facebook.png', 'PUBLICITES', 'Boost publicitaire Facebook'),
(8, 'Ads Instagram', 10, 'img/instagrame.png', 'PUBLICITES', 'Boost publicitaire Instagram'),
(9, 'Ads Youtube', 10, 'img/youtube.png', 'PUBLICITES', 'Boost publicitaire Youtube'),
(10, 'STEG', 10, 'img/STEG.jpg', 'PAIEMENT', 'Paiement facture STEG'),
(11, 'SONEDE', 10, 'img/SONEDE.webp', 'PAIEMENT', 'Paiement facture SONEDE'),
(12, 'Ecole', 10, 'img/ecole.png', 'PAIEMENT', 'Paiement frais scolarité'),
(13, 'Topnet', 10, 'img/topnet.webp', 'PAIEMENT', 'Paiement facture Topnet'),
(14, 'Tunisie Autoroutes', 10, 'img/tunis.webp', 'PAIEMENT', 'Paiement vignette autoroute');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `idU` int NOT NULL AUTO_INCREMENT,
  `nomU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `prenomU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `emailU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `mdpU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `telU` int NOT NULL,
  `adrU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `dateNU` date NOT NULL,
  `photoU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `sexeU` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `remember_token` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL,
  `banniU` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idU`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`idU`, `nomU`, `prenomU`, `emailU`, `mdpU`, `telU`, `adrU`, `dateNU`, `photoU`, `sexeU`, `remember_token`, `remember_expires`, `banniU`) VALUES
(1, 'dali', 'bejaoui', 'dalibejaoui80@gmail.com', '$2y$10$mwsuvEMBH/GarsEsozDz3uvdQNHWgLURq8JZImqNQ/TDKTKPADb3.', 54965599, 'douar hicher', '2001-02-20', 'default.png', 'Homme', NULL, NULL, 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`idPa`) REFERENCES `panier` (`idPa`) ON DELETE CASCADE;

--
-- Constraints for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `reclamation_ibfk_1` FOREIGN KEY (`idU`) REFERENCES `utilisateur` (`idU`) ON DELETE SET NULL,
  ADD CONSTRAINT `reclamation_ibfk_2` FOREIGN KEY (`idPa`) REFERENCES `panier` (`idPa`) ON DELETE SET NULL;

--
-- Constraints for table `regrouper`
--
ALTER TABLE `regrouper`
  ADD CONSTRAINT `regrouper_ibfk_1` FOREIGN KEY (`idPa`) REFERENCES `panier` (`idPa`) ON DELETE CASCADE,
  ADD CONSTRAINT `regrouper_ibfk_2` FOREIGN KEY (`idS`) REFERENCES `service` (`idS`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
