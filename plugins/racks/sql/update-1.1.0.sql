ALTER TABLE `glpi_plugin_rack` RENAME `glpi_plugin_racks_racks`;
ALTER TABLE `glpi_plugin_rack_content` RENAME `glpi_plugin_racks_racks_items`;
ALTER TABLE `glpi_plugin_rack_device_spec` RENAME `glpi_plugin_racks_itemspecifications`;
ALTER TABLE `glpi_plugin_rack_config` RENAME `glpi_plugin_racks_configs`;
ALTER TABLE `glpi_dropdown_plugin_rack_room_locations` RENAME `glpi_plugin_racks_roomlocations`;
ALTER TABLE `glpi_dropdown_plugin_rack_ways` RENAME `glpi_plugin_racks_connections`;
ALTER TABLE `glpi_plugin_rack_others` RENAME `glpi_plugin_racks_others`;
ALTER TABLE `glpi_dropdown_plugin_rack_others_type` RENAME `glpi_plugin_racks_othermodels`;
ALTER TABLE `glpi_plugin_rack_profiles` RENAME `glpi_plugin_racks_profiles`;

ALTER TABLE `glpi_plugin_racks_racks` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   ADD `serial` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `plugin_racks_rackmodels_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_rackmodels (id)',
   CHANGE `FK_location` `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   CHANGE `room_location` `plugin_racks_roomlocations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_roomlocations (id)',
   CHANGE `rack_size` `rack_size` int(11) NOT NULL default '0',
   CHANGE `FK_glpi_enterprise` `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   CHANGE `tech_num` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `is_template` `is_template` tinyint(1) NOT NULL default '0',
   CHANGE `tplname` `template_name` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `date_mod` datetime default NULL,
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_racks_roomlocations_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`groups_id`),
   ADD INDEX (`manufacturers_id`),
   ADD INDEX (`locations_id`),
   ADD INDEX (`is_deleted`),
   ADD INDEX (`is_template`),
   ADD INDEX (`date_mod`);

ALTER TABLE `glpi_plugin_racks_racks_items` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_rack` `plugin_racks_racks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_racks (id)',
   CHANGE `FK_face` `faces_id` int(11) NOT NULL default '0',
   CHANGE `FK_spec` `plugin_racks_itemspecifications_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_itemspecifications (id)',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   ADD UNIQUE `unicity` (`plugin_racks_racks_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`),
   ADD INDEX (`plugin_racks_racks_id`),
   ADD INDEX (`faces_id`),
   ADD INDEX (`plugin_racks_itemspecifications_id`);

ALTER TABLE `glpi_plugin_racks_itemspecifications` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_model` `model_id` int(11) NOT NULL default '0',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file';

ALTER TABLE `glpi_plugin_racks_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `rack` `racks` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_racks_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `unit` `unit` int(11) NOT NULL default '0';

ALTER TABLE `glpi_plugin_racks_roomlocations` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `parentID` `plugin_racks_roomlocations_id` int(11) NOT NULL default '0',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   DROP INDEX `name`,
   DROP INDEX `parentID`,
   DROP INDEX `FK_entities`,
   ADD INDEX (`name`),
   ADD INDEX (`plugin_racks_roomlocations_id`),
   ADD INDEX (`entities_id`),
   ADD UNIQUE (`entities_id`,`plugin_racks_roomlocations_id`,`name`);

ALTER TABLE `glpi_plugin_racks_connections` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_racks_others` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `model` `plugin_racks_othermodels_id` int(11) NOT NULL default '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_racks_othermodels_id`);

ALTER TABLE `glpi_plugin_racks_othermodels` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;


CREATE TABLE `glpi_plugin_racks_rackmodels` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE `glpi_plugin_racks_itemspecifications` SET `itemtype` = 'ComputerModel' WHERE `itemtype` = 1;
UPDATE `glpi_plugin_racks_itemspecifications` SET `itemtype` = 'NetworkEquipmentModel' WHERE `itemtype` = 2;
UPDATE `glpi_plugin_racks_itemspecifications` SET `itemtype` = 'PeripheralModel' WHERE `itemtype` = 5;
UPDATE `glpi_plugin_racks_itemspecifications` SET `itemtype` = 'PluginRacksOtherModel' WHERE `itemtype` = 4451;

UPDATE `glpi_plugin_racks_racks_items` SET `itemtype` = 'ComputerModel' WHERE `itemtype` = 1;
UPDATE `glpi_plugin_racks_racks_items` SET `itemtype` = 'NetworkEquipmentModel' WHERE `itemtype` = 2;
UPDATE `glpi_plugin_racks_racks_items` SET `itemtype` = 'PeripheralModel' WHERE `itemtype` = 5;
UPDATE `glpi_plugin_racks_racks_items` SET `itemtype` = 'PluginRacksOtherModel' WHERE `itemtype` = 4451;