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
             "monitoring", "host");

$pmHost = new PluginMonitoringHost();
if (isset($_POST["add"])) {
   if (((isset($_POST['items_id']) AND $_POST['items_id'] != "0") AND ($_POST['items_id'] != ""))
         OR (isset($_POST['is_template']) AND ($_POST['is_template'] == "1"))) {

      if (isset($_POST['template_id']) AND $_POST['template_id'] > 0) {
         $pmHost->getFromDB($_POST['template_id']);
         $_POST['parenttype'] = $pmHost->fields['parenttype'];
         $_POST['plugin_monitoring_commands_id'] = $pmHost->fields['plugin_monitoring_commands_id'];
         $_POST['plugin_monitoring_checks_id'] = $pmHost->fields['plugin_monitoring_checks_id'];
         $_POST['active_checks_enabled'] = $pmHost->fields['active_checks_enabled'];
         $_POST['passive_checks_enabled'] = $pmHost->fields['passive_checks_enabled'];
         $_POST['calendars_id'] = $pmHost->fields['calendars_id'];
      }

      $hosts_id = $pmHost->add($_POST);

      if (isset($_POST['template_id']) AND $_POST['template_id'] > 0) {
         // Add parents
         $pmHost_Host = new PluginMonitoringHost_Host();
         $a_list = $pmHost_Host->find("`plugin_monitoring_hosts_id_1`='".$_POST['template_id']."'");
         foreach ($a_list as $data) {
            $input = array();
            $input['plugin_monitoring_hosts_id_1'] = $hosts_id;
            $input['plugin_monitoring_hosts_id_2'] = $data['plugin_monitoring_hosts_id_2'];
            $pmHost_Host->add($input);
         }

         // Add contacts
         $pmHost_Contact = new PluginMonitoringHost_Contact();
         $a_list = $pmHost_Contact->find("`plugin_monitoring_hosts_id`='".$_POST['template_id']."'");
         foreach ($a_list as $data) {
            $input = array();
            $input['plugin_monitoring_hosts_id'] = $hosts_id;
            $input['plugin_monitoring_contacts_id'] = $data['plugin_monitoring_contacts_id'];
            $pmHost_Contact->add($input);
         }
      }
      if (isset($_POST['is_template']) AND ($_POST['is_template'] == "1")) {
         Html::redirect($_SERVER['HTTP_REFERER']."&id=".$hosts_id);
      }
   }
   Html::back();
} else if (isset ($_POST["update"])) {

   if ($_POST['parenttype'] == '0' OR $_POST['parenttype'] == '2') {
      $_POST['parents'] = "";
   }
   $pmHost->update($_POST);
   Html::back();
} else if (isset ($_POST["delete"])) {
   $pmHost->delete($_POST, 1);
   Html::back();
}

if (isset($_GET["id"])) {
   $pmHost->showForm($_GET["id"]);
} else {
   $pmHost->showForm("");
}

Html::footer();

?>