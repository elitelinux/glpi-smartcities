<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFinancialreportsFinancialreport extends CommonDBTM {
   
   static $rightname                   = "plugin_financialreports";
   
   static function getTypeName($nb = 0) {

      return _n('Asset situation', 'Asset situations', $nb, 'financialreports');
   }

   
   function getItemsTotal($itemtype,$PluginFinancialreportsParameter,$type,$date,$total,$items,$locations_id) {
      $total+=$this->QueryItemsTotalValue($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id);
      if ($items=="") {
         $items=$this->queryItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id);
      } else {
         $items=array_merge($items,$this->queryItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id));
      }
      return $total;
   }
   function getItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$total,$items,$locations_id) {
      if ($items=="") {
         $items=$this->queryItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id);
      } else {
         $items=array_merge($items,$this->queryItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id));
      }
      return $items;
   }

   function queryItems($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id) {
      global $DB,$CFG_GLPI;
      
      $itemtable=getTableForItemType($itemtype);
      $modeltable = getTableForItemType($itemtype."Model");
      $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype."Model"));
      $typetable = getTableForItemType($itemtype."Type");
      $typefield = getForeignKeyFieldForTable(getTableForItemType($itemtype."Type"));
      $deleted=0;
      $first=true;
      $items="";

      $query="SELECT `$itemtable`.`name` AS ITEM_0, `glpi_locations`.`completename` AS ITEM_1, `$itemtable`.`otherserial` AS ITEM_2, `glpi_infocoms`.`buy_date` AS ITEM_3, `glpi_users`.`name` AS ITEM_4, `glpi_users`.`realname` AS ITEM_4_2, `glpi_users`.`id` AS ITEM_4_3, `glpi_users`.`firstname` AS ITEM_4_4,`glpi_groups`.`name` AS ITEM_5,`glpi_groups`.`id` AS ITEM_5_1,`$modeltable`.`name` AS ITEM_6 ";
      $query.= ", `glpi_manufacturers`.`name` AS ITEM_7, `glpi_infocoms`.`value` AS ITEM_8, `$itemtable`.`id` AS id, `glpi_locations`.`completename` AS ITEM_9,'$itemtype' AS TYPE
               FROM `$itemtable`
               LEFT JOIN `glpi_locations` ON (`$itemtable`.`locations_id` = `glpi_locations`.`id`)
               LEFT JOIN `glpi_infocoms` ON (`$itemtable`.`id` = `glpi_infocoms`.`items_id` AND `glpi_infocoms`.`itemtype` = '".$itemtype."')
               LEFT JOIN `glpi_users` ON (`$itemtable`.`users_id` = `glpi_users`.`id`)
               LEFT JOIN `glpi_groups` ON (`$itemtable`.`groups_id` = `glpi_groups`.`id`) ";

      $query.= "LEFT JOIN `$modeltable` ON (`".$itemtable."`.`$modelfield` = `$modeltable`.`id`) ";
      $query.= "LEFT JOIN `glpi_manufacturers` ON (`$itemtable`.`manufacturers_id` = `glpi_manufacturers`.`id`)
               LEFT JOIN `$typetable` ON (`$itemtable`.`$typefield` = `$typetable`.`id`)
               LEFT JOIN `glpi_states` ON (`$itemtable`.`states_id` = `glpi_states`.`id`)";
      $query.= " WHERE ";
      
      $item = new $itemtype();
      // Add deleted if item have it
      if ($item->maybeDeleted()) {
         $LINK= " " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$itemtable."`.`is_deleted` = '$deleted' ";
      }
      // Remove template items
      if ($item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$itemtable."`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($item->isEntityAssign()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}

         $query.=getEntitiesRestrictRequest($LINK,$itemtable);
      }
      $query_state= "SELECT `states_id`
                  FROM `glpi_plugin_financialreports_configs`";
      $result_state = $DB->query($query_state);
      if ($DB->numrows($result_state)>0) {
            $query .= "AND (`$itemtable`.`states_id` = 999999 ";
            while ($data_state=$DB->fetch_array($result_state)) {
               $type_where="OR `$itemtable`.`states_id` != '".$data_state["states_id"]."' ";
               $query .= " $type_where ";
            }
            $query .= ") ";
         }
      if (!empty($PluginFinancialreportsParameter->fields[$type]))
         $query.= " AND (`$itemtable`.`otherserial` LIKE '%".$PluginFinancialreportsParameter->fields[$type]."%%') ";

      $query.= " AND (`glpi_infocoms`.`buy_date` < '".$date."') ";
      
      if (!empty($locations_id)) {
         $query .= " AND " . getRealQueryForTreeItem('glpi_locations',$locations_id,"`$itemtable`.`locations_id`");
      }
      
      $query.="ORDER BY ITEM_3,ITEM_0 ASC";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      while ($data=$DB->fetch_array($result)) {
         $items[]=$data;
      }
      
      return $items;
   }

   function QueryItemsTotalValue($itemtype,$PluginFinancialreportsParameter,$type,$date,$locations_id) {
      global $DB,$CFG_GLPI;
      
      $deleted=0;
      $first=true;
      $itemtable=getTableForItemType($itemtype);
      $item = new $itemtype();
      $somme=0;
      $query_value="SELECT SUM(`glpi_infocoms`.`value`) AS Total_value
                  FROM `glpi_infocoms`,`$itemtable`
                  LEFT JOIN `glpi_states` ON (`$itemtable`.`states_id` = `glpi_states`.`id`) ";
      $query_value.= " WHERE `glpi_infocoms`.`items_id` = `$itemtable`.`id`
                  AND `glpi_infocoms`.`itemtype` = '".$itemtype."'";
      // Add deleted if item have it
      if ($item->maybeDeleted()) {
         $LINK= " AND " ;
         $query_value.= $LINK."`".$itemtable."`.`is_deleted` = '$deleted' ";
         if ($first) {$LINK=" ";$first=false;}
      }
      // Remove template items
      if ($item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query_value.= $LINK."`".$itemtable."`.`is_template` = '0' ";
      }
      // Add Restrict to current entities
      if ($item->isEntityAssign()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}

         $query_value.=getEntitiesRestrictRequest($LINK,$itemtable);
      }

      $query_state= "SELECT `states_id`
                  FROM `glpi_plugin_financialreports_configs`";
      $result_state = $DB->query($query_state);
      if ($DB->numrows($result_state)>0) {
         $query_value .= "AND (`$itemtable`.`states_id` = 999999 ";
         while ($data_state=$DB->fetch_array($result_state)) {
            $type_where="OR `$itemtable`.`states_id` != '".$data_state["states_id"]."' ";
            $query_value .= " $type_where ";
            }
         $query_value .= ") ";
      }
      if (!empty($type) && !empty($PluginFinancialreportsParameter->fields[$type]))
         $query_value.= " AND (`$itemtable`.`otherserial` LIKE '%".$PluginFinancialreportsParameter->fields[$type]."%%') ";

      $query_value.= " AND (`glpi_infocoms`.`buy_date` < '".$date."') ";
      
      if (!empty($locations_id)) {
         $query_value .= " AND " . getRealQueryForTreeItem('glpi_locations',$locations_id,"`$itemtable`.`locations_id`");
      }
      
      $result_value = $DB->query($query_value);
      if ($data_value=$DB->fetch_array($result_value)) {
         $somme=$data_value["Total_value"];
      }
      return $somme;
   }
   
   function selectItemsForDisposalQuery($locations_id) {
      global $DB;
      
      $items=array();
      $type1='Computer';
      $type2='Printer';
      $type3='NetworkEquipment';
      $type4='Peripheral';
      $type5='Monitor';
      $type6='Phone';

      $query= $this->queryDisposalItems($type1,$locations_id);
      $query .= " UNION ".$this->queryDisposalItems($type2,$locations_id);
      $query .= " UNION ".$this->queryDisposalItems($type3,$locations_id);
      $query .= " UNION ".$this->queryDisposalItems($type4,$locations_id);
      $query .= " UNION ".$this->queryDisposalItems($type5,$locations_id);
      $query .= " UNION ".$this->queryDisposalItems($type6,$locations_id);

      $query.=" ORDER BY ITEM_10,ITEM_2 ASC";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      while ($data=$DB->fetch_array($result)) {
         $items[]=$data;
      }
      return $items;
   }
   
   function queryDisposalItems($type,$locations_id) {
      global $DB,$CFG_GLPI;
      
      $first=true;
      $deleted=0;
      $modeltable = getTableForItemType($type."Model");
      $modelfield = getForeignKeyFieldForTable(getTableForItemType($type."Model"));
      $itemtable=getTableForItemType($type);
      
      $query="SELECT `".$itemtable."`.`name` AS ITEM_0, `glpi_locations`.`completename` AS ITEM_1, `".$itemtable."`.`otherserial` AS ITEM_2, `glpi_infocoms`.`buy_date` AS ITEM_3, `glpi_users`.`name` AS ITEM_4, `glpi_users`.`realname` AS ITEM_4_2, `glpi_users`.`id` AS ITEM_4_3, `glpi_users`.`firstname` AS ITEM_4_4,`glpi_groups`.`name` AS ITEM_5,`glpi_groups`.`id` AS ITEM_5_1,`$modeltable`.`name` AS ITEM_6 ";
      $query.= ", `glpi_manufacturers`.`name` AS ITEM_7, `glpi_infocoms`.`value` AS ITEM_8, `".$itemtable."`.`id` AS id,`".$itemtable."`.`comment` AS ITEM_9, `glpi_plugin_financialreports_disposalitems`.`date_disposal` AS ITEM_10,'$type' AS TYPE
            FROM `".$itemtable."`
            LEFT JOIN `glpi_plugin_financialreports_disposalitems` ON (`".$itemtable."`.`id` = `glpi_plugin_financialreports_disposalitems`.`items_id` AND `glpi_plugin_financialreports_disposalitems`.`itemtype` = '".$type."')
            LEFT JOIN `glpi_locations` ON (`".$itemtable."`.`locations_id` = `glpi_locations`.`id`)
            LEFT JOIN `glpi_infocoms` ON (`".$itemtable."`.`id` = `glpi_infocoms`.`items_id` AND `glpi_infocoms`.`itemtype` = '".$type."')
            LEFT JOIN `glpi_users` ON (`".$itemtable."`.`users_id` = `glpi_users`.`id`)
            LEFT JOIN `glpi_groups` ON (`".$itemtable."`.`groups_id` = `glpi_groups`.`id`) ";
      $query.= "LEFT JOIN `$modeltable` ON (`".$itemtable."`.`$modelfield` = `$modeltable`.`id`) ";
      $query.="LEFT JOIN `glpi_states` ON (`".$itemtable."`.`states_id` = `glpi_states`.`id`)
            LEFT JOIN `glpi_manufacturers` ON (`".$itemtable."`.`manufacturers_id` = `glpi_manufacturers`.`id`)";
      $query.="WHERE ";

      $item = new $type();
      // Add deleted if item have it
      if ($item->maybeDeleted()) {
         $LINK= " " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$itemtable."`.`is_deleted` = '$deleted' ";
      }
      // Remove template items
      if ($item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$itemtable."`.`is_template` = '0' ";
      }
      // Add Restrict to current entities
      if ($item->isEntityAssign()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}

         $query.=getEntitiesRestrictRequest($LINK,$itemtable);
      }
      $query_state= "SELECT `states_id`
                  FROM `glpi_plugin_financialreports_configs`";
      $result_state = $DB->query($query_state);
      if ($DB->numrows($result_state)>0) {
         $query .= "AND (`".$itemtable."`.`states_id` IS NULL ";
         while ($data_state=$DB->fetch_array($result_state)) {
            $type_where="OR `".$itemtable."`.`states_id` = '".$data_state["states_id"]."' ";
            $query .= " $type_where ";
         }
         $query .= ") ";
      }
      if (!empty($locations_id)) {
         $query .= " AND " . getRealQueryForTreeItem('glpi_locations',$locations_id,"`$itemtable`.`locations_id`");
      }
      
      return $query;
   }
   
   function displayReport($values,$display) {
      global $PDF,$DB,$CFG_GLPI;
      
      $default_values["date"]  = date("Y-m-d");
      $default_values["locations_id"] = 0;
      $default_values["start"] = 0;
      $default_values["id"] = 0;
      $default_values["export"] = false;

      foreach ($default_values as $key => $val) {
         if (isset($values[$key])) {
            $$key=$values[$key];
         }
      }
      
      $output_type=Search::HTML_OUTPUT;

      if (isset($values["display_type"]))
         $output_type=$values["display_type"];
         
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF = new PluginFinancialreportsPdf('L', 'mm', 'A4');
         $PDF->setDate($date);
         $PDF->AddPage();
      }
      $param=new PluginFinancialreportsParameter();
      $param->getFromDB('1');

      $first=false;
      $deleted=0;
      $master_total=0;
      $title_report=__('Asset situation ended on', 'financialreports')." ".Html::convdate($date);
      $start=0;
      $numrows=0;
      $end_display=$start+$_SESSION["glpilist_limit"];
      $nbcols=7;
      $parameters="date=".$date."&amp;locations_id=".$locations_id;
      
      foreach ($display as $key => $val) {
            $$key=$key;
      }
      
      if ($output_type==Search::HTML_OUTPUT) { // HTML display
         echo "<div align='center'><b>".$title_report."</b></div><br>";
         self::printPager($start,0,'',$parameters,1);
      }
    
      echo Search::showHeader($output_type,$end_display-$start+1,$nbcols,1); //table + div
    
      if (!empty($param->fields["computers_otherserial"]) || !empty($param->fields["notebooks_otherserial"]) || !empty($param->fields["servers_otherserial"])) {
         $itemtable=getTableForItemType('Computer');
         //////////////////////COMPUTERS///////////////
         $total=$this->getItemsTotal('Computer',$param,"computers_otherserial",$date,0,"",$locations_id);
         $items=$this->getItems('Computer',$param,"computers_otherserial",$date,0,"",$locations_id);
         $master_total+=$total;
         if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
            $PDF->affiche_tableau($total,$items,_n('Computer' , 'Computers' ,2));
         } else {
            $this->afficheTableau($date,$displaypc,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Computer' , 'Computers' ,2),$total,$items,$locations_id);
         }
         if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();
         //////////////////////PORTABLES///////////////
         $total=$this->getItemsTotal('Computer',$param,"notebooks_otherserial",$date,0,"",$locations_id);
         $items=$this->getItems('Computer',$param,"notebooks_otherserial",$date,0,"",$locations_id);
         $master_total+=$total;
         if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
            $PDF->affiche_tableau($total,$items,_n('Notebook' , 'Notebooks' ,2, 'financialreports'));
         } else {
            $this->afficheTableau($date,$displaynotebook,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Notebook' , 'Notebooks' , 2, 'financialreports'),$total,$items,$locations_id);
         }
         if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();
         //////////////////////SERVERS///////////////
         $total=$this->getItemsTotal('Computer',$param,"servers_otherserial",$date,0,"",$locations_id);
         $items=$this->getItems('Computer',$param,"servers_otherserial",$date,0,"",$locations_id);
         $master_total+=$total;
         if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
            $PDF->affiche_tableau($total,$items,_n('Server' , 'Servers' ,2 , 'financialreports'));
         } else {
            $this->afficheTableau($date,$displayserver,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Server' , 'Servers' ,2, 'financialreports'),$total,$items,$locations_id);
         }
         if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();
      //No config
      } else {
         $itemtable=getTableForItemType('Computer');
         //////////////////////ALL COMPUTERS///////////////
         $total=$this->getItemsTotal('Computer',$param,"no_value",$date,0,"",$locations_id);
         $items=$this->getItems('Computer',$param,"no_value",$date,0,"",$locations_id);
         $master_total+=$total;
         if ($total > 1) {
         if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
            $PDF->affiche_tableau($total,$items,_n('Computer' , 'Computers' ,2));
         } else {
            $this->afficheTableau($date,$displaypc,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Computer' , 'Computers' ,2),$total,$items,$locations_id);
         }
         }
         if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();
      }

      //////////////////////MONITORS///////////////
      $itemtable=getTableForItemType('Monitor');
      $total=$this->getItemsTotal('Monitor',$param,"monitors_otherserial",$date,0,"",$locations_id);
      $items=$this->getItems('Monitor',$param,"monitors_otherserial",$date,0,"",$locations_id);
      $master_total+=$total;
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Monitor' , 'Monitors' ,2));
      } else {
         $this->afficheTableau($date,$displaymonitor,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Monitor' , 'Monitors' ,2),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();

      //////////////////////PRINTERS///////////////
      $itemtable=getTableForItemType('Printer');
      $total=$this->getItemsTotal('Printer',$param,"printers_otherserial",$date,0,"",$locations_id);
      $items=$this->getItems('Printer',$param,"printers_otherserial",$date,0,"",$locations_id);
      $master_total+=$total;
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Printer' , 'Printers' ,2));
      } else {
         $this->afficheTableau($date,$displayprinter,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Printer' , 'Printers' ,2),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();

      //////////////////////NETWORK///////////////
      $itemtable=getTableForItemType('NetworkEquipment');
      $total=$this->getItemsTotal('NetworkEquipment',$param,"networkequipments_otherserial",$date,0,"",$locations_id);
      $items=$this->getItems('NetworkEquipment',$param,"networkequipments_otherserial",$date,0,"",$locations_id);
      $master_total+=$total;
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Network device' , 'Network devices' ,2));
      } else {
         $this->afficheTableau($date,$displaynetworking,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Network device' , 'Network devices' ,2),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();

      //////////////////////PERIPHERIQUES///////////////
      $itemtable=getTableForItemType('Peripheral');
      $total=$this->getItemsTotal('Peripheral',$param,"peripherals_otherserial",$date,0,"",$locations_id);
      $items=$this->getItems('Peripheral',$param,"peripherals_otherserial",$date,0,"",$locations_id);
      $master_total+=$total;
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Device' , 'Devices' ,2));
      } else {
         $this->afficheTableau($date,$displayperipheral,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Device' , 'Devices' ,2),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();

      //////////////////////PHONES///////////////
      $itemtable=getTableForItemType('Phone');
      $total=$this->getItemsTotal('Phone',$param,"phones_otherserial",$date,0,"",$locations_id);
      $items=$this->getItems('Phone',$param,"phones_otherserial",$date,0,"",$locations_id);
      $master_total+=$total;
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Phone' , 'Phones' ,2));
      } else {
         $this->afficheTableau($date,$displayphone,$output_type,$itemtable,$end_display,$start,$nbcols,_n('Phone' , 'Phones' ,2),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();
      //////////////////////SORTIS///////////////
      $total=-1;
      $items="";
      $items=$this->selectItemsForDisposalQuery($locations_id);

      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $PDF->affiche_tableau($total,$items,_n('Element out' , 'Elements out' , 2, 'financialreports'),1);
      } else {
         $this->afficheTableau($date,$displaydisposal,$output_type,"disposal",$end_display,$start,$nbcols,_n('Element out' , 'Elements out' , 2, 'financialreports'),$total,$items,$locations_id);
      }
      if ($total!=0 && $output_type==Search::PDF_OUTPUT_LANDSCAPE) $PDF->AddPage();

      //////////END////////////////

      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         //////////Total général////////////////
         $PDF->affiche_tableau_fin($master_total);
         //////////END////////////////
         $PDF->Output();
      }
      if ($output_type==Search::HTML_OUTPUT) {
         echo "<br>";
         echo Search::showHeader($output_type,1,1,1);
      } else {
         echo Search::showNewLine($output_type);
         echo Search::showEndLine($output_type);
      }
      $row_num=6000;
      $item_num=1;
      echo Search::showNewLine($output_type,$row_num%2);
      echo Search::showItem($output_type,__('General Total', 'financialreports'),$item_num,$row_num);
      echo Search::showItem($output_type,Html::formatNumber($master_total)." "._n('Euro' , 'Euros' , 2 , 'financialreports'),$item_num,$row_num);
      echo Search::showEndLine($output_type);

      $title="";
      // Create title
      if ($output_type==Search::PDF_OUTPUT_LANDSCAPE) {
         $title.=$title_report;
      }
      // Display footer
      if ($output_type==Search::HTML_OUTPUT) {
         echo "</table></div>";
      }
   }

   function afficheTableau($date,$display,$output_type,$itemtable,$end_display,$start,$nbcols,$titre,$total,$items,$locations_id) {
      global $DB,$CFG_GLPI;
    
      $first=true;
      $deleted=0;
      $master_total=0;
      
      $master_total+=$total;
      if ($total!=0) {
         if ($output_type==Search::HTML_OUTPUT) {
            echo "<br>";
            echo Search::showHeader($output_type,$end_display-$start+1,$nbcols,1);
         } else {
            echo Search::showNewLine($output_type);
            echo Search::showEndLine($output_type);
         }
         echo Search::showNewLine($output_type); //tr

         if ($output_type==Search::HTML_OUTPUT) {
            if ($total!=-1) {
               echo "<th>".$titre."</th><th><font color='red'>"
               .Html::formatNumber($total)." "._n('Euro' , 'Euros', 2 , 'financialreports')."</font></th><th>";
            } else {
               echo "<th>".$titre."</th><th></th><th>";
            }
            if ($_SESSION[$display])
               $status = "false";
            else
               $status = "true";

            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/financialreports/front/financialreport.php?"
            .$display."=" . $status . "&date=".$date. "&locations_id=".$locations_id."'>";
            if ($_SESSION[$display])
               echo __('Hide', 'financialreports');
            else
               echo __('Display', 'financialreports');
            echo "</a>";

            if ($itemtable!='disposal') {
               echo "</th><th colspan='4'><th>";
            } else {
               echo "</th><th colspan='3'><th>";
            }
         } else {
            echo Search::showHeaderItem($output_type,$titre,$header_num);//th
            echo Search::showHeaderItem($output_type,__('Total'),$header_num);
            if ($total!=-1) {
               echo Search::showHeaderItem($output_type,Html::formatNumber($total)." "._n('Euro' , 'Euros', 2 , 'financialreports'),$header_num);
            }
         }

         echo Search::showEndLine($output_type);//tr

         echo Search::showNewLine($output_type);
         $header_num=1;

         echo Search::showHeaderItem($output_type,__('Name'),$header_num);
         echo Search::showHeaderItem($output_type,__('Inventory number'),$header_num);
         echo Search::showHeaderItem($output_type,__('Date of purchase'),$header_num);

         if ($itemtable!='disposal') {
            echo Search::showHeaderItem($output_type,__('User / Group', 'financialreports'),$header_num);
            echo Search::showHeaderItem($output_type,__('Location'),$header_num);
         }
         echo Search::showHeaderItem($output_type,__('Model'),$header_num);
         echo Search::showHeaderItem($output_type,__('Supplier'),$header_num);

         if ($itemtable=='disposal') {
            echo Search::showHeaderItem($output_type,__('Disposal date', 'financialreports'),$header_num);
            echo Search::showHeaderItem($output_type,__('Comments'),$header_num);
         } else {
            echo Search::showHeaderItem($output_type,__('Purchase Price HT in', 'financialreports')." "._n('Euro' , 'Euros', 2, 'financialreports'),$header_num);
         }
         // End Line for column headers
         echo Search::showEndLine($output_type);

         $row_num=1;
         if ($_SESSION[$display]) {

            foreach ($items as $data) {
               $row_num++;
               $item_num=1;

               echo Search::showNewLine($output_type,$row_num%2);
               //name
               $link=Toolbox::getItemTypeFormURL($data["TYPE"]);
               $output_iddev="<a href='".$link."?id=".$data["id"]."'>".$data["ITEM_0"].($_SESSION["glpiis_ids_visible"]?" (".$data["id"].")":"")."</a>";
               echo Search::showItem($output_type,$output_iddev,$item_num,$row_num);
               //otherserial
               echo Search::showItem($output_type,$data["ITEM_2"],$item_num,$row_num);
               //buy_date
               echo Search::showItem($output_type,Html::convdate($data["ITEM_3"]),$item_num,$row_num);

               if ($itemtable!='disposal') {
                  //user
                  $username_computer=formatUserName($data["ITEM_4_3"],$data["ITEM_4"],$data["ITEM_4_2"],$data["ITEM_4_4"]);
                  $output_iduser="<a href='".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$data["ITEM_4_3"]."'>".$username_computer."</a>";
                  if ($data["ITEM_4_3"] && $data["ITEM_5"]) {
                     $output_iduser.=" / <a href='".$CFG_GLPI["root_doc"]."/front/group.form.php?id=".$data["ITEM_5_1"]."'>".$data["ITEM_5"].($_SESSION["glpiis_ids_visible"]?" (".$data["ITEM_5_1"].")":"")."</a>";
                  } else if (!isset($data["ITEM_4_3"]) && $data["ITEM_5"]) {
                     $output_iduser="<a href='".$CFG_GLPI["root_doc"]."/front/group.form.php?id=".$data["ITEM_5_1"]."'>".$data["ITEM_5"].($_SESSION["glpiis_ids_visible"]?" (".$data["ITEM_5_1"].")":"")."</a>";
                  } else if (!isset($data["ITEM_4_3"]) && !isset($data["ITEM_5"])) {
                     $output_iduser=__('In stock / available', 'financialreports');
                  }

                  echo Search::showItem($output_type,$output_iduser,$item_num,$row_num);
                  //location
                  echo Search::showItem($output_type,$data["ITEM_9"],$item_num,$row_num);
               }
               //model
               echo Search::showItem($output_type,$data["ITEM_6"],$item_num,$row_num);
               //manufacturer
               echo Search::showItem($output_type,$data["ITEM_7"],$item_num,$row_num);

               if ($itemtable=='disposal') {
                  //comments
                  echo Search::showItem($output_type,Html::convdate($data["ITEM_10"]),$item_num,$row_num);
                  echo Search::showItem($output_type,nl2br($data["ITEM_9"]),$item_num,$row_num);
               } else {
                  //value
                  if ($output_type==Search::HTML_OUTPUT) {
                     $ouput_value="<font color='red'>".Html::formatNumber($data["ITEM_8"])."</font>";
                  } else {
                     $ouput_value=Html::formatNumber($data["ITEM_8"]);
                  }
                  echo Search::showItem($output_type,$ouput_value,$item_num,$row_num);
               }
               echo Search::showEndLine($output_type);
            }
         }
         echo Search::showFooter($output_type);
      }

      return $master_total;
   }
   
   static function printPager($start,$numrows,$target,$parameters,$item_type_output=0,$item_type_output_param=0) {
      global $CFG_GLPI;

      $list_limit=$_SESSION['glpilist_limit'];
      // Forward is the next step forward
      $forward = $start+$list_limit;

      // This is the end, my friend
      $end = $numrows-$list_limit;

      // Human readable count starts here
      $current_start=$start+1;

      // And the human is viewing from start to end
      $current_end = $current_start+$list_limit-1;
      if ($current_end>$numrows) {
         $current_end = $numrows;
      }

      // Backward browsing
      if ($current_start-$list_limit<=0) {
         $back=0;
      } else {
         $back=$start-$list_limit;
      }

      // Print it
      
      echo "<form method='POST' action=\"".$CFG_GLPI["root_doc"].
                                 "/plugins/financialreports/front/report.dynamic.php\" target='_blank'>\n";
                                 
      echo "<table class='tab_cadre_pager'>\n";
      echo "<tr>\n";

      if (isset($_SESSION["glpiactiveprofile"])
         &&$_SESSION["glpiactiveprofile"]["interface"]=="central") {
         echo "<td class='tab_bg_2' width='30%'>" ;
         
         echo "<input type='hidden' name='item_type' value='$item_type_output'>";
         if ($item_type_output_param!=0)
            echo "<input type='hidden' name='item_type_param' value='".
                                             serialize($item_type_output_param)."'>";
         $explode=explode("&amp;",$parameters);
         for ($i=0;$i<count($explode);$i++) {
            $pos=strpos($explode[$i],'=');
            echo "<input type='hidden' name=\"".substr($explode[$i],0,$pos)."\" value=\"".
                                             substr($explode[$i],$pos+1)."\">";
         }
         echo "<select name='display_type'>";
      echo "<option value='".Search::CSV_OUTPUT."'>".__('All pages in CSV')."</option>";
      echo "<option value='-".Search::PDF_OUTPUT_LANDSCAPE."'>".__('All pages in landscape PDF').
           "</option>";
         echo "</select>&nbsp;";
         echo "<input type='image' onClick=\"window.location.reload()\" name='export'  src='".$CFG_GLPI["root_doc"]."/pics/greenbutton.png'
                title=\"".__s('Export')."\" value=\"".__s('Export')."\">";
         echo "</td>" ;
      }

      // End pager
      echo "</tr>\n";
      echo "</table><br>\n";
      Html::closeForm();
   }
   
   function getRights($interface='central') {

      $values = parent::getRights();

      unset($values[CREATE], $values[UPDATE], $values[DELETE], $values[PURGE]);
      return $values;
   }
}
?>