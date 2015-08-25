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
   @author    David Durieux
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

class PluginMonitoringService extends CommonDBTM {

   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_component';


   static function getTypeName($nb=0) {
      return __('Resources', 'monitoring');
   }


   function getSearchOptions() {
      $tab = array();
      $tab['common'] = _n('Resource characteristic', 'Resource characteristics', 2);

      $tab[1]['table']           = 'glpi_computers';
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Host name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      $tab[1]['massiveaction']   = false; // implicit key==1
      $tab[1]['nosearch']        = TRUE;

      $tab[2]['table']           = "glpi_plugin_monitoring_components";
      $tab[2]['field']           = 'name';
      $tab[2]['linkfield']       = 'plugin_monitoring_components_id';
      $tab[2]['name']            = __('Component', 'monitoring');
      $tab[2]['datatype']        = 'itemlink';
      $tab[2]['itemlink_type']   = 'PluginMonitoringComponent';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'state';
      $tab[3]['name']            = __('Resource state', 'monitoring');
      $tab[3]['datatype']        = 'string';
      // $tab[3]['searchtype']      = 'equals';
      // $tab[3]['datatype']        = 'itemlink';
      // $tab[3]['itemlink_type']   = 'PluginMonitoringService';

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'state_type';
      $tab[4]['name']            = __('Service state type', 'monitoring');
      $tab[4]['datatype']        = 'string';
      // $tab[4]['searchtype']      = 'equals';
      // $tab[4]['datatype']        = 'itemlink';
      // $tab[4]['itemlink_type']   = 'PluginMonitoringService';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'last_check';
      $tab[5]['name']            = __('Last check', 'monitoring');
      $tab[5]['datatype']        = 'datetime';

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'event';
      $tab[6]['name']            = __('Result details', 'monitoring');
      $tab[6]['datatype']        = 'string';
      $tab[6]['massiveaction']   = false;

      $tab[7]['table']          = $this->getTable();
      $tab[7]['field']          = 'is_acknowledged';
      $tab[7]['name']           = __('Acknowledge', 'monitoring');
      $tab[7]['datatype']       = 'bool';

      $tab[8]['table']          = 'glpi_plugin_monitoring_hosts';
      $tab[8]['field']          = 'is_acknowledged';
      $tab[8]['name']           = __('Host acknowledge', 'monitoring');
      $tab[8]['datatype']       = 'bool';

      $tab[9]['table']          = "glpi_plugin_monitoring_componentscatalogs";
      $tab[9]['field']          = 'name';
      $tab[9]['name']           = __('Components catalog', 'monitoring');
      $tab[9]['datatype']       = 'itemlink';

      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'name';
      $tab[10]['name']           = __('Name');
      $tab[10]['datatype']       = 'itemlink';

      $tab[11]['table']          = 'glpi_plugin_monitoring_componentscatalogs_hosts';
      $tab[11]['field']          = 'id';
      $tab[11]['name']           = __('Name');

      $tab[20]['table']          = 'glpi_computers';
      $tab[20]['field']          = 'id';
      $tab[20]['name']           = __('Item')." > ".__('Computer');
      $tab[20]['searchtype']     = 'equals';
      $tab[20]['datatype']       = 'itemlink';
      $tab[20]['itemlink_type']  = 'Computer';

      $tab[21]['table']          = 'glpi_printers';
      $tab[21]['field']          = 'id';
      $tab[21]['name']           = __('Item')." > ".__('Printer');
      $tab[21]['searchtype']     = 'equals';
      $tab[21]['datatype']       = 'itemlink';
      $tab[21]['itemlink_type']  = 'Printer';

      $tab[22]['table']          = 'glpi_networkequipments';
      $tab[22]['field']          = 'id';
//      $tab[22]['linkfield']      = 'items_id';
      $tab[22]['name']           = __('Item')." > ".__('Network device');
      $tab[22]['searchtype']     = 'equals';
      $tab[22]['datatype']       = 'itemlink';
      $tab[22]['itemlink_type']  = 'NetworkEquipment';


      // TODO : ...
      // $tab[12]['table']          = 'glpi_plugin_monitoring_componentscatalogs_hosts';
      // $tab[12]['field']          = 'plugin_monitoring_componentscatalog_id';
      // $tab[12]['name']           = __('Components catalog', 'monitoring');
      // $tab[12]['datatype']       = 'equals';

      return $tab;
   }


   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'link':
            $pmService = new PluginMonitoringService();
            $pmService->getFromDB($values[$field]);
            return $pmService->getLink();
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Central' :
               if (Session::haveRight("plugin_monitoring_homepage", READ)
                       && Session::haveRight("plugin_monitoring_service", READ)) {
                  return array(1 => __('All resources', 'monitoring'));
               } else {
                  if (Session::haveRight("plugin_monitoring_homepage", READ)
                          && Session::haveRight("plugin_monitoring_perfdata", READ)) {
                     return array(1 => __('Performance data', 'monitoring'));
                  } else {
                     return '';
                  }
               }
         }
      }
      return '';
   }


   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = array();
      $values[self::HOMEPAGE]    = __('See in homepage', 'monitoring');
      $values[self::DASHBOARD]   = __('See in dashboard', 'monitoring');

      return $values;
   }




   /**
    * Get service name
    */
   function getName($options = array()) {
      if ($this->getID() == -1) return '';

      $pmComponent = new PluginMonitoringComponent();
      $a_component = current($pmComponent->find("`id`='".$this->fields['plugin_monitoring_components_id']."'", "", 1));

      $service_description = $a_component['name'];
      if (isset($options) && isset($options['shinken'])) {
         $service_description = preg_replace("/[^A-Za-z0-9\-_]/","",$a_component['description']);
         if (empty($service_description)) $service_description = preg_replace("/[^A-Za-z0-9\-_]/","",$a_component['name']);
      }

      if (isset($options) && isset($options['hostname'])) {
         $service_description .= ' '.__('on', 'monitoring').' '.$this->getHostName();
      }

      return $service_description;
   }


   /**
    * Get service link
    */
   function getLink($options = array()) {
      global $CFG_GLPI;

      if ($this->getID() == -1) return '';

      $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
              . "&criteria[0][field]=20"
              . "&criteria[0][searchtype]=equals"
              . "&criteria[0][value]=".$this->getComputerID().""

              . "&itemtype=PluginMonitoringService"
              . "&start=0'";
      if (isset($options['monitoring']) && $options['monitoring']) {
         return "<a href='$link'>".$this->getName(array('shinken'=>true, 'hostname'=>true))."</a>"."&nbsp;".$this->getComments();
      } else {
         return "<a href='$link'>".$this->getName(array('hostname'=>true))."</a>"."&nbsp;".$this->getComments();
      }
   }


   /**
    * Get service entity
    */
   function getEntityID($options = array()) {
      return $this->fields["entities_id"];
   }


   /**
    * Get host identifier for a service
    */
   function getHostID() {
      global $DB;

      $query = "SELECT
                  `glpi_plugin_monitoring_hosts`.`id`
                  , CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS name
               FROM `glpi_plugin_monitoring_hosts`
                  INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)

                  LEFT JOIN `glpi_computers`
                     ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`
                        AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
                  LEFT JOIN `glpi_printers`
                     ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_printers`.`id`
                        AND `glpi_plugin_monitoring_hosts`.`itemtype`='Printer'
                  LEFT JOIN `glpi_networkequipments`
                     ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_networkequipments`.`id`
                        AND `glpi_plugin_monitoring_hosts`.`itemtype`='NetworkEquipment'


                  INNER JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE (`glpi_plugin_monitoring_services`.`id` = '".$this->getID()."');";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($data=$DB->fetch_array($result)) {
            return $data['id'];
         }
      } else {
         return -1;
      }
   }


   /**
    * Get computer identifier for a service
    */
   function getComputerID() {
      global $DB;

      $query = "SELECT
                  `glpi_plugin_monitoring_hosts`.`id`
                  , `glpi_computers`.`id` AS idComputer
               FROM `glpi_plugin_monitoring_hosts`
                  INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
                  INNER JOIN `glpi_computers`
                     ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
                  INNER JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE (`glpi_plugin_monitoring_services`.`id` = '".$this->getID()."');";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($data=$DB->fetch_array($result)) {
            return $data['idComputer'];
         }
      } else {
         return -1;
      }
   }


   /**
    * Get host name for a service
    */
   function getHostName() {

      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmConfig                 = new PluginMonitoringConfig();

      $pmConfig->getFromDB(1);
      $pmComponentscatalog_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);
      $itemtype = $pmComponentscatalog_Host->fields['itemtype'];
      $item = new $itemtype();
      if ($item->getFromDB($pmComponentscatalog_Host->fields['items_id'])) {
         if ($pmConfig->fields['append_id_hostname'] == 1) {
            return  $item->fields['name']."-".$item->fields['id'];
         }
         return ($item->fields['name']);
      }

      return '';
   }


   /**
    * Get host overall state
    */
   function getHostState() {
      global $DB;

      $query = "SELECT
                  `glpi_plugin_monitoring_hosts`.`id`
                  , `glpi_computers`.`name`
               FROM `glpi_plugin_monitoring_hosts`
                  INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
                  INNER JOIN `glpi_computers`
                     ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
                  INNER JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE (`glpi_plugin_monitoring_services`.`id` = '".$this->getID()."');";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($data=$DB->fetch_array($result)) {
            return $data['name'];
         }
      } else {
         return -1;
      }
   }


   /**
    * Set service as acknowledged
    */
   function setAcknowledged($comment='', $creation=true) {
      if ($this->getID() == -1) return false;

      $start_time = strtotime(date('Y-m-d H:i:s'));
      $end_time = $start_time;

      if ($creation) {
         $ackData = array();
         $ackData['itemtype']       = 'PluginMonitoringService';
         $ackData['items_id']       = $this->getID();
         $ackData["start_time"]     = date('Y-m-d H:i:s', $start_time);
         $ackData["end_time"]       = date('Y-m-d H:i:s', $end_time);
         $ackData["comment"]        = $comment;
         $ackData["sticky"]         = 1;
         $ackData["persistent"]     = 1;
         $ackData["notify"]         = 1;
         $ackData["users_id"]       = $_SESSION['glpiID'];
         $ackData["notified"]       = 0;
         $ackData["expired"]        = 0;
         $pmAcknowledge = new PluginMonitoringAcknowledge();
         $pmAcknowledge->add($ackData);
      }

      $serviceData = array();
      $serviceData['id'] = $this->getID();
      $serviceData['is_acknowledged'] = '1';
      $this->update($serviceData);
   }
   function setUnacknowledged($comment='') {
      if ($this->getID() == -1) return false;

      $serviceData = array();
      $serviceData['id'] = $this->getID();
      $serviceData['is_acknowledged'] = '0';
      $this->update($serviceData);
   }


  /**
    * Is currently acknowledged ?
    */
   function isCurrentlyAcknowledged() {
      if ($this->getID() == -1) return false;

      $pmAcknowledge = new PluginMonitoringAcknowledge();
      if ($pmAcknowledge->getFromHost($this->getID(), 'Service') != -1) {
         // Toolbox::logInFile("pm", "isCurrentlyAcknowledged ? ".$this->getID()." : ".(! $pmAcknowledge->isExpired())." \n");
         return (! $pmAcknowledge->isExpired());
      }

      return false;
   }


   function getAcknowledge() {
      if ($this->getID() == -1) return false;

      $pmAcknowledge = new PluginMonitoringAcknowledge();
      if ($pmAcknowledge->getFromHost($this->getID(), 'Service') != -1) {
         return ($pmAcknowledge->getComments());
      }

      return '';
   }


   /**
    * Get service state
    *
    * Return :
    * - OK if service is OK
    * - CRITICAL if service is CRITICAL
    * - WARNING if host is WARNING, RECOVERY or FLAPPING
    * - UNKNOWN else
    */
   function getState() {
      global $CFG_GLPI;

      // Toolbox::logInFile("pm", "getShortState - ".$this->getID()."\n");
      if ($this->getID() == -1) return '';

      $acknowledge = $this->getField('is_acknowledged');
      $state_type = $this->getField('state_type');
      $state = $this->getField('state');
      $event = $this->getField('event');

      $returned_state = '';
      switch($state) {
         case 'OK':
            $returned_state = 'OK';
            break;

         case 'CRITICAL':
            $returned_state = 'CRITICAL';
            break;

         case 'WARNING':
         case 'RECOVERY':
         case 'FLAPPING':
            $returned_state = 'WARNING';
            break;

         default:
            $returned_state = 'UNKNOWN';
            break;

      }

      return $returned_state;
   }


   /**
    * Get service short state (state + acknowledgement)
    * options :
    * - image, if exists, returns URL to a state image
    *
    * Return :
    * - green if service is OK
    * - red if service is CRITICAL
    * - redblue if red and acknowledged
    * - orange if host is WARNING, RECOVERY or FLAPPING
    * - orangeblue if orange and acknowledged
    * - yellow for every other state
    * - yellowblue if yellow and acknowledged
    *
    * append '_soft' if service is in soft statetype
    */
   function getShortState($options=array()) {
      global $CFG_GLPI;

      // Toolbox::logInFile("pm", "getShortState - ".$this->getID()."\n");
      if ($this->getID() == -1) return '';

      $acknowledge = $this->getField('is_acknowledged');
      $state_type = $this->getField('state_type');
      $state = $this->getField('state');
      $event = $this->getField('event');


      $shortstate = '';
      switch($state) {
         case 'OK':
            $shortstate = 'green';
            break;

         case 'CRITICAL':
            if ($acknowledge) {
               $shortstate = 'redblue';
            } else {
               $shortstate = 'red';
            }
            break;

         case 'WARNING':
         case 'RECOVERY':
         case 'FLAPPING':
            if ($acknowledge) {
               $shortstate = 'orangeblue';
            } else {
               $shortstate = 'orange';
            }
            break;

         default:
            if ($acknowledge) {
               $shortstate = 'yellowblue';
            } else {
               $shortstate = 'yellow';
            }
            break;

      }
      if ($state == 'WARNING'
              && $event == '') {
         if ($acknowledge) {
            $shortstate = 'yellowblue';
         } else {
            $shortstate = 'yellow';
         }
      }
      if ($state_type == 'SOFT') {
         $shortstate.= '_soft';
      }

      if (isset($options) && isset($options['image'])) {
         return $CFG_GLPI['root_doc']."/plugins/monitoring/pics/box_".$shortstate."_".$options['image'].".png";
      }
      return $shortstate;
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Central' :
            $pmDisplay = new PluginMonitoringDisplay();
            // $pmDisplay->showCounters('Ressources');
            $params = Search::manageParams("PluginMonitoringService", array());
            $params['itemtype'] = 'PluginMonitoringService';
            $pmDisplay->showResourcesBoard('', true, $params);
            //$pmDisplay->showResourcesBoard('', $perfdatas);
            return true;

      }
      return true;
   }



   function manageServices($itemtype, $items_id) {

      if ($itemtype == 'Computer') {
         $pmHostaddress = new PluginMonitoringHostaddress();
         $item = new $itemtype();
         if ($item->can($items_id, UPDATE)) {
            $pmHostaddress->showForm($items_id, $itemtype);
         }
      }
      $pmServices = new PluginMonitoringService();
      $pmServices->listByHost($itemtype, $items_id);
   }



   /**
    * Display services associated with host
    *
    * @param $itemtype value type of item
    * @param $items_id integer id of the object
    *
    **/
   function listByHost($itemtype, $items_id) {

      $params = Search::manageParams("PluginMonitoringService", array(), false);
      $num = 20; // Computer
      if ($itemtype == 'Printer') {
         $num = 21;
      } else if ($itemtype == 'NetworkEquipment') {
         $num = 22;
      }
      $params['criteria'][0] = array(
         'field'      => $num,
         'searchtype' => 'is',
         'value'      => $items_id
      );
      $col_to_display = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);

      $data = Search::prepareDatasForSearch('PluginMonitoringService', $params, $col_to_display);
      $data['tocompute'] = $data['toview'];
      $data['search']['export_all'] = true;
      Search::constructSQL($data);
      Search::constructDatas($data);

      $pmComponentscatalog = new PluginMonitoringComponentscatalog();

      $services_id = array();
      foreach ($data['data']['rows'] as $row) {
         $services_id[] = $row['id'];
      }
      $oldvalue = current(getAllDatasFromTable(
              'glpi_plugin_monitoring_serviceevents',
              "`plugin_monitoring_services_id` IN ('".implode("', '", $services_id)."')",
              false,
              'id ASC LIMIT 1'));
      $date = new DateTime($oldvalue['date']);
      $start = time();
      if ($date->getTimestamp() < $start) {
         $start = $date->getTimestamp();
      }

      $nbdays = round((date('U') - $start) / 86400);
      echo "<script type=\"text/javascript\">
      $(function() {
          $( \"#custom_date\" ).datepicker({ minDate: -".$nbdays.", maxDate: \"+0D\", dateFormat:'mm/dd/yy' });
          $( \"#custom_time\" ).timepicker();

      });
      </script>";

      echo '<center><input type="text" id="custom_date" value="'.date('m/d/Y').'"> '
              . ' <input type="text" id="custom_time" value="'.date('H:i').'"></center>';

      echo '<div id="custom_date" style="display:none"></div>';
      echo '<div id="custom_time" style="display:none"></div>';

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='5'>";
      echo __('Resources', 'monitoring');
      $item = new $itemtype();
      $item->getFromDB($items_id);
      echo " - ".$item->getTypeName();
      echo " - ".$item->getName();
      echo "</th>";
      echo "</tr>";

      echo "<table class='tab_cadre_fixe'>";
      $previous_componentscatalog = 0;
      foreach ($data['data']['rows'] as $row) {
         $pmComponentscatalog->getFromDB($row[8][0]['id']);

         if ($row[8][0]['id'] != $previous_componentscatalog) {
            if ($previous_componentscatalog != 0) {
               echo "<tr style='border:1px solid #ccc;background-color:#ffffff'>";
               echo "<td colspan='14' height='5'></td>";
               echo "</tr>";
            }
            echo "<tr class='tab_bg_1'>";
            echo "<th colspan='14'>".$pmComponentscatalog->getTypeName()."&nbsp;:&nbsp;".$pmComponentscatalog->getLink()."</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<th>";
            echo __('Show graphics', 'monitoring');
            echo "</th>";
            echo "<th>";
            echo __('Component', 'monitoring');
            echo "</th>";
            echo "<th>";
            echo __('Resource state', 'monitoring');
            echo "</th>";
            echo "<th>";
            echo __('Last check', 'monitoring');
            echo "</th>";
            echo "<th>";
            echo __('Result details', 'monitoring');
            echo "</th>";
            echo "<th>";
            echo __('Check period', 'monitoring');
            echo "</th>";
            echo "<th>".__('Current month', 'monitoring')." ".Html::showToolTip(__('Availability', 'monitoring'), array('display'=>false))."</th>";
            echo "<th>".__('Last month', 'monitoring')." ".Html::showToolTip(__('Availability', 'monitoring'), array('display'=>false))."</th>";
            echo "<th>".__('Current year', 'monitoring')." ".Html::showToolTip(__('Availability', 'monitoring'), array('display'=>false))."</th>";
            echo "<th>".__('Detail', 'monitoring')."</th>";
            echo '<th>'.__('Acknowledge', 'monitoring').'</th>';
            echo "<th>".__('Arguments', 'monitoring')."</th>";
            echo "</tr>";
         }
         echo "<tr class='tab_bg_1'>";
         PluginMonitoringDisplay::displayLine($row, 0);
         echo "</tr>";

         $previous_componentscatalog = $row[8][0]['id'];

      }

      echo "</table>";

      Html::closeForm();
   }



   /**
    * Display graphs of services associated with host
    *
    * @param $itemtype value type of item
    * @param $items_id integer id of the object
    *
    **/
   function showGraphsByHost($itemtype, $items_id) {
      global $CFG_GLPI,$DB;

      PluginMonitoringToolbox::loadLib();
      $pmComponentscatalog = new PluginMonitoringComponentscatalog();
      $pmComponent = new PluginMonitoringComponent();
      $pmServicegraph = new PluginMonitoringServicegraph();
      $networkPort = new NetworkPort();

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `items_id`='".$items_id."'
            AND `itemtype`='".$itemtype."'";
      $result = $DB->query($query);

      echo '<center><input type="text" id="custom_date" value="'.date('m/d/Y').'"> '
              . ' <input type="text" id="custom_time" value="'.date('H:i').'"></center>';

      echo "<table class='tab_cadre_fixe'>";
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog->getFromDB($data['plugin_monitoring_componentscalalog_id']);

         $querys = "SELECT `glpi_plugin_monitoring_services`.* FROM `glpi_plugin_monitoring_services`
            LEFT JOIN `glpi_plugin_monitoring_components`
               on `plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$data['id']."'
               ORDER BY `name`";
         $results = $DB->query($querys);
         while ($datas=$DB->fetch_array($results)) {
            $pmComponent->getFromDB($datas['plugin_monitoring_components_id']);
            if ($pmComponent->fields['graph_template'] != 0) {
               echo "<tr>";
               echo "<td>";
               echo "<table class='tab_cadre'>";
               echo "<tr class='tab_bg_3'>";
               echo "<th>";
               echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/display.form.php?itemtype=PluginMonitoringService&items_id=".$datas['id']."'>";
               echo $pmComponent->fields['name'];
               echo "</a>";
               if (!is_null($datas['networkports_id'])
                       && $datas['networkports_id'] > 0) {
                  $networkPort->getFromDB($datas['networkports_id']);
                  echo " [".$networkPort->getLink()."]";
               }
               echo "</th>";
               echo "</tr>";
               echo "<tr class='tab_bg_1'>";
               echo "<td style='position: relative'>";
               $pmServicegraph->displayGraph($pmComponent->fields['graph_template'],
                                             "PluginMonitoringService",
                                             $datas['id'],
                                             "0",
                                             "2h",
                                             "",
                                             920);
               echo "</td>";
               echo "</tr>";
               echo "</table>";
               echo "</td>";
               echo "</tr>";
            }
         }
      }

      echo "</tr>";
      echo "</table>";

   }



   function showForm($items_id, $options=array(), $services_id='') {
      $pMonitoringCommand = new PluginMonitoringCommand();
      $pMonitoringServicedef = new PluginMonitoringServicedef();

      if (isset($_GET['withtemplate']) AND ($_GET['withtemplate'] == '1')) {
         $options['withtemplate'] = 1;
      } else {
         $options['withtemplate'] = 0;
      }

      if ($services_id!='') {
         $this->getEmpty();
      } else {
         $this->getFromDB($items_id);
      }
      $this->showTabs($options);
      $this->showFormHeader($options);
      if (!isset($this->fields['plugin_monitoring_servicedefs_id'])
              OR empty($this->fields['plugin_monitoring_servicedefs_id'])) {
         $pMonitoringServicedef->getEmpty();
      } else {
         $pMonitoringServicedef->getFromDB($this->fields['plugin_monitoring_servicedefs_id']);
      }
      $template = false;


      echo "<tr>";
      echo "<td>";
      if ($services_id!='') {
         echo "<input type='hidden' name='plugin_monitoring_services_id' value='".$services_id."' />";
      }
      echo __('Name')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name", ($template === "newcomp"),
                             $this->getType());
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";
      echo "<td>";
      echo __('Template')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      if ($items_id != '0') {
         echo "<input type='hidden' name='update' value='update'>\n";
      }
      echo "<input type='hidden' name='plugin_monitoring_servicedefs_id_s' value='".$this->fields['plugin_monitoring_servicedefs_id']."'>\n";
      if ($pMonitoringServicedef->fields['is_template'] == '0') {
         $this->fields['plugin_monitoring_servicedefs_id'] = 0;
      }
      Dropdown::show("PluginMonitoringServicetemplate", array(
            'name' => 'plugin_monitoring_servicetemplates_id',
            'value' => $this->fields['plugin_monitoring_servicetemplates_id'],
            'auto_submit' => true
      ));
      echo "</td>";
      echo "<td>";
      if ($this->fields["items_id"] == '') {

      } else {
         echo "<input type='hidden' name='items_id' value='".$this->fields["items_id"]."'>\n";
         echo "<input type='hidden' name='itemtype' value='".$this->fields["itemtype"]."'>\n";
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<th colspan='4'>&nbsp;</th>";
      echo "</tr>";

      echo "<tr>";
      // * itemtype link
      if ($this->fields['itemtype'] != '') {
         $itemtype = $this->fields['itemtype'];
         $item = new $itemtype();
         $item->getFromDB($this->fields['items_id']);
         echo "<td>";
         echo __('Item Type')." <i>".$item->getTypeName()."</i>";
         echo "&nbsp;:</td>";
         echo "<td>";
         echo $item->getLink(1);
         echo "</td>";
      } else {
         echo "<td colspan='2' align='center'>";
         echo __('No type associated', 'monitoring');
         echo "</td>";
      }
      // * command
      echo "<td>";
      echo __('Command', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
         $pMonitoringServicetemplate = new PluginMonitoringServicetemplate();
         $pMonitoringServicetemplate->getFromDB($this->fields['plugin_monitoring_servicetemplates_id']);
         $pMonitoringCommand->getFromDB($pMonitoringServicetemplate->fields['plugin_monitoring_commands_id']);
         echo $pMonitoringCommand->getLink(1);
      } else {
         $pMonitoringCommand->getFromDB($pMonitoringServicedef->fields['plugin_monitoring_commands_id']);
         Dropdown::show("PluginMonitoringCommand", array(
                              'name' =>'plugin_monitoring_commands_id',
                              'value'=>$pMonitoringServicedef->fields['plugin_monitoring_commands_id']
                              ));
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      // * checks
      echo "<td>".__('Check definition', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
         $pMonitoringCheck = new PluginMonitoringCheck();
         $pMonitoringCheck->getFromDB($pMonitoringServicetemplate->fields['plugin_monitoring_checks_id']);
         echo $pMonitoringCheck->getLink(1);
      } else {
         Dropdown::show("PluginMonitoringCheck",
                        array('name'=>'plugin_monitoring_checks_id',
                              'value'=>$pMonitoringServicedef->fields['plugin_monitoring_checks_id']));
      }
      echo "</td>";
      // * active check
      echo "<td>";
      echo __('Active check', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
         echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['active_checks_enabled']);
      } else {
         Dropdown::showYesNo("active_checks_enabled", $pMonitoringServicedef->fields['active_checks_enabled']);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      // * passive check
      echo "<td>";
      echo __('Passive check', 'monitoring')."&nbsp;:";
      echo "</td>";
      echo "<td align='center'>";
      if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
         echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['passive_checks_enabled']);
      } else {
         Dropdown::showYesNo("passive_checks_enabled", $pMonitoringServicedef->fields['passive_checks_enabled']);
      }
      echo "</td>";
      // * calendar
      echo "<td>".__('Check period', 'monitoring')."&nbsp;:</td>";
      echo "<td align='center'>";
      if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
         $calendar = new Calendar();
         $calendar->getFromDB($pMonitoringServicetemplate->fields['calendars_id']);
         echo $calendar->getLink(1);
      } else {
         dropdown::show("Calendar", array('name'=>'calendars_id',
                                 'value'=>$pMonitoringServicedef->fields['calendars_id']));
      }
      echo "</td>";
      echo "</tr>";

      if (!($this->fields['plugin_monitoring_servicetemplates_id'] > 0
              AND $pMonitoringServicetemplate->fields['remotesystem'] == '')) {

         echo "<tr>";
         echo "<th colspan='4'>".__('Remote check', 'monitoring')."</th>";
         echo "</tr>";

         echo "<tr>";
         // * remotesystem
         echo "<td>";
         echo __('Utility used for remote check', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         $input = array();
         $input[''] = '------';
         $input['byssh'] = 'byssh';
         $input['nrpe'] = 'nrpe';
         $input['nsca'] = 'nsca';
         if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            echo $input[$pMonitoringServicetemplate->fields['remotesystem']];
         } else {
            Dropdown::showFromArray("remotesystem",
                                 $input,
                                 array('value'=>$pMonitoringServicedef->fields['remotesystem']));
         }
         echo "</td>";
         // * is_argument
         echo "<td>";
         echo __('Use arguments (NRPE only)', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['is_arguments']);
         } else {
            Dropdown::showYesNo("is_arguments", $pMonitoringServicedef->fields['is_arguments']);
         }
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         // alias command
         echo "<td>";
         echo __('Alias command if required (NRPE only)', 'monitoring')."&nbsp;:";
         echo "</td>";
         echo "<td>";
         if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            echo "<input type='text' name='alias_commandservice' value='".$this->fields['alias_command']."' />";
         } else {
            echo "<input type='text' name='alias_command' value='".$pMonitoringServicedef->fields['alias_command']."' />";
         }
         echo "</td>";

         echo "<td>";
         echo __('Template (for graphs generation)', 'monitoring')."&nbsp;:GHJKL";
         echo "</td>";
         echo "<td>";
         if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            $pMonitoringCommand->getEmpty();
            $pMonitoringCommand->getFromDB($pMonitoringServicetemplate->fields['aliasperfdata_commands_id']);
            echo $pMonitoringCommand->getLink(1);
         } else {
            $pMonitoringCommand->getFromDB($pMonitoringServicedef->fields['aliasperfdata_commands_id']);
            Dropdown::show("PluginMonitoringCommand", array(
                                 'name' =>'aliasperfdata_commands_id',
                                 'value'=>$pMonitoringServicedef->fields['aliasperfdata_commands_id']
                                 ));
         }
         echo "</td>";
         echo "</tr>";
      }

      // * Manage arguments
      $array = array();
      $a_displayarg = array();
      if (isset($pMonitoringCommand->fields['command_line'])) {
         preg_match_all("/\\$(ARG\d+)\\$/", $pMonitoringCommand->fields['command_line'], $array);
         $a_arguments = importArrayFromDB($this->fields['arguments']);
         foreach ($array[0] as $arg) {
            if (strstr($arg, "ARG")) {
               $arg = str_replace('$', '', $arg);
               if (!isset($a_arguments[$arg])) {
                  $a_arguments[$arg] = '';
               }
               $a_displayarg[$arg] = $a_arguments[$arg];

            }
         }
      }
      if (count($a_displayarg) > 0) {
         $a_argtext = importArrayFromDB($pMonitoringCommand->fields['arguments']);
         echo "<tr>";
         echo "<th colspan='4'>".__('Argument ([text:text] is used to get values dynamically)', 'monitoring')."&nbsp;</th>";
         echo "</tr>";

         foreach ($a_displayarg as $key=>$value) {
         echo "<tr>";
         echo "<th>".$key."</th>";
         echo "<td colspan='2'>";
            if (isset($a_argtext[$key])) {
               echo nl2br($a_argtext[$key])."&nbsp;:";
            } else {
               echo __('Argument', 'monitoring')."&nbsp;:";
            }

            if ($value == '') {
               $matches = array();
               preg_match('/(\[\w+\:\w+\])/',
                              nl2br($a_argtext[$key]), $matches);
               if (isset($matches[0])) {
                  $value = $matches[0];
               }
            }

            echo "</td>";
            echo "<td>";
            echo "<input type='text' name='arg[".$key."]' value='".$value."'/><br/>";
            echo "</td>";
            echo "</tr>";
         }
      }

      $this->showFormButtons($options);
      return true;
   }


   static function convertArgument($services_id, $argument) {
      global $DB;

      $pmService = new PluginMonitoringService();
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

      $pmService->getFromDB($services_id);

      $pmComponentscatalog_Host->getFromDB($pmService->fields['plugin_monitoring_componentscatalogs_hosts_id']);

      $itemtype = $pmComponentscatalog_Host->fields['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($pmComponentscatalog_Host->fields['items_id']);

      $argument = str_replace("[", "", $argument);
      $argument = str_replace("]", "", $argument);
      $a_arg = explode(":", $argument);

      $devicetype = '';
      $devicedata = array();
      if ($itemtype == "NetworkPort") {
         $itemtype2 = $item->fields['itemtype'];
         $item2 = new $itemtype2();
         $item2->getFromDB($item->fields['items_id']);
         $devicetype = $itemtype2;
         $devicedata = $item2->fields;
      } else {
         $devicetype = $itemtype;
         $devicedata = $item->fields;
      }

      if ($devicetype == "NetworkEquipment") {
         if (class_exists("PluginFusioninventoryNetworkEquipment")) {
            $pfNetworkEquipment = new PluginFusioninventoryNetworkEquipment();
            $a_pfNetworkEquipment = current($pfNetworkEquipment->find("`networkequipments_id`='".$devicedata['id']."'", "", 1));

            switch ($a_arg[0]) {

               case 'OID':
                  // Load SNMP model and get oid.portnum
                  $query = "SELECT `glpi_plugin_fusioninventory_mappings`.`name` AS `mapping_name`,
                                   `glpi_plugin_fusioninventory_snmpmodelmibs`.*
                            FROM `glpi_plugin_fusioninventory_snmpmodelmibs`
                                 LEFT JOIN `glpi_plugin_fusioninventory_mappings`
                                           ON `glpi_plugin_fusioninventory_snmpmodelmibs`.`plugin_fusioninventory_mappings_id`=
                                              `glpi_plugin_fusioninventory_mappings`.`id`
                            WHERE `plugin_fusioninventory_snmpmodels_id`='".$a_pfNetworkEquipment['plugin_fusioninventory_snmpmodels_id']."'
                              AND `is_active`='1'
                              AND `oid_port_counter`='0'
                              AND `glpi_plugin_fusioninventory_mappings`.`name`='".$a_arg[1]."'";

                  $result=$DB->query($query);
                  while ($data=$DB->fetch_array($result)) {
                     return Dropdown::getDropdownName('glpi_plugin_fusioninventory_snmpmodelmiboids',$data['plugin_fusioninventory_snmpmodelmiboids_id']).
                          ".".$item->fields['logical_number'];
                  }


                  return '';
                  break;

               case 'SNMP':
                  if ($a_pfNetworkEquipment['plugin_fusioninventory_configsecurities_id'] == '0') {

                     switch ($a_arg[1]) {

                        case 'version':
                           return '2c';
                           break;

                        case 'authentication':
                           return 'public';
                           break;

                     }

                  }
                  $pfConfigSecurity = new PluginFusioninventoryConfigSecurity();
                  $pfConfigSecurity->getFromDB($a_pfNetworkEquipment['plugin_fusioninventory_configsecurities_id']);

                  switch ($a_arg[1]) {

                     case 'version':
                        if ($pfConfigSecurity->fields['snmpversion'] == '2') {
                           $pfConfigSecurity->fields['snmpversion'] = '2c';
                        }
                        return $pfConfigSecurity->fields['snmpversion'];
                        break;

                     case 'authentication':
                        return $pfConfigSecurity->fields['community'];
                        break;

                  }

                  break;

            }
         }
      }
      return $argument;
   }



   function showCustomArguments($services_id) {

      $pmComponent = new PluginMonitoringComponent();
      $pmCommand = new PluginMonitoringCommand();
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

      $this->getFromDB($services_id);

      $options = array();
      $options['target'] = str_replace("service.form.php", "servicearg.form.php", $this->getFormURL());

      $this->showFormHeader($options);

      $pmComponentscatalog_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);
      $itemtype = $pmComponentscatalog_Host->fields['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($pmComponentscatalog_Host->fields['items_id']);
      echo "<tr>";
      echo "<td>";
      echo $item->getTypeName()." :";
      echo "</td>";
      echo "<td>";
      echo $item->getLink();
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      $pmComponent->getFromDB($this->fields['plugin_monitoring_components_id']);
      $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);

      $array = array();
      $a_displayarg = array();
      if (isset($pmCommand->fields['command_line'])) {
         preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
         $a_arguments = importArrayFromDB($pmComponent->fields['arguments']);
         foreach ($array[0] as $arg) {
            if (strstr($arg, "ARG")) {
               $arg = str_replace('$', '', $arg);
               if (!isset($a_arguments[$arg])) {
                  $a_arguments[$arg] = '';
               }
               $a_displayarg[$arg] = $a_arguments[$arg];
            }
         }
      }
      if (count($a_displayarg) > 0) {
         $a_tags = $pmComponent->tagsAvailable();
         array_shift($a_tags);
         $a_argtext = importArrayFromDB($pmCommand->fields['arguments']);
         echo "<tr>";
         echo "<th colspan='2'>".__('Component arguments', 'monitoring')."</th>";
         echo "<th colspan='2'>".__('List of tags available', 'monitoring')."&nbsp;</th>";
         echo "</tr>";

         foreach ($a_displayarg as $key=>$value) {
         echo "<tr>";
         echo "<td>";
            if (isset($a_argtext[$key])
                    AND $a_argtext[$key] != '') {
               echo nl2br($a_argtext[$key])."&nbsp;:";
            } else {
               echo __('Argument', 'monitoring')." (".$key.")&nbsp;:";
            }
            echo "</td>";
            echo "<td>";
            echo $value."<br/>";
            echo "</td>";
            if (count($a_tags) > 0) {
               foreach ($a_tags as $key=>$value) {
                  echo "<td class='tab_bg_3'>";
                  echo "<strong>".$key."</strong>&nbsp;:";
                  echo "</td>";
                  echo "<td class='tab_bg_3'>";
                  echo $value;
                  echo "</td>";
                  unset($a_tags[$key]);
                  break;
               }
            } else {
               echo "<td colspan='2'></td>";
            }
            echo "</tr>";
         }
         foreach ($a_tags as $key=>$value) {
            echo "<tr>";
            echo "<td colspan='2'></td>";
            echo "<td class='tab_bg_3'>";
            echo "<strong>".$key."</strong>&nbsp;:";
            echo "</td>";
            echo "<td class='tab_bg_3'>";
            echo $value;
            echo "</td>";
            echo "</tr>";
         }
      }

      // customized arguments
      echo "<tr>";
      echo "<th colspan='4'>".__('Custom arguments for this resource (empty : inherit)', 'monitoring')."</th>";
      echo "</tr>";
      $array = array();
      $a_displayarg = array();
      if (isset($pmCommand->fields['command_line'])) {
         preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
         $a_arguments = importArrayFromDB($this->fields['arguments']);
         foreach ($array[0] as $arg) {
            if (strstr($arg, "ARG")) {
               $arg = str_replace('$', '', $arg);
               if (!isset($a_arguments[$arg])) {
                  $a_arguments[$arg] = '';
               }
               $a_displayarg[$arg] = $a_arguments[$arg];
            }
         }
      }
      $a_argtext = importArrayFromDB($pmCommand->fields['arguments']);
      foreach ($a_displayarg as $key=>$value) {
         echo "<tr>";
         echo "<td>";
         if (isset($a_argtext[$key])
                 AND $a_argtext[$key] != '') {
            echo nl2br($a_argtext[$key])."&nbsp;:";
         } else {
            echo __('Argument', 'monitoring')." (".$key.")&nbsp;:";
         }
         echo "</td>";
         echo "<td>";
         echo "<input type='text' name='arg[".$key."]' value='".$value."'/><br/>";
         echo "</td>";
         echo "<td colspan='2'></td>";
         echo "</tr>";
      }

      $this->showFormButtons($options);

   }



   function post_addItem() {

      $pmLog = new PluginMonitoringLog();
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

      $input = array();
      $input['itemtype'] = "PluginMonitoringService";
      $input['items_id'] = $this->fields['id'];
      $input['action'] = "add";
      $pmComponentscatalog_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);
      $itemtype = $pmComponentscatalog_Host->fields['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($pmComponentscatalog_Host->fields['items_id']);
      $input['value'] = "New service ".$this->fields['name']." for ".$item->getTypeName()." ".$item->getName();
      $pmLog->add($input);
   }



   function post_purgeItem() {

      $pmLog = new PluginMonitoringLog();

      $input = array();
      $input['itemtype'] = "PluginMonitoringService";
      $input['items_id'] = $this->fields['id'];
      $input['action'] = "delete";

      if (isset($_SESSION['plugin_monitoring_hosts'])
              && isset($_SESSION['plugin_monitoring_hosts']['itemtype'])) {
         $itemtype = $_SESSION['plugin_monitoring_hosts']['itemtype'];
         $item = new $itemtype();
         $item->getFromDB($_SESSION['plugin_monitoring_hosts']['items_id']);

         if (isset($_SESSION['plugin_monitoring_hosts']['id'])) {
            $input['value'] = "Service ".$this->fields['name']." of ".$item->getTypeName()." ".$item->getName();
         } else {
            $input['value'] = "Service ".$this->fields['name']." of port of ";
         }
         $pmLog->add($input);
      }
      unset($_SESSION['plugin_monitoring_hosts']);

      if ($this->fields['networkports_id'] > 0) {
         // Delete componentscatalog_host if no networkports in services
         if (countElementsInTable(
                 'glpi_plugin_monitoring_services',
                 "`plugin_monitoring_components_id`='".$this->fields['plugin_monitoring_components_id']."'
                  AND `networkports_id`>0
                  AND `plugin_monitoring_componentscatalogs_hosts_id`='".$this->fields['plugin_monitoring_componentscatalogs_hosts_id']."'") == 0) {
            $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
            $pmComponentscatalog_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);
            if ($pmComponentscatalog_Host->fields['is_static'] == 0) {
               $pmComponentscatalog_Host->delete($pmComponentscatalog_Host->fields);
            }
         }
      }
   }



   function showWidget($id, $time) {
      global $DB, $CFG_GLPI;

      $pmComponent = new PluginMonitoringComponent();

      if ($this->getFromDB($id)) {
         $pmComponent->getFromDB($this->fields['plugin_monitoring_components_id']);

         $pmServicegraph = new PluginMonitoringServicegraph();
         ob_start();
         $pmServicegraph->displayGraph($pmComponent->fields['graph_template'],
                                       "PluginMonitoringService",
                                       $id,
                                       "0",
                                       $time,
                                       "div",
                                       "475");
         $chart = ob_get_contents();
         ob_end_clean();
         return $chart;
      }
   }



   /**
    * Form to add acknowledge on a service/host
    */
   function showAddAcknowledgeForm($id=-1) {
      global $CFG_GLPI,$DB;

      Session::checkRight("plugin_monitoring_acknowledge", UPDATE);

      if ($id == -1) {
         $pm_Service = $this;
      } else {
         $pm_Service = new PluginMonitoringService();
         $pm_Service->getFromDB($id);
      }

      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/acknowledge.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Add an acknowledge for a service ', 'monitoring').' : '.$pm_Service->fields['name'];
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Comments');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='80' rows='4' name='acknowledge_comment' ></textarea>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='hidden' name='id' value='".$pm_Service->fields['id']."' />";
      echo "<input type='hidden' name='is_acknowledged' value='1' />";
      echo "<input type='hidden' name='acknowledge_users_id' value='".$_SESSION['glpiID']."' />";
      echo "<input type='hidden' name='referer' value='".$_SERVER['HTTP_REFERER']."' />";

      echo "<input type='submit' name='add' value=\"".__('Add an acknowledge', 'monitoring')."\" class='submit'>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      Html::closeForm();
   }



   /**
    * Form to modify acknowledge on a service
    */
   function showUpdateAcknowledgeForm($id='-1') {
      global $CFG_GLPI;

      Session::checkRight("plugin_monitoring_acknowledge", UPDATE);

      if ($id == -1) {
         $pm_Service = $this;
      } else {
         $pm_Service = new PluginMonitoringService();
         $pm_Service->getFromDB($id);
      }

      // Modify acknowledge of a service ...
      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/acknowledge.form.php'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Modify acknowledge for the service', 'monitoring').' : '.$pm_Service->getName();
      echo "</td>";
      echo "</tr>";

      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Acknowledge comment', 'monitoring');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='80' rows='4' name='acknowledge_comment' >".$pm_Service->fields['acknowledge_comment']."</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='hidden' name='id' value='".$pm_Service->fields['id']."' />";
      echo "<input type='hidden' name='is_acknowledged' value='1' />";
      echo "<input type='hidden' name='acknowledge_users_id' value='".$_SESSION['glpiID']."' />";
      echo "<input type='hidden' name='referer' value='".$_SERVER['HTTP_REFERER']."' />";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Acknowledge user', 'monitoring');
      echo "</td>";
      echo "<td>";
      $user = new User();
      $user->getFromDB($pm_Service->fields['acknowledge_users_id']);
      echo $user->getName(1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='3' align='center'>";
      echo "<input type='submit' name='update' value=\"".__('Update acknowledge comment', 'monitoring')."\" class='submit'>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
   }
}

?>