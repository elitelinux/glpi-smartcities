-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_waitingtickets'
-- Champs supplémentaire à gèrer pour les tickets en attente de GLPI
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_waitingtickets`;
CREATE TABLE `glpi_plugin_moreticket_waitingtickets` (
  `id` int(11) NOT NULL auto_increment, -- id ...
  `tickets_id` int(11) NOT NULL, -- id du ticket GLPI
  `reason` varchar(255) default NULL, -- raison de l'attente
  `date_suspension` DATETIME default NULL, -- date de suspension
  `date_report` DATETIME default NULL, -- date de report
  `date_end_suspension` DATETIME default NULL, -- date de sortie de suspension
  `plugin_moreticket_waitingtypes_id` int(11) default NULL, -- id du type d'attente
  PRIMARY KEY  (`id`), -- index
  KEY `date_suspension` (`date_suspension`),
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets(id),
  FOREIGN KEY (`plugin_moreticket_waitingtypes_id`) REFERENCES glpi_plugin_moreticket_waitingtypes(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_waitingtypes'
-- Liste des types d'attente pour un ticket 'en attente'
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_waitingtypes`;
CREATE TABLE `glpi_plugin_moreticket_waitingtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,-- nom du type d'attente
  `comment` text COLLATE utf8_unicode_ci,
  `plugin_moreticket_waitingtypes_id` int(11) NOT NULL DEFAULT '0',
  `completename` text COLLATE utf8_unicode_ci,
  `level` int(11) NOT NULL DEFAULT '0',
  `ancestors_cache` longtext COLLATE utf8_unicode_ci,
  `sons_cache` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `unicity` (`plugin_moreticket_waitingtypes_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_configs'
-- Plugin configuration
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_configs`;
CREATE TABLE `glpi_plugin_moreticket_configs` (
   `id` int(11) NOT NULL auto_increment,
   `use_waiting` tinyint(1) NOT NULL default '0',
   `use_solution` tinyint(1) NOT NULL default '0',
   `close_informations` tinyint(1) NOT NULL default '0',
   `solution_status` text COLLATE utf8_unicode_ci,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_moreticket_configs`(`id`,`use_waiting`,`use_solution`, `close_informations`, `solution_status`) VALUES (1,1,1,0, '{"5":1}');

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_closetickets'
-- informations pour un ticket 'clos'
-- 
DROP TABLE IF EXISTS `glpi_plugin_moreticket_closetickets`;
CREATE TABLE `glpi_plugin_moreticket_closetickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` int(11) NOT NULL, -- id du ticket GLPI
  `date` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `requesters_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
