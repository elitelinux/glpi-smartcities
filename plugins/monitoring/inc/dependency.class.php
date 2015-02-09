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

class PluginMonitoringDependency extends CommonDBTM {
   private $routes = array();
   private $devices = array();
   private $line = 0;

   function getRoutes($Shinkens_id) {
      // 2659
      $networkPort = new NetworkPort();
      $this->routes['0'] = array();
      $a_networkports = $networkPort->find("`itemtype`='Computer'
         AND `items_id`='".$Shinkens_id."'");
      foreach ($a_networkports as $data_n) {
         $networkports_id = $networkPort->getContact($data_n['id']);
         if ($networkports_id) {
            $networkPort->getFromDB($networkports_id);
            if ($networkPort->fields['itemtype'] == 'NetworkEquipment') {
               array_push($this->routes['0'], $networkPort->fields['items_id']);
               $this->devices[$networkPort->fields['items_id']] = $networkPort->fields['items_id'];
               $this->getNetworkEquipment($networkPort->fields['items_id'], 0);
            }
         }
      }
   }



   function getNetworkEquipment($id, $line, $a_links = array()) {
      $networkPort = new NetworkPort();

      $a_networkports = $networkPort->find("`itemtype`='NetworkEquipment'
         AND `items_id`='".$id."'");
      $i = 0;
      foreach ($a_networkports as $data_n) {
         $networkports_id = $networkPort->getContact($data_n['id']);
         if ($networkports_id) {
            $networkPort->getFromDB($networkports_id);
            switch ($networkPort->fields['itemtype']) {

               case 'NetworkEquipment':
                  if ($i > 0) {
                     $this->line++;
                     $this->routes[$this->line] = $this->routes[$line];
                     $line = $this->line;
                  }
                  array_push($this->routes[$line], $networkPort->fields['items_id']);
                  if (!isset($this->devices[$networkPort->fields['items_id']])) {
                     $this->devices[$networkPort->fields['items_id']] = $networkPort->fields['items_id'];
                     $this->getNetworkEquipment($networkPort->fields['items_id'], $line);
                  }
                  $i++;
                  break;

            }
         }
      }
   }


}

?>