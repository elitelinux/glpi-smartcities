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

class PluginMonitoringHostaddress extends CommonDBTM {
   public $table = "glpi_plugin_monitoring_hostaddresses";

   static $rightname = 'plugin_monitoring_hoststatus';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return "Host address";
   }



   /**
   *
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $itemtype, $options=array()) {
      global $DB,$CFG_GLPI;

      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `items_id`='".$items_id."'
            AND `itemtype`='".$itemtype."'
         LIMIT 1";

      $result = $DB->query($query);
      if ($DB->numrows($result) == '0') {
         $this->getEmpty();
      } else {
         $data = $DB->fetch_assoc($result);
         $this->getFromDB($data['id']);
      }

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td width='350'>".__('Interface and IP to use for checks (only if have many IPs)', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      echo "<input type='hidden' name='itemtype' value='".$itemtype."'/>";
      echo "<input type='hidden' name='items_id' value='".$items_id."'/>";
      if ($this->fields['networkports_id'] == '') {
         $this->fields['networkports_id'] = 0;
      }

      $a_networkport = array();
      $a_networkport['0'] = Dropdown::EMPTY_VALUE;
      $query = "SELECT `glpi_networkports`.* FROM `glpi_networkports`
         LEFT JOIN `glpi_networknames`
            ON `glpi_networknames`.`items_id`=`glpi_networkports`.`id`
               AND `glpi_networknames`.`itemtype`='NetworkPort'
         LEFT JOIN `glpi_ipaddresses`
            ON `glpi_ipaddresses`.`items_id`=`glpi_networknames`.`id`
               AND `glpi_ipaddresses`.`itemtype`='NetworkName'
         WHERE `glpi_networkports`.`items_id`='".$items_id."'
            AND `glpi_networkports`.`itemtype`='".$itemtype."'
            AND `glpi_ipaddresses`.`name` IS NOT NULL
            AND `glpi_ipaddresses`.`name` != '127.0.0.1'
            AND `glpi_ipaddresses`.`name` != '::1'
            AND `glpi_ipaddresses`.`name` != ''
         ORDER BY `name`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $a_networkport[$data['id']] = $data['name'];
      }
      $rand = Dropdown::showFromArray("networkports_id", $a_networkport,
                                      array('value'=>$this->fields['networkports_id']));
      echo "</td>";
      echo "<td colspan='2'>";
      // Specify ip address or 'first ip address'
      $params = array('networkports_id' => '__VALUE__',
                      'rand'            => $rand,
                      'ipaddresses_id'  => $this->fields['ipaddresses_id']);
      Ajax::updateItemOnEvent("dropdown_networkports_id".$rand,
                              "ipaddresses",
                              $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownipaddress.php",
                              $params);
      echo "<div id='ipaddresses'>";
      PluginMonitoringHostaddress::dropdownIP($this->fields['ipaddresses_id'], $this->fields['networkports_id']);
      echo "</div>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }


   static function dropdownIP($ipaddresses_id, $netwokports_id) {
      global $DB;

      $elements = array(0 => __('First IP', 'monitoring'));
      if (isset($netwokports_id)
              && is_numeric($netwokports_id)) {
         $query = "SELECT `glpi_ipaddresses`.* FROM `glpi_ipaddresses`
            LEFT JOIN `glpi_networknames`
               ON `glpi_ipaddresses`.`items_id`=`glpi_networknames`.`id`
                  AND `glpi_ipaddresses`.`itemtype`='NetworkName'
            WHERE `glpi_networknames`.`items_id`='".$netwokports_id."'
                  AND `glpi_networknames`.`itemtype`='NetworkPort'
               AND `glpi_ipaddresses`.`name` IS NOT NULL
               AND `glpi_ipaddresses`.`name` != '127.0.0.1'
               AND `glpi_ipaddresses`.`name` != '::1'
               AND `glpi_ipaddresses`.`name` != ''
            ORDER BY `name`";
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $elements[$data['id']] = $data['name'];
         }
      }
      Dropdown::showFromArray("ipaddresses_id", $elements, array('value'=>$ipaddresses_id));
   }


   static function getIp($items_id, $itemtype, $hostname) {
      global $DB;

      $networkPort   = new NetworkPort();
      $networkName   = new NetworkName();
      $iPAddress     = new IPAddress();
      $pmHostaddress = new PluginMonitoringHostaddress();

      $ip = '';
      $networkports_id = 0;

      $query = "SELECT * FROM `".$pmHostaddress->getTable()."`
      WHERE `items_id`='".$items_id."'
         AND `itemtype`='".$itemtype."'
      LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) == '1') {
         $data = $DB->fetch_assoc($result);

         if ($data['ipaddresses_id'] > 0) {
            $iPAddress->getFromDB($data['ipaddresses_id']);
            return $iPAddress->fields['name'];
         } else {
            $networkports_id = $data['networkports_id'];
         }
      }
      if ($ip == '') {
         if ($networkports_id > 0) {
            $a_listnetwork = $networkPort->find("`id`='".$networkports_id."'");
         } else {
            $a_listnetwork = $networkPort->find("`itemtype`='".$itemtype."'
               AND `items_id`='".$items_id."'", "`id`");
         }
         foreach ($a_listnetwork as $networkports_id=>$datanetwork) {
            $a_networknames_find = current($networkName->find("`items_id`='".$networkports_id."'
                                                               AND `itemtype`='NetworkPort'", "", 1));
            if (isset($a_networknames_find['id'])) {
               $networknames_id = $a_networknames_find['id'];
               $a_ips_fromDB = $iPAddress->find("`itemtype`='NetworkName'
                                                 AND `items_id`='".$networknames_id."'");
               foreach ($a_ips_fromDB as $data) {
                  if ($data['name'] != ''
                          && $data['name'] != '127.0.0.1'
                          && $data['name'] != '::1') {
                     return $data['name'];
                  }
               }
            }
         }
      }
      return $hostname;
   }
}

?>