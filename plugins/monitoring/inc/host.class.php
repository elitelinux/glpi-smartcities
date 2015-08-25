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

class PluginMonitoringHost extends CommonDBTM {

   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_hoststatus';


   static function getTypeName($nb=0) {
      return __('Host', 'monitoring');
   }



   function getSearchOptions() {
      $tab = array();
      $tab['common'] = _n('Host characteristic', 'Host characteristics', 2);

      $tab[0]['table']           = 'glpi_entities';
      $tab[0]['field']           = 'name';
      $tab[0]['name']            = __('Entity');
      $tab[0]['datatype']        = 'string';

      $tab[1]['table']           = 'glpi_computers';
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Host name', 'monitoring');
      $tab[1]['datatype']        = 'string';

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'state';
      $tab[2]['name']            = __('Host state', 'monitoring');
      $tab[2]['datatype']        = 'string';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'state_type';
      $tab[3]['name']            = __('Host state type', 'monitoring');
      $tab[3]['datatype']        = 'string';

      // $tab[4]['table']           = $this->getTable();
      // $tab[4]['field']           = 'state';
      // $tab[4]['name']            = __('Host resources state', 'monitoring');
      // $tab[4]['datatype']        = 'string';
      // $tab[4]['searchtype']      = 'equals';
      // $tab[4]['datatype']        = 'itemlink';
      // $tab[4]['itemlink_type']   = 'PluginMonitoringService';

      // $tab[5]['table']           = $this->getTable();
      // $tab[5]['field']           = 'ip_address';
      // $tab[5]['name']            = __('IP address', 'monitoring');
      // $tab[5]['datatype']        = 'string';

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'last_check';
      $tab[6]['name']            = __('Last check', 'monitoring');
      $tab[6]['datatype']        = 'datetime';

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'event';
      $tab[7]['name']            = __('Result details', 'monitoring');
      $tab[7]['datatype']       = 'string';
      $tab[7]['massiveaction']   = false;

      $tab[8]['table']          = $this->getTable();
      $tab[8]['field']          = 'perf_data';
      $tab[8]['name']           = __('Performance data', 'monitoring');
      $tab[8]['datatype']       = 'string';

      $tab[9]['table']          = $this->getTable();
      $tab[9]['field']          = 'is_acknowledged';
      $tab[9]['name']           = __('Acknowledge', 'monitoring');
      $tab[9]['datatype']       = 'bool';

      $tab[10]['table']           = $this->getTable();
      $tab[10]['field']           = 'itemtype';
      $tab[10]['name']            = __('Type', 'monitoring');
      $tab[10]['datatype']        = 'string';

      $tab[11]['table']           = $this->getTable();
      $tab[11]['field']           = 'items_id';
      $tab[11]['name']            = __('ID');
      $tab[11]['datatype']        = 'string';

      $tab[20]['table']          = 'glpi_computers';
      $tab[20]['field']          = 'type';
      $tab[20]['name']           = __('Item')." > ".__('Computer');
      $tab[20]['searchtype']     = 'equals';
      $tab[20]['datatype']       = 'itemlink';
      $tab[20]['itemlink_type']  = 'Computer';

      $tab[21]['table']          = 'glpi_printers';
      $tab[21]['field']          = 'name';
      $tab[21]['name']           = __('Item')." > ".__('Printer');
      $tab[21]['searchtype']     = 'equals';
      $tab[21]['datatype']       = 'itemlink';
      $tab[21]['itemlink_type']  = 'Printer';

      $tab[22]['table']          = 'glpi_networkequipments';
      $tab[22]['field']          = 'name';
      $tab[22]['name']           = __('Item')." > ".__('Network device');
      $tab[22]['searchtype']     = 'equals';
      $tab[22]['datatype']       = 'itemlink';
      $tab[22]['itemlink_type']  = 'NetworkEquipment';


      return $tab;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      PluginMonitoringToolbox::loadLib();

      if (!$withtemplate) {
         if ($item->getType() == 'Central') {
            if (Session::haveRight("plugin_monitoring_homepage", READ)
                    && Session::haveRight("plugin_monitoring_hoststatus", PluginMonitoringHost::HOMEPAGE)) {
               return array(1 => __('Hosts status', 'monitoring'));
            } else {
               return '';
            }
         }
         $array_ret = array();
         if ($item->getID() > 0) {
            if (self::canView()) {
               $array_ret[0] = self::createTabEntry(
                       __('Resources', 'monitoring'),
                       self::countForItem($item));
               $array_ret[1] = self::createTabEntry(
                       __('Resources (graph)', 'monitoring'));
            }
         }
         return $array_ret;
      }
      return '';
   }



   /**
    * @param CommonDBTM $item
   **/
   static function countForItem(CommonDBTM $item) {
      global $DB;

      $query = "SELECT COUNT(*) AS cpt FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         LEFT JOIN `glpi_plugin_monitoring_services`
            ON `glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` =
               `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
         WHERE `itemtype` = '".$item->getType()."'
            AND `items_id` ='".$item->getField('id')."'
            AND `glpi_plugin_monitoring_services`.`id` IS NOT NULL";

      $result = $DB->query($query);
      $ligne  = $DB->fetch_assoc($result);
      return $ligne['cpt'];
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Central' :
            $pmDisplay = new PluginMonitoringDisplay();
            // $pmDisplay->showHostsCounters("Hosts", 1, 1);
            $params = Search::manageParams("PluginMonitoringHost", array());
            $pmDisplay->showHostsBoard($params);
            return true;

      }
      if ($item->getID() > 0) {
         if ($tabnum == 0) {
            PluginMonitoringToolbox::loadLib();
            $pmService = new PluginMonitoringService();
            $pmService->manageServices(get_class($item), $item->fields['id']);
            $pmHostconfig = new PluginMonitoringHostconfig();
            $pmHostconfig->showForm($item->getID(), get_class($item));
         } else if ($tabnum == 1) {
            $pmService = new PluginMonitoringService();
            $pmService->showGraphsByHost(get_class($item), $item->fields['id']);
         }
      }
      return true;
   }



   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = array(READ => __('Read'));
      $values[self::HOMEPAGE]    = __('See in homepage', 'monitoring');
      $values[self::DASHBOARD]   = __('See in dashboard', 'monitoring');

      return $values;
   }



   // Only used when plugin is updated ... should be static ?
   function verifyHosts() {
      global $DB;

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         GROUP BY `itemtype`, `items_id`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $queryH = "SELECT * FROM `".$this->getTable()."`
            WHERE `itemtype`='".$data['itemtype']."'
              AND `items_id`='".$data['items_id']."'
            LIMIT 1";
         $resultH = $DB->query($queryH);
         if ($DB->numrows($resultH) == '0') {
            $input = array();
            $input['itemtype'] = $data['itemtype'];
            $input['items_id'] = $data['items_id'];
            $this->add($input);
         }
      }
   }


   /**
    * If host does not exist add it
    *
    */
   static function addHost($item) {
      global $DB;

      $pmHost = new self();

      $query = "SELECT * FROM `".$pmHost->getTable()."`
         WHERE `itemtype`='".$item->fields['itemtype']."'
           AND `items_id`='".$item->fields['items_id']."'
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) == '0') {
         $input = array();
         $input['itemtype'] = $item->fields['itemtype'];
         $input['items_id'] = $item->fields['items_id'];
         $item2 = new $item->fields['itemtype'];
         $item2->getFromDB($item->fields['items_id']);
         // Try to fix entities_id = 0
         $input['entities_id'] = $item2->fields['entities_id'];
         $pmHost->add($input);
      }
   }



   function updateDependencies($itemtype, $items_id, $parent) {
      global $DB;

      $query = "UPDATE `glpi_plugin_monitoring_hosts`
         SET `dependencies`='".$parent."'
         WHERE `itemtype`='".$itemtype."'
           AND `items_id`='".$items_id."'";
      $DB->query($query);
   }


   /**
    * Get host name
    */
   function getName($shinken = false) {
      if ($this->getID() == -1) return '';

      $itemtype = $this->getField("itemtype");
      $item = new $itemtype();
      $item->getFromDB($this->getField("items_id"));

      $hostname = $item->getName();
      if ($shinken) {
         $hostname = preg_replace("/[^A-Za-z0-9\-_]/","",$hostname);
      }

      return $hostname;
   }


    /**
    * Get host identifier for a service
    */
   function getServicesID() {
      if ($this->getID() == -1) return -1;

      global $DB;

      $query = "SELECT
                  `glpi_plugin_monitoring_services`.`id` as service_id
                  , `glpi_plugin_monitoring_services`.`name` as service_name
                  , `glpi_plugin_monitoring_hosts`.`id` as host_id
                  , `glpi_computers`.`name` as host_name
               FROM
                  `glpi_plugin_monitoring_hosts`
               INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                    ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
               INNER JOIN `glpi_computers`
                    ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
               INNER JOIN `glpi_plugin_monitoring_services`
                    ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE (`glpi_plugin_monitoring_hosts`.`id` = '".$this->getID()."');";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $a_services = array();
         while ($data=$DB->fetch_array($result)) {
            $a_services[] = $data['service_id'];
         }
         return $a_services;
      } else {
         return false;
      }
   }


  /**
    * Is host in scheduled downtime ?
    */
   function isInScheduledDowntime() {
      if ($this->getID() == -1) return false;

      $pmDowntime = new PluginMonitoringDowntime();
      if ($pmDowntime->getFromHost($this->getID()) != -1) {
         return $pmDowntime->isInDowntime();
      }

      // Toolbox::logInFile("pm", "Scheduled downtime ? ".$this->getID()." \n");
      // $pmDowntime->getFromDBByQuery("WHERE `" . $pmDowntime->getTable() . "`.`plugin_monitoring_hosts_id` = '" . $this->getID() . "' ORDER BY end_time DESC LIMIT 1");
      // Toolbox::logInFile("pm", "Scheduled downtime ? ".$pmAcknowledge->getID()." \n");
      // if ($pmDowntime->getID() != -1) {
         // return $pmDowntime->isInDowntime();
      // }

      return false;
   }


  /**
    * Is host currently acknowledged ?
    */
   function isCurrentlyAcknowledged() {
      if ($this->getID() == -1) return false;

      $pmAcknowledge = new PluginMonitoringAcknowledge();
      if ($pmAcknowledge->getFromHost($this->getID(), 'Host') != -1) {
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
    * Set host as acknowledged
    */
   function setAcknowledged($comment='') {
      if ($this->getID() == -1) return false;

      // Do not create a new acknoledge because this function is called from acknoledge creation function !
      // $ackData = array();
      // $ackData['itemtype']       = 'PluginMonitoringHost';
      // $ackData['items_id']       = $this->getID();
      // $ackData["start_time"]     = date('Y-m-d H:i:s', $start_time);
      // $ackData["end_time"]       = date('Y-m-d H:i:s', $end_time);
      // $ackData["comment"]        = $comment;
      // $ackData["sticky"]         = 1;
      // $ackData["persistent"]     = 1;
      // $ackData["notify"]         = 1;
      // $ackData["users_id"]       = $_SESSION['glpiID'];
      // $ackData["notified"]       = 0;
      // $ackData["expired"]        = 0;
      // $pmAcknowledge = new PluginMonitoringAcknowledge();
      // $pmAcknowledge->add($ackData);

      $hostData = array();
      $hostData['id'] = $this->getID();
      $hostData['is_acknowledged'] = '1';
      $this->update($hostData);
   }
   function setUnacknowledged($comment='') {
      if ($this->getID() == -1) return false;

      $hostData = array();
      $hostData['id'] = $this->getID();
      $hostData['is_acknowledged'] = '0';
      $this->update($hostData);
   }


   /**
    * Get host entity
    */
   function getEntityID($options = array()) {
      if ($this->getID() == -1) return -1;

      $itemtype = $this->getField("itemtype");
      $item = new $itemtype();
      $item->getFromDB($this->getField("items_id"));
      return $item->fields["entities_id"];
   }


   /**
    * Get host link to display
    * options :
    *    'monitoring' to return a link to the monitoring hosts view filtered with the host
    *    else, a link to GLPI computer form
    */
   function getLink($options = array()) {
      global $CFG_GLPI;

      if ($this->getID() == -1) return '';

      if (isset($options['monitoring']) && $options['monitoring']) {
         $itemtype = $this->getField("itemtype");
         $item = new $itemtype();
         $item->getFromDB($this->getField("items_id"));
         $search_id = 1;
         if ($itemtype == 'Computer') {
            $search_id = 20;
         } else if ($itemtype == 'Printer') {
            $search_id = 21;
         } else if ($itemtype == 'NetworkEquipment') {
            $search_id = 22;
         }


         $link = $CFG_GLPI['root_doc'].
            "/plugins/monitoring/front/host.php?field[0]=".$search_id."&searchtype[0]=equals&contains[0]=".$item->getID()."&itemtype=PluginMonitoringHost&start=0";
         return $item->getLink()." [<a href='$link'>".__('Status', 'monitoring')."</a>]"."&nbsp;".$this->getComments();
      } else {
         $itemtype = $this->getField("itemtype");
         $item = new $itemtype();
         $item->getFromDB($this->getField("items_id"));
         return $item->getLink()."&nbsp;".$this->getComments();
      }
   }


   /**
    * Get host short state (state + acknowledgement)
    *
    * Return :
    * - green if host is UP
    * - red if host is DOWN, UNREACHABLE or DOWNTIME
    * - redblue if red and acknowledged
    * - orange if host is WARNING, RECOVERY or FLAPPING
    * - orangeblue if orange and acknowledged
    * - yellow for every other state
    * - yellowblue if yellow and acknowledged
    */
   static function getState($state, $state_type, $event, $acknowledge=0) {
      $shortstate = '';
      switch($state) {

         case 'UP':
         case 'OK':
            $shortstate = 'green';
            break;

         case 'DOWN':
         case 'UNREACHABLE':
         case 'CRITICAL':
         case 'DOWNTIME':
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


         // case 'UNKNOWN':
         // case '':
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
      return $shortstate;
   }


   /**
    * Get summarized state for all host services
    * $id, host id
    *    default is current host instance
    *
    * $where, services search criteria
    *    default is not acknowledged faulty services
    *
    * Returns an array containing :
    * 0 : overall services state
    * 1 : text string including date, state, event for each service
    * 2 : array of services id
    *
    */
   static function getServicesState($id=-1, $where="`glpi_plugin_monitoring_services`.`state` != 'OK' AND `glpi_plugin_monitoring_services`.`is_acknowledged` = '0'") {
      global $DB;

      if ($id == 0) {
         $id = $this->getID();
      }

      if ($id == -1) {
         return;
      }

      // Get all host services except if state is ok or is already acknowledged ...
      $host_services_ids = array();
      $host_services_state_list = '';
      $host_services_state = 'OK';
      $query = "SELECT `glpi_plugin_monitoring_services`.*
               FROM `glpi_plugin_monitoring_hosts`
                  INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
                  INNER JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE ($where )
                  AND (`glpi_plugin_monitoring_hosts`.`id` = '$id')
               ORDER BY `glpi_plugin_monitoring_services`.`name` ASC;";
      // Toolbox::logInFile("pm", "Query services for host : $id : $query\n");
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $host_services_state_list = '';
         while ($data=$DB->fetch_array($result)) {
            // Toolbox::logInFile("pm", "Service ".$data['name']." is ".$data['state'].", state : ".$data['event']."\n");
            if (! empty($host_services_state_list)) $host_services_state_list .= "\n";
            $host_services_state_list .= $data['last_check']." - ".$data['name']." : ".$data['state'].", event : ".$data['event'];
            $host_services_ids[] = $data['id'];

            switch ($data['state']) {
               case 'CRITICAL':
                  if ($host_services_state != 'CRITICAL') $host_services_state = $data['state'];
                  break;

               case 'DOWNTIME':
                  if ($host_services_state != 'DOWNTIME') $host_services_state = $data['state'];
                  break;

               case 'WARNING':
               case 'RECOVERY':
               case 'UNKNOWN':
                  if ($host_services_state == 'OK') $host_services_state = $data['state'];
                  break;

               case 'FLAPPING':
                  break;
            }
         }
      }

      return (array($host_services_state, $host_services_state_list, $host_services_ids));
   }


   /**
    * Get comments for host
    * $id, host id
    *    default is current host instance
    *
    */
   function getComments($id=-1) {
      global $CFG_GLPI;

      if ($id == -1) {
         $pm_Host = $this;
      } else {
         $pm_Host = new PluginMonitoringHost();
         $pm_Host->getFromDB($id);
      }

      // Toolbox::logInFile("pm", "Host getcomments : $id : ".$pm_Host->getID()."\n");
      $comment = "";
      $toadd   = array();

      // associated computer ...
      $item = new $pm_Host->fields['itemtype'];
      $item->getFromDB($pm_Host->fields['items_id']);

      if ($pm_Host->getField('itemtype') == 'Computer') {
         if ($item->isField('completename')) {
            $toadd[] = array('name'  => __('Complete name'),
                             'value' => nl2br($item->getField('completename')));
         }

         $type = new ComputerType();
         if ($item->getField("computertypes_id")) {
            $type->getFromDB($item->getField("computertypes_id"));
            $type = $type->getName();
            if (! empty($type)) {
               $toadd[] = array('name'  => __('Type'),
                                'value' => nl2br($type));
            }
         } else {
            return $comment;
         }

         $model = new ComputerModel();
         if ($item->getField("computermodels_id")) {
            $model->getFromDB($item->getField("computermodels_id"));
            $model = $model->getName();
            if (! empty($model)) {
               $toadd[] = array('name'  => __('Model'),
                                'value' => nl2br($model));
            }
         }

         $state = new State();
         $state->getFromDB($item->fields["states_id"]);
         $state = $state->getName();
         if (! empty($state)) {
            $toadd[] = array('name'  => __('State'),
                             'value' => nl2br($state));
         }

         $entity = new Entity();
         $entity->getFromDB($item->fields["entities_id"]);
         $entity = $entity->getName();
         if (! empty($entity)) {
            $toadd[] = array('name'  => __('Entity'),
                             'value' => nl2br($entity));
         }

         $location = new Location();
         $location->getFromDB($item->fields["locations_id"]);
         $location = $location->getName(array('complete'  => true));
         if (! empty($location)) {
            $toadd[] = array('name'  => __('Location'),
                             'value' => nl2br($location));
         }

         if (! empty($item->fields["serial"])) {
            $toadd[] = array('name'  => __('Serial'),
                             'value' => nl2br($item->fields["serial"]));
         }
         if (! empty($item->fields["otherserial"])) {
            $toadd[] = array('name'  => __('Inventory number'),
                             'value' => nl2br($item->fields["otherserial"]));
         }

         if (($pm_Host instanceof CommonDropdown)
             && $pm_Host->isField('comment')) {
            $toadd[] = array('name'  => __('Comments'),
                             'value' => nl2br($pm_Host->getField('comment')));
         }

         if (count($toadd)) {
            foreach ($toadd as $data) {
               $comment .= sprintf(__('%1$s: %2$s')."<br>",
                                   "<span class='b'>".$data['name'], "</span>".$data['value']);
            }
         }
      } else {
         $toadd[] = array('name'  => __('Host type'),
                          'value' => nl2br($item->getTypeName()));

         if ($item->isField('completename')) {
            $toadd[] = array('name'  => __('Complete name'),
                             'value' => nl2br($item->getField('completename')));
         }
      }

      if (!empty($comment)) {
         return Html::showToolTip($comment, array('display' => false));
      }
   }


   /**
    * Form to add acknowledge on an host
    */
   function showAddAcknowledgeForm($id, $allServices=false) {
      global $CFG_GLPI,$DB;

      if ($id == -1) {
         $pm_Host = $this;
      } else {
         $pm_Host = new PluginMonitoringHost();
         $pm_Host->getFromDB($id);
      }
      $hostname = $pm_Host->getName();
      $id = $pm_Host->fields['id'];

      if (empty($hostname)) return false;

      // Acknowledge an host ...
      echo "<form name='form' method='post'
            action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/acknowledge.form.php'>";

      echo "<input type='hidden' name='host_id' value='$id' />";
      echo "<input type='hidden' name='hostname' value='$hostname' />";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='3'>";
      if ($allServices) {
         echo __('Add an acknowledge for all faulty services of the host', 'monitoring').' : '.$pm_Host->getLink();
      } else {
         echo __('Add an acknowledge for the host and all faulty services of the host', 'monitoring').' : '.$pm_Host->getLink();
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr><td colspan='3'><hr/></td></tr>";

      // Acknowledge host AND all faulty services ...
      // Get all host services except if state is ok or is already acknowledged ...
      $a_ret = PluginMonitoringHost::getServicesState($id,
                                                      "`glpi_plugin_monitoring_services`.`state` != 'OK'
                                                      AND `glpi_plugin_monitoring_services`.`is_acknowledged` = '0'");
      $host_services_state = $a_ret[0];
      $host_services_state_list = $a_ret[1];
      $host_services = $a_ret[2];

      if (count($host_services)) {
         echo "<tr><td colspan='3'>".__('All these services will be acknowledged', 'monitoring')." : </td></tr>";
         echo "<input type='hidden' name='serviceCount' value='".count($host_services)."' />";

         $i=0;
         foreach ($host_services as $data) {
            // Toolbox::logInFile("pm", "data : ".serialize($data)."\n");
            $pmService = new PluginMonitoringService();
            $pmService->getFromDB($data);
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='1'></td>";
            echo "<td colspan='2'>".Html::convDateTime($pmService->fields['last_check'])." : ".$pmService->fields['name']." - ".$pmService->fields['state']." : ".$pmService->fields['event']."</td>";
            echo "</tr>";
            echo "<input type='hidden' name='serviceId$i' value='".$pmService->fields['id']."' />";
            $i++;
         }
         echo "<tr><td colspan='3'><hr/></td></tr>";
      }

      if (! $allServices) {
         echo "<input type='hidden' name='hostAcknowledge' value='$hostname' />";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Acknowledge comment', 'monitoring');
      echo "</td>";
      echo "<td colspan='2'>";
      echo "<textarea cols='80' rows='4' name='acknowledge_comment' ></textarea>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='3' align='center'>";
      echo "<input type='hidden' name='id' value='$id' />";
      echo "<input type='hidden' name='is_acknowledged' value='1' />";
      echo "<input type='hidden' name='acknowledge_users_id' value='".$_SESSION['glpiID']."' />";
      echo "<input type='hidden' name='referer' value='".$_SERVER['HTTP_REFERER']."' />";

      echo "<input type='submit' name='add' value=\"".__('Add an acknowledge for host', 'monitoring')."\" class='submit'>";
      echo "</td>";
      echo "</tr>";

      if (Session::haveRight('create_ticket', 1)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='3' align='center'>";
         echo "<input type='hidden' name='name' value='".__('Host is down', 'monitoring')."' />";
         echo "<input type='hidden' name='redirect' value='".$CFG_GLPI["root_doc"]."/front/ticket.form.php' />";
         echo "<input type='submit' name='add_and_ticket' value=\"".__('Add an acknowledge for host and create a ticket', 'monitoring')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      Html::closeForm();

      return true;
   }



   /**
    * Form to modify acknowledge of an host
    */
   function showUpdateAcknowledgeForm($id=-1) {
      global $CFG_GLPI;

      Session::checkRight("plugin_monitoring_acknowledge", UPDATE);

      if ($id == -1) {
         $pm_Host = $this;
      } else {
         $pm_Host = new PluginMonitoringHost();
         $pm_Host->getFromDB($id);
      }
      $hostname = $pm_Host->getName();

      echo "<form name='form' method='post'
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/acknowledge.form.php'>";

      echo "<input type='hidden' name='host_id' value='$id' />";
      echo "<input type='hidden' name='hostname' value='$hostname' />";
      echo "<input type='hidden' name='hostAcknowledge' value='$hostname' />";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Modify acknowledge for the host', 'monitoring').' : '.$pm_Host->getLink();
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Acknowledge comment', 'monitoring');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='80' rows='4' name='acknowledge_comment' >".$pm_Host->fields['acknowledge_comment']."</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='hidden' name='id' value='".$pm_Host->fields['id']."' />";
      echo "<input type='hidden' name='is_acknowledged' value='1' />";
      echo "<input type='hidden' name='acknowledge_users_id' value='".$_SESSION['glpiID']."' />";
      echo "<input type='hidden' name='referer' value='".$_SERVER['HTTP_REFERER']."' />";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Acknowledge user', 'monitoring');
      echo "</td>";
      echo "<td>";
      $user = new User();
      $user->getFromDB($pm_Host->fields['acknowledge_users_id']);
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