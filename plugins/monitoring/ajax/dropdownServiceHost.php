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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

// Make a select box
if (class_exists($_POST["itemtype"]) && isset($_POST["hosts"])) {
   $a_services = array();
   $a_services[] = Dropdown::EMPTY_VALUE;
   $query = "SELECT `".getTableForItemType("PluginMonitoringService")."`.*
             FROM `".getTableForItemType("PluginMonitoringService")."`
             LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                  ON `plugin_monitoring_componentscatalogs_hosts_id`
                      = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
             WHERE `itemtype` = '".$_POST["itemtype"]."'
                AND `items_id`='".$_POST['hosts']."'
             ORDER BY `".getTableForItemType("PluginMonitoringService")."`.`name`";
   $result = $DB->query($query);
   while ($data = $DB->fetch_array($result)) {
      $a_services[$data['id']] = $data['name'];
   }

   $rand = Dropdown::showFromArray("plugin_monitoring_services_id", $a_services);

   if ($_POST['selectgraph'] == '1') {

      $params = array('hosts'    => '__VALUE__',
                      'entity'   => '',
                      'rand'     => $rand);

      Ajax::updateItemOnSelectEvent("dropdown_plugin_monitoring_services_id".$rand, "show_extrainfos$rand",
                                  $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownDisplayviewExtrainfos.php",
                                  $params);

      echo "<br/><span id='show_extrainfos$rand'></span>";
   }
}

?>