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
class PluginAdditionalalertsNotificationTargetInfocomAlert extends NotificationTarget {
   
   static $rightname = "plugin_additionalalerts";
   
   function getEvents() {
      return array ('notinfocom' => PluginAdditionalalertsInfocomAlert::getTypeName(2));
   }

   function getDatasForTemplate($event,$options=array()) {
      global $CFG_GLPI;

      $this->datas['##notinfocom.entity##'] =
                        Dropdown::getDropdownName('glpi_entities',
                                                  $options['entities_id']);
      $this->datas['##lang.notinfocom.entity##'] = __('Entity');
      
      $events = $this->getAllEvents();

      $this->datas['##lang.notinfocom.title##'] = $events[$event];
      
      $this->datas['##lang.notinfocom.name##'] = __('Name');
      $this->datas['##lang.notinfocom.urlname##'] = __('URL');
      $this->datas['##lang.notinfocom.computertype##'] = __('Type');
      $this->datas['##lang.notinfocom.operatingsystem##'] = __('Operating system');
      $this->datas['##lang.notinfocom.state##'] = __('Status');
      $this->datas['##lang.notinfocom.location##'] = __('Location');
      $this->datas['##lang.notinfocom.urluser##'] = __('URL');
      $this->datas['##lang.notinfocom.urlgroup##'] = __('URL');
      $this->datas['##lang.notinfocom.user##'] = __('User');
      $this->datas['##lang.notinfocom.group##'] = __('Group');
      
      foreach($options['notinfocoms'] as $id => $notinfocom) {
         $tmp = array();
         
         $tmp['##notinfocom.urlname##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=computer_".
                                    $notinfocom['id']);
         $tmp['##notinfocom.name##'] = $notinfocom['name'];
         $tmp['##notinfocom.computertype##'] = Dropdown::getDropdownName("glpi_computertypes",$notinfocom['computertypes_id']);
         $tmp['##notinfocom.operatingsystem##'] = Dropdown::getDropdownName("glpi_operatingsystems",$notinfocom['operatingsystems_id']);
         $tmp['##notinfocom.state##'] = Dropdown::getDropdownName("glpi_states",$notinfocom['states_id']);
         $tmp['##notinfocom.location##'] = Dropdown::getDropdownName("glpi_locations",$notinfocom['locations_id']);
         
         $tmp['##notinfocom.urluser##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=user_".
                                    $notinfocom['users_id']);
         
         $tmp['##notinfocom.urlgroup##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=group_".
                                    $notinfocom['groups_id']);

         $tmp['##notinfocom.user##'] = getUserName($notinfocom['users_id']);
         $tmp['##notinfocom.group##'] = Dropdown::getDropdownName("glpi_groups",$notinfocom['groups_id']);
         $tmp['##notinfocom.contact##'] = $notinfocom['contact'];
         
         $this->datas['notinfocoms'][] = $tmp;
      }
   }
   
   function getTags() {

      $tags = array('notinfocom.name'            => __('Name'),
                     'notinfocom.urlname'            => __('URL')." ".__('Name'),
                   'notinfocom.computertype'            => __('Type'),
                   'notinfocom.operatingsystem'    => __('Operating system'),
                   'notinfocom.state' => __('Status'),
                   'notinfocom.location' => __('Location'),
                   'notinfocom.user'    => __('User'),
                   'notinfocom.urluser' => __('URL')." ".__('User'),
                   'notinfocom.group' => __('Group'),
                   'notinfocom.urlgroup' => __('URL')." ".__('Group'),
                   'notinfocom.contact' => __('Alternate username'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'additionalalerts',
                                'label'=>PluginAdditionalalertsInfocomAlert::getTypeName(2),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('notinfocom')));
      
      
      asort($this->tag_descriptions);
   }
}

?>