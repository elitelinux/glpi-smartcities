DROP TABLE IF EXISTS `glpi_plugin_appweb`;
CREATE TABLE `glpi_plugin_appweb` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`type` int(4) NOT NULL default '0',
	`server` INT(4) NOT NULL DEFAULT '0',
	`technic` INT(4) NOT NULL DEFAULT '0',
	`version` VARCHAR(255) collate utf8_unicode_ci NOT NULL DEFAULT '',
	`port` VARCHAR(255) collate utf8_unicode_ci NOT NULL DEFAULT '',
	`protocol` INT(4) NOT NULL DEFAULT '0',
	`link_name` varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT '',
	`FK_enterprise` SMALLINT(6) NOT NULL DEFAULT '0',
	`FK_glpi_enterprise` SMALLINT(6) NOT NULL DEFAULT '0',
	`target` smallint(6) NOT NULL default '0',
	`location` INT(4) NOT NULL DEFAULT '0',
	`notes` LONGTEXT,
	`comment` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_type`;
	CREATE TABLE `glpi_dropdown_plugin_appweb_type` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_server_type`;
CREATE TABLE `glpi_dropdown_plugin_appweb_server_type` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_appweb_server_type` ( `ID` , `name` , `comments`) VALUES ('1', 'Apache','');
INSERT INTO `glpi_dropdown_plugin_appweb_server_type` ( `ID` , `name` , `comments`) VALUES ('2', 'IIS','');
INSERT INTO `glpi_dropdown_plugin_appweb_server_type` ( `ID` , `name` , `comments`)  VALUES ('3', 'Tomcat','');

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_technic`;
CREATE TABLE `glpi_dropdown_plugin_appweb_technic` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci  NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('1', 'Asp','');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('2', 'Cgi','');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('3', 'Java','');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('4', 'Perl','');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('5', 'Php','');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` ( `ID` , `name` , `comments`) VALUES ('6', '.Net','');

DROP TABLE IF EXISTS `glpi_plugin_appweb_device`;
CREATE TABLE `glpi_plugin_appweb_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_appweb` int(11) NOT NULL default '0',
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_compte` (`FK_appweb`,`FK_device`,`device_type`),
	KEY `FK_appweb_2` (`FK_appweb`),
	KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_appweb_profiles`;
CREATE TABLE `glpi_plugin_appweb_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'appweb',
	`is_default` smallint(6) NOT NULL default '0',
	`appweb` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1300','2','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1300','6','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1300','7','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1300','8','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1300','12','6','0');