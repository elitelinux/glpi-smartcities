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


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRacksReport extends CommonDBTM {
   
   const UTF8_ENCODING = 0;
   const ANSI_ENCODING = 1;
   
   const WINDOWS_END_OF_LINE = 0;
   const UNIX_END_OF_LINE    = 1;
   
   public function execQueryGetOnlyRacks(){
      global $DB;

      $pRack = new PluginRacksRack();

      $query = "SELECT DISTINCT `".$pRack->getTable()."`.*
              FROM `".$pRack->getTable()."`
              ORDER BY `".$pRack->getTable()."`.`name` ASC" ;

      $ret = array(
         "query" => $query,
         "query_result" => $DB->query($query)
      );
      return $ret;
   }


   public function execQuery($post){
      global $DB;

      $pRackItem = new PluginRacksRack_Item();
      $pRack = new PluginRacksRack();
      $query = "";

      $face = -1;
      if (isset($post['select_front_rear']) && $post['select_front_rear'] != 0){
         $face = $post['select_front_rear'];
      }

      if (isset($post['plugin_racks_racks_id']) && $post['plugin_racks_racks_id'] != 0){
         $restrictRackId = "   AND `".$pRack->getTable()."`.`id` = '".$post['plugin_racks_racks_id']."'";
         $restrictRackId .= "   AND `".$pRack->getTable()."`.`id` = `".$pRackItem->getTable()."`.`plugin_racks_racks_id`";
         $leftjoin=", `glpi_plugin_racks_racks_items` WHERE (1) ".$restrictRackId;


      }else{
         $restrictRackId="";
         $leftjoin = "LEFT JOIN `glpi_plugin_racks_racks_items` ON (`glpi_plugin_racks_racks_items`.`plugin_racks_racks_id` = `glpi_plugin_racks_racks`.`id`)";

         $restrictRackId = "AND `glpi_plugin_racks_racks_items`.`plugin_racks_racks_id` = `glpi_plugin_racks_racks`.`id`";
      }


      switch ($face) {
         case PluginRacksRack::FRONT_FACE:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM `".$pRackItem->getTable()."`,`glpi_plugin_racks_itemspecifications` , `".$pRack->getTable()."`
              WHERE `".$pRackItem->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` ".$restrictRackId." 
              AND (`".$pRackItem->getTable()."`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' ) AND NOT `".$pRack->getTable()."`.`is_deleted`
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;
            break;

         case PluginRacksRack::BACK_FACE:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM `".$pRackItem->getTable()."`,`glpi_plugin_racks_itemspecifications` , `".$pRack->getTable()."`
              WHERE `".$pRackItem->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` ".$restrictRackId." 
              AND (`".$pRackItem->getTable()."`.`faces_id` = '".PluginRacksRack::BACK_FACE."' ) AND NOT `".$pRack->getTable()."`.`is_deleted`
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;
            break;
         default:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM  `".$pRack->getTable()."`
              $leftjoin
              AND NOT `".$pRack->getTable()."`.`is_deleted`
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;

              break;
      }

      $ret = array(
         "query" => $query,
         "query_result" => $DB->query($query)
      );
      return $ret;
   }


   public function showResult($output_type, $limit=0, $params=array()){
      global $DB;

      $arrayRet = $this->execQuery($_POST);

      $result = $arrayRet['query_result'];
      $query = $arrayRet['query'];

      $nbtot = ($result ? $DB->numrows($result) : 0);

      if ($limit) {
         $start = (isset($_GET["start"]) ? $_GET["start"] : 0);
         if ($start >= $nbtot) {
            $start = 0;
         }
         if ($start > 0 || $start + $limit < $nbtot) {
            $result = $DB->query($query." LIMIT $start,$limit");
         }
      } else {
         $start = 0;
      }

      $nbCols = $DB->num_fields($result);
      $nbrows = $DB->numrows($result);


      $groupByRackName = true;
      if (isset($_POST['groupByRackName']) && $_POST['groupByRackName'] == "on"){
         $groupByRackName = false;
      }

      $title = date("d/m/Y H:i");
      if ($nbtot == 0) {
         echo "<div class='center'><font class='red b'>".__("No item found")."</font></div>";
         Html::footer();
      } else if ($output_type == Search::PDF_OUTPUT_LANDSCAPE || $output_type == Search::PDF_OUTPUT_PORTRAIT) {
         include (GLPI_ROOT."/lib/ezpdf/class.ezpdf.php");
      } else if ($output_type == Search::HTML_OUTPUT) {

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr  class='tab_bg_1'><th>$title</th></tr>\n";
         echo "<tr class='tab_bg_2 center'><td class='center'>";
         echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."?start=$start' target='_blank'>\n";

         $param = "";
         foreach ($_POST as $key => $val) {
            if (is_array($val)) {
               foreach ($val as $k => $v) {
                  echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
                  if (!empty($param)) {
                     $param .= "&";
                  }
                  $param .= $key."[".$k."]=".urlencode($v);
               }
            } else {
               echo "<input type='hidden' name='$key' value='$val' >";
               if (!empty($param)) {
                  $param .= "&";
               }
               $param .= "$key=".urlencode($val);
            }
         }

         echo "<input type='hidden' name='result_search_reports' value='searchdone' >";
         $param .= "&result_search_reports=searchdone&target=_blank";

         Dropdown::showOutputFormat();
          
         echo "<div id='display_csv_preferences' style='display:none'><br>";
         // Encoding
         echo __('Encoding', 'racks')."&nbsp;:&nbsp;";
         Dropdown::showFromArray("encoding", array('UTF-8 unicode', 'ANSI'), array('width' => '150px'));
         echo "&nbsp;";
         
         // Quotes
         echo "&nbsp;";
         echo __('Quotes', 'racks')."&nbsp;:&nbsp;";
         Dropdown::showYesNo("quotes", 0, -1, array('width' => '100px'));
         echo "&nbsp;";
         
         // End of line
         echo "&nbsp;";
         echo __('End of line', 'racks')."&nbsp;:&nbsp;";
         Dropdown::showFromArray("end_of_line", array('Windows', 'Unix'), array('width' => '100px'));
         echo "&nbsp;";
         echo "</div>";
         
         echo "<script type='text/javascript'>";
         echo "$('select[name=display_type]').change(function() {
                  switch($(this).val()){
                     case '3' :case '-3' :
                        $('#display_csv_preferences').css('display', 'block');
                        break;
                     default : 
                        $('#display_csv_preferences').css('display', 'none');
                        break;
                  }
               });";
         echo "</script>";
         
         Html::closeForm();
         echo "</td></tr>";
         echo "</table></div>";

         Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
      }

      if ($nbtot > 0) {

         if ($output_type == Search::HTML_OUTPUT)
         echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."?start=$start'>\n";

         echo Search::showHeader($output_type, $nbrows, $nbCols, true);

         $showAllFieds = true;
         $listFields = array();
         $cptField = 0;

         $showAllFieds =
            (!isset($_POST['cb_object_name'])      || $_POST['cb_object_name'] != "on")
         && (!isset($_POST['cb_object_location'])  || $_POST['cb_object_location'] != "on")
         && (!isset($_POST['cb_group'])            || $_POST['cb_group'] != "on")
         && (!isset($_POST['cb_manufacturer'])     || $_POST['cb_manufacturer'] != "on")
         && (!isset($_POST['cb_model'])            || $_POST['cb_model'] != "on")
         && (!isset($_POST['cb_serial_number'])    || $_POST['cb_serial_number'] != "on");


         $num = 1;
         $cptRow = 1;

         if  (!$showAllFieds){

            $this->showTitle($output_type, $num, __("Bay name","racks"), 'name', false, $params);
            $cptField++;

            $this->showTitle($output_type, $num, _n("Place","Places",1,"racks"), 'location', false, $params);
            $cptField++;

            $this->showTitle($output_type, $num,  __("Position","racks"), 'roomlocation', false, $params);
            $cptField++;
            
            $this->showTitle($output_type, $num,__("U","racks"), 'u', false, $params);
            $cptField++;
            
            $this->showTitle($output_type, $num,__("Front","racks")." / "._x('Rack enclosure' , 'Back', 'racks'), 'front_rear', false, $params);
            $cptField++;


            if (isset($_POST['cb_object_name']) && $_POST['cb_object_name'] == "on") {
               $listFields['object_name'] = $_POST['cb_object_name'];
               $this->showTitle($output_type, $num,__("Object name","racks"), 'object_name', false, $params);
               $cptField++;
            }

            // Lieu
            if (isset($_POST['cb_object_location']) && $_POST['cb_object_location'] == "on") {
               $listFields['object_location'] = $_POST['cb_object_location'];
               $this->showTitle($output_type, $num, __("Object location","racks"), 'object_location', false, $params);
               $cptField++;
            }

            // Groupe
            if (isset($_POST['cb_group']) && $_POST['cb_group'] == "on") {
               $listFields['group'] = $_POST['cb_group'];
               $this->showTitle($output_type, $num, __("Group"), 'roomlocation', false, $params);
               $cptField++;
            }

            // Fabricant
            if (isset($_POST['cb_manufacturer']) && $_POST['cb_manufacturer'] == "on") {
               $listFields['manufacturer'] = $_POST['cb_manufacturer'];
               $this->showTitle($output_type, $num, __("Manufacturer"), 'manufacturer', false, $params);
               $cptField++;
            }


            // Modèle
            if (isset($_POST['cb_model']) && $_POST['cb_model'] == "on") {
               $listFields['model'] = $_POST['cb_model'];
               $this->showTitle($output_type, $num, __("Model"), 'model', false, $params);
               $cptField++;
            }

            // Numéro de série
            if (isset($_POST['cb_serial_number']) && $_POST['cb_serial_number'] == "on") {
               $listFields['serial_number'] = $_POST['cb_serial_number'];
               $this->showTitle($output_type, $num, __("Serial number"), 'group', false, $params);
               $cptField++;
            }
         } else {
            $this->showTitle($output_type, $num, __("Bay name","racks"), 'rack_name', false, $params);
            $listFields['rack_name'] = true;

            $this->showTitle($output_type, $num, __("Place","racks"), 'location', false, $params);
            $listFields['location'] = true;

            $this->showTitle($output_type, $num, __("Position","racks"), 'roomlocation', false, $params);
            $listFields['roomlocation'] = true;

            $this->showTitle($output_type, $num, __("U","racks"), 'u', false, $params);
            $listFields['u'] = true;

            $this->showTitle($output_type, $num, __("Front","racks")." / "._x('Rack enclosure' , 'Back', 'racks'), 'front_rear', false, $params);
            $listFields['front_rear'] = true;

            $this->showTitle($output_type, $num, __("Object name","racks"), 'object_name', false, $params);
            $listFields['object_name'] = true;

            $this->showTitle($output_type, $num, __("Object location","racks"), 'object_location', false, $params);
            $listFields['object_location'] = true;

            $this->showTitle($output_type, $num, __("Group"), false, $params);
            $listFields['group'] = true;

            $this->showTitle($output_type, $num, __("Type"), 'type', false, $params);
            $listFields['type'] = true;

            $this->showTitle($output_type, $num, __("Manufacturer"), 'manufacturer', false, $params);
            $listFields['manufacturer'] = true;

            $this->showTitle($output_type, $num, __("Model"), 'model', false, $params);
            $listFields['model'] = true;

            $this->showTitle($output_type, $num, __("Serial number"), 'serial_number', false, $params);
            $listFields['serial_number'] = true;

            $this->showTitle($output_type, $num, __("Inventory number"), 'other_serial', false, $params);
            $listFields['other_serial'] = true;

            $cptField = 13;
         }

         echo self::showEndLine($output_type, $params);

         $num=1;

         $currentRack = -1;


         while ($row = $DB->fetch_array($result)) {

            // itemtype
            $itemtype = $row['itemtype'];

            $num = 1;
            $cptRow++;
            echo Search::showNewLine($output_type);

            if (isset($row['itemtype']) && $row['itemtype'] != "" ){
               $class = substr($itemtype, 0, -5);
               $item = new $class();
               $table = getTableForItemType($class);
               $r = $DB->query("SELECT * FROM `".$table."` WHERE `id` = '".$row["items_id"]."' ");
               $device = $DB->fetch_array($r);
            }

            // nom
            $link = Toolbox::getItemTypeFormURL("PluginRacksRack");
            if ($groupByRackName || $currentRack != $row['id']) {
               if($output_type == Search::HTML_OUTPUT){
                  echo self::showItem($output_type, "<a href=\"".$link."?id=".$row["id"]."\">".$row["name"]."</a>", $num, $cptRow, null, $params);
               }else{
                  echo self::showItem($output_type, $row["name"], $num, $cptRow, null, $params);
               }
            } else {
               echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
            }

            // lieu
            if ($groupByRackName || $currentRack != $row['id']) {
               $tmpId = $row['locations_id'];
               $tmpObj = new Location();
               $tmpObj->getFromDB($tmpId);
               if (isset($tmpObj->fields['name'])) {
                  echo self::showItem($output_type, $tmpObj->fields['name'], $num, $cptRow, null, $params);
               } else {
                  echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
               }
            } else {
               echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
            }

            // Emplacement
            if ($groupByRackName || $currentRack != $row['id']) {
               $tmpId = $row['plugin_racks_roomlocations_id'];
               $tmpObj = new PluginRacksRoomLocation();
               $tmpObj->getFromDB($tmpId);
               if (isset($tmpObj->fields['name'])) {
                  echo self::showItem($output_type, $tmpObj->fields['name'], $num, $cptRow, null, $params);
               } else {
                  echo self::showItem($output_type, '&nbsp;', $num, $cptRow, null, $params);
               }
            } else {
               echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
            }

            if (isset($row['itemtype']) && $row['itemtype'] != "" ){
               // U
               if (isset($row['position']) && $row['position'] != "") {
                  echo self::showItem($output_type, $row['position'], $num, $cptRow, null, $params);
               }else{
                  echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
               }

               // avant / arrière
               if ($row['faces_id'] == 1) {
                  echo self::showItem($output_type, __("Front","racks"), $num, $cptRow, null, $params);
               } else {
                  echo self::showItem($output_type, _x('Rack enclosure' , 'Back', 'racks'), $num, $cptRow, null, $params);
               }

               // Nom de l'objet
               if (array_key_exists("object_name", $listFields)) {
                  $link = Toolbox::getItemTypeFormURL(substr($itemtype, 0, -5));
                  if ($itemtype != 'PluginRacksOtherModel') {
                     if($output_type == Search::HTML_OUTPUT){
                        echo self::showItem($output_type, "<a href=\"".$link."?id=".$row["items_id"]."\">".$device["name"]."</a>", $num, $cptRow, null, $params);
                     }else{
                        echo self::showItem($output_type, $device["name"], $num, $cptRow, null, $params);
                     }
                  } else {
                     echo self::showItem($output_type, $device["name"], $num, $cptRow, null, $params);
                  }
               }
                
               // Lieu de l'objet
               if (array_key_exists("object_location", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo self::showItem($output_type, Dropdown::getDropdownName("glpi_locations", $device["locations_id"]), $num, $cptRow, null, $params);
                  } else {
                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }
                
               // Groupe
               if (array_key_exists("group", $listFields)) {
                  // Groupe
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo self::showItem($output_type, Dropdown::getDropdownName("glpi_groups", $device["groups_id_tech"]), $num, $cptRow, null, $params);
                  } else {
                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }

               // type
               if (array_key_exists("type", $listFields)) {
                  echo self::showItem($output_type, $item->getTypeName(), $num, $cptRow, null, $params);

               }

               // fabricant
               if (array_key_exists("manufacturer", $listFields)) {

                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo self::showItem($output_type, Dropdown::getDropdownName("glpi_manufacturers", $device["manufacturers_id"]), $num, $cptRow, null, $params);
                  } else {
                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }

               // modèle //TODO = model du rack => model des objets
               if (array_key_exists("model", $listFields)) {
                   
                  if ($itemtype != 'PluginRacksOtherModel') {
                      
                     $model_table = getTableForItemType($itemtype);
                     $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
                     echo self::showItem($output_type, Dropdown::getDropdownName($model_table, $device[$modelfield]), $num, $cptRow, null, $params);
                      
                  } else {

                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }

               // numéro de série
               if (array_key_exists("serial_number", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo self::showItem($output_type, $device['serial'], $num, $cptRow, null, $params);
                  } else {
                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }

               // numéro d'inventaire
               if (array_key_exists("other_serial", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo self::showItem($output_type, $device['otherserial'], $num, $cptRow, null, $params);
                  } else {
                     echo self::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow, null, $params);
                  }
               }

               $currentRack = $row['id'];
            }else{
               for ($k=0;$k<$cptField-3;$k++){
                  echo self::showItem($output_type, "&nbsp;", $num, $cptRow, null, $params);
               }
            }
            echo self::showEndLine($output_type, $params);
         }

         if ($output_type == Search::HTML_OUTPUT) {
            Html::closeForm();
         }

         echo self::showFooter($output_type, $title, $params);
      }
   }

   public function showForm($post){

      echo "<form name='form' method='post' action='../front/report.php'>";

      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__("Search criteria","racks") ."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Bay name","racks")." :</td>";
      echo "<td>";

      $arrayValue = array();
      if (isset($post['plugin_racks_racks_id'])){
         $arrayValue = array("value" => $post['plugin_racks_racks_id']);
      }
      $idSelectRankName = Dropdown::show( 'PluginRacksRack', $arrayValue );
      echo "<input type='hidden' name='id_select_rank_name' id='id_select_rank_name' val  ue='".$idSelectRankName."' />";
      echo "</td>";
      echo "<td>".__("Front","racks")." / " . _x('Rack enclosure' , 'Back', 'racks');
      echo "<input type='hidden' name='id_select_front_rear' id='id_select_front_rear' value='".$idSelectRankName."' />";
      echo "</td>";

      echo "<td>";

      $arrayValue = array();
      if (isset($post['select_front_rear'])){
         $arrayValue = array("value" => $post['select_front_rear']);
      }
      $idSelectFrontRear = Dropdown::showFromArray("select_front_rear", array("0" => Dropdown::EMPTY_VALUE,"1" => __("Front","racks"),"2" => _x('Rack enclosure' , 'Back', 'racks')),$arrayValue);
      echo "</td>";
      echo "</tr>";
      echo "<tr  class='tab_bg_1'>";
      echo "<td class='top'>".__("Field to export","racks")."</td>";
      echo "<td>";

      echo "<label for='cb_object_name'>   <input type='checkbox' name='cb_object_name' id='cb_object_name' ";
      if (isset($post['cb_object_name'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Object name","racks")."<br/></label>";

      echo "<label for='cb_object_location'>      <input type='checkbox' name='cb_object_location' id='cb_object_location' ";
      if (isset($post['cb_object_location'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Object location","racks")."<br/></label>";

      echo "<label for='cb_group'>      <input type='checkbox' name='cb_group' id='cb_group' ";
      if (isset($post['cb_group'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Group")."<br/></label>";

      echo "</td>";
      echo "<td colspan='2'>";
      echo "<label for='cb_manufacturer'>   <input type='checkbox' name='cb_manufacturer' id='cb_manufacturer' ";
      if (isset($post['cb_manufacturer'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Manufacturer")."<br/></label>";

      echo "<label for='cb_model'>      <input type='checkbox' name='cb_model' id='cb_model' ";
      if (isset($post['cb_model'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Model")."<br/></label>";

      echo "<label for='cb_serial_number'><input type='checkbox' name='cb_serial_number' id='cb_serial_number' ";
      if (isset($post['cb_serial_number'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Serial number")."<br/></label>";

      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";

      echo "<input type='hidden' name='result_search_reports' id='result_search_reports' value='searchdone' />";
      echo "<input type='submit' value='"._sx("button", "Search")."' class='submit' />";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Display options","racks")."</td>\n";
      echo "<td colspan='3'><label for='groupByRackName'><input type='checkbox' name='groupByRackName' id='groupByRackName' ";

      if (!isset($_POST['result_search_reports']) ){
         echo " checked ";
      }else if (isset($post['groupByRackName'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Group by bay name","racks")."</label></td>";
      echo "</tr>";
      echo "</table>";

      Html::closeForm();
   }

   /**
    * Display the column title and allow the sort
    *
    * @param $output_type
    * @param $num
    * @param $title
    * @param $columnname
    * @param bool $sort
    * @return mixed
    */
   function showTitle($output_type, &$num, $title, $columnname, $sort=false, $params=array()) {
      if ($output_type != Search::HTML_OUTPUT ||$sort==false) {
         echo self::showHeaderItem($output_type, $title, $num, null, 0, null, null, $params);
         return;
      }
      $order = 'ASC';
      $issort = false;
      if (isset($_REQUEST['sort']) && $_REQUEST['sort']==$columnname) {
         $issort = true;
         if (isset($_REQUEST['order']) && $_REQUEST['order']=='ASC') {
            $order = 'DESC';
         }
      }
      $link  = $_SERVER['PHP_SELF'];
      $first = true;
      foreach ($_REQUEST as $name => $value) {
         if (!in_array($name,array('sort','order','PHPSESSID'))) {
            $link .= ($first ? '?' : '&amp;');
            $link .= $name .'='.urlencode($value);
            $first = false;
         }
      }
      $link .= ($first ? '?' : '&amp;').'sort='.urlencode($columnname);
      $link .= '&amp;order='.$order;
      echo self::showHeaderItem($output_type, $title, $num,
      $link, $issort, ($order=='ASC'?'DESC':'ASC'), null, $params);
   }
   
   /**
    * Print generic Header Column
    *
    * @param $type            display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    * @param $value           value to display
    * @param &$num            column number
    * @param $linkto          link display element (HTML specific) (default '')
    * @param $issort          is the sort column ? (default 0)
    * @param $order           order type ASC or DESC (defaut '')
    * @param $options  string options to add (default '')
    *
    * @return string to display
   **/
   static function showHeaderItem($type, $value, &$num, $linkto="", $issort=0, $order="",
                                  $options="", $params=array()) {
      global $CFG_GLPI;

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf

         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            $PDF_TABLE .= "<th $options>";
            $PDF_TABLE .= Html::clean($value);
            $PDF_TABLE .= "</th>\n";
            break;

         case Search::SYLK_OUTPUT : //sylk
            global $SYLK_HEADER,$SYLK_SIZE;
            $SYLK_HEADER[$num] = Search::sylk_clean($value);
            $SYLK_SIZE[$num]   = Toolbox::strlen($SYLK_HEADER[$num]);
            break;

         case Search::CSV_OUTPUT : //CSV
            $quotes = "";
            if ($params['quotes']) {
               $quotes = "\"";
            }
            
            switch ($params['encoding']) {
               case self::ANSI_ENCODING :
                  $out = "$quotes".Toolbox::decodeFromUtf8(Search::csv_clean($value), 'windows-1252')."$quotes".$_SESSION["glpicsv_delimiter"];
                  break;
               case self::UTF8_ENCODING :
                  $out = "$quotes".Search::csv_clean($value)."$quotes".$_SESSION["glpicsv_delimiter"];
                  break;
            }
            break;

         default :
            $out = "<th $options>";
            if ($issort) {
               if ($order=="DESC") {
                  $out .= "<img src=\"".$CFG_GLPI["root_doc"]."/pics/puce-down.png\" alt='' title=''>";
               } else {
                  $out .= "<img src=\"".$CFG_GLPI["root_doc"]."/pics/puce-up.png\" alt='' title=''>";
               }
            }
            if (!empty($linkto)) {
               $out .= "<a href=\"$linkto\">";
            }
            $out .= $value;
            if (!empty($linkto)) {
               $out .= "</a>";
            }
            $out .= "</th>\n";
      }
      $num++;
      return $out;
   }

   
   /**
    * Print generic end line
    *
    * @param $type display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    *
    * @return string to display
   **/
   static function showEndLine($type, $params=array()) {

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf
         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            $PDF_TABLE.= '</tr>';
            break;

         case Search::SYLK_OUTPUT : //sylk
            break;

         case Search::CSV_OUTPUT : //csv
            switch ($params['end_of_line']) {
               case self::WINDOWS_END_OF_LINE :
                  $out = "\r\n";
                  break;
               case self::UNIX_END_OF_LINE :
                  $out = "\n";
                  break;
            }
            break;

         default :
            $out = "</tr>";
      }
      return $out;
   }
   
   /**
    * Print generic footer
    *
    * @param $type   display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    * @param $title  title of file : used for PDF (default '')
    *
    * @return string to display
   **/
   static function showFooter($type, $title="", $params=array()) {

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf
         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            if ($type == Search::PDF_OUTPUT_LANDSCAPE) {
               $pdf = new GLPIPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            } else {
               $pdf = new GLPIPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            }
            $pdf->SetCreator('GLPI');
            $pdf->SetAuthor('GLPI');
            $pdf->SetTitle($title);
            $pdf->SetHeaderData('', '', $title, '');
            $font       = 'helvetica';
            //$subsetting = true;
            $fonsize    = 8;
            if (isset($_SESSION['glpipdffont']) && $_SESSION['glpipdffont']) {
               $font       = $_SESSION['glpipdffont'];
               //$subsetting = false;
            }
            $pdf->setHeaderFont(Array($font, 'B', 8));
            $pdf->setFooterFont(Array($font, 'B', 8));

            //set margins
            $pdf->SetMargins(10, 15, 10);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);


            // For standard language
            //$pdf->setFontSubsetting($subsetting);
            // set font
            $pdf->SetFont($font, '', 8);
            $pdf->AddPage();
            $PDF_TABLE .= '</table>';
            $pdf->writeHTML($PDF_TABLE, true, false, true, false, '');
            $pdf->Output('glpi.pdf', 'I');
            break;

         case Search::SYLK_OUTPUT : //sylk
            global $SYLK_HEADER,$SYLK_ARRAY,$SYLK_SIZE;
            // largeurs des colonnes
            foreach ($SYLK_SIZE as $num => $val) {
               $out .= "F;W".$num." ".$num." ".min(50,$val)."\n";
            }
            $out .= "\n";
            // Header
            foreach ($SYLK_HEADER as $num => $val) {
               $out .= "F;SDM4;FG0C;".($num == 1 ? "Y1;" : "")."X$num\n";
               $out .= "C;N;K\"".Search::sylk_clean($val)."\"\n";
               $out .= "\n";
            }
            // Datas
            foreach ($SYLK_ARRAY as $row => $tab) {
               foreach ($tab as $num => $val) {
                  $out .= "F;P3;FG0L;".($num == 1 ? "Y".$row.";" : "")."X$num\n";
                  $out .= "C;N;K\"".Search::sylk_clean($val)."\"\n";
               }
            }
            $out.= "E\n";
            break;

         case Search::CSV_OUTPUT : //csv
            break;

         default :
            $out = "</table></div>\n";
      }
      return $out;
   }
   
   /**
    * Print generic normal Item Cell
    *
    * @param $type         display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    * @param $value        value to display
    * @param &$num         column number
    * @param $row          row number
    * @param $extraparam   extra parameters for display (default '')
    *
    *@return string to display
   **/
   static function showItem($type, $value, &$num, $row, $extraparam='', $params=array()) {

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf
         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            $value = preg_replace('/'.Search::LBBR.'/','<br>',$value);
            $value = preg_replace('/'.Search::LBHR.'/','<hr>',$value);
            $PDF_TABLE .= "<td $extraparam valign='top'>";
            $PDF_TABLE .= Html::weblink_extract(Html::clean($value));
            $PDF_TABLE .= "</td>\n";

            break;

         case Search::SYLK_OUTPUT : //sylk
            global $SYLK_ARRAY,$SYLK_HEADER,$SYLK_SIZE;
            $value                  = Html::weblink_extract($value);
            $value = preg_replace('/'.Search::LBBR.'/','<br>',$value);
            $value = preg_replace('/'.Search::LBHR.'/','<hr>',$value);
            $SYLK_ARRAY[$row][$num] = Search::sylk_clean($value);
            $SYLK_SIZE[$num]        = max($SYLK_SIZE[$num],
                                          Toolbox::strlen($SYLK_ARRAY[$row][$num]));
            break;

         case Search::CSV_OUTPUT : //csv
            $value = preg_replace('/'.Search::LBBR.'/','<br>',$value);
            $value = preg_replace('/'.Search::LBHR.'/','<hr>',$value);
            $value = Html::weblink_extract($value);
            
            $quotes = "";
            if ($params['quotes']) {
               $quotes = "\"";
            }
           
            switch ($params['encoding']) {
               case self::ANSI_ENCODING :
                  $out = "$quotes".Toolbox::decodeFromUtf8(Search::csv_clean($value), 'windows-1252')."$quotes".$_SESSION["glpicsv_delimiter"];
                  break;
               case self::UTF8_ENCODING :
                  $out = "$quotes".Search::csv_clean($value)."$quotes".$_SESSION["glpicsv_delimiter"];
                  break;
            }
            break;

         default :
            $out = "<td $extraparam valign='top'>";

            if (!preg_match('/'.Search::LBHR.'/',$value)) {
               $values = preg_split('/'.Search::LBBR.'/i',$value);
               $line_delimiter = '<br>';
            } else {
               $values = preg_split('/'.Search::LBHR.'/i',$value);
               $line_delimiter = '<hr>';
            }
            $limitto = 20;
            if (count($values) > $limitto) {
               for ( $i=0 ; $i<$limitto ; $i++) {
                  $out .= $values[$i].$line_delimiter;
               }
//                $rand=mt_rand();
               $out .= "...&nbsp;";
               $value = preg_replace('/'.Search::LBBR.'/','<br>',$value);
               $value = preg_replace('/'.Search::LBHR.'/','<hr>',$value);
               $out .= Html::showToolTip($value,array('display'   => false,
                                                      'autoclose' => false));

            } else {
               $value = preg_replace('/'.Search::LBBR.'/','<br>',$value);
               $value = preg_replace('/'.Search::LBHR.'/','<hr>',$value);
               $out .= $value;
            }
            $out .= "</td>\n";
      }
      $num++;
      return $out;
   }
}