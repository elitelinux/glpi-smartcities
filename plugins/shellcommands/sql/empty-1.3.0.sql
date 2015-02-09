DROP TABLE IF EXISTS `glpi_plugin_shellcommands_shellcommands`;
CREATE TABLE `glpi_plugin_shellcommands_shellcommands` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`link` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_shellcommands_shellcommandpaths_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_shellcommands_shellcommandpaths (id)',
	`parameters` varchar(255) collate utf8_unicode_ci default NULL,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_shellcommands_shellcommands` VALUES (1,0,1, 'Ping', '[IP]', '1','-c 2','0');
INSERT INTO `glpi_plugin_shellcommands_shellcommands` VALUES (2,0,1, 'Tracert', '[NAME]', '2','','0');
INSERT INTO `glpi_plugin_shellcommands_shellcommands` VALUES (3,0,1, 'Wake on Lan', '[MAC]', '0','','0');
INSERT INTO `glpi_plugin_shellcommands_shellcommands` VALUES (4,0,1, 'Nslookup', '[DOMAIN]', '3','','0');

DROP TABLE IF EXISTS `glpi_plugin_shellcommands_shellcommandpaths`;
CREATE TABLE `glpi_plugin_shellcommands_shellcommandpaths` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `comment` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_shellcommands_shellcommandpaths` (`ID`,`name`) VALUES
(1, '/bin/ping'),
(2, '/usr/bin/traceroute'),
(3,'/usr/bin/nslookup'),
(4, 'c:\\windows\\system32\\ping.exe'),
(5, 'c:\\windows\\system32\\tracert.exe'),
(6, 'c:\\windows\\system32\\nslookup.exe');

DROP TABLE IF EXISTS `glpi_plugin_shellcommands_shellcommands_items`;
CREATE TABLE `glpi_plugin_shellcommands_shellcommands_items` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_shellcommands_shellcommands_id` int(11) NOT NULL default '0',
	`itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `FK_cmd` (`plugin_shellcommands_shellcommands_id`,`itemtype`),
	KEY `itemtype` (`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_shellcommands_profiles`;
CREATE TABLE `glpi_plugin_shellcommands_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
  `shellcommands` char(1) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','2','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','3','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','4','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','5','5','0');