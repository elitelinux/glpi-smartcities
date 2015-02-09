<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Additionalalerts plugin for GLPI
 Copyright (C) 2003-2011 by the Additionalalerts Development Team.

 https://forge.indepnet.net/projects/additionalalerts
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Additionalalerts.

 Additionalalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Additionalalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with additionalalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginAdditionalalertsNotificationTargetOcsAlert extends NotificationTarget {
   
   static $rightname = "plugin_additionalalerts";
   
   function getEvents() {
      return array ('ocs' => PluginAdditionalalertsOcsAlert::getTypeName(2),
                     'newocs' => __('New imported computers from OCS-NG', 'additionalalerts'));
   }

   function getDatasForTemplate($event,$options=array()) {
      global $CFG_GLPI;

      $this->datas['##ocsmachine.entity##'] =
                        Dropdown::getDropdownName('glpi_entities',
                                                  $options['entities_id']);
      $this->datas['##lang.ocsmachine.entity##'] =__('Entity');
      
      $events = $this->getAllEvents();
      
      $delay_ocs=$options["delay_ocs"];
         
      if ($event=="newocs")
         $this->datas['##lang.ocsmachine.title##'] = $events[$event];
      else
         $this->datas['##lang.ocsmachine.title##'] = __('Computers not synchronized with OCS-NG since more', 'additionalalerts')." ".$delay_ocs." "._n('Day','Days',2);
      $this->datas['##lang.ocsmachine.name##'] = __('Name');
      $this->datas['##lang.ocsmachine.urlname##'] = __('URL');
      $this->datas['##lang.ocsmachine.operatingsystem##'] = __('Operating system');
      $this->datas['##lang.ocsmachine.state##'] = __('Status');
      $this->datas['##lang.ocsmachine.location##'] = __('Location');
      $this->datas['##lang.ocsmachine.user##'] = __('User')." / ".__('Group')." / ".__('Alternate username');
      $this->datas['##lang.ocsmachine.urluser##'] = __('URL');
      $this->datas['##lang.ocsmachine.urlgroup##'] = __('URL');
      $this->datas['##lang.ocsmachine.lastocsupdate##'] = __('Last OCSNG inventory date', 'additionalalerts');
      $this->datas['##lang.ocsmachine.lastupdate##'] = __('Import date in GLPI', 'additionalalerts');
      $this->datas['##lang.ocsmachine.ocsserver##'] = __('OCSNG server', 'additionalalerts');
      
      foreach($options['ocsmachines'] as $id => $ocsmachine) {
         $tmp = array();
         
         $tmp['##ocsmachine.urlname##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=computer_".
                                    $ocsmachine['id']);
         $tmp['##ocsmachine.name##'] = $ocsmachine['name'];
         $tmp['##ocsmachine.operatingsystem##'] = Dropdown::getDropdownName("glpi_operatingsystems",$ocsmachine['operatingsystems_id']);
         $tmp['##ocsmachine.state##'] = Dropdown::getDropdownName("glpi_states",$ocsmachine['states_id']);
         $tmp['##ocsmachine.location##'] = Dropdown::getDropdownName("glpi_locations",$ocsmachine['locations_id']);
         
         $tmp['##ocsmachine.urluser##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=user_".
                                    $ocsmachine['users_id']);
         
         $tmp['##ocsmachine.urlgroup##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=group_".
                                    $ocsmachine['groups_id']);

         $tmp['##ocsmachine.user##'] = getUserName($ocsmachine['users_id']);
         $tmp['##ocsmachine.group##'] = Dropdown::getDropdownName("glpi_groups",$ocsmachine['groups_id']);
         $tmp['##ocsmachine.contact##'] = $ocsmachine['contact'];
         
         $tmp['##ocsmachine.lastocsupdate##'] = Html::convDateTime($ocsmachine['last_ocs_update']);
         $tmp['##ocsmachine.lastupdate##'] = Html::convDateTime($ocsmachine['last_update']);
         $tmp['##ocsmachine.ocsserver##'] = Dropdown::getDropdownName("glpi_plugin_ocsinventoryng_ocsservers",$ocsmachine['plugin_ocsinventoryng_ocsservers_id']);
         
         $this->datas['ocsmachines'][] = $tmp;
      }
   }
   
   function getTags() {

      $tags = array('ocsmachine.name'            => __('Name'),
                     'ocsmachine.urlname'            => __('URL')." ".__('Name'),
                   'ocsmachine.operatingsystem'   => __('Operating system'),
                   'ocsmachine.state'    => __('Status'),
                   'ocsmachine.location' => __('Location'),
                   'ocsmachine.user'    => __('User'),
                   'ocsmachine.urluser' => __('URL')." ".__('User'),
                   'ocsmachine.group' => __('Group'),
                   'ocsmachine.urlgroup' => __('URL')." ".__('Group'),
                   'ocsmachine.contact' => __('Alternate username'),
                   'ocsmachine.lastocsupdate' => __('Last OCSNG inventory date', 'additionalalerts'),
                   'ocsmachine.lastupdate' => __('Import date in GLPI', 'additionalalerts'),
                   'ocsmachine.ocsserver' => __('OCSNG server', 'additionalalerts'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'additionalalerts',
                                'label'=>PluginAdditionalalertsOcsAlert::getTypeName(2),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('ocs','newocs')));

      asort($this->tag_descriptions);
   }
}

?>