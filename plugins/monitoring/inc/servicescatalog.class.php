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

class PluginMonitoringServicescatalog extends CommonDropdown {

   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_servicescatalog';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Services catalog', 'monitoring');
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
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginMonitoringBusinessrulegroup', $ong, $options);
      $this->addStandardTab("PluginMonitoringServicescatalog", $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);

	return $ong;
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $array_ret = array();
      if (get_class($item) == __CLASS__) {
         $array_ret[50] = __('Contacts', 'monitoring');
      } else {
         if (Session::haveRight("plugin_monitoring_homepage", READ)
                 && Session::haveRight("plugin_monitoring_servicescatalog", PluginMonitoringServicescatalog::HOMEPAGE)) {
            $array_ret[49] = self::createTabEntry(
                    __('Services catalogs', 'monitoring'));
         }
      }

      return $array_ret;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($tabnum == 49) {
         $pmServicescatalog   = new PluginMonitoringServicescatalog();
         $pmDisplay           = new PluginMonitoringDisplay();

         // $pmDisplay->showCounters("Businessrules");
         $pmServicescatalog->showChecks();
      } else if ($tabnum == 50) {
         $pmContact_Item = new PluginMonitoringContact_Item();
         $pmContact_Item->showContacts("PluginMonitoringServicescatalog", $item->getID());
      } else {
		Plugin::load('monitoring',true);
		Plugin::displayAction($appliance, $_POST['glpi_tab']);

      }

      return true;
   }



   function post_addItem() {
      global $DB;

      // Toolbox::logInFile("pm", "  post_addItem : ".$this->getID()." : ".$this->getField('is_generic')."\n");

      $pmLog = new PluginMonitoringLog();

      $input = array();
      $input['itemtype'] = "PluginMonitoringServicesCatalog";
      $input['items_id'] = $this->fields['id'];
      $input['action'] = "add";
      $input['value'] = "New service catalog ".$DB->escape($this->fields['name']);
      $pmLog->add($input);

      // Generic services catalogs only ...
      if ($this->getField('is_generic')) {
         $this->updateGenericServicesCatalogs();
      }
   }



   function post_updateItem($history=1) {
      // Toolbox::logInFile("pm", "  post_updateItem : ".$this->getID()." : ".$this->getField('is_generic')."\n");

      // Generic services catalogs only ...
      if ($this->getField('is_generic')) {
         $this->updateGenericServicesCatalogs();
      }
   }



   function post_deleteItem() {
   }



   function post_purgeItem() {
      global $DB;

      // Toolbox::logInFile("pm", "  post_purgeItem : \n");

      $pmLog = new PluginMonitoringLog();

      $input = array();
      $input['itemtype'] = "PluginMonitoringServicesCatalog";
      $input['items_id'] = $this->fields['id'];
      $input['action'] = "delete";
      $input['value'] = "Deleted service catalog : ".$DB->escape($this->fields['name']);
      $pmLog->add($input);

      // Generic services catalogs only ...
      if (! $this->getField('is_generic')) return;

      $this->updateGenericServicesCatalogs('delete');
   }



   /**
    * Get service short state (state + acknowledgement)
    * options :
    * - image, if exists, returns URL to a state image
    *
    * Return :
    * - green if service is OK
    * - red if service is CRITICAL
    * - redblue if red and acknowledged
    * - orange if host is WARNING, RECOVERY or FLAPPING
    * - orangeblue if orange and acknowledged
    * - yellow for every other state
    * - yellowblue if yellow and acknowledged
    *
    * append '_soft' if service is in soft statetype
    */
   function getShortState($options=array()) {
      global $CFG_GLPI;

      // Toolbox::logInFile("pm", "getShortState - ".$this->getID()."\n");
      if ($this->getID() == -1) return '';

      $acknowledge = $this->getField('is_acknowledged');
      $state_type = $this->getField('state_type');
      $state = $this->getField('state');
      $event = 'event';


      $shortstate = '';
      switch($state) {

         case 'OK':
            $shortstate = 'green';
            break;

         case 'CRITICAL':
            if ($acknowledge) {
               $shortstate = 'redblue';
            } else {
               $shortstate = 'red';
            }
            break;

         case 'WARNING':
         case 'RECOVERY':
         case 'FLAPPING':
            if ($acknowledge) {
               $shortstate = 'orangeblue';
            } else {
               $shortstate = 'orange';
            }
            break;

         default:
            if ($acknowledge) {
               $shortstate = 'yellowblue';
            } else {
               $shortstate = 'yellow';
            }
            break;

      }
      if ($state == 'WARNING'
              && $event == '') {
         if ($acknowledge) {
            $shortstate = 'yellowblue';
         } else {
            $shortstate = 'yellow';
         }
      }
      if ($state_type == 'SOFT') {
         $shortstate.= '_soft';
      }

      if (isset($options) && isset($options['image'])) {
         return $CFG_GLPI['root_doc']."/plugins/monitoring/pics/box_".$shortstate."_".$options['image'].".png";
      }
      return $shortstate;
   }


   function showForm($items_id, $options=array()) {

      if ($items_id == '-1') {
         $items_id = '';
      }

      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";
      echo "<td>".__('Check definition', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::show("PluginMonitoringCheck",
                        array('name'=>'plugin_monitoring_checks_id',
                              'value'=>$this->fields['plugin_monitoring_checks_id']));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Comment', 'Comments', 2)."&nbsp;: </td>";
      echo "<td>";
      echo "<textarea cols='45' rows='2' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      echo "<td>".__('Check period', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      dropdown::show("Calendar", array('name'=>'calendars_id',
                                 'value'=>$this->fields['calendars_id']));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'></td>";
      echo "<td>".__('Interval between 2 notifications', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showNumber('notification_interval', array(
         'value'    => $this->fields['notification_interval'],
         'min'      => 0,
         'max'      => 2880,
         'unit'     => 'minute(s)')
      );
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'></td>";
      echo "<td>".__('Business priority level', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showNumber('business_priority', array(
         'value'    => $this->fields['business_priority'],
         'min'      => 1,
         'max'      => 5)
      );
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'></td>";
      echo "<td>".__('Services catalog template ?', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      if (PluginMonitoringServicescatalog::canUpdate()) {
         Dropdown::showYesNo('is_generic', $this->fields['is_generic']);
      } else {
         echo Dropdown::getYesNo($this->fields['is_generic']);
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }



   function showChecks() {

      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr class='tab_bg_4' style='background: #cececc;'>";

      // All services catalogs except generic ones ...
      $a_ba = $this->find("`entities_id` IN (".$_SESSION['glpiactiveentities_string'].") AND `is_generic`='0'", "`business_priority`");
      $i = 0;
      foreach ($a_ba as $data) {
         echo "<td>";

         echo $this->showWidget($data['id']);
         if (isset($_SESSION['plugin_monitoring_reduced_interface'])) {
            $this->ajaxLoad($data['id'], ! $_SESSION['plugin_monitoring_reduced_interface']);
         } else {
            $this->ajaxLoad($data['id']);
         }

         echo "</td>";

         $i++;
         if ($i == '6') {
            echo "</tr>";
            echo "<tr class='tab_bg_4' style='background: #cececc;'>";
            $i = 0;
         }
      }

      echo "</tr>";
      echo "</table>";
   }



   function showWidget($id) {
      return "<div id=\"updateservicescatalog".$id."\"></div>";
   }



   function showBADetail($id) {
      global $CFG_GLPI;

      $pmParentSC = new PluginMonitoringServicescatalog();
      $pMonitoringBusinessrule = new PluginMonitoringBusinessrule();
      $pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pMonitoringService = new PluginMonitoringService();

      $this->getFromDB($id);
      $derivated = false;
      if ($this->getField('plugin_monitoring_servicescatalogs_id') > 0) {
         $derivated = true;
         $pmParentSC->getFromDB($this->getField('plugin_monitoring_servicescatalogs_id'));
      }

      // If SC is derivated from a template, get groups from its parent ...
      if ($derivated) {
         $a_groups = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$pmParentSC->fields['id']."'");
      } else {
         $a_groups = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$id."'");
      }

      echo "<table class='tab_cadrehov'>";
      echo "<tr class='tab_bg_1'>";

/*
      $color = $this->getShortState();
      // $color = PluginMonitoringHost::getState($this->fields['state'],
                                                 // $this->fields['state_type'],
                                                 // 'data',
                                                 // $this->fields['is_acknowledged']);
      $pic = $color;
      $color = str_replace("_soft", "", $color);
*/

      echo "<td rowspan='".count($a_groups)."' class='center service".$this->getField('state')."' width='200'>";
      echo "<strong style='font-size: 20px'>".$this->getName()."</strong><br/>";
      echo "<img src='".$this->getShortState(array('image'=>'40'))."'/>";
      echo "</td>";

      $i = 0;
      foreach($a_groups as $gdata) {
         $a_brulesg = $pMonitoringBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");

         if ($i > 0) {
            echo "<tr>";
         }

         $state = array();
         $state['OK'] = 0;
         $state['WARNING'] = 0;
         $state['CRITICAL'] = 0;
         $state['UNKNOWN'] = 0;
         foreach ($a_brulesg as $brulesdata) {
            // If SC is derivated from a template, do not care about services not from the same entity  ...
            if (! $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id'])) continue;
            if ($derivated && ($pMonitoringService->getField('entities_id') != $this->getField('entities_id'))) continue;

            $state[$pMonitoringService->getState()]++;
         }
         $overall_state = "OK";
         if ($gdata['operator'] == 'and') {
            if ($state['UNKNOWN'] >= 1) {
               $overall_state = "UNKNOWN";
            } else if ($state['CRITICAL'] >= 1) {
               $overall_state = "CRITICAL";
            } else if ($state['WARNING'] >= 1) {
               $overall_state = "WARNING";
            } else if ($state['OK'] >= 1) {
               $overall_state = "OK";
            }
         } else if ($gdata['operator'] == 'or') {
            if ($state['OK'] >= 1) {
               $overall_state = "OK";
            } else if ($state['WARNING'] >= 1) {
               $overall_state = "WARNING";
            } else if ($state['CRITICAL'] >= 1) {
               $overall_state = "CRITICAL";
            } else if ($state['UNKNOWN'] >= 1) {
               $overall_state = "UNKNOWN";
            }
         } else {
            $num_min = str_replace(" of:", "", $gdata['operator']);
            if ($state['OK'] >= $num_min) {
               $overall_state = "OK";
            } else if ($state['WARNING'] >= $num_min) {
               $overall_state = "WARNING";
            } else if ($state['CRITICAL'] >= $num_min) {
               $overall_state = "CRITICAL";
            } else if ($state['UNKNOWN'] >= $num_min) {
               $overall_state = "UNKNOWN";
            }
         }

         echo "<td class='center service".$overall_state."'>";
         echo $gdata['name']."<br/>[ ".$gdata['operator']." ]";
         echo "</td>";

         echo "<td class='service".$overall_state."'>";
            echo "<table>";
            foreach ($a_brulesg as $brulesdata) {
               // $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id']);
               // If SC is derivated from a template, do not care about services not from the same entity  ...
               if (! $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id'])) continue;
               if ($derivated && ($pMonitoringService->getField('entities_id') != $this->getField('entities_id'))) continue;

               // Last parameter is true to display counters/graphs, false if not needed
               echo "<tr class='tab_bg_1'>";
               // Display a line for the service including host information, no counters but graphs
               PluginMonitoringDisplay::displayLine($pMonitoringService->fields, true, false, true);
               echo "</tr>";
            }
            echo "</table>";
         echo "</th>";
         echo "</tr>";
         $i++;
      }
      echo "</tr>";

      echo "</table>";
   }



   function showWidgetFrame($id, $reduced_interface=false, $is_minemap=FALSE) {
      global $DB, $CFG_GLPI;

      $pmParentSC = new PluginMonitoringServicescatalog();
      $pMonitoringBusinessrule = new PluginMonitoringBusinessrule();
      $pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pMonitoringService = new PluginMonitoringService();

      $this->getFromDB($id);
      $derivated = false;
      if ($this->getField('plugin_monitoring_servicescatalogs_id') > 0) {
         $derivated = true;
         $pmParentSC->getFromDB($this->getField('plugin_monitoring_servicescatalogs_id'));
      }
      $data = $this->fields;
      // Toolbox::logInFile("pm", "SC : ".$data['id'].", name : ".$data['name'].", derivated : ".$derivated."\n");

      $colorclass = 'ok';
      switch($data['state']) {
         case 'CRITICAL':
         case 'DOWNTIME':
            $colorclass = 'crit';
            break;

         case 'WARNING':
         case 'UNKNOWN':
         case 'RECOVERY':
         case 'FLAPPING':
         case '':
            $colorclass = 'warn';
            break;

      }


      echo '<br/><div class="ch-itemup">
         <div class="ch-info-'.$colorclass.'">
         <h1><a href="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/front/servicescatalog.form.php?id='.$data['id'].'&detail=1">';
         echo $data['name'];
         if ($data['comment'] != '') {
            echo ' '.$this->getComments();
         }
         echo '</a></h1>
         </div>
      </div>';

      // If SC is derivated from a template, get groups from its parent ...
      if ($derivated) {
         $a_group = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$pmParentSC->fields['id']."'");
      } else {
         $a_group = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$data['id']."'");
      }

      // Array updated dynamically with groups/hosts/services status ...
      $colorclass = 'ok';
      $a_gstate = array();
      $cs_info = array();
      foreach ($a_group as $gdata) {
         // Toolbox::logInFile("pm", "BR group : ".$gdata['id'].", name : ".$gdata['name']."\n");
         $a_brules = $pMonitoringBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");

         $state = array();
         $state['OK'] = 0;
         $state['WARNING'] = 0;
         $state['CRITICAL'] = 0;
         $cs_info_hosts = array();
         $cs_info_services = array();
         foreach ($a_brules as $brulesdata) {
            // If SC is derivated from a template, do not care about services not from the same entity  ...
            if (! $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id'])) continue;
            if ($derivated && ($pMonitoringService->getField('entities_id') != $this->getField('entities_id'))) continue;
            // Toolbox::logInFile("pm", "BR : ".$brulesdata['id'].", service : ".$brulesdata['plugin_monitoring_services_id']."\n");

            if (! isset($cs_info_hosts[$pMonitoringService->getHostName()])) $cs_info_hosts[$pMonitoringService->getHostName()] = array();
            $cs_info_hosts[$pMonitoringService->getHostName()]['id'] = $pMonitoringService->getHostId();
            $cs_info_hosts[$pMonitoringService->getHostName()]['name'] = $pMonitoringService->getHostName();
            $cs_info_hosts[$pMonitoringService->getHostName()]['services'][$pMonitoringService->getName()]['id'] = $pMonitoringService->fields['id'];
            $cs_info_hosts[$pMonitoringService->getHostName()]['services'][$pMonitoringService->getName()]['state'] = $pMonitoringService->fields['state'];
            $cs_info_hosts[$pMonitoringService->getHostName()]['services'][$pMonitoringService->getName()]['last_check'] = $pMonitoringService->fields['last_check'];
            $cs_info_hosts[$pMonitoringService->getHostName()]['services'][$pMonitoringService->getName()]['event'] = $pMonitoringService->fields['event'];

            // Get all host services except if state is ok or is already acknowledged ...
            $a_ret = PluginMonitoringHost::getServicesState($pMonitoringService->getHostId(), "`glpi_plugin_monitoring_services`.`state` != 'OK'");
            $cs_info_hosts[$pMonitoringService->getHostName()]['state'] = $a_ret[0];

            $cs_info_services[$pMonitoringService->getName()] = $pMonitoringService->fields['plugin_monitoring_components_id'];

            switch($pMonitoringService->fields['state']) {

               case 'UP':
               case 'OK':
                  $state['OK']++;
                  break;

               case 'DOWN':
               case 'UNREACHABLE':
               case 'CRITICAL':
               case 'DOWNTIME':
                  $state['CRITICAL']++;
                  break;

               case 'WARNING':
               case 'UNKNOWN':
               case 'RECOVERY':
               case 'FLAPPING':
                  $state['WARNING']++;
                  break;

            }
         }
         if ($state['CRITICAL'] >= 1) {
            $a_gstate[$gdata['id']] = "CRITICAL";
         } else if ($state['WARNING'] >= 1) {
            $a_gstate[$gdata['id']] = "WARNING";
         } else {
            $a_gstate[$gdata['id']] = "OK";
         }

         $cs_info[$gdata['id']] = array();
         $cs_info[$gdata['id']]['name'] = $gdata['name'];
         $cs_info[$gdata['id']]['state'] = $a_gstate[$gdata['id']];
         $cs_info[$gdata['id']]['hosts'] = $cs_info_hosts;
         $cs_info[$gdata['id']]['services'] = $cs_info_services;
      }
      $state = array();
      $state['OK'] = 0;
      $state['WARNING'] = 0;
      $state['CRITICAL'] = 0;
      foreach ($a_gstate as $value) {
         $state[$value]++;
      }
      $color = 'green';
      if ($state['CRITICAL'] > 0) {
         $color = 'red';
         $colorclass = 'crit';
      } else if ($state['WARNING'] > 0) {
         $color = 'orange';
         $colorclass = 'warn';
      }

      echo '<div class="ch-itemdown">
         <div class="ch-info-'.$colorclass.'">
         <p><font style="font-size: 20px;">';
      if ($colorclass != 'ok') {
         echo __('Degraded mode', 'monitoring').'!';
      }

      echo '</font></p>
         </div>
      </div>';


      // Show a minemap if requested ...
      echo "<div class='minemapdiv' align='center'>"
            ."<a onclick='$(\"#minemapSC-".$id."\").toggle();'>"
            .__('Minemap', 'monitoring')."</a></div>";
      if (!$is_minemap) {
         echo '<div class="minemapdiv" id="minemapSC-'.$id.'" style="display: none; z-index: 1500">';
      } else {
         echo '<div class="minemapdiv" id="minemapSC-'.$id.'">';
      }
      echo '<table class="tab_cadrehov">';


      foreach ($cs_info as $groupName=>$group) {
         echo '<table class="tab_cadrehov">';
         echo '<tr>';
         echo '<th colspan="'. (1+count($group['services'])) .'">'.__('Business rules group', 'monitoring')."&nbsp; : ".$group['name'].'</th>';
         echo '</tr>';

         echo '<tr>';
         echo "<th>";
         echo __('Hosts', 'monitoring');
         echo "</th>";
         foreach ($group['services'] as $serviceName => $service) {
            if (Session::haveRight("plugin_monitoring_service", READ)) {
               $link = $CFG_GLPI['root_doc'].
                  "/plugins/monitoring/front/service.php?hidesearch=1"
//                       . "&reset=reset"
                       . "&criteria[0][field]=2"
                       . "&criteria[0][searchtype]=equals"
                       . "&criteria[0][value]=".$service

                       . "&itemtype=PluginMonitoringService"
                       . "&start=0'";
               echo  '<th class="vertical">';
               echo  '<a href="'.$link.'"><div class="rotated-text"><span class="rotated-text__inner">'.$serviceName.'</span></div></a>';
               echo  '</th>';
            } else {
               echo  '<th class="vertical">';
               echo  '<div class="rotated-text"><span class="rotated-text__inner">'.$serviceName.'</span></div>';
               echo  '</th>';
            }
         }
         echo '</tr>';

         foreach ($group['hosts'] as $host) {
            echo  "<tr class='tab_bg_2' style='height: 50px;'>";

            if (Session::haveRight("plugin_monitoring_service", READ)) {
               $link = $CFG_GLPI['root_doc'].
                  "/plugins/monitoring/front/service.php?hidesearch=1"
//                       . "&reset=reset"
                       . "&criteria[0][field]=20"
                       . "&criteria[0][searchtype]=equals"
                       . "&criteria[0][value]=".$host['id']

                       . "&itemtype=PluginMonitoringService"
                       . "&start=0'";
               echo  "<td class='left'><a href='".$link."'>".$host['name']."</a></td>";
            } else {
               echo  "<td class='left'>".$host['name']."</td>";
            }
            // echo  "<td class='left'>".$host['name']."</td>";
            foreach ($host['services'] as $serviceName => $service) {
               echo '<td>';
               echo '<div title="'.$service['last_check'].' - '.$service['event'].'" class="service service'.$service['state'].'"></div>';
               echo  '</td>';
            }


            echo  '</tr>';
         }
         echo  '</table>';
      }

      echo  '</table>';
      echo '</div>';
   }



   function ajaxLoad($id) {
      global $CFG_GLPI;

         echo "<script type=\"text/javascript\">
            (function worker() {
              $.get('".$CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updateWidgetServicescatalog.php"
                    ."?id=".$id."', function(data) {
                $('#updateservicescatalog".$id."').html(data);
                setTimeout(worker, 50000);
              });
            })();
         </script>";
   }



   function updateGenericServicesCatalogs($action='update') {
      global $DB;

      $entity = new Entity();
      $pmServicescatalog = new PluginMonitoringServicescatalog();
      $pmBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pmBusinessrulecomponent = new PluginMonitoringBusinessrule_component();


      $existingSCs = array();

      // Find existing instances of generic services catalog ...
      $a_SCs = $this->find("`name` LIKE '".$this->getName()."%'");
      foreach ($a_SCs as $a_SC) {
         // Toolbox::logInFile("pm", "SC : ".$a_SC['id'].", name : ".$a_SC['name'].", generic : ".$a_SC['is_generic']."\n");

         if ($a_SC['name'] == $this->getField('name')) continue;
         $existingSCs[$a_SC['name']] = $a_SC;
      }
      if ($action=='delete') {
         foreach ($existingSCs as $name=>$a_SC) {
            $pmServicescatalog->getFromDB($a_SC['id']);
            $pmServicescatalog->delete($pmServicescatalog->fields);
            // Toolbox::logInFile("pm", "Deleted : ".$a_SC['name']."\n");

            $pmBusinessrulecomponent = new PluginMonitoringBusinessrule_component();
            $pmBusinessrule = new PluginMonitoringBusinessrule();

            // Get business rules groups ...
            $a_BRgroups = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$a_SC['id']."'");

            // Delete business groups components
            foreach ($a_BRgroups as $a_BRgroup) {
               // Toolbox::logInFile("pm", "a_BRgroup : ".$a_BRgroup['id']."\n");
               $a_brcomponents = $pmBusinessrulecomponent->find("`plugin_monitoring_businessrulegroups_id`='".$a_BRgroup['id']."'");
               foreach ($a_brcomponents as $a_brcomponent) {
                  // Toolbox::logInFile("pm", "a_brcomponent : ".$a_brcomponent['id']."\n");
                  $pmBusinessrulecomponent->getFromDB($a_brcomponent['id']);
                  $pmBusinessrulecomponent->delete($pmBusinessrulecomponent->fields);
               }
            }

            // Delete business groups rules
            foreach ($a_BRgroups as $a_BRgroup) {
               $a_brs = $pmBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$a_BRgroup['id']."'");
               foreach ($a_brs as $a_br) {
                  // Toolbox::logInFile("pm", "a_br : ".$a_br['id']."\n");
                  $pmBusinessrulecomponent->getFromDB($a_brcomponent['id']);
                  $pmBusinessrulecomponent->delete($pmBusinessrulecomponent->fields);
               }
            }
         }
         return;
      } else {
         foreach ($existingSCs as $name=>$a_SC) {
            $pmServicescatalog->getFromDB($a_SC['id']);

            $pmBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
            $pmBusinessrulecomponent = new PluginMonitoringBusinessrule_component();
            $pmBusinessrule = new PluginMonitoringBusinessrule();

            // Get business rules groups ...
            $a_BRgroups = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$a_SC['id']."'");

            // Delete business groups components
            foreach ($a_BRgroups as $a_BRgroup) {
               // Toolbox::logInFile("pm", "a_BRgroup : ".$a_BRgroup['id']."\n");
               $a_brcomponents = $pmBusinessrulecomponent->find("`plugin_monitoring_businessrulegroups_id`='".$a_BRgroup['id']."'");
               foreach ($a_brcomponents as $a_brcomponent) {
                  // Toolbox::logInFile("pm", "a_brcomponent : ".$a_brcomponent['id']."\n");
                  $pmBusinessrulecomponent->getFromDB($a_brcomponent['id']);
                  $pmBusinessrulecomponent->delete($pmBusinessrulecomponent->fields);
               }
            }

            // Delete business groups rules
            foreach ($a_BRgroups as $a_BRgroup) {
               $a_brs = $pmBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$a_BRgroup['id']."'");
               foreach ($a_brs as $a_br) {
                  // Toolbox::logInFile("pm", "a_br : ".$a_br['id']."\n");
                  $pmBusinessrulecomponent->getFromDB($a_brcomponent['id']);
                  $pmBusinessrulecomponent->delete($pmBusinessrulecomponent->fields);
               }
            }
         }
      }


      // Find entities concerned ...
      $a_entitiesServices = $this->getGenericServicesEntities();
      foreach ($a_entitiesServices as $idEntity=>$a_entityServices) {
         // New entity ... so it must exist a derivated SC !
         $entity->getFromDB($idEntity);
         // Toolbox::logInFile("pm", "Found entity : ".$idEntity." / ".$entity->getName()."\n");

         $scName = $this->getName()." - ".$entity->getName();

         if (isset($existingSCs[$scName])) {
            // Update SC
            $pmServicescatalog->getFromDB($existingSCs[$scName]['id']);
            $pmServicescatalog->fields = $this->fields;
            unset($pmServicescatalog->fields['id']);
            $pmServicescatalog->fields['id'] = $existingSCs[$scName]['id'];
            $pmServicescatalog->fields['entities_id'] = $idEntity;
            $pmServicescatalog->fields['is_generic'] = 0;
            $pmServicescatalog->fields['name'] = $DB->escape($scName);
            $pmServicescatalog->update($pmServicescatalog->fields);

/*            // Finish updating if needed ...
            $a_BRgroups = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$this->fields['id']."'");
            foreach ($a_BRgroups as $a_BRgroup) {
               $pmBusinessrulegroup = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$a_SC['id']."' AND `name`='".$a_BRgroup['name']."'");
               $pmBusinessrulegroup->fields = $a_BRgroup->fields;
               unset($pmBusinessrulegroup->fields['id']);
               $pmBusinessrulegroup->fields['plugin_monitoring_servicescatalogs_id'] = $pmServicescatalog->fields['id'];
               $pmBusinessrulegroup->update($pmBusinessrulegroup->fields);
            }
*/
            // Toolbox::logInFile("pm", "Updated : ".$scName."\n");
         } else {
            // Add SC
            $pmServicescatalog = new PluginMonitoringServicescatalog();
            $pmServicescatalog->getEmpty();
            $pmServicescatalog->fields = $this->fields;
            unset($pmServicescatalog->fields['id']);
            $pmServicescatalog->fields['entities_id'] = $idEntity;
            $pmServicescatalog->fields['is_recursive'] = 0;
            $pmServicescatalog->fields['is_generic'] = 0;
            $pmServicescatalog->fields['name'] = $DB->escape($scName);
            $pmServicescatalog->fields['plugin_monitoring_servicescatalogs_id'] = $this->fields['id'];
            $pmServicescatalog->add($pmServicescatalog->fields);

/*
            // Finish updating if needed ...
            $a_BRgroups = $pmBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$this->fields['id']."'");
            foreach ($a_BRgroups as $a_BRgroup) {
               $ref = new PluginMonitoringBusinessrulegroup();
               $ref->getFromDB($a_BRgroup['id']);
               $pmBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
               $pmBusinessrulegroup->getEmpty();
               $pmBusinessrulegroup->fields = $ref->fields;
               unset($pmBusinessrulegroup->fields['id']);
               $pmBusinessrulegroup->fields['plugin_monitoring_servicescatalogs_id'] = $pmServicescatalog->fields['id'];
               $pmBusinessrulegroup->add($pmBusinessrulegroup->fields);

               $a_brcomponents = $pmBusinessrulecomponent->find("`plugin_monitoring_businessrulegroups_id`='".$a_BRgroup['id']."'");
               foreach ($a_brcomponents as $a_brcomponent) {
                  $ref = new PluginMonitoringBusinessrule_component();
                  $ref->getFromDB($a_brcomponent['id']);
                  $pmBusinessrule_component = new PluginMonitoringBusinessrule_component();
                  $pmBusinessrule_component->getEmpty();
                  $pmBusinessrule_component->fields = $ref->fields;
                  unset($pmBusinessrule_component->fields['id']);
                  $pmBusinessrule_component->fields['plugin_monitoring_businessrulegroups_id'] = $pmBusinessrulegroup->fields['id'];
                  $pmBusinessrule_component->add($pmBusinessrule_component->fields);
               }
            }
*/
            // Toolbox::logInFile("pm", "Added : ".$scName."\n");
         }
      }
   }


   function getGenericServicesEntities() {
      global $DB;

      // SC must be a template ...
      if (! ($this->fields['is_generic'])) {
         return;
      }

      if ($this->fields['is_recursive']) {
         $a_sons = getSonsOf("glpi_entities", $this->fields['entities_id']);
         $restrict_entities = "AND ( `glpi_plugin_monitoring_services`.`entities_id` IN ('".implode("','", $a_sons)."') )";
      } else {
         $restrict_entities = "AND ( `glpi_plugin_monitoring_services`.`entities_id` = '".
                 $this->fields['entities_id']."' )";
      }

      $a_services = array();
      // foreach ($a_sons as $entity) {
         // $a_services[$entity] = array();
      // }

      $query = "SELECT
         `glpi_plugin_monitoring_services`.`id`
         , `glpi_plugin_monitoring_services`.`name`
         , `glpi_plugin_monitoring_services`.`entities_id`
         , `glpi_plugin_monitoring_businessrules`.`plugin_monitoring_businessrulegroups_id`
         FROM `glpi_plugin_monitoring_services`
         INNER JOIN `glpi_plugin_monitoring_businessrules`
            ON (`glpi_plugin_monitoring_services`.`id` = `glpi_plugin_monitoring_businessrules`.`plugin_monitoring_services_id`)
         INNER JOIN `glpi_plugin_monitoring_businessrulegroups`
            ON (`glpi_plugin_monitoring_businessrules`.`plugin_monitoring_businessrulegroups_id` = `glpi_plugin_monitoring_businessrulegroups`.`id`)
         INNER JOIN `glpi_plugin_monitoring_servicescatalogs`
            ON (`glpi_plugin_monitoring_businessrulegroups`.`plugin_monitoring_servicescatalogs_id` = `glpi_plugin_monitoring_servicescatalogs`.`id`)
         WHERE (`glpi_plugin_monitoring_servicescatalogs`.`id` ='".$this->getID()."'
            AND `glpi_plugin_monitoring_businessrules`.`is_generic` ='1'
            ".$restrict_entities.")
         ORDER BY `glpi_plugin_monitoring_services`.`entities_id` ASC, `glpi_plugin_monitoring_services`.`id` ASC;
      ";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $a_services[$data['entities_id']][$data['id']] =
                  array("entityId" => $data['entities_id'],
                        "serviceId" => $data['id'],
                        "BRgroupId" => $data['plugin_monitoring_businessrulegroups_id']
                        );
      }

      return $a_services;
   }
}

?>