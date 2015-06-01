-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Version du serveur: 5.5.36-cll-lve
-- Version de PHP: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `sbm`
--

-- Remplacer `dafapfr_bossdemo` par le user de la base

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_calendar`
--

DROP TABLE IF EXISTS `sbm_s_calendar`;
CREATE TABLE IF NOT EXISTS `sbm_s_calendar` (
  `calendarId` int(11) NOT NULL AUTO_INCREMENT,
  `ouvert` tinyint(1) NOT NULL DEFAULT '0',
  `millesime` int(4) NOT NULL,
  `ordinal` tinyint(3) NOT NULL,
  `nature` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `rang` tinyint(3) NOT NULL DEFAULT '1',
  `libelle` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `echeance` date DEFAULT NULL,
  `exercice` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`calendarId`),
  UNIQUE KEY `millesime-ordinal` (`millesime`,`ordinal`),
  UNIQUE KEY `millesime-nature` (`millesime`,`nature`,`rang`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_docaffectations`
--

DROP TABLE IF EXISTS `sbm_s_docaffectations`;
CREATE TABLE IF NOT EXISTS `sbm_s_docaffectations` (
  `docaffectationId` int(11) NOT NULL AUTO_INCREMENT,
  `documentId` int(11) NOT NULL,
  `methodeAction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`docaffectationId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_doccolumns`
--

DROP TABLE IF EXISTS `sbm_s_doccolumns`;
CREATE TABLE IF NOT EXISTS `sbm_s_doccolumns` (
  `doccolumnId` int(11) NOT NULL AUTO_INCREMENT,
  `documentId` int(11) NOT NULL DEFAULT '1',
  `ordinal_table` int(11) NOT NULL DEFAULT '1',
  `ordinal_position` int(11) NOT NULL DEFAULT '1',
  `thead` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `thead_align` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'standard',
  `thead_stretch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `thead_precision` tinyint(3) NOT NULL DEFAULT '-1',
  `thead_completion` tinyint(3) NOT NULL DEFAULT '0',
  `tbody` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tbody_align` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'standard',
  `tbody_stretch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tbody_precision` tinyint(3) NOT NULL DEFAULT '-1',
  `tbody_completion` tinyint(3) NOT NULL DEFAULT '0',
  `tfoot` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tfoot_align` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'standard',
  `tfoot_stretch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tfoot_precision` tinyint(3) NOT NULL DEFAULT '-1',
  `tfoot_completion` tinyint(3) NOT NULL DEFAULT '0',
  `filter` text COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `truncate` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`doccolumnId`),
  KEY `documentId` (`documentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_docfields`
--

DROP TABLE IF EXISTS `sbm_s_docfields`;
CREATE TABLE IF NOT EXISTS `sbm_s_docfields` (
  `docfieldId` int(11) NOT NULL AUTO_INCREMENT,
  `documentId` int(11) NOT NULL,
  `fieldname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`docfieldId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_doctables`
--

DROP TABLE IF EXISTS `sbm_s_doctables`;
CREATE TABLE IF NOT EXISTS `sbm_s_doctables` (
  `doctableId` int(11) NOT NULL AUTO_INCREMENT,
  `documentId` int(11) NOT NULL DEFAULT '1',
  `ordinal_table` int(11) NOT NULL DEFAULT '1',
  `section` char(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `width` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `row_height` int(11) NOT NULL DEFAULT '6',
  `cell_border` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `cell_align` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'L',
  `cell_link` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cell_stretch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cell_ignore_min_height` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cell_calign` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'T',
  `cell_valign` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M',
  `draw_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'black',
  `line_width` float(2,1) NOT NULL DEFAULT '0.1',
  `fill_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'E0EBFF',
  `text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'black',
  `font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`doctableId`),
  KEY `documentId` (`documentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_documents`
--

DROP TABLE IF EXISTS `sbm_s_documents`;
CREATE TABLE IF NOT EXISTS `sbm_s_documents` (
  `documentId` int(11) NOT NULL AUTO_INCREMENT,
  `type` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pdf',
  `disposition` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Tabulaire',
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `out_mode` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'I',
  `out_name` varchar(32) COLLATE utf8_unicode_ci DEFAULT 'document-sbm.pdf',
  `recordSource` text COLLATE utf8_unicode_ci NOT NULL,
  `recordSourceType` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'T',
  `filter` text COLLATE utf8_unicode_ci,
  `orderBy` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_path_images` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/public/img/',
  `image_blank` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '_blank.png',
  `docheader` tinyint(1) NOT NULL DEFAULT '0',
  `docfooter` tinyint(1) NOT NULL DEFAULT '0',
  `pageheader` tinyint(1) NOT NULL DEFAULT '0',
  `pagefooter` tinyint(1) NOT NULL DEFAULT '0',
  `creator` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SchoolBusManager',
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `docheader_subtitle` text COLLATE utf8_unicode_ci,
  `docheader_page_distincte` tinyint(1) NOT NULL DEFAULT '1',
  `docheader_margin` int(11) NOT NULL DEFAULT '20',
  `docheader_pageheader` tinyint(1) NOT NULL DEFAULT '0',
  `docheader_pagefooter` tinyint(1) NOT NULL DEFAULT '0',
  `docheader_templateId` int(11) NOT NULL DEFAULT '1',
  `docfooter_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `docfooter_string` text COLLATE utf8_unicode_ci,
  `docfooter_page_distincte` tinyint(1) NOT NULL DEFAULT '1',
  `docfooter_insecable` tinyint(1) NOT NULL DEFAULT '1',
  `docfooter_margin` int(11) NOT NULL DEFAULT '20',
  `docfooter_pageheader` tinyint(1) NOT NULL DEFAULT '0',
  `docfooter_pagefooter` tinyint(1) NOT NULL DEFAULT '0',
  `docfooter_templateId` int(11) NOT NULL DEFAULT '1',
  `pageheader_templateId` int(11) NOT NULL DEFAULT '1',
  `pageheader_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pageheader_string` text COLLATE utf8_unicode_ci,
  `pageheader_logo_visible` tinyint(1) NOT NULL DEFAULT '1',
  `pageheader_logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sbm-logo.gif',
  `pageheader_logo_width` int(11) NOT NULL DEFAULT '15',
  `pageheader_margin` int(11) NOT NULL DEFAULT '5',
  `pageheader_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `pageheader_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pageheader_font_size` int(11) NOT NULL DEFAULT '11',
  `pageheader_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `pageheader_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `pagefooter_templateId` int(11) NOT NULL DEFAULT '1',
  `pagefooter_margin` int(11) NOT NULL DEFAULT '10',
  `pagefooter_string` text COLLATE utf8_unicode_ci,
  `pagefooter_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `pagefooter_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pagefooter_font_size` int(11) NOT NULL DEFAULT '11',
  `pagefooter_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `pagefooter_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `page_templateId` int(11) NOT NULL DEFAULT '1',
  `page_format` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A4',
  `page_orientation` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'P',
  `page_margin_top` int(11) NOT NULL DEFAULT '27',
  `page_margin_bottom` int(11) NOT NULL DEFAULT '25',
  `page_margin_left` int(11) NOT NULL DEFAULT '15',
  `page_margin_right` int(11) NOT NULL DEFAULT '15',
  `main_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `main_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `main_font_size` int(11) NOT NULL DEFAULT '11',
  `data_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `data_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `data_font_size` int(11) NOT NULL DEFAULT '8',
  `titre1_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `titre1_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titre1_font_size` int(11) NOT NULL DEFAULT '14',
  `titre1_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre1_line` tinyint(1) NOT NULL DEFAULT '0',
  `titre1_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre2_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `titre2_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titre2_font_size` int(11) NOT NULL DEFAULT '13',
  `titre2_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre2_line` tinyint(1) NOT NULL DEFAULT '0',
  `titre2_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre3_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `titre3_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titre3_font_size` int(11) NOT NULL DEFAULT '12',
  `titre3_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre3_line` tinyint(1) NOT NULL DEFAULT '0',
  `titre3_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre4_font_family` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'helvetica',
  `titre4_font_style` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titre4_font_size` int(11) NOT NULL DEFAULT '11',
  `titre4_text_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `titre4_line` tinyint(1) NOT NULL DEFAULT '0',
  `titre4_line_color` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '000000',
  `default_font_monospaced` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'courier',
  PRIMARY KEY (`documentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_history`
--

DROP TABLE IF EXISTS `sbm_s_history`;
CREATE TABLE IF NOT EXISTS `sbm_s_history` (
  `table_name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` char(6) COLLATE utf8_unicode_ci NOT NULL,
  `id_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_int` int(11) NOT NULL DEFAULT '0',
  `id_txt` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dt` datetime NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  KEY `HISTORY_Table` (`table_name`,`dt`),
  KEY `HISTORY_Table_IndexInt` (`table_name`,`id_name`,`id_int`,`dt`),
  KEY `HISTORY_Table_IndexTxt` (`table_name`,`id_name`,`id_txt`,`dt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_s_libelles`
--

DROP TABLE IF EXISTS `sbm_s_libelles`;
CREATE TABLE IF NOT EXISTS `sbm_s_libelles` (
  `nature` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `code` int(11) NOT NULL DEFAULT '1',
  `libelle` text COLLATE utf8_unicode_ci NOT NULL,
  `ouvert` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nature`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_affectations`
--

DROP TABLE IF EXISTS `sbm_t_affectations`;
CREATE TABLE IF NOT EXISTS `sbm_t_affectations` (
  `millesime` int(4) NOT NULL DEFAULT '0',
  `eleveId` int(11) NOT NULL DEFAULT '0',
  `trajet` tinyint(1) NOT NULL DEFAULT '1',
  `jours` tinyint(2) NOT NULL DEFAULT '31',
  `sens` tinyint(1) NOT NULL DEFAULT '3',
  `correspondance` tinyint(1) NOT NULL DEFAULT '1',
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `responsableId` int(11) NOT NULL,
  `station1Id` int(11) NOT NULL,
  `service1Id` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `station2Id` int(11) DEFAULT NULL,
  `service2Id` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`millesime`,`eleveId`,`trajet`,`jours`,`sens`,`correspondance`),
  KEY `responsableId` (`responsableId`),
  KEY `station1Id` (`station1Id`),
  KEY `service1Id` (`service1Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déclencheurs `sbm_t_affectations`
--
DROP TRIGGER IF EXISTS `affectations_bd_history`;
DELIMITER //
CREATE TRIGGER `affectations_bd_history` BEFORE DELETE ON `sbm_t_affectations`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_affectations', 'delete', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `affectations_bi_history`;
DELIMITER //
CREATE TRIGGER `affectations_bi_history` BEFORE INSERT ON `sbm_t_affectations`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_affectations', 'insert', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', NEW.millesime, NEW.eleveId, NEW.trajet, NEW.jours, NEW.sens, NEW.correspondance), NOW(), CONCAT_WS('|', NEW.selection, NEW.responsableId, NEW.station1Id, NEW.service1Id, NEW.station2Id, NEW.service2Id));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `affectations_bu_history`;
DELIMITER //
CREATE TRIGGER `affectations_bu_history` BEFORE UPDATE ON `sbm_t_affectations`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_affectations', 'update', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_appels`
--

DROP TABLE IF EXISTS `sbm_t_appels`;
CREATE TABLE IF NOT EXISTS `sbm_t_appels` (
  `appelId` int(11) NOT NULL AUTO_INCREMENT,
  `referenceId` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `responsableId` int(11) NOT NULL,
  `eleveId` int(11) NOT NULL,
  PRIMARY KEY (`appelId`),
  KEY `responsableId` (`responsableId`),
  KEY `eleveId` (`eleveId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_circuits`
--

DROP TABLE IF EXISTS `sbm_t_circuits`;
CREATE TABLE IF NOT EXISTS `sbm_t_circuits` (
  `circuitId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `millesime` int(11) NOT NULL,
  `serviceId` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `stationId` int(11) NOT NULL,
  `semaine` tinyint(4) unsigned NOT NULL DEFAULT '31',
  `m1` time DEFAULT NULL COMMENT 'Aller (4 jours)',
  `s1` time DEFAULT NULL COMMENT 'Retour (4 jours)',
  `m2` time DEFAULT NULL COMMENT 'Aller (Me)',
  `s2` time DEFAULT NULL COMMENT 'Retour (Me)',
  `m3` time DEFAULT NULL COMMENT 'Aller (Sa)',
  `s3` time DEFAULT NULL COMMENT 'Retour (Sa)',
  `distance` decimal(7,3) NOT NULL DEFAULT '0.000',
  `montee` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `descente` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `typeArret` text COLLATE utf8_unicode_ci,
  `commentaire1` text COLLATE utf8_unicode_ci,
  `commentaire2` text COLLATE utf8_unicode_ci NOT NULL,
  `geopt` geometry DEFAULT NULL,
  PRIMARY KEY (`circuitId`),
  UNIQUE KEY `milsersta` (`millesime`,`serviceId`,`stationId`),
  KEY `serviceId` (`serviceId`),
  KEY `stationId` (`stationId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_classes`
--

DROP TABLE IF EXISTS `sbm_t_classes`;
CREATE TABLE IF NOT EXISTS `sbm_t_classes` (
  `classeId` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `aliasCG` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `niveau` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`classeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_communes`
--

DROP TABLE IF EXISTS `sbm_t_communes`;
CREATE TABLE IF NOT EXISTS `sbm_t_communes` (
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `nom_min` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias_min` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aliasCG` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codePostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `departement` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `canton` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `membre` tinyint(1) NOT NULL DEFAULT '0',
  `desservie` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `population` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`communeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_eleves`
--

DROP TABLE IF EXISTS `sbm_t_eleves`;
CREATE TABLE IF NOT EXISTS `sbm_t_eleves` (
  `eleveId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `dateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `nom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `nomSA` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `prenomSA` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `dateN` date NOT NULL,
  `numero` int(11) NOT NULL,
  `responsable1Id` int(11) NOT NULL DEFAULT '0',
  `x1` decimal(18,10) NOT NULL DEFAULT '1641520.6000000000',
  `y1` decimal(18,10) NOT NULL DEFAULT '3262032.5000000000',
  `geopt1` geometry DEFAULT NULL,
  `responsable2Id` int(11) DEFAULT NULL,
  `x2` decimal(18,10) DEFAULT NULL,
  `y2` decimal(18,10) DEFAULT NULL,
  `geopt2` geometry DEFAULT NULL,
  `responsableFId` int(11) DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `id_ccda` int(11) DEFAULT NULL,
  PRIMARY KEY (`eleveId`),
  KEY `responsable1Id` (`responsable1Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Déclencheurs `sbm_t_eleves`
--
DROP TRIGGER IF EXISTS `eleves_bd_history`;
DELIMITER //
CREATE TRIGGER `eleves_bd_history` BEFORE DELETE ON `sbm_t_eleves`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_eleves', 'delete', 'eleveId', OLD.eleveId, NOW(), CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, '|', OLD.numero, '|', OLD.responsable1Id, '|', OLD.responsable2Id, '|', OLD.responsableFId));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `eleves_bi_history`;
DELIMITER //
CREATE TRIGGER `eleves_bi_history` BEFORE INSERT ON `sbm_t_eleves`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_eleves', 'insert', 'eleveId', NEW.eleveId, NOW(), CONCAT(NEW.selection, '|', NEW.dateCreation, '|', NEW.dateModification, '|', NEW.nom, '|', NEW.nomSA, '|', NEW.prenom, '|', NEW.prenomSA, '|', NEW.dateN, '|', NEW.numero, '|', NEW.responsable1Id, '|', NEW.responsable2Id, '|', NEW.responsableFId));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `eleves_bu_history`;
DELIMITER //
CREATE TRIGGER `eleves_bu_history` BEFORE UPDATE ON `sbm_t_eleves`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_eleves', 'update', 'eleveId', OLD.eleveId, NOW(), CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, '|', OLD.numero, '|', OLD.responsable1Id, '|', OLD.responsable2Id, '|', OLD.responsableFId));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_etablissements`
--

DROP TABLE IF EXISTS `sbm_t_etablissements`;
CREATE TABLE IF NOT EXISTS `sbm_t_etablissements` (
  `etablissementId` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `nom` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `aliasCG` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `adresse1` varchar(38) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `adresse2` varchar(38) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `codePostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `statut` tinyint(1) NOT NULL DEFAULT '1',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `desservie` tinyint(1) NOT NULL DEFAULT '1',
  `regrPeda` tinyint(1) NOT NULL DEFAULT '0',
  `rattacheA` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telephone` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `directeur` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `jOuverture` tinyint(3) unsigned NOT NULL DEFAULT '127',
  `hMatin` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hMidi` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hAMidi` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hSoir` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hGarderieOMatin` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hGarderieFMidi` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hGarderieFSoir` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `x` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `y` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `geopt` geometry DEFAULT NULL,
  PRIMARY KEY (`etablissementId`),
  KEY `communeId` (`communeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_etablissements-services`
--

DROP TABLE IF EXISTS `sbm_t_etablissements-services`;
CREATE TABLE IF NOT EXISTS `sbm_t_etablissements-services` (
  `etablissementId` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `serviceId` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`etablissementId`,`serviceId`),
  KEY `serviceId` (`serviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_paiements`
--

DROP TABLE IF EXISTS `sbm_t_paiements`;
CREATE TABLE IF NOT EXISTS `sbm_t_paiements` (
  `paiementId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(4) NOT NULL DEFAULT '0',
  `dateDepot` datetime DEFAULT NULL,
  `datePaiement` datetime NOT NULL,
  `dateValeur` date DEFAULT NULL,
  `responsableId` int(11) NOT NULL,
  `anneeScolaire` varchar(9) COLLATE utf8_unicode_ci NOT NULL,
  `exercice` smallint(4) NOT NULL,
  `montant` decimal(11,2) NOT NULL,
  `codeModeDePaiement` int(11) NOT NULL,
  `codeCaisse` int(11) NOT NULL,
  `banque` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titulaire` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reference` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `note` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`paiementId`),
  UNIQUE KEY `PAIEMENTS_date_reference` (`datePaiement`,`reference`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Déclencheurs `sbm_t_paiements`
--
DROP TRIGGER IF EXISTS `paiements_bd_history`;
DELIMITER //
CREATE TRIGGER `paiements_bd_history` BEFORE DELETE ON `sbm_t_paiements`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_paiements', 'delete', 'paiementId', OLD.paiementId, NOW(), CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|', IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire, '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|', OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference, '|', IFNULL(OLD.note, '')));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `paiements_bi_history`;
DELIMITER //
CREATE TRIGGER `paiements_bi_history` BEFORE INSERT ON `sbm_t_paiements`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_paiements', 'insert', 'paiementId', NEW.paiementId, NOW(), CONCAT(IFNULL(NEW.dateDepot, ''), '|', NEW.datePaiement, '|', IFNULL(NEW.dateValeur, ''), '|', NEW.responsableId, '|', NEW.anneeScolaire, '|', NEW.exercice, '|', NEW.montant, '|', NEW.codeModeDePaiement, '|', NEW.codeCaisse, '|', NEW.banque, '|', NEW.titulaire, '|', NEW.reference));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `paiements_bu_history`;
DELIMITER //
CREATE TRIGGER `paiements_bu_history` BEFORE UPDATE ON `sbm_t_paiements`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_paiements', 'update', 'paiementId', OLD.paiementId, NOW(), CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|', IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire, '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|', OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference, '|', IFNULL(NEW.note, '')));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_responsables`
--

DROP TABLE IF EXISTS `sbm_t_responsables`;
CREATE TABLE IF NOT EXISTS `sbm_t_responsables` (
  `responsableId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `nature` tinyint(1) NOT NULL DEFAULT '0',
  `titre` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M.',
  `nom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `nomSA` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prenomSA` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titre2` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nom2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nom2SA` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prenom2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prenom2SA` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `adresseL1` varchar(38) COLLATE utf8_unicode_ci NOT NULL,
  `adresseL2` varchar(38) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `codePostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `ancienAdresseL1` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ancienAdresseL2` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ancienCodePostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ancienCommuneId` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telephoneF` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telephoneP` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telephoneT` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `etiquette` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `demenagement` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dateDemenagement` date NOT NULL DEFAULT '1900-01-01',
  `facture` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `grilleTarif` int(4) NOT NULL DEFAULT '1',
  `ribTit` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ribDom` varchar(24) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `iban` varchar(34) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bic` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `x` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `y` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `userId` int(11) NOT NULL DEFAULT '3',
  `id_ccda` int(11) DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`responsableId`),
  UNIQUE KEY `RESPONSABLE_email` (`email`),
  KEY `communeId` (`communeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Déclencheurs `sbm_t_responsables`
--
DROP TRIGGER IF EXISTS `responsables_bd_history`;
DELIMITER //
CREATE TRIGGER `responsables_bd_history` BEFORE DELETE ON `sbm_t_responsables`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_responsables', 'delete', 'responsableId', OLD.responsableId, NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal, OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP, OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement, OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic, OLD.x, OLD.y, OLD.userId, OLD.note));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `responsables_bi_history`;
DELIMITER //
CREATE TRIGGER `responsables_bi_history` BEFORE INSERT ON `sbm_t_responsables`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_responsables', 'insert', 'responsableId', NEW.responsableId, NOW(), CONCAT_WS('|', NEW.selection, NEW.dateCreation, NEW.dateModification, NEW.nature, NEW.titre, NEW.nom, NEW.nomSA, NEW.prenom, NEW.prenomSA, NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId, NEW.ancienAdresseL1, NEW.ancienAdresseL2, NEW.ancienCodePostal, NEW.ancienCommuneId, NEW.email, NEW.telephoneF, NEW.telephoneP, NEW.telephoneT, NEW.etiquette, NEW.demenagement, NEW.dateDemenagement, NEW.facture, NEW.grilleTarif, NEW.ribTit, NEW.ribDom, NEW.iban, NEW.bic, NEW.x, NEW.y, NEW.userId, NEW.note));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `responsables_bu_history`;
DELIMITER //
CREATE TRIGGER `responsables_bu_history` BEFORE UPDATE ON `sbm_t_responsables`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_responsables', 'update', 'responsableId', OLD.responsableId, NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal, OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP, OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement, OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic, OLD.x, OLD.y, OLD.userId, OLD.note));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_scolarites`
--

DROP TABLE IF EXISTS `sbm_t_scolarites`;
CREATE TABLE IF NOT EXISTS `sbm_t_scolarites` (
  `millesime` int(4) NOT NULL DEFAULT '0',
  `eleveId` int(11) NOT NULL DEFAULT '0',
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `dateInscription` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `etablissementId` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `classeId` int(11) NOT NULL DEFAULT '0',
  `chez` varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adresseL1` varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adresseL2` varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codePostal` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `communeId` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `x` decimal(18,10) NOT NULL DEFAULT '1641520.6000000000',
  `y` decimal(18,10) NOT NULL DEFAULT '3262032.5000000000',
  `geopt` geometry DEFAULT NULL,
  `distanceR1` decimal(7,3) NOT NULL DEFAULT '0.000',
  `distanceR2` decimal(7,3) NOT NULL DEFAULT '0.000',
  `dateEtiquette` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `dateCarte` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `inscrit` tinyint(1) NOT NULL DEFAULT '1',
  `gratuit` tinyint(1) NOT NULL DEFAULT '0',
  `paiement` tinyint(1) NOT NULL DEFAULT '1',
  `fa` tinyint(1) NOT NULL DEFAULT '0',
  `anneeComplete` tinyint(1) NOT NULL DEFAULT '1',
  `subventionR1` tinyint(1) NOT NULL DEFAULT '0',
  `subventionR2` tinyint(1) NOT NULL DEFAULT '0',
  `demandeR1` tinyint(1) NOT NULL DEFAULT '1',
  `demandeR2` tinyint(1) NOT NULL DEFAULT '0',
  `accordR1` tinyint(1) NOT NULL DEFAULT '1',
  `accordR2` tinyint(1) NOT NULL DEFAULT '1',
  `internet` tinyint(1) NOT NULL DEFAULT '1',
  `district` tinyint(1) NOT NULL DEFAULT '0',
  `derogation` tinyint(1) NOT NULL DEFAULT '0',
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `joursTransport` tinyint(3) unsigned NOT NULL DEFAULT '127',
  `subventionTaux` int(3) NOT NULL DEFAULT '0',
  `tarifId` int(11) NOT NULL DEFAULT '0',
  `regimeId` tinyint(1) NOT NULL DEFAULT '0',
  `motifDerogation` text COLLATE utf8_unicode_ci,
  `motifRefusR1` text COLLATE utf8_unicode_ci NOT NULL,
  `motifRefusR2` text COLLATE utf8_unicode_ci NOT NULL,
  `commentaire` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`millesime`,`eleveId`),
  KEY `eleveId` (`eleveId`),
  KEY `etablissementId` (`etablissementId`),
  KEY `classeId` (`classeId`),
  KEY `communeId` (`communeId`),
  KEY `tarifId` (`tarifId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déclencheurs `sbm_t_scolarites`
--
DROP TRIGGER IF EXISTS `scolarites_bd_history`;
DELIMITER //
CREATE TRIGGER `scolarites_bd_history` BEFORE DELETE ON `sbm_t_scolarites`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_scolarites', 'delete', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `scolarites_bi_history`;
DELIMITER //
CREATE TRIGGER `scolarites_bi_history` BEFORE INSERT ON `sbm_t_scolarites`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_scolarites', 'insert', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', NEW.millesime, NEW.eleveId), NOW(), CONCAT_WS('|', NEW.selection, NEW.dateInscription, NEW.dateModification, NEW.etablissementId, NEW.classeId, NEW.chez, NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId, NEW.x, NEW.y, NEW.distanceR1, NEW.distanceR2, NEW.dateEtiquette, NEW.dateCarte, NEW.inscrit, NEW.gratuit, NEW.paiement, NEW.anneeComplete, NEW.subventionR1, NEW.subventionR2, NEW.demandeR1, NEW.demandeR2, NEW.accordR1, NEW.accordR2, NEW.internet, NEW.district, NEW.derogation, NEW.dateDebut, NEW.dateFin, NEW.joursTransport, NEW.subventionTaux, NEW.tarifId, NEW.regimeId, NEW.motifDerogation, NEW.motifRefusR1, NEW.motifRefusR2, NEW.commentaire));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `scolarites_bu_history`;
DELIMITER //
CREATE TRIGGER `scolarites_bu_history` BEFORE UPDATE ON `sbm_t_scolarites`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)
VALUES ('sbm_t_scolarites', 'update', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_secteurs-scolaires-clg-pu`
--

DROP TABLE IF EXISTS `sbm_t_secteurs-scolaires-clg-pu`;
CREATE TABLE IF NOT EXISTS `sbm_t_secteurs-scolaires-clg-pu` (
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `etablissementId` char(8) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`communeId`,`etablissementId`),
  KEY `etablissementId` (`etablissementId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_services`
--

DROP TABLE IF EXISTS `sbm_t_services`;
CREATE TABLE IF NOT EXISTS `sbm_t_services` (
  `serviceId` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `nom` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `aliasCG` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `transporteurId` int(11) NOT NULL DEFAULT '0',
  `nbPlaces` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `surEtatCG` tinyint(1) NOT NULL DEFAULT '0',
  `operateur` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CCDA',
  `kmAVide` decimal(7,3) NOT NULL DEFAULT '0.000',
  `kmEnCharge` decimal(7,3) NOT NULL DEFAULT '0.000',
  `geotrajet` polygon DEFAULT NULL,
  PRIMARY KEY (`serviceId`),
  KEY `transporteurId` (`transporteurId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_stations`
--

DROP TABLE IF EXISTS `sbm_t_stations`;
CREATE TABLE IF NOT EXISTS `sbm_t_stations` (
  `stationId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `aliasCG` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `codeCG` int(11) NOT NULL DEFAULT '0',
  `x` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `y` decimal(18,10) NOT NULL DEFAULT '0.0000000000',
  `geopt` geometry DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `ouverte` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`stationId`),
  KEY `communeId` (`communeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_systempay`
--

DROP TABLE IF EXISTS `sbm_t_systempay`;
CREATE TABLE IF NOT EXISTS `sbm_t_systempay` (
  `systempayId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `vads_ctx_mode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `vads_operation_type` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `vads_trans_date` char(14) COLLATE utf8_unicode_ci NOT NULL,
  `vads_trans_id` char(6) COLLATE utf8_unicode_ci NOT NULL,
  `vads_trans_status` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `vads_result` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `vads_extra_result` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vads_auth_result` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `vads_auth_number` char(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `vads_cust_email` char(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_cust_id` int(11) NOT NULL,
  `vads_cust_last_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `vads_cust_first_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `vads_order_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ref_eleveIds` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_payment_certificate` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `vads_payment_config` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_payment_error` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vads_sequence_number` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `vads_capture_delay` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vads_amount` int(11) NOT NULL DEFAULT '0',
  `vads_currency` char(3) COLLATE utf8_unicode_ci DEFAULT '978',
  `vads_threeds_enrolled` char(1) COLLATE utf8_unicode_ci DEFAULT 'U',
  `vads_threeds_status` char(1) COLLATE utf8_unicode_ci DEFAULT 'U',
  `vads_card_brand` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_card_country` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_card_number` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_expiry_month` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_expiry_year` char(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_bank_code` char(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vads_bank_product` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`systempayId`),
  UNIQUE KEY `SYSTEMPAY_date_id` (`vads_trans_date`,`vads_trans_id`),
  KEY `SYSTEMPAY_cust_id` (`vads_cust_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_tarifs`
--

DROP TABLE IF EXISTS `sbm_t_tarifs`;
CREATE TABLE IF NOT EXISTS `sbm_t_tarifs` (
  `tarifId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `montant` decimal(10,2) NOT NULL DEFAULT '0.00',
  `nom` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `rythme` int(4) NOT NULL DEFAULT '1',
  `grille` int(4) NOT NULL DEFAULT '1',
  `mode` int(4) NOT NULL DEFAULT '3',
  PRIMARY KEY (`tarifId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_transporteurs`
--

DROP TABLE IF EXISTS `sbm_t_transporteurs`;
CREATE TABLE IF NOT EXISTS `sbm_t_transporteurs` (
  `transporteurId` int(11) NOT NULL AUTO_INCREMENT,
  `selection` tinyint(1) NOT NULL DEFAULT '0',
  `nom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `adresse1` varchar(38) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `adresse2` varchar(38) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `codePostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `communeId` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `siret` varchar(14) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `naf` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tvaIntraCommunautaire` varchar(13) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rib_titulaire` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rib_domiciliation` varchar(24) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rib_bic` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rib_iban` varchar(34) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`transporteurId`),
  KEY `communeId` (`communeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sbm_t_users`
--

DROP TABLE IF EXISTS `sbm_t_users`;
CREATE TABLE IF NOT EXISTS `sbm_t_users` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tokenalive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `confirme` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `selection` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `dateLastLogin` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `datePreviousLogin` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `adresseIp` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `previousIp` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `categorieId` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `titre` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'M.',
  `nom` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `mdp` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `gds` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `note` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `USER_Email` (`email`),
  UNIQUE KEY `USER_Token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Déclencheurs `sbm_t_users`
--
DROP TRIGGER IF EXISTS `users_bd_history`;
DELIMITER //
CREATE TRIGGER `users_bd_history` BEFORE DELETE ON `sbm_t_users`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_users', 'delete', 'userId', OLD.userId, NOW(), CONCAT(OLD.token, '|', OLD.tokenalive, '|', OLD.confirme, '|', OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin, '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|', OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp, '|', OLD.gds, '|', OLD.note));
 END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `users_bu_history`;
DELIMITER //
CREATE TRIGGER `users_bu_history` BEFORE UPDATE ON `sbm_t_users`
 FOR EACH ROW BEGIN
 INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)
VALUES ('sbm_t_users', 'update', 'userId', OLD.userId, NOW(), CONCAT(OLD.token, '|', OLD.tokenalive, '|', OLD.confirme, '|', OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin, '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|', OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp, '|', OLD.gds, '|', OLD.note));
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_circuits`
--
DROP VIEW IF EXISTS `sbm_v_circuits`;
CREATE TABLE IF NOT EXISTS `sbm_v_circuits` (
`circuitId` int(11)
,`selection` tinyint(1)
,`millesime` int(11)
,`serviceId` varchar(11)
,`stationId` int(11)
,`semaine` tinyint(4) unsigned
,`m1` time
,`s1` time
,`m2` time
,`s2` time
,`m3` time
,`s3` time
,`distance` decimal(7,3)
,`montee` tinyint(1) unsigned
,`descente` tinyint(1) unsigned
,`typeArret` text
,`commentaire1` text
,`commentaire2` text
,`service` varchar(45)
,`nbPlaces` tinyint(3) unsigned
,`operateur` varchar(4)
,`kmAVide` decimal(7,3)
,`kmEnCharge` decimal(7,3)
,`transporteurId` int(11)
,`transporteur` varchar(30)
,`communeTransporteur` varchar(45)
,`station` varchar(45)
,`stationOuverte` tinyint(1)
,`stationVisible` tinyint(1)
,`communeStation` varchar(45)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_eleves`
--
DROP VIEW IF EXISTS `sbm_v_eleves`;
CREATE TABLE IF NOT EXISTS `sbm_v_eleves` (
`eleveId` int(11)
,`selection` tinyint(1)
,`dateCreation` timestamp
,`dateModification` datetime
,`nom` varchar(30)
,`nomSA` varchar(30)
,`prenom` varchar(30)
,`prenomSA` varchar(30)
,`dateN` date
,`numero` int(11)
,`responsable1Id` int(11)
,`x1` decimal(18,10)
,`y1` decimal(18,10)
,`responsable2Id` int(11)
,`x2` decimal(18,10)
,`y2` decimal(18,10)
,`responsableFId` int(11)
,`note` text
,`millesime` int(4)
,`etablissementId` char(8)
,`classeId` int(11)
,`inscrit` tinyint(1)
,`paiement` tinyint(1)
,`district` tinyint(1)
,`derogation` tinyint(1)
,`distanceR1` decimal(7,3)
,`distanceR2` decimal(7,3)
,`demandeR1` tinyint(1)
,`demandeR2` tinyint(1)
,`accordR1` tinyint(1)
,`accordR2` tinyint(1)
,`subventionR1` tinyint(1)
,`subventionR2` tinyint(1)
,`etablissement` varchar(45)
,`communeEtablissement` varchar(45)
,`responsable1NomPrenom` varchar(61)
,`responsable2NomPrenom` varchar(61)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_etablissements`
--
DROP VIEW IF EXISTS `sbm_v_etablissements`;
CREATE TABLE IF NOT EXISTS `sbm_v_etablissements` (
`etablissementId` char(8)
,`selection` tinyint(1)
,`nom` varchar(45)
,`alias` varchar(30)
,`aliasCG` varchar(50)
,`adresse1` varchar(38)
,`adresse2` varchar(38)
,`codePostal` varchar(5)
,`communeId` varchar(6)
,`niveau` tinyint(3) unsigned
,`statut` tinyint(1)
,`visible` tinyint(1)
,`desservie` tinyint(1)
,`regrPeda` tinyint(1)
,`rattacheA` varchar(8)
,`telephone` varchar(10)
,`fax` varchar(10)
,`email` varchar(80)
,`directeur` varchar(30)
,`jOuverture` tinyint(3) unsigned
,`hMatin` varchar(5)
,`hMidi` varchar(5)
,`hAMidi` varchar(5)
,`hSoir` varchar(5)
,`hGarderieOMatin` varchar(5)
,`hGarderieFMidi` varchar(5)
,`hGarderieFSoir` varchar(5)
,`x` decimal(18,10)
,`y` decimal(18,10)
,`commune` varchar(45)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_etablissements-services`
--
DROP VIEW IF EXISTS `sbm_v_etablissements-services`;
CREATE TABLE IF NOT EXISTS `sbm_v_etablissements-services` (
`etablissementId` char(8)
,`serviceId` varchar(11)
,`etab_nom` varchar(45)
,`etab_alias` varchar(30)
,`etab_aliasCG` varchar(50)
,`etab_adresse1` varchar(38)
,`etab_adresse2` varchar(38)
,`etab_codePostal` varchar(5)
,`etab_communeId` varchar(6)
,`etab_niveau` tinyint(3) unsigned
,`etab_statut` tinyint(1)
,`etab_visible` tinyint(1)
,`etab_desservie` tinyint(1)
,`etab_regrPeda` tinyint(1)
,`etab_rattacheA` varchar(8)
,`etab_telephone` varchar(10)
,`etab_fax` varchar(10)
,`etab_email` varchar(80)
,`etab_directeur` varchar(30)
,`etab_jOuverture` tinyint(3) unsigned
,`etab_hMatin` varchar(5)
,`etab_hMidi` varchar(5)
,`etab_hAMidi` varchar(5)
,`etab_hSoir` varchar(5)
,`etab_hGarderieOMatin` varchar(5)
,`etab_hGarderieFMidi` varchar(5)
,`etab_hGarderieFSoir` varchar(5)
,`etab_x` decimal(18,10)
,`etab_y` decimal(18,10)
,`etab_commune` varchar(45)
,`serv_nom` varchar(45)
,`serv_aliasCG` varchar(15)
,`serv_transporteurId` int(11)
,`serv_nbPlaces` tinyint(3) unsigned
,`serv_surEtatCG` tinyint(1)
,`serv_operateur` varchar(4)
,`serv_kmAVide` decimal(7,3)
,`serv_kmEnCharge` decimal(7,3)
,`serv_transporteur` varchar(30)
,`serv_communeTransporteur` varchar(45)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_libelles-caisses`
--
DROP VIEW IF EXISTS `sbm_v_libelles-caisses`;
CREATE TABLE IF NOT EXISTS `sbm_v_libelles-caisses` (
`code` int(11)
,`libelle` text
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_libelles-modes-de-paiement`
--
DROP VIEW IF EXISTS `sbm_v_libelles-modes-de-paiement`;
CREATE TABLE IF NOT EXISTS `sbm_v_libelles-modes-de-paiement` (
`code` int(11)
,`libelle` text
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_paiements`
--
DROP VIEW IF EXISTS `sbm_v_paiements`;
CREATE TABLE IF NOT EXISTS `sbm_v_paiements` (
`paiementId` int(11)
,`selection` tinyint(4)
,`dateDepot` datetime
,`datePaiement` datetime
,`dateValeur` date
,`responsableId` int(11)
,`anneeScolaire` varchar(9)
,`exercice` smallint(4)
,`montant` decimal(11,2)
,`codeModeDePaiement` int(11)
,`codeCaisse` int(11)
,`banque` varchar(30)
,`titulaire` varchar(30)
,`reference` varchar(30)
,`responsable` varchar(61)
,`caisse` text
,`modeDePaiement` text
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_responsables`
--
DROP VIEW IF EXISTS `sbm_v_responsables`;
CREATE TABLE IF NOT EXISTS `sbm_v_responsables` (
`responsableId` int(11)
,`selection` tinyint(1) unsigned
,`dateCreation` timestamp
,`dateModification` datetime
,`nature` tinyint(1)
,`titre` varchar(20)
,`nom` varchar(30)
,`nomSA` varchar(30)
,`prenom` varchar(30)
,`prenomSA` varchar(30)
,`titre2` varchar(20)
,`nom2` varchar(30)
,`nom2SA` varchar(30)
,`prenom2` varchar(30)
,`prenom2SA` varchar(30)
,`adresseL1` varchar(38)
,`adresseL2` varchar(38)
,`codePostal` varchar(5)
,`communeId` varchar(6)
,`ancienAdresseL1` varchar(30)
,`ancienAdresseL2` varchar(30)
,`ancienCodePostal` varchar(5)
,`ancienCommuneId` varchar(6)
,`email` varchar(80)
,`telephoneF` varchar(10)
,`telephoneP` varchar(10)
,`telephoneT` varchar(10)
,`etiquette` tinyint(1) unsigned
,`demenagement` tinyint(1) unsigned
,`dateDemenagement` date
,`facture` tinyint(1) unsigned
,`grilleTarif` int(4)
,`ribTit` varchar(32)
,`ribDom` varchar(24)
,`iban` varchar(34)
,`bic` varchar(11)
,`x` decimal(18,10)
,`y` decimal(18,10)
,`userId` int(11)
,`note` text
,`commune` varchar(45)
,`nbEleves` bigint(21)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_services`
--
DROP VIEW IF EXISTS `sbm_v_services`;
CREATE TABLE IF NOT EXISTS `sbm_v_services` (
`serviceId` varchar(11)
,`selection` tinyint(1)
,`nom` varchar(45)
,`aliasCG` varchar(15)
,`transporteurId` int(11)
,`nbPlaces` tinyint(3) unsigned
,`surEtatCG` tinyint(1)
,`operateur` varchar(4)
,`kmAVide` decimal(7,3)
,`kmEnCharge` decimal(7,3)
,`transporteur` varchar(30)
,`communeTransporteur` varchar(45)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_stations`
--
DROP VIEW IF EXISTS `sbm_v_stations`;
CREATE TABLE IF NOT EXISTS `sbm_v_stations` (
`stationId` int(11)
,`selection` tinyint(1)
,`communeId` varchar(6)
,`nom` varchar(45)
,`aliasCG` varchar(45)
,`codeCG` int(11)
,`x` decimal(18,10)
,`y` decimal(18,10)
,`visible` tinyint(1)
,`ouverte` tinyint(1)
,`commune` varchar(45)
);
-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `sbm_v_transporteurs`
--
DROP VIEW IF EXISTS `sbm_v_transporteurs`;
CREATE TABLE IF NOT EXISTS `sbm_v_transporteurs` (
`transporteurId` int(11)
,`selection` tinyint(1)
,`nom` varchar(30)
,`adresse1` varchar(38)
,`adresse2` varchar(38)
,`codePostal` varchar(5)
,`communeId` varchar(6)
,`telephone` varchar(10)
,`fax` varchar(10)
,`email` varchar(80)
,`siret` varchar(14)
,`naf` varchar(5)
,`rib_titulaire` varchar(32)
,`rib_domiciliation` varchar(24)
,`rib_bic` varchar(11)
,`rib_iban` varchar(34)
,`commune` varchar(45)
);
-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_circuits`
--
DROP TABLE IF EXISTS `sbm_v_circuits`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_circuits` AS select `cir`.`circuitId` AS `circuitId`,`cir`.`selection` AS `selection`,`cir`.`millesime` AS `millesime`,`cir`.`serviceId` AS `serviceId`,`cir`.`stationId` AS `stationId`,`cir`.`semaine` AS `semaine`,`cir`.`m1` AS `m1`,`cir`.`s1` AS `s1`,`cir`.`m2` AS `m2`,`cir`.`s2` AS `s2`,`cir`.`m3` AS `m3`,`cir`.`s3` AS `s3`,`cir`.`distance` AS `distance`,`cir`.`montee` AS `montee`,`cir`.`descente` AS `descente`,`cir`.`typeArret` AS `typeArret`,`cir`.`commentaire1` AS `commentaire1`,`cir`.`commentaire2` AS `commentaire2`,`ser`.`nom` AS `service`,`ser`.`nbPlaces` AS `nbPlaces`,`ser`.`operateur` AS `operateur`,`ser`.`kmAVide` AS `kmAVide`,`ser`.`kmEnCharge` AS `kmEnCharge`,`ser`.`transporteurId` AS `transporteurId`,`tra`.`nom` AS `transporteur`,`comtra`.`nom` AS `communeTransporteur`,`sta`.`nom` AS `station`,`sta`.`ouverte` AS `stationOuverte`,`sta`.`visible` AS `stationVisible`,`comsta`.`nom` AS `communeStation` from (((((`sbm_t_circuits` `cir` join `sbm_t_services` `ser` on((`ser`.`serviceId` = `cir`.`serviceId`))) join `sbm_t_transporteurs` `tra` on((`ser`.`transporteurId` = `tra`.`transporteurId`))) join `sbm_t_communes` `comtra` on((`comtra`.`communeId` = `tra`.`communeId`))) join `sbm_t_stations` `sta` on((`sta`.`stationId` = `cir`.`stationId`))) join `sbm_t_communes` `comsta` on((`comsta`.`communeId` = `sta`.`communeId`))) order by `cir`.`serviceId`,`cir`.`m1`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_eleves`
--
DROP TABLE IF EXISTS `sbm_v_eleves`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_eleves` AS select `ele`.`eleveId` AS `eleveId`,`ele`.`selection` AS `selection`,`ele`.`dateCreation` AS `dateCreation`,`ele`.`dateModification` AS `dateModification`,`ele`.`nom` AS `nom`,`ele`.`nomSA` AS `nomSA`,`ele`.`prenom` AS `prenom`,`ele`.`prenomSA` AS `prenomSA`,`ele`.`dateN` AS `dateN`,`ele`.`numero` AS `numero`,`ele`.`responsable1Id` AS `responsable1Id`,`ele`.`x1` AS `x1`,`ele`.`y1` AS `y1`,`ele`.`responsable2Id` AS `responsable2Id`,`ele`.`x2` AS `x2`,`ele`.`y2` AS `y2`,`ele`.`responsableFId` AS `responsableFId`,`ele`.`note` AS `note`,`sco`.`millesime` AS `millesime`,`sco`.`etablissementId` AS `etablissementId`,`sco`.`classeId` AS `classeId`,`sco`.`inscrit` AS `inscrit`,`sco`.`paiement` AS `paiement`,`sco`.`district` AS `district`,`sco`.`derogation` AS `derogation`,`sco`.`distanceR1` AS `distanceR1`,`sco`.`distanceR2` AS `distanceR2`,`sco`.`demandeR1` AS `demandeR1`,`sco`.`demandeR2` AS `demandeR2`,`sco`.`accordR1` AS `accordR1`,`sco`.`accordR2` AS `accordR2`,`sco`.`subventionR1` AS `subventionR1`,`sco`.`subventionR2` AS `subventionR2`,(case when isnull(`eta`.`alias`) then `eta`.`nom` else `eta`.`alias` end) AS `etablissement`,`com`.`nom` AS `communeEtablissement`,concat(`r1`.`nom`,' ',`r1`.`prenom`) AS `responsable1NomPrenom`,(case when isnull(`r2`.`responsableId`) then '' else concat(`r2`.`nom`,' ',`r2`.`prenom`) end) AS `responsable2NomPrenom` from (((((`sbm_t_eleves` `ele` join `sbm_t_scolarites` `sco` on((`sco`.`eleveId` = `ele`.`eleveId`))) join `sbm_t_etablissements` `eta` on((`sco`.`etablissementId` = `eta`.`etablissementId`))) join `sbm_t_communes` `com` on((`com`.`communeId` = `eta`.`communeId`))) join `sbm_t_responsables` `r1` on((`ele`.`responsable1Id` = `r1`.`responsableId`))) left join `sbm_t_responsables` `r2` on((`ele`.`responsable2Id` = `r2`.`responsableId`)));

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_etablissements`
--
DROP TABLE IF EXISTS `sbm_v_etablissements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_etablissements` AS select `eta`.`etablissementId` AS `etablissementId`,`eta`.`selection` AS `selection`,`eta`.`nom` AS `nom`,`eta`.`alias` AS `alias`,`eta`.`aliasCG` AS `aliasCG`,`eta`.`adresse1` AS `adresse1`,`eta`.`adresse2` AS `adresse2`,`eta`.`codePostal` AS `codePostal`,`eta`.`communeId` AS `communeId`,`eta`.`niveau` AS `niveau`,`eta`.`statut` AS `statut`,`eta`.`visible` AS `visible`,`eta`.`desservie` AS `desservie`,`eta`.`regrPeda` AS `regrPeda`,`eta`.`rattacheA` AS `rattacheA`,`eta`.`telephone` AS `telephone`,`eta`.`fax` AS `fax`,`eta`.`email` AS `email`,`eta`.`directeur` AS `directeur`,`eta`.`jOuverture` AS `jOuverture`,`eta`.`hMatin` AS `hMatin`,`eta`.`hMidi` AS `hMidi`,`eta`.`hAMidi` AS `hAMidi`,`eta`.`hSoir` AS `hSoir`,`eta`.`hGarderieOMatin` AS `hGarderieOMatin`,`eta`.`hGarderieFMidi` AS `hGarderieFMidi`,`eta`.`hGarderieFSoir` AS `hGarderieFSoir`,`eta`.`x` AS `x`,`eta`.`y` AS `y`,`com`.`nom` AS `commune` from (`sbm_t_etablissements` `eta` join `sbm_t_communes` `com` on((`com`.`communeId` = `eta`.`communeId`))) order by `com`.`nom`,`eta`.`niveau`,`eta`.`nom`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_etablissements-services`
--
DROP TABLE IF EXISTS `sbm_v_etablissements-services`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_etablissements-services` AS select `rel`.`etablissementId` AS `etablissementId`,`rel`.`serviceId` AS `serviceId`,`eta`.`nom` AS `etab_nom`,`eta`.`alias` AS `etab_alias`,`eta`.`aliasCG` AS `etab_aliasCG`,`eta`.`adresse1` AS `etab_adresse1`,`eta`.`adresse2` AS `etab_adresse2`,`eta`.`codePostal` AS `etab_codePostal`,`eta`.`communeId` AS `etab_communeId`,`eta`.`niveau` AS `etab_niveau`,`eta`.`statut` AS `etab_statut`,`eta`.`visible` AS `etab_visible`,`eta`.`desservie` AS `etab_desservie`,`eta`.`regrPeda` AS `etab_regrPeda`,`eta`.`rattacheA` AS `etab_rattacheA`,`eta`.`telephone` AS `etab_telephone`,`eta`.`fax` AS `etab_fax`,`eta`.`email` AS `etab_email`,`eta`.`directeur` AS `etab_directeur`,`eta`.`jOuverture` AS `etab_jOuverture`,`eta`.`hMatin` AS `etab_hMatin`,`eta`.`hMidi` AS `etab_hMidi`,`eta`.`hAMidi` AS `etab_hAMidi`,`eta`.`hSoir` AS `etab_hSoir`,`eta`.`hGarderieOMatin` AS `etab_hGarderieOMatin`,`eta`.`hGarderieFMidi` AS `etab_hGarderieFMidi`,`eta`.`hGarderieFSoir` AS `etab_hGarderieFSoir`,`eta`.`x` AS `etab_x`,`eta`.`y` AS `etab_y`,`com1`.`nom` AS `etab_commune`,`ser`.`nom` AS `serv_nom`,`ser`.`aliasCG` AS `serv_aliasCG`,`ser`.`transporteurId` AS `serv_transporteurId`,`ser`.`nbPlaces` AS `serv_nbPlaces`,`ser`.`surEtatCG` AS `serv_surEtatCG`,`ser`.`operateur` AS `serv_operateur`,`ser`.`kmAVide` AS `serv_kmAVide`,`ser`.`kmEnCharge` AS `serv_kmEnCharge`,`tra`.`nom` AS `serv_transporteur`,`com2`.`nom` AS `serv_communeTransporteur` from (((((`sbm_t_etablissements-services` `rel` join `sbm_t_etablissements` `eta` on((`rel`.`etablissementId` = `eta`.`etablissementId`))) join `sbm_t_communes` `com1` on((`com1`.`communeId` = `eta`.`communeId`))) join `sbm_t_services` `ser` on((`rel`.`serviceId` = `ser`.`serviceId`))) join `sbm_t_transporteurs` `tra` on((`tra`.`transporteurId` = `ser`.`transporteurId`))) join `sbm_t_communes` `com2` on((`com2`.`communeId` = `tra`.`communeId`))) order by `rel`.`etablissementId`,`rel`.`serviceId`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_libelles-caisses`
--
DROP TABLE IF EXISTS `sbm_v_libelles-caisses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_libelles-caisses` AS select `caisse`.`code` AS `code`,`caisse`.`libelle` AS `libelle` from `sbm_s_libelles` `caisse` where ((`caisse`.`nature` = 'Caisse') and (`caisse`.`ouvert` = 1));

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_libelles-modes-de-paiement`
--
DROP TABLE IF EXISTS `sbm_v_libelles-modes-de-paiement`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_libelles-modes-de-paiement` AS select `mode`.`code` AS `code`,`mode`.`libelle` AS `libelle` from `sbm_s_libelles` `mode` where ((`mode`.`nature` = 'ModeDePaiement') and (`mode`.`ouvert` = 1));

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_paiements`
--
DROP TABLE IF EXISTS `sbm_v_paiements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_paiements` AS select `pai`.`paiementId` AS `paiementId`,`pai`.`selection` AS `selection`,`pai`.`dateDepot` AS `dateDepot`,`pai`.`datePaiement` AS `datePaiement`,`pai`.`dateValeur` AS `dateValeur`,`pai`.`responsableId` AS `responsableId`,`pai`.`anneeScolaire` AS `anneeScolaire`,`pai`.`exercice` AS `exercice`,`pai`.`montant` AS `montant`,`pai`.`codeModeDePaiement` AS `codeModeDePaiement`,`pai`.`codeCaisse` AS `codeCaisse`,`pai`.`banque` AS `banque`,`pai`.`titulaire` AS `titulaire`,`pai`.`reference` AS `reference`,concat(`res`.`nom`,' ',`res`.`prenom`) AS `responsable`,`cai`.`libelle` AS `caisse`,`mod`.`libelle` AS `modeDePaiement` from (((`sbm_t_paiements` `pai` join `sbm_t_responsables` `res` on((`pai`.`responsableId` = `res`.`responsableId`))) join `sbm_v_libelles-caisses` `cai` on((`pai`.`codeCaisse` = `cai`.`code`))) join `sbm_v_libelles-modes-de-paiement` `mod` on((`pai`.`codeModeDePaiement` = `mod`.`code`)));

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_responsables`
--
DROP TABLE IF EXISTS `sbm_v_responsables`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_responsables` AS select `res`.`responsableId` AS `responsableId`,`res`.`selection` AS `selection`,`res`.`dateCreation` AS `dateCreation`,`res`.`dateModification` AS `dateModification`,`res`.`nature` AS `nature`,`res`.`titre` AS `titre`,`res`.`nom` AS `nom`,`res`.`nomSA` AS `nomSA`,`res`.`prenom` AS `prenom`,`res`.`prenomSA` AS `prenomSA`,`res`.`titre2` AS `titre2`,`res`.`nom2` AS `nom2`,`res`.`nom2SA` AS `nom2SA`,`res`.`prenom2` AS `prenom2`,`res`.`prenom2SA` AS `prenom2SA`,`res`.`adresseL1` AS `adresseL1`,`res`.`adresseL2` AS `adresseL2`,`res`.`codePostal` AS `codePostal`,`res`.`communeId` AS `communeId`,`res`.`ancienAdresseL1` AS `ancienAdresseL1`,`res`.`ancienAdresseL2` AS `ancienAdresseL2`,`res`.`ancienCodePostal` AS `ancienCodePostal`,`res`.`ancienCommuneId` AS `ancienCommuneId`,`res`.`email` AS `email`,`res`.`telephoneF` AS `telephoneF`,`res`.`telephoneP` AS `telephoneP`,`res`.`telephoneT` AS `telephoneT`,`res`.`etiquette` AS `etiquette`,`res`.`demenagement` AS `demenagement`,`res`.`dateDemenagement` AS `dateDemenagement`,`res`.`facture` AS `facture`,`res`.`grilleTarif` AS `grilleTarif`,`res`.`ribTit` AS `ribTit`,`res`.`ribDom` AS `ribDom`,`res`.`iban` AS `iban`,`res`.`bic` AS `bic`,`res`.`x` AS `x`,`res`.`y` AS `y`,`res`.`userId` AS `userId`,`res`.`note` AS `note`,`com`.`nom` AS `commune`,count(`ele`.`eleveId`) AS `nbEleves` from ((`sbm_t_responsables` `res` join `sbm_t_communes` `com` on((`res`.`communeId` = `com`.`communeId`))) left join `sbm_t_eleves` `ele` on(((`res`.`responsableId` = `ele`.`responsable1Id`) or (`res`.`responsableId` = `ele`.`responsable2Id`) or (`res`.`responsableId` = `ele`.`responsableFId`)))) group by `res`.`responsableId` order by `res`.`nomSA`,`res`.`prenomSA`,`com`.`nom`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_services`
--
DROP TABLE IF EXISTS `sbm_v_services`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_services` AS select `ser`.`serviceId` AS `serviceId`,`ser`.`selection` AS `selection`,`ser`.`nom` AS `nom`,`ser`.`aliasCG` AS `aliasCG`,`ser`.`transporteurId` AS `transporteurId`,`ser`.`nbPlaces` AS `nbPlaces`,`ser`.`surEtatCG` AS `surEtatCG`,`ser`.`operateur` AS `operateur`,`ser`.`kmAVide` AS `kmAVide`,`ser`.`kmEnCharge` AS `kmEnCharge`,`tra`.`nom` AS `transporteur`,`com`.`nom` AS `communeTransporteur` from ((`sbm_t_services` `ser` join `sbm_t_transporteurs` `tra` on((`tra`.`transporteurId` = `ser`.`transporteurId`))) join `sbm_t_communes` `com` on((`com`.`communeId` = `tra`.`communeId`))) order by `ser`.`serviceId`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_stations`
--
DROP TABLE IF EXISTS `sbm_v_stations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_stations` AS select `sta`.`stationId` AS `stationId`,`sta`.`selection` AS `selection`,`sta`.`communeId` AS `communeId`,`sta`.`nom` AS `nom`,`sta`.`aliasCG` AS `aliasCG`,`sta`.`codeCG` AS `codeCG`,`sta`.`x` AS `x`,`sta`.`y` AS `y`,`sta`.`visible` AS `visible`,`sta`.`ouverte` AS `ouverte`,`com`.`nom` AS `commune` from (`sbm_t_stations` `sta` join `sbm_t_communes` `com` on((`com`.`communeId` = `sta`.`communeId`))) order by `com`.`nom`,`sta`.`nom`;

-- --------------------------------------------------------

--
-- Structure de la vue `sbm_v_transporteurs`
--
DROP TABLE IF EXISTS `sbm_v_transporteurs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dafapfr_bossdemo`@`localhost` SQL SECURITY DEFINER VIEW `sbm_v_transporteurs` AS select `tra`.`transporteurId` AS `transporteurId`,`tra`.`selection` AS `selection`,`tra`.`nom` AS `nom`,`tra`.`adresse1` AS `adresse1`,`tra`.`adresse2` AS `adresse2`,`tra`.`codePostal` AS `codePostal`,`tra`.`communeId` AS `communeId`,`tra`.`telephone` AS `telephone`,`tra`.`fax` AS `fax`,`tra`.`email` AS `email`,`tra`.`siret` AS `siret`,`tra`.`naf` AS `naf`,`tra`.`rib_titulaire` AS `rib_titulaire`,`tra`.`rib_domiciliation` AS `rib_domiciliation`,`tra`.`rib_bic` AS `rib_bic`,`tra`.`rib_iban` AS `rib_iban`,`com`.`nom` AS `commune` from (`sbm_t_transporteurs` `tra` join `sbm_t_communes` `com` on((`com`.`communeId` = `tra`.`communeId`))) order by `tra`.`nom`;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `sbm_s_doccolumns`
--
ALTER TABLE `sbm_s_doccolumns`
  ADD CONSTRAINT `sbm_s_doccolumns_ibfk_1` FOREIGN KEY (`documentId`) REFERENCES `sbm_s_documents` (`documentId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_s_doctables`
--
ALTER TABLE `sbm_s_doctables`
  ADD CONSTRAINT `sbm_s_doctables_ibfk_1` FOREIGN KEY (`documentId`) REFERENCES `sbm_s_documents` (`documentId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_affectations`
--
ALTER TABLE `sbm_t_affectations`
  ADD CONSTRAINT `sbm_t_affectations_ibfk_1` FOREIGN KEY (`millesime`, `eleveId`) REFERENCES `sbm_t_scolarites` (`millesime`, `eleveId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_affectations_ibfk_2` FOREIGN KEY (`responsableId`) REFERENCES `sbm_t_responsables` (`responsableId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_affectations_ibfk_3` FOREIGN KEY (`station1Id`) REFERENCES `sbm_t_stations` (`stationId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_affectations_ibfk_4` FOREIGN KEY (`service1Id`) REFERENCES `sbm_t_services` (`serviceId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_circuits`
--
ALTER TABLE `sbm_t_circuits`
  ADD CONSTRAINT `sbm_t_circuits_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `sbm_t_services` (`serviceId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_circuits_ibfk_2` FOREIGN KEY (`stationId`) REFERENCES `sbm_t_stations` (`stationId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_eleves`
--
ALTER TABLE `sbm_t_eleves`
  ADD CONSTRAINT `sbm_t_eleves_ibfk_1` FOREIGN KEY (`responsable1Id`) REFERENCES `sbm_t_responsables` (`responsableId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_etablissements`
--
ALTER TABLE `sbm_t_etablissements`
  ADD CONSTRAINT `sbm_t_etablissements_ibfk_1` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_etablissements-services`
--
ALTER TABLE `sbm_t_etablissements-services`
  ADD CONSTRAINT `sbm_t_etablissements-services_ibfk_1` FOREIGN KEY (`etablissementId`) REFERENCES `sbm_t_etablissements` (`etablissementId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_etablissements-services_ibfk_2` FOREIGN KEY (`serviceId`) REFERENCES `sbm_t_services` (`serviceId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_responsables`
--
ALTER TABLE `sbm_t_responsables`
  ADD CONSTRAINT `sbm_t_responsables_ibfk_1` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_scolarites`
--
ALTER TABLE `sbm_t_scolarites`
  ADD CONSTRAINT `sbm_t_scolarites_ibfk_1` FOREIGN KEY (`eleveId`) REFERENCES `sbm_t_eleves` (`eleveId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_scolarites_ibfk_2` FOREIGN KEY (`etablissementId`) REFERENCES `sbm_t_etablissements` (`etablissementId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_scolarites_ibfk_3` FOREIGN KEY (`classeId`) REFERENCES `sbm_t_classes` (`classeId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_scolarites_ibfk_4` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_scolarites_ibfk_5` FOREIGN KEY (`tarifId`) REFERENCES `sbm_t_tarifs` (`tarifId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_secteurs-scolaires-clg-pu`
--
ALTER TABLE `sbm_t_secteurs-scolaires-clg-pu`
  ADD CONSTRAINT `sbm_t_secteurs-scolaires-clg-pu_ibfk_1` FOREIGN KEY (`etablissementId`) REFERENCES `sbm_t_etablissements` (`etablissementId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sbm_t_secteurs-scolaires-clg-pu_ibfk_2` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_services`
--
ALTER TABLE `sbm_t_services`
  ADD CONSTRAINT `sbm_t_services_ibfk_1` FOREIGN KEY (`transporteurId`) REFERENCES `sbm_t_transporteurs` (`transporteurId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_stations`
--
ALTER TABLE `sbm_t_stations`
  ADD CONSTRAINT `sbm_t_stations_ibfk_1` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sbm_t_transporteurs`
--
ALTER TABLE `sbm_t_transporteurs`
  ADD CONSTRAINT `sbm_t_transporteurs_ibfk_1` FOREIGN KEY (`communeId`) REFERENCES `sbm_t_communes` (`communeId`) ON UPDATE CASCADE;

