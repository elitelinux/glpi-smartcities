<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Badges plugin for GLPI
 Copyright (C) 2003-2011 by the badges Development Team.

 https://forge.indepnet.net/projects/badges
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of badges.

 Badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginBadgesNotificationTargetBadge extends NotificationTarget {

   function getEvents() {
      return array ('ExpiredBadges' => __('Badges at the end of the validity', 'badges'),
                     'BadgesWhichExpire' => __('Badges which expires', 'badges'));
   }

   function getDatasForTemplate($event,$options=array()) {

      $this->datas['##badge.entity##'] =
                        Dropdown::getDropdownName('glpi_entities',
                                                  $options['entities_id']);
      $this->datas['##lang.badge.entity##'] = __('Entity');
      $this->datas['##badge.action##'] = ($event=="ExpiredBadges"?__('Badges at the end of the validity', 'badges'):
         __('Badges which expires', 'badges'));
      
      $this->datas['##lang.badge.name##'] = __('Name');
      $this->datas['##lang.badge.dateexpiration##'] = __('Date of end of validity', 'badges');
      $this->datas['##lang.badge.serial##'] = __('Serial number');
      $this->datas['##lang.badge.users##'] = __('Allotted to', 'badges');

      foreach($options['badges'] as $id => $badge) {
         $tmp = array();

         $tmp['##badge.name##'] = $badge['name'];
         $tmp['##badge.serial##'] = $badge['serial'];
         $tmp['##badge.users##'] = Html::clean(getUserName($badge["users_id"]));
         $tmp['##badge.dateexpiration##'] = Html::convDate($badge['date_expiration']);

         $this->datas['badges'][] = $tmp;
      }
   }
   
   function getTags() {

      $tags = array('badge.name'             => __('Name'),
                   'badge.serial'            => __('Serial number'),
                   'badge.dateexpiration'    => __('Date of end of validity', 'badges'),
                   'badge.users'             => __('Allotted to', 'badges'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'badges',
                                'label'=> __('Badges expired or badges which expires', 'badges'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('BadgesWhichExpire','ExpiredBadges')));

      asort($this->tag_descriptions);
   }
}

?>