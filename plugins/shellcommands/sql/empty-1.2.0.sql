DROP TABLE IF EXISTS `glpi_plugin_cmd`;
CREATE TABLE `glpi_plugin_cmd` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`link` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_cmd` VALUES (1, 'Ping', '[IP]', '0');
INSERT INTO `glpi_plugin_cmd` VALUES (2, 'Tracert', '[NAME]', '0');
INSERT INTO `glpi_plugin_cmd` VALUES (3, 'Wake on Lan', '[MAC]', '0');
INSERT INTO `glpi_plugin_cmd` VALUES (4, 'Nslookup', '[DOMAIN]', '0');

DROP TABLE IF EXISTS `glpi_plugin_cmd_path`;
CREATE TABLE `glpi_plugin_cmd_path` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_cmd` int(11) NOT NULL,
  `FK_type` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `path` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

INSERT INTO `glpi_plugin_cmd_path` (`ID`, `FK_cmd`, `FK_type`, `path`) VALUES 
(1, 1, 'linux', '/bin/ping -c 4'),
(2, 2, 'linux', '/usr/bin/traceroute'),
(3, 4, 'linux', '/usr/bin/nslookup'),
(4, 1, 'windows', 'c:\\windows\\system32\\ping.exe'),
(5, 2, 'windows', 'c:\\windows\\system32\\tracert.exe'),
(6, 4, 'windows', 'c:\\windows\\system32\\nslookup.exe');

DROP TABLE IF EXISTS `glpi_plugin_cmd_device`;
CREATE TABLE `glpi_plugin_cmd_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_cmd` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_cmd` (`FK_cmd`,`device_type`),
	KEY `FK_cmd_2` (`FK_cmd`),
	KEY `device_type` (`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_cmd_setup`;
CREATE TABLE `glpi_plugin_cmd_setup` (
	`ID` int(11) NOT NULL auto_increment,
	`type` varchar(50)  collate utf8_unicode_ci NOT NULL default 'linux',
PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_cmd_setup` ( `ID` , `type` ) VALUES ('1', 'linux');

DROP TABLE IF EXISTS `glpi_plugin_cmd_profiles`;
CREATE TABLE `glpi_plugin_cmd_profiles` (
`ID` int(11) NOT NULL auto_increment,
`name` varchar(255) default NULL,
`cmd` char(1) default NULL,
`update_cmd` char(1) default NULL,
PRIMARY KEY  (`ID`),
KEY `name` (`name`)
) TYPE=MyISAM;