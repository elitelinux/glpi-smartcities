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
   @author    Frédéric Mohier
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2013

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_downtime", READ);

Html::header(__('Monitoring - downtimes', 'monitoring'),'', "plugins",
        "PluginMonitoringDashboard", "downtime");

$pmDowntime = new PluginMonitoringDowntime();

if (isset ($_POST["add"])) {
   // If category is specified, a new ticket is to be created ...
   if (isset ($_POST['itilcategories_id']) && ($_POST['itilcategories_id'] != 0)) {
      $track = new Ticket();
      $track->check(-1, CREATE ,$_POST);

      // $sla_name = Dropdown::getDropdownName("glpi_slas", $_POST['slas_id']);
      $category_name = Dropdown::getDropdownName("glpi_itilcategories", $_POST['itilcategories_id']);
      $track_name = __('Scheduled downtime', 'monitoring')." / ".$category_name;

      $fields = array();
      $fields['content'] = "";

      // Find ticket template if available ...
      $tt = $track->getTicketTemplateToUse(0, $_POST['type'], $_POST['itilcategories_id']);
      if (isset($tt->predefined) && count($tt->predefined)) {
         foreach ($tt->predefined as $predeffield => $predefvalue) {
            // Load template data
            $fields[$predeffield]            = $predefvalue;
         }
      }
/*
      echo "<pre>";
      print_r($fields);
      echo "</pre>";
*/

      $fields['itemtype'] =            $_POST['itemtype'];
      $fields['items_id'] =            $_POST['items_id'];
      // $fields['slas_id'] =             $_POST['slas_id'];
      $fields['entities_id'] =         $_POST['entities_id'];
      // $fields['type'] =                $_POST['type'];
      $fields['itilcategories_id'] =   $_POST['itilcategories_id'];
      $fields['locations_id'] =        $_POST['locations_id'];
      $fields['name'] =                $track_name;
      $fields['content'] .=            "\n-----\n" . $_POST['comment'];

/*
      echo "<pre>";
      print_r($fields);
      echo "</pre>";
      die('test');
*/

      // Create a new ticket ...
      $_POST['tickets_id'] = $track->add($fields);

      // Create new downtime with associated ticket ...
      $pmDowntime->add($_POST);

      // Redirect to new ticket form if required
      if (isset ($_POST["redirect"])) {
         Html::redirect($_POST["redirect"]."?id=".$_POST['tickets_id']);
      } else {
         $pmDowntime->redirectToList();
      }
   } else {
      // Create new downtime without associated ticket ...
      $pmDowntime->add($_POST);
      $pmDowntime->redirectToList();
   }
} else if (isset ($_POST["update"])) {
   $pmDowntime->update($_POST);
   $pmDowntime->redirectToList();
} else if (isset ($_POST["purge"])) {
   $pmDowntime->delete($_POST);
   $pmDowntime->redirectToList();
}

// Read or edit downtime ...
if (isset($_GET['id']) || isset($_GET['host_id'])) {
   // If host_id is defined, use it ...
   $data = array('id' => $_GET["id"]);
   if (isset($_GET['host_id'])) {
      $data['host_id'] = $_GET['host_id'];
   }
   $pmDowntime->display($data);
//   $pmDowntime->showForm((isset($_GET['id'])) ? $_GET['id'] : -1, (isset($_GET['host_id'])) ? $_GET['host_id'] : -1);
}

Html::footer();

?>