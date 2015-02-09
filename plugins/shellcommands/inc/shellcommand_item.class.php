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

class PluginShellcommandsShellcommand_Item extends CommonDBTM {

   // From CommonDBRelation
   static public $itemtype_1 = "PluginShellcommandsShellcommand";
   static public $items_id_1 = 'plugin_shellcommands_shellcommands_id';
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   
   static $rightname = 'plugin_shellcommands';
   
   /**
     * Name of Wake on LAN command
     * @var string
     */
   const WOL_COMMAND_NAME = 'Wake on Lan';

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
   }

   static function getClasses($all = false) {

      static $types = array(
         'Computer', 'NetworkEquipment', 'Peripheral',
         'Phone', 'Printer'
      );

      if ($all) {
         return $types;
      }

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

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      
      if (!$withtemplate) {
         if ($item->getType() == 'PluginShellcommandsShellcommand'
                 && count(PluginShellcommandsShellcommand::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(__('Associated item'), self::countForShellcommand($item));
            }
            return __('Associated item');
         } else if (in_array($item->getType(), PluginShellcommandsShellcommand::getTypes(true))
                 && self::canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginShellcommandsShellcommand::getTypeName(2), self::countForItem($item));
            }
            return PluginShellcommandsShellcommand::getTypeName(2);
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      
      if ($item->getType() == 'PluginShellcommandsShellcommand') {
         self::showForShellcommands($item);
         
      } else if (in_array($item->getType(), PluginShellcommandsShellcommand::getTypes(true))) {
         self::showForItem($item);
         PluginShellcommandsCommandGroup_Item::showForItem($item);
      }
      return true;
   }

   static function countForShellcommand(PluginShellcommandsShellcommand $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_shellcommands_shellcommands_items', "`itemtype` IN ('$types')
                                   AND `plugin_shellcommands_shellcommands_id` = '".$item->getID()."'");
   }

   static function countForItem(CommonDBTM $item, $options = array()) {
      global $DB;
     
      $ID = $item->getField('id');

      $query = "SELECT `glpi_plugin_shellcommands_shellcommands_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_shellcommands_shellcommands`.`name` AS assocName,
                       `glpi_plugin_shellcommands_shellcommands`.*
                FROM `glpi_plugin_shellcommands_shellcommands_items`
                LEFT JOIN `glpi_plugin_shellcommands_shellcommands`
                 ON (`glpi_plugin_shellcommands_shellcommands_items`.`plugin_shellcommands_shellcommands_id`=`glpi_plugin_shellcommands_shellcommands`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_shellcommands_shellcommands`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_shellcommands_shellcommands_items`.`itemtype` = '".$item->getType()."' ";

      $query .= getEntitiesRestrictRequest(" AND", "glpi_plugin_shellcommands_shellcommands", '', '', true);

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;

      $shells = array();
      if ($number) {
         while ($data = $DB->fetch_assoc($result)) {
            $shells[$data['assocID']] = $data;
         }
      }

      $countCommand = array();
      $count = 0;
      
      if ($number) {

         foreach ($shells as $data) {
            
            $link = $data["link"];
            $item->getFromDB($ID);

            if (strstr($link, '[NAME]')) {
               // NAME
               $countCommand['[NAME]'.$data['id']][] = 1;
               $count++;
            } else if(strstr($link, '[ID]')) {
               // ID
               $countCommand['[ID]'.$data['id']][] = 1;
               $count++;
            } else if (strstr($link, '[DOMAIN]')) {
               // DOMAIN
               if (isset($item->fields['domains_id'])){
                  $countCommand['[DOMAIN]'.$data['id']][] = 1;
                  $count++;
               }
            } else if (strstr($link, '[IP]') || strstr($link, '[MAC]')) {
               $mac = array();
               $ip = array();
               $i = 0;
               $query2 = "SELECT `glpi_networkports`.*, `glpi_ipaddresses`.`name` as ip
                              FROM `glpi_networkports`
                              LEFT JOIN `glpi_networknames`
                                 ON (`glpi_networknames`.`items_id`=`glpi_networkports`.`id`)                            
                              LEFT JOIN `glpi_ipaddresses`
                                 ON (`glpi_networknames`.`id`=`glpi_ipaddresses`.`items_id`) 
                              WHERE `glpi_networkports`.`items_id` = '$ID' 
                              AND `glpi_networkports`.`itemtype` = '".$item->getType()."' 
                              ORDER BY `glpi_networkports`.`logical_number`";
               $result2 = $DB->query($query2);
               if ($DB->numrows($result2) > 0)
                  while ($data2 = $DB->fetch_array($result2)) {
                     if((!empty($data2["ip"]) && $data2["ip"] != '0.0.0.0')){
                        $ip[$i]['ip'] = $data2["ip"];
                        $i++;
                     }
                     if(!empty($data2["mac"])){
                        $mac[$i]['mac'] = $data2["mac"];
                        $i++;
                     }
                  }
               if (strstr($link, '[IP]')) {
                  // IP internal switch
                  if ($item->getType() == 'NetworkEquipment'){
                     $countCommand['[IP]'.$data['id']][] = 1;
                     $count++;
                  }
                  
                  if (count($ip) > 0){
                     foreach ($ip as $val) $countCommand['[IP]'.$data['id']][] = 1;
                     $count++;
                  }
               }
               if (strstr($link, '[MAC]')) {
                  // MAC internal switch
                  if ($item->getType() == 'NetworkEquipment'){
                     $countCommand['[MAC]'.$data['id']][] = 1;
                     $count++;
                  }
                  
                  if (count($mac) > 0){
                     foreach ($mac as $val) $countCommand['[MAC]'.$data['id']][] = 1;
                     $count++;
                  }
               }
            }
         }
      }
      
      if (isset($options['type']) && isset($options['itemId']) 
              && isset($countCommand['[IP]'.$options['itemId']]) 
              && stristr($options['type'] ,'ip')) 
         return sizeof($countCommand['[IP]'.$options['itemId']]);
      
      elseif (isset($options['type']) && isset($options['itemId']) 
              && isset($countCommand['[MAC]'.$options['itemId']]) 
              && stristr($options['type'] ,'mac')) 
         return sizeof($countCommand['[MAC]'.$options['itemId']]);
      
      elseif (isset($options['type']) && $options['type'] == 'ALL') 
         return $countCommand;
      
      else return $count;
   }

   function getFromDBbyShellCommandsAndItem($plugin_shellcommands_shellcommands_id, $itemtype) {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."` ".
              "WHERE `plugin_shellcommands_shellcommands_id` = '".$plugin_shellcommands_shellcommands_id."' 
         AND `itemtype` = '".$itemtype."' ";
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
   
   /**
    * Get itemtypes of a shellcommand
    *
    * @param $plugin_shellcommands_shellcommands_id 
    *
    * @return array $itemtype
    * */
   function getShellCommandItemtypes($plugin_shellcommands_shellcommands_id) {
      global $DB;

      $itemtypes = array();

      $data = $this->find("`plugin_shellcommands_shellcommands_id` = $plugin_shellcommands_shellcommands_id");

      if (!empty($data)) {
         foreach ($data as $val) {
            $itemtypes[] = __($val['itemtype']);
         }
      }

      return $itemtypes;
   }

   function addItem($plugin_shellcommands_shellcommands_id, $itemtype) {

      $this->add(array('plugin_shellcommands_shellcommands_id' => $plugin_shellcommands_shellcommands_id, 'itemtype' => $itemtype));
      return true;
   }

   function deleteItemByShellCommandsAndItem($plugin_shellcommands_shellcommands_id, $itemtype) {

      if ($this->getFromDBbyShellCommandsAndItem($plugin_shellcommands_shellcommands_id, $itemtype)) {
         $this->delete(array('id' => $this->fields["id"]));
         return true;
      }
      return false;
   }

   /**
    * Show items links to a shellcommands
    *
    * @since version 0.84
    *
    * @param $shellcommand PluginShellcommandsShellcommand object
    *
    * @return nothing (HTML display)
    * */
   public static function showForShellcommands(PluginShellcommandsShellcommand $shellcommand) {
      global $DB, $CFG_GLPI;

      $shell_id = $shellcommand->getField('id');

      $canedit = $shellcommand->can($shell_id, UPDATE);
      $rand = mt_rand();

      if (!Session::haveRight("link", READ)
              || !$shellcommand->can($shell_id, READ)) {
         return false;
      }

      $query = "SELECT *
                FROM `glpi_plugin_shellcommands_shellcommands_items`
                WHERE `plugin_shellcommands_shellcommands_id` = '$shell_id'
                ORDER BY `itemtype`";
      $result = $DB->query($query);
      $types = array();
      $used = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $types[$data['id']] = $data;
            $used[$data['itemtype']] = $data['itemtype'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='changeticket_form$rand' id='changeticket_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL('PluginShellcommandsShellcommand')."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item type')."</th></tr>";

         echo "<tr class='tab_bg_2'><td class='right'>";
         echo "<input type='hidden' name='plugin_shellcommands_shellcommands_id' value='$shell_id'>";
         Dropdown::showItemTypes('itemtype', PluginShellcommandsShellcommand::getTypes(true));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='additem' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $numrows) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand, 'num_displayed' => $numrows);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      if ($canedit && $numrows) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Type')."</th>";
      echo "</tr>";

      foreach ($types as $data) {
         $typename = NOT_AVAILABLE;
         if ($item = getItemForItemtype($data['itemtype'])) {
            $typename = $item->getTypeName(1);
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }
            echo "<td class='center'>$typename</td>";
            echo "</tr>";
         }
      }
      echo "</table>";
      if ($canedit && $numrows) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
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
      
      $canedit = $item->canadditem('PluginShellcommandsShellcommand');
      $rand = mt_rand();
      $is_recursive = $item->isRecursive();

      $query = "SELECT `glpi_plugin_shellcommands_shellcommands_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_shellcommands_shellcommands`.`name` AS assocName,
                       `glpi_plugin_shellcommands_shellcommands`.*
                FROM `glpi_plugin_shellcommands_shellcommands_items`
                LEFT JOIN `glpi_plugin_shellcommands_shellcommands`
                 ON (`glpi_plugin_shellcommands_shellcommands_items`.`plugin_shellcommands_shellcommands_id`=`glpi_plugin_shellcommands_shellcommands`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_shellcommands_shellcommands`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_shellcommands_shellcommands_items`.`itemtype` = '".$item->getType()."' 
                  AND !`glpi_plugin_shellcommands_shellcommands`.`is_deleted`";

      $query .= getEntitiesRestrictRequest(" AND", "glpi_plugin_shellcommands_shellcommands", '', '', true);

      $query .= " ORDER BY `assocName`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;

      $shells = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $shells[$data['assocID']] = $data;
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".__('Associated Commands', 'shellcommands')."</th>";
      echo "</tr>";

      if ($number) {

         Session::initNavigateListItems('PluginShellcommandsShellcommand',
                 //TRANS : %1$s is the itemtype name,
                 //        %2$s is the name of the item (used for headings of a list)
                 sprintf(__('%1$s = %2$s'), $item->getTypeName(1), $item->getName()));
         
         $selectCommandName[0] = Dropdown::EMPTY_VALUE;
//         $countCommand = self::countForItem($item, array('type' => 'ALL'));
         foreach ($shells as $data) {
//            if(isset($countCommand[$data['link'].$data['id']]))
               $selectCommandName[$data['link'].'-'.$data['id']] = $data['assocName'];
         }
         
         echo "<tr class='tab_bg_2'>
               <td class='center'>".PluginShellcommandsShellcommand::getTypeName(1)." ";
         $randSelect = Dropdown::showFromArray("name", $selectCommandName, array('width' => $width));
         echo "<span id='command_name$randSelect'></span></td>";
         echo "</tr>";
         
         Ajax::updateItemOnSelectEvent("dropdown_name$randSelect", "command_name$randSelect", $CFG_GLPI["root_doc"]."/plugins/shellcommands/ajax/dropdownCommandValue.php", 
                 array('idtable'        => $item->getType(),
                       'width'          => $width,
                       'value'          => '__VALUE__',
                       'itemID'         => $ID,
                       'countItem'      => 1,
                       'itemtype'       => $item->getType(),
                       'toupdate'       => 'shellcommand_result',
                       'command_type'   => 'PluginShellcommandsShellcommand',
                       'myname'         => "command_name"));
      }

      echo "</table>";
      echo "</div>";
      echo "<div class='spaced' id='shellcommand_result'></div>";
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
      
      $targetParam = $values['value'];
      $commandName = Dropdown::getDropdownName("glpi_plugin_shellcommands_shellcommands", $values['id']);

      $shellcommands = new PluginShellcommandsShellcommand();
      $shellcommands->getFromDBbyName($commandName);
      
      list($error, $message) = self::execCommand($shellcommands->fields['id'], $targetParam);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe center'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__('Result details')."</th>";
      echo "</tr>";
      
      PluginShellcommandsShellcommand::displayCommandResult($shellcommands, $targetParam, $message, $error);
      
      echo "</table>";
      echo "</div>";
   }
    
   /**
   * Resolve the "link" of a command for a specific inventory item
   * 
   * @param integer $commandId        Shellcommand ID
   * @param mixed $item               GLPI Item for which the command's link should be resolved
   * @param boolean $fetchItemName    (Optional) Also fetches the items' names
   *  
   * @return array|boolean            Resolved links, bool(false) in case of any error
   */
   static function resolveLinkOfCommand($commandId, $item, $fetchItemName = false) {
      global $DB;
     
      $resolvedLinks = array();
      $currentlyResolvedLink = array();
      $networkportResolvedLinks = array();
     
      $command = new PluginShellcommandsShellcommand();
      if ($command->getFromDB($commandId) && $item instanceof CommonDBTM && $item->getID() != -1) { // If both command and $item exists
         $commandLink = $command->fields['link'];
         $currentlyResolvedLink = $commandLink;
         
         if (strstr($commandLink, '[NAME]')) { // Handle "NAME" tag
            $currentlyResolvedLink = str_replace('[NAME]', $item->getField('name'), $currentlyResolvedLink);
         }
         if (strstr($commandLink, '[ID]')) { // Handle "ID" tag
            $currentlyResolvedLink = str_replace('[ID]', $item->getID(), $currentlyResolvedLink);
         }
         if (strstr($commandLink, '[DOMAIN]')) {
            if (isset($item->fields['domains_id'])) {
               $currentlyResolvedLink = str_replace('[DOMAIN]', Dropdown::getDropdownName('glpi_domains', $item->getField('domains_id')), $currentlyResolvedLink);
            }
            $currentlyResolvedLink = str_replace('[DOMAIN]', '', $currentlyResolvedLink); // Clean [DOMAIN] tag
         }
         if (strstr($commandLink, '[IP]') || strstr($commandLink, '[MAC]')) { // Handle "IP" and "MAC" tags
            $handledTags = array(
               // TAG => rowset key
               '[IP]'  => 'ip',
               '[MAC]' => 'mac',
            );
            
            // Fetches every IP (glpi_ipaddresses) and MAC of any network ports (glpi_networkports) of given item:
            //Note: As of GLPI 0.84 it returns both IPv4 and IPv6 under the same "ip" key. Usually convenient but can bother some commands
            //Internet Protocol version could be filtered using `glpi_ipaddresses`.`version`
            $networkportsRowset = $DB->query(
               'SELECT DISTINCT
                  `glpi_networkports`.`name` AS "name",
                  `glpi_networkports`.`mac` AS "mac",
                  `glpi_ipaddresses`.`name` AS "ip"
               FROM
                  `glpi_networkports`
                  JOIN `glpi_networknames`
                     ON (`glpi_networknames`.`itemtype` = \'NetworkPort\' AND `glpi_networknames`.`items_id` = `glpi_networkports`.`id`)
                  JOIN `glpi_ipaddresses`
                     ON (`glpi_ipaddresses`.`itemtype` = \'NetworkName\' AND `glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)
               WHERE
                  `glpi_networkports`.`items_id` = ' . $item->getID() . '
                  AND `glpi_networkports`.`itemtype` = \'' . $DB->escape(get_class($item)) . '\'
                  AND `glpi_networkports`.`is_deleted` = 0
                  AND `glpi_networknames`.`is_deleted` = 0
                  AND `glpi_ipaddresses`.`is_deleted` = 0
               ORDER
                  BY `glpi_networkports`.`logical_number` ASC');
            
            while ($networkportRow = $DB->fetch_assoc($networkportsRowset)) { // For each found network port
               $currentlyResolvedNetworkport = $currentlyResolvedLink;
               foreach ($handledTags as $currentHandledTag => $currentHandledRowsetKey) { // For each handled tags
                  if (strstr($commandLink, $currentHandledTag)) { // Tag in $commandLink?
                     if (array_key_exists($currentHandledRowsetKey, $networkportRow) && !empty($networkportRow[$currentHandledRowsetKey])) { // Matching key found in row
                        $currentlyResolvedValue = $networkportRow[$currentHandledRowsetKey];
                     } else {
                        $currentlyResolvedValue = ''; // To clean this unmatched tag
                     }
                     $currentlyResolvedNetworkport = str_replace($currentHandledTag, $currentlyResolvedValue, $currentlyResolvedNetworkport);
                  }
               }
               
               if (!empty($currentlyResolvedNetworkport)) { // If something was resolved
                  if ($fetchItemName) {
                     $resolvedLinks[] = array($networkportRow['name'] => $currentlyResolvedNetworkport);
                  } else {
                     $resolvedLinks[] = $currentlyResolvedNetworkport;
                  }
               }
            }
            
            /* Remove/clean tags */
            foreach (array_keys($handledTags) as $currentHandledTag) { // For each handled tags
               if (strstr($commandLink, $currentHandledTag)) { // Tag in $commandLink?
                  $currentlyResolvedLink = str_replace($currentHandledTag, '', $currentlyResolvedLink); // Clean
               }
            }
            /* /Remove/clean tags */
         }
         if (!empty($currentlyResolvedLink)) {
            if ($fetchItemName) {
               $resolvedLinks[] = array($item->getField('name') => $currentlyResolvedLink);
            } else {
               $resolvedLinks[] = $currentlyResolvedLink;
            }
         }
         
         if (!$fetchItemName) {
            $resolvedLinks = array_unique($resolvedLinks); // Remove possible duplicates
         }
         
         //DROP NULL IP
         if (strstr($commandLink, '[IP]')) {
            foreach ($resolvedLinks as $k => $v) {
               if ($v == '0.0.0.0') {
                  unset($resolvedLinks[$k]);
               }
            }
         }
         
         return $resolvedLinks;
      } else {
         return false;
      }
   }
  
   /**
   * Compute the command
   * 
   * @param integer $commandId     ID of the command to execute
   * @param string $targetParam    Target of the command
   * 
   * @return string|null    Command line, null if no command line for this shellcommand
   */
   static function getCommandLine($commandId, $targetParam) {
      $commandToExec = '';
     
      $command = new PluginShellcommandsShellcommand();
      if (!$command->getFromDB($commandId)) { // Command not found
         $commandToExec = null;
      } else {
         if ($command->fields['name'] === self::WOL_COMMAND_NAME) {
            $commandToExec = null;
         } else {
            $commandPath = Dropdown::getDropdownName('glpi_plugin_shellcommands_shellcommandpaths', 
                                                      $command->fields["plugin_shellcommands_shellcommandpaths_id"]);
            $commandParameters = $command->fields["parameters"];
            if ($command->fields["tag_position"]) {
               $commandToExec = $commandPath.' '.$commandParameters.' '.$targetParam;
            } else {
               $commandToExec = $commandPath.' '.$targetParam.' '.$commandParameters;
            }
         }
      }
      return str_replace(array("\r\n", "\r"), "", $commandToExec);
   }
  
   /**
   * Actually executes the Shellcommand
   * 
   * @param integer $commandId     ID of the command to execute
   * @param string $targetParam    Target of the command
   * 
   * @return string    Execution output, bool(false) in case of error
   */
   static function execCommand($commandId, $targetParam) {
      
      $output    = null;
      $execOuput = array();
      $error     = 1;
      
      $command = new PluginShellcommandsShellcommand();
      $commandFound = $command->getFromDB($commandId);
      
      // Lauch command
      if ($commandFound) {
         if ($command->fields['name'] === self::WOL_COMMAND_NAME) {
            $command_item = new PluginShellcommandsShellcommand_Item();
            ob_start();
            $command_item->sendMagicPacket($targetParam);
            $output = ob_get_clean();

         } else {
            if (($commandToExec = self::getCommandLine($commandId, $targetParam)) !== null) {
               exec($commandToExec." 2>&1", $execOuput, $error);
            }
         }
      }
      
      // Format output message
      foreach ($execOuput as $currentOutputLine) {
         if (!is_array($currentOutputLine)) {
            $output .= Toolbox::encodeInUtf8($currentOutputLine).PHP_EOL;
         }
      }

      return array($error, $output);
   }

   //static function lauchCommand($values) {
   //   $host = $values['value'];
   //   $id = $values['plugin_shellcommands_shellcommands_id'];

   //   $command = new PluginShellcommandsShellcommand();
   //   $command_item = new PluginShellcommandsShellcommand_Item();
   //   $command->getFromDBbyName($id);
   //   $path = Dropdown::getDropdownName("glpi_plugin_shellcommands_shellcommandpaths", $command->fields["plugin_shellcommands_shellcommandpaths_id"]);
   //   $parameters = $command->fields["parameters"];

   //   echo "<div align='center'>";
   //   echo "<table class='tab_cadrehov' cellpadding='5'>";
   //   echo "<tr><th>".__('Command', 'shellcommands');
   //   echo "</th></tr>";
   //   echo "<tr class='tab_bg_2'><td>";

   //   echo "<p><b>$id -> ".$host."</b><br>";

   //   if ($id == "Wake on Lan")
   //      $command = $command_item->sendMagicPacket($host);
   //   else
   //      $command = $path." ".$parameters." ".$host;

   //   $ouput[] = null;
   //   if ($id != "Wake on Lan") {
   //      exec($command, $ouput);
   //      $cmd = count($ouput);
   //      echo "<font color=blue>";
   //      for ($i = 0; $i < $cmd; $i++) {
   //         echo Toolbox::encodeInUtf8($ouput[$i])."<br>";
   //      }
   //      echo "</font>";
   //      echo "</p>".$command;
   //   }

   //   echo "</td></tr></table></div>";
   //}

   /* DoMagicPacket : Fabrique le "paquet magic" permettant le "reveil" des PC
     Description du paquet :
     header : 6 octets de valeur 0xff
     corps : on repete 16 fois l'adresse MAC du pc a reveiller
    */

   function doMagicPacket($MacAddress) {

      $p_header = '';
      for ($i = 0; $i < 6; $i++)
         $p_header .= chr(0xff);

      //Les adresses MAC doivent etre s�par�es par des ":"
      //c'est normalement le cas si les donnees ont ete importees avec OCS
      $fragment = explode(":", $MacAddress);
      $body = '';

      for ($i = 0; $i < 16; $i++) {
         for ($j = 0; $j < 6; $j++)
            $body .= chr(hexdec($fragment[$j]));
      }
      return $p_header.$body;
   }

   //envoie le packet magique en UDP
   //broacast ip => Ne passe pas les routeurs
   function sendMagicPacket($macaddress, $ip = null, $netmask = null) {
      $packet = $this->doMagicPacket($macaddress);

      $ip = ip2long($ip);
      $mask = ip2long($netmask);
      $broadcast = null;
      // Si l'IP et le masque sont fournis (et sont valides) on tente de calculer l'adresse de broadcast du reseau
      if ($ip !== false && $mask !== false) {
         $mask = ~ $mask;
         $broadcast = long2ip($ip | $mask);
      }

      $error = 0;
      $mcastaddr = "224.0.0.1";

      /* TO BE TESTED
        $addr_byte = explode(':', $macaddress);
        $hw_addr = '';
        for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
        $packet = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
        for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr; */

      if (is_null($broadcast)) {

         //j'arrive pas a faire du broadcast avec cette methode alors c'est du multicast pour tous les
         //postes d'un ss reseau
         $sock = fsockopen("udp://".$mcastaddr, 9, $errno, $errostr);
         //$sock = fsockopen("udp://255.255.255.255", 9, $errno, $errostr);
         if (!$sock) {
            echo __('There is an error with socket creation', 'shellcommands')." : ";
            echo Toolbox::encodeInUtf8($errostr)." ".Toolbox::encodeInUtf8($errno);
            echo "<br />";
            $error = 1;
         } else {
            fwrite($sock, $packet);
            fclose($sock);
         }
      } else {

         // Cette methode necessite que php soit compile avec les sockets
         $sock = socket_create(AF_INET, SOCK_DGRAM, 0);
         //TO BE TESTED $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

         if ($sock < 0) {
            echo "Error creating socket : ".strerror($sock)."\n";
            echo "Error code is '".socket_last_error($sock)."' - ";
            echo socket_strerror(socket_last_error($sock));
            $error = 1;
         } else {
            $opt_ret = socket_set_option($sock, 1, 6, TRUE);
            if ($opt_ret < 0) {
               echo "setsockopt() failed, error: ".strerror($opt_ret)."\n";
               $error = 1;
            } else {
               $send_ret = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 9);
               //TO BE TESTED $send_ret = socket_sendto($sock, $packet, strlen($packet), 0, $ip, $socket_number) $socket_number=7 ?
               if ($send_ret < 0) {
                  echo "Error when sending packet ".strerror($send_ret)."<BR>\n";
                  $error = 1;
               } else {
                  socket_close($sock);
               }
            }
         }
      }

      if ($error != 1) {
         $txt = is_null($broadcast) ? $mcastaddr : $broadcast;
         echo __('Magic packet sending to', 'shellcommands')." ".$macaddress." (".$txt.")";
      } else {
         echo __('The packet cannot be send', 'shellcommands');
      }
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    * */
   public function getForbiddenStandardMassiveAction() {
      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }

}

?>