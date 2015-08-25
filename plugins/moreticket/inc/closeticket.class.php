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

class PluginMoreticketCloseTicket extends CommonDBTM {

   static $types = array('Ticket');
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
    * Display moreticket-item's tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $config = new PluginMoreticketConfig();

      if (!$withtemplate) {
         if ($item->getType() == 'Ticket' 
               && $item->fields['status'] == Ticket::CLOSED 
               && $config->closeInformations()) {
            
            return __('Close ticket informations', 'moreticket');
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
      
      $config = new PluginMoreticketConfig();
      
      if ($item->getType() == 'Ticket' 
            && ($item->fields['status'] == Ticket::CLOSED)
            && $config->closeInformations()) {
         
         self::showForTicket($item);
      }
      
      return true;
   }
   
   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   public static function getTypeName($nb=0) {

      return __('Close ticket informations', 'moreticket');
   }
   
   // Check the mandatory values of forms
   static function checkMandatory($values) {
      $checkKo = array();

      $config = new PluginMoreticketConfig();

      $mandatory_fields = array('solution' => __('Solution description', 'moreticket'));

      if ($config->mandatorySolutionType() == true) {
         $mandatory_fields['solutiontypes_id'] = _n('Solution type', 'Solution types', 1);
      }

      $msg = array();

      foreach ($values as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo[] = 1;
            }
         }
         $_SESSION['glpi_plugin_moreticket_close'][$key] = $value;
      }

      if (in_array(1, $checkKo)) {
         Session::addMessageAfterRedirect(__('Ticket cannot be closed', 'moreticket')."<br>"._n('Mandatory field', 'Mandatory fields', 2)." : ".implode(', ', $msg), false, ERROR);
         return false;
      }
      return true;
   }
   
   static function showForTicket(Ticket $item) {

      if (!self::canView()){
         return false;
      }

      $canedit = ($item->canUpdate() && self::canUpdate());
      
      echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Close ticket informations', 'moreticket')."</th></tr>";

      // Writer
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Writer');
      echo "</td>";
      echo "<td>";
      echo getUserName(Session::getLoginUserID());
      echo "<input name='requesters_id' type='hidden' value='".Session::getLoginUserID()."'>";
      echo "</td>";
      echo "</tr>";
      
      // Date
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "</td>";
      echo "<td>";
      Html::showDateTimeField("date", array('value'  => date('Y-m-d H:i:s')));
      echo "</td>";
      echo "</tr>";
            
      // Comments
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Comments');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='80' rows='8' name='comment'></textarea>";
      echo "</td>";
      echo "</tr>";
      
      // Documents
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' style='padding:10px 20px 0px 20px'>";
      echo Html::file();
      echo "(".Document::getMaxUploadSize().")&nbsp;";
      echo "</td>";
      echo "</tr>";

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add' class='submit' value='"._sx('button', 'Add')."' >";
         echo "<input type='hidden' name='tickets_id' class='submit' value='".$item->fields['id']."' >";
         echo "<input type='hidden' name='items_id' class='submit' value='".$item->fields['id']."' >";
         echo "<input type='hidden' name='itemtype' class='submit' value='Ticket' >";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div>";
      Html::closeForm();
      
      // List
     self::showList($item, $canedit);
   }
      
   function getSearchOptions() {

      $tab = parent::getSearchOptions();

      $tab[10]['table']         = $this->getTable();
      $tab[10]['field']         = 'date';
      $tab[10]['name']          = __('Date');
      $tab[10]['datatype']      = 'datetime';
      $tab[10]['massiveaction'] = false;

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'comment';
      $tab[11]['name']          = __('Comments');
      $tab[11]['datatype']      = 'text';
      $tab[11]['massiveaction'] = true;
      
      $tab[12]['table']         = "glpi_users";
      $tab[12]['field']         = 'name';
      $tab[12]['name']          = __('Writer');
      $tab[12]['datatype']      = 'dropdown';
      $tab[12]['linkfield']     = 'requesters_id';
      $tab[12]['massiveaction'] = false;

      return $tab;
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
   static function showList($item, $canedit) {
      global $CFG_GLPI;

      // validation des droits
      if (!self::canView()) {
         return false;
      }

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }
      
      $rand = mt_rand();

      // Get close informations
      $data = self::getCloseTicketFromDB($item->getField('id'), array('start' => $start,
                                                                      'limit' => $_SESSION['glpilist_limit']));

      if (!count($data)) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>".__('No historical')."</th></tr>";
         echo "</table>";
         echo "</div><br>";
         
      } else {
         $doc = new Document();
         echo "<div class='center'>";
         // Display the pager
         Html::printAjaxPager(__('Close ticket informations', 'moreticket'), $start, count($data));
         
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand);
            Html::showMassiveActions($massiveactionparams);
         }
         
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th width='10'>";
         if ($canedit) {
            echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         }
         echo "</th>";
         echo "<th>".__('Date')."</th>";
         echo "<th>".__('Comments')."</th>";
         echo "<th>".__('Writer')."</th>";
         echo "<th>".__('Document')."</th>";
         echo"</tr>";

         foreach ($data as $closeTicket) {
            echo "<tr class='tab_bg_2'>";
            echo "<td width='10'>";
            if ($canedit) {
               Html::showMassiveActionCheckBox(__CLASS__, $closeTicket['id']);
            }
            echo "</td>";
            echo "<td>";
            echo Html::convDateTime($closeTicket['date']);
            echo "</td>";
            echo "<td>";
            echo $closeTicket['comment'];
            echo "</td>";
            echo "<td>";
            echo getUserName($closeTicket['requesters_id']);
            echo "</td>";
            echo "<td>";
            if ($doc->getFromDB($closeTicket['documents_id'])) {
               echo $doc->getLink();
            }
            echo "</td>";
            echo"</tr>";
         }
         
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm(); 
         }
         echo "</table>";
         echo "</div>";
         Html::printAjaxPager(__('Close ticket informations', 'moreticket'), $start, count($data));
      }
   }
   
   /**
    * Get close ticket informations
    * 
    * @param type $tickets_id
    * @param type $options
    * @return boolean
    */
   static function getCloseTicketFromDB($tickets_id, $options = array()) {

      $data = getAllDatasFromTable("glpi_plugin_moreticket_closetickets", 'tickets_id = '.$tickets_id, false, '`date` DESC LIMIT '.intval($options['start']).",".intval($options['limit']));

      return $data;
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
   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      // validation des droits
      if (!$this->canview()) {
         return false;
      }
      
      $ticket = new Ticket();
      
      if ($ID > 0) {
         if (!$ticket->getFromDB($ID)) {
            $ticket->getEmpty();
         }
      } else {
         // Create item
         $ticket->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_close'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_close'] as $key => $value) {
            $ticket->fields[$key] = str_replace(array('\r\n','\r','\n'), '', $value);
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_close']);
      
      echo "<div class='spaced' id='moreticket_close_ticket'>";
      echo "</br>";
      echo "<table class='moreticket_close_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo _n('Solution template', 'Solution templates', 1)."&nbsp;:&nbsp;&nbsp;";
      $rand_template = mt_rand();
      $rand_type = 0;
      $rand_text = mt_rand();
      $rand_type = mt_rand();
      SolutionTemplate::dropdown(array('value'    => 0,
                                       'entity'   => $ticket->getEntityID(),
                                       'rand'     => $rand_template,
                                       // Load type and solution from bookmark
                                       'toupdate'
                                         => array('value_fieldname'
                                                               => 'value',
                                                  'to_update'  => 'solution'.$rand_text,
                                                  'url'        => $CFG_GLPI["root_doc"].
                                                                  "/ajax/solution.php",
                                                  'moreparams'
                                                     => array('type_id'
                                                               => 'dropdown_solutiontypes_id'.
                                                                    $rand_type))));

      echo "</td></tr>";
         
      echo "<tr><td>";
      echo _n('Solution type', 'Solution types', 1);
      $config = new PluginMoreticketConfig();
      if ($config->mandatorySolutionType() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      Dropdown::show('SolutionType',
                        array('value'  => $ticket->getField('solutiontypes_id'),
                              'rand'   => $rand_type,
                              'entity' => $ticket->getEntityID()));
      echo "</td></tr>";
      echo "<tr><td>";
      echo __('Solution description', 'moreticket')."&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      $rand = mt_rand();
      Html::initEditorSystem("solution".$rand);
      echo "<div id='solution$rand_text'>";
      echo "<textarea id='solution$rand' name='solution' rows='3'>".stripslashes($ticket->fields['solution'])."</textarea></div>";
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   // Hook done on before add ticket - checkMandatory
   static function preAddCloseTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      // Get allowed status
      $config = new PluginMoreticketConfig();
      $solution_status = array_keys(json_decode($config->solutionStatus(), true));
      
      // Then we add tickets informations
      if (isset($item->input['id']) 
            && isset($item->input['status']) 
               && in_array($item->input['status'], $solution_status)
                  && !self::checkMandatory($item->input, true)) {
         
         $_SESSION['saveInput'][$item->getType()] = $item->input;
         $item->input = array();
      }

      return true;
   }


   // Hook done on after add ticket - update closetickets
   static function postAddCloseTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      $ticket = new Ticket();
      if (isset($_POST['solution'])) {
         $item->input['solution'] = str_replace(array('\r\n','\r','\n'), '', $_POST['solution']);
      }
      
      // Get allowed status
      $config = new PluginMoreticketConfig();
      $solution_status = array_keys(json_decode($config->solutionStatus(), true));

      if (isset($item->input['id'])) {
         if (isset($item->input['status']) 
               && isset($_POST['solutiontypes_id'])
               && isset($_POST['solution'])
               && in_array($item->input['status'], $solution_status)) {
            if (self::checkMandatory($_POST)) {
               // Then we add tickets informations
               $ticket->update(array('id'               => $item->input['id'],
                                     'solutiontypes_id' => $_POST['solutiontypes_id'],
                                     'solution'         => $_POST['solution']));
               unset($_SESSION['glpi_plugin_moreticket_close']);
            } else {
               //$item->input = array();
               $_SESSION['saveInput'][$item->getType()] = $item->input;
               $item->input = array();
            }
         }
      }

      return true;
   }
   
   public function post_addItem() {

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s added closing informations', 'moreticket'), getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
      
      parent::post_addItem();
   }
   
   
   
   public function post_updateItem($history=1) {

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s updated closing informations', 'moreticket'), getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
      
      parent::post_updateItem();
   }
   
      
   public function post_purgeItem($history=1) {

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s deleted closing informations', 'moreticket'), getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
      
      parent::post_updateItem();
   }
}

?>