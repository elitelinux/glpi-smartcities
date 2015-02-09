<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginCertificatesNotificationTargetCertificate extends NotificationTarget {

   function getEvents() {
      return array ('ExpiredCertificates' => __('Expired certificates', 'certificates'),
                     'CertificatesWhichExpire' => __('Expiring certificates', 'certificates'));
   }

   function getDatasForTemplate($event,$options=array()) {

      $this->datas['##certificate.entity##'] =
                        Dropdown::getDropdownName('glpi_entities',
                                                  $options['entities_id']);
      $this->datas['##lang.certificate.entity##'] = __('Entity');
      $this->datas['##certificate.action##'] = ($event=="ExpiredCertificates"?__('Expired certificates', 'certificates'):
                                                         __('Expiring certificates', 'certificates'));
      
      $this->datas['##lang.certificate.name##'] = __('Name');
      $this->datas['##lang.certificate.dateexpiration##'] = __('Expiration date');

      foreach($options['certificates'] as $id => $certificate) {
         $tmp = array();

         $tmp['##certificate.name##'] = $certificate['name'];
         $tmp['##certificate.dateexpiration##'] = Html::convDate($certificate['date_expiration']);

         $this->datas['certificates'][] = $tmp;
      }
   }
   
   function getTags() {

      $tags = array('certificate.name'             => __('Name'),
                     'certificate.dateexpiration'  => __('Expiration date'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'certificates',
                                'label'=>__('Expired or expiring certificates', 'certificates'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('CertificatesWhichExpire','ExpiredCertificates')));

      asort($this->tag_descriptions);
   }
}

?>