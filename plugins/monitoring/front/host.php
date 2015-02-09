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

include ("../../../inc/includes.php");

Session::checkCentralAccess();

Html::header(__('Monitoring - dashboard (hosts)', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
             "PluginMonitoringDashboard", "dashboard");

$params = Search::manageParams("PluginMonitoringHost", $_GET);

// Display ressources perfdata ?
if (isset($_SESSION['plugin_monitoring']['ressources_perfdata'])) {
   unset($_SESSION['plugin_monitoring']['ressources_perfdata']);
}
// Reduced or normal interface ?
if (! isset($_SESSION['plugin_monitoring_reduced_interface'])) {
   $_SESSION['plugin_monitoring_reduced_interface'] = false;
}
if (isset($_POST['reduced_interface'])) {
   $_SESSION['plugin_monitoring_reduced_interface'] = $_POST['reduced_interface'];
}

$pmDisplay = new PluginMonitoringDisplay();
$pmMessage = new PluginMonitoringMessage();

$pmMessage->getMessages();

$pmDisplay->menu();

$pmDisplay->refreshPage();

$pmDisplay->showHostsCounters("Hosts", 1, 1);

// Manage search
if (isset($_SESSION['plugin_monitoring']['host'])) {
   $_GET = $_SESSION['plugin_monitoring']['host'];
}
if (isset($_GET['reset'])) {
   unset($_SESSION['glpisearch']['PluginMonitoringHost']);
}
if (isset($_GET['glpi_tab'])) {
   unset($_GET['glpi_tab']);
}
if (isset($_GET['hidesearch'])) {
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th>";
   echo "<a onClick='$(\"#searchformhosts\").toggle();'>
      <img src='".$CFG_GLPI["root_doc"]."/pics/deplier_down.png' />&nbsp;
         ".__('Display search form', 'monitoring')."
      &nbsp;<img src='".$CFG_GLPI["root_doc"]."/pics/deplier_down.png' /></a>";
   echo "</th>";
   echo "</tr>";
   echo "</table>";
   echo "<div style='display: none;' id='searchformhosts'>";
}
Search::showGenericSearch("PluginMonitoringHost", $params);
if (isset($_GET['hidesearch'])) {
   echo "</div>";
}

$pmDisplay->showHostsBoard($params);
if (isset($_SESSION['glpisearch']['PluginMonitoringHost']['reset'])) {
   unset($_SESSION['glpisearch']['PluginMonitoringHost']['reset']);
}

Html::footer();
?>
