DROP TABLE IF EXISTS `glpi_plugin_certificates_certificates`;
CREATE TABLE `glpi_plugin_certificates_certificates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_certificates_certificatetypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_certificates_certificatetypes (id)',
   `dns_name` varchar(255) collate utf8_unicode_ci default NULL,
   `dns_suffix` varchar(255) collate utf8_unicode_ci default NULL,
   `users_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   `auto_sign` smallint(6) NOT NULL default '0',
   `date_query` date default NULL,
   `date_expiration` date default NULL,
   `plugin_certificates_certificatestates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_certificates_certificatestates (id)',
   `mailing` int(11) NOT NULL default '0',
   `command` text collate utf8_unicode_ci,
   `certificate_request` text collate utf8_unicode_ci,
   `certificate_item` text collate utf8_unicode_ci,
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `date_mod` datetime default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_certificates_certificatetypes_id` (`plugin_certificates_certificatetypes_id`),
   KEY `users_id_tech` (`users_id_tech`),
   KEY `groups_id_tech` (`groups_id_tech`),
   KEY `locations_id` (`locations_id`),
   KEY `manufacturers_id` (`manufacturers_id`),
   KEY `plugin_certificates_certificatestates_id` (`plugin_certificates_certificatestates_id`),
   KEY `date_mod` (`date_mod`),
   KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_certificates_items`;
CREATE TABLE `glpi_plugin_certificates_certificates_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_certificates_certificates_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_certificates_certificates_id`,`itemtype`,`items_id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_certificatetypes`;
CREATE TABLE `glpi_plugin_certificates_certificatetypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_certificatestates`;
CREATE TABLE `glpi_plugin_certificates_certificatestates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_certificates_configs`;
CREATE TABLE `glpi_plugin_certificates_configs` (
   `id` int(11) NOT NULL auto_increment,
   `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_certificates_configs` VALUES (1, '30', '30');

DROP TABLE IF EXISTS `glpi_plugin_certificates_notificationstates`;
CREATE TABLE `glpi_plugin_certificates_notificationstates` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_certificates_certificatestates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_certificates_certificatestates (id)',
   PRIMARY KEY  (`id`),
   KEY `plugin_certificates_certificatestates_id` (`plugin_certificates_certificatestates_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Certificates', 'PluginCertificatesCertificate', '2010-02-24 21:34:46','',NULL);

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginCertificatesCertificate','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginCertificatesCertificate','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginCertificatesCertificate','5','4','0');