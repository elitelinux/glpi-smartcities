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

//define('GLPI_ROOT', '../../..'); 
//define("MOBILE_EXTRANET_ROOT", GLPI_ROOT . "/plugins/mobile/front");

define("MOBILE_EXTRANET_ROOT", "../../../plugins/mobile/front");

class PluginMobileOption extends CommonGLPI {
   
   public function getTable() {
      return "glpi_plugin_mobile_options";
   }
	
	public function showForm () {
      global $LANG, $CFG_GLPI, $DB;

      
      echo "<div data-theme='c'>";
         echo "<a href='".$CFG_GLPI['root_doc']."/plugins/mobile/logout.php' data-icon='delete' data-role='button' data-inline='true' rel='external' data-theme='a'>".$LANG['central'][6]."</a>";
         echo "<br /><br />";
         echo "<form action='".$CFG_GLPI['root_doc']."/plugins/mobile/front/option.form.php' method='get'>";
         echo "<div data-role='collapsible' data-collapsed='false'>";
            echo "<h3>".$LANG['setup'][119]."</h3>";
            echo "<p data-role='fieldcontain'>";
               echo "<label for='cols_limit' class='ui-input-text'>".$LANG['plugin_mobile']['opt'][0]." : </label>";
               echo "<input type='range' min='2' max='7' name='cols_limit' value='".$_SESSION['plugin_mobile']['cols_limit']."' /><br />";
               echo "<label for='rows_limit' class='ui-input-text'>".$LANG['plugin_mobile']['opt'][1]." : </label>";
               echo "<input type='range' min='3' max='100' name='rows_limit' value='".$_SESSION['plugin_mobile']['rows_limit']."' /><br />";
            echo "</p>";
         echo "</div>";
         
         echo "<div data-role='collapsible' data-collapsed='false'>";
    
            echo "<h3>".$LANG['common'][67]."</h3>";
            echo "<p data-role='fieldcontain'>";
       
// user rights Stevenes Donato       
$query = "SELECT `profiles_id` AS id
FROM `glpi_profiles_users`
WHERE `users_id` = ".$_SESSION['glpiID']."
ORDER BY `glpi_profiles_users`.`profiles_id` DESC";   

$result = $DB->query($query);
$cont = $DB->numrows($result);
$profile = $DB->fetch_assoc($result);
    
 if($cont != "1" && $profile != "1") {           
               if ($_SESSION['plugin_mobile']['edit_mode'] == 1) $edit_mode = "checked='checked'";
               else $edit_mode = "";
               echo "<input type='checkbox' name='edit_mode' id='edit_mode' $edit_mode />";
               echo "<label for='edit_mode' class='ui-input-text'>".$LANG['plugin_mobile']['opt'][2]."</label>";
 }              
               if ($_SESSION['plugin_mobile']['native_select'] == 1) $native_select = "checked='checked'";
               else $native_select = "";
               echo "<input type='checkbox' name='native_select' id='native_select' $native_select />";
               echo "<label for='native_select' class='ui-input-text'>".$LANG['plugin_mobile']['opt'][3]."</label>";
            echo "</p>";
         echo "</div>";
         echo "<input type='submit' id='option_submit' value='".$LANG['buttons'][7]."' data-theme='a' data-inline='true' />";
         //echo "</form>";
         Html::closeForm();

      echo "</div>";
   }
   
   
   public function save($p) {
      global $DB;
      
      $options = array(
         'cols_limit' => 3,
         'rows_limit' => 9,
         'edit_mode' => 0,
         'native_select' => 1
      );
      
      foreach ($p as $key => $val) {
         $options[$key] = $val;
      }
      
      if ($options['edit_mode'] === 'on') $options['edit_mode'] = 1;
      else $options['edit_mode'] = 0;
      if ($options['native_select'] === 'on') $options['native_select'] = 1;
      else $options['native_select'] = 0;
      
      $query = "SELECT * FROM ".$this->getTable()." WHERE users_id='".$_SESSION['glpiID']."'";
      $res = $DB->query($query);
      if ($DB->numrows($res) > 0) {
  
         //update pref
         
         $query = "UPDATE ".$this->getTable()." SET 
                  cols_limit = '".$options['cols_limit']."',
                  rows_limit = '".$options['rows_limit']."',
                  edit_mode = '".$options['edit_mode']."',
                  native_select = '".$options['native_select']."'
               WHERE users_id='".$_SESSION['glpiID']."'";
         $res = $DB->query($query);
      } else {
  
         //create
         
         $query = "INSERT INTO ".$this->getTable()." (
                     users_id, 
                     cols_limit, 
                     rows_limit, 
                     edit_mode,
                     native_select
                  )
                  VALUES (
                     '".$_SESSION['glpiID']."', 
                     '".$options['cols_limit']."', 
                     '".$options['rows_limit']."', 
                     '".$options['edit_mode']."',
                     '".$options['native_select']."'
                  )";
         $res = $DB->query($query);
      }
      
      $this->getOptions();
   } 
      
   public function getOptions() {
      global $DB;
      
      $query = "SELECT * FROM ".$this->getTable()." WHERE users_id='".$_SESSION['glpiID']."'";
      $res = $DB->query($query);
      $options = array();
      if ($DB->numrows($res) > 0) while ($data = $DB->fetch_array($res)) {
         $_SESSION['plugin_mobile']['cols_limit'] = $data['cols_limit'];
         $_SESSION['plugin_mobile']['rows_limit'] = $data['rows_limit'];
         $_SESSION['plugin_mobile']['edit_mode'] = $data['edit_mode'];
         $_SESSION['plugin_mobile']['native_select'] = $data['native_select'];
      } else return false;
      
      return true;
   } 
}
