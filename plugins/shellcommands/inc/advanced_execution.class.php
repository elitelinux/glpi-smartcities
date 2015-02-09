<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Shellcommands plugin for GLPI
  Copyright (C) 2003-2011 by the Shellcommands Development Team.

  https://forge.indepnet.net/projects/shellcommands
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Shellcommands.

  Shellcommands is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Shellcommands is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginShellcommandsAdvanced_Execution extends CommonDBTM {

   public $dohistory = true;
   static $rightname = 'plugin_shellcommands';

   public static function getTypeName($nb = 0) {
      return _n('Advanced execution', 'Advanced executions', $nb, 'shellcommands');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }
   
  /**
   * Show form
   * 
   * @global type $CFG_GLPI
   * @param type $ID
   * @param type $options
   */
   function showForm($ID = 0, $options=array()) {
      global $CFG_GLPI;
      
      echo "<div class='center first-bloc'>";
      echo "<form name='field_form' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>".self::getTypeName()."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_2 center'>";
      echo PluginShellcommandsCommandGroup::getTypeName(1)." ";
      Dropdown::show('PluginShellcommandsCommandGroup', array('entity' => $_SESSION['glpiactive_entity'], 'width' => 200));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_2 center'>";
      $this->getEditValue();
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_2 center'>";
      echo "<input type='button' class='submit' onclick='shellcommand_advanced_execution(\"".$CFG_GLPI['root_doc']."\",\"field_form\", \"advanced_execution_result\");' name='advanced_execution' value='".__('Execute')."'/>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
      echo "</div>";
      
      echo "<div class='spaced' id='advanced_execution_result'></div>";
   }
   
  /**
   * View custom values for items or types
   *
   * @return html
   */
   function getEditValue() {
      global $CFG_GLPI;
      
      echo "<table width='100%' class='shellcommands_show_values'>";
      echo "<tr><th colspan='2'>"._n('Item', 'Items', 2)."</th></tr>";
      echo "<tr><td>";
      echo '<table class="shellcommands_show_custom_fields">';
      echo "<tr>";
      echo "<td id='show_custom_fields'>";
      self::addNewValue(1);
      echo '</td>';
      echo '<td>'; 
      self::initCustomValue(1);
      echo '</td>';
      echo '</tr></table>';
      echo "</td>";
      echo "</tr>";
      echo "</table>";

   }
   
  /**
   * Init values
   * 
   * @param array $count
   */
   static function initCustomValue($count){
      global $CFG_GLPI;
      
      echo '<input type="hidden" id="count_custom_values" value="'.$count.'"/>';

      echo "&nbsp;<img style='cursor:pointer' 
            onclick='shellcommands_add_custom_values(\"show_custom_fields\", \"".$CFG_GLPI['root_doc']."\");' 
            src='".$CFG_GLPI['root_doc']."/plugins/shellcommands/pics/add.png' 
            alt='"._sx("button", "Add")."'
            title='"._sx("button", "Add")."'/>&nbsp;";
      
      echo '<img style="cursor:pointer" 
            onclick="shellcommands_delete_custom_values(\'custom_values\')" 
            src="'.$CFG_GLPI['root_doc'].'/plugins/shellcommands/pics/delete.png"
            alt="'._sx('button', 'Delete permanently').'" 
            title="'._sx('button', 'Delete permanently').'"/>';
   }
   
  /**
   * Add new value to form
   * 
   * @param array $valueId
   */
   static function addNewValue($valueId){
      global $CFG_GLPI;
      
      echo "<div id='custom_values$valueId' class='shellcommands_custom_values'><span>".__('Item').' '.$valueId.'</span> '; 
      self::dropdownAllDevices('items', null, 0, 1, 0, $_SESSION['glpiactive_entity']);
      echo "</div>";
   }

   /**
   * Launch a command
   * 
   * @param array $values
   * 
   * @return void
   */
   static function lauchCommand($values) {
      global $CFG_GLPI;

      $items_to_execute = json_decode(stripslashes($values['items_to_execute']), true);

      if (!empty($items_to_execute)) {
         foreach ($items_to_execute as $key => $items) {
            PluginShellcommandsCommandGroup_Item::lauchCommand(array('itemID'   => $items['items_id'], 
                                                                     'itemtype' => $items['itemtype'], 
                                                                     'id'       => $values['command_group'], 
                                                                     'value'    => null));
         }
      }
   }
   
  /**
    * Make a select box for Tracking All Devices
    *
    * @param $myname             select name
    * @param $itemtype           preselected value.for item type
    * @param $items_id           preselected value for item ID (default 0)
    * @param $admin              is an admin access ? (default 0)
    * @param $users_id           user ID used to display my devices (default 0
    * @param $entity_restrict    Restrict to a defined entity (default -1)
    * @param $tickets_id         Id of the ticket
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdownAllDevices($myname, $itemtype, $items_id=0, $admin=0, $users_id=0,
                                      $entity_restrict=-1, $tickets_id=0) {
      global $CFG_GLPI, $DB;
      
      $rand = mt_rand();

      if ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"] == 0) {
         echo "<input type='hidden' name='$myname' value=''>";
         echo "<input type='hidden' name='items_id' value='0'>";

      } else {
         $rand = mt_rand();
         echo "<span id='tracking_all_devices$rand'>";
         if ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"]&pow(2,
                                                                     Ticket::HELPDESK_ALL_HARDWARE)) {

            if ($users_id
                &&($_SESSION["glpiactiveprofile"]["helpdesk_hardware"]&pow(2,
                                                                           Ticket::HELPDESK_MY_HARDWARE))) {
               echo __('Or complete search')."&nbsp;";
            }

            $types = Ticket::getAllTypesForHelpdesk(); 
            $emptylabel = Dropdown::EMPTY_VALUE;
            
            $rand       = Dropdown::showItemTypes($myname, array_keys($types),
                                                  array('emptylabel' => $emptylabel,
                                                        'value'      => $itemtype, 'width' => 200));
            $found_type = isset($types[$itemtype]);
            
            $width = 250;

            $params     = array('itemtype'        => '__VALUE__',
                                'entity_restrict' => $entity_restrict,
                                'admin'           => $admin,
                                'width'           => $width,
                                'myname'          => "items_id",);

            Ajax::updateItemOnSelectEvent("dropdown_$myname$rand","results_$myname$rand",
                                          $CFG_GLPI["root_doc"].
                                             "/plugins/shellcommands/ajax/dropdownTrackingDeviceType.php",
                                          $params);
            echo "<span id='results_$myname$rand'>\n";

            // Display default value if itemtype is displayed
            if ($found_type
                && $itemtype) {
                if (($item = getItemForItemtype($itemtype))
                    && $items_id) {
                  if ($item->getFromDB($items_id)) {
                     Dropdown::showFromArray('items_id', array($items_id => $item->getName()),
                                             array('value' => $items_id, 'width' => $width));
                  }
               } else {
                  $params['itemtype'] = $itemtype;
                  echo "<script type='text/javascript' >\n";
                  Ajax::updateItemJsCode("results_$myname$rand",
                                         $CFG_GLPI["root_doc"].
                                            "/plugins/shellcommands/ajax/dropdownTrackingDeviceType.php",
                                         $params);
                  echo '</script>';
               }
            }
            echo "</span>\n";
         }
         echo "</span>";
      }
      return $rand;
   }
   
}

?>