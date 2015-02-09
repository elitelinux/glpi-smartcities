DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplications`;
CREATE TABLE `glpi_plugin_webapplications_webapplications` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`address` varchar(255) collate utf8_unicode_ci default NULL,
	`backoffice` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_webapplications_webapplicationtypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
	`plugin_webapplications_webapplicationservertypes_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
	`plugin_webapplications_webapplicationtechnics_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
	`version` VARCHAR(255) collate utf8_unicode_ci default NULL,
	`users_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`groups_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
	`suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
	`manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
	`locations_id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_locations (id)',
	`date_mod` datetime default NULL,
	`is_helpdesk_visible` int(11) NOT NULL default '1',
	`notepad` longtext collate utf8_unicode_ci,
	`comment` text collate utf8_unicode_ci,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_webapplications_webapplicationtypes_id` (`plugin_webapplications_webapplicationtypes_id`),
  KEY `plugin_webapplications_webapplicationservertypes_id` (`plugin_webapplications_webapplicationservertypes_id`),
  KEY `plugin_webapplications_webapplicationtechnics_id` (`plugin_webapplications_webapplicationtechnics_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `locations_id` (`locations_id`),
  KEY date_mod (date_mod),
  KEY is_helpdesk_visible (is_helpdesk_visible),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtypes`;
	CREATE TABLE `glpi_plugin_webapplications_webapplicationtypes` (
	`id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationservertypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationservertypes` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('1', 'Apache','');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('2', 'IIS','');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('3', 'Tomcat','');

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtechnics`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtechnics` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('1', 'Asp','');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('2', 'Cgi','');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('3', 'Java','');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('4', 'Perl','');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('5', 'Php','');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('6', '.Net','');

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplications_items`;
CREATE TABLE `glpi_plugin_webapplications_webapplications_items` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_webapplications_webapplications_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplications (id)',
	`items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unicity` (`plugin_webapplications_webapplications_id`,`items_id`,`itemtype`),
  KEY `FK_device` (`items_id`,`itemtype`),
  KEY `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_profiles`;
CREATE TABLE `glpi_plugin_webapplications_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`webapplications` char(1) collate utf8_unicode_ci default NULL,
	`open_ticket` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebapplicationsWebapplication','2','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebapplicationsWebapplication','3','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebapplicationsWebapplication','6','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebapplicationsWebapplication','7','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebapplicationsWebapplication','8','7','0');