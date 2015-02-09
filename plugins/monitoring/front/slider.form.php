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

include ("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_slider", READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "PluginMonitoringDashboard", "slider");

$pmSlider = new PluginMonitoringSlider();

if (isset($_POST["addvisibility"])) {
   if (isset($_POST["_type"])
       && !empty($_POST["_type"])
       && isset($_POST["pluginmonitoringsliders_id"])
       && $_POST["pluginmonitoringsliders_id"]) {
      $item = NULL;
      switch ($_POST["_type"]) {
         case 'User' :
            if (isset($_POST['users_id']) && $_POST['users_id']) {
               $item = new PluginMonitoringSlider_User();
            }
            break;

         case 'Group' :
            if (isset($_POST['groups_id']) && $_POST['groups_id']) {
               $item = new PluginMonitoringSlider_Group();
            }
            break;

         case 'Profile' :
            if (isset($_POST['profiles_id']) && $_POST['profiles_id']) {
               $item = new Profile_Reminder();
            }
            break;

         case 'Entity' :
            $item = new Entity_Reminder();
            break;
      }
      if (!is_null($item)) {
         $item->add($_POST);
         Event::log($_POST["pluginmonitoringsliders_id"], "pluginmonitoringslider", 4, "tools",
                    //TRANS: %s is the user login
                    sprintf(__('%s adds a target'), $_SESSION["glpiname"]));
      }
      Html::back();
   }
}

if (isset($_POST['users_id'])) {
   if ($_POST['users_id'] == 'public') {
      $_POST['users_id'] = '0';
   } else {
      $_POST['users_id'] = $_SESSION['glpiID'];
   }
}

if (isset ($_POST["add"])) {
   $pmSlider->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   $pmSlider->update($_POST);
   Html::back();
} else if (isset ($_POST["purge"])) {
   $pmSlider->delete($_POST);
   $pmSlider->redirectToList();
}

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$pmSlider->display(array('id' => $_GET["id"]));

Html::footer();

?>