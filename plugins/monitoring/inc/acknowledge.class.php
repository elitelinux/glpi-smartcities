<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    Frédéric Mohier
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringAcknowledge extends CommonDBTM {

   static $rightname = 'plugin_monitoring_acknowledge';

   static function getTypeName($nb=0) {
      return __('Acknowledge', 'monitoring');
      // return _n(__('Host acknowledge', 'monitoring'),__('Host acknowledges', 'monitoring'),$nb);
   }



   function defineTabs($options=array()){
      $ong = array();
      $this->addDefaultFormTab($ong);
      return $ong;
   }



   function getComments() {
      global $CFG_GLPI;

      // Toolbox::logInFile("pm-ack", "getComments ".$this->getID()." \n");
      $this->isExpired();

      // echo $this->fields["start_time"];
      // echo $this->fields["end_time"];
      // echo "<textarea cols='80' rows='4' name='comment' readonly='1' disabled='1' >".$this->getField('comment')."</textarea>";
      // echo $this->getUsername();
      echo"<i>". __('Acknowledged by: ', 'monitoring').$this->getUsername()."</i><br/>";
      echo"<i>". __('Acknowledge started: ', 'monitoring').$this->getField('start_time')."</i><br/>";
      if (Session::haveRight("plugin_monitoring_acknowledge", UPDATE)) {
         echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/acknowledge.form.php?id=".$this->getID()."' title='".htmlspecialchars(__('Modify acknowledge comment', 'monitoring'), ENT_QUOTES)."'>";
         echo $this->getField('comment')."</a>";
      } else {
         echo $this->getField('comment')."</a>";
      }
   }



    /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType()=='Computer') {
         if (self::canView()) {
            return __('Acknowledges', 'monitoring');
         }
      }

      return '';
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Computer') {
         if (self::canView()) {
            // Show list filtered on item, sorted on day descending ...
            Search::manageGetValues(self::getTypeName());
            Search::showList(self::getTypeName(), array(
               'field' => array(2), 'searchtype' => array('equals'), 'contains' => array($item->getID()),
               'sort' => 4, 'order' => 'DESC'
               ));
            return true;
         }
      }
      return true;
   }



   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Host acknowledges', 'monitoring');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'id';
      $tab[1]['name']            = __('ID');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false; // implicit field is id

      $tab[2]['table']              = $this->getTable();
      $tab[2]['field']              = 'items_id';
      $tab[2]['name']               = __('Associated element');
      $tab[2]['datatype']           = 'specific';
      // $tab[2]['nosearch']           = true;
      $tab[2]['nosort']             = true;
      $tab[2]['massiveaction']      = false;
      $tab[2]['additionalfields']   = array('itemtype');
      $tab[2]['options']            = array('hostname'=>'1');

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'start_time';
      $tab[3]['name']            = __('Start time', 'monitoring');
      $tab[3]['datatype']        = 'datetime';
      $tab[3]['massiveaction']   = false;

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'end_time';
      $tab[4]['name']            = __('End time', 'monitoring');
      $tab[4]['datatype']        = 'datetime';
      $tab[4]['massiveaction']   = false;

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'sticky';
      $tab[5]['name']            = __('Sticky', 'monitoring');
      $tab[5]['datatype']        = 'bool';
      $tab[5]['massiveaction']   = false;

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'notify';
      $tab[6]['name']            = __('Notify', 'monitoring');
      $tab[6]['datatype']        = 'bool';
      $tab[6]['massiveaction']   = false;

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'persistent';
      $tab[7]['name']            = __('Persistent', 'monitoring');
      $tab[7]['datatype']        = 'bool';
      $tab[7]['massiveaction']   = false;

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'comment';
      $tab[8]['name']            = __('Comment', 'monitoring');
      $tab[8]['datatype']        = 'itemlink';
      $tab[8]['massiveaction']   = false;

      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'users_id';
      $tab[9]['name']            = __('User', 'monitoring');
      $tab[9]['massiveaction']   = false;

      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'notified';
      $tab[10]['name']           = __('Notified to monitoring system', 'monitoring');
      $tab[10]['datatype']       = 'bool';
      $tab[10]['massiveaction']  = false;

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'expired';
      $tab[11]['name']           = __('Expired', 'monitoring');
      $tab[11]['datatype']       = 'bool';
      $tab[11]['massiveaction']  = false;

      return $tab;
   }



   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'items_id':
            if (isset($values['itemtype'])) {
               $itemtype = $values['itemtype'];
               $item = new $itemtype();
               $item->getFromDB($values[$field]);
               return $item->getLink();
            }
            break;

         case 'users_id':
            $user = new User();
            $user->getFromDB($values[$field]);
            return $user->getName(1);
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * Get entity
    */
   function getEntityID($options = array()) {
      return $this->fields["entities_id"];
   }


   /**
    * Set default content
    */
   function setDefaultContent($itemtype, $items_id) {
      // Start time : now ...
      $start_time = strtotime(date('Y-m-d H:i:s'));
      // End time : now + 2 hours ...
      $end_time = $start_time;

      $this->fields["itemtype"]        = $itemtype;
      $this->fields["items_id"]        = $items_id;
      $this->fields["start_time"]      = date('Y-m-d H:i:s', $start_time);
      $this->fields["end_time"]        = date('Y-m-d H:i:s', $end_time);
      $this->fields["sticky"]          = 1;
      $this->fields["persistent"]      = 1;
      $this->fields["notify"]          = 1;
      $this->fields["users_id"]        = $_SESSION['glpiID'];
      $this->fields["notified"]        = 0;
      $this->fields["expired"]         = 0;
   }


   /**
    * Get host identifier for an acknowledge
    */
   function getHostID() {
      return $this->fields["items_id"];
   }


   /**
    * Get current acknowledge for an host/service
    */
   function getFromHost($items_id, $itemtype='Host') {
      // $pmAcknowledge = new PluginMonitoringAcknowledge();
      // Toolbox::logInFile("pm-ack", "getFromHost ? $itemtype / $items_id \n");
      $this->getFromDBByQuery("WHERE `" . $this->getTable() . "`.`itemtype` = 'PluginMonitoring$itemtype' AND `" . $this->getTable() . "`.`items_id` = '$items_id' AND `expired` = '0' LIMIT 1");
      // Toolbox::logInFile("pm-ack", "getFromHost ? ".$pmAcknowledge->getID()." \n");
      return $this->getID();
   }


   /**
    * Get user name for an acknowledge
    */
   function getUsername() {
      $user = new User();
      $user->getFromDB($this->getField('users_id'));
      return $user->getName(1);
   }


   /**
    * In acknowledge time ?
    */
   function isInAcknowledge() {
      if ($this->getID() == -1) return false;

      if ($this->isExpired()) return false;

      // Now ...
      $now = strtotime(date('Y-m-d H:i:s'));
      // Start time ...
      $start_time = strtotime($this->fields["start_time"]);
      // End time ...
      $end_time = strtotime($this->fields["end_time"]);

      // Toolbox::logInFile("pm-ack", "isInacknowledge, now : $now, start : $start_time, end : $end_time\n");
      if (($start_time <= $now) && ($now <= $end_time)) {
         // Toolbox::logInFile("pm-ack", "isInacknowledge, yes, id : ".$this->getID()."\n");
         return true;
      }

      return false;
   }


   /**
    * Acknowleged expired ?
    */
   function isExpired() {
      if ($this->getID() == -1) return false;

      return ($this->fields["expired"] == 1);
   }


   function prepareInputForAdd($input) {
      // Toolbox::logInFile("pm-ack", "acknowledge, prepareInputForAdd, item type : ".$input['itemtype']." / ".$input['items_id']."\n");

      if ($this->isExpired()) {
         Session::addMessageAfterRedirect(__('Acknowledge period has already expired!', 'monitoring'), false, ERROR);
         return false;
      }

      // Check user ...
      if ($input["users_id"] == NOT_AVAILABLE) {;
         $input["users_id"] = $_SESSION['glpiID'];
      }

      $user = new User();
      $user->getFromDB($input['users_id']);

      $item = new $input['itemtype']();
      $item->getFromDB($input['items_id']);

      $host_id = -1;
      $service_id = -1;
      if ($input['itemtype'] == 'PluginMonitoringHost') {
         $host_id = $input['items_id'];
      } else {
         $service_id = $input['items_id'];
      }

      if ($host_id != -1) {
         // Acknowledge is to be created ...
         // ... send information to shinken via webservice
         $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
         if ($pmShinkenwebservice->sendAcknowledge($host_id,
                                                   -1,
                                                   $user->getName(1),
                                                   $input['comment'],
                                                   $input['sticky'],
                                                   $input['notify'],
                                                   $input['persistent'],
                                                   'add')) {
            // Set host as acknowledged
            $item->setAcknowledged($input['comment']);

            $a_services = $item->getServicesID();
            if (is_array($a_services)) {
               foreach ($a_services as $service_id) {
                  // Send acknowledge command for a service to shinken via webservice
                  $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
                  if ($pmShinkenwebservice->sendAcknowledge(-1,
                                                            $service_id,
                                                            $user->getName(1),
                                                            $input['comment'],
                                                            $input['sticky'],
                                                            $input['notify'],
                                                            $input['persistent'],
                                                            'add'
                                                            )) {
                     // Set service as acknowledged
                     $pmService = new PluginMonitoringService();
                     $pmService->getFromDB($service_id);
                     // Will force to create a new acknowledgement for a service ... beware of infinite loop in this function !
                     $pmService->setAcknowledged($input['comment'], true);
                  }
               }
            }

            Session::addMessageAfterRedirect(__('Acknowledge notified to the monitoring application for the host', 'monitoring'));
            $input['notified'] = 1;
         } else {
            Session::addMessageAfterRedirect(__('Acknowledge has not been accepted by the monitoring application for the host', 'monitoring'), false, ERROR);
            return false;
         }
      } else {
         // Send acknowledge command for a service to shinken via webservice
         $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
         if ($pmShinkenwebservice->sendAcknowledge(-1,
                                                   $service_id,
                                                   $user->getName(1),
                                                   $input['comment'],
                                                   $input['sticky'],
                                                   $input['notify'],
                                                   $input['persistent'],
                                                   'add'
                                                   )) {
            // Set service as acknowledged
            $pmService = new PluginMonitoringService();
            $pmService->getFromDB($service_id);
            // Do not create a new acknowledgement for a service ... false will simply update PMService table !
            $pmService->setAcknowledged($input['comment'], false);

            Session::addMessageAfterRedirect(__('Acknowledge notified to the monitoring application:', 'monitoring'));
            $input['notified'] = 1;
         } else {
            Session::addMessageAfterRedirect(__('Acknowledge has not been accepted by the monitoring application:', 'monitoring'), false, ERROR);
            return false;
         }
      }

      return $input;
   }


   /**
    * Actions done after the ADD of the item in the database
    *
    * @return nothing
   **/
   function post_addItem() {
      // Toolbox::logInFile("pm-ack", "acknowledge, post_add\n");

   }


   /**
    * Actions done before the DELETE of the item in the database /
    * Maybe used to add another check for deletion
    *
    * @return bool : true if item need to be deleted else false
   **/
   function pre_deleteItem() {
      PluginMonitoringToolbox::logIfExtradebug(
         'pm-ack',
         "acknowledge, pre_deleteItem : ".$this->fields['id']."\n"
      );

      $user = new User();
      $user->getFromDB($this->fields['users_id']);

      $item = new $this->fields['itemtype']();
      $item->getFromDB($this->fields['items_id']);

      $host_id = -1;
      $service_id = -1;
      if ($this->fields['itemtype'] == 'PluginMonitoringHost') {
         $host_id = $this->fields['items_id'];
      } else {
         $service_id = $this->fields['items_id'];
      }

      if ($host_id != -1) {
         // Acknowledge is to be deleted ...
         // ... send information to shinken via webservice
         $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
         if ($pmShinkenwebservice->sendAcknowledge($host_id,
                                                   -1,
                                                   $user->getName(1),
                                                   '', '', '', '', 'delete'
                                                   )) {
            // Set host as acknowledged
            // $pmHost = new PluginMonitoringHost();
            // $pmHost->getFromDB($this->fields['plugin_monitoring_hosts_id']);
            // $item->setAcknowledged($this->fields['comment']);

            $a_services = $item->getServicesID();
            if (is_array($a_services)) {
               foreach ($a_services as $service_id) {
                  // Send acknowledge command for a service to shinken via webservice
                  $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
                  if ($pmShinkenwebservice->sendAcknowledge(-1,
                                                            $service_id,
                                                            $user->getName(1),
                                                            '', '', '', '', 'delete'
                                                            )) {
                     // Set service as acknowledged
                     // $pmService = new PluginMonitoringService();
                     // $pmService->getFromDB($service_id);
                     // $pmService->setAcknowledged($this->fields['comment']);
                  }
               }
            }

            Session::addMessageAfterRedirect(__('Acknowledge deletion notified to the monitoring application:', 'monitoring'));
            $this->fields['notified'] = 1;
         } else {
            Session::addMessageAfterRedirect(__('Acknowledge deletion has not been accepted by the monitoring application:', 'monitoring'), false, ERROR);
            // return false;
         }
      } else {
         // Send acknowledge command for a service to shinken via webservice
         $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
         if ($pmShinkenwebservice->sendAcknowledge(-1,
                                                   $service_id,
                                                   $user->getName(1),
                                                   '', '', '', '', 'delete'
                                                   )) {
            // Set service as acknowledged
            // $pmService = new PluginMonitoringService();
            // $pmService->getFromDB($service_id);
            // $pmService->setAcknowledged($this->fields['comment']);

            Session::addMessageAfterRedirect(__('Acknowledge deletion notified to the monitoring application:', 'monitoring'));
            $this->fields['notified'] = 1;
         } else {
            Session::addMessageAfterRedirect(__('Acknowledge deletion has not been accepted by the monitoring application:', 'monitoring'), false, ERROR);
            // return false;
         }
      }

      return true;
   }


   /**
   *
   *
   * @param $items_id integer ID
   *
   * @param $host_id integer associated host ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($id=-1, $itemtype='Host', $items_id=-1, $options=array()) {
      global $DB,$CFG_GLPI;
      // Toolbox::logInFile("pm-ack", "acknowledge, showForm, id : $id, item type : $itemtype / $items_id\n");

      $createAcknowledge = false;

      if ($id == -1) {
         // if ($itemtype == 'N/A') $itemtype = 'Computer';
         // if ($itemtype == '') $itemtype = 'Computer';
         $itemtype = 'PluginMonitoring'.$itemtype;
         $item = new $itemtype();
         $item->getFromDB($items_id);
         if ($item->isCurrentlyAcknowledged()) {
            // If host currently acknowledged, show current acknowledge ...
            $pmAcknowledge = new PluginMonitoringAcknowledge();
            $this->getFromDB($pmAcknowledge->getFromHost($this->getID()));
         } else {
            // .. else create new acknowledge
            $createAcknowledge = true;
            $this->getEmpty();
            $this->setDefaultContent($itemtype, $items_id);
         }
      } else {
         $this->getFromDB($id);
         $createAcknowledge = true;
      }

      // Now ...
      $nowDate = date('Y-m-d');
      $nowTime = date('H:i:s');

      $this->showFormHeader($options);

      $this->isExpired();

      $itemtype = $this->getField('itemtype');
      if ($itemtype == 'N/A') $itemtype = 'Computer';
      if ($itemtype == '') $itemtype = 'Computer';
      $item = new $itemtype();

      $item->getFromDB($this->getField("items_id"));
      echo "<tr class='tab_bg_1'>";
      echo "<td>".$item->getTypeName()."</td>";
      echo "<td>";
      echo "<input type='hidden' name='itemtype' value='".$this->fields['itemtype']."' />";
      echo "<input type='hidden' name='items_id' value='".$this->fields['items_id']."' />";
      echo $item->getLink()."&nbsp;".$item->getComments();
      echo "</td>";

      echo "<td>".__('Sticky ?', 'monitoring')."</td>";
      echo "<td>";
      if ($createAcknowledge) {
         Dropdown::showYesNo('sticky', $this->fields['sticky']);
      } else {
         echo Dropdown::getYesNo($this->fields['sticky']);
      }
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Start time', 'monitoring')."</td>";
      echo "<td>";
      $date = $this->fields["start_time"];
      Html::showDateTimeField("start_time", array('value'      => $date,
                                                  'timestep'   => 10,
                                                  'maybeempty' => false,
                                                  'canedit'    => $createAcknowledge,
                                                  'mindate'    => $nowDate,
                                                  'mintime'    => $nowTime
                                            ));
      echo "</td>";

      echo "<td>".__('Persistent ?', 'monitoring')."</td>";
      echo "<td>";
      if ($createAcknowledge) {
         Dropdown::showYesNo('persistent', $this->fields['persistent']);
      } else {
         echo Dropdown::getYesNo($this->fields['persistent']);
      }
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('End time', 'monitoring')."</td>";
      echo "<td>";
      $date = $this->fields["end_time"];
      Html::showDateTimeField("end_time", array('value'      => $date,
                                                  'timestep'   => 10,
                                                  'maybeempty' => false,
                                                  'canedit'    => $createAcknowledge,
                                                  'mindate'    => $nowDate,
                                                  'mintime'    => $nowTime
                                            ));
      echo "</td>";

      echo "<td>".__('Notify ?', 'monitoring')."</td>";
      echo "<td>";
      if ($createAcknowledge) {
         Dropdown::showYesNo('notify', $this->fields['notify']);
      } else {
         echo Dropdown::getYesNo($this->fields['notify']);
      }
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Comment', 'monitoring')."</td>";
      echo "<td >";
      if ($createAcknowledge) {
         echo "<textarea cols='80' rows='4' name='comment' >".$this->fields['comment']."</textarea>";
      } else {
         echo "<textarea cols='80' rows='4' name='comment' readonly='1' disabled='1' >".$this->fields['comment']."</textarea>";
      }
      echo "</td>";

      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('User', 'monitoring')."</td>";
      echo "<td>";
      echo "<input type='hidden' name='users_id' value='".$this->fields['users_id']."' />";
      echo $this->getUsername();
      echo "</td>";

      echo "<td>".__('Expired ?', 'monitoring')."</td>";
      echo "<td>";
      echo Dropdown::getYesNo($this->fields['expired']);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons();

      return true;
   }
}

?>