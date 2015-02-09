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
   @since     2013

   ------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updateFilariane.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

$pmDisplayview = new PluginMonitoringDisplayview();

if (!isset($_SESSION['plugin_monitoring_view_ariane'])) {
   $_SESSION['plugin_monitoring_view_ariane'] = array();
}
if (!isset($_SESSION['plugin_monitoring_view_ariane'][$_POST['id']])) {
   $_SESSION['plugin_monitoring_view_ariane'][$_POST['id']] = array();
}

if (!isset($_SESSION['plugin_monitoring_view_arianelist'])) {
   $_SESSION['plugin_monitoring_view_arianelist'] = array();
}
if (!isset($_SESSION['plugin_monitoring_view_arianelist'][$_POST['id']])) {
   $_SESSION['plugin_monitoring_view_arianelist'][$_POST['id']] = array();
}

if (strstr($_POST['updatefil'], '!')) {
   $displayviews_id = str_replace("!", "", $_POST['updatefil']);
   $pmDisplayview->getFromDB($displayviews_id);
   if (!isset($_SESSION['plugin_monitoring_view_arianelist'][$_POST['id']][$displayviews_id])) {
      $cnt = count($_SESSION['plugin_monitoring_view_ariane'][$_POST['id']]);
      $_SESSION['plugin_monitoring_view_ariane'][$_POST['id']][$cnt] = array(
          'id'    => $displayviews_id,
          'name'  => $pmDisplayview->fields['name']
      );
      $_SESSION['plugin_monitoring_view_arianelist'][$_POST['id']][$displayviews_id] = 1;
   }
}


$elements = array();
$todelete = 0;
foreach ($_SESSION['plugin_monitoring_view_ariane'][$_POST['id']] as $num=>$data) {
   if ($todelete) {
      unset($_SESSION['plugin_monitoring_view_ariane'][$_POST['id']][$num]);
      unset($_SESSION['plugin_monitoring_view_arianelist'][$_POST['id']][$data['id']]);
   } else {
      $link = '';
      if ($data['id'] != $_POST['currentview']) {
      $link = '<a href="javascript:;" onclick="document.getElementById(\'updatefil\').value = \''.$data['id'].'!\';'.
                 'document.getElementById(\'updateviewid\').value = \''.$data['id'].'\';reloadfil();reloadview();">';
         $elements[] = "<i>".$link.$data['name']."</a></i>";
      } else {
         $elements[] = "<i>".$data['name']."</i>";
         $todelete = 1;
      }
   }
}

echo implode(" > ", $elements);

?>