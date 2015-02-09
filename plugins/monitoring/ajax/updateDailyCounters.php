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
// Original Author of file: Frédéric MOHIER
// Purpose of file:
// ----------------------------------------------------------------------

$USEDBREPLICATE = 1;

session_id($_POST['sess_id']);
$_SESSION['glpiID'] = $_POST['glpiID'];
$_SESSION['plugin_monitoring_securekey'] = $_POST['plugin_monitoring_securekey'];
$_SESSION['plugin_monitoring_checktime'] = 1;

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updateDailyCounters.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

$pmSecurity = new PluginMonitoringSecurity();
$pmSecurity->isSecure();
PluginMonitoringSecurity::deleteCheckSessionTime();

Session::checkLoginUser();

$pmDailyCounters = new PluginMonitoringHostdailycounter();
$counters = array();

$counter_types = array (
   // 'first'        => __('First value', 'monitoring'),
   'last'         => __('Last value', 'monitoring')
);

$counter_operations = array (
   // 'difference'   => __('Difference', 'monitoring')
);

foreach ($counter_types as $type => $type_title) {
   if (isset($_POST['debug'])) echo "<pre>Type : $type</pre>";

   $counters[$type] = array();
   $a_ret = $pmDailyCounters->getSpecificData($_POST['hostname'], $type);
   // if (isset($_POST['debug'])) echo "<pre>".print_r($a_ret)."</pre>";
   foreach ($a_ret as $counter) {
      if (isset($_POST['debug'])) echo "<pre>".$counter['id']." (".$counter['name'].") =".$counter['value']."</pre>";

      $counters[$type][$counter['id']] = $counter;
   }
}

foreach ($counter_operations as $type => $type_title) {
   if (isset($_POST['debug'])) echo "<br/><pre>Operation : $type</pre>";

   $counters[$type] = array();

   switch ($type) {
      case 'difference':
         foreach ($counters['first'] as $id => $data) {
            $counter = array();
            $counter['id'] = $id;
            $counter['name'] = $data['name'];
            $counter['value'] = $counters['last'][$id]['value'] - $counters['first'][$id]['value'];
            if (isset($_POST['debug'])) echo "<pre>Difference : ".$counter['name']." = ".$counter['value']."</pre>";;
            $counters[$type][$id] = $counter;
         }
         break;
      default :
         break;
   }
}


// if (isset($_POST['debug'])) echo "<pre>Found counters : ".print_r($counters)."</pre>";

if (isset($_POST['counters']) && (! empty($_POST['counters']))) {
   $hdr_types = "<tr class='tab_bg_1'>";
   $hdr_counters = "<tr class='tab_bg_1'>";
   $row_counter = "<tr class='tab_bg_2'>";

   foreach ($_POST['counters'] as $requestedCounter) {
      if (isset($_POST['debug'])) echo "<pre>Requested counter : ".$requestedCounter."</pre>";
      $header=false;

      foreach ($counters as $type => $typeCounters) {
         if (isset($_POST['debug'])) echo "<pre>Counter type '$type'</pre>";

         foreach ($typeCounters as $id => $data) {
            if ($id != $requestedCounter) continue;
            if (isset($_POST['debug'])) echo "<pre>Counter : $id - ".$data['name']." = ".$data['value']."</pre>";

            if (! $header) {
               $hdr_counters .= "<th colspan='".count($counters)."' counterId='$requestedCounter' counterName='".$data['name']."' class='counterId center'>".$data['name']."</th>";
               $header=true;
            }

            if (array_key_exists($type, $counter_types)) $hdr_types .= "<th class='center'>".$counter_types[$type]."</th>";
            if (array_key_exists($type, $counter_operations)) $hdr_types .= "<th class='center'>".$counter_operations[$type]."</th>";

            $row_counter .= "<td counter='$id' counterType='$type' class='localCounter center'>".$data['value']."</th>";
         }
      }
   }
   echo "<table class='tab_cadrehov'>";
   echo "$hdr_counters</tr>";
   if (count ($counters) != 1) echo "$hdr_types</tr>";
   echo "$row_counter</tr>";
   echo "</table>";
} else {
   if (isset($_POST['json']) && ($_POST['json']=='1')) {
      echo json_encode($counters);
   } else {
      echo "<table class='tab_cadrehov'>";

      echo "<tr class='tab_bg_1'><th colspan='2'>".__('Counters : registered values', 'monitoring')."</th></tr>";
      foreach ($counters as $type => $typeCounters) {
         if (isset($_POST['debug'])) echo "<pre>Counter type '$type' : ".print_r($typeCounters)."</pre>";

         if (array_key_exists($type, $counter_types))
            echo "<tr class='tab_bg_1'><th colspan='2' class='left'>".$counter_types[$type]."</th></tr>";
         if (array_key_exists($type, $counter_operations))
            echo "<tr class='tab_bg_1'><th colspan='2' class='left'>".$counter_operations[$type]."</th></tr>";

         foreach ($typeCounters as $id => $data) {
            if (isset($_POST['debug'])) echo "<pre>Id : $id - ".$data['name']." = ".$data['value']."</pre>";;

            echo "<tr class='tab_bg_3'><td class='left'>".$data['name']."</td><td class='center'>".$data['value']."</td></tr>";
         }
      }

      echo "</table>";
   }
}
?>