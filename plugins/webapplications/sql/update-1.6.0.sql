DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_protocol`;

ALTER TABLE `glpi_plugin_appweb` RENAME `glpi_plugin_webapplications_webapplications`;
ALTER TABLE `glpi_dropdown_plugin_appweb_type` RENAME `glpi_plugin_webapplications_webapplicationtypes`;
ALTER TABLE `glpi_dropdown_plugin_appweb_server_type` RENAME `glpi_plugin_webapplications_webapplicationservertypes`;
ALTER TABLE `glpi_dropdown_plugin_appweb_technic` RENAME `glpi_plugin_webapplications_webapplicationtechnics`;
ALTER TABLE `glpi_plugin_appweb_device` RENAME `glpi_plugin_webapplications_webapplications_items`;
ALTER TABLE `glpi_plugin_appweb_profiles` RENAME `glpi_plugin_webapplications_profiles`;

UPDATE `glpi_plugin_webapplications_webapplications` SET `FK_users` = '0' WHERE `FK_users` IS NULL;

ALTER TABLE `glpi_plugin_webapplications_webapplications` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `address` `address` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `backoffice` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `type` `plugin_webapplications_webapplicationtypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
   CHANGE `server` `plugin_webapplications_webapplicationservertypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
   CHANGE `technic` `plugin_webapplications_webapplicationtechnics_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
   CHANGE `version` `version` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_users` `users_id` int(11) default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `FK_glpi_enterprise` `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   CHANGE `location` `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   CHANGE `helpdesk_visible` `is_helpdesk_visible` int(11) NOT NULL default '1',
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `comment` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_webapplications_webapplicationtypes_id`),
   ADD INDEX (`plugin_webapplications_webapplicationservertypes_id`),
   ADD INDEX (`plugin_webapplications_webapplicationtechnics_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`groups_id`),
   ADD INDEX (`suppliers_id`),
   ADD INDEX (`manufacturers_id`),
   ADD INDEX (`locations_id`),
   ADD INDEX (`date_mod`),
   ADD INDEX (`is_helpdesk_visible`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_webapplications_webapplicationtypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplicationservertypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplicationtechnics` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplications_items` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_appweb` `plugin_webapplications_webapplications_id` int(11) NOT NULL default '0',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   DROP INDEX `FK_compte`,
   DROP INDEX `FK_appweb_2`,
   DROP INDEX `FK_device`,
   ADD UNIQUE `unicity` (`plugin_webapplications_webapplications_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`);

ALTER TABLE `glpi_plugin_webapplications_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `appweb` `webapplications` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `open_ticket` `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);
