DROP TABLE IF EXISTS `glpi_plugin_cmd_path`;
CREATE TABLE `glpi_plugin_cmd_path` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_cmd` int(11) NOT NULL,
  `FK_type` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `path` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

INSERT INTO `glpi_plugin_cmd_path` (`ID`, `FK_cmd`, `FK_type`, `path`) VALUES 
(1, 1, 'linux', '/bin/ping'),
(2, 2, 'linux', '/usr/bin/traceroute'),
(3, 4, 'linux', '/usr/bin/nslookup'),
(4, 1, 'windows', 'c:\\windows\\system32\\ping.exe'),
(5, 2, 'windows', 'c:\\windows\\system32\\tracert.exe'),
(6, 4, 'windows', 'c:\\windows\\system32\\nslookup.exe');
