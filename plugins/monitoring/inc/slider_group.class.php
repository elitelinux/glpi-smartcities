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
   @since     2014

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginMonitoringSlider_Group extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'PluginMonitoringSlider';
   static public $items_id_1          = 'pluginmonitoringsliders_id';
   static public $itemtype_2          = 'Group';
   static public $items_id_2          = 'groups_id';

   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
   static public $logs_for_item_2     = false;


   /**
    * Get groups for a slider
    *
    * @param $pluginmonitoringsliders_id ID of the slider
    *
    * @return array of groups linked to a slider
   **/
   static function getGroups($pluginmonitoringsliders_id) {
      global $DB;

      $groups = array();
      $query  = "SELECT `glpi_plugin_monitoring_sliders_groups`.*
                 FROM `glpi_plugin_monitoring_sliders_groups`
                 WHERE `pluginmonitoringsliders_id` = '$pluginmonitoringsliders_id'";

      foreach ($DB->request($query) as $data) {
         $groups[$data['groups_id']][] = $data;
      }
      return $groups;
   }

}
?>
