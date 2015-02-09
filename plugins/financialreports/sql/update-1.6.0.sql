ALTER TABLE `glpi_plugin_state_profiles` DROP COLUMN `interface`, DROP COLUMN `is_default`;

DROP TABLE IF EXISTS `glpi_plugin_state_repelled`;
CREATE TABLE `glpi_plugin_state_repelled` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	`date_repelled` DATE NULL default NULL,
	PRIMARY KEY  (`ID`),
	KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;