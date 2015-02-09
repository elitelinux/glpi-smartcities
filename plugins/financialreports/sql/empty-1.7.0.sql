DROP TABLE IF EXISTS `glpi_plugin_financialreports_profiles`;
CREATE TABLE `glpi_plugin_financialreports_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`financialreports` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_financialreports_configs`;
CREATE TABLE `glpi_plugin_financialreports_configs` (
	`id` int(11) NOT NULL auto_increment,
	`states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_financialreports_parameters`;
CREATE TABLE `glpi_plugin_financialreports_parameters` (
	`id` int(11) NOT NULL auto_increment,
	`computers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`notebooks_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`servers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`monitors_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`printers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`peripherals_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`networkequipments_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	`phones_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_financialreports_parameters` ( `computers_otherserial` , `notebooks_otherserial` , `servers_otherserial`, `monitors_otherserial`, `printers_otherserial`, `peripherals_otherserial`, `networkequipments_otherserial`, `phones_otherserial`) VALUES (NULL , NULL, NULL, NULL, NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `glpi_plugin_financialreports_disposalitems`;
CREATE TABLE `glpi_plugin_financialreports_disposalitems` (
	`id` int(11) NOT NULL auto_increment,
	`items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
	`itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	`date_disposal` DATE default NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unicity` (`items_id`,`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;