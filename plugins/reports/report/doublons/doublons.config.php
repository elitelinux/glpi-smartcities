<?php
/**
 * @version $Id: doublons.config.php 298 2015-05-30 22:05:39Z yllen $
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
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

include ("../../../../inc/includes.php");

Plugin::load('reports');

Session::checkRight("profile", UPDATE);

Html::header(__('Duplicate computers', 'reports'), $_SERVER['PHP_SELF'], "config", "plugins");

$types = array(1 => __('MAC'),
               2 => __('IP'),
               3 => __('Serial number'));

if (isset($_POST["delete"]) && isset($_POST['id'])) {
   $query = "DELETE
             FROM `glpi_plugin_reports_doublons_backlists`
             WHERE `id` = '".$_POST['id']."'";
   $DB->query($query);

} else if (isset($_POST["add"])
           && isset($_POST["type"])
           && isset($_POST["addr"])
           && strlen($_POST["addr"])) {

   $query = "INSERT INTO `glpi_plugin_reports_doublons_backlists`
             SET `type` = '".$_POST["type"]."',
                 `addr` = '".trim($_POST["addr"])."',
                 `comment` = '".trim($_POST["comment"])."'";
   $DB->query($query);
}

// Initial creation
if (TableExists("glpi_plugin_reports_doublons_backlist")) {
   $migration = new Migration(160);
   $migration->renameTable("glpi_plugin_reports_doublons_backlist",
                           "glpi_plugin_reports_doublons_backlists");

   $migration->changeField("glpi_plugin_reports_doublons_backlists", "ID", "id", 'autoincrement');

   $migration->executeMigration();

} else if (!TableExists("glpi_plugin_reports_doublons_backlists")) {
   $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_doublons_backlists` (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `type` int(11) NOT NULL DEFAULT '0',
               `addr` varchar(255) DEFAULT NULL,
               `comment` varchar(255) DEFAULT NULL,
               PRIMARY KEY (`id`)
             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
   $DB->query($query) or die($DB->error());

   $query = "INSERT INTO`glpi_plugin_reports_doublons_backlists`
                    (`type`, `addr`, `comment`)
             VALUES (1, '44:45:53:54:42:00', 'Nortel IPSECSHM Adapter'),
                    (1, 'BA:D0:BE:EF:FA:CE', 'GlobeTrotter Module 3G+ Network Card'),
                    (1, '00:53:45:00:00:00', 'WAN (PPP/SLIP) Interface'),
                    (1, '80:00:60:0F:E8:00', 'Windows Mobile-based'),
                    (2, '127.0.0.1', 'loopback'),
                    (3, 'INVALID', 'from OCSNG'),
                    (3, 'XxXxXxX', 'from IBM')";
   $DB->query($query);
}

// ---------- Form ------------
echo "<div class='center'><table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th><a href='".GLPI_ROOT."/plugins/reports/front/config.form.php'>".
      __('Reports plugin configuration', 'reports') . "</a><br />&nbsp;<br />" .
      sprintf(__('%1$s: %2$s'), __('Report configuration', 'reports'),
              __('Duplicate computers', 'reports')) .
      "</th></tr>\n";

$plug = new Plugin();
if ($plug->isActivated('reports')) {
   echo "<tr class='tab_bg_1 center'><td>";
   echo "<a href='./doublons.php'>" .sprintf(__('%1$s - %2$s'), __('Report'),
                                             __('Duplicate computers', 'reports'))."</a>";
   echo "</td></tr>\n";
}

echo "</table>\n";

echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'><br />" .
      "<table class='tab_cadre' cellpadding='5'>\n" .
      "<tr class='tab_bg_1 center'><th colspan='4'>".__('Exception list setup', 'reports')."</th>".
      "</tr>\n" .
      "<tr class='tab_bg_1 center'><th>" . _n('Type', 'Types', 1) . "</th><th>" .
       sprintf(__('%1$s / %2$s'), __('IP'), __('MAC')) . "</th>" .
       "<th>" . __('Comments') . "</th><th>&nbsp;</th></tr>\n";

echo "<tr class='tab_bg_1 center'><td>";
Dropdown::showFromArray("type", $types);
echo "</td><td><input type='text' name='addr' size='20'></td><td>".
   "<input type='text' name='comment' size='40'></td>" .
   "<td><input type='submit' name='add' value='"._sx('button', 'Add')."' class='submit' ></td></tr>\n";

$query = "SELECT *
          FROM `glpi_plugin_reports_doublons_backlists`
          ORDER BY `type`, `addr`";
$res = $DB->query($query);

while ($data = $DB->fetch_array($res)) {
   echo "<tr class='tab_bg_1 center'><td>" . $types[$data["type"]] . "</td>" .
      "<td>" . $data["addr"] . "</td><td>" . $data["comment"] . "</td><td>";
   Html::showSimpleForm($_SERVER["PHP_SELF"], 'delete', _x('button', 'Put in dustbin'),
                        array('id' => $data["id"]));
   echo "</td></td></tr>\n";
}

echo "</table>";
Html::closeForm();
echo "</div>";

Html::footer();
?>