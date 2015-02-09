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

if (class_exists($_POST["itemtype"])) {
   $table = getTableForItemType($_POST["itemtype"]);

   $query = "SELECT `$table`.`name`,
                    `".$table."`.`id`

             FROM `".getTableForItemType("PluginMonitoringService")."`
             LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                  ON `plugin_monitoring_componentscatalogs_hosts_id`
                      = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
             LEFT JOIN `$table` ON `$table`.`id` = `items_id`
             WHERE `itemtype` = '".$_POST["itemtype"]."'
             ORDER BY `$table`.`name`";
   $result = $DB->query($query);
   $a_hosts = array();
   $a_hosts[0] = Dropdown::EMPTY_VALUE;
   while ($data = $DB->fetch_array($result)) {
      $a_hosts[$data['id']] = $data['name'];
   }
   $rand = Dropdown::showFromArray("hosts", $a_hosts);

   $selectgraph = 0;
   if (isset($_POST['selectgraph'])) {
      $selectgraph = $_POST['selectgraph'];
   }

   $params = array('hosts'           => '__VALUE__',
                   'entity_restrict' => $_POST["entity_restrict"],
                   'itemtype'        => $_POST['itemtype'],
                   'selectgraph'     => $selectgraph,
                   'rand'            => $rand,
                   'myname'          => "items");

   Ajax::updateItemOnSelectEvent("dropdown_hosts".$rand, "show_items$rand",
                               $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownServiceHost.php",
                               $params);

   echo "<span id='show_items$rand'><input type='hidden' name='services_id[]' value='0'/></span>";
}

?>