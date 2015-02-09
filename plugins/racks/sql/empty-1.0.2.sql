-- 
-- Structure de la table `glpi_plugin_rack`
-- 

DROP TABLE IF EXISTS `glpi_plugin_rack`;
CREATE TABLE `glpi_plugin_rack` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `FK_location` int(11) NOT NULL,
  `room_location` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `rack_size` int(11) NOT NULL,
  `FK_glpi_enterprise` smallint(6) NOT NULL,
  `deleted` smallint(6) NOT NULL default '0',
  `FK_entities` tinyint(4) NOT NULL default '0',
  `recursive` tinyint(11) NOT NULL default '0',
  `notes` LONGTEXT,
  `FK_groups` int(11)  NOT NULL default '0',
  `tech_num` int(11) NOT NULL default '0',
  `weight` decimal(20,4) NOT NULL default '0.0000',
  `height` decimal(20,4) NOT NULL default '0.0000',
  `width` decimal(20,4) NOT NULL default '0.0000',
  `depth` decimal(20,4) NOT NULL default '0.0000',
  `is_template` int(1) NOT NULL default 0,
  `tplname` varchar(255) NULL,      
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `glpi_plugin_rack_content`
-- 

DROP TABLE IF EXISTS `glpi_plugin_rack_content`;
CREATE TABLE `glpi_plugin_rack_content` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_rack` int(11) NOT NULL default '0',
  `FK_face` int(11)  NOT NULL default '0',
  `FK_spec` int(11) NOT NULL default '0',
  `FK_device` int(11) NOT NULL default '0',
  `device_type` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '1',
  `first_powersupply` int(11) NOT NULL default '0',
  `second_powersupply` int(11) NOT NULL default '0',
  `amps` decimal(20,4) NOT NULL default '0.0000',
  `flow_rate` decimal(20,4) NOT NULL default '0.0000',
  `dissipation` decimal(20,4) NOT NULL default '0.0000',
  `weight` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;


-- --------------------------------------------------------

-- 
-- Structure de la table `glpi_plugin_rack_device_spec`
-- 

CREATE TABLE `glpi_plugin_rack_device_spec` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_model` int(11) NOT NULL default '0',
  `device_type` int(11) NOT NULL default '0',
  `nb_alim` int(11) NOT NULL default '0',
  `amps` decimal(20,4) NOT NULL default '0.0000',
  `flow_rate` decimal(20,4) NOT NULL default '0.0000',
  `dissipation` decimal(20,4) NOT NULL default '0.0000',
  `size` int(255) NOT NULL default '1',
  `weight` decimal(20,4) NOT NULL default '0.0000',
  `length` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_rack_profiles`
-- 

DROP TABLE IF EXISTS `glpi_plugin_rack_profiles`;
CREATE TABLE `glpi_plugin_rack_profiles` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `rack` char(1) collate utf8_unicode_ci default NULL,
  `model` char(1) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_rack_config`
-- 

DROP TABLE IF EXISTS `glpi_plugin_rack_config`;
CREATE TABLE `glpi_plugin_rack_config` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`unit` INT( 11 ) NOT NULL 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_rack_config` (`ID`, `unit`) VALUES ('1', '1');

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_dropdown_plugin_rack_room_locations`
-- 

DROP TABLE IF EXISTS `glpi_dropdown_plugin_rack_room_locations`;
CREATE TABLE IF NOT EXISTS `glpi_dropdown_plugin_rack_room_locations` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_entities` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `parentID` int(11) NOT NULL default '0',
  `completename` text collate utf8_unicode_ci,
  `comments` text collate utf8_unicode_ci,
  `level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `name` (`name`,`parentID`,`FK_entities`),
  KEY `parentID` (`parentID`),
  KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_dropdown_plugin_rack_ways`
-- 

DROP TABLE IF EXISTS `glpi_dropdown_plugin_rack_ways`;
CREATE TABLE IF NOT EXISTS `glpi_dropdown_plugin_rack_ways` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comments` text collate utf8_unicode_ci,
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_rack_others`
-- 

DROP TABLE IF EXISTS `glpi_plugin_rack_others`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_rack_others` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_entities` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `model` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_dropdown_plugin_rack_others_type`
-- 

DROP TABLE IF EXISTS `glpi_dropdown_plugin_rack_others_type`;
CREATE TABLE IF NOT EXISTS `glpi_dropdown_plugin_rack_others_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comments` text collate utf8_unicode_ci,
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

INSERT INTO glpi_display VALUES (NULL,'4450','2','2','0');
INSERT INTO glpi_display VALUES (NULL,'4450','3','3','0');
INSERT INTO glpi_display VALUES (NULL,'4450','4','4','0');
INSERT INTO glpi_display VALUES (NULL,'4450','5','5','0');