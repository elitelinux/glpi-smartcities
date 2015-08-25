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

class PluginMonitoringComponentscatalog extends CommonDropdown {


   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_componentscatalog';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Components catalog', 'monitoring');
   }



   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = parent::getRights();
      $values[self::HOMEPAGE]    = __('See in homepage', 'monitoring');
      $values[self::DASHBOARD]   = __('See in dashboard', 'monitoring');

      return $values;
   }



   function defineTabs($options=array()){

      $ong = array();
      $this->addStandardTab("PluginMonitoringComponentscatalog", $ong, $options);
      return $ong;
   }



   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Central' :
               if (Session::haveRight("plugin_monitoring_homepage", READ)
                       && Session::haveRight("plugin_monitoring_componentscatalog", PluginMonitoringComponentscatalog::HOMEPAGE)) {
                  return array(1 => __('Components catalogs', 'monitoring'));
               } else {
                  return '';
               }
         }
         if ($item->getID() > 0) {
            $ong = array();
            $ong[1] = self::createTabEntry(__('Components', 'monitoring'), self::countForComponents($item));
            $ong[2] = self::createTabEntry(__('Static hosts', 'monitoring'), self::countForStaticHosts($item));
            $ong[3] = self::createTabEntry(_n('Rule', 'Rules', 2), self::countForRules($item));
            $ong[4] = self::createTabEntry(__('Dynamic hosts', 'monitoring'), self::countForDynamicHosts($item));
            $ong[5] = __('Contacts', 'monitoring');
            $ong[6] = __('Availability', 'monitoring');
   //         $ong[7] = __('Simple report', "monitoring");
            $ong[7] = __('Synthese', "monitoring");
            //$ong[7] = __('Report');

            return $ong;
         }
      }
      return '';
   }



   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForStaticHosts(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'
         AND `is_static`='1'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts', $restrict);
   }



   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForDynamicHosts(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'
         AND `is_static`='0'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts', $restrict);
   }



   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForRules(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_rules', $restrict);
   }



   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForComponents(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_components', $restrict);
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Central' :
            $pmDisplay = new PluginMonitoringDisplay();
            $pmComponentscatalog = new PluginMonitoringComponentscatalog();
            // $pmDisplay->showCounters("Componentscatalog");
            $pmComponentscatalog->showChecks();
            return true;

      }
      if ($item->getID() > 0) {
         switch($tabnum) {

            case 1:
               $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
               $pmComponentscatalog_Component->showComponents($item->getID());
               break;

            case 2 :
               $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
               $pmComponentscatalog_Host->showHosts($item->getID(), 1);
               break;

            case 3 :
               $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
               $pmComponentscatalog_rule->showRules($item->getID());
               break;

            case 4 :
               $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
               $pmComponentscatalog_Host->showHosts($item->getID(), 0);
               break;

            case 5 :
               $pmContact_Item = new PluginMonitoringContact_Item();
               $pmContact_Item->showContacts("PluginMonitoringComponentscatalog", $item->getID());
               break;

            case 6:
               $pmUnavailability = new PluginMonitoringUnavailability();
               $pmUnavailability->displayComponentscatalog($item->getID());
               break;

            case 7:
               $pmPluginMonitoringComponentscatalog = new PluginMonitoringComponentscatalog();
               $pmPluginMonitoringComponentscatalog->showSimpleReport($item->getID());
               break;

            case 8:
               $pmPluginMonitoringComponentscatalog = new PluginMonitoringComponentscatalog();
               $pmPluginMonitoringComponentscatalog->showSyntheseReport($item->getID());
               break;


            default :

         }

      }
      return true;
   }



   function getAdditionalFields() {
      return array(array('name'  => 'notification_interval',
                         'label' => __('Interval between 2 notifications (in minutes)', 'monitoring'),
                         'type'  => 'notificationinterval'));
   }



   function prepareInputForUpdate($input) {

      if (isset($input["notification_interval_hours"])
              && isset($input['notification_interval_minutes'])) {
         $input['notification_interval'] = $input["notification_interval_hours"]*60 + $input['notification_interval_minutes'];
         unset($input["notification_interval_hours"]);
         unset($input['notification_interval_minutes']);
      }

      return $input;
   }


   function displaySpecificTypeField($ID, $field=array()) {


      switch ($field['type']) {
         case 'notificationinterval' :
            if ($ID > 0) {
//               $this->fields['notification_interval'];
            } else {
               $this->fields['notification_interval'] = 30;
            }
            $hours = (int)($this->fields['notification_interval'] / 60);
            $minutes = (int)($this->fields['notification_interval'] % 60);
            Dropdown::showNumber('notification_interval_hours', array(
                'value' => $hours,
                'min'   => 0,
                'max'   => 168,
                'step'  => 1)
            );
            echo "&nbsp;".__('hours', 'monitoring');
            Dropdown::showNumber('notification_interval_minutes', array(
                'value' => $minutes,
                'min'   => 0,
                'max'   => 59,
                'step'  => 1)
            );
            echo "&nbsp;".__('minutes', 'monitoring');
            // Dropdown::showNumber('notification_interval', array(
                // 'value' => $this->fields['notification_interval'],
                // 'min'   => 0,
                // 'max'   => 2880,
                // 'step'  => 10)
            // );
            break;
      }
   }



   function showChecks() {

      echo "
      <script>
         function toggleEntity(idEntity) {
            Ext.select('#'+idEntity).each(function(el) {
               var displayed = false;
               el.select('tr.services').each(function(elTr) {
                  elTr.setDisplayed(! elTr.isDisplayed());
                  displayed = elTr.isDisplayed();
               });
               el.select('tr.header').each(function(elTr) {
                  elTr.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
                  elTr.select('th').each(function(elTd) {
                     elTd.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
                  });
               });
            });
         };
      </script>
      ";

      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr class='tab_bg_4' style='background: #cececc;'>";

      $a_componentscatalogs = $this->find();
      $i = 0;
      foreach ($a_componentscatalogs as $data) {
         $ret = $this->getInfoOfCatalog($data['id']);
         if ($ret[0] > 0) {
            echo "<td style='vertical-align: top;'>";

            echo $this->showWidget($data['id']);
            if (isset($_SESSION['plugin_monitoring_reduced_interface'])) {
               $this->ajaxLoad($data['id'], ! $_SESSION['plugin_monitoring_reduced_interface']);
            } else {
               $this->ajaxLoad($data['id'], TRUE);
            }

            echo "</td>";

            $i++;
            if ($i == '4') {
               echo "</tr>";
               echo "<tr class='tab_bg_4' style='background: #cececc;'>";
               $i = 0;
            }
         }
      }

      echo "</tr>";
      echo "</table>";
   }



   static function replayRulesCatalog($item) {

      $datas = getAllDatasFromTable("glpi_plugin_monitoring_componentscatalogs_rules",
              "`plugin_monitoring_componentscalalog_id`='".$item->getID()."'");
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
      foreach($datas as $data) {
         $pmComponentscatalog_rule->getFromDB($data['id']);
         PluginMonitoringComponentscatalog_rule::getItemsDynamicly($pmComponentscatalog_rule);
      }
   }



   static function removeCatalog($item) {
      global $DB;

      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'
            AND `is_static`='1'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_Host->delete($data);
      }

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_rules`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_rule->delete($data);
      }
   }



   function showWidget($id) {
      return "<div id=\"updatecomponentscatalog".$id."\"></div>";
   }



   function showWidgetFrame($id, $reduced_interface=false, $is_minemap=FALSE) {
      global $DB, $CFG_GLPI;

      $this->getFromDB($id);
      $data = $this->fields;

      $ret = $this->getInfoOfCatalog($id);
      $nb_ressources = $ret[0];
      if ($nb_ressources == 0) {
         echo '<div class="ch-item">
            <div>
            <h1>'.__('Nothing to display ...', 'monitoring').'</h1>
            </div>
         </div>';

         return;
      }

      $stateg = $ret[1];
      $hosts_ids = $ret[2];
      $services_ids = $ret[3];
      $hosts_ressources = $ret[4];
      $hosts_states = $ret[5];

      $colorclass = 'ok';
      $count = 0;

      $link = '';
      // Toolbox::logInFile("pm", "stateg $id - ".serialize($stateg)."\n");
      if ($stateg['CRITICAL'] > 0) {
         $count = $stateg['CRITICAL'];
         $colorclass = 'crit';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset&"
                 . "&criteria[0][field]=3"
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=CRITICAL"

                 . "&criteria[1][link]=AND"
                 . "&criteria[1][field]=9"
                 . "&criteris[1][searchtype]=equals"
                 . "&criteria[1][value]=".$id

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0";
      } else if ($stateg['WARNING'] > 0) {
         $count = $stateg['WARNING'];
         $colorclass = 'warn';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                 . "&criteria[0][field]=3"
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=WARNING"

                 . "&criteria[1][link]=AND"
                 . "&criteria[1][field]=9"
                 . "&criteria[1][searchtype]=equals"
                 . "&criteria[1][value]=".$id

                 . "&criteria[2][link]=OR"
                 . "&criteria[2][field]=3"
                 . "&criteria[2][searchtype]=equals"
                 . "&criteria[2][value]=UNKNOWN"

                 . "&criteria[3][link]=AND"
                 . "&criteria[3][field]=9"
                 . "&criteria[3][searchtype]=equals"
                 . "&criteria[3][value]=".$id

                 . "&criteria[4][link]=OR"
                 . "&criteria[4][field]=3"
                 . "&criteria[4][searchtype]=equals"
                 . "&criteria[4][value]=RECOVERY"

                 . "&criteria[5][link]=AND"
                 . "&criteria[5][field]=9"
                 . "&criteria[5][searchtype]=equals"
                 . "&criteria[5][value]=".$id

                 . "&criteria[6][link]=OR"
                 . "&criteria[6][field]=3"
                 . "&criteria[6][searchtype]=equals"
                 . "&criteria[6][value]=FLAPPING"

                 . "&criteria[7][link]=AND"
                 . "&criteria[7][field]=9"
                 . "&criteria[7][searchtype]=equals"
                 . "&criteria[7][value]=".$id

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0";
      } else {
         $count = $stateg['OK'];
         $count += $stateg['ACKNOWLEDGE'];
         $count += $stateg['UNKNOWN'];
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                 . "&criteria[0][field]=3"
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=OK"

                 . "&criteria[1][link]=AND"
                 . "&criteria[1][field]=9"
                 . "&criteria[1][searchtype]=equals"
                 . "&criteria[1][value]=".$id

                 . "&criteria[2][link]=OR"
                 . "&criteria[2][field]=3"
                 . "&criteria[2][searchtype]=equals"
                 . "&criteria[2][value]=UP"

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0";
      }

      if (Session::haveRight("plugin_monitoring_service", READ)) {
         $link_catalog = $CFG_GLPI['root_doc'].
            "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                 . "&criteria[0][field]=9"
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=".$id

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0";

         echo '<div class="ch-item">
            <div class="ch-info-'.$colorclass.'">
            <h1><a href="'.$link_catalog.'">'.ucfirst($data['name']);
            if ($data['comment'] != '') {
               echo ' '.$this->getComments();
            }
            echo '</a></h1>
               <p><a href="'.$link.'">'.$count.'</a><font style="font-size: 14px;">/ '.
               ($stateg['CRITICAL'] + $stateg['WARNING'] + $stateg['OK'] + $stateg['ACKNOWLEDGE'] + $stateg['UNKNOWN']).'</font></p>
            </div>
         </div>';
      } else {
         echo '<div class="ch-item">
            <div class="ch-info-'.$colorclass.'">
            <h1>'.ucfirst($data['name']);
            if ($data['comment'] != '') {
               echo ' '.$this->getComments();
            }
            echo '</h1>
               <p>'.$count.'<font style="font-size: 14px;">/ '.
               ($stateg['CRITICAL'] + $stateg['WARNING'] + $stateg['OK'] + $stateg['ACKNOWLEDGE'] + $stateg['UNKNOWN']).'</font></p>
            </div>
         </div>';
      }

      // Get services list ...
      $services = array();
      $i = 0;
      foreach ($hosts_ressources as $resources) {
         foreach ($resources as $resource=>$status) {
            $services[$i++] = $resource;
         }
         break;
      }
      sort($services);

      echo "<div class='minemapdiv' align='center'>"
            ."<a onclick='$(\"#minemapCC-".$id."\").toggle();'>"
            .__('Minemap', 'monitoring')."</a></div>";
      if (!$is_minemap) {
         echo '<div class="minemapdiv" id="minemapCC-'.$id.'" style="display: none; z-index: 1500">';
      } else {
         echo '<div class="minemapdiv" id="minemapCC-'.$id.'">';
      }

      echo '<table class="tab_cadrehov" >';

      // Header with services name and link to services list ...
      echo "<tr>";
      echo "<th colspan='2'>";
      echo __('Hosts', 'monitoring');
      echo "</th>";
      for ($i = 0; $i < count($services); $i++) {
         // Do not display fake host service ...
         if ($services[$i] == '_fake_') continue;

         if (Session::haveRight("plugin_monitoring_service", READ)) {
            $link = $CFG_GLPI['root_doc'].
               "/plugins/monitoring/front/service.php?hidesearch=1"
//                    . "&reset=reset"
                    . "&criteria[0][field]=2"
                    . "&criteria[0][searchtype]=equals"
                    . "&criteria[0][value]=".$services_ids[$services[$i]]

                    . "&itemtype=PluginMonitoringService"
                    . "&start=0'";
            echo '<th class="vertical">';
            echo '<a href="'.$link.'"><div class="rotated-text"><span class="rotated-text__inner">'.$services[$i].'</span></div></a>';
            echo '</th>';
         } else {
            echo '<th class="vertical">';
            echo '<div class="rotated-text"><span class="rotated-text__inner">'.$services[$i].'</span></div>';
            echo '</th>';
         }
      }
      echo '</tr>';

      $pmHost = new PluginMonitoringHost();
      $entityId = -1;
      $overallServicesState = 'OK';
      foreach ($hosts_ressources as $hosts_id=>$resources) {
         // Reduced array or not ?
         if ($reduced_interface and $hosts_states[$hosts_id]) continue;

         $pmHost->getFromDB($hosts_ids[$hosts_id]['id']);
         if ($entityId != $pmHost->fields['entities_id']) {
            if ($entityId != -1) {
               echo "</table>";
               if ($overallServicesState != 'OK') {
                  echo "<script>
                     Ext.onReady(function(){
                        toggleEntity('entity-$id-$entityId');
                     });</script>";
                  $overallServicesState = 'OK';
               }
            }
            // A new sub-table for each entity ...
            $entityId = $pmHost->fields['entities_id'];
            $pmEntity = new Entity();
            $pmEntity->getFromDB($entityId);
            echo "<tr class='tab_bg_1'>";
            echo "<table class='tab_cadrehov' style='height: auto;' id='entity-$id-$entityId' onClick='javascript: toggleEntity(\"entity-$id-$entityId\");'>";
            $overallServicesState = 'OK';
            echo "<tr class='header'><th class='left' style='height:50px;' colspan='".(count($services))."'>".$pmEntity->fields['name']."</th></tr>";
         }
         $field_id = 20;
         if ($hosts_ids[$hosts_id]['itemtype'] == 'Printer') {
            $field_id = 21;
         } else if ($hosts_ids[$hosts_id]['itemtype'] == 'NetworkEquipment') {
            $field_id = 22;
         }

         $link = $CFG_GLPI['root_doc'].
            "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                 . "&criteria[0][field]=".$field_id.""
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=".$hosts_ids[$hosts_id]['items_id']

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0'";

         if ($hosts_states[$hosts_id]) {
            echo  "<tr class='services tab_bg_2' style='display:none;'>";
         } else {
            echo  "<tr class='services tab_bg_3' style='display:none;'>";
         }
         // echo "<td><div style='width: 5px !important;'>&nbsp;</div></td>";
         if (Session::haveRight("plugin_monitoring_service", READ)) {
            $item = new $hosts_ids[$hosts_id]['itemtype'];
            $item->getFromDB($hosts_ids[$hosts_id]['items_id']);
            echo  "<td class='left'><a href='".$link."'>".$hosts_ids[$hosts_id]['name']."</a> ".$item->getComments()."</td>";
         } else {
            echo  "<td class='left'>".$hosts_ids[$hosts_id]['name']."</td>";
         }
         for ($i = 0; $i < count($services); $i++) {
            if ($services[$i] == '_fake_') continue;

            if ($resources[$services[$i]]['state'] != 'OK') {
               $overallServicesState = $resources[$services[$i]]['state'];
            }
            echo '<td class="serviceState">';
            if (Session::haveRight("plugin_monitoring_service", READ)) {
               $link_service = $link;
               $link_service .= "&link[1]=AND&field[1]=2&searchtype[1]=equals&contains[1]=".
                       $resources[$services[$i]]['plugin_monitoring_components_id'];
               echo '<a href="'.$link_service.'">'.
                        '<div title="'.$resources[$services[$i]]['state'].
                        " - ".$resources[$services[$i]]['last_check']." - ".
                        $resources[$services[$i]]['event'].
                        '" class="service service'.$resources[$services[$i]]['state_type'].' service'.$resources[$services[$i]]['state'].'"></div>'.
                        '</a>';
            } else {
               echo '<div title="'.$resources[$services[$i]]['state'].
                       " - ".$resources[$services[$i]]['last_check']." - ".
                       $resources[$services[$i]]['event'].
                       '" class="service service'.$resources[$services[$i]]['state_type'].' service'.$resources[$services[$i]]['state'].'"></div>';
            }
            echo '</td>';
         }
         echo  '</tr>';
      }
      echo  '</table>';
      echo '</div>';
      if ($overallServicesState != 'OK') {
         echo "<script>
            $(document).ready(function(){
               toggleEntity('entity-$id-$entityId');
            });</script>";
      }
   }



   function ajaxLoad($id, $is_minemap=false) {
      global $CFG_GLPI;

         echo "<script type=\"text/javascript\">
            (function worker() {
              $.get('".$CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updateWidgetComponentscatalog.php"
                    ."?id=".$id."&is_minemap=".$is_minemap.
                          "', function(data) {
                $('#updatecomponentscatalog".$id."').html(data);
                setTimeout(worker, 50000);
              });
            })();
         </script>";
   }



   function getInfoOfCatalog($componentscatalogs_id) {
      global $DB;

      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmService = new PluginMonitoringService();

      $stateg = array();
      $stateg['OK']          = 0;
      $stateg['WARNING']     = 0;
      $stateg['CRITICAL']    = 0;
      $stateg['UNKNOWN']     = 0;
      $stateg['ACKNOWLEDGE'] = 0;
      $a_gstate = array();
      $nb_ressources = 0;
      $hosts_ids = array();
      $hosts_states = array();
      $services_ids = array();
      $hosts_ressources = array();
      $a_componentscatalogs_hosts = array();

      $query = "
         SELECT
            CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS name,
            CONCAT_WS('', `glpi_computers`.`entities_id`, `glpi_printers`.`entities_id`, `glpi_networkequipments`.`entities_id`) AS entities_id,
            `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` AS catalog_id,
            `glpi_plugin_monitoring_hosts`.*
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_printers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_printers`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Printer'
         LEFT JOIN `glpi_networkequipments`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='NetworkEquipment'

         INNER JOIN `glpi_plugin_monitoring_hosts`
            ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_plugin_monitoring_hosts`.`items_id`
            AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = `glpi_plugin_monitoring_hosts`.`itemtype`)
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
            AND CONCAT_WS('', `glpi_computers`.`entities_id`, `glpi_printers`.`entities_id`, `glpi_networkequipments`.`entities_id`) IN (".$_SESSION['glpiactiveentities_string'].")
         ORDER BY entities_id ASC, name ASC";
      // Toolbox::logInFile("pm", "query : $query\n");

      $result = $DB->query($query);
      while ($dataComponentscatalog_Host=$DB->fetch_array($result)) {
         $ressources = array();
         $fakeService = array();
         $host_overall_state_ok = false;

         // Dummy service id ...
         $fakeService['name'] = '_fake_';
         $fakeService['id'] = $dataComponentscatalog_Host['id'] + 1000000;
         $fakeService['is_acknowledged'] = $dataComponentscatalog_Host['is_acknowledged'];
         $fakeService['last_check'] = $dataComponentscatalog_Host['last_check'];
         $fakeService['event'] = $dataComponentscatalog_Host['event'];
         $fakeService['perf_data'] = $dataComponentscatalog_Host['perf_data'];
         $fakeService['state_type'] = $dataComponentscatalog_Host['state_type'];
         $fakeService['state'] = ($dataComponentscatalog_Host['is_acknowledged']=='1') ? 'ACKNOWLEDGE' : $dataComponentscatalog_Host['state'];
         $fakeService['state'] = ($dataComponentscatalog_Host['state_type']=='HARD') ? $fakeService['state'] : 'UNKNOWN';
         switch($fakeService['state']) {
            case 'UP':
               $fakeService['state'] = 'OK';
               $host_overall_state_ok = true;
               break;

            case 'DOWN':
            case 'UNREACHABLE':
               $fakeService['state'] = 'CRITICAL';
               break;

            case 'DOWNTIME':
               $fakeService['state'] = 'ACKNOWLEDGE';
               break;

            case 'WARNING':
            case 'RECOVERY':
            case 'FLAPPING':
               $fakeService['state'] = 'WARNING';
               break;

            default:
               $fakeService['state'] = 'UNKNOWN';
               break;
         }

         $queryService = "SELECT *, `glpi_plugin_monitoring_services`.`id` as serviceId, `glpi_plugin_monitoring_components`.`name`,
                 `glpi_plugin_monitoring_components`.`description` FROM `".$pmService->getTable()."`
            INNER JOIN `glpi_plugin_monitoring_components`
               ON (`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['catalog_id']."'
               AND `entities_id` IN (".$_SESSION['glpiactiveentities_string'].")
            ORDER BY `glpi_plugin_monitoring_services`.`name` ASC;";
         // Toolbox::logInFile("pm", "query services - $queryService\n");
         $resultService = $DB->query($queryService);
         while ($dataService=$DB->fetch_array($resultService)) {
            $nb_ressources++;

            $pmService->getFromDB($dataService["serviceId"]);

            if ($dataService['is_acknowledged'] == '1') {
               $dataService['state'] = 'ACKNOWLEDGE';
            }
            // If not hard state, then unknown ...
            if ($dataService['state_type'] != "HARD") {
               $a_gstate[$dataService['id']] = "UNKNOWN";
               if ($host_overall_state_ok) $host_overall_state_ok = false;
            } else {
               // $statecurrent = PluginMonitoringHost::getState($dataService['state'],
                                                              // $dataService['state_type'],
                                                              // $dataService['event'],
                                                              // $dataService['is_acknowledged']);
               $statecurrent = $pmService->getShortState();
               if ($statecurrent == 'green') {
                  $a_gstate[$dataService['id']] = "OK";
               } else if ($statecurrent == 'orange') {
                  $a_gstate[$dataService['id']] = "WARNING";
                  if ($host_overall_state_ok) $host_overall_state_ok = false;
               } else if ($statecurrent == 'yellow') {
                  $a_gstate[$dataService['id']] = "WARNING";
                  if ($host_overall_state_ok) $host_overall_state_ok = false;
               } else if ($statecurrent == 'red') {
                  $a_gstate[$dataService['id']] = "CRITICAL";
                  if ($host_overall_state_ok) $host_overall_state_ok = false;
               } else if ($statecurrent == 'redblue') {
                  $a_gstate[$dataService['id']] = "ACKNOWLEDGE";
                  if ($host_overall_state_ok) $host_overall_state_ok = false;
               }
            }
            $ressources[$dataService['name']] = $dataService;
            $services_ids[$dataService['name']] = $dataService['plugin_monitoring_components_id'];

            if (isset($dataService['id'])
                    && isset($a_gstate[$dataService['id']])) {
               $stateg[$a_gstate[$dataService['id']]]++;
            }
         }

         if ($host_overall_state_ok) {
            $fakeService['state'] = 'OK';
         } else {
            $fakeService['state'] = 'CRITICAL';
         }
         $ressources[$fakeService['name']] = $fakeService;
         $services_ids[$fakeService['name']] = '';
         $a_gstate[$fakeService['id']] = $fakeService['state'];
         // $stateg[$a_gstate[$fakeService['id']]]++;

         $hosts_ids[$dataComponentscatalog_Host['id']] = $dataComponentscatalog_Host;
         $hosts_states[$dataComponentscatalog_Host['id']] = $host_overall_state_ok;
         $a_componentscatalogs_hosts[$dataComponentscatalog_Host['catalog_id']] = $dataComponentscatalog_Host['catalog_id'];
         $hosts_ressources[$dataComponentscatalog_Host['id']] = $ressources;
      }

      return array($nb_ressources,
                   $stateg,
                   $hosts_ids,
                   $services_ids,
                   $hosts_ressources,
                   $hosts_states,
                   $a_componentscatalogs_hosts);
   }



   function getRessources($componentscatalogs_id, $state, $state_type='HARD') {
      global $DB;

      $a_services = array();

      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmService = new PluginMonitoringService();

      $query = "SELECT * FROM `glpi_plugin_monitoring_services`
         LEFT JOIN `".$pmComponentscatalog_Host->getTable()."`
            ON `plugin_monitoring_componentscatalogs_hosts_id`=
               `".$pmComponentscatalog_Host->getTable()."`.`id`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
            AND `state_type` LIKE '".$state_type."'
         ORDER BY `name`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmService->getFromDB($dataService["id"]);
         if ($pmService->getShortState()) {
         // if (PluginMonitoringHost::getState($data['state'],
                                               // $data['state_type'],
                                               // '',
                                               // $data['is_acknowledged']) == $state) {
            $a_services[] = $data;
         }
      }
      return $a_services;
   }



   function showSimpleReport($componentscatalogs_id) {
      global $CFG_GLPI;

      $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
      $pmComponent = new PluginMonitoringComponent();
      $a_options = array();

      $this->getFromDB($componentscatalogs_id);

      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/report_componentscatalog.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo '<tr class="tab_bg_1">';
      echo '<th colspan="5">';
      echo __('Report');
      echo "<input type='hidden' name='componentscatalogs_id' value='".$componentscatalogs_id."' />";
      $a_options['componentscatalogs_id'] = $componentscatalogs_id;
      echo '</th>';
      echo '</tr>';

      // ** simple report
      echo '<tr class="tab_bg_1">';
      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '<input type="radio" name="reporttype" value="simplereport" checked />';
      echo '</td>';
      echo '<td colspan="4">';
      echo '<strong>'.__('Simple report', "monitoring").'</strong>';
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '</td>';
      echo "<td>".__('Start date')." :</td>";
      echo "<td>";
      Html::showDateFormItem("date_start", date('Y-m-d H:i:s', date('U') - (24 * 3600 * 7)));
      $a_options['date_start'] = date('Y-m-d H:i:s', date('U') - (24 * 3600 * 7));
      // Fred ?
      $a_options['date_start'] = '2013-01-01 01:01:01';
      echo "</td>";
      echo "<td>".__('End date')." :</td>";
      echo "<td>";
      Html::showDateFormItem("date_end", date('Y-m-d'));
      $a_options['date_end'] = date('Y-m-d');
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      $a_composants = $pmComponentscatalog_Component->find("`plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'");
      foreach ($a_composants as $comp_data) {
         $pmComponent->getFromDB($comp_data['plugin_monitoring_components_id']);

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "<input type='checkbox' name='components_id[]' value='".$pmComponent->getID()."' checked />";
         $a_options['components_id'][] = $pmComponent->getID();
         echo "</td>";
         echo "<td>";
         echo $pmComponent->getLink();
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "</td>";
         echo "<td>";
         PluginMonitoringToolbox::preferences($pmComponent->getID(), 1, 1);
         echo "</td>";

         echo "</tr>";
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' class='submit' name='generate' value='".__('Generate the report', 'monitoring')."'/>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      Html::closeForm();

      $this->generateReport($a_options, FALSE);
   }



   function showSyntheseReport($componentscatalogs_id) {
      global $CFG_GLPI;

      if (!isset($_SESSION['glpi_plugin_monitoring']['synthese'])) {
         $_SESSION['glpi_plugin_monitoring']['synthese'] = array();
      }
      if (!isset($_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id])) {
         $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id] = array();
      }
      $sess = $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id];
      $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
      $pmComponent = new PluginMonitoringComponent();
      $a_options = array();

      $this->getFromDB($componentscatalogs_id);

      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/report_componentscatalog.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo '<tr class="tab_bg_1">';
      echo '<th colspan="5">';
      echo __('Report');
      echo "<input type='hidden' name='componentscatalogs_id' value='".$componentscatalogs_id."' />";
      echo "<input type='hidden' name='reporttype' value='synthese' />";
      $a_options['componentscatalogs_id'] = $componentscatalogs_id;
      echo '</th>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '</td>';
      echo '<td colspan="2">';
      $default_value = 12;
      if (isset($sess['synthesenumber'])) {
         $default_value = $sess['synthesenumber'];
      }
      Dropdown::showNumber("synthesenumber", array(
                'value' => $default_value,
                'min'   => 2,
                'max'   => 30)
      );
      $a_options['synthesenumber'] = $default_value;
      echo "&nbsp;";
      $a_time = array('week' => __('Week'),
                      'month' => __('Month'),
                      'year' => __('Year'));
      $default_value = 'week';
      if (isset($sess['synthesenumber'])) {
         $default_value = $sess['synthesenumber'];
      }
      Dropdown::showFromArray("syntheseperiod", $a_time, array('value' => $default_value));
      $a_options['syntheseperiod'] = $default_value;
      echo '</td>';
      echo "<td>".__('End date')." :</td>";
      echo "<td>";
      $default_value = date('Y-m-d');
      if (isset($sess['synthesedate_end'])) {
         $default_value = $sess['synthesedate_end'];
      }
      Html::showDateFormItem("synthesedate_end", $default_value);
      $a_options['synthesedate_end'] = $default_value;
      echo "</td>";
      echo '</tr>';

      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      $a_composants = $pmComponentscatalog_Component->find("`plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'");
      foreach ($a_composants as $comp_data) {
         $pmComponent->getFromDB($comp_data['plugin_monitoring_components_id']);

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         //echo "<input type='checkbox' name='components_id[]' value='".$pmComponent->getID()."' checked />";
         echo "<input type='hidden' name='components_id[]' value='".$pmComponent->getID()."' />";
         $a_options['components_id'][] = $pmComponent->getID();
         echo "</td>";
         echo "<td>";
         echo $pmComponent->getLink();
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "</td>";
         echo "<td>";

         PluginMonitoringToolbox::loadPreferences($pmComponent->getID());

         $a_perfnames = PluginMonitoringServicegraph::getperfdataNames($pmComponent->fields['graph_template']);
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_3'>";
         echo "<td rowspan='".count($a_perfnames)."' width='90'>";
         echo __('Use for report', 'monitoring')."&nbsp;:";

         echo "</td>";
         $i = 0;
         $j = 0;
         if (!isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()])) {
            foreach ($a_perfnames as $name) {
               $_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name] = 'checked';
            }
         }

         foreach ($a_perfnames as $name) {
            if ($i > 0) {
               echo "<tr class='tab_bg_3'>";
            }
            echo "<td>";
            $checked = "checked";
            if (isset($sess['perfname'])
                 && isset($sess['perfname'][$pmComponent->getID()])) {

               if (isset($sess['perfname'][$pmComponent->getID()])) {
                  $checked = "";
               }
               if (isset($sess['perfname'][$pmComponent->getID()][$name])) {
                  $checked = "checked";
               }
            } else {
               if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()])) {
                  $checked = "";
               }
               if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name])) {
                  $checked = $_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name];
               }
            }
            echo "<input type='checkbox' name='perfname[".$pmComponent->getID()."][".$name."]' value='".$name."' ".$checked."/> ".$name;
            if ($checked == 'checked') {
               $a_options['perfname'][$pmComponent->getID()][] = $name;
            }
            echo "</td>";
            echo "<td>";
            echo __('Best is high value', 'monitoring').' :';
            echo "</td>";
            echo "<td>";
            $default_value = 1;
            if (isset($sess['perfname_val'])
                 && isset($sess['perfname_val'][$pmComponent->getID()])) {

               if (isset($sess['perfname_val'][$pmComponent->getID()][$name])) {
                  $default_value = $sess['perfname_val'][$pmComponent->getID()][$name];
               }
            }
            Dropdown::showYesNo('perfname_val['.$pmComponent->getID().']['.$name.']', $default_value);
            if ($checked == 'checked') {
               $a_options['perfname_val'][$pmComponent->getID()][$name] = $default_value;
            }
            echo "</td>";
            echo "</tr>";
            $i++;
         }

         echo "</table>";

         echo "</td>";

         echo "</tr>";
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' class='submit' name='update' value='".__('Save')."'/>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <input type='submit' class='submit' name='generatepdf' value='".__('Generate PDF', 'monitoring')."'/>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      Html::closeForm();

      if (isset($_SESSION['plugin_monitoring_report'])) {
//         $a_options = $_SESSION['plugin_monitoring_report'];
      }
      $this->generateSyntheseReport(
              $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id],
              FALSE);
   }



   function generateReport($array, $pdf=TRUE) {
      global $DB,$CFG_GLPI;

      $componentscatalogs_id = $array['componentscatalogs_id'];

      // define time for the report:
      // Week, week -1, week -2, month, month -1, month -2, year, year -1

      $pmUnavailability = new PluginMonitoringUnavailability();
      $pmComponent = new PluginMonitoringComponent();
      $pmServiceevent = new PluginMonitoringServiceevent();

      if ($pdf) {
         PluginMonitoringReport::beginCapture();
      }

      $this->getFromDB($componentscatalogs_id);
      echo '<h1>'.$this->getTypeName().' : '.$this->getName().'<br/>
         Mois de Novembre</h1>';

      echo '<br/>';

      foreach ($array['components_id'] as $components_id) {
         $pmComponent->getFromDB($components_id);

         $a_name = $array['perfname'];

         echo "<table class='tab_cadre_fixe'>";
         echo '<tr class="tab_bg_1">';
         echo '<th colspan="'.(6 + (count($a_name) * 3)).'">';
         echo $pmComponent->getName();
         echo '</th>';
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo '<th rowspan="2">';
         echo __('Name');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Entity');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Itemtype');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Trend', 'monitoring');
         echo '</th>';
         echo '<th colspan="2">';
         echo __('Avaibility', 'monitoring');
         echo '</th>';
         foreach ($a_name as $name) {
            echo '<th colspan="3">';
            echo str_replace('_', ' ', $name);
            echo '</th>';
         }
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo '<th>';
         echo __('%', 'monitoring');
         echo '</th>';
         echo '<th>';
         echo __('Time', 'monitoring');
         echo '</th>';
         foreach ($a_name as $name) {
            echo '<th>';
            echo __('Min', 'monitoring');
            echo '</th>';
            echo '<th>';
            echo __('Avg', 'monitoring');
            echo '</th>';
            echo '<th>';
            echo __('Max', 'monitoring');
            echo '</th>';
         }
         echo '</tr>';


         $query = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
               `glpi_plugin_monitoring_services`.`id` as sid FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
            LEFT JOIN `glpi_plugin_monitoring_services`
               ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`=`plugin_monitoring_componentscatalogs_hosts_id`
            WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
               AND `plugin_monitoring_components_id`='".$components_id."'";
         $result = $DB->query($query);
         $rownb = true;
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);

            $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmComponent->fields['id']);

            $ret = array();
            if (count($a_name) > 0) {
               $queryevents = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                  WHERE `plugin_monitoring_services_id`='".$data['sid']."'
                     AND `date` >= '".$array['date_start']."'
                     AND `date` <= '".$array['date_end']."'
                  ORDER BY `date`";
               $resultevents = $DB->query($queryevents);
               $ret = $pmServiceevent->getData($resultevents, $pmComponent->fields['graph_template'], $array['date_start'], $array['date_end']);
            }

            echo '<tr class="tab_bg_1'.(($rownb = !$rownb)?'_2':'').'">';
            echo '<td>';
            echo $item->getName();
            echo '</td>';
            echo '<td>';
            echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']);
            echo '</td>';
            echo '<td>';
            echo $item->getTypeName();
            echo '</td>';
            echo '<td>';
            $a_times = $pmUnavailability->parseEvents($data['id'], '', $array['date_start'], $array['date_end']);
            // previous unavailability
            $str_start = strtotime($array['date_start']);
            $str_end   = strtotime($array['date_end']);
            $a_times_previous = $pmUnavailability->parseEvents($data['id'], '',
                                 date('Y-m-d', $str_start - ($str_end - $str_start)),
                                 $array['date_start']);
            $previous_percentage = round(((($a_times_previous[1] - $a_times_previous[0]) / $a_times_previous[1]) * 100), 3);
            $percentage = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 3);
            if ($previous_percentage < $percentage) {
               echo '<img src="../pics/arrow-up-right.png" width="16" />';
            } else if ($previous_percentage == $percentage) {
               echo '<img src="../pics/arrow-right.png" width="16" />';
            } else if ($previous_percentage > $percentage) {
               echo '<img src="../pics/arrow-down-right.png" width="16" />';
            }
            echo '</td>';
            echo '<td>';
            echo $percentage."%";
            echo '</td>';
            echo '<td>';
            if ($a_times[0] == 0) {
               echo "-";
            } else {
               echo Html::timestampToString($a_times[0]);
            }
            echo '</td>';
            foreach ($a_name as $name) {
               echo '<td>';
               echo min($ret[0][$name]);
               echo '</td>';
               echo '<td>';
               echo round(array_sum($ret[0][$name]) / count($ret[0][$name]), 3);
               echo '</td>';
               echo '<td>';
               echo max($ret[0][$name]);
               echo '</td>';
            }
            echo '</tr>';
         }
         echo '</table>';
      }
      if ($pdf) {
         $content = PluginMonitoringReport::endCapture();
         PluginMonitoringReport::generatePDF($content);
      }
   }



   function generateSyntheseReport($array, $pdf=TRUE) {
      global $DB;

      if (count($array) == 0) {
         return;
      }
      $end_date = $array['synthesedate_end'];
      $end_date_timestamp = strtotime($end_date);
      $number   = $array['synthesenumber'];
      $period   = $array['syntheseperiod'];

      $componentscatalogs_id = $array['componentscatalogs_id'];

      $pmComponent    = new PluginMonitoringComponent();
      $pmUnavailability = new PluginMonitoringUnavailability();
      $pmServiceevent = new PluginMonitoringServiceevent();

      if ($pdf) {
         PluginMonitoringReport::beginCapture();
      }
      echo "<table class='tab_cadrehov'>";
      foreach ($array['components_id'] as $components_id) {
         $pmComponent->getFromDB($components_id);
         array_unshift($array['perfname'][$components_id], 'avaibility');
         array_unshift($array['perfname_val'][$components_id], 1);
         echo '<tr class="tab_bg_1" height="90">';
         echo '<th colspan="'.(3 + ($number * 2)).'">';
         echo $pmComponent->getName();
         echo '</th>';
         echo '</tr>';

         foreach ($array['perfname'][$components_id] as $num=>$groupname) {
            echo '<tr class="tab_bg_1">';
            echo '<th colspan="'.(3 + ($number * 2)).'">';
            if ($groupname == 'avaibility') {
               echo __('Avaibility', 'monitoring');
            } else {
               echo $groupname;
            }
            echo '</th>';
            echo '</tr>';

            echo '<tr class="tab_bg_1">';
            echo '<th rowspan="2">';
            echo __('Name');
            echo '</th>';
            echo '<th rowspan="2">';
            echo __('Entity');
            echo '</th>';
            echo '<th rowspan="2">';
            echo __('Itemtype');
            echo '</th>';
            $a_year = array();
            for ($i = $number; $i >= 1;$i--) {
               $year = date('Y', strtotime("-".$i." ".$period, $end_date_timestamp));
               if (!isset($a_year[$year])) {
                  $a_year[$year] = 2;
               } else {
                  $a_year[$year] += 2;
               }
            }
            foreach ($a_year as $year=>$colspan) {
               echo '<th colspan="'.$colspan.'">';
               echo $year;
               echo '</th>';
            }
            echo '</tr>';

            echo '<tr class="tab_bg_1">';
            for ($i = $number; $i >= 1;$i--) {
               echo '<th colspan="2">';
               echo Html::convDate(date('m-d', strtotime("-".$i." ".$period, $end_date_timestamp)));
               echo "<br/>";
               echo Html::convDate(date('m-d', strtotime("-".($i-1)." ".$period, $end_date_timestamp)));
               echo '</th>';
            }
            echo '</tr>';

            $query = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
                  `glpi_plugin_monitoring_services`.`id` as sid FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               LEFT JOIN `glpi_plugin_monitoring_services`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`=`plugin_monitoring_componentscatalogs_hosts_id`
               WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
                  AND `plugin_monitoring_components_id`='".$components_id."'";
            $result = $DB->query($query);
            $rownb = true;
            while ($data=$DB->fetch_array($result)) {
               $itemtype = $data['itemtype'];
               $item = new $itemtype();
               $item->getFromDB($data['items_id']);

               if ($groupname == 'avaibility') {
                  $a_times = $pmUnavailability->parseEvents($data['id'], '',
                                                          date('Y-m-d', strtotime("-".($number + 1)." ".$period, $end_date_timestamp)),
                                                          date('Y-m-d', strtotime("-".$number." ".$period, $end_date_timestamp)));
                  $previous_value = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 3);
               } else {
                  $previous_value = 0;
               }
               echo '<tr class="tab_bg'.(($rownb = !$rownb)?'_4':'_1').'">';
               echo '<td>';
               echo $item->getLink();
               echo '</td>';
               echo '<td>';
               echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']);
               echo '</td>';
               echo '<td>';
               echo $item->getTypeName();
               echo '</td>';
               for ($i = $number; $i >= 1;$i--) {
                  $startdatet = date('Y-m-d', strtotime("-".$i." ".$period, $end_date_timestamp));
                  $enddatet   = date('Y-m-d', strtotime("-".($i-1)." ".$period, $end_date_timestamp));
                  if ($groupname == 'avaibility') {
                     $a_times = $pmUnavailability->parseEvents($data['id'], '', $startdatet, $enddatet);
                     $value = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 2);
                  } else {
                     $queryevents = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                        WHERE `plugin_monitoring_services_id`='".$data['sid']."'
                           AND `date` >= '".$startdatet."'
                           AND `date` <= '".$enddatet."'
                        ORDER BY `date`";
                     $resultevents = $DB->query($queryevents);
                     $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmComponent->fields['id']);
                     $ret = $pmServiceevent->getData($resultevents, $pmComponent->fields['graph_template'], $startdatet, $enddatet);
                     if (!isset($ret[0][$groupname])) {
                        $value = 0;
                     } else {
                        $value = round(array_sum($ret[0][$groupname]) / count($ret[0][$groupname]), 2);
                     }
                  }

                  $bgcolor = '';
                  if ($array['perfname_val'][$components_id][$num] == 1) {
                     if ($previous_value < $value) {
                        $bgcolor = 'style="background-color:#d1ffc3"';
                     } else if ($previous_value > $value) {
                        $bgcolor = 'style="background-color:#ffd1d3"';
                     }
                  } else {
                     if ($previous_value < $value) {
                        $bgcolor = 'style="background-color:#ffd1d3"';
                     } else if ($previous_value > $value) {
                        $bgcolor = 'style="background-color:#d1ffc3"';
                     }
                  }

                  echo '<td '.$bgcolor.'>';
                  if ($groupname == 'avaibility') {
                     echo $value."%";
                  } else {
                     if ($value > 3000000000) {
                        echo round($value/1000000000, 2).'T';
                     } else if ($value > 3000000) {
                        echo round($value/1000000, 2).'M';
                     } else if ($value > 3000) {
                        echo round($value/1000, 2).'K';
                     } else {
                        echo $value;
                     }
                  }
                  echo '</td>';
                  echo '<td '.$bgcolor.'>';
                  if ($array['perfname_val'][$components_id][$num] == 1) {
                     if ($previous_value < $value) {
                        echo '<img src="../pics/arrow-up-right.png" width="16" />';
                     } else if ($previous_value == $value) {
                        echo '<img src="../pics/arrow-right.png" width="16" />';
                     } else if ($previous_value > $value) {
                        echo '<img src="../pics/arrow-down-right.png" width="16" />';
                     }
                  } else {
                     if ($previous_value < $value) {
                        echo '<img src="../pics/arrow-up-right_inv.png" width="16" />';
                     } else if ($previous_value == $value) {
                        echo '<img src="../pics/arrow-right.png" width="16" />';
                     } else if ($previous_value > $value) {
                        echo '<img src="../pics/arrow-down-right_inv.png" width="16" />';
                     }
                  }
                  $previous_value = $value;
                  echo '</td>';
               }
               echo "</tr>";

            }
         }
         echo '<tr class="tab_bg_1" height="50">';
         echo '<td colspan="'.(3 + ($number * 2)).'">';
         echo '</td>';
         echo '</tr>';
      }
      echo "</table>";
      if ($pdf) {
         $content = PluginMonitoringReport::endCapture();
         PluginMonitoringReport::generatePDF($content, 'L');
      }
   }
}

?>