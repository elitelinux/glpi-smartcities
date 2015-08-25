<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2014
 
   ------------------------------------------------------------------------
 */

if (in_array('--help', $_SERVER['argv'])) {
   die("usage: ".$_SERVER['argv'][0]." [ --optimize ]\n");
}

chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

include ("../../../inc/includes.php");

// Init debug variable
$_SESSION['glpi_use_mode'] = Session::DEBUG_MODE;
$_SESSION['glpilanguage']  = "en_GB";

Session::LoadLanguage();

// Only show errors
$CFG_GLPI["debug_sql"]        = $CFG_GLPI["debug_vars"] = 0;
$CFG_GLPI["use_log_in_files"] = 1;
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
//set_error_handler('userErrorHandlerDebug');

$DB = new DB();
if (!$DB->connected) {
   die("No DB connection\n");
}

// Exit if plugin monitoring not activated
$Plugin = new Plugin();
if (!$Plugin->isActivated('monitoring')) {
   echo "Plugin monitoring not activated!\n";
   exit;
}

// * used to clean networkports
   echo "Delete orphaned networkports ";
   $pmNetworkport = new PluginMonitoringNetworkport();
   $query = "SELECT `glpi_plugin_monitoring_networkports`.* 
           FROM `glpi_plugin_monitoring_networkports`
           LEFT JOIN `glpi_networkports`
               ON `glpi_plugin_monitoring_networkports`.`networkports_id`
                  = `glpi_networkports`.`id`
           WHERE `glpi_networkports`.`id` IS NULL";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $pmNetworkport->delete($data);
      echo ".";
   }
   echo " done\n";

// * Clean services 
   echo "Delete orphaned services ";
   $pmService = new PluginMonitoringService();
   $query = "SELECT `glpi_plugin_monitoring_services`.* 
           FROM `glpi_plugin_monitoring_services`
           LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
               ON `plugin_monitoring_componentscatalogs_hosts_id`
                  = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
           WHERE `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` IS NULL";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $pmService->delete($data);
      echo ".";
   }
   echo " done\n";

// * clean services for networkport not linked with networkport
   echo "Delete services not have netwokrport linked";
   $pmService = new PluginMonitoringService();
   $query = "SELECT * 
           FROM `glpi_plugin_monitoring_services`
           WHERE `networkports_id` > 0
           GROUP BY plugin_monitoring_components_id";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $DB->query("DELETE FROM `glpi_plugin_monitoring_services`
              WHERE `plugin_monitoring_components_id`='".$data['plugin_monitoring_components_id']."'
              AND `networkports_id`='0'");
      echo ".";
   }
   echo " done\n";
   
// * clean serviceevents have service deleted
   echo "Delete orphaned serviceevents ";
   $query = "SELECT `plugin_monitoring_services_id` 
           FROM `glpi_plugin_monitoring_serviceevents`
           LEFT JOIN `glpi_plugin_monitoring_services`
               ON `plugin_monitoring_services_id`
                  = `glpi_plugin_monitoring_services`.`id`
           WHERE `glpi_plugin_monitoring_services`.`id` IS NULL
           GROUP BY `plugin_monitoring_services_id`";
   $result = $DB->query($query);
   $nb = 0;
   while ($data=$DB->fetch_array($result)) {
      $nb += countElementsInTable(
              'glpi_plugin_monitoring_serviceevents', 
              "`plugin_monitoring_services_id`='".$data['plugin_monitoring_services_id']."'");
      $DB->query("DELETE FROM `glpi_plugin_monitoring_serviceevents`
              WHERE `plugin_monitoring_services_id`='".$data['plugin_monitoring_services_id']."'");
      echo ".";
   }
   echo " deleted ".$nb." rows ";
   echo " done\n";
   