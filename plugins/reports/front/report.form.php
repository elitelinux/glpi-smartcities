<?php
/**
 * @version $Id: report.form.php 304 2015-05-31 11:46:26Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

include_once ("../../../inc/includes.php");

Session::checkRight('profile', READ);

Plugin::load('reports', true);

Html::header(__('Reports plugin configuration', 'reports'), $_SERVER['PHP_SELF'], 'config',
             'plugins');

require_once "../inc/profile.class.php";

$report='';
if (isset($_POST['report'])) {
   $report=$_POST['report'];
}

$prof = new PluginReportsProfile();

if (isset($_POST['delete']) && $report) {
   $profile_right = new ProfileRight;
   $profile_right->deleteByCriteria(array('name' => "plugin_reports_$report"));
   ProfileRight::addProfileRights(array("plugin_reports_$report"));

} else  if (isset($_POST['update']) && $report) {
   Session::checkRight('profile', UPDATE);
   PluginReportsProfile::updateForReport($_POST);
}

$tab = $prof->updatePluginRights();

echo "<form method='post' action=\"".$_SERVER["PHP_SELF"]."\">";
echo "<table class='tab_cadre'><tr><th colspan='2'>";
echo "<a href='config.form.php'>".__('Reports plugin configuration', 'reports')."</a><br>&nbsp;<br>";
echo __('Rights management by report', 'reports'). "</th></tr>\n";

echo "<tr class='tab_bg_1'><td>".__('Report', 'Reports', 1). "&nbsp; ";
$query = "SELECT `id`, `name`
          FROM `glpi_profiles`
          ORDER BY `name`";
$result = $DB->query($query);

echo "<select name='report'>";
$plugname = array();
$rap      = array();
foreach($tab as $key => $plug) {
   $mod = (($plug == 'reports') ? $key : $plug.'_'.$key);
   if (!isset($plugname[$plug])) {
      // Retrieve the plugin name
      $function        = "plugin_version_$plug";
      $tmp             = $function();
      $plugname[$plug] = $tmp['name'];
   }
   $section = (isStat($mod) ? sprintf(__('%1$s - %2$s'), __('Assistance'), __('Statistics'))
                            : sprintf(__('%1$s - %2$s'), __('Tools'), __('Report', 'Reports', 2)));

   $rap[$plug][$section][$mod] = $LANG["plugin_$plug"][$key];
}

$tab = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
foreach ($rap as $plug => $tmp1) {
   echo '<optgroup label="'.sprintf(__('%1$s - %2$s'), __('Plugins'), $plugname[$plug]).'">';
   foreach ($tmp1 as $section => $tmp2) {
      echo '<optgroup label="'.$tab."&raquo;&nbsp;".$section.'">';
      foreach ($tmp2 as $mod => $name) {
         echo "<option value='$mod' ".($report=="$mod"?"selected":"").">${tab}${tab}$name</option>\n";
      }
      echo "</optgroup>\n";
   }
   echo "</optgroup>\n";
}

echo "</select>";
echo "<td><input type='submit' value='"._sx('button', 'Post')."' class='submit' ></td></tr>";
echo "</table>";
Html::closeForm();

if ($report) {
   PluginReportsProfile::showForReport($report);
}

Html::footer();
?>