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

Html::header(__('Monitoring', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
             "monitoring", "display");

if (isset($_POST['sessionupdate'])) {
   $_SESSION['glpi_plugin_monitoring']['_refresh'] = $_POST['_refresh'];
   Html::back();
   exit;
}

if (isset($_POST["plugin_monitoring_timezone"])) {
   $_SESSION['plugin_monitoring_timezone'] = $_POST["plugin_monitoring_timezone"];
   Html::back();
}

if(isset($_POST['updateperfdata'])) {
   $pmComponent = new PluginMonitoringComponent();

   if (isset($_POST["perfnameinvert"])) {
      $itemtype = $_GET['itemtype'];
      $items_id = $_GET['items_id'];
      $item = new $itemtype();
      $item->getFromDB($items_id);
      $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
      $_SESSION['glpi_plugin_monitoring']['perfnameinvert'][$pmComponent->fields['id']] = array();
      $_POST['perfnameinvert'] = explode("####", $_POST['perfnameinvert']);
      foreach ($_POST["perfnameinvert"] as $perfname) {
         $_SESSION['glpi_plugin_monitoring']['perfnameinvert'][$pmComponent->fields['id']][$perfname] = "checked";
      }
   }

   if (isset($_POST["perfnamecolor"])) {
      $itemtype = $_GET['itemtype'];
      $items_id = $_GET['items_id'];
      $item = new $itemtype();
      $item->getFromDB($items_id);
      $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
      $_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$pmComponent->fields['id']] = array();
      foreach ($_POST["perfnamecolor"] as $perfname=>$color) {
         if ($color != '') {
            $_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$pmComponent->fields['id']][$perfname] = $color;
         }
      }
   }
   Html::back();
}

$pMonitoringDisplay = new PluginMonitoringDisplay();

if (isset($_GET['itemtype']) AND isset($_GET['items_id'])) {

   PluginMonitoringToolbox::loadLib();

   $pmServicegraph = new PluginMonitoringServicegraph();
   $pMonitoringDisplay->displayGraphs($_GET['itemtype'], $_GET['items_id']);
}

Html::footer();

?>