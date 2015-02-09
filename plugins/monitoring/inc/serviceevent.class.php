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

class PluginMonitoringServiceevent extends CommonDBTM {

   static $rightname = 'plugin_monitoring_service';

   static function getTypeName($nb=0) {
      return __CLASS__;
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType() == 'Computer'){
         if (self::canView()) {
            return __('Service events', 'monitoring');
         }
      }

      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Computer') {
         if (self::canView()) {
            // Show list filtered on computer, sorted on day descending ...
            $_GET = array(
               'field' => array(22),
               'searchtype' => array('equals'),
               'contains' => array($item->getID()),
               'itemtype' => 'PluginMonitoringServiceevent',
               'start' => 0,
               'sort' => 3,
               'order' => 'DESC');
            Search::manageGetValues(self::getTypeName());
            Search::showList(self::getTypeName(), $_GET);
            return true;
         }
      }
      return true;
   }


   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Service events', 'monitoring');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'id';
      $tab[1]['linkfield']       = 'id';
      $tab[1]['name']            = __('ID');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false; // implicit field is id

      $tab[2]['table']           = 'glpi_plugin_monitoring_services';
      $tab[2]['field']           = 'name';
      $tab[2]['linkfield']       = 'plugin_monitoring_services_id';
      $tab[2]['name']            = __('Service instance', 'monitoring');
      $tab[2]['datatype']        = 'itemlink';

      $items_joinparams = array(
          'beforejoin' => array('table' => 'glpi_plugin_monitoring_services'));

      $tab[21]['table']         = 'glpi_plugin_monitoring_components';
      $tab[21]['field']         = 'name';
      $tab[21]['name']          = __('Component', 'monitoring');
      $tab[21]['datatype']      = 'itemlink';
      $tab[21]['joinparams']    = $items_joinparams;

      $tab[22]['table']         = 'glpi_computers';
      $tab[22]['field']         = 'name';
      $tab[22]['linkfield']     = 'items_id';
      $tab[22]['name']          = __('Computer');
      $tab[22]['datatype']      = 'itemlink';
      $tab[22]['itemlink_type'] = 'Computer';
      $tab[22]['joinparams']      = array(
          'condition'  => " AND REFTABLE.itemtype='Computer' ",
          'beforejoin' => array('table'      => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                                'joinparams' => $items_joinparams));

      $tab[23]['table']         = 'glpi_networkequipments';
      $tab[23]['field']         = 'name';
      $tab[23]['linkfield']     = 'items_id';
      $tab[23]['name']          = _n('Network device', 'Network devices', 1);
      $tab[23]['datatype']      = 'itemlink';
      $tab[23]['itemlink_type'] = 'NetworkEquipment';
      $tab[23]['joinparams']      = array(
          'condition'  => " AND REFTABLE.itemtype='NetworkEquipment' ",
          'beforejoin' => array('table'      => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                                'joinparams' => $items_joinparams));

      $tab[24]['table']         = 'glpi_printers';
      $tab[24]['field']         = 'name';
      $tab[24]['linkfield']     = 'items_id';
      $tab[24]['name']          = __('Printer');
      $tab[24]['datatype']      = 'itemlink';
      $tab[24]['itemlink_type'] = 'Printer';
      $tab[24]['joinparams']      = array(
          'condition'  => " AND REFTABLE.itemtype='Printer' ",
          'beforejoin' => array('table'      => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                                'joinparams' => $items_joinparams));

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'date';
      $tab[3]['name']            = __('Date', 'monitoring');
      $tab[3]['datatype']        = 'datetime';
      $tab[3]['massiveaction']   = false;

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'event';
      $tab[4]['name']            = __('Event output', 'monitoring');
      $tab[4]['massiveaction']   = false;

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'perf_data';
      $tab[5]['name']            = __('Event performance data', 'monitoring');
      $tab[5]['massiveaction']   = false;

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'state';
      $tab[6]['name']            = __('Service state', 'monitoring');
      $tab[6]['massiveaction']   = false;

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'state_type';
      $tab[7]['name']            = __('Service state type', 'monitoring');
      $tab[7]['massiveaction']   = false;

      return $tab;
   }


   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'plugin_monitoring_services_id':
            $pmService = new PluginMonitoringService();
            $pmService->getFromDB($values[$field]);
            return $pmService->getLink();
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   static function convert_datetime_timestamp($str) {

      list($date, $time) = explode(' ', $str);
      list($year, $month, $day) = explode('-', $date);
      list($hour, $minute, $second) = explode(':', $time);

      $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

      return $timestamp;
   }



   function calculateUptime($hosts_id, $startDate, $endDate) {
      $a_list = $this->find("`plugin_monitoring_hosts_id`='".$hosts_id."'
         AND `date` > '".date("Y-m-d H:i:s", $startDate)."'
         AND `date` < '".date("Y-m-d H:i:s", $endDate)."'", "date");

      $a_list_before = $this->find("`plugin_monitoring_hosts_id`='".$hosts_id."'
         AND `date` < '".date("Y-m-d H:i:s", $startDate)."'", "date DESC", 1);

      $state_before = '';
      if (count($a_list_before) == '0') {
         $state_before = 'OK';
      } else {
         $datat = current($a_list_before);
         if (strstr($datat['event'], ' OK -')) {
            $state_before = 'OK';
         } else {
            $state_before = 'CRITICAL';
         }
      }

      $count = array();
      $count['critical'] = 0;
      $count['ok'] = 0;
      $last_datetime= date("Y-m-d H:i:s", $startDate);

      foreach($a_list as $data) {
         if (strstr($data['event'], ' OK -')) {
            if ($state_before == "OK") {
               $count['ok'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
            } else {
               $count['critical'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
            }
            $state_before = '';
         } else {
            if ($state_before == "CRITICAL") {
               $count['critical'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
            } else {
               $count['ok'] += $this->convert_datetime_timestamp($data['date']) -
                       $this->convert_datetime_timestamp($last_datetime);
            }
            $state_before = '';
         }
         $last_datetime = $data['date'];

      }
      if (!isset($data['event']) OR strstr($data['event'], ' OK -')) {
         $count['ok'] += date('U') - $this->convert_datetime_timestamp($last_datetime);
      } else {
         $count['critical'] += date('U') - $this->convert_datetime_timestamp($last_datetime);
      }
      $total = $count['ok'] + $count['critical'];
      return array('ok_t'      => $count['ok']." seconds",
                   'critical_t'=> $count['critical']." seconds",
                   'ok_p'      => round(($count['ok'] * 100) / $total, 3),
                   'critical_p'=> round(($count['critical'] * 100) / $total, 3));

   }



   function getSpecificData($perfdatas_id, $items_id, $which='last', $state="AND `state` = 'OK'") {
      global $DB;

      // ** Get in table serviceevents
      $mydatat = array();
      $a_labels = array();
      $a_ref = array();
      $pmService = new PluginMonitoringService();
      $pmService->getFromDB($items_id);

      $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);

      $enddate = date('U');
      $counters = array();

      switch ($which) {
         case 'first':
            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `perf_data` != ''
                  ".$state."
               ORDER BY `date` ASC
               LIMIT 1";
            break;

         case 'last':
            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `perf_data` != ''
                  ".$state."
               ORDER BY `date` DESC
               LIMIT 1";
            break;

         default:
            return $counters;
            break;
      }

      $resultevent = $DB->query($query);
      $dataevent = $DB->fetch_assoc($resultevent);
      $result = $DB->query($query);

      $ret = $this->getData(
              $result,
              $perfdatas_id,
              $dataevent['date'],
              $dataevent['date']);
/*
      if (is_array($ret)) {
         foreach ($ret[0] as $name=>$data) {
            $counters[$name] = $data[0];
         }
      }
*/
      if (is_array($ret) && is_array($ret[0]) && is_array($ret[4])) {
         foreach ($ret[4] as $name=>$data) {
            // Toolbox::logInFile("pm", "$name -> $data = ".$ret[0][$data][0]."\n");
            $counter = array();
            $counter['id'] = preg_replace("/[^A-Za-z0-9\-_]/","",$name);
            $counter['name'] = $data;
            $counter['value'] = $ret[0][$data][0];
            $counters[] = $counter;
         }
      }

      return $counters;
   }



   function getData($result, $perfdatas_id, $start_date, $end_date, $ret=array(), $timecomplete=0, $todisplay=array()) {
      global $DB;

      // Toolbox::logInFile("pm", "serviceevent, getData : $perfdatas_id, from $start_date to $end_date\n");
      if (empty($ret)) {
         $ret = $this->getRef($perfdatas_id);
      }
      $a_ref = $ret[0];
      $a_convert = $ret[1];

      $mydatat = array();
      $a_labels = array();
      $a_perfdata_name = array();

      $a_perf = PluginMonitoringPerfdata::getArrayPerfdata($perfdatas_id);
      $previous_timestamp = strtotime($start_date);
      $query_data = array();
      $cnt = 0;
      if (gettype($result) == 'object') {
         while ($edata=$DB->fetch_array($result)) {

            $current_timestamp = strtotime($edata['date']);
            $cnt++;

            // Timeup = time between 2 checks + 20%
            $timeup = $_SESSION['plugin_monitoring_checkinterval'] * 1.2;
            while (($previous_timestamp + $timeup) < $current_timestamp) {
               $previous_timestamp += $_SESSION['plugin_monitoring_checkinterval'];
               if ($previous_timestamp < $current_timestamp) {
                  $query_data[] = array(
                      'date'      => date('Y-m-d H:i:s', $previous_timestamp),
                      'perf_data' => ''
                  );
               }
            }
            $previous_timestamp = $current_timestamp;
            $query_data[] = $edata;
         }
      } else {
         foreach ($result as $edata) {
            // Toolbox::logInFile("pm", "serviceevent, getData : ".$edata['id']."\n");

            $current_timestamp = strtotime($edata['date']);
            $cnt++;

            // Timeup = time between 2 checks + 20%
            $timeup = $_SESSION['plugin_monitoring_checkinterval'] * 1.2;
            while (($previous_timestamp + $timeup) < $current_timestamp) {
               $previous_timestamp += $_SESSION['plugin_monitoring_checkinterval'];
               if ($previous_timestamp < $current_timestamp) {
                  $query_data[] = array(
                      'date'      => date('Y-m-d H:i:s', $previous_timestamp),
                      'perf_data' => ''
                  );
               }
            }
            $previous_timestamp = $current_timestamp;
            $query_data[] = $edata;
         }
      }

      $timeup = $_SESSION['plugin_monitoring_checkinterval'] * 1.2;
      $current_timestamp = strtotime($end_date);
      while (($previous_timestamp + $timeup) < $current_timestamp) {
         $previous_timestamp += $_SESSION['plugin_monitoring_checkinterval'];
         if ($previous_timestamp < $current_timestamp) {
            $query_data[] = array(
                'date'      => date('Y-m-d H:i:s', $previous_timestamp),
                'perf_data' => ''
            );
         }
      }
      foreach ($query_data as $edata) {
         $current_timestamp = strtotime($edata['date']);
         if ('' == $previous_timestamp) {
            $previous_timestamp = $current_timestamp;
         }
         $a_perfdata = PluginMonitoringPerfdata::splitPerfdata($edata['perf_data']);
         $a_labels[] = $current_timestamp;
//         $a_time = explode(" ", $edata['date']);
//         $a_time2 = explode(":", $a_time[1]);
//         if ($timecomplete == 1) {
//            $a_labels[] = $a_time[0]." ".$a_time2[0].":".$a_time2[1];
//         } else {
//            $day = explode("-", $a_time[0]);
//            if ($timecomplete == 2) {
//               $a_labels[] = $day[1]."-".$day[2]." ".$a_time2[0].":".$a_time2[1];
//            } else {
//               $a_labels[] = "(".$day[2].")".$a_time2[0].":".$a_time2[1];
//            }
//         }
         foreach ($a_perf['parseperfdata'] as $num=>$data) {

            if (isset($a_perfdata[$num])) {
               $a_perfdata[$num] = trim($a_perfdata[$num], ", ");
               $a_a_perfdata = explode("=", $a_perfdata[$num]);
               $a_a_perfdata[0] = trim($a_a_perfdata[0], "'");
               $regex = 0;
               if (strstr($data['name'], "*")) {
                  $datanameregex = str_replace("*", "(.*)", $data['name']);
                  $regex = 1;
               }
               if (($a_a_perfdata[0] == $data['name']
                       OR '' == $data['name']
                       OR ($regex == 1
                               AND preg_match("/".$datanameregex."/", $data['name']))
                    )
                       AND isset($a_a_perfdata[1])) {

                  $a_perfdata_final = explode(";", $a_a_perfdata[1]);
                  // New perfdata row, no unit knew.
                  $unity = '';
                  foreach ($a_perfdata_final as $nb_val=>$val) {
                     if (count($todisplay) == 0
                             || isset($todisplay[$data['DS'][$nb_val]['dsname']])) {

                        //No value, no graph
                        if ('' == $val) {
                           if ($nb_val >=(count($a_perfdata_final) - 1)) {
                              continue;
                           } else {
                              $val = 0;
                           }
                        }
                        $toreplace = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ".");
                        $not_number = str_replace($toreplace, '', $val);
                        if ($not_number) {
                           $unity = $not_number;
                        }

                        //Maintain for a same perfdata row, unity data. If set it's normally a new perfdata row.
                        if ($unity == '') {
                           $val = round(($val + 0), 2);
                        } else {
                           $val = str_replace($unity, '', $val);

                           switch ($unity) {
                              case 'ms':
                              case 'bps':
                              case 'B' :
                              case "Bits/s" :
                                 $val = round($val, 0);
                                 break;
                              case '%' :
                                 $val = round($val, 2);
                                 break;
                              case 'KB' :
                                 $val = $val * 1000; // Have in B
                                 break;
                              case 'MB' :
                                 $val = $val * 1000000; // Have in B
                                 break;
                              case 'TB':
                                 $val = $val * 1000000000; // Have in B
                                 break;
                              case 's' :
                                 $val = round($val * 1000, 0);
                                 break;
                              case 'timeout' :
                                 if ($val > 2) {
                                    $val = round($val);
                                 } else {
                                    $val = round($val, 2);
                                 }
                                 break;
                              default :
                                 $val = round(($val + 0), 2);

                           }
                        }
                        $a_perfdata_name[$data['name']] = $data['DS'][$nb_val]['dsname'];

                        if (!isset($mydatat[$data['DS'][$nb_val]['dsname']])) {
                           $mydatat[$data['DS'][$nb_val]['dsname']] = array();
                        }
                        array_push($mydatat[$data['DS'][$nb_val]['dsname']], $val);
                        if ($data['incremental'][$nb_val] == 1) {
                           if (!isset($mydatat[$data['DS'][$nb_val]['dsname']." | diff"])) {
                              $mydatat[$data['DS'][$nb_val]['dsname']." | diff"] = array();
                           }
                           array_push($mydatat[$data['DS'][$nb_val]['dsname']." | diff"], $val);
                        }
                     }
                  }
               } else {
                  $nb_DS = count($data['DS']);
                  for ($nb_val=0; $nb_val < $nb_DS; $nb_val++) {
                     if (count($todisplay) == 0
                          || isset($todisplay[$data['DS'][$nb_val]['dsname']])) {
                        $a_perfdata_name[$data['name']] = $data['DS'][$nb_val]['dsname'];

                        if (!isset($mydatat[$data['DS'][$nb_val]['dsname']])) {
                           $mydatat[$data['DS'][$nb_val]['dsname']] = array();
                        }
                        array_push($mydatat[$data['DS'][$nb_val]['dsname']], 0);
                        if ($data['incremental'][$nb_val] == 1) {
                           if (!isset($mydatat[$data['DS'][$nb_val]['dsname']." | diff"])) {
                              $mydatat[$data['DS'][$nb_val]['dsname']." | diff"] = array();
                           }
                           array_push($mydatat[$data['DS'][$nb_val]['dsname']." | diff"], 0);
                        }
                     }
                  }
               }
            } else {
               $nb_DS = count($data['DS']);
               for ($nb_val=0; $nb_val < $nb_DS; $nb_val++) {
                  if (count($todisplay) == 0
                       || isset($todisplay[$data['DS'][$nb_val]['dsname']])) {

                     $a_perfdata_name[$data['name']] = $data['DS'][$nb_val]['dsname'];

                     if (!isset($mydatat[$data['DS'][$nb_val]['dsname']])) {
                        $mydatat[$data['DS'][$nb_val]['dsname']] = array();
                     }
                     array_push($mydatat[$data['DS'][$nb_val]['dsname']], 0);
                     if ($data['incremental'][$nb_val] == 1) {
                        if (!isset($mydatat[$data['DS'][$nb_val]['dsname']." | diff"])) {
                           $mydatat[$data['DS'][$nb_val]['dsname']." | diff"] = array();
                        }
                        array_push($mydatat[$data['DS'][$nb_val]['dsname']." | diff"], 0);
                     }
                  }
               }
            }
         }
      }

      $a_incremental = array();
      foreach ($a_perf['parseperfdata'] as $data) {
         foreach ($data['DS'] as $num=>$data1) {
            if ($data['incremental'][$num] == 1) {
               $a_incremental[$data1['dsname']] = 1;
            }
         }
      }

      foreach ($mydatat as $name=>$data) {
         if (strstr($name, " | diff")) {
            $old_val = -1;
            foreach ($data as $num=>$val) {
               if ($old_val == -1) {
                  $data[$num] = '###';
               } else if ($val < $old_val) {
                  $data[$num] = 0;
               } else {
                  $data[$num] = $val - $old_val;
               }
               if ($data[0] == '###') {
                  $data[0] = $data[$num];
               }
               $old_val = $val;
            }
            $mydatat[$name] = $data;
         } else if (isset($a_incremental[$name])) {
            $old_val = 0;
            foreach ($data as $num=>$val) {
               if ($val == 0) {
                  $data[$num] = $old_val;
               } else {
                  $old_val = $val;
               }
            }
            $mydatat[$name] = $data;
         }
      }

      $a_perfdata_name = array_unique($a_perfdata_name);
      // Toolbox::logInFile("pm", "a_perfdata_name : ".serialize($a_perfdata_name)."\n");
      // Toolbox::logInFile("pm", "mydatat : ".serialize($mydatat)."\n");
      return array($mydatat, $a_labels, $a_ref, $a_convert, $a_perfdata_name);
   }



   function getRef($perfdatas_id) {

      $a_convert = array();
      $a_ref = array();
      return array($a_ref, $a_convert);

      $a_perfg = PluginMonitoringPerfdata::getArrayPerfdata($perfdatas_id);
      // Get data
      $a_convert = array();
      $a_ref = array();
      foreach ($a_perfg['data'][0]['data'] as $data) {
         $data = str_replace("'", "", $data);
         if (strstr($data, "DEF")
                 AND !strstr($data, "CDEF")) {
            $a_explode = explode(":", $data);
            $a_name = explode("=", $a_explode[1]);
            if ($a_name[0] == 'outboundtmp') {
               $a_name[0] = 'outbound';
            }
            $a_convert[$a_name[0]] = $a_explode[2];
         }
         if (strstr($data, "AREA")) {
            $a_explode = explode(":", $data);
            $a_split = explode("#", $a_explode[1]);
            $a_ref[$a_convert[$a_split[0]]] = $a_split[1];
         }
      }
      return array($a_ref, $a_convert);
   }
}

?>
