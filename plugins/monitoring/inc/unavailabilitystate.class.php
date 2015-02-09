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

class PluginMonitoringUnavailabilityState extends CommonDBTM {
   private $currentid = 0;


   static function getTypeName($nb=0) {
      return __CLASS__;
   }


   static function canCreate() {
      return true;
   }



   static function canView() {
      return true;
   }



   function getLastID($services_id) {

      $datas = $this->find("`plugin_monitoring_services_id`='".$services_id."'",
                                           "",
                                           1);
      if (count($datas) == 0) {
         // Create a new line
         $input = array(
             'plugin_monitoring_services_id' => $services_id
         );
         $this->currentid = $this->add($input);
         return 0;
      } else {
         $data = current($datas);
         $this->currentid = $data['id'];
         return $data['plugin_monitoring_serviceevents_id'];
      }
      return 0;
   }



   function setLastID($services_id, $serviceevents_id) {
      $input = array(
          'id'                                 => $this->currentid,
          'plugin_monitoring_serviceevents_id' => $serviceevents_id
      );
      $this->update($input);
   }
}
?>