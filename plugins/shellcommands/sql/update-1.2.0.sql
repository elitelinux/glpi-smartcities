ALTER TABLE `glpi_plugin_cmd_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;
UPDATE `glpi_plugin_cmd_path` SET `path` = '/bin/ping -c 4' WHERE `glpi_plugin_cmd_path`.`ID` =1;