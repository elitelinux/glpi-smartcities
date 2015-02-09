<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
        die("Sorry. You can't access directly to this file");
}

// Class for a Dropdown
class PluginRacksRoomLocation extends CommonTreeDropdown {

   static $rightname = "plugin_racks";
   
   static function getTypeName($nb = 0) {
      return _n('Place', 'Places', $nb, 'racks');
   }

   static function getUsedNumber() {
      global $DB;

      $used  = array();
      $query = "SELECT `plugin_racks_roomlocations_id`
                FROM `glpi_plugin_racks_racks`
                WHERE `plugin_racks_roomlocations_id` IS NOT NULL";

      foreach ($DB->request($query) as $data ) {
         $used[] = $data["plugin_racks_roomlocations_id"];
      }
      return $used;
   }

   function dropdownRoomLocations($name,$value,$entity) {
      $used = self::getUsedNumber();
      Dropdown::show('PluginRacksRoomLocation', 
                     array('name'   => "plugin_racks_roomlocations_id",
                           'value'  => $value,
                           'entity' => $entity,
                           'used'   => $used));

   }
}
?>