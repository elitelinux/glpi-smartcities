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

class PluginMonitoringDowntime extends CommonDBTM {

   static $rightname = 'plugin_monitoring_downtime';


   static function getTypeName($nb=0) {
      return __('Downtime', 'monitoring');
   }



   function defineTabs($options=array()){
      $ong = array();
      $this->addDefaultFormTab($ong);
      return $ong;
   }



   function getComments() {
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
      if ($item->getType() == 'Ticket' || $item->getType() == 'Computer'){
         if (self::canView()) {
            return __('Downtimes', 'monitoring');
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

      // Toolbox::logInFile("pm-downtime", "Downtime, displayTabContentForItem ($withtemplate), item concerned : ".$item->getTypeName()."/".$item->getID()."\n");
      if ($item->getType()=='Ticket') {
         if (self::canView()) {
            // Show list filtered on item, sorted on day descending ...
            Search::manageGetValues(self::getTypeName());
            Search::showList(self::getTypeName(), array(
               'field' => array(12), 'searchtype' => array('equals'), 'contains' => array($item->getID()),
               'sort' => 4, 'order' => 'DESC'
               ));
            return true;
         }
      }

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

      $tab['common'] = __('Host downtimes', 'monitoring');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'id';
      $tab[1]['linkfield']       = 'id';
      $tab[1]['name']            = __('ID');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false; // implicit field is id

      // $tab[2]['table']           = $this->getTable();
      // $tab[2]['field']           = 'plugin_monitoring_hosts_id';
      // $tab[2]['name']            = __('Host name', 'monitoring');
      // $tab[2]['datatype']        = 'specific';
      // $tab[2]['massiveaction']   = false;

      $tab[2]['table']           = "glpi_computers";
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Computer');
      $tab[2]['datatype']        = 'itemlink';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'flexible';
      $tab[3]['name']            = __('Flexible downtime', 'monitoring');
      $tab[3]['datatype']        = 'bool';
      $tab[3]['massiveaction']   = false;

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'start_time';
      $tab[4]['name']            = __('Start time', 'monitoring');
      $tab[4]['datatype']        = 'datetime';
      $tab[4]['massiveaction']   = false;

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'end_time';
      $tab[5]['name']            = __('End time', 'monitoring');
      $tab[5]['datatype']        = 'datetime';
      $tab[5]['massiveaction']   = false;

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'duration';
      $tab[6]['name']            = __('Duration', 'monitoring');
      $tab[6]['massiveaction']   = false;

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'duration_type';
      $tab[7]['name']            = __('Duration type', 'monitoring');
      $tab[7]['massiveaction']   = false;

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'comment';
      $tab[8]['name']            = __('Comment', 'monitoring');
      $tab[8]['datatype']        = 'itemlink';
      // $tab[8]['datatype']        = 'text';
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
      $tab[11]['name']           = __('Period expired', 'monitoring');
      $tab[11]['datatype']       = 'bool';
      $tab[11]['massiveaction']  = false;

      $tab[12]['table']          = "glpi_tickets";
      $tab[12]['field']          = 'id';
      $tab[12]['name']           = __('Ticket');
      $tab[12]['datatype']       = 'itemlink';

      return $tab;
   }


   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'plugin_monitoring_hosts_id':
            $pmHost = new PluginMonitoringHost();
            $pmHost->getFromDB($values[$field]);
            return $pmHost->getLink(array ("monitoring" => "1"));
            break;

         case 'duration_type':
            $a_duration_type = array();
            $a_duration_type['seconds'] = __('Second(s)', 'monitoring');
            $a_duration_type['minutes'] = __('Minute(s)', 'monitoring');
            $a_duration_type['hours']   = __('Hour(s)', 'monitoring');
            $a_duration_type['days']    = __('Day(s)', 'monitoring');
            return $a_duration_type[$values[$field]];
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
   function setDefaultContent($host_id) {
      // Start time : now ...
      $start_time = strtotime(date('Y-m-d H:i:s'));
      // End time : now + 2 hours ...
      $end_time = $start_time+7200;

      $this->fields["plugin_monitoring_hosts_id"]  = $host_id;
      $this->fields["start_time"]                  = date('Y-m-d H:i:s', $start_time);
      $this->fields["end_time"]                    = date('Y-m-d H:i:s', $end_time);
      $this->fields["flexible"]                    = 0;
      $this->fields["duration"]                    = 4;
      $this->fields["duration_type"]               = 'hours';
      $this->fields["users_id"]                    = $_SESSION['glpiID'];
      $this->fields["notified"]                    = 0;
      $this->fields["expired"]                     = 0;
   }


   /**
    * Get host identifier for a downtime
    */
   function getHostID() {
      return $this->fields["plugin_monitoring_hosts_id"];
   }


   /**
    * Get current downtime for an host
    */
   function getFromHost($host_id) {
      // $pmDowntime = new PluginMonitoringDowntime();
      $this->getFromDBByQuery("WHERE `" . $this->getTable() . "`.`plugin_monitoring_hosts_id` = '" . $this->getID() . "' AND `expired` = '0' ORDER BY end_time DESC LIMIT 1");
      return $this->getID();
   }


   /**
    * Get user name for a downtime
    */
   function getUsername() {
      $user = new User();
      $user->getFromDB($this->fields['users_id']);
      return $user->getName(1);
   }


   /**
    * In scheduled downtime ?
    */
   function isInDowntime() {
      if ($this->getID() == -1) return false;

      if ($this->isExpired()) return false;

      // Now ...
      $now = strtotime(date('Y-m-d H:i:s'));
      // Start time ...
      $start_time = strtotime($this->fields["start_time"]);
      // End time ...
      $end_time = strtotime($this->fields["end_time"]);

      // Toolbox::logInFile("pm-downtime", "isInDowntime, now : $now, start : $start_time, end : $end_time\n");
      if (($start_time <= $now) && ($now <= $end_time)) {
         // Toolbox::logInFile("pm-downtime", "isInDowntime, yes, id : ".$this->getID()."\n");
         return true;
      }

      return false;
   }


   /**
    * Downtime expired ?
    */
   function isExpired() {
      if ($this->getID() == -1) return false;

      // Now ...
      $now = strtotime(date('Y-m-d H:i:s'));
      // Start time ...
      $start_time = strtotime($this->fields["start_time"]);
      // End time ...
      $end_time = strtotime($this->fields["end_time"]);

      $this->fields["expired"] = ($now > $end_time);
      $this->update($this->fields);
      return ($this->fields["expired"] == 1);
   }


   /**
    * Downtime has an associated ticket ?
    */
   function isAssociatedTicket() {
      if ($this->getID() == -1) return false;

      return ($this->fields["tickets_id"] != 0);
   }


   function prepareInputForAdd($input) {
      // Toolbox::logInFile("pm-downtime", "Downtime, prepareInputForAdd\n");

      if ($this->isExpired()) {
         Session::addMessageAfterRedirect(__('Downtime period has already expired!', 'monitoring'), false, ERROR);
         return false;
      }

      // Check user ...
      if ($input["users_id"] == NOT_AVAILABLE) {;
         $input["users_id"] = $_SESSION['glpiID'];
      }

      // Compute duration in seconds
      if ($input['duration'] == 0) {
         $input['duration_seconds'] = 0;
      } else {
         $multiple = 1;
         if ($input['duration_type'] == 'seconds') {
            $multiple = 1;
         } else if ($input['duration_type'] == 'minutes') {
            $multiple = 60;
         } else if ($input['duration_type'] == 'hours') {
            $multiple = 3600;
         } else if ($input['duration_type'] == 'days') {
            $multiple = 86400;
         }
         $input['duration_seconds'] = $multiple * $input['duration'];
      }

      $user = new User();
      $user->getFromDB($input['users_id']);

      // Downtime is to be created ...
      // ... send information to shinken via webservice
      $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
      if ($pmShinkenwebservice->sendDowntime($input['plugin_monitoring_hosts_id'],
                                             -1,
                                             $user->getName(1),
                                             $input['comment'],
                                             $input['flexible'],
                                             $input['start_time'],
                                             $input['end_time'],
                                             $input['duration_seconds'],
                                             'add'
                                             )) {
         // ... and then send an acknowledge for the host
         if ($pmShinkenwebservice->sendAcknowledge($input['plugin_monitoring_hosts_id'],
                                                   -1,
                                                   $user->getName(1),
                                                   $input['comment'],
                                                   '1', '1', '1')) {
            // Set host as acknowledged
            $pmHost = new PluginMonitoringHost();
            $pmHost->getFromDB($input['plugin_monitoring_hosts_id']);
            $pmHost->setAcknowledged($input['comment']);

            $a_services = $pmHost->getServicesID();
            if (is_array($a_services)) {
               foreach ($a_services as $service_id) {
                  // Send downtime for a service to shinken via webservice
                  $pmShinkenwebservice->sendDowntime(-1,
                                                      $service_id,
                                                      $user->getName(1),
                                                      $input['comment'],
                                                      $input['flexible'],
                                                      $input['start_time'],
                                                      $input['end_time'],
                                                      $input['duration_seconds'],
                                                      'add'
                                                      );

/*
                  // Send acknowledge command for a service to shinken via webservice
                  if ($pmShinkenwebservice->sendAcknowledge(-1,
                                                            $service_id,
                                                            $user->getName(1),
                                                            $input['comment'],
                                                            '1', '1', '1')) {
                     // Set service as acknowledged
                     $pmService = new PluginMonitoringService();
                     $pmService->getFromDB($service_id);
                     $pmService->setAcknowledged($input['comment']);
                  }
*/
               }
            }
         }

         Session::addMessageAfterRedirect(__('Downtime notified to the monitoring application:', 'monitoring'));
         $input['notified'] = 1;
      } else {
         Session::addMessageAfterRedirect(__('Downtime has not been accepted by the monitoring application:', 'monitoring'), false, ERROR);
         return false;
      }

      return $input;
   }


   /**
    * Actions done after the ADD of the item in the database
    *
    * @return nothing
   **/
   function post_addItem() {
      // Toolbox::logInFile("pm-downtime", "Downtime, post_add\n");

   }


   /**
    * Actions done before the DELETE of the item in the database /
    * Maybe used to add another check for deletion
    *
    * @return bool : true if item need to be deleted else false
   **/
   function pre_deleteItem() {
      // Toolbox::logInFile("pm-downtime", "Downtime, pre_deleteItem\n");

      $user = new User();
      $user->getFromDB($this->fields['users_id']);

      // Downtime is to be created ...
      // ... send information to shinken via webservice
      $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
      if ($pmShinkenwebservice->sendDowntime($this->fields['plugin_monitoring_hosts_id'],
                                             -1,
                                             $user->getName(1),
                                             $this->fields['comment'],
                                             $this->fields['flexible'],
                                             $this->fields['start_time'],
                                             $this->fields['end_time'],
                                             $this->fields['duration_seconds'],
                                             'delete'
                                             )) {
         Session::addMessageAfterRedirect(__('Downtime deletion notified to the monitoring application:', 'monitoring'));
         $this->fields['notified'] = 1;
      } else {
         Session::addMessageAfterRedirect(__('Downtime deletion has not been accepted by the monitoring application:', 'monitoring'), false, ERROR);
         // return false;
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
   function showForm($items_id=-1, $options=array()) {
      global $DB,$CFG_GLPI;

      $host_id = -1;
      if (isset($_GET['host_id'])) {
         $host_id = $_GET['host_id'];
      }
      if (($host_id == -1) && ($items_id == -1)) return false;

      $createDowntime = false;

      $pmHost = new PluginMonitoringHost();
      if ($host_id != -1) {
         $pmHost->getFromDB($host_id);
         if ($pmHost->isInScheduledDowntime()) {
            // If host already in scheduled downtime, show current downtime ...
            $pmDowntime = new PluginMonitoringDowntime();
            $pmDowntime->getFromDBByQuery("WHERE `" . $pmDowntime->getTable() . "`.`plugin_monitoring_hosts_id` = '" . $host_id . "' LIMIT 1");
            $items_id = $pmDowntime->getID();
            $this->getFromDB($items_id);
         } else {
            // .. else create new downtime
            $createDowntime = true;
            $this->getEmpty();
            $this->setDefaultContent($host_id);
         }
      } else {
         $this->getFromDB($items_id);
      }

      // Now ...
      $nowDate = date('Y-m-d');
      $nowTime = date('H:i:s');

      $this->showFormHeader(array('colspan' => '4'));

      $this->isExpired();

      $pmHost = new PluginMonitoringHost();
      $pmHost->getFromDB($this->fields["plugin_monitoring_hosts_id"]);
      $itemtype = $pmHost->getField("itemtype");
      $item = new $itemtype();
      $item->getFromDB($pmHost->getField("items_id"));
      echo "<tr class='tab_bg_1'>";
      echo "<td>".$item->getTypeName()."</td>";
      echo "<td>";
      echo "<input type='hidden' name='plugin_monitoring_hosts_id' value='".$this->fields['plugin_monitoring_hosts_id']."' />";
      echo $item->getLink()."&nbsp;".$pmHost->getComments();
      echo "</td>";

      echo "<td></td>";
      echo "<td></td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Start time', 'monitoring')."</td>";
      echo "<td>";
      $date = $this->fields["start_time"];
      Html::showDateTimeField("start_time", array('value'      => $date,
                                                  'timestep'   => 10,
                                                  'maybeempty' => false,
                                                  'canedit'    => $createDowntime,
                                                  'mindate'    => $nowDate,
                                                  'mintime'    => $nowTime
                                            ));
      echo "</td>";

      echo "<td>".__('Flexible ?', 'monitoring')."</td>";
      echo "<td>";
      if ($createDowntime) {
         Dropdown::showYesNo('flexible', $this->fields['flexible']);
      } else {
         echo Dropdown::getYesNo($this->fields['flexible']);
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
                                                  'canedit'    => $createDowntime,
                                                  'mindate'    => $nowDate,
                                                  'mintime'    => $nowTime
                                            ));
      echo "</td>";

      echo "<td>".__('Duration', 'monitoring')."</td>";
      echo "<td>";
      if ($createDowntime) {
         Dropdown::showNumber("duration", array(
                   'value' => $this->fields['duration'],
                   'min'   => 1,
                   'max'   => 300)
         );
      } else {
         echo $this->fields['duration'];
      }
      $a_duration_type = array();
      $a_duration_type['seconds'] = __('Second(s)', 'monitoring');
      $a_duration_type['minutes'] = __('Minute(s)', 'monitoring');
      $a_duration_type['hours']   = __('Hour(s)', 'monitoring');
      $a_duration_type['days']    = __('Day(s)', 'monitoring');

      if ($createDowntime) {
         Dropdown::showFromArray("duration_type",
                                 $a_duration_type,
                                 array('value'=>$this->fields['duration_type']));
      } else {
         echo "&nbsp;".$this->fields['duration_type'];
      }
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Comment', 'monitoring')."</td>";
      echo "<td >";
      if ($createDowntime) {
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

      if (Ticket::canView()) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4'>&nbsp;</td>";
         echo "</tr>";

         if ($this->isAssociatedTicket()) {
            echo "<tr class='tab_bg_3'>";
            echo "<td colspan='4'>".__('Downtime associated ticket', 'monitoring')."</td>";
            echo "</tr>";

            // Find ticket in DB ...
            $track = new Ticket();
            $track->getFromDB($this->getField("tickets_id"));

            // Display ticket id, name and tracking ...
            $bgcolor = $_SESSION["glpipriority_".$track->fields["priority"]];
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' bgcolor='$bgcolor'>".sprintf(__('%1$s: %2$s'), __('ID'),
                                                                  $track->fields["id"])."</td>";
            echo "<td class='center'>";

            $showprivate = Session::haveRight("show_full_ticket", 1);
            $link = "<a id='ticket".$track->fields["id"]."' href='".$CFG_GLPI["root_doc"].
                      "/front/ticket.form.php?id=".$track->fields["id"];
            $link .= "'>";
            $link .= "<span class='b'>".$track->getNameID()."</span></a>";
            $link = sprintf(__('%1$s (%2$s)'), $link,
                            sprintf(__('%1$s - %2$s'), $track->numberOfFollowups($showprivate),
                                    $track->numberOfTasks($showprivate)));
            $link = printf(__('%1$s %2$s'), $link,
                           Html::showToolTip($track->fields['content'],
                                             array('applyto' => 'ticket'.$track->fields["id"],
                                                   'display' => false)));

            echo "</td>";
            echo "</tr>";
         } else if ($createDowntime && Ticket::canCreate()) {
            echo "<tr class='tab_bg_3'>";
            echo "<td colspan='4'>".__('Associated ticket (no declared category implies no ticket created):', 'monitoring')."</td>";
            echo "</tr>";

            echo "<input type='hidden' name='redirect' value='".$CFG_GLPI["root_doc"]."/front/ticket.form.php' />";
            echo "<input type='hidden' name='itemtype' value='".$pmHost->getField("itemtype")."' />";
            echo "<input type='hidden' name='items_id' value='".$pmHost->getField("items_id")."' />";

            echo '<input type="hidden" name="entities_id" value="'.$item->fields['entities_id'].'" />';

            $item = new $itemtype();
            $item->getFromDB($pmHost->getField("items_id"));
            echo "<input type='hidden' name='locations_id' value='".$item->getField("locations_id")."' />";

/*
            // Find SLA ...
            $sla = new Sla();
            $slas = current($sla->find("`name` LIKE '%proactive%' LIMIT 1"));
            $sla_id = isset($slas['id']) ? $slas['id'] : 0;

            echo "<tr class='tab_bg_3'>";
            echo "<td>".__('Ticket SLA:', 'monitoring')."</td>";
            echo "<td colspan='3'>";
            Sla::dropdown(array('value'  => $sla_id));
            echo "</td>";
            echo "</tr>";
*/

            // Ticket type ...
            echo "<tr class='tab_bg_3'>";
            echo "<td>".__('Ticket type:', 'monitoring')."</td>";
            echo "<td colspan='3'>";
            Ticket::dropdownType("type", array('value'  => Ticket::INCIDENT_TYPE));
            echo "</td>";
            echo "</tr>";

            // Find category ...
            $category = new ITILCategory();
            $categories = current($category->find("`name` LIKE '%incident%' LIMIT 1"));
            $category_id = isset($categories['id']) ? $categories['id'] : 0;

/*
            echo "
            <script>
            function changeCategory() {
               alert(document.getElementById('dropdown_itilcategories_idcategory'));
               alert($('#dropdown_itilcategories_idcategory').val());
            }
            </script>
            ";
*/
            echo "<tr class='tab_bg_3'>";
            echo "<td>".__('Ticket category:', 'monitoring')."</td>";
            echo "<td colspan='3'>";
            ITILCategory::dropdown(array(
               'value'     => $category_id,
            ));
/*
            ITILCategory::dropdown(array(
               'value'     => $category_id,
               'rand'      => 'category',
               'on_change' => 'changeCategory();'
            ));
*/
            echo "</td>";
            echo "</tr>";
         } else {
            echo "<tr class='tab_bg_3'>";
            echo "<td colspan='4'>".__('No associated ticket for this downtime', 'monitoring')."</td>";
            echo "</tr>";
         }
      }

      $this->showFormButtons(array(
         'canedit'      => $createDowntime
         ));

      return true;
   }


   static function cronInfo($name){

      switch ($name) {
         case 'DowntimesExpired':
            return array (
               'description' => __('Update downtimes expiration','monitoring'));
            break;
      }
      return array();
   }

   static function cronDowntimesExpired() {
      global $DB;

      $query = "UPDATE `glpi_plugin_monitoring_downtimes` SET `expired` = '1' WHERE `expired` = '0' AND `end_time` < NOW();";
      $DB->query($query);

      return true;
   }
}

?>