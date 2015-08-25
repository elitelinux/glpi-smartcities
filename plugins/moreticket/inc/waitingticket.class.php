<?php

/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMoreticketWaitingTicket extends CommonDBTM {

   static $types     = array('Ticket');
   var $dohistory = true;
   static $rightname = "plugin_moreticket";
   
   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
   **/
   static function canCreate() {
      
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, UPDATE);
      }
      return false;
   }

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   public static function getTypeName($nb = 0) {

      return _n('Waiting ticket', 'Waiting tickets', $nb, 'moreticket');
   }

   /**
    * Display moreticket-item's tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $config = new PluginMoreticketConfig();

      if (!$withtemplate) {
         if ($item->getType() == 'Ticket' && $config->useWaiting() == true) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName(2), countElementsInTable($this->getTable(), "`tickets_id` = '".$item->getID()."'"));
            }
            return self::getTypeName(2);
         }
      }
      return '';
   }

   /**
    * Display tab's content for each users
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (in_array($item->getType(), PluginMoreticketWaitingTicket::getTypes(true))) {
         self::showForTicket($item);
      }
      return true;
   }

   // Check the mandatory values of forms
   static function checkMandatory($values, $add = false) {

      $checkKo   = array();
      $dateError = false;
      
      $config = new PluginMoreticketConfig();
      
      $mandatory_fields = array('reason' => __('Reason', 'moreticket'));
                                   
      if ($config->mandatoryReportDate() == true) {
         $mandatory_fields['date_report'] = __('Postponement date', 'moreticket');
      } 
      
      if ($config->mandatoryWaitingType() == true) {
         $mandatory_fields['plugin_moreticket_waitingtypes_id'] = PluginMoreticketWaitingType::getTypeName(1);
      }

      $msg = array();

      foreach ($mandatory_fields as $key => $value) {
         if (!array_key_exists($key, $values)) {
            $msg[]     = $value;
            $checkKo[] = 1;
         }
      }

      foreach ($values as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if ($key != 'date_report' && empty($value)) {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            } else if ($key == 'date_report' && $value == 'NULL') {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            } else if ($key == 'date_report' && strtotime($value) <= time()) {
               $dateError = Html::convDateTime($value);
               $checkKo[] = 1;
            }
         }

         $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $value;
      }

      if (in_array(1, $checkKo)) {
         if (!$add) {
            $errorMessage = __('Waiting ticket cannot be saved', 'moreticket')."<br>";
         } else {
            $errorMessage = __('Ticket cannot be saved', 'moreticket')."<br>";
         }

         if ($dateError) {
            $errorMessage .= __("Report date is inferior of today's date", 'moreticket')." : ".$dateError."<br>";
         }

         if (count($msg)) {
            $errorMessage .= _n('Mandatory field', 'Mandatory fields', 2)." : ".implode(', ', $msg);
         }

         Session::addMessageAfterRedirect($errorMessage, false, ERROR);

         return false;
      }

      return true;
   }

   /**
    * Print the waiting ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      // validation des droits
      if (!$this->canview()) {
         return false;
      }

      if ($ID > 0) {
         if (!$this->fields = self::getWaitingTicketFromDB($ID)) {
            $this->getEmpty();
         }
      } else {
         // Create item
         $this->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_waiting'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_waiting'] as $key => $value) {
            switch($key){
               case 'reason':
                  $this->fields[$key] = stripslashes($value);
                  break;
               default :
                  $this->fields[$key] = $value;
                  break;
            }
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_waiting']);
      
      $config = new PluginMoreticketConfig();
      
      echo "<div class='spaced' id='moreticket_waiting_ticket'>";
      echo "</br>";
      echo "<table class='moreticket_waiting_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo __('Reason', 'moreticket')."&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      Html::autocompletionTextField($this, "reason");
      echo "</td></tr>";
      echo "<tr><td>";
      echo PluginMoreticketWaitingType::getTypeName(1);
      if ($config->mandatoryWaitingType() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      $opt       = array('value'     => $this->fields['plugin_moreticket_waitingtypes_id']);
      Dropdown::show('PluginMoreticketWaitingType', $opt);
      echo "</td></tr>";
      echo "<tr><td>";
      _e('Postponement date', 'moreticket');
      
      if ($config->mandatoryReportDate() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      if ($this->fields['date_report'] == 'NULL') {
         $this->fields['date_report'] = date("Y-m-d H:i:s");
      }
      Html::showDateTimeFormItem("date_report", $this->fields['date_report'], 1, false);

      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   /**
    * Print the wainting ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   static function showForTicket($item) {
      global $CFG_GLPI;

      // validation des droits
      if (!Session::haveRight('plugin_moreticket', READ)) {
         return false;
      }

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }

      // Total Number of events
      $number = countElementsInTable("glpi_plugin_moreticket_waitingtickets", "`tickets_id`='".$item->getField('id')."'");

      if ($number < 1) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>".__('No historical')."</th></tr>";
         echo "</table>";
         echo "</div><br>";
         return;
      } else {
         echo "<div class='center'>";
         // Display the pager
         Html::printAjaxPager(__('Ticket suspension history', 'moreticket'), $start, $number);
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th>".__('Suspension date', 'moreticket')."</th>";
         echo "<th>".__('Reason', 'moreticket')."</th>";
         echo "<th>".PluginMoreticketWaitingType::getTypeName(1)."</th>";
         echo "<th>".__('Postponement date', 'moreticket')."</th>";
         echo "<th>".__('Suspension end date', 'moreticket')."</th>";
         echo"</tr>";

         foreach (self::getWaitingTicketFromDB($item->getField('id'), array('start' => $start,
            'limit' => $_SESSION['glpilist_limit'])) as $waitingTicket) {

            echo "<tr class='tab_bg_2'>";
            echo "<td>";
            echo Html::convDateTime($waitingTicket['date_suspension']);
            echo "</td>";
            echo "<td>";
            echo $waitingTicket['reason'];
            echo "</td>";
            echo "<td>";
            echo Dropdown::getDropdownName('glpi_plugin_moreticket_waitingtypes', $waitingTicket['plugin_moreticket_waitingtypes_id']);
            echo "</td>";
            echo "<td>";
            if ($waitingTicket['date_report'] =="0000-00-00 00:00:00") {
               echo _x('periodicity', 'None');
            } else {
               echo Html::convDateTime($waitingTicket['date_report']);
            }
            echo "</td>";
            echo "<td>";
            echo Html::convDateTime($waitingTicket['date_end_suspension']);
            echo "</td>";
            echo"</tr>";
         }

         echo "</table>";
         echo "</div>";
         Html::printAjaxPager(__('Ticket suspension history', 'moreticket'), $start, $number);
      }
   }

   // Get last waitingTicket 
   static function getWaitingTicketFromDB($tickets_id, $options = array()) {
      global $DB;

      if (sizeof($options) == 0) {
         $data_WaitingType = getAllDatasFromTable("glpi_plugin_moreticket_waitingtickets", '`tickets_id` = '.$tickets_id.
               ' AND `date_suspension` IN (SELECT max(`date_suspension`) 
                                                FROM `glpi_plugin_moreticket_waitingtickets` WHERE `tickets_id` = '.$tickets_id.')
                 AND (UNIX_TIMESTAMP(`date_end_suspension`) = 0 OR UNIX_TIMESTAMP(`date_end_suspension`) IS NULL)');
      } else {
         $data_WaitingType = getAllDatasFromTable("glpi_plugin_moreticket_waitingtickets", 'tickets_id = '.$tickets_id, false, '`date_suspension` DESC LIMIT '.intval($options['start']).",".intval($options['limit']));
      }
      
      if (sizeof($data_WaitingType) > 0) {
         if (sizeof($options) == 0)
            $data_WaitingType = reset($data_WaitingType);

         return $data_WaitingType;
      }

      return false;
   }

   static function preUpdateWaitingTicket($item) {
      $waiting_ticket = new self();
      // Then we add tickets informations
      if (isset($item->fields['id']) && isset($item->input['status']) && $item->input['status'] == CommonITILObject::WAITING) {

         if (self::checkMandatory($item->input)) {
            if ($item->input['date_report'] == "0000-00-00 00:00:00") {
               $item->input['date_report'] = 'NULL';
            }
            // Then we add tickets informations
            if ($waiting_ticket->add(array('reason'                            => $item->input['reason'],
                                           'tickets_id'                        => $item->fields['id'],
                                           'date_report'                       => $item->input['date_report'],
                                           'date_suspension'                   => date("Y-m-d H:i:s"),
                                           'date_end_suspension'               => 'NULL',
                                           'plugin_moreticket_waitingtypes_id' => $item->input['plugin_moreticket_waitingtypes_id']))) {

               unset($_SESSION['glpi_plugin_moreticket_waiting']);
            }
         } else {
            unset($item->input['status']);
         }
      }
   }

   static function postUpdateWaitingTicket($item) {

      $waiting_ticket = new self();
      // Then we add tickets informations
      if (isset($item->fields['id'])) {

         if (isset($item->oldvalues['status']) && $item->oldvalues['status'] == CommonITILObject::WAITING) {

            if (isset($item->input['status']) && $item->input['status'] != CommonITILObject::WAITING) {
               $fields = self::getWaitingTicketFromDB($item->fields['id']);
               if ($waiting_ticket->update(array('id'                  => $fields['id'],
                        'date_end_suspension' => date("Y-m-d H:i:s")))) {
                  unset($_SESSION['glpi_plugin_moreticket_waiting']);
               }
            }
         }
      }
   }

   // Hook done on before add ticket - checkMandatory
   static function preAddWaitingTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      // Then we add tickets informations
      if (isset($item->input['id']) && isset($item->input['status']) && $item->input['status'] == CommonITILObject::WAITING && !self::checkMandatory($item->input, true)) {

         $_SESSION['saveInput'][$item->getType()] = $item->input;
         $item->input                             = array();
      }

      return true;
   }

   // Hook done on after add ticket - add waitingtickets
   static function postAddWaitingTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $waiting_ticket = new self();
      // Then we add tickets informations
      if (isset($item->fields['id']) && $item->input['status'] == CommonITILObject::WAITING) {
         if (self::checkMandatory($item->input)) {

            if (empty($item->input['date_report'])) {
               $item->input['date_report'] = 'NULL';
            }
            // Then we add tickets informations
            if ($waiting_ticket->add(array('reason'                            => $item->input['reason'],
                                           'tickets_id'                        => $item->fields['id'],
                                           'date_report'                       => $item->input['date_report'],
                                           'date_suspension'                   => date("Y-m-d H:i:s"),
                                           'date_end_suspension'               => 'NULL',
                                           'plugin_moreticket_waitingtypes_id' => $item->input['plugin_moreticket_waitingtypes_id']))) {

               unset($_SESSION['glpi_plugin_moreticket_waiting']);
            }
         } else {
            $item->input['id']                       = $item->fields['id'];
            $_SESSION['saveInput'][$item->getType()] = $item->input;
            unset($item->input['status']);
         }
      }

      return true;
   }

   /**
    * Type than could be linked to a typo
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
         if (!($item = getItemForItemtype($type))) {
            continue;
         }

         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

}

?>