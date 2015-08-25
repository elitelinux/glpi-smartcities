<?php
/**
 * @version $Id: equipmentbygroups.php 296 2015-05-25 00:14:30Z yllen $
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

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0; // Not really a big SQL request

include ("../../../../inc/includes.php");

includeLocales("equipmentbygroups");
//TRANS: The name of the report = List all devices of a group, ordered by users
Html::header(__('equipmentbygroups_report_title', 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

if (isset ($_GET["reset_search"])) {
   resetSearch();
}
$_GET = getValues($_GET, $_POST);

displaySearchForm();

$sql = "SELECT `id` AS group_id,
               `name` AS group_name
        FROM `glpi_groups`
        WHERE `entities_id` = ".$_SESSION["glpiactive_entity"].
              (isset($_GET["groups_id"]) && $_GET["groups_id"]
                     ? " AND `glpi_groups`.`id` = ".$_GET["groups_id"] : "") . "
        ORDER BY `name`";

$result = $DB->query($sql);
$last_group_id = -1;

while ($datas = $DB->fetch_array($result)) {
   if ($last_group_id != $datas["group_id"]) {
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr><th>".sprintf(__('%1$s: %2$s'), __('Group'), $datas['group_name'])."</th></th></tr>";
      $last_group_id = $datas["group_id"];
      echo "</table>";
   }

   getObjectsByGroupAndEntity($datas["group_id"], $_SESSION["glpiactive_entity"]);
}

Html::footer();


/**
 * Display group form
**/
function displaySearchForm() {
   global $_SERVER, $_GET, $CFG_GLPI;

   echo "<form action='" . $_SERVER["PHP_SELF"] . "' method='post'>";
   echo "<table class='tab_cadre' cellpadding='5'>";
   echo "<tr class='tab_bg_1 center'>";
   echo "<td width='300'>";
   echo __('Group')."&nbsp;&nbsp;";
   Group::dropdown(array('name =>'  => "group",
                         'value'    => $_GET["group"],
                         'entity'   => $_SESSION["glpiactive_entity"],
                         'condition' => "is_itemgroup"));
   echo "</td>";

   // Display Reset search
   echo "<td>";
   echo "<a href='" . $CFG_GLPI["root_doc"] .
         "/plugins/reports/report/equipmentbygroups/equipmentbygroups.php?reset_search=reset_search'>".
         "<img title='" . __s('Blank') . "' alt='" . __s('Blank') . "' src='" .
         $CFG_GLPI["root_doc"] . "/pics/reset.png' class='calendrier'></a>";
   echo "</td>";

   echo "<td>";
   echo "<input type='submit' value='Valider' class='submit' />";
   echo "</td>";

   echo "</tr></table>";
   Html::closeForm();
}


function getValues($get, $post) {

   $get = array_merge($get, $post);

   if (!isset ($get["group"])) {
      $get["group"] = 0;
   }
   return $get;
}


/**
 * Reset search
**/
function resetSearch() {
   $_GET["group"] = 0;
}


/**
 * Display all devices by group
 *
 * @param $group_id  the group ID
 * @param $entity    the current entity
**/
function getObjectsByGroupAndEntity($group_id, $entity) {
   global $DB;

   $display_header = false;

   $types = array('Computer', 'Monitor', 'NetworkEquipment', 'Phone', 'Printer');
   foreach ($types as $type) {
      $item = new $type();

      $query = "SELECT `".$item->getTable()."`.`id`, `name`, `groups_id`, `serial`, `otherserial`,
                       `immo_number`, `suppliers_id`, `buy_date`
                FROM `".$item->getTable()."`
                LEFT JOIN `glpi_infocoms`
                     ON (`".$item->getTable()."`.`id` = `glpi_infocoms`.`items_id`
                         AND `itemtype` = '$type')
                WHERE `groups_id` = '$group_id'
                      AND `".$item->getTable()."`.`entities_id` = '$entity'
                      AND `is_template` = '0'
                      AND `is_deleted` = '0'";

      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         if (!$display_header) {
            echo "<br><table class='tab_cadre_fixehov'>";
            echo "<tr><th>" .__('Type'). "</th><th>" .__('Name'). "</th>";
            echo "<th>" .__('Serial number'). "</th><th>" . __('Inventory number'). "</th>";
            echo "<th>" .__('Immobilization number')."</th>";
            echo "<th>" .__('Supplier'). "</th><th>" .__('Date of purchase'). "</th>";
            echo "</tr>";
            $display_header = true;
         }
         displayUserDevices($type, $result);
      }
   }
   echo "</table>";
}


/**
 * Display all device for a group
 *
 * @param $type      the objet type
 * @param $result    the resultset of all the devices found
**/
function displayUserDevices($type, $result) {
   global $DB, $CFG_GLPI;

   $item = new $type();
   while ($data = $DB->fetch_array($result)) {
      $link = $data["name"];
      $url  = Toolbox::getItemTypeFormURL("$type");
      $link = "<a href='" . $url . "?id=" . $data["id"] . "'>" . $link .
               (($CFG_GLPI["is_ids_visible"] || empty ($link)) ? " (" . $data["id"] . ")" : "") .
               "</a>";
      $linktype = "";
      if (isset ($groups[$data["groups_id"]])) {
         $linktype = sprintf(__('%1$s %2$s'), __('Group'), $groups[$data["groups_id"]]);
      }

      echo "<tr class='tab_bg_1'><td class='center'>".$item->getTypeName()."</td>".
            "<td class='center'>$link</td>";

      echo "<td class='center'>";
      if (isset ($data["serial"]) && !empty ($data["serial"])) {
         echo $data["serial"];
      } else {
         echo '&nbsp;';
      }
      echo "</td><td class='center'>";

      if (isset ($data["otherserial"]) && !empty ($data["otherserial"])) {
         echo $data["otherserial"];
      } else {
         echo '&nbsp;';
      }
      echo "</td><td class='center'>";

      if (isset ($data["immo_number"]) && !empty ($data["immo_number"])) {
         echo $data["immo_number"];
      } else {
         echo '&nbsp;';
      }
      echo "</td><td class='center'>";

      if (isset ($data["suppliers_id"]) && !empty ($data["suppliers_id"])) {
         echo Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]);
      } else {
         echo '&nbsp;';
      }
      echo "</td><td class='center'>";

      if (isset ($data["buy_date"]) && !empty ($data["buy_date"])) {
         echo Html::convDate($data["buy_date"]);
      } else {
         echo '&nbsp;';
      }
      echo "</td></tr>";
   }
}
?>
