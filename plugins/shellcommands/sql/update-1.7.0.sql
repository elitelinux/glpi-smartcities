ALTER TABLE `glpi_plugin_shellcommands_shellcommands` ADD `tag_position` tinyint(1) NOT NULL default '1';

DROP TABLE IF EXISTS `glpi_plugin_shellcommands_commandgroups_items`;
CREATE TABLE `glpi_plugin_shellcommands_commandgroups_items` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_shellcommands_shellcommands_id` int(11) NOT NULL default '0',
        `plugin_shellcommands_commandgroups_id` int(11) NOT NULL default '0',
        `rank` int(11) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `FK_cmd` (`plugin_shellcommands_shellcommands_id`,`plugin_shellcommands_commandgroups_id`),
	KEY `plugin_shellcommands_commandgroups_id` (`plugin_shellcommands_commandgroups_id`),
        KEY `plugin_shellcommands_shellcommands_id` (`plugin_shellcommands_shellcommands_id`),
        KEY `rank` (`rank`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_shellcommands_commandgroups`;
CREATE TABLE `glpi_plugin_shellcommands_commandgroups` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL,
        `check_commands_id` int(11) NOT NULL default '0',
        `entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
        KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;