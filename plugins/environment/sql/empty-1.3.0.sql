DROP TABLE IF EXISTS `glpi_plugin_environment_profiles`;
CREATE TABLE `glpi_plugin_environment_profiles` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `environment` char(1) default NULL,
  `applicatifs` char(1) default NULL,
  `appweb` char(1) default NULL,
  `certificates` char(1) default NULL,
  `compte` char(1) default NULL,
  `connections` char(1) default NULL,
  `domain` char(1) default NULL,
  `sgbd` char(1) default NULL,
  `backups` char(1) default NULL,
  `parametre` char(1) default NULL,
  `badges` char(1) default NULL,
  `droits` char(1) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`)
) ENGINE=MyISAM;