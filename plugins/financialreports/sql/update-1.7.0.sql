ALTER TABLE `glpi_plugin_state_profiles` RENAME `glpi_plugin_financialreports_profiles`;
ALTER TABLE `glpi_plugin_state_config` RENAME `glpi_plugin_financialreports_configs`;
ALTER TABLE `glpi_plugin_state_parameters` RENAME `glpi_plugin_financialreports_parameters`;
ALTER TABLE `glpi_plugin_state_repelled` RENAME `glpi_plugin_financialreports_disposalitems`;

ALTER TABLE `glpi_plugin_financialreports_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `state` `financialreports` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_financialreports_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `state` `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)';

ALTER TABLE `glpi_plugin_financialreports_parameters` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `computer` `computers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `notebook` `notebooks_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `server` `servers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `monitor` `monitors_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `printer` `printers_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `peripheral` `peripherals_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `networking` `networkequipments_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `phone` `phones_otherserial` varchar(255) collate utf8_unicode_ci default NULL;

ALTER TABLE `glpi_plugin_financialreports_disposalitems` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `device_type` `itemtype`  varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   CHANGE `date_repelled` `date_disposal` DATE default NULL,
   DROP INDEX `FK_device`,
   ADD UNIQUE `unicity` (`items_id`,`itemtype`);