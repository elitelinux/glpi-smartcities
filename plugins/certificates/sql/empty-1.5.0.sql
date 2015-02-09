DROP TABLE IF EXISTS `glpi_plugin_certificates`;
CREATE TABLE `glpi_plugin_certificates` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`recursive` tinyint(1) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`type` tinyint(4) NOT NULL default '1',
	`dns_name` varchar(30) collate utf8_unicode_ci NOT NULL default '',
	`dns_suffix` varchar(30) collate utf8_unicode_ci NOT NULL default '',
	`FK_users` int(4) NOT NULL default '0',
	`FK_groups` int(4) NOT NULL default '0',
	`location` int(4) NOT NULL default '0',
	`FK_glpi_enterprise` int(4) NOT NULL default '0',
	`auto_sign` smallint(6) NOT NULL default '0',
	`query_date` DATE NULL default NULL,
	`expiration_date` DATE NULL default NULL,
	`status` int(4) NOT NULL default '0',
	`mailing` int(4) NOT NULL default '0',
	`command` text,
	`certificate_request` text,
	`certificate_item` text,
	`notes` LONGTEXT,
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_device`;
CREATE TABLE `glpi_plugin_certificates_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_certificate` int(11) NOT NULL default '0',
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_certificate` (`FK_certificate`,`FK_device`,`device_type`),
	KEY `FK_certificate_2` (`FK_certificate`),
	KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_certificates_type`;
CREATE TABLE `glpi_dropdown_plugin_certificates_type` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_certificates_status`;
CREATE TABLE `glpi_dropdown_plugin_certificates_status` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_profiles`;
CREATE TABLE `glpi_plugin_certificates_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`certificates` char(1) default NULL,
	`open_ticket` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_config`;
CREATE TABLE `glpi_plugin_certificates_config` (
	`ID` int(11) NOT NULL auto_increment,
	`delay` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_certificates_config` VALUES (1, '30');

DROP TABLE IF EXISTS `glpi_plugin_certificates_default`;
CREATE TABLE `glpi_plugin_certificates_default` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`status` INT( 11 ) NOT NULL 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_mailing`;
CREATE TABLE `glpi_plugin_certificates_mailing` (
	`ID` int(11) NOT NULL auto_increment,
	`type` varchar(255) collate utf8_unicode_ci default NULL,
	`FK_item` int(11) NOT NULL default '0',
	`item_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
	KEY `type` (`type`),
	KEY `FK_item` (`FK_item`),
	KEY `item_type` (`item_type`),
	KEY `items` (`item_type`,`FK_item`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_certificates_mailing VALUES ('1','certificates','1','1');

INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1700','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1700','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1700','5','4','0');