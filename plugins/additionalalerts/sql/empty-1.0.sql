DROP TABLE IF EXISTS `glpi_plugin_alerting_config`;
CREATE TABLE `glpi_plugin_alerting_config` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`alerting_tickets` smallint(6) NOT NULL default '0',
	`delay_tickets` int(11) NOT NULL default '30',
	`alerting_reservations` smallint(6) NOT NULL default '0',
	`delay_reminder` int(11) NOT NULL default '5',
	`alerting_reminder` smallint(6) NOT NULL default '0',
	`delay_reservations` int(11) NOT NULL default '5',
	`alerting_ocs` smallint(6) NOT NULL default '0',
	`delay_ocs` int(11) NOT NULL default '90'
) TYPE = MYISAM;

INSERT INTO `glpi_plugin_alerting_config` ( `ID`, `alerting_tickets`, `delay_tickets`,`alerting_reservations`,`delay_reservations`,`alerting_reminder`,`delay_reminder`,`alerting_ocs`, `delay_ocs`) VALUES ('1','0','30','0','5','0','5','0','90');

DROP TABLE IF EXISTS `glpi_plugin_alerting_state`;
CREATE TABLE `glpi_plugin_alerting_state` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`state` INT( 11 ) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_alerting_profiles`;
CREATE TABLE `glpi_plugin_alerting_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) default NULL,
	`interface` varchar(50) NOT NULL default 'alerting',
	`is_default` smallint(6) NOT NULL default '0',
	`alerting` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `glpi_plugin_alerting_mailing`;
CREATE TABLE `glpi_plugin_alerting_mailing` (
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_alerting_mailing VALUES ('1','tickets','1','1');
INSERT INTO glpi_plugin_alerting_mailing VALUES ('2','ocs','1','1');
INSERT INTO glpi_plugin_alerting_mailing VALUES ('3','reservations','1','1');
INSERT INTO glpi_plugin_alerting_mailing VALUES ('4','reminder','1','1');	