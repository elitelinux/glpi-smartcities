<?php
/**
 * @version $Id: pcsbyentity.php 297 2015-05-30 21:34:55Z yllen $
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

function cmpStat ($a, $b) {
   return $a["tot"] - $b["tot"];
}


function doStatBis ($table, $entities, $header) {
   global $DB;

   // Compute stat
   $counts = array();
   foreach ($entities as $entity) {
      // Count for this entity
      $sql = "SELECT `states_id`, count(*) AS cpt
              FROM `$table`
              WHERE `is_deleted` = '0'
                    AND `is_template` = '0'
                    AND `entities_id` = '$entity'
              GROUP BY `states_id`";

      $result          = $DB->query($sql);
      $counts[$entity] = array();
      while ($data = $DB->fetch_array($result)) {
         $counts[$entity][$data["states_id"]] = $data["cpt"];
      }

      $counts[$entity]["tot"] = 0;
      foreach ($header as $id => $name) {
         if (isset($counts[$entity][$id])) {
            $counts[$entity]["tot"] += $counts[$entity][$id];
         } else {
            $counts[$entity][$id] = 0;
         }
      }
   }

   // Sort result
   uasort($counts,"cmpStat");

   // Display result
   $total["tot"] = 0;
   foreach ($header as $id => $name) {
      $total[$id] = 0;
   }
   foreach ($counts as $entity => $count) {
      if ($count["tot"]) {
         $Ent = new Entity();
         $Ent->getFromDB($entity);

         echo "<tr class='tab_bg_2'><td class='left'>";
         if ($entity) {
            echo $Ent->fields["name"];
         } else {
            _e('Root entity');
         }
         echo "</td><td class='right'>" . $count["tot"] . "</td>";
         $total["tot"] += $count["tot"];
         foreach ($header as $id => $name) {
            echo "<td class='right'>" . $count[$id] . "</td>";
            $total[$id] += $count[$id];
         }
      }
      echo "</tr>\n";
   }

   // Display total
   if (count($entities) >1) {
      echo "<tr class='tab_bg_1'><td class='left'>".__('Total')."</td>";
      echo "<td class='right'>" . $total["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $total[$id] . "</td>";
      }
      echo "</tr>\n";
   }
}


function doStat ($table, $entity, $header, $level=0) {
   global $DB;

   $Ent = new Entity();
   $Ent->getFromDB($entity);

   // Count for this entity
   $sql = "SELECT `states_id`, count(*) AS cpt
           FROM `$table`
           WHERE `is_deleted` = '0'
                 AND `is_template` = '0'
                 AND `entities_id` = '$entity'
           GROUP BY `states_id`";

   $result = $DB->query($sql);
   $count  = array();
   while ($data = $DB->fetch_array($result)) {
      $count[$data["states_id"]] = $data["cpt"];
   }

   $count["tot"] = 0;
   foreach ($header as $id => $name) {
      if (isset($count[$id])) {
         $count["tot"] += $count[$id];
      } else {
         $count[$id] = 0;
      }
   }

   // Display counters for this entity
   if ($count["tot"] >0) {
      echo "<tr class='tab_bg_2'><td>";
      for ($i=0 ; $i<$level ; $i++) {
         echo "&nbsp;&nbsp;&nbsp;";
      }
      if ($entity) {
         echo $Ent->fields["name"];
      }else {
         _e('Root entity');
      }
      echo "</td>";
      echo "<td class='right'>" . $count["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $count[$id] . "</td>";
      }
      echo "</tr>\n";
   }

   // Call for Childs
   $save = $count["tot"];
   doStatChilds($table, $entity, $header, $count, $level+1);

   // Display total (Current+Childs)
   if ($save != $count["tot"]) {
      echo "<tr class='tab_bg_1'><td>";
      for ($i=0 ; $i<$level ; $i++) {
         echo "&nbsp;&nbsp;&nbsp;";
      }
      _e('Total');

      if ($entity) {
         echo $Ent->fields["name"];
      } else {
         _e ('Root entity');
      }
      echo "</td>";
      echo "<td class='right'>" . $count["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $count[$id] . "</td>";
      }
      echo "</tr>\n";
   }
   return $count;
}


function doStatChilds($table, $entity, $header, &$total, $level) {
   global $DB;

   // Search child entities
   $sql = "SELECT `id`
           FROM `glpi_entities`
           WHERE `entities_id` = '$entity'
           ORDER BY `name`";
   $result = $DB->query($sql);

   while ($data = $DB->fetch_array($result)) {
      $fille = doStat($table, $data["id"], $header, $level);
      foreach ($header as $id => $name) {
         $total[$id] += $fille[$id];
      }
      $total["tot"] += $fille["tot"];
   }
}

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

include ("../../../../inc/includes.php");

Session::checkRight("plugin_reports_pcsbyentity", READ);
//TRANS: The name of the report = Number of items by entity
Html::header(__('pcsbyentity_report_title', 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

echo "<div class='center'>";

// ---------- Form ------------
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='2'>" . __('Number of items by entity', 'reports') .
      "</th></tr>\n";
echo "<tr class='tab_bg_1'><td class='right'>" . __('Item type') ."</td>";
echo "<td><select name='type'><option value=''>".Dropdown::EMPTY_VALUE."</option>";

$choix = array('Computer'         => _n('Computer', 'Computers', 2),
               'Monitor'          => _n('Monitor', 'Monitors', 2),
               'Printer'          => _n('Printer', 'Printers', 2),
               'NetworkEquipment' => __('Networking'),
               'Phone'            => _n('Phone', 'Phones', 2));

foreach ($choix as $id => $name) {
   $item = new $id();
   if ($item->canView()) {
      echo "<option value='" . $id;
      if (isset($_POST["type"]) && $_POST["type"]==$id) {
         echo "' selected='selected'>";
      } else {
         echo "'>";
      }
      echo $name . "</option>";
   }
}
echo "</select></td></tr>\n";

if (count($_SESSION["glpiactiveentities"]) > 1) {
   echo "<tr class='tab_bg_1'><td class='right'>" . __('Display', 'reports') ."</td>";
   echo "<td><select name='sort'><option value='0'>".__('Entity tree', 'reports')."</option>";
   $sel = (isset($_POST["sort"]) && $_POST["sort"] ? "selected='selected'" : "");

   echo "<option value='1' $sel>".__('Sort by count', 'reports')."</option>".
        "</select></td></tr>\n";
}

echo "<tr class='tab_bg_1 center'>".
     "<td colspan='2'><input type='submit' value='valider' class='submit'/></td>";
echo "</tr>\n";
echo "</table>\n";
Html::closeForm();
echo "</div>\n";

// --------------- Result -------------
if (isset($_POST["type"]) && $_POST["type"] != '') {
   echo "<table class='tab_cadre'>\n";

   echo "<tr><th>".__('Entity'). "</th>" .
         "<th>&nbsp;" . __('Total') . "&nbsp;</th>" .
         "<th>&nbsp;" . __('Unknown', 'reports') . "&nbsp;</th>";

   $sql = "SELECT `id`, `name`
           FROM `glpi_states`
           ORDER BY `id`";
   $result = $DB->query($sql);

   $header[0] = __('Unknown', 'reports');
   while ($data = $DB->fetch_array($result)) {
      $header[$data["id"]] = $data["name"];
      echo "<th>&nbsp;" . $data["name"] . "&nbsp;</th>";
   }
   echo "</tr>\n";

   if (isset($_POST["sort"]) && ($_POST["sort"] > 0)) {
      doStatBis(getTableForItemType($_POST["type"]), $_SESSION["glpiactiveentities"], $header);
   } else {
      doStat(getTableForItemType($_POST["type"]), $_SESSION["glpiactive_entity"], $header);
   }
   echo "</table></div>";
}

Html::footer();
?>