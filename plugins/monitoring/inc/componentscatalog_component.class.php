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

class PluginMonitoringComponentscatalog_Component extends CommonDBTM {

   static $rightname = 'plugin_monitoring_componentscatalog';

   static function getTypeName($nb=0) {
      return __('Components', 'monitoring');
   }



   function showComponents($componentscatalogs_id) {
      global $DB,$CFG_GLPI;

      $this->addComponent($componentscatalogs_id);

      $rand = mt_rand();

      $pmComponent = new PluginMonitoringComponent();
      $pmCommand   = new PluginMonitoringCommand();
      $pmCheck     = new PluginMonitoringCheck();
      $calendar    = new Calendar();

      echo "<form method='post' name='componentscatalog_component_form$rand' id='componentscatalog_component_form$rand' action=\"".
                $CFG_GLPI["root_doc"]."/plugins/monitoring/front/componentscatalog_component.form.php\">";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>";
      echo __('Associated components', 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th width='10'>&nbsp;</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Command name', 'monitoring')."</th>";
      echo "<th>".__('Check definition', 'monitoring')."</th>";
      echo "<th>".__('Check period', 'monitoring')."</th>";
      echo "<th>".__('Remote check', 'monitoring')."</th>";
      echo "</tr>";

      $used = array();
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $used[] = $data['plugin_monitoring_components_id'];
         $pmComponent->getFromDB($data['plugin_monitoring_components_id']);
         echo "<tr>";
         echo "<td>";
         echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
         echo "</td>";
         echo "<td class='center'>";
         echo $pmComponent->getLink(1);
         echo "</td>";
         echo "<td class='center'>";
         $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);
         echo $pmCommand->getLink();
         echo "</td>";
         echo "<td class='center'>";
         $pmCheck->getFromDB($pmComponent->fields['plugin_monitoring_checks_id']);
         echo $pmCheck->getLink();
         echo "</td>";
         echo "<td class='center'>";
         $calendar->getFromDB($pmComponent->fields['calendars_id']);
         echo $calendar->getLink();
         echo "</td>";
         echo "<td class='center'>";
         if ($pmComponent->fields['remotesystem'] == '') {
            echo "-";
         } else {
            echo $pmComponent->fields['remotesystem'];
         }
         echo "</td>";

         echo "</tr>";
      }

      Html::openArrowMassives("componentscatalog_host_form$rand", true);
      Html::closeArrowMassives(array('deleteitem' => _sx('button', 'Delete permanently')));
      Html::closeForm();
      echo "</table>";

   }


   function addComponent($componentscatalogs_id) {
      global $DB;

      if (! Session::haveRight("plugin_monitoring_componentscatalog", UPDATE)) return;

      $this->getEmpty();

      $this->showFormHeader();

      $used = array();
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $used[] = $data['plugin_monitoring_components_id'];
      }

      echo "<tr>";
      echo "<td colspan='2'>";
      echo __('Add a new component', 'monitoring')."&nbsp;:";
      echo "<input type='hidden' name='plugin_monitoring_componentscalalog_id' value='".$componentscatalogs_id."'/>";
      echo "</td>";
      echo "<td colspan='2'>";
      Dropdown::show("PluginMonitoringComponent", array('name'=>'plugin_monitoring_components_id',
                                                        'used'=>$used));
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons();
   }



   function addComponentToItems($componentscatalogs_id, $components_id) {
      global $DB;

      $pmService = new PluginMonitoringService();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
      $pmNetworkport = new PluginMonitoringNetworkport();

      $pluginMonitoringNetworkport = 0;
      $query = "SELECT * FROM `".$pmComponentscatalog_rule->getTable()."`
         WHERE `itemtype`='PluginMonitoringNetworkport'
            AND `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $pluginMonitoringNetworkport = 1;
      }

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $itemtype = $data['itemtype'];
         $item = new $itemtype();
         $item->getFromDB($data['items_id']);
         if ($pluginMonitoringNetworkport == '0') {
            $input = array();
            $input['entities_id'] = $item->fields['entities_id'];
            $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
            $input['plugin_monitoring_components_id'] = $components_id;
            $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
            $input['state'] = 'WARNING';
            $input['state_type'] = 'HARD';
            $pmService->add($input);
         } else if ($pluginMonitoringNetworkport == '1') {
            $a_services_created = array();
            $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
               WHERE `plugin_monitoring_components_id`='".$components_id."'
                  AND `plugin_monitoring_componentscatalogs_hosts_id`='".$data['id']."'";
            $results = $DB->query($querys);
            while ($datas=$DB->fetch_array($results)) {
               $a_services_created[$datas['networkports_id']] = $datas['id'];
            }

            $a_ports = $pmNetworkport->find("`itemtype`='".$data['itemtype']."'
               AND `items_id`='".$data['items_id']."'");
            foreach ($a_ports as $datap) {
               if (isset($a_services_created[$datap["id"]])) {
                  unset($a_services_created[$datap["id"]]);
               } else {
                  $input = array();
                  $input['networkports_id'] = $datap['networkports_id'];
                  $input['entities_id'] =  $item->fields['entities_id'];
                  $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
                  $input['plugin_monitoring_components_id'] = $components_id;
                  $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
                  $input['state'] = 'WARNING';
                  $input['state_type'] = 'HARD';
                  $pmService->add($input);
               }
            }
            foreach ($a_services_created as $id) {
               $_SESSION['plugin_monitoring_hosts'] = $data;
               $pmService->delete(array('id'=>$id));
            }
         }
      }
   }



   function removeComponentToItems($componentscatalogs_id, $components_id) {
      global $DB;

      $pmService = new PluginMonitoringService();

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$data['id']."'
               AND `plugin_monitoring_components_id`='".$components_id."'";
         $results = $DB->query($querys);
         while ($datas=$DB->fetch_array($results)) {
            $_SESSION['plugin_monitoring_hosts'] = $data;
            $pmService->delete(array('id'=>$datas['id']));
         }
      }
   }



   static function listForComponents($components_id) {
      global $DB;

      $pmComponentscatalog = new PluginMonitoringComponentscatalog();

      echo "<table class='tab_cadre' width='400'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__('Components catalog', 'monitoring')."</th>";
      echo "</tr>";

      $query = "SELECT `glpi_plugin_monitoring_componentscatalogs`.* FROM `glpi_plugin_monitoring_componentscatalogs_components`
         LEFT JOIN `glpi_plugin_monitoring_componentscatalogs`
            ON `plugin_monitoring_componentscalalog_id` =
               `glpi_plugin_monitoring_componentscatalogs`.`id`
         WHERE `plugin_monitoring_components_id`='".$components_id."'
         ORDER BY `glpi_plugin_monitoring_componentscatalogs`.`name`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         $pmComponentscatalog->getFromDB($data['id']);
         echo $pmComponentscatalog->getLink(1);
         echo "</td>";
         echo "</tr>";
      }
      echo "</table>";
   }
}

?>