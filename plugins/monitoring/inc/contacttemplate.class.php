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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringContacttemplate extends CommonDBTM {


   static $rightname = 'user';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Contact templates', 'monitoring');
   }



   /**
   * Display form for agent configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      if ($items_id == '') {
         if (isset($_POST['id'])) {
            $a_list = $this->find("`users_id`='".$_POST['id']."'", '', 1);
            if (count($a_list)) {
               $array = current($a_list);
               $items_id = $array['id'];
            }
         }
      }

      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."&nbsp;:</td>";
      echo "<td align='center'>";

      $objectName = autoName($this->fields["name"], "name", false,
                             $this->getType());
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";
      echo "<td>".__('Default template', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo("is_default", $this->fields['is_default']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__('Contact configuration for Shinken WebUI', 'monitoring')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Contact has Shinken administrator rights', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('shinken_administrator', $this->fields['shinken_administrator']);
      echo "</td>";
      echo "<td>".__('Contact can submit Shinken commands', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('shinken_can_submit_commands', $this->fields['shinken_can_submit_commands']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>".__('Hosts', 'monitoring')."</th>";
      echo "<th colspan='2'>".__('Services', 'monitoring')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notifications', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notifications_enabled', $this->fields['host_notifications_enabled']);
      echo "</td>";
      echo "<td>".__('Notifications', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notifications_enabled', $this->fields['service_notifications_enabled']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notification command', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      dropdown::show("PluginMonitoringNotificationcommand", array('name'=>'host_notification_commands',
                                 'value'=>$this->fields['host_notification_commands']));
      echo "</td>";
      echo "<td>".__('Notification command', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      dropdown::show("PluginMonitoringNotificationcommand", array('name'=>'service_notification_commands',
                                 'value'=>$this->fields['service_notification_commands']));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Period', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      dropdown::show("Calendar", array('name'=>'host_notification_period',
                                 'value'=>$this->fields['host_notification_period']));
      echo "</td>";
      echo "<td>".__('Period', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      dropdown::show("Calendar", array('name'=>'service_notification_period',
                                 'value'=>$this->fields['service_notification_period']));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notify on DOWN host states', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_d', $this->fields['host_notification_options_d']);
      echo "</td>";
      echo "<td>".__('Notify on WARNING service states', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_w', $this->fields['service_notification_options_w']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notify on UNREACHABLE host states', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_u', $this->fields['host_notification_options_u']);
      echo "</td>";
      echo "<td>".__('Notify on UNKNOWN service states', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_u', $this->fields['service_notification_options_u']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notify on host recoveries (UP states)', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_r', $this->fields['host_notification_options_r']);
      echo "</td>";
      echo "<td>".__('Notify on CRITICAL service states', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_c', $this->fields['service_notification_options_c']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Notify when the host starts and stops flapping', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_f', $this->fields['host_notification_options_f']);
      echo "</td>";
      echo "<td>".__('Notify on service recoveries (OK states)', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_r', $this->fields['service_notification_options_r']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Send notifications when host or service scheduled downtime starts and ends', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_s', $this->fields['host_notification_options_s']);
      echo "</td>";
      echo "<td>".__('Notify when the service starts and stops flapping', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_f', $this->fields['service_notification_options_f']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('The contact will not receive any type of host notifications', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('host_notification_options_n', $this->fields['host_notification_options_n']);
      echo "</td>";
      echo "<td>".__('The contact will not receive any type of service notifications', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      Dropdown::showYesNo('service_notification_options_n', $this->fields['service_notification_options_n']);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }


}

?>