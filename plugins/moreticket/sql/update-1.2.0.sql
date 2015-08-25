ALTER TABLE glpi_plugin_moreticket_configs ADD `date_report_mandatory` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_moreticket_configs ADD `waitingtype_mandatory` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_moreticket_configs ADD `solutiontype_mandatory` tinyint(1) NOT NULL default '0';
