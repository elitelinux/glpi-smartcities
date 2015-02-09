DROP TABLE IF EXISTS `glpi_plugin_alerting_config`;
ALTER TABLE `glpi_plugin_alerting_state` RENAME `glpi_plugin_additionalalerts_notificationstates`;
ALTER TABLE `glpi_plugin_alerting_type` RENAME `glpi_plugin_additionalalerts_notificationtypes`;
ALTER TABLE `glpi_plugin_alerting_profiles` RENAME `glpi_plugin_additionalalerts_profiles`;
DROP TABLE IF EXISTS `glpi_plugin_alerting_mailing`;

DROP TABLE IF EXISTS `glpi_plugin_additionalalerts_configs`;
CREATE TABLE `glpi_plugin_additionalalerts_configs` (
	`id` int(11) NOT NULL auto_increment,
	`delay_reminder` int(11) NOT NULL default '-1',
	`delay_ocs` int(11) NOT NULL default '-1',
	`use_infocom_alert` TINYINT( 1 ) NOT NULL DEFAULT '-1',
	`use_newocs_alert` TINYINT( 1 ) NOT NULL DEFAULT '-1',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_additionalalerts_configs` ( `id`,`delay_reminder`,`delay_ocs`,`use_infocom_alert`,`use_newocs_alert`) VALUES ('1','-1','-1','-1','-1');

DROP TABLE IF EXISTS `glpi_plugin_additionalalerts_reminderalerts`;
CREATE TABLE `glpi_plugin_additionalalerts_reminderalerts` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`delay_reminder` int(11) NOT NULL default '-1',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_additionalalerts_ocsalerts`;
CREATE TABLE `glpi_plugin_additionalalerts_ocsalerts` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`delay_ocs` int(11) NOT NULL default '-1',
	`use_newocs_alert` TINYINT( 1 ) NOT NULL DEFAULT '-1',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_additionalalerts_infocomalerts`;
CREATE TABLE `glpi_plugin_additionalalerts_infocomalerts` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`use_infocom_alert` TINYINT( 1 ) NOT NULL DEFAULT '-1',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_additionalalerts_notificationstates` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `state` `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)';

ALTER TABLE `glpi_plugin_additionalalerts_notificationtypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `type` `types_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_computertypes (id)';

ALTER TABLE `glpi_plugin_additionalalerts_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `alerting` `additionalalerts` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

UPDATE `glpi_plugin_additionalalerts_profiles` SET `additionalalerts`='w' WHERE `additionalalerts` ='r';

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert infocoms', 'PluginAdditionalalertsInfocomAlert', '2010-03-13 10:44:46','',NULL);
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert machines ocs', 'PluginAdditionalalertsOcsAlert', '2010-03-13 10:44:46','',NULL);
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert reminders', 'PluginAdditionalalertsReminderAlert', '2010-03-13 10:44:46','',NULL);