-- 
-- Structure de la table `glpi_plugin_racks_racks`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_racks`;
CREATE TABLE `glpi_plugin_racks_racks` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `serial` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_racks_rackmodels_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_rackmodels (id)',
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `plugin_racks_roomlocations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_roomlocations (id)',
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   `rack_size` int(11) NOT NULL default '0',
   `weight` decimal(20,4) NOT NULL default '0.0000',
   `height` decimal(20,4) NOT NULL default '0.0000',
   `width` decimal(20,4) NOT NULL default '0.0000',
   `depth` decimal(20,4) NOT NULL default '0.0000',
   `is_template` tinyint(1) NOT NULL default '0',
   `template_name` varchar(255) collate utf8_unicode_ci default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
   `notepad` longtext collate utf8_unicode_ci,
   `date_mod` datetime default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_racks_roomlocations_id` (`plugin_racks_roomlocations_id`),
   KEY `users_id` (`users_id`),
   KEY `groups_id` (`groups_id`),
   KEY `manufacturers_id` (`manufacturers_id`),
   KEY `locations_id` (`locations_id`),
   KEY `is_deleted` (`is_deleted`),
   KEY date_mod (date_mod),
   KEY `is_template` (`is_template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_rackmodels`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_rackmodels`;
CREATE TABLE `glpi_plugin_racks_rackmodels` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_racks_items`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_racks_items`;
CREATE TABLE `glpi_plugin_racks_racks_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_racks_racks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_racks (id)',
   `faces_id` int(11) NOT NULL default '0',
   `plugin_racks_itemspecifications_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_itemspecifications (id)',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `position` int(11) NOT NULL default '1',
   `first_powersupply` int(11) NOT NULL default '0',
   `second_powersupply` int(11) NOT NULL default '0',
   `amps` decimal(20,4) NOT NULL default '0.0000',
   `flow_rate` decimal(20,4) NOT NULL default '0.0000',
   `dissipation` decimal(20,4) NOT NULL default '0.0000',
   `weight` decimal(20,4) NOT NULL default '0.0000',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_racks_racks_id`,`items_id`,`itemtype`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`),
   KEY `faces_id` (`faces_id`),
   KEY `plugin_racks_itemspecifications_id` (`plugin_racks_itemspecifications_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_itemspecifications`
-- 
DROP TABLE IF EXISTS `glpi_plugin_racks_itemspecifications`;
CREATE TABLE `glpi_plugin_racks_itemspecifications` (
   `id` int(11) NOT NULL auto_increment,
   `model_id` int(11) NOT NULL default '0',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `nb_alim` int(11) NOT NULL default '0',
   `amps` decimal(20,4) NOT NULL default '0.0000',
   `flow_rate` decimal(20,4) NOT NULL default '0.0000',
   `dissipation` decimal(20,4) NOT NULL default '0.0000',
   `size` int(255) NOT NULL default '1',
   `weight` decimal(20,4) NOT NULL default '0.0000',
   `length` smallint(6) NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_profiles`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_profiles`;
CREATE TABLE `glpi_plugin_racks_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `racks` char(1) collate utf8_unicode_ci default NULL,
   `model` char(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_configs`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_configs`;
CREATE TABLE `glpi_plugin_racks_configs` (
	`id` int(11) NOT NULL auto_increment,
	`unit` int(11) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_racks_configs` VALUES ('1', '1');

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_roomlocations`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_roomlocations`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_racks_roomlocations` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `plugin_racks_roomlocations_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `completename` text collate utf8_unicode_ci,
   `comment` text collate utf8_unicode_ci,
   `level` int(11) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`entities_id`,`plugin_racks_roomlocations_id`,`name`),
   KEY `name` (`name`),
   KEY `plugin_racks_roomlocations_id` (`plugin_racks_roomlocations_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_connections`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_connections`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_racks_connections` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_rack_others`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_others`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_racks_others` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_racks_othermodels_id` int(11) NOT NULL default '0',
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_racks_othermodels_id` (`plugin_racks_othermodels_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_racks_othermodels`
-- 

DROP TABLE IF EXISTS `glpi_plugin_racks_othermodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_racks_othermodels` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginRacksRack','2','2','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginRacksRack','3','3','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginRacksRack','4','4','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginRacksRack','5','5','0');