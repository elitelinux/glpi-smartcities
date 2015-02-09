DROP TABLE IF EXISTS `glpi_plugin_environment_profiles`;
CREATE TABLE `glpi_plugin_environment_profiles` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `interface` varchar(50) NOT NULL default 'environment',
  `is_default` smallint(6) NOT NULL default '0',
  `environment` char(1) default NULL,
  `applicatifs` char(1) default NULL,
  `appweb` char(1) default NULL,
  `certificates` char(1) default NULL,
  `compte` char(1) default NULL,
  `connections` char(1) default NULL,
  `domain` char(1) default NULL,
  `sgbd` char(1) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `interface` (`interface`)
) ENGINE=MyISAM;