-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 09, 2016 at 11:38 AM
-- Server version: 5.5.46-0+deb8u1
-- PHP Version: 5.6.14-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webgr`
--
CREATE DATABASE IF NOT EXISTS `webgr` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `webgr`;

-- --------------------------------------------------------

--
-- Table structure for table `annotations`
--

CREATE TABLE IF NOT EXISTS `annotations` (
`ANNO_ID` int(11) unsigned NOT NULL,
  `CEhIM_ID` int(10) unsigned NOT NULL,
  `PARENT_ID` int(11) unsigned DEFAULT NULL,
  `PART_ID` int(8) unsigned NOT NULL,
  `ANNO_COMMENT` text,
  `ANNO_DATE` datetime DEFAULT NULL,
  `ANNO_GROUP` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ANNO_WS_REF` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ANNO_WEBGR_REF` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ANNO_COUNT` int(4) unsigned NOT NULL,
  `ANNO_DECIMAL` decimal(7,0) DEFAULT NULL,
  `ANNO_SUB` varchar(10) DEFAULT NULL,
  `ANNO_BRIGHTNESS` decimal(10,0) DEFAULT NULL,
  `ANNO_CONTRAST` decimal(10,0) DEFAULT NULL,
  `ANNO_COLOR` decimal(10,0) DEFAULT NULL,
  `ANNO_MAGNIFICATION` decimal(10,0) DEFAULT NULL,
  `ANNO_FINAL` tinyint(1) NOT NULL DEFAULT '0',
  `ANNO_CREATE_DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_desc`
--

CREATE TABLE IF NOT EXISTS `attribute_desc` (
`ATDE_ID` int(5) NOT NULL,
  `USER_ID` int(7) unsigned NOT NULL,
  `ATDE_NAME` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ATDE_UNIT` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `ATDE_DESCRIPTION` text CHARACTER SET latin1,
  `ATDE_DEFAULT` varchar(4000) CHARACTER SET latin1 DEFAULT NULL,
  `ATDE_REQUIRED` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ATDE_IS_STANDARD` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ATDE_ACTIVE` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ATDE_DATATYPE` varchar(20) CHARACTER SET latin1 NOT NULL,
  `ATDE_FORMTYPE` varchar(20) CHARACTER SET latin1 NOT NULL,
  `ATDE_VALUELIST` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ATDE_SEQUENCE` int(3) unsigned DEFAULT '0',
  `ATDE_MULTIPLE` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ATDE_SHOWINLIST` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ATDE_GROUP` varchar(20) CHARACTER SET latin1 NOT NULL,
  `ATDE_FILTERS` varchar(4000) CHARACTER SET latin1 DEFAULT NULL,
  `ATDE_VALIDATORS` varchar(4000) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=607 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `attribute_desc`
--

INSERT INTO `attribute_desc` (`ATDE_ID`, `USER_ID`, `ATDE_NAME`, `ATDE_UNIT`, `ATDE_DESCRIPTION`, `ATDE_DEFAULT`, `ATDE_REQUIRED`, `ATDE_IS_STANDARD`, `ATDE_ACTIVE`, `ATDE_DATATYPE`, `ATDE_FORMTYPE`, `ATDE_VALUELIST`, `ATDE_SEQUENCE`, `ATDE_MULTIPLE`, `ATDE_SHOWINLIST`, `ATDE_GROUP`, `ATDE_FILTERS`, `ATDE_VALIDATORS`) VALUES
(1, 1, 'LENGTH', '218', 'total length of the fish in millimeter', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(2, 1, 'WEIGHT', '220', 'weight of the fish sample in gramm', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(4, 1, 'RESOLUTION', '418', 'Image scan/print resolution in dots per inch', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'image', NULL, NULL),
(8, 1, 'STOCK', '', 'Individual information/classification about fish stock, refers to the spatial distribution of a population', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(10, 1, 'ARCHIVING_CODE', '', 'Internal institute code to store the physical structure', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(13, 1, 'SEX', '', 'gender/sex of fish', '', 0, 1, 1, 'integer', 'radio', 1, 2, 0, 1, 'fish', NULL, NULL),
(16, 1, 'AREA', '', 'referes to a geographic region, area code like ICES and NAFO', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(17, 1, 'CAPTURE_DATE', '', 'Date of capture of fish, format YYYY-MM-DD', '', 0, 1, 1, 'date', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(24, 1, 'GEAR', '', '', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'fish', NULL, NULL),
(30, 1, 'FISH_COMMENT', '', 'just additional comment to this dataset', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'fish', NULL, NULL),
(31, 1, 'IMAGE_COMMENT', '', 'just additional comment to this dataset', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'image', NULL, NULL),
(32, 1, 'MAGNIFICATION', '', 'Magnification of subject for image creation', '', 0, 0, 1, 'decimal', 'text', 0, NULL, 0, 1, 'image', NULL, NULL),
(33, 1, 'PREPARATION_METHOD', '', 'Preparation method of subject shown on image', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'image', NULL, NULL),
(501, 1, 'SAMPLING_DATE', NULL, 'Date of sampling of fish, format YYYY-MM-DD', NULL, 0, 1, 1, 'date', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(502, 1, 'OBSERVED_MATURITY_STAGE', NULL, 'Maturity stage observed before processing inside WebGR annotation', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(503, 1, 'SAMPLING_INSTITUTE', NULL, 'institute of sampling the physical structure', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(504, 1, 'ARCHIVING_INSTITUTE', NULL, 'institute of archiving the physical structure', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(505, 1, 'RESPONSABLE_SCIENTIST', NULL, 'Responsable scientist to contact', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 0, 'fish', NULL, NULL),
(506, 1, 'SAMPLING_SOURCE', NULL, 'Sampling source', NULL, 0, 0, 1, 'integer', 'select', 1, 0, 0, 1, 'fish', NULL, NULL),
(507, 1, 'LONGTITUDE', '1011', 'Longtitude of fish catch, measured in G  (= degree), no degree sign, western hemisphere has negative sign', NULL, 0, 0, 1, 'decimal', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(508, 1, 'LATITUDE', '1011', 'Latitude of fish catch, measured in G  (= degree), no degree sign, southern hemisphere has negative sign', NULL, 0, 0, 1, 'decimal', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(601, 1, 'Location', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(602, 1, 'Country', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(603, 1, 'Institution', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(604, 1, 'UNIT', NULL, NULL, NULL, 0, 0, 1, 'integer', 'select', 1, 0, 0, 0, 'system', NULL, NULL),
(605, 1, 'SPECIES', '', 'Fish scientific name (latin)', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 1, 'fish', NULL, NULL),
(606, 1, 'TYPE_OF_STRUCTURE', NULL, 'Subject of visual analysis (otolith, gonad etc.)', NULL, 0, 1, 1, 'integer', 'select', 1, 1, 0, 1, 'image', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `caex_has_atde`
--

CREATE TABLE IF NOT EXISTS `caex_has_atde` (
  `CAEX_ID` int(6) unsigned NOT NULL,
  `ATDE_ID` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='shown attributes';

-- --------------------------------------------------------

--
-- Table structure for table `calibration_exercise`
--

CREATE TABLE IF NOT EXISTS `calibration_exercise` (
`CAEX_ID` int(6) unsigned NOT NULL,
  `KETA_ID` int(5) unsigned DEFAULT NULL,
  `WORK_ID` int(5) unsigned DEFAULT NULL,
  `EXPE_ID` int(5) unsigned DEFAULT NULL,
  `CAEX_NAME` varchar(255) NOT NULL,
  `CAEX_DESCRIPTION` text,
  `CAEX_COMPAREABLE` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CAEX_RANDOMIZED` int(1) unsigned DEFAULT NULL,
  `CAEX_IS_STOPPED` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CAEX_TRAINING` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ce_has_image`
--

CREATE TABLE IF NOT EXISTS `ce_has_image` (
`CEhIM_ID` int(10) unsigned NOT NULL,
  `CAEX_ID` int(6) unsigned NOT NULL,
  `IMAGE_ID` int(9) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dots`
--

CREATE TABLE IF NOT EXISTS `dots` (
`DOTS_ID` int(11) unsigned NOT NULL,
  `ANNO_ID` int(11) unsigned NOT NULL,
  `DOTS_X` float DEFAULT NULL,
  `DOTS_Y` float DEFAULT NULL,
  `DOTS_SEQUENCE` int(2) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `expertise`
--

CREATE TABLE IF NOT EXISTS `expertise` (
`EXPE_ID` int(5) unsigned NOT NULL,
  `EXPE_SPECIES` varchar(255) NOT NULL,
  `EXPE_AREA` varchar(255) NOT NULL,
  `EXPE_SUBJECT` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fish`
--

CREATE TABLE IF NOT EXISTS `fish` (
`FISH_ID` int(9) unsigned NOT NULL,
  `FISH_SAMPLE_CODE` varchar(50) NOT NULL,
  `USER_ID` int(7) unsigned NOT NULL COMMENT 'owner of data/metadata'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
`IMAGE_ID` int(9) unsigned NOT NULL,
  `USER_ID` int(7) unsigned NOT NULL COMMENT 'owner of file and data/metadata',
  `FISH_ID` int(9) unsigned NOT NULL,
  `IMAGE_CHECKSUM` varchar(50) DEFAULT NULL,
  `IMAGE_GUID` char(36) NOT NULL,
  `IMAGE_ORIGINAL_CHECKSUM` varchar(50) NOT NULL,
  `IMAGE_ORIGINAL_FILENAME` varchar(255) NOT NULL,
  `IMAGE_DIM_X` int(4) NOT NULL,
  `IMAGE_DIM_Y` int(4) NOT NULL,
  `IMAGE_RATIO_EXTERNAL` float DEFAULT NULL COMMENT 'ratio (calculated WebGR external) between pixel and physical length in micrometer',
  `IMAGE_RATIO_INTERNAL` float DEFAULT NULL COMMENT 'ratio (calculated WebGR internal) between pixel and physical length in micrometer',
  `IMAGE_SHRINKED_RATIO` float DEFAULT NULL COMMENT 'ratio between shrinked working copy and original image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `imageset_attributes`
--

CREATE TABLE IF NOT EXISTS `imageset_attributes` (
`IMAT_ID` int(6) unsigned NOT NULL,
  `ATDE_ID` int(5) NOT NULL,
  `CAEX_ID` int(6) unsigned NOT NULL,
  `VALI_ID` int(5) unsigned DEFAULT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `IMAT_FROM` varchar(4000) DEFAULT NULL,
  `IMAT_TO` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `key_table`
--

CREATE TABLE IF NOT EXISTS `key_table` (
`KETA_ID` int(5) unsigned NOT NULL,
  `KETA_AREA` varchar(255) NOT NULL,
  `KETA_SPECIES` varchar(255) NOT NULL,
  `KETA_AGE` tinyint(1) unsigned DEFAULT NULL,
  `KETA_MATURITY` tinyint(1) unsigned DEFAULT NULL,
  `KETA_NAME` varchar(255) NOT NULL,
  `KETA_SUBJECT` varchar(255) NOT NULL COMMENT 'replaces KETA_AGE and _MATURITY',
  `KETA_FILENAME` varchar(259) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `meta_data_fish`
--

CREATE TABLE IF NOT EXISTS `meta_data_fish` (
`MEDFI_ID` int(11) unsigned NOT NULL,
  `ATDE_ID` int(5) NOT NULL,
  `FISH_ID` int(9) unsigned NOT NULL,
  `MEDFI_VALUE` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `meta_data_image`
--

CREATE TABLE IF NOT EXISTS `meta_data_image` (
`MEDIM_ID` int(11) unsigned NOT NULL,
  `ATDE_ID` int(5) NOT NULL,
  `IMAGE_ID` int(9) unsigned NOT NULL,
  `MEDIM_VALUE` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `participant`
--

CREATE TABLE IF NOT EXISTS `participant` (
`PART_ID` int(8) unsigned NOT NULL,
  `CAEX_ID` int(6) unsigned NOT NULL,
  `USER_ID` int(7) unsigned NOT NULL,
  `PART_EXPERTISE_LEVEL` varchar(30) DEFAULT NULL,
  `PART_STOCK_ASSESSMENT` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `PART_PARTICIPANT_ROLE` varchar(30) NOT NULL DEFAULT 'Reader',
  `PART_NUMBER` int(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`USER_ID` int(7) unsigned NOT NULL,
  `USER_USERNAME` varchar(255) NOT NULL,
  `USER_LASTNAME` varchar(255) NOT NULL,
  `USER_FIRSTNAME` varchar(255) NOT NULL,
  `USER_PASSWORD` varchar(255) NOT NULL,
  `USER_E_MAIL` varchar(255) NOT NULL,
  `USER_INSTITUTION` varchar(255) DEFAULT NULL,
  `USER_STREET` varchar(255) DEFAULT NULL,
  `USER_COUNTRY` varchar(255) DEFAULT NULL,
  `USER_PHONE` varchar(255) DEFAULT NULL,
  `USER_FAX` varchar(255) DEFAULT NULL,
  `USER_CITY` varchar(255) DEFAULT NULL,
  `USER_ACTIVE` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `USER_ROLE` varchar(11) NOT NULL DEFAULT 'reader',
  `USER_GUID` varchar(40) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USER_USERNAME`, `USER_LASTNAME`, `USER_FIRSTNAME`, `USER_PASSWORD`, `USER_E_MAIL`, `USER_INSTITUTION`, `USER_STREET`, `USER_COUNTRY`, `USER_PHONE`, `USER_FAX`, `USER_CITY`, `USER_ACTIVE`, `USER_ROLE`, `USER_GUID`) VALUES
(1, 'webgradmin@domain.com', 'Admin', 'Webgr', '{SHA}D1hMZDFpxR6tBhKStqJ98u7E8TA=', 'webgradmin@domain.com', '255', '', '234', '', '', '', 1, 'admin', '6D10C2D8-9F6C-B41F-6D77-3A61325D9671');

-- --------------------------------------------------------

--
-- Table structure for table `user_has_expertise`
--

CREATE TABLE IF NOT EXISTS `user_has_expertise` (
  `USER_ID` int(7) unsigned NOT NULL,
  `EXPE_ID` int(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `value_list`
--

CREATE TABLE IF NOT EXISTS `value_list` (
`VALI_ID` int(5) unsigned NOT NULL,
  `ATDE_ID` int(5) NOT NULL,
  `VALI_NAME` varchar(255) DEFAULT NULL,
  `VALI_VALUE` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1087 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `value_list`
--

INSERT INTO `value_list` (`VALI_ID`, `ATDE_ID`, `VALI_NAME`, `VALI_VALUE`) VALUES
(215, 601, 'Bonn', 'Bonn'),
(218, 604, 'mm', 'mm'),
(219, 604, 'm', 'm'),
(220, 604, 'g', 'g'),
(221, 604, 'kg', 'kg'),
(225, 602, 'Austria', 'Austria'),
(226, 602, 'Belgium', 'Belgium'),
(227, 602, 'Bulgaria', 'Bulgaria'),
(228, 602, 'Cyprus', 'Cyprus'),
(229, 602, 'Czech Republic', 'Czech Republic'),
(230, 602, 'Denmark', 'Denmark'),
(231, 602, 'Estonia', 'Estonia'),
(232, 602, 'Finland', 'Finland'),
(233, 602, 'France', 'France'),
(234, 602, 'Germany', 'Germany'),
(235, 602, 'Greece', 'Greece'),
(236, 602, 'Hungary', 'Hungary'),
(237, 602, 'Ireland', 'Ireland'),
(238, 602, 'Italy', 'Italy'),
(239, 602, 'Latvia', 'Latvia'),
(240, 602, 'Lithuania', 'Lithuania'),
(241, 602, 'Luxembourg', 'Luxembourg'),
(242, 602, 'Malta', 'Malta'),
(243, 602, 'Netherlands', 'Netherlands'),
(244, 602, 'Poland', 'Poland'),
(245, 602, 'Portugal', 'Portugal'),
(246, 602, 'Romania', 'Romania'),
(247, 602, 'Slovakia', 'Slovakia'),
(248, 602, 'Slovenia', 'Slovenia'),
(249, 602, 'Spain', 'Spain'),
(250, 602, 'Sweden', 'Sweden'),
(251, 602, 'United Kingdom', 'United Kingdom'),
(252, 603, 'Laboratório Nacional de Recursos Biológicos – IPIMAR (Portugal) –', 'Laboratório Nacional de Recursos Biológicos – IPIMAR (Portugal)'),
(253, 603, 'The Agri-Food & Biosciences Institute (UK)', 'The Agri-Food & Biosciences Institute (UK)'),
(254, 603, 'AZTI Foundation (Spain)', 'AZTI Foundation (Spain)'),
(255, 603, 'Federal Agency for Agriculture and Food (Germany)', 'Federal Agency for Agriculture and Food (Germany)'),
(256, 603, 'Johann Heinrich von Thünen Institute (Germany)', 'Johann Heinrich von Thünen Institute (Germany)'),
(257, 603, 'Hellenic Centre for Marine Research (Greece)', 'Hellenic Centre for Marine Research (Greece)'),
(258, 603, 'Instituto Español de Oceanografia (Spain)', 'Instituto Español de Oceanografia (Spain)'),
(259, 603, 'Institut français de recherche pour l’exploitation de la mer (France)', 'Institut français de recherche pour l’exploitation de la mer (France)'),
(260, 603, 'Wageningen IMARES (The Netherlands)', 'Wageningen IMARES (The Netherlands)'),
(261, 603, 'Institute of Marine Research (Norway)', 'Institute of Marine Research (Norway)'),
(262, 603, 'Swedish Board of Fisheries (Sweden)', 'Swedish Board of Fisheries (Sweden)'),
(263, 603, 'Italian Society for Marine Biology (Italy)', 'Italian Society for Marine Biology (Italy)'),
(266, 13, 'female', 'female'),
(267, 13, 'male', 'male'),
(268, 13, 'undefined', 'undefined'),
(269, 601, 'Sukarrieta', 'Sukarrieta'),
(401, 605, 'Clupea harengus', 'Clupea harengus'),
(402, 605, 'Engraulis encrasicolus', 'Engraulis encrasicolus'),
(403, 605, 'Gadus morhua', 'Gadus morhua'),
(404, 605, 'Limanda limanda', 'Limanda limanda'),
(405, 605, 'Melanogrammus aeglefinus', 'Melanogrammus aeglefinus'),
(406, 605, 'Merlangius merlangus', 'Merlangius merlangus'),
(407, 605, 'Merluccius merluccius', 'Merluccius merluccius'),
(408, 605, 'Micromesistius poutassou', 'Micromesistius poutassou'),
(409, 605, 'Platichthys flesus', 'Platichthys flesus'),
(410, 605, 'Pleuronectes platessa', 'Pleuronectes platessa'),
(411, 605, 'Psetta maxima', 'Psetta maxima'),
(412, 605, 'Sardina pilchardus', 'Sardina pilchardus'),
(413, 605, 'Scomber scombrus', 'Scomber scombrus'),
(414, 605, 'Scophthalmus rhombus', 'Scophthalmus rhombus'),
(415, 605, 'Solea solea', 'Solea solea'),
(416, 605, 'Sprattus sprattus', 'Sprattus sprattus'),
(417, 605, 'Trachurus trachurus', 'Trachurus trachurus'),
(418, 604, 'dpi', 'dpi'),
(419, 603, 'Guest', 'Guest'),
(420, 605, 'Mullus surmuletus', 'Mullus surmuletus'),
(421, 605, 'Pollachius virens', 'Pollachius virens'),
(426, 605, 'Glyptocephalus cynoglossus', 'Glyptocephalus cynoglossus'),
(1001, 606, 'gonad', 'gonad'),
(1002, 606, 'otolith', 'otolith'),
(1003, 606, 'spine', 'spine'),
(1004, 606, 'scale', 'scale'),
(1005, 606, 'operculum', 'operculum'),
(1006, 606, 'illicium', 'illicium'),
(1007, 606, 'egg', 'egg'),
(1008, 606, 'vertebra', 'vertebra'),
(1009, 606, 'fin rays', 'fin rays'),
(1010, 606, 'bone', 'bone'),
(1011, 604, 'degree', 'degree'),
(1012, 506, 'harbour', 'harbour'),
(1013, 506, 'survey', 'survey'),
(1014, 506, 'self sampling', 'self sampling'),
(1015, 506, 'on board', 'on board'),
(1016, 602, 'Norway', 'Norway'),
(1017, 603, 'CEFAS', 'CEFAS'),
(1019, 602, 'other', 'other'),
(1020, 605, 'Lepidorhombus whiffiagonis', 'Lepidorhombus whiffiagonis'),
(1021, 605, 'Brosme brosme', 'Brosme brosme'),
(1022, 605, 'Molva molva', 'Molva molva'),
(1023, 605, 'Argentina silus', 'Argentina silus'),
(1024, 605, 'Pagellus bogareaveo', 'Pagellus bogareaveo'),
(1025, 605, 'Coryphaenoides rupestris', 'Coryphaenoides rupestris'),
(1026, 605, 'Molva dypteria', 'Molva dypteria'),
(1027, 605, 'Hoplostethus atlanticus', 'Hoplostethus atlanticus'),
(1028, 605, 'Phycis blennoides', 'Phycis blennoides'),
(1029, 603, 'Marine Research Institute MRI', 'Marine Research Institute MRI'),
(1030, 602, 'Iceland', 'Iceland'),
(1031, 601, 'Reykjavik', 'Reykjavik'),
(1032, 601, 'Iceland', 'Iceland'),
(1033, 13, 'Female', 'Female'),
(1034, 13, 'Male', 'Male'),
(1035, 603, 'DSIP', 'DSIP'),
(1036, 603, 'IPMA', 'IPMA'),
(1037, 603, 'Marine Scotland', 'Marine Scotland'),
(1038, 603, 'ICCM', 'ICCM'),
(1039, 605, 'Apanopus carbo', 'Apanopus carbo'),
(1040, 506, 'Icelandic autumn survey', 'Icelandic autumn survey'),
(1041, 506, 'Commercial fishery', 'Commercial fishery'),
(1042, 506, 'Scottish deep water survey', 'Scottish deep water survey'),
(1043, 506, 'Experimental fishery', 'Experimental fishery'),
(1044, 605, 'Aphanopus carbo', 'Aphanopus carbo'),
(1045, 605, 'Pollachius virens', 'Pollachius virens'),
(1046, 605, 'Dicentrarchus labrax', 'Dicentrarchus labrax'),
(1047, 13, 'F', 'F'),
(1048, 13, 'M', 'M'),
(1049, 13, 'U', 'U'),
(1050, 506, 'Unknown', 'Unknown'),
(1051, 601, 'Denmark', 'Denmark'),
(1052, 603, 'DTU Aqua', 'DTU Aqua'),
(1053, 606, 'XXX', 'XXX'),
(1054, 506, 'U', 'U'),
(1055, 605, 'Pagellus bogaraveo', 'Pagellus bogaraveo'),
(1056, 605, 'Molva dypterygia', 'Molva dypterygia'),
(1057, 601, 'Donosti', 'Donosti'),
(1058, 601, 'San Sebastian', 'San Sebastian'),
(1059, 603, 'Faroese Island Marine Institute', 'Faroese Island Marine Institute'),
(1060, 602, 'Faroe Islands', 'Faroe Islands'),
(1061, 603, 'Marine Institute', 'Marine Institute'),
(1062, 602, 'Russia', 'Russia'),
(1063, 603, 'PINRO', 'PINRO'),
(1064, 605, 'Hippoglossus hippoglossus', 'Hippoglossus hippoglossus'),
(1065, 605, 'Trachurus mediterraneus', 'Trachurus mediterraneus'),
(1066, 605, 'Trachurus picturatus', 'Trachurus picturatus'),
(1067, 603, 'ILVO', 'ILVO'),
(1068, 605, 'Ammodytes tobianus', 'Ammodytes tobianus'),
(1069, 603, 'ELGO Fisheries Research Institute ', 'ELGO Fisheries Research Institute '),
(1070, 603, 'IAMC CNR ', 'IAMC CNR '),
(1071, 601, 'Santander', 'Santander'),
(1072, 605, 'Scomber colias', 'Scomber colias'),
(1073, 605, 'Trisopterus luscus', 'Trisopterus luscus'),
(1074, 601, 'Lisboa', 'Lisboa'),
(1075, 601, 'Vigo', 'Vigo'),
(1076, 603, 'Instituto de Investigaciones Marinas de Vigo CSIC IIM', 'Instituto de Investigaciones Marinas de Vigo CSIC IIM'),
(1077, 603, 'Instituto portugués do mar e da atmosfera IPMA', 'Instituto portugués do mar e da atmosfera IPMA'),
(1078, 506, 'MEDITS', 'MEDITS'),
(1079, 506, 'CAMPBIOL', 'CAMPBIOL'),
(1080, 606, 'gonads', 'gonads'),
(1081, 601, 'Bari', 'Bari'),
(1082, 605, 'Mullus barbatus', 'Mullus barbatus'),
(1083, 506, 'DCF', 'DCF'),
(1084, 506, 'DCF', 'DCF'),
(1085, 506, 'DCF', 'DCF'),
(1086, 601, 'Tenerifa', 'Tenerifa');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_all_annotations`
--
CREATE TABLE IF NOT EXISTS `v_all_annotations` (
`ANNO_ID` int(11) unsigned
,`CEhIM_ID` int(10) unsigned
,`PARENT_ID` int(11) unsigned
,`PART_ID` int(8) unsigned
,`ANNO_COMMENT` text
,`ANNO_DATE` datetime
,`ANNO_GROUP` tinyint(1) unsigned
,`ANNO_WS_REF` tinyint(1) unsigned
,`ANNO_WEBGR_REF` tinyint(1) unsigned
,`ANNO_COUNT` int(4) unsigned
,`ANNO_DECIMAL` decimal(7,0)
,`ANNO_SUB` varchar(10)
,`ANNO_BRIGHTNESS` decimal(10,0)
,`ANNO_CONTRAST` decimal(10,0)
,`ANNO_COLOR` decimal(10,0)
,`ANNO_MAGNIFICATION` decimal(10,0)
,`ANNO_FINAL` tinyint(1)
,`ANNO_CREATE_DATE` timestamp
,`PART_NUMBER` int(3) unsigned
,`IMAGE_ID` int(9) unsigned
,`CAEX_ID` int(6) unsigned
,`KETA_ID` int(5) unsigned
,`WORK_ID` int(5) unsigned
,`EXPE_ID` int(5) unsigned
,`CAEX_NAME` varchar(255)
,`CAEX_DESCRIPTION` text
,`CAEX_COMPAREABLE` tinyint(1) unsigned
,`CAEX_RANDOMIZED` int(1) unsigned
,`CAEX_IS_STOPPED` tinyint(1) unsigned
,`CAEX_TRAINING` tinyint(1) unsigned
,`WORK_NAME` varchar(255)
,`IMAGE_ORIGINAL_FILENAME` varchar(255)
,`KETA_NAME` varchar(255)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ce_list`
--
CREATE TABLE IF NOT EXISTS `v_ce_list` (
`CAEX_ID` int(6) unsigned
,`CAEX_DESCRIPTION` text
,`CAEX_NAME` varchar(255)
,`CAEX_TRAINING` tinyint(1) unsigned
,`KETA_ID` int(5) unsigned
,`EXPE_ID` int(5) unsigned
,`WORK_NAME` varchar(255)
,`WORK_ID` int(5) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `v_fish_info`
--
CREATE TABLE IF NOT EXISTS `v_fish_info` (
`FISH_SAMPLE_CODE` varchar(50)
,`MEDFI_VALUE` varchar(4000)
,`ATDE_NAME` varchar(255)
,`ATDE_UNIT` varchar(255)
,`ATDE_VALUELIST` tinyint(1) unsigned
,`VALI_NAME` varchar(255)
,`CEhIM_ID` int(10) unsigned
,`UNIT` varchar(4000)
,`IMAGE_ID` int(9) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `v_imageset_info`
--
CREATE TABLE IF NOT EXISTS `v_imageset_info` (
`IMAT_FROM` varchar(4000)
,`IMAT_TO` varchar(4000)
,`VALI_NAME` varchar(255)
,`VALUE` varchar(255)
,`ATDE_NAME` varchar(255)
,`ATDE_UNIT` varchar(255)
,`ATDE_VALUELIST` tinyint(1) unsigned
,`CEhIM_ID` int(10) unsigned
,`CAEX_ID` int(6) unsigned
,`UNIT` varchar(4000)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `v_image_info`
--
CREATE TABLE IF NOT EXISTS `v_image_info` (
`IMAGE_ORIGINAL_FILENAME` varchar(255)
,`MEDIM_VALUE` varchar(4000)
,`ATDE_NAME` varchar(255)
,`ATDE_UNIT` varchar(255)
,`ATDE_VALUELIST` tinyint(1) unsigned
,`VALI_NAME` varchar(255)
,`CEhIM_ID` int(10) unsigned
,`UNIT` varchar(4000)
,`IMAGE_ID` int(9) unsigned
);
-- --------------------------------------------------------

--
-- Table structure for table `workshop`
--

CREATE TABLE IF NOT EXISTS `workshop` (
`WORK_ID` int(5) unsigned NOT NULL,
  `USER_ID` int(7) unsigned NOT NULL,
  `WORK_NAME` varchar(255) NOT NULL,
  `WORK_STARTDATE` date NOT NULL,
  `WORK_ENDDATE` date DEFAULT NULL,
  `WORK_LOCATION` varchar(255) DEFAULT NULL,
  `WORK_HOST_ORGANISATION` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ws_info`
--

CREATE TABLE IF NOT EXISTS `ws_info` (
`WSIN_ID` int(7) unsigned NOT NULL,
  `WORK_ID` int(5) unsigned NOT NULL,
  `WSIN_TEXT` varchar(255) NOT NULL,
  `WSIN_LINK` varchar(255) DEFAULT NULL,
  `WSIN_FILE` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `v_all_annotations`
--
DROP TABLE IF EXISTS `v_all_annotations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_all_annotations` AS (select `annotations`.`ANNO_ID` AS `ANNO_ID`,`annotations`.`CEhIM_ID` AS `CEhIM_ID`,`annotations`.`PARENT_ID` AS `PARENT_ID`,`annotations`.`PART_ID` AS `PART_ID`,`annotations`.`ANNO_COMMENT` AS `ANNO_COMMENT`,`annotations`.`ANNO_DATE` AS `ANNO_DATE`,`annotations`.`ANNO_GROUP` AS `ANNO_GROUP`,`annotations`.`ANNO_WS_REF` AS `ANNO_WS_REF`,`annotations`.`ANNO_WEBGR_REF` AS `ANNO_WEBGR_REF`,`annotations`.`ANNO_COUNT` AS `ANNO_COUNT`,`annotations`.`ANNO_DECIMAL` AS `ANNO_DECIMAL`,`annotations`.`ANNO_SUB` AS `ANNO_SUB`,`annotations`.`ANNO_BRIGHTNESS` AS `ANNO_BRIGHTNESS`,`annotations`.`ANNO_CONTRAST` AS `ANNO_CONTRAST`,`annotations`.`ANNO_COLOR` AS `ANNO_COLOR`,`annotations`.`ANNO_MAGNIFICATION` AS `ANNO_MAGNIFICATION`,`annotations`.`ANNO_FINAL` AS `ANNO_FINAL`,`annotations`.`ANNO_CREATE_DATE` AS `ANNO_CREATE_DATE`,`participant`.`PART_NUMBER` AS `PART_NUMBER`,`ce_has_image`.`IMAGE_ID` AS `IMAGE_ID`,`calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,`calibration_exercise`.`KETA_ID` AS `KETA_ID`,`calibration_exercise`.`WORK_ID` AS `WORK_ID`,`calibration_exercise`.`EXPE_ID` AS `EXPE_ID`,`calibration_exercise`.`CAEX_NAME` AS `CAEX_NAME`,`calibration_exercise`.`CAEX_DESCRIPTION` AS `CAEX_DESCRIPTION`,`calibration_exercise`.`CAEX_COMPAREABLE` AS `CAEX_COMPAREABLE`,`calibration_exercise`.`CAEX_RANDOMIZED` AS `CAEX_RANDOMIZED`,`calibration_exercise`.`CAEX_IS_STOPPED` AS `CAEX_IS_STOPPED`,`calibration_exercise`.`CAEX_TRAINING` AS `CAEX_TRAINING`,`workshop`.`WORK_NAME` AS `WORK_NAME`,`image`.`IMAGE_ORIGINAL_FILENAME` AS `IMAGE_ORIGINAL_FILENAME`,`key_table`.`KETA_NAME` AS `KETA_NAME` from ((((((`annotations` join `participant` on((`annotations`.`PART_ID` = `participant`.`PART_ID`))) join `ce_has_image` on((`annotations`.`CEhIM_ID` = `ce_has_image`.`CEhIM_ID`))) join `image` on((`ce_has_image`.`IMAGE_ID` = `image`.`IMAGE_ID`))) join `calibration_exercise` on((`ce_has_image`.`CAEX_ID` = `calibration_exercise`.`CAEX_ID`))) left join `workshop` on((`calibration_exercise`.`WORK_ID` = `workshop`.`WORK_ID`))) join `key_table` on((`calibration_exercise`.`KETA_ID` = `key_table`.`KETA_ID`))));

-- --------------------------------------------------------

--
-- Structure for view `v_ce_list`
--
DROP TABLE IF EXISTS `v_ce_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ce_list` AS (select `calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,`calibration_exercise`.`CAEX_DESCRIPTION` AS `CAEX_DESCRIPTION`,`calibration_exercise`.`CAEX_NAME` AS `CAEX_NAME`,`calibration_exercise`.`CAEX_TRAINING` AS `CAEX_TRAINING`,`calibration_exercise`.`KETA_ID` AS `KETA_ID`,`calibration_exercise`.`EXPE_ID` AS `EXPE_ID`,`workshop`.`WORK_NAME` AS `WORK_NAME`,`workshop`.`WORK_ID` AS `WORK_ID` from ((`calibration_exercise` left join `workshop` on((`calibration_exercise`.`WORK_ID` = `workshop`.`WORK_ID`))) left join `participant` on((`calibration_exercise`.`CAEX_ID` = `participant`.`CAEX_ID`))) group by `calibration_exercise`.`CAEX_ID`);

-- --------------------------------------------------------

--
-- Structure for view `v_fish_info`
--
DROP TABLE IF EXISTS `v_fish_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_fish_info` AS (select `fish`.`FISH_SAMPLE_CODE` AS `FISH_SAMPLE_CODE`,`meta_data_fish`.`MEDFI_VALUE` AS `MEDFI_VALUE`,`attribute_desc`.`ATDE_NAME` AS `ATDE_NAME`,`attribute_desc`.`ATDE_UNIT` AS `ATDE_UNIT`,`attribute_desc`.`ATDE_VALUELIST` AS `ATDE_VALUELIST`,`value_list`.`VALI_NAME` AS `VALI_NAME`,`ce_has_image`.`CEhIM_ID` AS `CEhIM_ID`,`unitlist`.`VALI_VALUE` AS `UNIT`,`image`.`IMAGE_ID` AS `IMAGE_ID` from (((((((`caex_has_atde` join `ce_has_image` on((`caex_has_atde`.`CAEX_ID` = `ce_has_image`.`CAEX_ID`))) join `image` on((`ce_has_image`.`IMAGE_ID` = `image`.`IMAGE_ID`))) join `fish` on((`image`.`FISH_ID` = `fish`.`FISH_ID`))) join `meta_data_fish` on(((`fish`.`FISH_ID` = `meta_data_fish`.`FISH_ID`) and (`meta_data_fish`.`ATDE_ID` = `caex_has_atde`.`ATDE_ID`)))) join `attribute_desc` on((`meta_data_fish`.`ATDE_ID` = `attribute_desc`.`ATDE_ID`))) left join `value_list` on((`meta_data_fish`.`MEDFI_VALUE` = `value_list`.`VALI_ID`))) left join `value_list` `unitlist` on((`attribute_desc`.`ATDE_UNIT` = `unitlist`.`VALI_ID`))));

-- --------------------------------------------------------

--
-- Structure for view `v_imageset_info`
--
DROP TABLE IF EXISTS `v_imageset_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_imageset_info` AS (select max(`imageset_attributes`.`IMAT_FROM`) AS `IMAT_FROM`,max(`imageset_attributes`.`IMAT_TO`) AS `IMAT_TO`,max(`value_list`.`VALI_NAME`) AS `VALI_NAME`,max(`imageset_attributes`.`VALUE`) AS `VALUE`,`attribute_desc`.`ATDE_NAME` AS `ATDE_NAME`,`attribute_desc`.`ATDE_UNIT` AS `ATDE_UNIT`,`attribute_desc`.`ATDE_VALUELIST` AS `ATDE_VALUELIST`,`ce_has_image`.`CEhIM_ID` AS `CEhIM_ID`,`calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,`unitlist`.`VALI_VALUE` AS `UNIT` from (((((`imageset_attributes` join `attribute_desc` on((`imageset_attributes`.`ATDE_ID` = `attribute_desc`.`ATDE_ID`))) left join `value_list` `unitlist` on((`attribute_desc`.`ATDE_UNIT` = `unitlist`.`VALI_ID`))) left join `value_list` on((`imageset_attributes`.`VALUE` = `value_list`.`VALI_ID`))) join `calibration_exercise` on((`imageset_attributes`.`CAEX_ID` = `calibration_exercise`.`CAEX_ID`))) join `ce_has_image` on((`calibration_exercise`.`CAEX_ID` = `ce_has_image`.`CAEX_ID`))) group by `attribute_desc`.`ATDE_NAME`,`ce_has_image`.`CEhIM_ID`);

-- --------------------------------------------------------

--
-- Structure for view `v_image_info`
--
DROP TABLE IF EXISTS `v_image_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_image_info` AS (select `image`.`IMAGE_ORIGINAL_FILENAME` AS `IMAGE_ORIGINAL_FILENAME`,`meta_data_image`.`MEDIM_VALUE` AS `MEDIM_VALUE`,`attribute_desc`.`ATDE_NAME` AS `ATDE_NAME`,`attribute_desc`.`ATDE_UNIT` AS `ATDE_UNIT`,`attribute_desc`.`ATDE_VALUELIST` AS `ATDE_VALUELIST`,`value_list`.`VALI_NAME` AS `VALI_NAME`,`ce_has_image`.`CEhIM_ID` AS `CEhIM_ID`,`unitlist`.`VALI_VALUE` AS `UNIT`,`image`.`IMAGE_ID` AS `IMAGE_ID` from ((((((`caex_has_atde` join `ce_has_image` on((`caex_has_atde`.`CAEX_ID` = `ce_has_image`.`CAEX_ID`))) join `image` on((`ce_has_image`.`IMAGE_ID` = `image`.`IMAGE_ID`))) join `meta_data_image` on(((`image`.`IMAGE_ID` = `meta_data_image`.`IMAGE_ID`) and (`meta_data_image`.`ATDE_ID` = `caex_has_atde`.`ATDE_ID`)))) join `attribute_desc` on((`meta_data_image`.`ATDE_ID` = `attribute_desc`.`ATDE_ID`))) left join `value_list` `unitlist` on((`attribute_desc`.`ATDE_UNIT` = `unitlist`.`VALI_ID`))) left join `value_list` on((`meta_data_image`.`MEDIM_VALUE` = `value_list`.`VALI_ID`))));

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annotations`
--
ALTER TABLE `annotations`
 ADD PRIMARY KEY (`ANNO_ID`), ADD KEY `ANNOTATIONS_FKIndex1` (`PART_ID`), ADD KEY `ANNOTATIONS_FKIndex2` (`PARENT_ID`), ADD KEY `ANNOTATIONS_FKIndex3` (`CEhIM_ID`);

--
-- Indexes for table `attribute_desc`
--
ALTER TABLE `attribute_desc`
 ADD PRIMARY KEY (`ATDE_ID`), ADD KEY `ATTRIBUTE_DESC_FISH_FKIndex1` (`USER_ID`);

--
-- Indexes for table `caex_has_atde`
--
ALTER TABLE `caex_has_atde`
 ADD PRIMARY KEY (`CAEX_ID`,`ATDE_ID`), ADD KEY `CAEX_has_ATDEF_FKIndex1` (`CAEX_ID`), ADD KEY `CAEX_has_ATDEF_FKIndex2` (`ATDE_ID`);

--
-- Indexes for table `calibration_exercise`
--
ALTER TABLE `calibration_exercise`
 ADD PRIMARY KEY (`CAEX_ID`), ADD KEY `CALIBRATION_EXERCISE_FKIndex1` (`KETA_ID`), ADD KEY `CALIBRATION_EXERCISE_FKIndex2` (`EXPE_ID`), ADD KEY `CALIBRATION_EXERCISE_FKIndex3` (`WORK_ID`);

--
-- Indexes for table `ce_has_image`
--
ALTER TABLE `ce_has_image`
 ADD PRIMARY KEY (`CEhIM_ID`), ADD KEY `SUBSET_HAS_IMAGE_FKIndex1` (`IMAGE_ID`), ADD KEY `CE_HAS_IMAGE_FKIndex2` (`CAEX_ID`);

--
-- Indexes for table `dots`
--
ALTER TABLE `dots`
 ADD PRIMARY KEY (`DOTS_ID`), ADD KEY `DOTS_FKIndex1` (`ANNO_ID`);

--
-- Indexes for table `expertise`
--
ALTER TABLE `expertise`
 ADD PRIMARY KEY (`EXPE_ID`);

--
-- Indexes for table `fish`
--
ALTER TABLE `fish`
 ADD PRIMARY KEY (`FISH_ID`), ADD UNIQUE KEY `FISH_SAMPLE_CODE_2` (`FISH_SAMPLE_CODE`), ADD KEY `FISH_SAMPLE_CODE` (`FISH_SAMPLE_CODE`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
 ADD PRIMARY KEY (`IMAGE_ID`), ADD KEY `IMAGE_FKIndex2` (`FISH_ID`);

--
-- Indexes for table `imageset_attributes`
--
ALTER TABLE `imageset_attributes`
 ADD PRIMARY KEY (`IMAT_ID`), ADD KEY `COLLECTION_ATTRIBUTES_FKIndex4` (`VALI_ID`), ADD KEY `COLLECTION_ATTRIBUTES_FKIndex1` (`CAEX_ID`), ADD KEY `IMAGESET_ATTRIBUTES_FKIndex3` (`ATDE_ID`);

--
-- Indexes for table `key_table`
--
ALTER TABLE `key_table`
 ADD PRIMARY KEY (`KETA_ID`);

--
-- Indexes for table `meta_data_fish`
--
ALTER TABLE `meta_data_fish`
 ADD PRIMARY KEY (`MEDFI_ID`), ADD KEY `META_DATA_FKIndex3` (`FISH_ID`), ADD KEY `META_DATA_FISH_FKIndex2` (`ATDE_ID`);

--
-- Indexes for table `meta_data_image`
--
ALTER TABLE `meta_data_image`
 ADD PRIMARY KEY (`MEDIM_ID`), ADD KEY `META_DATA_IMAGE_FKIndex1` (`IMAGE_ID`), ADD KEY `META_DATA_IMAGE_FKIndex2` (`ATDE_ID`);

--
-- Indexes for table `participant`
--
ALTER TABLE `participant`
 ADD PRIMARY KEY (`PART_ID`), ADD KEY `PARTICIPANT_FKIndex1` (`USER_ID`), ADD KEY `PARTICIPANT_FKIndex2` (`CAEX_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`USER_ID`), ADD UNIQUE KEY `USERNAME_UNIQUE` (`USER_USERNAME`), ADD UNIQUE KEY `USER_GUID` (`USER_GUID`);

--
-- Indexes for table `user_has_expertise`
--
ALTER TABLE `user_has_expertise`
 ADD PRIMARY KEY (`USER_ID`,`EXPE_ID`), ADD KEY `USER_has_STOCK_FKIndex1` (`USER_ID`), ADD KEY `USER_has_EXPERTISE_FKIndex2` (`EXPE_ID`);

--
-- Indexes for table `value_list`
--
ALTER TABLE `value_list`
 ADD PRIMARY KEY (`VALI_ID`), ADD KEY `VALUE_LIST_FKIndex1` (`ATDE_ID`);

--
-- Indexes for table `workshop`
--
ALTER TABLE `workshop`
 ADD PRIMARY KEY (`WORK_ID`), ADD KEY `WORKSHOP_FKIndex1` (`USER_ID`);

--
-- Indexes for table `ws_info`
--
ALTER TABLE `ws_info`
 ADD PRIMARY KEY (`WSIN_ID`), ADD KEY `WS_INFO_FKIndex1` (`WORK_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annotations`
--
ALTER TABLE `annotations`
MODIFY `ANNO_ID` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `attribute_desc`
--
ALTER TABLE `attribute_desc`
MODIFY `ATDE_ID` int(5) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=607;
--
-- AUTO_INCREMENT for table `calibration_exercise`
--
ALTER TABLE `calibration_exercise`
MODIFY `CAEX_ID` int(6) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ce_has_image`
--
ALTER TABLE `ce_has_image`
MODIFY `CEhIM_ID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dots`
--
ALTER TABLE `dots`
MODIFY `DOTS_ID` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `expertise`
--
ALTER TABLE `expertise`
MODIFY `EXPE_ID` int(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fish`
--
ALTER TABLE `fish`
MODIFY `FISH_ID` int(9) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
MODIFY `IMAGE_ID` int(9) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `imageset_attributes`
--
ALTER TABLE `imageset_attributes`
MODIFY `IMAT_ID` int(6) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `key_table`
--
ALTER TABLE `key_table`
MODIFY `KETA_ID` int(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `meta_data_fish`
--
ALTER TABLE `meta_data_fish`
MODIFY `MEDFI_ID` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `meta_data_image`
--
ALTER TABLE `meta_data_image`
MODIFY `MEDIM_ID` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `participant`
--
ALTER TABLE `participant`
MODIFY `PART_ID` int(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `USER_ID` int(7) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `value_list`
--
ALTER TABLE `value_list`
MODIFY `VALI_ID` int(5) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1087;
--
-- AUTO_INCREMENT for table `workshop`
--
ALTER TABLE `workshop`
MODIFY `WORK_ID` int(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ws_info`
--
ALTER TABLE `ws_info`
MODIFY `WSIN_ID` int(7) unsigned NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `annotations`
--
ALTER TABLE `annotations`
ADD CONSTRAINT `annotations_ibfk_1` FOREIGN KEY (`PART_ID`) REFERENCES `participant` (`PART_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `annotations_ibfk_2` FOREIGN KEY (`PARENT_ID`) REFERENCES `annotations` (`ANNO_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `annotations_ibfk_3` FOREIGN KEY (`CEhIM_ID`) REFERENCES `ce_has_image` (`CEhIM_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `attribute_desc`
--
ALTER TABLE `attribute_desc`
ADD CONSTRAINT `attribute_desc_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `caex_has_atde`
--
ALTER TABLE `caex_has_atde`
ADD CONSTRAINT `caex_has_atde_ibfk_2` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `caex_has_atde_ibfk_3` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `calibration_exercise`
--
ALTER TABLE `calibration_exercise`
ADD CONSTRAINT `calibration_exercise_ibfk_1` FOREIGN KEY (`EXPE_ID`) REFERENCES `expertise` (`EXPE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `calibration_exercise_ibfk_3` FOREIGN KEY (`KETA_ID`) REFERENCES `key_table` (`KETA_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `calibration_exercise_ibfk_4` FOREIGN KEY (`WORK_ID`) REFERENCES `workshop` (`WORK_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `ce_has_image`
--
ALTER TABLE `ce_has_image`
ADD CONSTRAINT `ce_has_image_ibfk_1` FOREIGN KEY (`IMAGE_ID`) REFERENCES `image` (`IMAGE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `ce_has_image_ibfk_2` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `dots`
--
ALTER TABLE `dots`
ADD CONSTRAINT `dots_ibfk_1` FOREIGN KEY (`ANNO_ID`) REFERENCES `annotations` (`ANNO_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `image`
--
ALTER TABLE `image`
ADD CONSTRAINT `image_ibfk_1` FOREIGN KEY (`FISH_ID`) REFERENCES `fish` (`FISH_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `imageset_attributes`
--
ALTER TABLE `imageset_attributes`
ADD CONSTRAINT `imageset_attributes_ibfk_1` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `imageset_attributes_ibfk_2` FOREIGN KEY (`VALI_ID`) REFERENCES `value_list` (`VALI_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `imageset_attributes_ibfk_3` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `meta_data_fish`
--
ALTER TABLE `meta_data_fish`
ADD CONSTRAINT `meta_data_fish_ibfk_4` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `meta_data_fish_ibfk_5` FOREIGN KEY (`FISH_ID`) REFERENCES `fish` (`FISH_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `meta_data_image`
--
ALTER TABLE `meta_data_image`
ADD CONSTRAINT `meta_data_image_ibfk_2` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `meta_data_image_ibfk_3` FOREIGN KEY (`IMAGE_ID`) REFERENCES `image` (`IMAGE_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `participant`
--
ALTER TABLE `participant`
ADD CONSTRAINT `participant_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `participant_ibfk_2` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `user_has_expertise`
--
ALTER TABLE `user_has_expertise`
ADD CONSTRAINT `user_has_expertise_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `user_has_expertise_ibfk_2` FOREIGN KEY (`EXPE_ID`) REFERENCES `expertise` (`EXPE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `value_list`
--
ALTER TABLE `value_list`
ADD CONSTRAINT `value_list_ibfk_1` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `workshop`
--
ALTER TABLE `workshop`
ADD CONSTRAINT `workshop_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `ws_info`
--
ALTER TABLE `ws_info`
ADD CONSTRAINT `ws_info_ibfk_1` FOREIGN KEY (`WORK_ID`) REFERENCES `workshop` (`WORK_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;