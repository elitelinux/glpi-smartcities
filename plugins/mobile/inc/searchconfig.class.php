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

class PluginMobileSearchconfig extends CommonDBTM {
   
   public static function getTable() {
      return "glpi_displaypreferences";
   }
   
   
   public function showSearchConfigList($itemtype, $type = 'global') {
      global $LANG,$CFG_GLPI,$DB;
      
      $IDuser = 0;
      if ($type == 'perso') $IDuser=Session::getLoginUserID();
      
      $searchopt=Search::getOptions($itemtype);
      if (!is_array($searchopt)) {
         return false;
      }
      
      $item = NULL;
      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }
      
      $global_write=Session::haveRight("search_config_global","w");
      
      // Defined items
      
      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `itemtype` = '$itemtype'
                     AND `users_id` = '$IDuser'
                ORDER BY `rank`";
      $result=$DB->query($query);
      $numrows=0;
      $numrows=$DB->numrows($result);
      
      if ($numrows==0) {
         Session::checkRight("search_config","w");
         echo $LANG['setup'][241]."&nbsp;&nbsp;&nbsp;";
         echo "<a href='searchconfig.form.php?type=$type&amp;itemtype=$itemtype&amp;users_id=$IDuser&amp;activate' data-role='button' data-inline='true' data-theme='a'>".$LANG['buttons'][2]."</a>";
      } else {
      
         $already_added = DisplayPreference::getForTypeUser($itemtype,$IDuser);
         
         echo $LANG['setup'][252];
         
         echo "<form method='post' action='".$CFG_GLPI["root_doc"]."/plugins/mobile/front/searchconfig.form.php?type=$type&amp;itemtype=$itemtype&amp;users_id=$IDuser'>";
         
         //echo "<form method='post' action='../../../plugins/mobile/front/searchconfig.form.php?type=$type&amp;itemtype=$itemtype&amp;users_id=$IDuser'>";
         echo "<input type='hidden' name='users_id' value='$IDuser'>";
         echo "<select name='num'>";
         $first_group=true;
         $searchopt=Search::getCleanedOptions($itemtype);
         foreach ($searchopt as $key => $val) {
            if (!is_array($val)) {
               if (!$first_group) {
                  echo "</optgroup>\n";
               } else {
                  $first_group=false;
               }
               echo "<optgroup label='$val'>";
            } else if ($key!=1 && !in_array($key,$already_added)) {
               echo "<option value='$key'>".$val["name"]."</option>";
            }
         }
         if (!$first_group) {
            echo "</optgroup>\n";
         }
         echo "</select>";
         echo "<input type='submit' name='add' value='".$LANG['buttons'][8]."' data-inline='true' data-theme='a'>";
         //echo "</form>";
         Html::closeForm();
         
         
         //print list
         echo "<ul data-role='listview' data-inset='true' data-theme='d' data-dividertheme='a'>";
            // print first element
            echo "<li>".$searchopt[1]["name"]."</li>";
            
            // print entity
            if (Session::isMultiEntitiesMode()
            && (isset($CFG_GLPI["union_search_type"][$itemtype])
               || ($item && $item->maybeRecursive())
               || count($_SESSION["glpiactiveentities"])>1)
            && isset($searchopt[80])) {
               echo "<li>".$searchopt[80]["name"]."</li>";
            }
            
            $i=0;
            if ($numrows) {
               while ($data=$DB->fetch_array($result)) {
                  if ($data["num"]!=1 && isset($searchopt[$data["num"]])) {
                     echo "<li>";
                     echo $searchopt[$data["num"]]["name"];
                     
                     if ($global_write) {
                        echo "<span class='right_searchconfig'>";
                        if ($i!=0) {
                           echo "<a data-role='button' data-icon='arrow-u' data-inline='true' "
                           ."href='searchconfig.form.php?type=$type&amp;itemtype=$itemtype"
                           ."&amp;users_id=$IDuser&amp;up&amp;id=".$data['id']
                           ."&amp;rand=".mt_rand()."'>&nbsp;</a>";
                        } 
                        
                        if ($i!=$numrows-1) {
                           echo "<a data-role='button' data-icon='arrow-d' data-inline='true' "
                           ."href='searchconfig.form.php?type=$type&amp;itemtype=$itemtype"
                           ."&amp;users_id=$IDuser&amp;down&amp;id=".$data['id']
                           ."&amp;rand=".mt_rand()."'>&nbsp;</a>";
                        } 
                        
                        echo "<a data-role='button' data-icon='delete' data-inline='true' "
                        ."href='searchconfig.form.php?type=$type&amp;itemtype=$itemtype"
                        ."&amp;users_id=$IDuser&amp;delete&amp;id=".$data['id']."'>"
                        ."&nbsp;</a>";
                        
                        echo "</span>";
                     }
                     echo "</li>";
                     $i++;
                  }
               }
            }
            
            
         echo "</ul>";
      }
      
      $this->showNavBar($itemtype, $type);
   }
   
   public function showNavBar($itemtype, $type) {
      global $LANG, $CFG_GLPI; 
      
      if ($type == 'global') {
         $hrefGlobal = "#";
         $hrefPerso = "searchconfig.php?itemtype=$itemtype&type=perso";
         $themeGlobal = "data-theme='d' class='ui-disabled'";
         $themePerso = "";
      } else {
         $hrefGlobal = "searchconfig.php?itemtype=$itemtype&type=global";
         $hrefPerso = "#";
         $themeGlobal = "";
         $themePerso = "data-theme='d' class='ui-disabled'";
      }
      
      echo "<div data-role='footer' data-position='fixed' data-theme='c'>";
      echo "<div data-role='navbar'>";
      echo "<ul>";
      
      echo "<li><a href='$hrefGlobal' $themeGlobal>".$LANG['central'][13]."</a></li>";
      echo "<li><a href='$hrefPerso' $themePerso>".$LANG['central'][12]."</a></li>";
      
      echo "</ul>";
      echo "</div>";
      echo "</div>";
   }
}
