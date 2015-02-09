DROP TABLE IF EXISTS `glpi_plugin_environment_profiles`;
CREATE TABLE `glpi_plugin_environment_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
  `environment` char(1) collate utf8_unicode_ci default NULL,
  `appliances` char(1) collate utf8_unicode_ci default NULL,
  `webapplications` char(1) collate utf8_unicode_ci default NULL,
  `certificates` char(1) collate utf8_unicode_ci default NULL,
  `accounts` char(1) collate utf8_unicode_ci default NULL,
  `domains` char(1) collate utf8_unicode_ci default NULL,
  `databases` char(1) collate utf8_unicode_ci default NULL,
  `badges` char(1) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;