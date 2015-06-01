-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 01 Juin 2015 à 14:17
-- Version du serveur: 5.5.36-cll-lve
-- Version de PHP: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `dafapfr_sbm-demo`
--

--
-- Vider la table avant d'insérer `sbm_s_calendar`
--

TRUNCATE TABLE `sbm_s_calendar`;
--
-- Contenu de la table `sbm_s_calendar`
--

INSERT INTO `sbm_s_calendar` (`millesime`, `ordinal`, `nature`, `rang`, `libelle`, `description`, `dateDebut`, `dateFin`, `echeance`, `ouvert`) VALUES
(2013, 1, 'AS', 1, '2013-2014', 'Année scolaire 2013-2014', '2013-09-02', '2014-07-04', '2014-07-04', 1),
(2013, 2, 'INS', 1, 'Période d''inscription', 'Période d''inscription 2013-2014', '2013-05-02', '2013-07-31', '2013-08-31', 1),
(2013, 8, 'VACA', 1, 'Vacances de Toussaint', 'Vacances de Toussaint 2013-2014', '2013-10-18', '2013-11-02', '2013-11-02', 1),
(2013, 9, 'VACA', 2, 'Vacances de Noël', 'Vacances de Noël 2013-2014', '2013-12-20', '2014-01-04', '2014-01-04', 1),
(2013, 10, 'VACA', 3, 'Vacances d''hiver', 'Vacances d''hiver 2013-2014', '2014-02-14', '2014-03-01', '2014-03-01', 1),
(2013, 11, 'VACA', 4, 'Vacances de printemps', 'Vacances de printemps 2013-2014', '2014-04-18', '2014-05-03', '2014-05-03', 1),
(2014, 1, 'AS', 1, '2014-2015', 'Année scolaire 2014-2015', '2014-09-02', '2015-07-04', '2015-07-04', 1),
(2014, 2, 'INS', 1, 'Période d''inscription', 'Période d''inscription 2014-2015', '2014-05-02', '2015-05-31', '2014-07-21', 1),
(2014, 8, 'VACA', 1, 'Vacances de Toussaint', 'Vacances de Toussaint 2014-2015', '2014-10-18', '2014-11-02', '2014-11-02', 1),
(2014, 9, 'VACA', 2, 'Vacances de Noël', 'Vacances de Noël 2014-2015', '2014-12-20', '2015-01-04', '2015-01-04', 1),
(2014, 10, 'VACA', 3, 'Vacances d''hiver', 'Vacances d''hiver 2014-2015', '2015-02-14', '2015-03-01', '2015-03-01', 1),
(2014, 11, 'VACA', 4, 'Vacances de printemps', 'Vacances de printemps 2014-2015', '2015-04-18', '2015-05-03', '2015-05-03', 1),
(2015, 1, 'AS', 1, '2015-2016', 'Année scolaire 2015-2016', '2015-08-31', '2016-07-05', '2016-07-05', 1),
(2015, 2, 'INS', 1, 'Période d''inscription', 'Période d''inscription 2015-2016', '2015-06-01', '2015-07-20', '2015-07-20', 1),
(2015, 8, 'VACA', 1, 'Vacances de Toussaint', 'Vacances de Toussaint 2015-2016', '2015-10-16', '2015-11-01', '2015-11-01', 1),
(2015, 9, 'VACA', 2, 'Vacances de Noël', 'Vacances de Noël 2015-2016', '2015-12-18', '2016-01-03', '2016-01-03', 1),
(2015, 10, 'VACA', 3, 'Vacances d''hiver', 'Vacances d''hiver 2015-2016', '2016-02-19', '2016-03-06', '2016-03-06', 1),
(2015, 11, 'VACA', 4, 'Vacances de printemps', 'Vacances de printemps 2015-2016', '2016-04-15', '2016-05-01', '2016-05-01', 1);


--
-- Vider la table avant d'insérer `sbm_t_users`
--

TRUNCATE TABLE `sbm_t_users`;
--
-- Contenu de la table `sbm_t_users`
--

INSERT INTO `sbm_t_users` (`categorieId`, `titre`, `nom`, `prenom`, `email`, `gds`, `confirme`, `active`) VALUES
(255, 'Superviseur', 'sadmin', 'Démo', 'dafap@dafap.fr', '*R?ap5s!', 1, 1),
(254, 'M.', 'ADMINISTRATEUR', 'Démo', 'admin.demo@dafap.fr', 'ap4?*Fa%', 1, 1),
(253, 'M.', 'GESTION', 'Démo', 'gestion.demo@dafap.fr', '!G!ap4g1', 1, 1),
(3, 'M.', 'ETABLISSEMENT', 'Démo', 'etablissement.demo@dafap.fr', 'pU6xyb0k', 1, 1),
(2, 'M.', 'TRANSPORTEUR', 'Démo', 'transporteur.demo@dafap.fr', 'pU6xyb0k', 1, 1),
(1, 'M.', 'PARENT', 'Démo', 'parent.demo@dafap.fr', 'pU6xyb0k', 1, 1);


--
-- Vider la table avant d'insérer `sbm_t_classes`
--

TRUNCATE TABLE `sbm_t_classes`;
--
-- Contenu de la table `sbm_t_classes`
--

INSERT INTO `sbm_t_classes` (`nom`, `aliasCG`, `niveau`) VALUES
('1', NULL, 8),
('1 BAC PRO', NULL, 8),
('2', NULL, 8),
('2 BAC PRO', NULL, 8),
('3', NULL, 4),
('3 PREPA PRO', NULL, 8),
('3 SEGPA', NULL, 4),
('4', NULL, 4),
('4 SEGPA', NULL, 4),
('5', NULL, 4),
('5 SEGPA', NULL, 4),
('6', NULL, 4),
('6 ULIS', NULL, 4),
('BTS', NULL, 8),
('CAP 2', NULL, 8),
('CE1', NULL, 2),
('CE2', NULL, 2),
('CM1', NULL, 2),
('CM2', NULL, 2),
('CP', NULL, 2),
('GS', NULL, 1),
('MS', NULL, 1),
('PS', NULL, 1),
('T', NULL, 8),
('T BAC PRO', NULL, 8);

--
-- Vider la table avant d'insérer `sbm_t_etablissements`
--

TRUNCATE TABLE `sbm_t_etablissements`;
--
-- Contenu de la table `sbm_t_etablissements`
--

INSERT INTO `sbm_t_etablissements` (`etablissementId`, `selection`, `nom`, `adresse1`, `adresse2`, `codePostal`, `communeId`, `niveau`, `statut`, `visible`, `desservie`, `regrPeda`, `rattacheA`, `telephone`, `fax`, `email`, `directeur`, `jOuverture`, `hMatin`, `hMidi`, `hAMidi`, `hSoir`, `hGarderieOMatin`, `hGarderieFMidi`, `hGarderieFSoir`, `x`, `y`, `geopt`) VALUES
('0330001A', 0, 'COLLÈGE JEAN JAURÈS', '4 Rue Jules Ferry', 'BP143', '33210', '33227', 4, 1, 1, 1, 0, '', '0556202020', '0556202021', '', '', 31, '', '', '', '', '', '', '', '1642966.9731317000', '3258538.2209265000', NULL),
('0330002B', 0, 'COLLÈGE EDMOND ROSTAND', '21 Avenue François Mauriac', '', '33210', '33504', 4, 1, 1, 1, 0, '', '0556303030', '0556303031', '', '', 31, '', '', '', '', '', '', '', '1645155.6754657000', '3260296.3234385000', NULL),
('0330003C', 0, 'COLLÈGE PRIVÉ SAINTE-FOY', '17 avenue François Mitterrand', '', '33210', '33432', 4, 0, 1, 1, 0, '', '0565438250', '0565438251', '', '', 31, '', '', '', '', '', '', '', '1640688.7549773000', '3262437.2764548000', NULL),
('0330004D', 0, 'LYCÉE POLYVALENT', '7 Avenue Jean Moulin', 'BP 352', '33210', '33227', 8, 1, 1, 1, 0, '', '0556404040', '0556404041', '', '', 31, '', '', '', '', '', '', '', '1640903.9543951000', '3263447.0100236000', NULL),
('0330005E', 0, 'LYCÉE DES MÉTIERS DU BOIS', '2 Avenue du lycée', 'BP 11', '33210', '33533', 8, 1, 1, 1, 0, '', '0556505050', '0556505051', '', '', 31, '', '', '', '', '', '', '', '1639714.7224060000', '3259190.2759314000', NULL),
('0330006F', 0, 'ECOLE MARCEL PAGNOL', 'Place Marcel Pagnol', '', '33210', '33227', 3, 1, 1, 1, 0, '', '0565631463', '', '', '', 31, '', '', '', '', '', '', '', '1641721.5055784000', '3260146.3269832000', NULL),
('0330007G', 0, 'ECOLE JULES FERRY', 'Rue du Général de Gaulle', '', '33210', '33533', 3, 1, 1, 1, 0, '', '0556603161', '', '', '', 31, '09:00', '12:00', '14:00', '17:00', '', '', '', '1641376.8713619000', '3258974.2762179000', NULL),
('0330008H', 0, 'ÉCOLE ÉLÉMENTAIRE MARIE CURIE', '5 avenue des Vignes', '', '33210', '33504', 2, 1, 1, 1, 1, '0330010K', '0556603062', '', '', '', 31, '', '', '', '', '', '', '', '1645212.8284869000', '3260135.9627127000', NULL),
('0330009J', 0, 'ÉCOLE MATERNELLE JEAN ZAY', '7 avenue Gambetta', '', '33210', '33227', 1, 1, 1, 1, 0, '', '0556607077', '', '', '', 31, '', '', '', '', '', '', '', '1645206.7954353000', '3260052.4624252000', NULL),
('0330010K', 0, 'ÉCOLE ÉLÉMENTAIRE SAINT EXUPERY', 'Lieu-dit Les Gares', '', '33210', '33164', 2, 1, 1, 1, 1, '0330008H', '0565432702', '', '', '', 31, '', '', '', '', '', '', '', '1644908.9203387000', '3263149.4288414000', NULL),
('0330011L', 0, 'ECOLE PRIVÉE SAINT AGNÈS', 'Quartier Saint Michel', '', '33210', '33465', 3, 0, 1, 1, 0, '', '0565432885', '', '', '', 31, '', '', '', '', '', '', '', '1642178.8000000000', '3264062.7000000000', NULL);



