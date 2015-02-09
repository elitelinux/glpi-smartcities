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
   @since     2011

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkCentralAccess();

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "host_service");


$pMonitoringService = new PluginMonitoringService();

//echo "<pre>";print_r($_POST);exit;

if (isset($_POST['add'])) {

   $pMonitoringServicedef = new PluginMonitoringServicedef();
   $_POST['plugin_monitoring_servicedefs_id'] = $pMonitoringServicedef->add($_POST);
   if (isset($_POST['arg'])) {
      $_POST['arguments'] = exportArrayToDB($_POST['arg']);
   }
   if (isset($_POST['alias_commandservice'])) {
      $_POST['alias_command'] = $_POST['alias_commandservice'];
   }
   if ($_POST['plugin_monitoring_servicedefs_id'] == '0') {
      $_POST['plugin_monitoring_servicedefs_id'] = $_POST['plugin_monitoring_servicedefs_id_s'];
   }
   $pMonitoringService->add($_POST);
   Html::back();
} else if (isset($_POST['update'])) {
   if (is_array($_POST['id'])) {
      foreach ($_POST['id'] as $key=>$id) {
         $input = array();
         $input['id'] = $id;
         $input['plugin_monitoring_servicedefs_id'] = $_POST['plugin_monitoring_servicedefs_id'][$key];
         $a_arguments = array();
         foreach ($_POST as $key=>$value) {
            if (strstr($key, "arg".$id."||")) {
               $a_ex = explode("||", $key);
               $a_arguments[$a_ex[1]] = $value;
            }
         }
         $input['arguments'] = exportArrayToDB($a_arguments);
         $pMonitoringService->update($input);
      }
   } else {
      $pMonitoringServicedef = new PluginMonitoringServicedef();
      if ($_POST['plugin_monitoring_servicedefs_id'] == '0') {
         // Add the service
         $id = $_POST['id'];
         unset($_POST['id']);
         $_POST['plugin_monitoring_servicedefs_id'] = $pMonitoringServicedef->add($_POST);
         $_POST['id'] = $id;
      } else {
         $pMonitoringServicedef->getFromDB($_POST['plugin_monitoring_servicedefs_id']);
         if ($pMonitoringServicedef->fields['is_template'] == '0') {
            $pMonitoringServicedef->update($_POST);
         }
      }
      if (isset($_POST['arg'])) {
         $_POST['arguments'] = exportArrayToDB($_POST['arg']);
      }
      if (isset($_POST['alias_commandservice'])) {
         $_POST['alias_command'] = $_POST['alias_commandservice'];
      }
      $pMonitoringService->update($_POST);
   }
   Html::back();
}

if (isset($_GET["id"])) {
   $pMonitoringService->showForm($_GET["id"]);
} else {
   $pMonitoringService->showForm('', array(), $_GET['services_id']);
}

Html::footer();

?>