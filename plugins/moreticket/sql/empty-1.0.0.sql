DROP TABLE IF EXISTS `glpi_plugin_moreticket_profiles`;
CREATE TABLE `glpi_plugin_moreticket_profiles` (
  `id` int(11) NOT NULL auto_increment, -- id du profil
  `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)', -- lien avec profiles de glpi
  `moreticket` char(1) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `is_helpdeskvisible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `unicity` (`plugin_moreticket_waitingtypes_id`,`name`),
  KEY `is_helpdeskvisible` (`is_helpdeskvisible`)
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
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_moreticket_configs`(`id`,`use_waiting`,`use_solution`) VALUES (1,1,1);
