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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringCustomitem_Counter extends CommonDBTM {


   static $rightname = 'plugin_monitoring_displayview';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Custom item', 'monitoring')." - ".__('Counter', 'monitoring');
   }

   

   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Commands', 'monitoring');

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = __('Name');
		$tab[1]['datatype'] = 'itemlink';

      return $tab;
   }



   function defineTabs($options=array()){
      $ong = array();
      return $ong;
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
   function showForm($items_id, $options=array(), $copy=array()) {
      global $DB,$CFG_GLPI;

      if ($items_id!='') {
         $this->getFromDB($items_id);
      } else {
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";
      echo "<td>".__('Type', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      $elements = $this->getCounterTypes();
      Dropdown::showFromArray('type', $elements, array('value' => $this->fields["type"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Time (not used for `last value` type)', 'monitoring')." :</td>";
      echo "<td>";
      $elements = PluginMonitoringCustomitem_Common::getTimes();
      Dropdown::showFromArray('time', $elements, array('value' => $this->fields["time"]));
      echo "</td>";
      echo "<td>".__('Calendar', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::show('Calendar', array(
          'name'  => 'time_specific',
          'value' => $this->fields['time_specific']
      ));
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      if ($items_id == 0) {
         return;
      }

      echo "<form name='form' method='post' action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/customitem_counter.form.php'>";
      echo "<input type='hidden' name='id' value='".$items_id."' />";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>";
      echo __('Add counter', 'monitoring');
      echo "</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo PluginMonitoringComponentscatalog::getTypeName();
      echo "</th>";
      echo "<th>";
      echo PluginMonitoringComponent::getTypeName();
      echo "</th>";
      echo "<th colspan='2'>";
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_3'>";
      echo "<td>";
      $toupdate = array(
          'value_fieldname' => 'id',
          'to_update'  => "add_selectcomponent",
          'url'        => $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/counterComponents.php"
      );
      Dropdown::show(
              'PluginMonitoringComponentscatalog',
              array('toupdate' => $toupdate));
      echo "</td>";
      echo "<td id='add_selectcomponent'>";

      echo "</td>";
      echo "<td id='add_data'>";

      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='add_item' value='".__('Add')."' class='submit' />";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();

      $array = importArrayFromDB($this->fields['aggregate_items']);
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();
      echo "<table class='tab_cadre_fixe'>";
      foreach ($array as $itemtype=>$data1) {
         foreach ($data1 as $items_id1=>$data2) {
            $item1 = new $itemtype();
            $item1->getFromDB(str_replace('id', '', $items_id1));
            echo "<tr class='tab_bg_3'>";
            echo "<td>";
            echo "[".$item1->getTypeName()."] ";
            echo $item1->getLink();
            echo "</td>";
            foreach ($data2 as $itemtype2=>$data3) {
               $nb4 = 0;
               foreach ($data3 as $items_id2=>$data4) {
                  if ($nb4 > 0) {
                     echo "</tr>";
                     echo "<tr class='tab_bg_3'>";
                     echo "<td>";
                     echo "[".$item1->getTypeName()."] ";
                     echo $item1->getLink();
                     echo "</td>";
                  }
                  $item2 = new $itemtype2();
                  $item2->getFromDB(str_replace('id', '', $items_id2));
                  echo "<td>";
                  echo "[".$item2->getTypeName()."] ";
                  echo $item2->getLink();
                  echo "</td>";
                  echo "<td>";
                  $j = 0;
                  foreach ($data4 as $num=>$data5) {
                     if ($j > 0) {
                        echo "<hr/>";
                     }
                     $this->showDefineDataOfCounter(
                             $items_id2,
                             array(
                                 'a' => $itemtype,
                                 'b' => str_replace('id', '', $items_id1),
                                 'c' => $itemtype2,
                                 'd' => str_replace('id', '', $items_id2),
                                 'num' => $num,
                                 'id' => $items_id
                             ),
                             TRUE);
                     $j++;
                     $nb4++;
                  }
               }
            }

            echo "</td>";
            echo "</tr>";

         }
      }
      echo "</table>";

      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo __("Preview", 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      $this->showWidget($items_id);
      $this->showWidgetFrame($items_id);
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      return true;
   }



   function getCounterTypes() {
      $a_types = array(
          'lastvalue'      => __('Last value', 'monitoring'),
          'lastvaluediff'  => __('Last value (diff for incremental)', 'monitoring'),
          'firstvalue'     => __('First value', 'monitoring'),
          'average'        => __('Average', 'monitoring'),
          'median'         => __('Median', 'monitoring'),
      );
      return $a_types;
   }



   function type_valueType($type='lastvalue') {
      global $DB;

      $pmService        = new PluginMonitoringService();
      $pmServiceevent   = new PluginMonitoringServiceevent();
      $pmComponent      = new PluginMonitoringComponent();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      $val    = 0;
      $nb_val = 0;

      $items = importArrayFromDB($this->fields['aggregate_items']);
      foreach ($items as $itemtype=>$data) {
         switch ($itemtype) {

            case 'PluginMonitoringService':
               $func = 'get_'.$type;
               $a_ret = $this->$func($data, $val, $nb_val);

               // $a_ret = $this->getLastValofServices($data, $val, $nb_val);
               $val    = $a_ret[0];
               $nb_val = $a_ret[1];
               break;

            case 'PluginMonitoringComponentscatalog':
               $pmComponentscatalog = new PluginMonitoringComponentscatalog();
               foreach ($data as $items_id=>$data2) {
                  $ret = $pmComponentscatalog->getInfoOfCatalog(str_replace('id', '', $items_id));
                  $a_hosts = $ret[6];
                  foreach ($data2['PluginMonitoringComponent'] as $items_id_components=>$data4) {
                     // get services  (use entities of user)
                     $a_services = array();
                     $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                        WHERE `plugin_monitoring_components_id`='".str_replace('id', '', $items_id_components)."'
                           AND `plugin_monitoring_componentscatalogs_hosts_id` IN
                              ('".implode("','", $a_hosts)."')
                           AND `entities_id` IN (".$_SESSION['glpiactiveentities_string'].")";
                     $result = $DB->query($query);
                     while ($dataq=$DB->fetch_array($result)) {
                        $a_services[$dataq['id']] = $data4;
                     }
                     // foreach ($a_services as $serviceId) {
                     //    Toolbox::logInFile("pm", "service ".array_keys($serviceId)."\n");
                     // }
                     $func = 'get_'.$type;
                     $a_ret = $this->$func(
                             $a_services,
                             $val,
                             $nb_val,
                             $a_ret);

                     // $this->getLastValofServices(
                             // $a_services,
                             // $val,
                             // $nb_val,
                             // $a_ret);
                  }
               }
               break;

         }
      }
      if ($nb_val != 0) {
         $val = ($val / $nb_val);
      }

      $a_ret['val'] = $val;
      return $a_ret;
   }


   /*
    * Function called by type_valueType function when selecting last value type counter
    */
   function get_lastvalue($data, &$val, &$nb_val, &$a_ret) {
      global $DB;

      $pmService        = new PluginMonitoringService();
      $pmServiceevent   = new PluginMonitoringServiceevent();
      $pmComponent      = new PluginMonitoringComponent();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      $a_services_id = array_keys($data);
      $data2 = current($data);

      $pmService->getFromDB($a_services_id[0]);
      $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);
      $pmComponent->getFromDB($pmService->fields['plugin_monitoring_components_id']);

      $query = "SELECT
           id,
           perf_data,
           date
         FROM
           glpi_plugin_monitoring_serviceevents
             JOIN
               (SELECT MAX(glpi_plugin_monitoring_serviceevents.id) AS max
                FROM glpi_plugin_monitoring_serviceevents
                WHERE `plugin_monitoring_services_id` IN ('".implode("','", $a_services_id)."')
                   AND `glpi_plugin_monitoring_serviceevents`.`state` = 'OK'
                   AND `glpi_plugin_monitoring_serviceevents`.`perf_data` != ''

                GROUP BY plugin_monitoring_services_id
                ORDER BY glpi_plugin_monitoring_serviceevents.`date` DESC) max_id ON
              (max_id.max = id)";

      $resultevent = $DB->query($query);
      while ($dataevent=$DB->fetch_array($resultevent)) {
         $ret = $pmServiceevent->getData(
                 array($dataevent),
                 $pmComponent->fields['graph_template'],
                 $dataevent['date'],
                 $dataevent['date']);
         foreach ($data2 as $a_perfdatadetails) {
            $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
            if (isset($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]])) {
               $val += $ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]][0];
               $nb_val++;
            }
         }
      }
   }


   /*
    * Function called by type_valueType function when selecting first value type counter
    */
   function get_firstvalue($data, &$val, &$nb_val, &$a_ret) {
      global $DB;

      $pmService        = new PluginMonitoringService();
      $pmServiceevent   = new PluginMonitoringServiceevent();
      $pmComponent      = new PluginMonitoringComponent();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      $a_services_id = array_keys($data);
      $data2 = current($data);

      $pmService->getFromDB($a_services_id[0]);
      $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);
      $pmComponent->getFromDB($pmService->fields['plugin_monitoring_components_id']);

      $query = "SELECT
           id,
           perf_data,
           date
         FROM
           glpi_plugin_monitoring_serviceevents
             JOIN
               (SELECT MIN(glpi_plugin_monitoring_serviceevents.id) AS min
                FROM glpi_plugin_monitoring_serviceevents
                WHERE `plugin_monitoring_services_id` IN ('".implode("','", $a_services_id)."')
                   AND `glpi_plugin_monitoring_serviceevents`.`state` = 'OK'
                   AND `glpi_plugin_monitoring_serviceevents`.`perf_data` != ''

                GROUP BY plugin_monitoring_services_id
                ORDER BY glpi_plugin_monitoring_serviceevents.`date` ASC) min_id ON
              (min_id.min = id)";

      $resultevent = $DB->query($query);
      while ($dataevent=$DB->fetch_array($resultevent)) {
         $ret = $pmServiceevent->getData(
                 array($dataevent),
                 $pmComponent->fields['graph_template'],
                 $dataevent['date'],
                 $dataevent['date']);
         foreach ($data2 as $a_perfdatadetails) {
            $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
            if (isset($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]])) {
               $val += $ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]][0];
               $nb_val++;
            }
         }
      }
   }



   function type_other($type='average') {
      global $DB;

      $pmService        = new PluginMonitoringService();
      $pmServiceevent   = new PluginMonitoringServiceevent();
      $pmComponent      = new PluginMonitoringComponent();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      $a_date = PluginMonitoringCustomitem_Common::getTimeRange($this->fields);

      $val    = 0;
      $a_val  = array();
      $nb_val = 0;

/*
      $a_tocheck = array(
          'warn'  => 0,
          'crit'  => 0,
          'limit' => 0
      );

      $a_types = array('warn', 'crit', 'limit');
      for ($i=0; $i< count($a_types); $i++) {
         if (is_numeric($this->fields['aggregate_'.$a_types[$i]])) {
            $a_ret[$a_types[$i]] = $this->fields['aggregate_'.$a_types[$i]];
         } else {
            $a_ret[$a_types[$i]] = 0;
            $a_tocheck[$a_types[$i]] = 1;
         }
      }
*/

      $items = importArrayFromDB($this->fields['aggregate_items']);
      foreach ($items as $itemtype=>$data) {
         switch ($itemtype) {

            case 'PluginMonitoringService':
               foreach ($data as $items_id=>$data2) {
                  $pmService->getFromDB($items_id);
                  $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);
                  $pmComponent->getFromDB($pmService->fields['plugin_monitoring_components_id']);
                  $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                     WHERE `plugin_monitoring_services_id`='".$items_id."'
                        AND `date` >= '".$a_date['begin']."'
                     ORDER BY `date`";
                  $result = $DB->query($query);

                  $ret = $pmServiceevent->getData(
                          $result,
                          $pmComponent->fields['graph_template'],
                          $a_date['begin'],
                          $a_date['end']);
                  foreach ($data2 as $a_perfdatadetails) {
                     $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
                     $nb_val += count($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                     $val += array_sum($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                     $a_val = array_merge($a_val, $ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                  }
/*
                  // for manage warn, crit and limit
                  foreach ($a_tocheck as $other_type=>$num_type) {
                     if ($num_type == 1) {
                        $other_items = importArrayFromDB($this->fields['aggregate_'.$other_type]);
                        foreach ($other_items[$itemtype][$items_id] as $a_perfdatadetails) {
                           $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
                           $a_ret[$other_type] += array_sum($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                        }
                     }
                  }
*/
               }
               break;

            case 'PluginMonitoringComponentscatalog':
               $pmComponentscatalog = new PluginMonitoringComponentscatalog();
               foreach ($data as $items_id=>$data2) {
                  $ret = $pmComponentscatalog->getInfoOfCatalog(str_replace('id', '', $items_id));
                  $a_hosts = $ret[6];
                  foreach ($data2['PluginMonitoringComponent'] as $items_id_components=>$data4) {
                     $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                        WHERE `plugin_monitoring_components_id`='".str_replace('id', '', $items_id_components)."'
                           AND `plugin_monitoring_componentscatalogs_hosts_id` IN
                              ('".implode("','", $a_hosts)."')
                           AND `entities_id` IN (".$_SESSION['glpiactiveentities_string'].")";
                     $result = $DB->query($query);
                     while ($dataq=$DB->fetch_array($result)) {
                        $pmService->getFromDB($dataq['id']);
                        $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);
                        $pmComponent->getFromDB($dataq['plugin_monitoring_components_id']);
                        $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                           WHERE `plugin_monitoring_services_id`='".$dataq['id']."'
                              AND `date` >= '".$a_date['begin']."'
                           ORDER BY `date`";
                        $result = $DB->query($query);

                        $ret = $pmServiceevent->getData(
                                $result,
                                $pmComponent->fields['graph_template'],
                                $a_date['begin'],
                                $a_date['end']);
                        foreach ($data4 as $a_perfdatadetails) {
                           $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
                           $nb_val += count($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                           $val += array_sum($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                           $a_val = array_merge($a_val, $ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                        }
/*
                        // for manage warn, crit and limit
                        foreach ($a_tocheck as $other_type=>$num_type) {
                           if ($num_type == 1) {
                              $other_items = importArrayFromDB($this->fields['aggregate_'.$other_type]);
                              foreach ($other_items[$itemtype][$items_id]['PluginMonitoringComponent'][$items_id_components] as $a_perfdatadetails) {
                                 $pmPerfdataDetail->getFromDB($a_perfdatadetails['perfdatadetails_id']);
                                 $a_ret[$other_type] += array_sum($ret[0][$pmPerfdataDetail->fields['dsname'.$a_perfdatadetails['perfdatadetails_dsname']]]);
                              }
                           }
                        }
*/
                     }
                  }
               }
               break;

         }
      }
      if ($nb_val != 0) {
         if ($type == 'average') {
            $val = ($val / $nb_val);
         } else if ($type == 'median') {
            sort($a_val);
            $count = count($a_val); //total numbers in array
            $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
            if($count % 2) { // odd number, middle is the median
               $median = $a_val[$middleval];
            } else { // even number, calculate avg of 2 medians
               $low = $arr[$middleval];
               $high = $arr[$middleval+1];
               $median = (($low+$high)/2);
            }
            $val = $median;
         }
      }
/*
      foreach ($a_tocheck as $other_type=>$num_type) {
         if ($num_type == 1) {
            $a_ret[$other_type] = ($a_ret[$other_type] / $nb_val);
         }
      }
*/
      $a_ret['val'] = $val;
      return $a_ret;
   }


   // *********************************************************************//
   // ************************** Show widget ******************************//
   // *********************************************************************//



   function showWidget($id) {
      PluginMonitoringToolbox::loadLib();

      return "<div id=\"updateCustomitem_Counter".$id."\"></div>";
   }



   function showWidgetFrame($id) {
      global $DB, $CFG_GLPI;

      $this->getFromDB($id);
      if ($this->fields['type'] == 'average'
              || $this->fields['type'] == 'median') {
         $a_val = $this->type_other($this->fields['type']);
      } else {
         $a_val = $this->type_valueType($this->fields['type']);
      }

      $val = $a_val['val'];
      if (strstr($val, '.')) {
         $split = explode('.', $val);
         if (count($split[1]) > 2) {
            $val = round($val, 2);
         }
      }

      echo '<div class="ch-item">
         <div class="ch-info-counter">
			<h1><a href="';
         echo '<span id="devicea-'.$id.'">'.$this->getName().'</span></a></h1>
			<p><font style="font-size: 28px;">'.$val.'</font></p>
         </div>
		</div>';
/*
      echo "<script>
			var counters = [];

			function createCounter(name, label, min, max) {
				var config = {
					size: 198,
					label: label,
					min: undefined != min ? min : 0,
					max: undefined != max ? max : ".$a_val['limit'].",
					majorTicks: 11,
					minorTicks: 5
				}

				var range = config.max - config.min;
				config.greenZones = [{ from: config.min, to: config.min + range*".$warn_cnt." }];
				config.yellowZones = [{ from: config.min + range*".$warn_cnt.", to: config.min + range*".$crit_cnt." }];
				config.redZones = [{ from: config.min + range*".$crit_cnt.", to: config.max }];

				counters[name] = new Counter(name + 'CounterContainer', config);
				counters[name].render();
            counters[name].redraw(".$val.");
			}

		</script>
		<span id='updateCustomitem_Counter".$id."CounterContainer'></span>

      <script>createCounter('updateCustomitem_Counter".$id."', '".$this->fields['name']."');</script>";
*/
   }



   function ajaxLoad($id) {
      global $CFG_GLPI;

      $sess_id = session_id();
      PluginMonitoringSecurity::updateSession();

      echo "<script type=\"text/javascript\">

      var elcc".$id." = Ext.get(\"updateCustomitem_Counter".$id."\");
      var mgrcc".$id." = elcc".$id.".getUpdateManager();
      mgrcc".$id.".loadScripts=true;
      mgrcc".$id.".showLoadIndicator=false;
      mgrcc".$id.".startAutoRefresh(50, \"".$CFG_GLPI["root_doc"].
              "/plugins/monitoring/ajax/updateWidgetCustomitem_Counter.php\","
              . " \"id=".$id."&sess_id=".$sess_id.
              "&glpiID=".$_SESSION['glpiID'].
              "&plugin_monitoring_securekey=".$_SESSION['plugin_monitoring_securekey'].
              "\", \"\", true);
      </script>";
   }



   function showDefineDataOfCounter($components_id, $a_path=array(), $deletebutton=FALSE) {

      if (!isset($this->fields)
              || !isset($this->fields['aggregate_items'])
              || $this->fields['aggregate_items'] == '') {
         $this->getEmpty();
      }

      $pmComponent = new PluginMonitoringComponent();
      $pmComponent->getFromDB($components_id);

      $perfdetail = getAllDatasFromTable(
              'glpi_plugin_monitoring_perfdatadetails',
              "`plugin_monitoring_perfdatas_id`='".$pmComponent->fields['graph_template']."'");
      $elements = array();
      foreach ($perfdetail as $perfdata) {
         for ($i=1; $i <= 15; $i++) {
            if ($perfdata['dsname'.$i] != '') {
               $elements[$perfdata['id']."/".$i] = $perfdata['dsname'.$i];
            }
         }
      }

      echo "<table>";
      echo "<tr>";
      echo "<td>";
      echo __('Value', 'monitoring');
      echo " : </td>";
      echo "<td>";
      $value = '';
      if ($this->fields['aggregate_items'] != '') {
         $aggregate_items = importArrayFromDB($this->fields['aggregate_items']);
         $value = $aggregate_items[$a_path['a']]["id".$a_path['b']][$a_path['c']]["id".$a_path['d']][$a_path['num']]['perfdatadetails_id'].
              '/'.$aggregate_items[$a_path['a']]["id".$a_path['b']][$a_path['c']]["id".$a_path['d']][$a_path['num']]['perfdatadetails_dsname'];
      }
      Dropdown::showFromArray(
              'item',
              $elements,
              array('value' => $value));
      echo "</td>";
      if ($deletebutton) {
         echo "<td rowspan='4'>";
         echo "<form name='form2' method='post' action=''>";

         echo "<input type='hidden' name='id' value='".$a_path['id']."' />";
         echo "<input type='hidden' name='delete_item' value='".
                 $a_path['a']."|id".$a_path['b']."|".$a_path['c']."|id".$a_path['d']."|".$a_path['num']."' />";
         echo "<input type='submit' class='submit' name='delete' value='".
                 _sx('button', 'Delete permanently')."' />";
         Html::closeForm();
         echo "</td>";
      }
      echo "</tr>";

      echo "</table>";
   }



   function deleteCounterItems($array) {
      $this->getFromDB($array['id']);

      $aggregate_items = importArrayFromDB($this->fields['aggregate_items']);
      $split = explode('|', $array['delete_item']);
      if (count($split) == 5) {
         unset($aggregate_items[$split[0]][$split[1]][$split[2]][$split[3]][$split[4]]);
         if (count($aggregate_items[$split[0]][$split[1]][$split[2]][$split[3]]) == 0) {
            unset($aggregate_items[$split[0]][$split[1]][$split[2]][$split[3]]);
            if (count($aggregate_items[$split[0]][$split[1]][$split[2]]) == 0) {
               unset($aggregate_items[$split[0]][$split[1]][$split[2]]);
               if (count($aggregate_items[$split[0]][$split[1]]) == 0) {
                  unset($aggregate_items[$split[0]][$split[1]]);
                  if (count($aggregate_items[$split[0]]) == 0) {
                     unset($aggregate_items[$split[0]]);
                  }
               }
            }
         }
      }
      $input = array(
          'id' => $array['id'],
          'aggregate_items' => exportArrayToDB($aggregate_items)
      );
      $this->update($input);
   }
}

?>