ALTER TABLE `glpi_plugin_racks_racktypes` 
   ADD `is_recursive` tinyint(1) NOT NULL default '0';
   
ALTER TABLE `glpi_plugin_racks_rackstates` 
   ADD `is_recursive` tinyint(1) NOT NULL default '0';