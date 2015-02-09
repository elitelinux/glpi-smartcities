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

Session::checkRight("plugin_monitoring_displayview", READ);

Html::header(__('Monitoring - gauge', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "customitem_gauge");

$pmCustomitem_Gauge = new PluginMonitoringCustomitem_Gauge();

if (isset($_POST['add_item'])) {
   if (isset($_POST['item'])) {
      $pmCustomitem_Gauge->getFromDB($_POST['id']);
      $input = array();
      $input['id'] = $_POST['id'];

      if ($pmCustomitem_Gauge->fields['aggregate_items'] == '') {
         $aggregate_items = array();
      } else {
         $aggregate_items = importArrayFromDB($pmCustomitem_Gauge->fields['aggregate_items']);
      }
      if (isset($_POST['plugin_monitoring_componentscatalogs_id'])) {
         $aggregate_items_add = array();
         $a = 'PluginMonitoringComponentscatalog';
         $b = $_POST['plugin_monitoring_componentscatalogs_id'];
         $c = 'PluginMonitoringComponent';
         $d = $_POST['PluginMonitoringComponent'];
         $item_split = explode('/', $_POST['item']);

         $aggregate_items_add[$a]["id".$b][$c]["id".$d] = array(
             array(
                 'perfdatadetails_id' => $item_split[0],
                 'perfdatadetails_dsname' => $item_split[1]
             )
         );
         $aggregate_items = array_merge_recursive($aggregate_items, $aggregate_items_add);
      }
      $input['aggregate_items'] = exportArrayToDB($aggregate_items);

      if (isset($_POST['warn_other_value'])) {
         $input['aggregate_warn'] = $_POST['warn_other_value'];
      } else {
         if (is_numeric($input['aggregate_warn'])) {

         }
         // It's an array
      }

      if (isset($_POST['crit_other_value'])) {
         $input['aggregate_crit'] = $_POST['crit_other_value'];
      } else {
         // It's an array
      }

      if (isset($_POST['limit_other_value'])) {
         $input['aggregate_limit'] = $_POST['limit_other_value'];
      } else {
         // It's an array
      }

      $pmCustomitem_Gauge->update($input);
      Html::back();
   }
   Html::back();
} else if (isset($_POST['delete_item'])) {
   $pmCustomitem_Gauge->deleteGaugeItems($_POST);
   Html::back();
} else if (isset ($_POST["add"])) {
   $pmCustomitem_Gauge->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   $pmCustomitem_Gauge->update($_POST);
   Html::back();
} else if (isset ($_POST["delete"])) {
   $pmCustomitem_Gauge->delete($_POST);
   $pmCustomitem_Gauge->redirectToList();
}


if (isset($_GET["id"])) {
   $pmCustomitem_Gauge->showForm($_GET["id"], array('canedit' => Session::haveRight("config", UPDATE)));
} else {
   $pmCustomitem_Gauge->showForm("", array('canedit' => Session::haveRight("config", UPDATE)));
}

Html::footer();

?>