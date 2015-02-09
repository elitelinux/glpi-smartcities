ALTER TABLE `glpi_plugin_certificates` CHANGE `query_date` `query_date` DATE NULL default NULL;
UPDATE `glpi_plugin_certificates` SET `query_date` = NULL WHERE `query_date` ='0000-00-00';
ALTER TABLE `glpi_plugin_certificates` CHANGE `expiration_date` `expiration_date` DATE NULL default NULL;
UPDATE `glpi_plugin_certificates` SET `expiration_date` = NULL WHERE `expiration_date` ='0000-00-00';

ALTER TABLE `glpi_plugin_certificates_profiles` ADD `open_ticket` char(1) default NULL;

DROP TABLE IF EXISTS `glpi_plugin_certificates_default`;
CREATE TABLE `glpi_plugin_certificates_default` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`status` INT( 11 ) NOT NULL 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_certificates_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;