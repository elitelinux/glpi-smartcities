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
   @co-author Frédéric Mohier
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringShinkenwebservice extends CommonDBTM {

   function sendAcknowledge($host_id=-1, $service_id=-1, $author= '', $comment='', $sticky='1', $notify='1', $persistent='1', $operation='') {
      global $DB;

      if (($host_id == -1) && ($service_id == -1)) return false;

//      Toolbox::logInFile("pm", "acknowledge, sendAcknowledge, host : $host_id / $service_id\n");

      $pmTag = new PluginMonitoringTag();
      $pmService = new PluginMonitoringService();
      $pmService->getFromDB($service_id);
      $service_description = $pmService->getName(array('shinken'=>'1'));
      $pmHost = new PluginMonitoringHost();
      $pmHost->getFromDB(($host_id == -1) ? $pmService->getHostID() : $host_id);
      $hostname = $pmHost->getName(true);

//      Toolbox::logInFile("pm", "acknowledge, sendAcknowledge, host : $hostname\n");

      // Acknowledge an host ...
      $acknowledgeServiceOnly = true;
      $a_fields = array();

      if ($host_id == -1) {
         $tag = PluginMonitoringEntity::getTagByEntities($pmService->getEntityID());
      } else {
         // ... one service of the host.
         $tag = PluginMonitoringEntity::getTagByEntities($pmHost->getEntityID());
      }
      $ip = $pmTag->getIP($tag);
      $auth = $pmTag->getAuth($tag);
      $port = $pmTag->getPort($tag);

      $url = 'http://'.$ip.':'.$port.'/';
      $action = 'acknowledge';
      $a_fields = array(
          'action'               => empty($operation) ? 'add' : $operation,
          'host_name'            => $hostname,
          'author'               => $author,
          'service_description'  => $service_description,
          'comment'              => mb_convert_encoding($comment, "iso-8859-1"),
          // 'comment'              => $comment,
          'sticky'               => $sticky,
          'notify'               => $notify,
          'persistent'           => $persistent
      );

      return $this->sendCommand($url, $action, $a_fields, '', $auth);
   }


   function sendDowntime($host_id=-1, $service_id=-1, $author= '', $comment='', $flexible='0', $start_time='0', $end_time='0', $duration='3600', $operation='') {
      global $DB;

      if (($host_id == -1) && ($service_id == -1)) return false;

      $pmTag = new PluginMonitoringTag();
      $pmService = new PluginMonitoringService();
      $pmService->getFromDB($service_id);
      $service_description = $pmService->getName(array('shinken'=>'1'));
      $pmHost = new PluginMonitoringHost();
      $pmHost->getFromDB(($host_id == -1) ? $pmService->getHostID() : $host_id);
      $hostname = $pmHost->getName(true);

      // Downtime an host ...
      $acknowledgeServiceOnly = true;
      $a_fields = array();

      if ($host_id == -1) {
         $tag = PluginMonitoringEntity::getTagByEntities($pmService->getEntityID());
      } else {
         // ... one service of the host.
         $tag = PluginMonitoringEntity::getTagByEntities($pmHost->getEntityID());
      }
      $ip = $pmTag->getIP($tag);
      $auth = $pmTag->getAuth($tag);
      $port = $pmTag->getPort($tag);

      $url = 'http://'.$ip.':'.$port.'/';
      $action = 'downtime';
      $a_fields = array(
          'action'               => empty($operation) ? 'add' : $operation,
          'host_name'            => $hostname,
          'service_description'  => $service_description,
          'author'               => $author,
          'comment'              => mb_convert_encoding($comment, "iso-8859-1"),
          'flexible'             => $flexible,
          'start_time'           => PluginMonitoringServiceevent::convert_datetime_timestamp($start_time),
          'end_time'             => PluginMonitoringServiceevent::convert_datetime_timestamp($end_time),
          'trigger_id'           => '0',
          'duration'             => $duration
      );

      // Send downtime command ...
      return $this->sendCommand($url, $action, $a_fields, '', $auth);
   }


   function sendRestartArbiter($force=0, $tag=0, $command='reload') {

      $pmTag = new PluginMonitoringTag();
      $pmLog = new PluginMonitoringLog();

      if (!$pmLog->isRestartLessThanFiveMinutes()
              || $force) {
         if ($tag > 0) {
            $pmTag->getFromDB($tag);

            $url = 'http://'.$pmTag->fields['ip'].':'.$pmTag->fields['port'].'/';

            $auth = $pmTag->getAuth($pmTag->fields['tag']);
            if ($this->sendCommand($url, $command, array(), '', $auth)) {
               $input = array();
               $input['user_name'] = $_SESSION['glpifirstname'].' '.$_SESSION['glpirealname'].
                       ' ('.$_SESSION['glpiname'].')';
               $input['action']    = $command . "_planned";
               $input['date_mod']  = date("Y-m-d H:i:s");
               $input['value']     = $pmTag->fields['tag'];
               $pmLog->add($input);
            }
         } else {
            $a_tagsBrut = $pmTag->find();

            $a_tags = array();
            foreach ($a_tagsBrut as $data) {
               if (!isset($a_tags[$data['ip'].':'.$data['port']])) {
                  $a_tags[$data['ip'].':'.$data['port']] = $data;
               }
            }
            foreach ($a_tags as $data) {
               // TODO : should be parameters ... Shinken arbiter may use another port and may use HTTPS !
               $url = 'http://'.$data['ip'].':'.$data['port'].'/';

               $auth = $pmTag->getAuth($data['tag']);
               if ($this->sendCommand($url, $command, array(), '', $auth)) {
                  $input = array();
                  $input['user_name'] = $_SESSION['glpifirstname'].' '.$_SESSION['glpirealname'].
                          ' ('.$_SESSION['glpiname'].')';
                  $input['action']    = $command . "_planned";
                  $input['date_mod']  = date("Y-m-d H:i:s");
                  $input['value']     = $data['tag'];
                  $pmLog->add($input);
               }
            }
         }
      }
   }


   function sendCommand($url, $action, $a_fields, $fields_string='', $auth='') {

      if ($fields_string == '') {
         foreach($a_fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
         }
         rtrim($fields_string, '&');
      }

      $ch = curl_init();

      curl_setopt($ch,CURLOPT_URL, $url.$action);
      curl_setopt($ch,CURLOPT_POST, count($a_fields));
      curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 4);
      curl_setopt($ch,CURLOPT_TIMEOUT, 4);
      if ($auth != '') {
         curl_setopt($ch,CURLOPT_USERPWD, $auth);
      }

      $ret = curl_exec($ch);
      $return = true;
      if ($ret === false) {
         Session::addMessageAfterRedirect(
                 __('Shinken communication failed:', 'monitoring').' '.curl_error($ch).'<br/>'.$url.$action.' '.$fields_string,
                 false,
                 ERROR);
         $return = false;
      } else if (strstr($ret, 'error')) {
         Session::addMessageAfterRedirect(
                 __('Shinken communication failed:', 'monitoring').' '.$ret.'<br/>'.$url.$action.' '.$fields_string,
                 false,
                 ERROR);
         $return = false;
      } else {
         Session::addMessageAfterRedirect(
                 __('Shinken communication succeeded:', 'monitoring').' '.$ret.'<br/>'.$url.$action.' '.$fields_string,
                 false);
         $return = true;
      }
      curl_close($ch);
      return $return;
   }

}

?>