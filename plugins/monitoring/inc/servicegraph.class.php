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

class PluginMonitoringServicegraph {
   private $jsongraph_a_ref = array();
   private $jsongraph_a_convert = array();


   function displayCounter($rrdtool_template, $items_id, $json=0, $counter_id='', $counter_name='') {
      global $CFG_GLPI;

      // Toolbox::logInFile("pm", "displayCounter : $rrdtool_template, $items_id\n");
      $pmComponent = new PluginMonitoringComponent();
      $item = new PluginMonitoringService();
      if (! $item->getFromDB($items_id)) return '';

      $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
      $ret = '<div class="counter" id="counters_'.$counter_id.'_'.$items_id.'">'.$counter_id.'</div>';

      $sess_id = session_id();
      PluginMonitoringSecurity::updateSession();

      $ret .= "<script>
         // window.setInterval(function () {
            Ext.Ajax.request({
               url: '". $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updatePerfdata.php" ."',
               params: {";
      // define 'debug' parameter to add debug data in server response
      // $ret .= "   'debug': 'debug',";
      // define 'json' parameter to get a json server response
      $ret .= "   'json': '$json',";
      $ret .= "   'rrdtool_template': '$rrdtool_template',
                  'counter_id': '$counter_id',
                  'counter_name': '$counter_name',
                  'items_id': '$items_id',
                  'components_id': '". $item->fields['plugin_monitoring_components_id'] ."',
                  'sess_id': '".$sess_id."',
                  'glpiID': '".$_SESSION['glpiID']."',
                  'plugin_monitoring_securekey': '".$_SESSION['plugin_monitoring_securekey']."'
               },
               success: function(response)  {
                  document.getElementById('counters_".$counter_id.'_'.$items_id."').innerHTML = response.responseText;
                }
            });
         // } , 5000);
      </script>";

      return $ret;
   }


   function displayGraph($rrdtool_template, $itemtype, $items_id, $timezone, $time='1d', $part='', $width='900') {
      global $CFG_GLPI;

      $pmComponent = new PluginMonitoringComponent();
//      if (isset($_GET['itemtype'])) {
//         $itemtype = $_GET['itemtype'];
//         $items_id = $_GET['items_id'];
//      }
      $item = new $itemtype();
      if ($item->getFromDB($items_id)) {
         $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
         $ident = $items_id.$time;

         if ($part == ''
                 OR $part == 'div') {
            echo '<div id="chart'.$ident.'">'.
                '<svg style="width: '.$width.'px;display: block;"></svg>'.
              '</div>';

            echo "<div id=\"updategraph".$items_id.$time."\"></div>";
         }
         if ($part == ''
                 OR $part == 'js') {
            if (!isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->fields['id']])) {
               PluginMonitoringToolbox::loadPreferences($pmComponent->fields['id']);
            }
            if (! isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->fields['id']])) {
               $format = '%H:%M';
            } else {
               if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->fields['id']][''])) {
                  unset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->fields['id']]['']);
               }
               $pmServicegraph = new PluginMonitoringServicegraph();
               $a_ret = $pmServicegraph->generateData($rrdtool_template,
                                            $itemtype,
                                            $items_id,
                                            $timezone,
                                            $time,
                                            '',
                                            $_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->fields['id']]);
               $format = $a_ret[2];
            }
            echo "<script>";
            $formaty = ".0f";
            echo '

            var data'.$ident.' = [];
            var chart'.$ident.';
            redraw'.$ident.' = function () {
               nv.addGraph(function() {
                  chart'.$ident.' = nv.models.lineChart();

                  chart'.$ident.'.useInteractiveGuideline(true);

                  chart'.$ident.'.xAxis
                     .tickFormat(function(d) { return d3.time.format("'.$format.'")(new Date(d)); });

                  chart'.$ident.'.yAxis
                     .axisLabel("test")
                     .tickFormat(d3.format(\''.$formaty.'\'));

                  chart'.$ident.'.forceY([0]);

                  d3.select("#chart'.$ident.' svg")
                    .attr("height", 300)
                    .datum(data'.$ident.')
                    .transition().duration(50)
                    .call(chart'.$ident.');



                 return chart'.$ident.';
               });
            };';

            echo "
            (function worker".$items_id.$time."() {
              startDate = new Date($('#custom_date').val());
              startTime = Date.parse('04/03/1980 ' + ($('#custom_time').val()) + ':00');
              $.getJSON('".$CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updateChart.php"
                    ."?rrdtool_template=".$rrdtool_template."&itemtype=".$itemtype.
                          "&items_id=".$items_id.
                          "&timezone=".$timezone.
                          "&time=".$time."&customdate=' + (startDate.getTime()/1000.0) + '".
                          "&customtime=' + (startTime/1000.0) + '".
                          "&components_id=".$pmComponent->fields['id']."', function(data) {
                data".$ident." = data;

                redraw".$ident."();
                setTimeout(worker".$items_id.$time.", 30000);
              });
            })();";
            echo "
            </script>";
         }

                 }
      return;
   }



   function startAutoRefresh($rrdtool_template, $itemtype, $items_id, $timezone, $time, $pmComponents_id) {
      global $CFG_GLPI;

      $sess_id = session_id();
      PluginMonitoringSecurity::updateSession();
      $refresh = "50"; // all 50 seconds
      if ($time == '1d') {
         $refresh = "300"; // 5 minutes
      } else if ($time == '1w'
              || $time == '1m'
              || $time == '0y6m'
              || $time == '1y') {
         $refresh = "1000";
      }

      echo "mgr".$items_id.$time.".startAutoRefresh(".$refresh.", \"".$CFG_GLPI["root_doc"].
                 "/plugins/monitoring/ajax/updateChart.php\", ".
                 "\"rrdtool_template=".$rrdtool_template.
                 "&itemtype=".$itemtype.
                 "&items_id=".$items_id.
                 "&timezone=".$timezone.
                 "&time=".$time.
                 "&customdate=\" + document.getElementById('custom_date').textContent + \"".
                 "&customtime=\" + document.getElementById('custom_time').textContent + \"".
                 "&components_id=".$pmComponents_id."&sess_id=".$sess_id.
                 "&glpiID=".$_SESSION['glpiID'].
//                 "&plugin_monitoring_securekey=".$_SESSION['plugin_monitoring_securekey'].
                 "\", \"\", true);
                    ";
   }



   function generateData($rrdtool_template, $itemtype, $items_id, $timezone, $time, $enddate='', $todisplay=array()) {
      global $DB;

      if ($enddate == '') {
         $enddate = date('U');
      }

      // Manage timezones
      $converttimezone = '0';
      if (strstr($timezone, '-')) {
         $timezone_temp = str_replace("-", "", $timezone);
         $converttimezone = ($timezone_temp * 3600);
         $timezone = str_replace("-", "+", $timezone);
      } else if (strstr($timezone, '+')) {
         $timezone_temp = str_replace("+", "", $timezone);
         $converttimezone = ($timezone_temp * 3600);
         $timezone = str_replace("+", "-", $timezone);
      }

      // ** Get in table serviceevents
      $mydatat = array();
      $a_labels = array();
      $a_ref = array();
      $pmServiceevent = new PluginMonitoringServiceevent();
      $pmService = new PluginMonitoringService();
      $pmService->getFromDB($items_id);

      $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmService->fields['plugin_monitoring_components_id']);

      $dateformat = "%Y-%m-%d %Hh";

      $begin = '';
      switch ($time) {

         case '2h':
            $begin = date('Y-m-d H:i:s', $enddate - (2 * 3600));
            $timecomplete = 0;
            $dateformat = "(%d)%H:%M";
            if (date('m', $enddate) != date('m', $enddate - (2 * 3600))) {
               $timecomplete = 2;
               $dateformat = "%m-%d %H:%M";
            }

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = array();
            if (isset($this->jsongraph_a_ref[$rrdtool_template])) {
               $ret = $pmServiceevent->getData(
                       $result,
                       $rrdtool_template,
                       $begin,
                       date('Y-m-d H:i:s', $enddate),
                       array($this->jsongraph_a_ref[$rrdtool_template],
                             $this->jsongraph_a_convert[$rrdtool_template]),
                       $timecomplete,
                       $todisplay);
            } else {
               $ret = $pmServiceevent->getData(
                       $result,
                       $rrdtool_template,
                       $begin,
                       date('Y-m-d H:i:s', $enddate),
                       array(),
                       $timecomplete,
                       $todisplay);
            }
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];
               if (!isset($this->jsongraph_a_ref[$rrdtool_template])) {
                  $this->jsongraph_a_ref[$rrdtool_template] = $ret[2];
                  $this->jsongraph_a_convert[$rrdtool_template] = $ret[3];
               }
            }
            break;

         case '12h':
            $begin = date('Y-m-d H:i:s', $enddate - (12 * 3600));
            $timecomplete = 0;
            $dateformat = "(%d)%H:%M";
            if (date('m', $enddate) != date('m', $enddate - (12 * 3600))) {
               $timecomplete = 2;
               $dateformat = "%m-%d %H:%M";
            }

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    $timecomplete,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];
            }
            break;

         case '1d':
            $begin = date('Y-m-d H:i:s', $enddate - (24 * 3600));
            $timecomplete = 0;
            $dateformat = "(%d)%H:%M";
            if (date('m', $enddate) != date('m', $enddate - (24 * 3600))) {
               $timecomplete = 2;
               $dateformat = "%m-%d %H:%M";
            }

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    $timecomplete,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];
            }
            break;

         case '1w':
            $begin = date('Y-m-d H:i:s', $enddate - (7 * 24 * 3600));
            $dateformat = "%Y-%m-%d %H:%M";

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    TRUE,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];

               $nb_val = count($a_labels);
               // May have 22 points in the graph
               $nb_val_period = $nb_val / 75;
               $mydatatNew = array();
               foreach ($mydatat as $name=>$data) {
                  $nb = 1;
                  $val = 0;
                  foreach ($data as $value) {
                     $val += $value;
                     $nb++;
                     if ($nb > $nb_val_period) {
                        $mydatatNew[$name][] = round($val / ($nb - 1), 2);
                        $nb = 1;
                        $val = 0;
                     }
                  }
                  if ($nb > 1
                          && $nb <= $nb_val_period) {

                        $mydatatNew[$name][] = round($val / ($nb - 1), 2);
                  }
               }
               $mydatat = $mydatatNew;
               $a_labelsNew = array();
               $nb = 1;
               $val = 0;
               foreach ($a_labels as $value) {
                  if ($nb == 1) {
                     $a_labelsNew[] = $value;
                  }
                  $nb++;
                  if ($nb > $nb_val_period) {
                     $nb = 1;
                  }
               }
               $a_labels = $a_labelsNew;
            }
            break;

         case '1m':
            $begin = date('Y-m-d H:i:s', $enddate - (30 * 24 * 3600));
            $dateformat = "%Y-%m-%d %H:%M";

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    TRUE,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];

               $nb_val = count($a_labels);
               // May have 22 points in the graph
               $nb_val_period = $nb_val / 75;
               $mydatatNew = array();
               foreach ($mydatat as $name=>$data) {
                  $nb = 1;
                  $val = 0;
                  foreach ($data as $value) {
                     $val += $value;
                     $nb++;
                     if ($nb > $nb_val_period) {
                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                        $nb = 1;
                        $val = 0;
                     }
                  }
                  if ($nb > 1
                          && $nb <= $nb_val_period) {

                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                  }
               }
               $mydatat = $mydatatNew;

               $a_labelsNew = array();
               $nb = 1;
               $val = 0;
               foreach ($a_labels as $value) {
                  if ($nb == 1) {
                     $a_labelsNew[] = $value;
                  }
                  $nb++;
                  if ($nb > $nb_val_period) {
                     $nb = 1;
                  }
               }
               $a_labels = $a_labelsNew;
            }
            break;

         case '0y6m':
            $begin = date('Y-m-d H:i:s', date('U') - ((364 / 2) * 24 * 3600));
            $dateformat = "%Y-%m-%d %H:%M";

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    TRUE,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];

               $nb_val = count($a_labels);
               // May have 22 points in the graph
               $nb_val_period = $nb_val / 75;
               $mydatatNew = array();
               foreach ($mydatat as $name=>$data) {
                  $nb = 1;
                  $val = 0;
                  foreach ($data as $value) {
                     $val += $value;
                     $nb++;
                     if ($nb > $nb_val_period) {
                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                        $nb = 1;
                        $val = 0;
                     }
                  }
                  if ($nb > 1
                          && $nb <= $nb_val_period) {

                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                  }
               }
               $mydatat = $mydatatNew;

               $a_labelsNew = array();
               $nb = 1;
               $val = 0;
               foreach ($a_labels as $value) {
                  if ($nb == 1) {
                     $a_labelsNew[] = $value;
                  }
                  $nb++;
                  if ($nb > $nb_val_period) {
                     $nb = 1;
                  }
               }
               $a_labels = $a_labelsNew;
            }
            break;

         case '1y':
            $begin = date('Y-m-d H:i:s', date('U') - (365 * 24 * 3600));
            $dateformat = "%Y-%m-%d %H:%M";

            $query = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
               WHERE `plugin_monitoring_services_id`='".$items_id."'
                  AND `date` > '".$begin."'
                  AND `date` <= '".date('Y-m-d H:i:s', $enddate)."'
               ORDER BY `date`";
            $result = $DB->query($query);
            $ret = $pmServiceevent->getData(
                    $result,
                    $rrdtool_template,
                    $begin,
                    date('Y-m-d H:i:s', $enddate),
                    array(),
                    TRUE,
                    $todisplay);
            if (is_array($ret)) {
               $mydatat  = $ret[0];
               $a_labels = $ret[1];
               $a_ref    = $ret[2];

               $nb_val = count($a_labels);
               // May have 22 points in the graph
               $nb_val_period = $nb_val / 75;
               $mydatatNew = array();
               foreach ($mydatat as $name=>$data) {
                  $nb = 1;
                  $val = 0;
                  foreach ($data as $value) {
                     $val += $value;
                     $nb++;
                     if ($nb > $nb_val_period) {
                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                        $nb = 1;
                        $val = 0;
                     }
                  }
                  if ($nb > 1
                          && $nb <= $nb_val_period) {

                        $mydatatNew[$name][] = ceil($val / ($nb - 1));
                  }
               }
               $mydatat = $mydatatNew;

               $a_labelsNew = array();
               $nb = 1;
               $val = 0;
               foreach ($a_labels as $value) {
                  if ($nb == 1) {
                     $a_labelsNew[] = $value;
                  }
                  $nb++;
                  if ($nb > $nb_val_period) {
                     $nb = 1;
                  }
               }
               $a_labels = $a_labelsNew;
            }
            break;
      }
      return array($mydatat, $a_labels, $dateformat);
   }



   static function getperfdataNames($rrdtool_template,$keepwarcrit=1) {

      $a_name = array();
      if ($rrdtool_template == 0) {
         return $a_name;
      }

      $a_perf = PluginMonitoringPerfdata::getArrayPerfdata($rrdtool_template);

      foreach ($a_perf['parseperfdata'] as $data) {
         foreach ($data['DS'] as $data2) {
            if ($keepwarcrit == 0) {
               if (!strstr($data2['dsname'], "warning")
                       && !strstr($data2['dsname'], "critical")) {
                  $a_name[] = $data2['dsname'];
               }
            } else {
               $a_name[] = $data2['dsname'];
            }
         }
      }
      return $a_name;
   }



   static function colors($type='normal') {
      $a_colors = array();
      switch ($type) {
         case 'normal':
            $a_colors["006600"] = "006600";
            $a_colors["009900"] = "009900";
            $a_colors["67cb33"] = "67cb33";
            $a_colors["9afe66"] = "9afe66";

            $a_colors["003399"] = "003399";
            $a_colors["0066cb"] = "0066cb";
            $a_colors["0099ff"] = "0099ff";
            $a_colors["99cdff"] = "99cdff";

            $a_colors["6c6024"] = "6c6024";
            $a_colors["a39136"] = "a39136";
            $a_colors["d3c57e"] = "d3c57e";

            $a_colors["66246c"] = "66246c";
            $a_colors["9a36a3"] = "9a36a3";
            $a_colors["cd7ed3"] = "cd7ed3";
            $a_colors["eacaed"] = "eacaed";

            break;

         case 'warn':
            $a_colors["eacc00"] = "eacc00";
            $a_colors["ea8f00"] = "ea8f00";
            $a_colors["ea991a"] = "ea991a";

            break;

         case 'crit':
            $a_colors["ff0000"] = "ff0000";
            $a_colors["a00000"] = "a00000";
            $a_colors["720000"] = "720000";

            break;
      }
      return $a_colors;
   }

}

?>