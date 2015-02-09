ALTER TABLE `glpi_plugin_appweb` ADD `recursive` tinyint(1) NOT NULL default '0' AFTER `FK_entities`;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_protocol`;
CREATE TABLE `glpi_dropdown_plugin_appweb_protocol` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_appweb_protocol` ( `ID` , `name` , `comments`) VALUES ('1', 'http','');
INSERT INTO `glpi_dropdown_plugin_appweb_protocol` ( `ID` , `name` , `comments`) VALUES ('2', 'https','');