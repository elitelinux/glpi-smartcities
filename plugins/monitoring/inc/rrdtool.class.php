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

class PluginMonitoringRrdtool extends CommonDBTM {

   function createGraph($rrdtool_template, $items_id, $timestamp) {

      $fname = GLPI_PLUGIN_DOC_DIR."/monitoring/PluginMonitoringService-".$items_id.".rrd";

      $a_filename = explode("-", $rrdtool_template);
      $filename = GLPI_PLUGIN_DOC_DIR."/monitoring/templates/".$a_filename[0]."-perfdata.json";
      if (!file_exists($filename)) {
         return;
      }
      $a_json = json_decode(file_get_contents($filename));

      $opts = '';
      $opts .= ' --start '.($timestamp - 300);
      $opts .= ' --step 300';

      foreach ($a_json->parseperfdata as $data) {
         foreach ($data->DS as $data_DS) {
            $opts .= ' DS:'.$data_DS->dsname.':'.$data_DS->format.':'.$data_DS->heartbeat.':'.$data_DS->min.':'.$data_DS->max;
         }
      }

      $opts .= " RRA:LAST:0.5:1:1400";
      $opts .= " RRA:AVERAGE:0.5:5:1016";

      system(PluginMonitoringConfig::getRRDPath().'/rrdtool create '.$fname.$opts, $ret);
      if (isset($ret)
              AND $ret != '0' ) {
         echo "Create error: $ret for ".PluginMonitoringConfig::getRRDPath()."/rrdtool create ".$fname.$opts."\n";
      }
   }



   function addData($rrdtool_template, $items_id, $timestamp, $perf_data) {

      $fname = GLPI_PLUGIN_DOC_DIR."/monitoring/PluginMonitoringService-".$items_id.".rrd";
      if (!file_exists($fname)) {
         $this->createGraph($rrdtool_template, $items_id, $timestamp);
      }

      $a_filename = explode("-", $rrdtool_template);
      $filename = GLPI_PLUGIN_DOC_DIR."/monitoring/templates/".$a_filename[0]."-perfdata.json";
      if (!file_exists($filename)) {
         return;
      }
      $a_json = json_decode(file_get_contents($filename));
      $a_perfdata = explode(" ", $perf_data);
      $rrdtool_value = $timestamp;
      foreach ($a_json->parseperfdata as $num=>$data) {
         if (isset($a_perfdata[$num])) {
            $a_a_perfdata = explode("=", $a_perfdata[$num]);
            if ($a_a_perfdata[0] == $data->name) {
               $a_perfdata_final = explode(";", $a_a_perfdata[1]);
               foreach ($a_perfdata_final as $nb_val=>$val) {
                  if ($val != '') {
                     if (strstr($val, "ms")) {
                        $val = round(str_replace("ms", "", $val),0);
                     } else if (strstr($val, "s")) {
                        $val = round((str_replace("s", "", $val) * 1000),0);
                     } else if (strstr($val, "%")) {
                        $val = round(str_replace("%", "", $val),0);
                     } else if (!strstr($val, "timeout")){
                        $val = round($val,2);
                     } else {
                        $val = $data->DS[$nb_val]->max;
                     }
                     $rrdtool_value .= ':'.$val;
                  }
               }
            } else {
               foreach ($data->DS as $nb_DS) {
                  $rrdtool_value .= ':U';
               }
            }
         } else {
            foreach ($data->DS as $nb_DS) {
               $rrdtool_value .= ':U';
            }
         }
      }
      //$ret = rrd_update($fname, $value);

      system(PluginMonitoringConfig::getRRDPath()."/rrdtool update ".$fname." ".$rrdtool_value, $ret);
      if (isset($ret)
              AND $ret != '0' ) {
         echo "Create error: $ret for ".PluginMonitoringConfig::getRRDPath()."/rrdtool update ".$fname." ".$rrdtool_value."\n";
      }
   }



   /**
    * Function used to generate gif of rrdtool graph
    *
    * @param type $itemtype
    * @param type $items_id
    * @param type $time
    */
   function displayGLPIGraph($rrdtool_template, $itemtype, $items_id, $timezone, $time='1d', $width='470') {

      $filename = GLPI_PLUGIN_DOC_DIR."/monitoring/templates/".$rrdtool_template."_graph.json";
      if (!file_exists($filename)) {
         return;
      }
      $a_json = json_decode(file_get_contents($filename));

      $timezonefile = str_replace("+", ".", $timezone);
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


      $opts = "";

      $opts .= ' --start -'.$time;
      $opts .= " --title '".$a_json->data[0]->labels[0]->title."'";
//      $opts .= " --vertical-label '".$a_json->data->labels->vertical-label."'";
      $opts .= " --width ".$width;
      $opts .= " --height 200";
      foreach ($a_json->data[0]->miscellaneous[0]->color as $color) {
         $opts .= " --color ".$color;
      }
      if ($a_json->data[0]->limits[0]->{"upper-limit"} != "") {
         $opts .= " --upper-limit ".$a_json->data[0]->limits[0]->{"upper-limit"};
      }
      if ($a_json->data[0]->limits[0]->{"lower-limit"} != "") {
         $opts .= " --lower-limit ".$a_json->data[0]->limits[0]->{"lower-limit"};
      }

      foreach ($a_json->data[0]->data as $data) {
         $data = str_replace("[[RRDFILE]]",
                             GLPI_PLUGIN_DOC_DIR."/monitoring/".$itemtype."-".$items_id.".rrd",
                             $data);
         if (strstr($time, "d") OR  strstr($time, "h")) {
            $data = str_replace("AVERAGE", "LAST", $data);
         }
         if (strstr($data, "DEF")
                 AND !strstr($data, "CDEF")
                 AND $converttimezone != '0') {
            $data = $data.':start=-'.$time.$timezone.'h:end='.$timezone.'h';
         }
         $opts .= " ".$data;
         if (strstr($data, "DEF")
                 AND !strstr($data, "CDEF")
                 AND $converttimezone != '0') {
            $a_explode = explode(":", $data);
            $a_name = explode("=", $a_explode[1]);
            $opts .= " SHIFT:".$a_name[0].":".$converttimezone;
         }
      }

      //$ret = rrd_graph(GLPI_PLUGIN_DOC_DIR."/monitoring/".$itemtype."-".$items_id."-".$time.".gif", $opts, count($opts));
      if (file_exists(GLPI_PLUGIN_DOC_DIR."/monitoring/".$itemtype."-".$items_id.".rrd")) {
         ob_start();
         system(PluginMonitoringConfig::getRRDPath()."/rrdtool graph ".GLPI_PLUGIN_DOC_DIR."/monitoring/".$itemtype."-".$items_id."-".$time.$timezonefile.".gif ".$opts, $ret);
         ob_end_clean();
         if (isset($ret)
                 AND $ret != '0' ) {
            echo "Create error: $ret for ".PluginMonitoringConfig::getRRDPath()."/rrdtool graph ".GLPI_PLUGIN_DOC_DIR."/monitoring/".$itemtype."-".$items_id."-".$time.$timezonefile.".gif ".
                     $opts."\n";
         }
      }
      return true;
   }



   function showRRDTemplates() {
      $a_templates = array();
      $a_perfdata= array();
      if ($handle = opendir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates/")) {
          while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                 if (strstr($entry, "-perfdata.json")) {
                    $entry = str_replace("-perfdata.json", "", $entry);
                    $a_templates[$entry] = array();
                    $a_perfdata[$entry] = 1;
                 }
              }
          }
          closedir($handle);
      }
      if ($handle = opendir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates/")) {
          while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                 if (strstr($entry, "_graph.json")) {
                    $graph = $entry;
                    $a_entry = explode("-", $entry);
                    $a_templates[$a_entry[0]][] = $graph;
                 }
              }
          }
          closedir($handle);
      }

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('RRDtool templates', 'monitoring');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo "perfdata";
      echo "</th>";
      echo "<th>";
      echo "Graphs";
      echo "</th>";
      echo "</tr>";

      foreach ($a_templates as $name=>$data) {
         echo "<tr class='tab_bg_3'>";
         echo "<td rowspan='".count($data)."'>";
         echo $name;

         if (!isset($a_perfdata[$name])) {
            echo "*";
         }
         echo "</td>";

         $i = 0;
         foreach ($data as $graphname) {
            if ($i > 0) {
               echo "<tr class='tab_bg_3'>";
            }
            echo "<td>";
            echo $graphname;
            echo "</td>";
            echo "</tr>";
            $i++;
         }
      }
      echo "</table>";
   }



   function addTemplate() {
      global $CFG_GLPI;

      echo "<form name='form' method='post' enctype='multipart/form-data'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/rrdtemplate.form.php'>";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('RRDtool templates', 'monitoring')." (".__('Find files on', 'monitoring')."
          <a href='https://github.com/rrdtooltemplates/rrdtool_templates'>github</a>)";
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Upload perfdata and graph files', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo "<input type='file' name='filename' value='' size='39'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' name='add' value=\"".__('Add')."\" class='submit'>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</form>";
   }
}

?>
