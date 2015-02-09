<?php
/*
 -------------------------------------------------------------------------
 Shellcommands plugin for GLPI
 Copyright (C) 2014 by the Shellcommands Development Team.
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
 along with Shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------  */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginShellcommandsCommandGroup_Item
 * 
 * This class allows to add and manage the commands used for the conforimty check of the items
 * 
 * @package    Shellcommands
 * @author     Ludovic Dupont
 */
class PluginShellcommandsCommandGroup_Item extends CommonDBRelation {
   
   // From CommonDBRelation
   static public $itemtype_1 = "PluginShellcommandsCommandGroup";
   static public $items_id_1 = 'plugin_shellcommands_commandgroups_id';
   static public $itemtype_2 = 'PluginShellcommandsShellcommand';
   static public $items_id_2 = 'plugin_shellcommands_shellcommands_id';
   
   static $rightname = 'plugin_shellcommands';
   
   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb=0) {
      return _n('Command group', 'Command groups', $nb, 'shellcommands');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }

   
   /**
    * Display tab for item
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginShellcommandsCommandGroup') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginShellcommandsShellcommand::getTypeName(2), countElementsInTable($this->getTable(), "`plugin_shellcommands_commandgroups_id` = '".$item->getID()."'"));
            }
         } else if ($item->getType() == 'PluginShellcommandsShellcommand'
                 && self::canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginShellcommandsCommandGroup::getTypeName(2), countElementsInTable($this->getTable(), "`plugin_shellcommands_shellcommands_id` = '".$item->getID()."'"));
            }
            return PluginShellcommandsCommandGroup::getTypeName(2);
         }
      }
      
      return '';
   }
   
   /**
    * Display content for each users
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginShellcommandsCommandGroup') {
         $command = new self();
         $command->showForCommandGroup($item);
         
      } elseif($item->getType() == 'PluginShellcommandsShellcommand'){
         $command = new self();
         $command->showForShellcommand($item);
      }
      
      return true;
   }
   
   /**
    * Function show for record model
    * 
    * @param type $item
    * @return boolean
    */
   function showForCommandGroup($item) {
      
      if (!$this->canView() || !$this->canCreate()) return false;
      
      $used = array();
      
      $dataGroup = $this->find('`plugin_shellcommands_commandgroups_id` = '.$item->fields['id'], "`rank`");
      
      $shellcommand = new PluginShellcommandsShellcommand();
      $canedit = $shellcommand->can($item->fields['id'], UPDATE);
      
      if($dataGroup){
         foreach($dataGroup as $field){
            $used[] = $field['plugin_shellcommands_shellcommands_id'];
         }
      }
      if ($canedit) {
         echo "<form name='form' method='post' action='".
            Toolbox::getItemTypeFormURL('PluginShellcommandsCommandGroup_Item')."'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>".__('Add a command', 'shellcommands')."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown group
         echo "<td class='center'>";
         echo PluginShellcommandsShellcommand::getTypeName().'&nbsp;';
         Dropdown::show("PluginShellcommandsShellcommand", array('name' => 'plugin_shellcommands_shellcommands_id', 'used' => $used));
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add' class='submit' value='"._sx('button', 'Add')."' >";
         echo "<input type='hidden' name='plugin_shellcommands_commandgroups_id' class='submit' value='".$item->fields['id']."' >";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }
      if($dataGroup)
         $this->listItems($dataGroup, $canedit);
   }
   
   /**
    * Function show for record model
    * 
    * @param type $item
    * @return boolean
    */
   function showForShellcommand($item) {
      
      if (!$this->canView() || !$this->canCreate()) return false;
      
      $used = array();
      
      $dataGroup = $this->find('`plugin_shellcommands_shellcommands_id` = '.$item->fields['id'], "`rank`");
      
      $shellcommand = new PluginShellcommandsShellcommand();
      $canedit = $shellcommand->can($item->fields['id'], UPDATE);
      
      if($dataGroup){
         foreach($dataGroup as $field){
            $used[] = $field['plugin_shellcommands_shellcommands_id'];
         }
      }
      if ($canedit) {
         echo "<form name='form' method='post' action='".
            Toolbox::getItemTypeFormURL('PluginShellcommandsCommandGroup_Item')."'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>".__('Add a command group', 'shellcommands')."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown group
         echo "<td class='center'>";
         echo PluginShellcommandsShellcommand::getTypeName().'&nbsp;';
         Dropdown::show("PluginShellcommandsCommandGroup", array('name' => 'plugin_shellcommands_commandgroups_id', 'used' => $used));
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add' class='submit' value='"._sx('button', 'Add')."' >";
         echo "<input type='hidden' name='plugin_shellcommands_shellcommands_id' class='submit' value='".$item->fields['id']."' >";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }
      if($dataGroup)
         $this->listItemsForShellCommand($dataGroup, $canedit);
   }
   
   /**
    * Show shellcommands associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated shellcommands must be displayed
    * @param $withtemplate    (default '')
    * */
   static function showForItem(CommonDBTM $item, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!self::canView()) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      
      $width = 200;
      
      $command_group = new PluginShellcommandsCommandGroup();
      $restrict = getEntitiesRestrictRequest(" AND", "glpi_plugin_shellcommands_commandgroups", '', '', true);
      $data = $command_group->find("1".$restrict);
      $shells = array(0 => Dropdown::EMPTY_VALUE);
      if (!empty($data)) {
         foreach($data as $val){
            $shells['[IP]-'.$val['id'].'-0'] = $val['name'];
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".PluginShellcommandsCommandGroup::getTypeName(1)."</th>";
      echo "</tr>";
         
      echo "<tr class='tab_bg_2'>
            <td class='center'>".PluginShellcommandsCommandGroup::getTypeName(1)." ";
      $randSelect = Dropdown::showFromArray("name", $shells, array('width' => $width));
      echo "<span id='command_name$randSelect'></span></td>";
      echo "</tr>";

      Ajax::updateItemOnSelectEvent("dropdown_name$randSelect", "command_name$randSelect", $CFG_GLPI["root_doc"]."/plugins/shellcommands/ajax/dropdownCommandValue.php", 
              array('idtable'       => $item->getType(),
                    'value'         => '__VALUE__',
                    'itemID'        => $ID,
                    'countItem'     => 1,
                    'width'         => $width,
                    'command_type'  => 'PluginShellcommandsCommandGroup',
                    'toupdate'      => 'command_group_result',
                    'itemtype'      => $item->getType(),
                    'myname'        => "command_ip"));
      

      echo "</table>";
      echo "</div>";
      echo "<div class='spaced' id='command_group_result'></div>";
   }
   
      /**
    * Function list items
    * 
    * @global type $CFG_GLPI
    * @param type $ID
    * @param type $data
    * @param type $canedit
    * @param type $rand
    */
   private function listItemsForShellCommand($data, $canedit){
      global $CFG_GLPI;

      $rand = mt_rand();
      $numrows = count($data);
      $target = Toolbox::getItemTypeFormURL('PluginShellcommandsCommandGroup_Item');
      
      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand, 'num_displayed' => $numrows);
         Html::showMassiveActions($massiveactionparams);
      }
      
//      Html::printAjaxPager(self::getTypeName(2), $start, countElementsInTable($this->getTable()));
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         Html::closeForm(); 
      }
      echo "</th>";
      echo "<th colspan='3'>".PluginShellcommandsCommandGroup::getTypeName(2)."</th>";
      echo "</tr>";
      
      $commandgroup = new PluginShellcommandsCommandGroup();
      
      $i = 0;
      foreach ($data as $field) {
         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         // Command
         $commandgroup->getFromDB($field['plugin_shellcommands_commandgroups_id']);
         echo "<td>".$commandgroup->getLink()."</td>";
         echo "</tr>";
         
         $i++;
      }

      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm(); 
      }
      echo "</table>";
      echo "</div>";
   }
   
   /**
    * Function list items
    * 
    * @global type $CFG_GLPI
    * @param type $ID
    * @param type $data
    * @param type $canedit
    * @param type $rand
    */
   private function listItems($data, $canedit){
      global $CFG_GLPI;

      $rand = mt_rand();
      $numrows = count($data);
      $target = Toolbox::getItemTypeFormURL('PluginShellcommandsCommandGroup_Item');
      
      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand, 'num_displayed' => $numrows);
         Html::showMassiveActions($massiveactionparams);
      }
      
//      Html::printAjaxPager(self::getTypeName(2), $start, countElementsInTable($this->getTable()));
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         Html::closeForm(); 
      }
      echo "</th>";
      echo "<th>".PluginShellcommandsShellcommand::getTypeName(2)."</th>";
      echo "<th>"._n('Type', 'Types', 1)."</th>";
      echo "<th colspan='2'>".__('Order', 'shellcommands')."</th>";
      echo "</tr>";
      
      $shellcommand = new PluginShellcommandsShellcommand();
      $shellcommand_item = new PluginShellcommandsShellcommand_Item();
      
      $i = 0;
      foreach ($data as $field) {
         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         // Command
         $shellcommand->getFromDB($field['plugin_shellcommands_shellcommands_id']);
         $itemtypes = $shellcommand_item->getShellCommandItemtypes($field['plugin_shellcommands_shellcommands_id']);
         echo "<td>".$shellcommand->getLink()."</td>";
         echo "<td>";
         if (!empty($itemtypes)) {
            echo implode("<br>", $itemtypes);
         }
         echo "</td>";
                  
         // Change order
         if ($i != 0) {
            echo "<td class='center middle'>";
            echo "<form method='post' action='$target'>";
            echo "<input type='hidden' name='id' value='".$field["id"]."'>";
            echo "<input type='image' name='up' value=\"".__s('Bring up')."\" src='".
                   $CFG_GLPI["root_doc"]."/pics/puce-up2.png' alt=\"".
                   __s('Bring up')."\"  title=\"".__s('Bring up')."\">";
            Html::closeForm();
            echo "</td>";

         } else {
            echo "<td>&nbsp;</td>\n";
         }

         if ($i != ($numrows-1)) {
            echo "<td class='center middle'>";
            echo "<form method='post' action='$target'>";
            echo "<input type='hidden' name='id' value='".$field["id"]."'>";
            echo "<input type='image' name='down' value=\"".__s('Bring down')."\" src='".
                   $CFG_GLPI["root_doc"]."/pics/puce-down2.png' alt=\"".
                   __s('Bring down')."\" title=\"".__s('Bring down')."\">";
            Html::closeForm();
            echo "</td>";

         } else {
            echo "<td>&nbsp;</td>\n";
         }
         echo "</tr>";
         
         $i++;
      }

      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm(); 
      }
      echo "</table>";
      echo "</div>";
   }
   
   /**
    * Function get items for record models
    * 
    * @global type $DB
    * @param type $commandgroups_id
    * @param type $start
    * @return type
    */
   function getItems($commandgroups_id, $start=0){
      global $DB;
      
      $output = array();
      
      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`plugin_shellcommands_shellcommands_id`,
                       `".$this->getTable()."`.`plugin_shellcommands_commandgroups_id`
          FROM ".$this->getTable()."
          WHERE `".$this->getTable()."`.`plugin_shellcommands_commandgroups_id` = ".Toolbox::cleanInteger($commandgroups_id)."
          LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['id']] = $data;
         }
      }
      
      return $output;
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

      if (!empty($values['itemtype']) && !empty($values['itemID'])) {
         $item = getItemForItemtype($values['itemtype']);
         $item->getFromDB($values['itemID']);

         $shellcommands_item = new PluginShellcommandsShellcommand_Item();
         $shellcommands = new PluginShellcommandsShellcommand();
         $commandgroups = new PluginShellcommandsCommandGroup();
         $commandgroups_item = new PluginShellcommandsCommandGroup_Item();
         $commandgroups_items = $commandgroups_item->find("`plugin_shellcommands_commandgroups_id`=".$values['id'], "`rank`");

         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe shellcommands_result_line'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='4'>".__('Result details')."</th>";
         echo "</tr>";

         // First : test ping
         $error = 1;
         $commandgroups->getFromDB($values['id']);
         if (!empty($commandgroups->fields['check_commands_id'])) {
            $targetParam = PluginShellcommandsShellcommand_Item::resolveLinkOfCommand($commandgroups->fields['check_commands_id'], $item);
            // Exec command on each targets : stop on first success
            if ($targetParam !== false) {
               foreach ($targetParam as $target) {
                  list($error, $execPing) = PluginShellcommandsShellcommand_Item::execCommand($commandgroups->fields['check_commands_id'], $target);
                  if (!$error) {
                     break;
                  }
               }
            }
         }

         // If Ping ok execute group commands
         if (!$error) {
            echo "<tr class='tab_bg_1 shellcommands_result_line'>";
            echo "<td class='center' colspan='2'>".__($item->getType()).' : '.$item->getLink()."</td>";
            echo "<td class='center'><div class='shellcommands_result_ok'>UP</div></td>";
            echo "<td>";
            echo __('Host UP', 'shellcommands');
            echo "</td>";
            echo "</tr>";

            if (!empty($commandgroups_items)) {
               foreach ($commandgroups_items as $val) {
                  if (!$shellcommands_item->getFromDBbyShellCommandsAndItem($val['plugin_shellcommands_shellcommands_id'], $values['itemtype'])) {
                     continue;
                  }
                  $shellcommands->getFromDB($val['plugin_shellcommands_shellcommands_id']);
                  $targetParam = PluginShellcommandsShellcommand_Item::resolveLinkOfCommand($val['plugin_shellcommands_shellcommands_id'], $item);
                  
                  // Exec command on each targets : stop on first success
                  if ($targetParam !== false) {
                     foreach ($targetParam as $target) {
                        list($error, $message) = PluginShellcommandsShellcommand_Item::execCommand($shellcommands->getID(), $target);
                        if (!$error) {
                           break;
                        }
                     }
                  }

                  PluginShellcommandsShellcommand::displayCommandResult($shellcommands, $target, $message, $error);
               }
            }

         } else {
            echo "<tr class='tab_bg_1 shellcommands_result_line'>";
            echo "<td class='center' colspan='2'>".__($item->getType()).' : '.$item->getLink()."</td>";
            echo "<td class='center'><div class='shellcommands_result_ko'>DOWN</div></td>";
            echo "<td>";
            echo __('Host DOWN', 'shellcommands');
            echo "</td>";
            echo "</tr>";
         }

         echo "</table>";
         echo "</div>";
      }
   }
   
   /**
    * Order to move an item
    *
    * @param $input  array parameter (id,itemtype,users_id)
    * @param $action       up or down
   **/
   function orderItem(array $input, $action) {
      global $DB;

      // Get current item
      $query = "SELECT `rank`
                FROM `".$this->getTable()."`
                WHERE `id` = '".$input['id']."'";
      $result = $DB->query($query);
      $rank1  = $DB->result($result, 0, 0);

      // Get previous or next item
      $query = "SELECT `id`, `rank`
                FROM `".$this->getTable()."`
                WHERE 1";

      switch ($action) {
         case "up" :
            $query .= " AND `rank` < '$rank1'
                      ORDER BY `rank` DESC";
            break;

         case "down" :
            $query .= " AND `rank` > '$rank1'
                      ORDER BY `rank` ASC";
            break;

         default :
            return false;
      }

      $result = $DB->query($query);
      $rank2  = $DB->result($result, 0, "rank");
      $ID2    = $DB->result($result, 0, "id");

      // Update items
      $query = "UPDATE `".$this->getTable()."`
                SET `rank` = '$rank2'
                WHERE `id` = '".$input['id']."'";
      $DB->query($query);

      $query = "UPDATE `".$this->getTable()."`
                SET `rank` = '$rank1'
                WHERE `id` = '$ID2'";
      $DB->query($query);
   }
   
   /**
    * @see CommonDBTM::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {
      global $DB;

      $query = "SELECT MAX(`rank`)
                FROM `".$this->getTable()."`";
      $result = $DB->query($query);

      $input["rank"] = $DB->result($result,0,0)+1;

      return $input;
   }
   
}
?>