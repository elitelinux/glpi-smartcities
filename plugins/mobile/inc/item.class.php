<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define("DISPLAYLEFTPANEL", false);


class PluginMobileItem extends CommonDBTM {
	
      
   public function displayItem($ID, $itemtype) {
      global $LANG,$CFG_GLPI, $ID;         
      
      $welcome = "&nbsp;";
        
      if (class_exists($itemtype)) {
         $classname = ucfirst($itemtype);
         $obj = new $classname;
         $welcome = $obj->getTypeName()." (".$_REQUEST['id'].")";
      }
       
      $table = $obj->getTable();
      
      //saveActiveProfileAndApplyRead();
      
      $common = new PluginMobileCommon;
      $common->displayHeader($welcome, "search.php?menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&itemtype=$itemtype", '', '', 'item');
      
      echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/mobile/front/item.form.php?menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&itemtype=$itemtype&id=$ID"."' method='post'>";
             
     
      if (largeScreen() && DISPLAYLEFTPANEL) {
      //if (true) {
      //if (false) { 
 
         /*ob_start();
         $obj->showForm($ID);  
         $out = ob_get_contents();      
         ob_end_clean();
         
         //delete script         
         $out = preg_replace('/((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))/i', '', $out);
         
         //hide tooltips
         $out = preg_replace('/(<a id=\'tooltip[^>])(.*?)(<\/a>)/', '', $out);
         $out = preg_replace('/(<img id=\'tooltip[^>])(.*?)(>)/', '', $out);
         
         //parse menu navigate
         $out = preg_replace('/(<a)(.*?)(search\.php[^>])(.*?)(<\/a>&nbsp;\:)/', '', $out);      
         $out = preg_replace('/(item\.php\?id=[0-9]*)/', '$0'."&menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&itemtype=$itemtype", $out);     
         $out = preg_replace('/(<a[^.]*showHideDiv[^>])(.*?)(<\/a>)/', '', $out);         
         preg_match_all('/(<div id=\'menu_navigate[^>])(.*?)(<\/div>)/', $out, $matches);
         $out = preg_replace('/(<div id=\'menu_navigate[^>])(.*?)(<\/div>)/', '', $out);
         
         //show select in native 
         $out = str_replace('<select', "<select data-native-menu='true'", $out);           
         
         $common->displayTabBar($matches[0]);
                  
         ob_start();
         $this->showButtons($itemtype, $ID);
         $buttons_form = ob_get_contents();      
         ob_end_clean();
         $out .= $buttons_form;

         echo $out;*/
         
         $savecolslimit = $_SESSION['plugin_mobile']['cols_limit'];
         $saverowslimit = $_SESSION['plugin_mobile']['rows_limit'];
         $_SESSION['plugin_mobile']['cols_limit'] = 1;
         $_SESSION['plugin_mobile']['rows_limit'] = 50;
         $_GET['showheader'] = false;
         
         echo "<div class='ui-grid-a' style='position:relative;'>";
         echo "<div id='left_panel' class='ui-block-a'>";
         
         echo "<div data-role='header' data-backbtn='false' data-theme='a' data-id='TabBar'>";   
         echo "<div data-theme='c' class='ui-btn-right' style='top:0' data-position='inline'>";
         echo "<a data-role='button' data-theme='c'>test</a>";
         echo "</div></div>";
         
         echo "<div style='height:600px;'>";
         PluginMobileSearch::manageGetValues($itemtype);
			//Search::manageParams($itemtype);
         $numrows = PluginMobileSearch::show(ucfirst($itemtype));
         echo "</div>";
         echo "</div>";
         
         echo "<div class='ui-block-b' id='right_panel'>";
         
         PluginMobileTab::displayTabBar();
         echo "<div style='height:570px;'>";
         
         $_SESSION['plugin_mobile']['cols_limit'] = $savecolslimit;
         $_SESSION['plugin_mobile']['rows_limit'] = $saverowslimit;
         
      } else PluginMobileTab::displayTabBar();
      
      $obj->getFromDB($ID);
      $p['itemtype'] = $_REQUEST['itemtype'];
      //$p['itemtype'] = '';
      $p['id'] = false;
      $p['return_fields'] = array();
      $p['show_label'] = true;
      $p['show_name'] = true;
      
      foreach ($params as $key => $value) {
         $p[$key] = $value;
      }


      if (largeScreen()) {
         $obj = $this->removeBlacklistedField($obj);
         $nb_items = count($obj->fields);
                  
         $tmp = array_chunk($obj->fields, ceil($nb_items/2), true);
         $fields1 = $tmp[1];
         $fields2 = $tmp[0];
         
         $params1 = array('data'      => $fields1,
                      'options'       => $p,
                      'searchOptions' => $obj->getSearchOptions(),
                      'itemtype'      => $itemtype);
         $params2 = $params1;
         $params2['data'] = $fields2;
                      
         $this->getLabels($params1, $labels1);
         $this->getLabels($params2, $labels2);
         
         //echo $fields1 ."<br>";
         //echo count($labels2)."<br>";
         
         echo "<div class='ui-grid-a' id='tablet-grid'>";
         echo "<div class='ui-block-a'>";
         $this->showList($labels1, $itemtype);
         echo "</div>";
         echo "<div class='ui-block-b'>";
         $this->showList($labels2, $itemtype);
         echo "</div>";
         echo "</div>";
      }
       
      else {
         $params = array('data'       => $obj->fields,
                      'options'       => $p,
                      'searchOptions' => $obj->getSearchOptions(),
                      'itemtype'      => $itemtype);
         $this->getLabels($params, $labels);
         unset($labels['id']);
         $this->showList($labels, $itemtype);
      }
      if (editMode()) $this->showButtons($itemtype, $ID);
   
      if (largeScreen() && DISPLAYLEFTPANEL) {
         echo "</div></div></div>";
      }
      // restoreActiveProfile();
      
      //Close Form
      //echo "</form>";
      Html::closeForm();
            
      $common->displayFooter();    
   }
   
   
   public function showList($fields, $itemtype) {
      $classname = ucfirst($itemtype);
      $obj = new $classname;
      $table = $obj->getTable();
      
      $readonlyFields = array('date_mod');
      
      $searchOptions = Search::getOptions($itemtype);
      
      echo "<ul data-role='listview' data-theme='c'>";    
      
      foreach($fields as $key => $row) {
         if (strpos($key, '_label') !== false || strpos($key, '_name') !== false) continue;
         
         echo "<li data-role='list-divider'>".$fields[$key."_label"]."&nbsp;:</li>";
         echo "<li>";
          
         $itemSearchOptions = $searchOptions[$this->getOptionNumber($searchOptions, $itemtype, $key, $fields[$key."_label"])];
         if (editMode() && !in_array($key, $readonlyFields)) {
            if ($itemSearchOptions['table'] != $table) {
               Dropdown::show(getItemTypeForTable($itemSearchOptions['table']),
                              array('value'     => $row,
                                    'name'      => $key,
                                    'rand'      => "' data-native-menu='true",
                                    'comments'  => false));
            } elseif(isset($itemSearchOptions['searchtype']) && $itemSearchOptions['searchtype'] == "equals") {
               $this->showEquals($itemSearchOptions, $row);
            } elseif(isset($itemSearchOptions['datatype']) && $itemSearchOptions['datatype'] == "bool") {
               $this->showBool($itemSearchOptions, $row);
            } elseif(isset($itemSearchOptions['datatype']) && $itemSearchOptions['datatype'] == "date") {
               echo "<input type='text' data-role='date' name='$key' value='$row' />";
            } else echo "<input type='text' name='$key' value='$row' />";
         } else {
            if ($itemSearchOptions['table'] != $table) {
               echo Dropdown::getDropdownName($itemSearchOptions['table'], $row);
            } elseif(isset($itemSearchOptions['searchtype']) && $itemSearchOptions['searchtype'] == "equals") {
               $this->showEquals($itemSearchOptions, $row, false);
            } elseif(isset($itemSearchOptions['datatype']) && $itemSearchOptions['datatype'] == "bool") {
               $this->showBool($itemSearchOptions, $row, false);
            } else {
               $name = 'N/A';
               $tmp_name = str_replace('_id', '', $key."_name");
               if (isset($fields[$tmp_name])) $name = $fields[$tmp_name];
               if ($name != 'N/A') echo $name;
               else echo $row;
            }
         }
         
         echo "</li>";
      }
      echo "</ul>";
   }
   
   
   public function getLabels($params, &$output) {
      global $LANG;
            
      $p['searchOptions'] = array();
      $p['data'] = array();
      $p['options'] = array();
      $p['subtype'] = false;

      foreach ($params as $key => $value) {
         $p[$key] = $value;
      }
      
      $p['table']          = getTableForItemType($p['itemtype']);
      $p['show_label']     = $p['options']['show_label'];
      $p['show_name']      = $p['options']['show_name'];
      $p['return_fields']  = $p['options']['return_fields'];

      $p['searchOptions'][999]['table']       = $p['table'];
      $p['searchOptions'][999]['field']       = 'id';
      $p['searchOptions'][999]['linkfield']   = 'id';
      $p['searchOptions'][999]['name']        = $LANG['login'][6];

      $tmp = array();
      foreach($p['searchOptions'] as $id => $option) {
         if (isset($option['table'])) {
            if (!isset($option['linkfield']) || empty($option['linkfield'])) {
               if ($p['table'] == $option['table']) {
                  $linkfield = $option['field'];
               } else {
                  $linkfield = getForeignKeyFieldForTable($p['table']);
               }
            } else {
               $linkfield = $option['linkfield']; ///////*********///
            }
            
            
            //if (isset($p['data'][$linkfield])
            if ( $p['data'][$linkfield] != ''
                     && (empty($p['return_fields'][$p['options']['itemtype']])
                        || (!empty($p['return_fields'][$p['options']['itemtype']])
                           && in_array($linkfield,$p['return_fields'][$p['options']['itemtype']])))) {
               $tmp[$linkfield] = $p['data'][$linkfield];
               if ($p['show_label']) {
                  $tmp[$linkfield."_label"] = $option['name'];
               }
               if ($p['show_name']) {
                  
                  if (self::isForeignKey($linkfield) 
                        && (!isset($option['datatype']) 
                           || isset($option['datatype']) && $option['datatype'] != 'itemlink')) {
                     $option_name = str_replace("_id","_name",$linkfield);
                     $result = Dropdown::getDropdownName($option['table'],
                                                         $p['data'][$linkfield]);
                     if ($result != '&nbsp;') {
                        $tmp[$option_name] = $result;
                     }
                  } else {
                      //Should exists if we could get results directly from the search engine...
                      if (isset($option['datatype'])) {
                        $option_name = $linkfield."_name";
                        switch ($option['datatype']) {
                           case 'date':
                              $tmp[$linkfield] = Html::convDateTime($p['data'][$linkfield]);
                              break;
                           case 'bool':
                              $tmp[$option_name] = Dropdown::getYesNo($p['data'][$linkfield]);
                              break;
                     /*      case 'itemlink':
                                 if (isset($option['itemlink_type'])) {
                                    $obj = new $option['itemlink_type']();
                                 } else {
                                    $obj = new $option['itemlink_link']();
                                 }
                                 $obj->getFromDB($p['data'][$linkfield]);
                                 $tmp[$linkfield] = $p['data'][$linkfield];
                                 $tmp[$option_name] = $obj->getField($option['field']);
                              break;
                        */      
                           case 'itemtype':
                              if (class_exists($p['data'][$linkfield])) {
                                 $obj = new $p['data'][$linkfield];
                                 $tmp[$option_name] = $obj->getTypeName();
                              }
                              break;
                        }
                     }
                  }
               }
            }
         }
      }
      if (!empty($tmp)) {
         $output = $tmp;
      }
   }
   
   
   static public function isForeignKey($field) {
      if (preg_match("/s_id/",$field)) {
         return true;
      } else {
         return false;
      }
   }
   
   
   function showButtons ($itemtype, $id, $options = array()) {
      global $LANG, $CFG_GLPI;
            
      if (class_exists($itemtype)) {
         $classname = ucfirst($itemtype);
         $obj = new $classname;
         $obj->getFromDB($id);
      } else return false;

      // for single object like config
      if (isset($obj->fields['id'])) {
         $ID = $obj->fields['id'];
      } else {
        $ID = 1;
      }
      $params['colspan'] = 2;
      $params['withtemplate'] = '';
      $params['candel'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key]=$val;
         }
      }
      if (!$obj->can($ID,'w')) {
         return false;
      }
      
      if ($ID>0) {
         echo "<input type='hidden' name='id' value='$ID'>";
      }
      
      echo "<div data-role='footer' data-position='fixed' data-theme='c' data-id='item-footer'>";
      echo "<div data-role='navbar'><ul>";
      if ($params['candel'] && !$obj->can($ID,'d')) {
         $params['candel'] = false;
      }
      if ($params['candel']) {
         echo "<li><input type='submit' name='update' value=\""
            .$LANG['buttons'][7]."\" class='submit' data-icon='check' data-theme='a'></li>";
         if ($obj->isDeleted()){
            echo "<li><input type='submit' name='restore' value=\"".$LANG['buttons'][21]
               ."\" data-icon='back' data-theme='a'></li>";
            echo "<li><input type='submit' name='purge' value=\"" .$LANG['buttons'][22]
               ."\" data-icon='delete' data-theme='a'></li>";
         }else {
            if (!$obj->maybeDeleted()) {
               echo "<li><input type='submit' name='delete' value=\""
                  .$LANG['buttons'][22]."\" OnClick='return window.confirm(\"" 
                  .$LANG['common'][50]. "\");' data-icon='delete' data-theme='a'></li>";
            } else {
               echo "<li><input type='submit' name='delete' value=\""
                  .$LANG['buttons'][6]."\" data-icon='delete' data-theme='a'></li>";
            }
         }
      } else {
         echo "<li><input type='submit' name='update' value=\""
            .$LANG['buttons'][7]."\" data-icon='check' data-theme='a'></li>";
      }
      
      echo "</ul></div>";
      echo "</div>";

      
   }
   
   public function getOptionNumber($opts, $itemtype, $field_key, $label) {
      foreach ($opts as $num => $opt) {
         if (($opt['linkfield']==$field_key && $opt['name']==$label) || $opt['field']==$field_key) {
            return $num;
         }
      }
      
     /* foreach ($opts as $num => $opt) {
         if ($opt['linkfield']==$field_key || $opt['field']==$field_key) {
            return $num;
         }
      }*/
   } 
   
   
   public function showNavigation() {
      
   }
   
   public function showEquals($searchopt, $value, $edit = true) {
      $inputname = $searchopt['linkfield'];
      
      switch ($searchopt['table'].".".$searchopt['linkfield']) {
         case "glpi_tickets.status" :
            if ($edit) Ticket::dropdownStatus($inputname,$value,1);
            else echo Ticket::getStatus($value);
            break;
         case "glpi_tickets.priority" :
            if ($edit) Ticket::dropdownPriority($inputname,$value,true,true);
            else echo Ticket::getPriorityName(trim($value));
            break;
         case "glpi_tickets.impact" :
            if ($edit) Ticket::dropdownImpact($inputname,$value,true);
            else echo Ticket::getImpactName($value);
            break;
         case "glpi_tickets.urgency" :
            if ($edit) Ticket::dropdownUrgency($inputname,$value,true);
            else echo Ticket::getUrgencyName($value);
            break;
         case "glpi_tickets.global_validation" :
            if ($edit) TicketValidation::dropdownStatus($inputname,array('value'=>$value,'all'=>1));
            else TicketValidation::getStatus($value);
            break;
         case "glpi_users.name":
            if ($edit) User::dropdown(array('name'      => $inputname,
                                 'value'     => $value,
                                 'comments'  => false,
                                 'all'       => -1,
                                 'right'     => 'all'));
            else echo getUserName($value);
            break;
         case "glpi_ticketvalidations.status" :
            if ($edit) TicketValidation::dropdownStatus($inputname,array('value'=>$value,'all'=>1));
            else echo TicketValidation::getStatus($value);
            break;
      }
   }
   
   public function showBool($searchopt, $value, $edit = true) {
      if ($edit) Dropdown::showYesNo($searchopt['linkfield'], $value);
      else echo Dropdown::getYesNo($value);
   }
   
   public function removeBlacklistedField($obj) {
      $blacklisted_fields = array('items_id', 'id');
      
      foreach($obj->fields as $key => $value) {
         if (in_array($key, $blacklisted_fields)) unset($obj->fields[$key]);
      }
      
      return $obj;
   }
   
}
