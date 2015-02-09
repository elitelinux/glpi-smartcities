<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
                
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "dropdownValue.php")) {
   $AJAX_INCLUDE=1;
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();
Session::checkLoginUser();

$item  = new $_REQUEST['itemtype'];
$table = getTableForItemType($_REQUEST['itemtype']);

// Security
if (!TableExists($table)) {
   exit();
}

// Make a select box with preselected values
if (!isset ($_REQUEST["limit"])){
   $_REQUEST["limit"] = $_SESSION["glpidropdown_chars_limit"];
}
$where = "WHERE 1=1";

if ($item->maybeDeleted()) {
   $where .= " AND `is_deleted` = '0' ";
}
if ($item->maybeTemplate()) {
   $where .= " AND `is_template` = '0' ";
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";
if ($_REQUEST['searchText'] == $CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

//why ?
$PluginRacksRack_Item = new PluginRacksRack_Item();
if (in_array(get_class($item), PluginRacksRack::getTypes())) {
   if (isset($_REQUEST['value'])) {
      $where .= "AND `" .$table. "`.`id` <> '" . $_REQUEST['value'] . "' ";
   }
   if ($item->isEntityAssign()) {

      $multi     = $item->maybeRecursive();
      $field     = "entities_id";
      $add_order = " entities_id, ";
      if (isset($_REQUEST["entity_restrict"]) && !($_REQUEST["entity_restrict"]<0)) {
         $where .= getEntitiesRestrictRequest(" AND ", $table, $field, 
                                              $_REQUEST["entity_restrict"]);
         if (is_array($_REQUEST["entity_restrict"]) 
            && count($_REQUEST["entity_restrict"])>1) {
            $multi = true;    
         }
      } else {
         $where .= getEntitiesRestrictRequest(" AND ", $table, $field);
         if (count($_SESSION['glpiactiveentities']) > 1) {
            $multi=true;    
         }
      }
   }

   $field = "name";
   if ($_REQUEST['searchText'] != $CFG_GLPI["ajax_wildcard"])
      $where .= " AND $field " .
      Search::makeTextSearch($_REQUEST['searchText']);

      $where .= " AND `" . $table . "`.`id` NOT IN (0";
      $where .= $PluginRacksRack_Item->findItems($DB, $_REQUEST['modeltable']);
      $where .= ") ";
      $query = "SELECT `" . $table . "`.`name` AS name,
                       `" . $table . "`.`entities_id` AS entities_id,
                       `" . $table . "`.`id`, 
                       `glpi_plugin_racks_itemspecifications`.`id` AS spec 
               FROM `glpi_plugin_racks_itemspecifications`,`" . $table . "` 
                  $where 
                  AND `glpi_plugin_racks_itemspecifications`.`model_id` = `" . $table . "`.`".$_REQUEST['modelfield']."` 
                  AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '" . $_REQUEST['modeltable'] . "' 
               ORDER BY $add_order  `" . $table . "`.`name` 
               $LIMIT";
      $result = $DB->query($query);
} else {
   $multi = false;
   $query = "SELECT `glpi_plugin_racks_othermodels`.`id`,
                    `glpi_plugin_racks_othermodels`.`name`,
                    `glpi_plugin_racks_othermodels`.`comment`, 
                    `glpi_plugin_racks_itemspecifications`.`id` AS spec
             FROM `glpi_plugin_racks_othermodels`, 
                  `glpi_plugin_racks_itemspecifications` 
             WHERE `glpi_plugin_racks_itemspecifications`.`model_id` = `glpi_plugin_racks_othermodels`.`id` 
                AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '".$_REQUEST['modeltable']."' 
             ORDER BY `glpi_plugin_racks_othermodels`.`name` $LIMIT";
   $result = $DB->query($query);
}

$return = array('results' => array(array('id' => null, 'text' => '-----')));
$results = &$return['results'];
if ($count = $DB->numrows($result)) {
   $prev = -1;
   $tmp_results = array();
   while ($data=$DB->fetch_array($result)) {
      $entities_id = 0;
      if (isset($data["entities_id"])) {
         $entities_id = $data["entities_id"];
      }
      $tmp_results[$entities_id]['text']= Dropdown::getDropdownName("glpi_entities", $entities_id);
      $tmp_results[$entities_id]['children'][] = array('id'    => $_REQUEST["modeltable"].";".
                                                   $data['id'].";".
                                                   $data['spec'],
                                        'level' => 1,
                                        'text'  => substr($data["name"], 0, 
                                                         $CFG_GLPI["dropdown_chars_limit"]));
   }

   foreach ($tmp_results as $tmp_result) {
      $results[] = $tmp_result;
   }
}

$return['count'] = $count;
echo json_encode($return);
?>