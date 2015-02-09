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

Session::checkRight("plugin_monitoring_component", READ);

Html::header(__('Monitoring - component', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "PluginMonitoringDashboard", "component");


$pMonitoringComponent = new PluginMonitoringComponent();
//echo "<pre>";print_r($_POST);exit;
if (isset($_POST["copy"])) {
   $pMonitoringComponent->showForm(0, array(), $_POST);
   Html::footer();
   exit;
} else if (isset ($_POST["add"])) {
   if (isset($_POST['arg'])) {
      $_POST['arguments'] = exportArrayToDB($_POST['arg']);
   }
   if (empty($_POST['name'])
           OR empty($_POST['plugin_monitoring_checks_id'])
           OR empty($_POST['plugin_monitoring_commands_id'])
           OR empty($_POST['calendars_id'])) {

      $_SESSION['plugin_monitoring_components'] = $_POST;

      Session::addMessageAfterRedirect("<font class='red'>".__('Fields with asterisk are required', 'monitoring')."</font>");
      Html::back();
   }
   if ($_POST['graph_template'] != 0) {
      $a_perfnames = array();
      $a_perfnames = PluginMonitoringServicegraph::getperfdataNames($_POST['graph_template']);
      foreach ($a_perfnames as $name) {
         $a_perfnames[$name] = 1;
      }
      $_POST['perfname'] = exportArrayToDB($a_perfnames);
   }

   $pMonitoringComponent->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   if (isset($_POST['arg'])) {
      $_POST['arguments'] = exportArrayToDB($_POST['arg']);
   }
   if (empty($_POST['name'])
           OR empty($_POST['plugin_monitoring_checks_id'])
           OR empty($_POST['plugin_monitoring_commands_id'])
           OR empty($_POST['calendars_id'])) {

      $_SESSION['plugin_monitoring_components'] = $_POST;

      Session::addMessageAfterRedirect("<font class='red'>".__('Fields with asterisk are required', 'monitoring')."</font>");
      Html::back();
   }
   if ($_POST['graph_template'] != 0) {
      if (!isset($_POST['perfname'])
              AND !isset($_POST['perfnameinvert'])
              AND !isset($_POST['perfnamecolor'])) {
         $pMonitoringComponent->getFromDB($_POST['id']);
         if (empty($pMonitoringComponent->fields['perfname'])
                 AND empty($pMonitoringComponent->fields['perfnameinvert'])
                 AND empty($pMonitoringComponent->fields['perfnamecolor'])) {

            $a_perfnames = array();
            $a_perfnames = PluginMonitoringServicegraph::getperfdataNames($_POST['graph_template']);
            foreach ($a_perfnames as $name) {
               $a_perfnames[$name] = 1;
            }
            $_POST['perfname'] = exportArrayToDB($a_perfnames);
         }
      }
   }
   $pMonitoringComponent->update($_POST);
   Html::back();
} else if (isset ($_POST["purge"])) {
   $pMonitoringComponent->delete($_POST);
   $pMonitoringComponent->redirectToList();
} else if(isset($_POST['updateperfdata'])) {
   $a_perfname = array();

   $a_perfnameinvert = array();
   if (isset($_POST['perfnameinvert'])) {
      $_POST['perfnameinvert'] = explode("####", $_POST['perfnameinvert']);
      foreach ($_POST['perfnameinvert'] as $perfname) {
         $a_perfnameinvert[$perfname] = '1';
      }
   }

   $a_perfnamecolor = array();
   if (isset($_POST['perfnamecolor'])) {
      foreach ($_POST['perfnamecolor'] as $perfname=>$color) {
         if ($color != '') {
            $a_perfnamecolor[$perfname] = $color;
         }
      }
   }
   $input = array();
   $input['id'] = $_POST['id'];
   // $input['perfnameinvert'] = exportArrayToDB($a_perfnameinvert);
   $input['perfnameinvert'] = serialize($a_perfnameinvert);
   // $input['perfnamecolor'] = exportArrayToDB($a_perfnamecolor);
   $input['perfnamecolor'] = serialize($a_perfnamecolor);

   $pMonitoringComponent->update($input);
   Html::back();
}

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$pMonitoringComponent->display(array('id' => $_GET["id"]));

Html::footer();

?>