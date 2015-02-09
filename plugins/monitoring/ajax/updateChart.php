<?php
/*
 * @version $Id: dropdownValue.php 15573 2011-09-01 10:10:06Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: David DURIEUX
// Purpose of file:
// ----------------------------------------------------------------------

$USEDBREPLICATE = 1;

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updateChart.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
session_write_close();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();
$itemtype = $_GET['itemtype'];
$item = new $itemtype();
if (!$item->getFromDB($_GET['items_id'])) {
   echo json_encode(array());
//   echo __('Item not exist', 'monitoring');
   exit;
}

$pmServicegraph = new PluginMonitoringServicegraph();

$enddate = '';
if ($_GET['customdate'] == ''
        && $_GET['customtime'] == '') {
   $enddate = '';
} else if ($_GET['customdate'] == '') {
   $enddate =  mktime(date('H', $_GET['customtime']),
                      date('i', $_GET['customtime']),
                      date('s', $_GET['customtime']));
} else if ($_GET['customtime'] == '') {
   $enddate = $_GET['customdate'];
} else {
   // have the 2 defined
   $enddate =  mktime(date('H', $_GET['customtime']),
                      date('i', $_GET['customtime']),
                      date('s', $_GET['customtime']),
                      date('n', $_GET['customdate']),
                      date('d', $_GET['customdate']),
                      date('Y', $_GET['customdate']));
}
if (isset($_GET['components_id'])
        && !isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']])) {
   PluginMonitoringToolbox::loadPreferences($_GET['components_id']);
}
if (! isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']])) {
   echo json_encode(array());
//   echo __('No data ...', 'monitoring');
   exit;
}
if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']][''])) {
   unset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']]['']);
}
$a_ret = $pmServicegraph->generateData($_GET['rrdtool_template'],
                             $_GET['itemtype'],
                             $_GET['items_id'],
                             $_GET['timezone'],
                             $_GET['time'],
                             $enddate,
                             $_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']]);

$mydatat = $a_ret[0];
$a_labels = $a_ret[1];

$pmComponent = new PluginMonitoringComponent();
$pmCommand = new PluginMonitoringCommand();

$pmComponent->getFromDB($_GET['components_id']);
$pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);

$a_data   = array();
$a_values = array();

$lab = '';
$num = 1;
$a_names = array();
foreach ($mydatat as $name=>$data) {
   if (!isset($a_names[$name])) {
      $a_names[$name] = $num;
      $num++;
   }
   $display = "checked";
   if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']])) {
      $display = "";
   }
   if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']][$name])) {
      $display = $_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']][$name];
   }
   if ($display == "checked") {
      $i = 0;
      $datawarn=0;
      $datacrit=0;
      foreach ($a_labels as $label) {
         if (!isset($data[$i])
                 OR $data[$i] == '') {
            $data[$i] = 0;
         }
         if (isset($_SESSION['glpi_plugin_monitoring']['perfnameinvert'][$_GET['components_id']][$name])) {
            $data[$i] = "-".$data[$i];
         }
         if ($data[$i]=='0') {
            if (strstr(strtolower($name), "warn")) {
               $data[$i]=$datawarn;
            } else if (strstr(strtolower($name), "crit")) {
               $data[$i]=$datacrit;
            }
         } else {
            if (strstr(strtolower($name), "warn")) {
               $datawarn=max($datawarn, $data[$i]);
            } else if (strstr(strtolower($name), "crit")) {
               $datacrit=max($datacrit, $data[$i]);
            }
         }
         $a_values["val".$a_names[$name]][] = array(
            'x' => $label * 1000,
            'y' => $data[$i]
         );
         $i++;
         $lab = $label;
      }
   }
}

$color = array();
$color = PluginMonitoringServicegraph::colors();

$colorwarn = array();
$colorwarn = PluginMonitoringServicegraph::colors("warn");

$colorcrit = array();
$colorcrit = PluginMonitoringServicegraph::colors("crit");

if (isset($_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$_GET['components_id']])) {
   foreach ($_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$_GET['components_id']] as $perfname=>$colorperfname) {
      if (isset($color[$colorperfname])) {
         unset($color[$colorperfname]);
      }
      if (isset($colorwarn[$colorperfname])) {
         unset($colorwarn[$colorperfname]);
      }
      if (isset($colorcrit[$colorperfname])) {
         unset($colorcrit[$colorperfname]);
      }
   }
}

$nSerie=0;
foreach ($mydatat as $name=>$data) {
   $display = "checked";
   if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']])) {
      $display = "";
   }
   if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']][$name])) {
      $display = $_SESSION['glpi_plugin_monitoring']['perfname'][$_GET['components_id']][$name];
   }
   if ($display == "checked") {
      $area = 'true';
      $colordisplay = '';
      if (isset($_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$_GET['components_id']][$name])) {
         $colordisplay = $_SESSION['glpi_plugin_monitoring']['perfnamecolor'][$_GET['components_id']][$name];
      } else {
         if (strstr(strtolower($name), "warn")) {
            $colordisplay = array_shift($colorwarn);
         } else if (strstr(strtolower($name), "crit")) {
            $colordisplay = array_shift($colorcrit);
         } else {
            $colordisplay = array_shift($color);
         }
      }

      if (strstr(strtolower($name), "warn")) {
         $area = 'false';
      } else if (strstr(strtolower($name), "crit")) {
         $area = 'false';
      }
      $a_data[] = array(
         'area'   => $area,
         'values' => $a_values['val'.$a_names[$name]],
         'key'    => $name,
         'color'  => '#'.$colordisplay
      );
      $nSerie++;
   }
}

echo json_encode($a_data);

?>