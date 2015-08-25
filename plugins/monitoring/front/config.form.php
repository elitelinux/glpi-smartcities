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

Session::checkRight("config", UPDATE);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "PluginMonitoringDashboard", "config");


$pmConfig = new PluginMonitoringConfig();
if (isset ($_POST["update"])) {
   $pmConfig->update($_POST);
   Html::back();
} else if (isset($_POST['timezones_add'])) {
   $input = array();
   $pmConfig->getFromDB($_POST['id']);
   $input['id'] = $_POST['id'];
   $a_timezones = importArrayFromDB($pmConfig->fields['timezones']);
   foreach ($_POST['timezones_to_add'] as $timezone) {
      $a_timezones[] = $timezone;
   }
   $input['timezones'] = exportArrayToDB($a_timezones);
   $pmConfig->update($input);
   Html::back();
} else if (isset($_POST['timezones_delete'])) {
   $input = array();
   $pmConfig->getFromDB($_POST['id']);
   $input['id'] = $_POST['id'];
   $a_timezones = importArrayFromDB($pmConfig->fields['timezones']);
    foreach ($_POST['timezones_to_delete'] as $timezone) {
      $key = array_search($timezone, $a_timezones);
      unset($a_timezones[$key]);
   }
   $input['timezones'] = exportArrayToDB($a_timezones);
   $pmConfig->update($input);
   Html::back();
}


$pmConfig->showForm(0, array('canedit' => Session::haveRight("config", UPDATE)));

Html::footer();

?>