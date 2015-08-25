ALTER TABLE glpi_plugin_moreticket_configs ADD `solution_status` text COLLATE utf8_unicode_ci;
ALTER TABLE glpi_plugin_moreticket_configs ADD `close_informations` tinyint NOT NULL DEFAULT '0';

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
  PRIMARY KEY (`id`),
  KEY (`tickets_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;