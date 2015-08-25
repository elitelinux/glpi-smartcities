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
   @since     2012

   ------------------------------------------------------------------------
 */

if (strpos($_SERVER['PHP_SELF'],"widgetWeathermap.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
session_write_close();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (!isset($_GET["id"])
        OR !isset($_GET['extra_infos'])) {
   exit();
}

$pmWeathermap = new PluginMonitoringWeathermap();
$pmWeathermap->getFromDB($_GET['id']);

echo "<center>";
if ($_GET["id"] == -1) {
   echo "<table width='100%' class='tab_cadre'>";
} else {
   echo "<table width='100%'>";
}
echo "<tr>";
echo "<th>";
if ($_GET["id"] == -1) {
   echo __('Weathermap legend', 'monitoring');
} else {
   echo $pmWeathermap->getName();
   if ($_GET['extra_infos'] < 100) {
      echo " <a href='".$CFG_GLPI['root_doc'].
              "/plugins/monitoring/front/weathermap_full.php?id=".$_GET["id"].
              "' target='_blank'>(".__('full 100%', 'monitoring').")</a>";
   }
}
echo "</th>";
echo "</tr>";

if ($_GET["id"] == -1) {
   echo "<tr class='tab_bg_1'>";
   echo "<td>";

   echo "<table width='100%' style='border-collapse:collapse'>";
   echo "<tr>";
   echo "<td width='60%' style='background-color: green;' align='right'>";
   echo "<= 60%";
   echo "</td>";
   echo "<td width='20%' style='background-color: orange;' align='right'>";
   echo "<= 80%";
   echo "</td>";
   echo "<td width='20%' style='background-color: red;' align='right'>";
   echo "<= 100%";
   echo "</td>";
   echo "</tr>";
   echo "</table>";

   echo "</td>";
   echo "</tr>";
}

echo "</table>";


if ($_GET["id"] > 0) {
   $pmWeathermap->drawMap($_GET["id"], $_GET['extra_infos']);
}
?>