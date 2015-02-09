ALTER TABLE `glpi_plugin_alerting_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;
INSERT INTO glpi_plugin_alerting_mailing VALUES ('5','newocs','1','1');
ALTER TABLE `glpi_plugin_alerting_config` ADD `alerting_new_ocs` SMALLINT( 6 ) NOT NULL AFTER `delay_ocs`;
INSERT INTO glpi_plugin_alerting_mailing VALUES ('6','notinfocom','1','1');
ALTER TABLE `glpi_plugin_alerting_config` ADD `alerting_not_infocom` SMALLINT( 6 ) NOT NULL AFTER `alerting_new_ocs`;

CREATE TABLE `glpi_plugin_alerting_type` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`type` INT( 11 ) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;