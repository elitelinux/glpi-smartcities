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

class PluginMobileTab extends CommonDBTM {
	
   
   public static function getTitle($id, $itemtype, $items_id)  {
      $obj = new $itemtype;
      $obj->getFromDB($items_id);
      $tabs = $obj->defineTabs();
      return $tabs[$id];
   } 
   
   public function showUrl($url, $params = array()) {
      global $CFG_GLPI;
      
      $js_params = "{\n";
      foreach ($params as $key => $value) {
         $js_params .= "'$key' : '$value',\n";
      }
      $js_params = substr($js_params, 0, -2)."}";
      
      saveActiveProfileAndApplyRead();
      echo "<link rel='stylesheet'  href='".
             $CFG_GLPI["root_doc"]."/plugins/mobile/lib/scrollview/jquery.mobile.scrollview.css' type='text/css' media='screen' >\n";
      echo "<script type=\"text/javascript\" src='".
          $CFG_GLPI["root_doc"]."/plugins/mobile/lib/scrollview/jquery.easing.1.3.js'></script>";
      echo "<script type=\"text/javascript\" src='".
          $CFG_GLPI["root_doc"]."/plugins/mobile/lib/scrollview/jquery.mobile.scrollview.js'></script>";
      echo "<script type=\"text/javascript\" src='".
          $CFG_GLPI["root_doc"]."/plugins/mobile/lib/scrollview/scrollview.js'></script>";

      echo "<script type='text/javascript'>
      $.post('$url', $js_params,
         function(data){
            $('#tab_content').html(data);
            $('#tab_content').html($('#tab_content table:first'));
            //$('#tab_content script').remove();
            $('#tab_content th' ).addClass('ui-bar-b');
            $('#tab_content tr' ).addClass('ui-btn-up-c').removeClass('tab_bg_2');
            $('#tab_content table:first').attr('class', '').attr('style', 'width:100%');
            $('#tab_content #debugajax').remove();
            /*$('#tab_content a').each( function(){
                $(this).replaceWith($(this).html());
            });*/
            /*$('#tab_content select, #tab_content input').each( function(){
                $(this).replaceWith($(this).val());
            });*/
            //$('#tab_content input[type=submit]').remove();
            
            
            $('#tab_content select').attr('data-native-menu', 'true');
            $('#tab_content').page({keepNative:true});
            
            mobileScrollView();          
            
      });
      </script>";
      echo "<div id='tab_content' class='scroll_content' data-scroll='true'></div>";
      
      restoreActiveProfile();
   }
     
   
   public function showTab($itemtype)  {  
      $url = Toolbox::getItemTypeTabsURL($itemtype);     
      $params = array(
         'id' => $_GET['id'], 
         'glpi_tab' => $_GET['glpi_tab'], 
         'target' => $_SERVER['PHP_SELF']
      );
      $this->showUrl($url, $params); 
   }
   
   public static function getPluginTitle($plugin_name, $itemtype, $items_id)  {
      $obj = new $itemtype;
      $obj->getFromDB($items_id);
      
      $target = $_SERVER['PHP_SELF'];
      $pluginsTabs = PluginMobilePlugin::getTabs($target,$obj, false);
      
      return $pluginsTabs[$plugin_name]['title'];
   } 
   
   public function showPluginTab($plugin_name, $itemtype, $items_id) {
      $obj = new $itemtype;
      $obj->getFromDB($items_id);
      
      $target = $_SERVER['PHP_SELF'];
      $pluginsTabs = PluginMobilePlugin::getTabs($target,$obj, false);
      
      $url = $pluginsTabs[$plugin_name]['url'];
      
      parse_str($pluginsTabs[$plugin_name]['params'], $paramsArray);
      
      $params = array(
         'id' => $paramsArray['id'], 
         'glpi_tab' => $paramsArray['glpi_tab'], 
         'target' => $paramsArray['target']
      );
      $this->showUrl($url, $params); 
   }
      
/*
   
function defineTabs($options=array()) 
{ global $LANG, $CFG_GLPI, $DB;

$ong = array(); 
// modif de l'ordre des onglets;
 $this->addStandardTab('TicketTask', $ong, $options); 
 $this->addStandardTab('TicketFollowup',$ong, $options);
 $this->addStandardTab('TicketValidation', $ong, $options);
 //$this->addStandardTab('TicketTask', $ong, $options); 
$this->addStandardTab(CLASS, $ong, $options); 
$this->addStandardTab('Document', $ong, $options);
 $this->addStandardTab('Problem', $ong, $options);
 // $this->addStandardTab('Change', $ong, $options); 
$this->addStandardTab('Log', $ong, $options);

return $ong; }   
 

   
//ticket.class.php   
   
  function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('TicketFollowup',$ong, $options);
      $this->addStandardTab('TicketValidation', $ong, $options);
      $this->addStandardTab('TicketTask', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('TicketCost', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Problem', $ong, $options);
//       $this->addStandardTab('Change', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   } 
        
  
  public function defineTabs($options=array()) {
      global $LANG, $CFG_GLPI, $DB;

      if ($this->fields['id'] > 0) {
         if (Session::haveRight('observe_ticket','1')) {
            $ong[1] = $LANG['mailing'][141];
         }
         if (Session::haveRight('create_validation','1') ||Session::haveRight('validate_ticket','1')) {
            $ong[7] = $LANG['validation'][8];
         }
         if (Session::haveRight('observe_ticket','1')) {
            $ong[2] = $LANG['mailing'][142];
         }
         $ong[3] = $LANG['job'][47];
         $ong[4] = $LANG['jobresolution'][2];
         // enquete si statut clos
         if ($this->fields['status'] == '5') {
            $ong[10] = $LANG['satisfaction'][0];
         }
         $ong[5] = $LANG['Menu'][27];
         $ong[6] = $LANG['title'][38];
         if (Session::haveRight('observe_ticket','1')) {
            $ong[8] = $LANG['Menu'][13];
         }

      //   $ong['no_all_tab'] = true;
      } else {
         $ong[1] = $LANG['job'][13];
      }

      return $ong;
   }
   
   
     function defineTabs($options=array()) {
      return array();
   }  
   
   */ 
     
   public static function displayTabBar($items = array()) {
      global $LANG, $CFG_GLPI;
      
      $classname = ucfirst($_GET['itemtype']);
      $obj = new $classname;
      $obj->getFromDB($_GET['id']);
     
      $tabs = $obj->defineTabs();
     
      $target = $_SERVER['PHP_SELF'];
      $pluginsTabs = PluginMobilePlugin::getTabs($target,$obj, false);
      
    
      echo "<div data-role='header' data-backbtn='false' data-theme='a' data-id='TabBar'>";   
         echo "<div data-theme='c' class='ui-btn-right' style='top:0' data-position='inline'>";
         foreach($items as $item) {
            $item = str_replace('<a', "<a data-role='button' data-theme='c'", $item);
            echo $item;
         }   
         echo "</div>";
         
         //echo "&nbsp;|&nbsp;";
                          
         echo "<div data-role='collapsible' data-theme='c' data-collapsed='true'>";
            echo "<h2>&nbsp;&nbsp;&nbsp;".$LANG['plugin_mobile']['common'][4]."</h2>";
            echo "<div>";
               
               //echo "<ul data-role='listview' id='ultabs'>";
                                             
               foreach($tabs as $key => $tab) {
                  echo "<a href='".$CFG_GLPI["root_doc"]
                     ."/plugins/mobile/front/tab.php?glpi_tab=$key&id=".$_GET['id']
                     ."&itemtype=".$_GET['itemtype']
                     ."&menu=".$_GET['menu']
                     ."&ssmenu=".$_GET['ssmenu']
                     ."' data-theme='c' rel='external' data-role='button'>".$tab."</a>";
               }
              
               //plugins tabs
                                                       
               foreach($pluginsTabs as $key => $tab) {
                  $params = explode('&', $tab['params']);                                    
                  echo "<a href='".$CFG_GLPI["root_doc"]
                     ."/plugins/mobile/front/tab_plugins.php?".$params[2]."&id=".$_GET['id']
                     ."&itemtype=".$_GET['itemtype']
                     ."&menu=".$_GET['menu']
                     ."&ssmenu=".$_GET['ssmenu']
                     ."' data-theme='c' rel='external' data-role='button'>".$tab['title']."</a>";
               }               
               //echo "</ul>";
                             
            echo "</div>";
         echo "</div>";
      echo "</div>";
   }
   

//Stevenes Donato

static function showLog($glpi_tab, $itemtype) {
global $CFG_GLPI, $DB, $LANG;
          
$sql_sol = "SELECT id, date_mod, user_name, itemtype_link
FROM `glpi_logs`
WHERE items_id = ".$_REQUEST['id']."
AND itemtype = '".$_REQUEST['itemtype']."'
ORDER BY `glpi_logs`.`id` DESC";

$result_sol = $DB->query($sql_sol);	

echo '<ul class="ui-listview" data-theme="c" data-role="listview">';
	
while($row = $DB->fetch_assoc($result_sol)) {	
	
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> ID: </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['id'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['common'][27].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['date_mod'].'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"> '.$LANG['stats'][20].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row['user_name'].'</li>';


$sql_log = "SELECT linked_action, id_search_option, old_value, new_value
FROM `glpi_logs`
WHERE id = ".$row['id']."";

$result_log = $DB->query($sql_log);	

while($row_log = $DB->fetch_assoc($result_log)) {	

include('../inc/logtype.inc.php');

}
echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading">'.$LANG['buttons'][7].': </li>';
echo '<li class="ui-li ui-li-static ui-body-c">'.$row_log['linked_action'].' '.$log.'</li>';

echo '<li class="ui-li ui-li-divider ui-btn ui-bar-b ui-btn-up-undefined" data-role="list-divider" role="heading"></li>';
echo '<li class="ui-li ui-li-static ui-body-c"></li>';
	
	}	
	echo '</ul>';                       
}



}
