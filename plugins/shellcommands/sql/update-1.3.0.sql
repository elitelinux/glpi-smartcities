ALTER TABLE `glpi_plugin_cmd` RENAME `glpi_plugin_shellcommands_shellcommands`;
ALTER TABLE `glpi_plugin_cmd_device` RENAME `glpi_plugin_shellcommands_shellcommands_items`;
DROP TABLE IF EXISTS `glpi_plugin_cmd_setup`;
ALTER TABLE `glpi_plugin_cmd_profiles` RENAME `glpi_plugin_shellcommands_profiles`;
ALTER TABLE `glpi_plugin_cmd_path` RENAME `glpi_plugin_shellcommands_shellcommandpaths`;

ALTER TABLE `glpi_plugin_shellcommands_shellcommands` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `entities_id` int(11) NOT NULL default '0',
   ADD `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `link` `link` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `plugin_shellcommands_shellcommandpaths_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_shellcommands_shellcommandpaths (id)',
   ADD `parameters` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`is_deleted`),
   ADD INDEX (`entities_id`);

ALTER TABLE `glpi_plugin_shellcommands_shellcommandpaths` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `path` `name` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `comment` text collate utf8_unicode_ci,
   DROP `FK_cmd`,
   DROP `FK_type`,
   ADD INDEX (`name`);

ALTER TABLE `glpi_plugin_shellcommands_shellcommands_items` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_cmd` `plugin_shellcommands_shellcommands_id` int(11) NOT NULL default '0',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   DROP INDEX `FK_cmd`,
   DROP INDEX `FK_cmd_2`,
   DROP INDEX `device_type`,
   ADD UNIQUE `FK_cmd` (`plugin_shellcommands_shellcommands_id`,`itemtype`),
   ADD INDEX `itemtype` (`itemtype`);

ALTER TABLE `glpi_plugin_shellcommands_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `cmd` `shellcommands` char(1) collate utf8_unicode_ci default NULL,
   DROP `update_cmd`,
   ADD INDEX (`profiles_id`);

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','2','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','3','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','4','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginShellcommandsShellcommand','5','5','0');
