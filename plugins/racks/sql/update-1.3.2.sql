ALTER TABLE `glpi_plugin_racks_racks` 
   ADD `plugin_racks_racktypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_racktypes (id)',
   ADD `plugin_racks_rackstates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_racks_rackstates (id)';
   
CREATE TABLE `glpi_plugin_racks_racktypes` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `glpi_plugin_racks_rackstates` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;