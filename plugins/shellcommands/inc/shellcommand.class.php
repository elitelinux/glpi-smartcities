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

class PluginShellcommandsShellcommand extends CommonDBTM {

   static $types = array('Computer', 'NetworkEquipment', 'Peripheral',
                         'Phone', 'Printer');
   
   static $rightname = 'plugin_shellcommands';
   
   public $dohistory = true;
   
   const KO_RESULT       = 0;
   const OK_RESULT       = 1;
   const WARNING_RESULT  = 2;
   const CRITICAL_RESULT = 3;

   public static function getTypeName($nb = 0) {
      return _n('Shell Command', 'Shell Commands', $nb, 'shellcommands');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }

   function getFromDBbyName($name) {
      global $DB;

      $query = "SELECT * FROM `".$this->gettable()."` ".
              "WHERE (`name` = '".$name."') ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   function cleanDBonPurge() {
      global $DB;

      $temp = new PluginShellcommandsShellcommand_Item();
      $temp->deleteByCriteria(array('plugin_shellcommands_shellcommands_id' => $this->fields['id']));

      $path = new PluginShellcommandsShellcommandPath();
      $path->deleteByCriteria(array('plugin_shellcommands_shellcommands_id' => $this->fields['id']));
   }

   function getSearchOptions() {
      $tab = array();

      $tab['common'] = self::getTypeName(2);

      $tab[1]['table']         = $this->gettable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';

      $tab[2]['table']         = $this->gettable();
      $tab[2]['field']         = 'link';
      $tab[2]['name']          = __('Tag');

      $tab[3]['table']         = 'glpi_plugin_shellcommands_shellcommandpaths';
      $tab[3]['field']         = 'name';
      $tab[3]['linkfield']     = 'plugin_shellcommands_shellcommandpaths_id';
      $tab[3]['name']          = __('Path','shellcommands');
      $tab[3]['datatype']      = 'itemlink';

      $tab[4]['table']         = $this->gettable();
      $tab[4]['field']         = 'parameters';
      $tab[4]['name']          = __('Windows','shellcommands');

      $tab[5]['table']         = 'glpi_plugin_shellcommands_shellcommands_items';
      $tab[5]['field']         = 'itemtype';
      $tab[5]['nosearch']      = true;
      $tab[5]['massiveaction'] = false;
      $tab[5]['name']          = _n('Associated item type','Associated item types',2);
      $tab[5]['forcegroupby']  = true;
      $tab[5]['joinparams']    = array('jointype' => 'child');
      $tab[5]['datatype']      = 'dropdown';

      $tab[30]['table']        = $this->gettable();
      $tab[30]['field']        = 'id';
      $tab[30]['name']         = __('ID');
      $tab[30]['datatype']     = 'integer';

      $tab[80]['table']        = 'glpi_entities';
      $tab[80]['field']        = 'completename';
      $tab[80]['name']         = __('Entity');
      $tab[80]['datatype']      = 'dropdown';

      $tab[81]['table']        = $this->gettable();
      $tab[81]['field']        = 'is_recursive';
      $tab[81]['name']         = __('Child entities');
      $tab[81]['datatype']     = 'bool';

      return $tab;
   }

   function defineTabs($options = array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginShellcommandsShellcommand_Item', $ong, $options);
      $this->addStandardTab('PluginShellcommandsCommandGroup_Item', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td>".__('Valid tags')."</td>";
      echo "<td>[ID], [NAME], [IP], [MAC], [NETWORK], [DOMAIN]</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Tag')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "link", array('size' => "50"));
      echo "</td>";
      
            
      echo "<td>".__('Tag position', 'shellcommands')."</td>";
      echo "<td>";
      Dropdown::showFromArray("tag_position", array(__('Before parameters', 'shellcommands'), __('After parameters', 'shellcommands')), array('value' => $this->fields["tag_position"]));
      echo "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Windows','shellcommands')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "parameters");
      echo "</td>";

      echo "<td>".__('Path','shellcommands')."</td>";
      echo "<td>";
      Dropdown::show('PluginShellcommandsShellcommandPath', array('value' => $this->fields["plugin_shellcommands_shellcommandpaths_id"]));
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */

   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_shellcommands_shellcommands_items`
              WHERE `plugin_shellcommands_shellcommands_id`='".$this->fields['id']."'";
   }

   function dropdownCommands($itemtype) {
      global $DB;

      $query = "SELECT `".$this->gettable()."`.`id`, `".$this->gettable()."`.`name`,`".$this->gettable()."`.`link`
          FROM `".$this->gettable()."`,`glpi_plugin_shellcommands_shellcommands_items`
          WHERE `".$this->gettable()."`.`id` = `glpi_plugin_shellcommands_shellcommands_items`.`plugin_shellcommands_shellcommands_id`
          AND `glpi_plugin_shellcommands_shellcommands_items`.`itemtype` = '".$itemtype."'
          AND `".$this->gettable()."`.`is_deleted` = '0'
          ORDER BY `".$this->gettable()."`.`name`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $elements = array(Dropdown::EMPTY_VALUE);
      if ($number != "0") {
         while ($data = $DB->fetch_assoc($result)) {
            $elements[$data["id"]] = $data["name"];
         }
      }
      
      Dropdown::showFromArray('command', $elements);
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    * */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
    /**
    * Get the specific massive actions
    * 
    * @since version 0.84
    * @param $checkitem link item to check right   (default NULL)
    * 
    * @return an array of massive actions
    **/
   public function getSpecificMassiveActions($checkitem = NULL) {
      $actions = parent::getSpecificMassiveActions($checkitem);

      $actions['PluginShellcommandsShellcommand'.MassiveAction::CLASS_ACTION_SEPARATOR.'install'] = _x('button', 'Associate');
      $actions['PluginShellcommandsShellcommand'.MassiveAction::CLASS_ACTION_SEPARATOR.'uninstall'] = _x('button', 'Dissociate');

      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "install":
            Dropdown::showItemTypes("item_item",self::getTypes(true));
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
         case "uninstall":
            Dropdown::showItemTypes("item_item",self::getTypes(true));
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
         case 'generate':
            $PluginShellcommandsShellcommand = new PluginShellcommandsShellcommand();
            $itemtype = $ma->getItemtype(false);
            if (in_array($itemtype, PluginShellcommandsShellcommand::getTypes(true))) {
               $PluginShellcommandsShellcommand->dropdownCommands($itemtype);
               echo "<br><br>";
            }
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      global $CFG_GLPI;
      
      $command_item = new PluginShellcommandsShellcommand_Item();

      switch ($ma->getAction()) {
         
         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($command_item->addItem($key, $input['item_item'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            break;
         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($command_item->deleteItemByShellCommandsAndItem($key, $input['item_item'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            break;
         case 'generate':
            if ($ma->POST['command']) {
               $_SESSION["plugin_shellcommands"]["massiveaction"] = $ma;
               $_SESSION["plugin_shellcommands"]["ids"]           = $ids;
               
               $ma->results['ok'] = 1;
               $ma->display_progress_bars = false;

               echo "<script type='text/javascript'>";
               echo "location.href='".$CFG_GLPI['root_doc']."/plugins/shellcommands/front/massiveexec.php';";
               echo "</script>";
               
            }
            break;
      }
   }
   
   /**
    * Handle shellcommand message
    * 
    * @param $message 
    * 
    **/
   static function handleShellcommandResult($error, $message){

      if (preg_match('/^WARNING/i', $message)) {
         return self::WARNING_RESULT;
         
      } else if(preg_match('/^OK/i', $message)){
         return self::OK_RESULT;
         
      } else if(preg_match('/^CRITICAL/i', $message)){
         return self::CRITICAL_RESULT;
         
      } else {
         if ($error) {
            return self::KO_RESULT;
         }
         return self::OK_RESULT;
      }
   }
   
   /**
    *  Display command result
    * 
    * @param $message 
    * 
    **/
   static function displayCommandResult(PluginShellcommandsShellcommand $shellcommands, $targetParam, $message, $error){
      global $CFG_GLPI;
      
      $result = PluginShellcommandsShellcommand::handleShellcommandResult($error, $message);

      // Result icon
      echo "<tr class='tab_bg_1 shellcommands_result_line'>";
      switch ($result) {
         case PluginShellcommandsShellcommand::OK_RESULT :
            echo "<td class='center'><img src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/ok.png'></td>";
            break;
         case PluginShellcommandsShellcommand::WARNING_RESULT :
            echo "<td class='center'><img src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/warning.png'></td>";
            break;
         case PluginShellcommandsShellcommand::KO_RESULT :
            echo "<td class='center'><img src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/ko.png'></td>";
            break;
         case PluginShellcommandsShellcommand::CRITICAL_RESULT :
            echo "<td class='center'><img src='".$CFG_GLPI["root_doc"]."/plugins/shellcommands/pics/ko.png'></td>";
            break;
      }

      echo "<td class='center'>".$shellcommands->getName()."</td>";

      // Result short message
      switch ($result) {
         case PluginShellcommandsShellcommand::OK_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ok'>OK</div></td>";
            break;
         case PluginShellcommandsShellcommand::WARNING_RESULT :
            echo "<td class='center'><div class='shellcommands_result_warning'>WARNING</div></td>";
            break;
         case PluginShellcommandsShellcommand::KO_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ko'>KO</div></td>";
            break;
         case PluginShellcommandsShellcommand::CRITICAL_RESULT :
            echo "<td class='center'><div class='shellcommands_result_ko'>CRITICAL</div></td>";
            break;
      }

      echo "<td>";
      if ($command = PluginShellcommandsShellcommand_Item::getCommandLine($shellcommands->getID(), $targetParam)) {
         echo "<b> > ".$command."</b><br>";
      }
      if ($shellcommands->getName() !== PluginShellcommandsShellcommand_Item::WOL_COMMAND_NAME) {
         echo "<font color='blue'>".nl2br($message)."</font>";
      } else {
         echo nl2br($message);
      }
      echo "</td>";
      echo "</tr>";
   }
   
   static function getMenuContent() {
      $plugin_page = "/plugins/shellcommands/front/menu.php";
      $menu = array();
      //Menu entry in helpdesk
      $menu['title'] = self::getTypeName(2);
      $menu['page'] = $plugin_page;
      $menu['links']['search'] = $plugin_page;
      
      $menu['options']['shellcommand']['title']            = _n('Shell Command', 'Shell Commands', 2, 'shellcommands');
      $menu['options']['shellcommand']['page']             = '/plugins/shellcommands/front/shellcommand.php';
      $menu['options']['shellcommand']['links']['add']     = '/plugins/shellcommands/front/shellcommand.form.php';
      $menu['options']['shellcommand']['links']['search']  = '/plugins/shellcommands/front/shellcommand.php';
      
      $menu['options']['commandgroup']['title']            = _n('Command group', 'Command groups', 2, 'shellcommands');
      $menu['options']['commandgroup']['page']             = '/plugins/shellcommands/front/commandgroup.php';
      $menu['options']['commandgroup']['links']['add']     = '/plugins/shellcommands/front/commandgroup.form.php';
      $menu['options']['commandgroup']['links']['search']  = '/plugins/shellcommands/front/commandgroup.php';

      $menu['options']['advanced_execution']['title']      = _n('Advanced execution', 'Advanced executions', 2, 'shellcommands');
      $menu['options']['advanced_execution']['page']       = '/plugins/shellcommands/front/advanced_execution.php';

      return $menu;
   }
   
   
   /**
    * Custom fonction to process shellcommand massive action
   **/
   function doMassiveAction(MassiveAction $ma, array $ids){
      
      if (!empty($ids)) {
         $input = $ma->getInput();

         $itemtype = $ma->getItemType(false);
         $commands_id = $input['command'];

         switch($ma->getAction()){
            case 'generate':
               $shellcommands_item = new PluginShellcommandsShellcommand_Item();
               $shellcommands = new PluginShellcommandsShellcommand();
               $item = getItemForItemtype($itemtype);

               echo "<div class='center'>";
               echo "<table class='tab_cadre_fixe center'>";
               echo "<tr class='tab_bg_1'>";
               echo "<th colspan='4'>".PluginShellcommandsShellcommand::getTypeName(2)."</th>";
               echo "</tr>";

               foreach ($ids as $key => $items_id) {
                  if (!$shellcommands_item->getFromDBbyShellCommandsAndItem($commands_id, $itemtype)) {
                     continue;
                  }
                  $shellcommands->getFromDB($commands_id);
                  $item->getFromDB($items_id);
                  $targetParam = PluginShellcommandsShellcommand_Item::resolveLinkOfCommand($shellcommands->getID(), $item);
                  // Exec command on each targets : stop on first success
                  $selectedTarget = null;
                  if ($targetParam !== false) {
                     foreach ($targetParam as $target) {
                        list($error, $message) = PluginShellcommandsShellcommand_Item::execCommand($shellcommands->getID(), $target);
                        if (!$error) {
                           $selectedTarget = $target;
                           break;
                        }
                     }
                  }

                  echo "<tr class='tab_bg_1 shellcommands_result_line'>";
                  echo "<td class='center' colspan='4'>".__($item->getType()).' : '.$item->getLink()." - ".$selectedTarget."</td>";
                  echo "</tr>";
                  
                  PluginShellcommandsShellcommand::displayCommandResult($shellcommands, $selectedTarget, $message, $error);
               }
               echo "</table>";
               echo "</div>";
               break;
         }
      }
   }
   
}

?>