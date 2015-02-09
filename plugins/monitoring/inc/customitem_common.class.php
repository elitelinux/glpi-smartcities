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

class PluginMonitoringCustomitem_Common {


   static function getTimes() {
      $a_times = array(
          'lastday24h'      => __('Last day (last 24 hours)', 'monitoring'),
          'lastdaymidnight' => __('Last day (since midnight)', 'monitoring'),
          'week7d'          => __('Last week (last 7 days)', 'monitoring'),
          'weekmonday'      => __('Last week (since Monday)', 'monitoring'),
          'weeksunday'      => __('Last week (since Sunday)', 'monitoring'),
          'month30d'        => __('Last month (last 30 days)', 'monitoring'),
          'monthfirstday'   => __('Last month (since first day of month)', 'monitoring'),
          'year365day'      => __('Last year (365 days)', 'monitoring'),
          'yearjanuary'     => __('Last year (since first January)', 'monitoring')
      );
      return $a_times;
   }



   static function getTimeRange($data) {

      $begin = '';
      switch ($data['time']) {

         case 'lastday24h':
            $begin = date('Y-m-d H:i:s', strtotime("-1 day"));
            break;

         case 'lastdaymidnight':
            $begin = date('Y-m-d H:i:s', strtotime("today"));
            break;

         case 'week7d':
            $begin = date('Y-m-d H:i:s', strtotime("-1 week"));
            break;

         case 'weekmonday':
            $begin = date('Y-m-d H:i:s', strtotime("last Monday"));
            break;

         case 'weeksunday':
            $begin = date('Y-m-d H:i:s', strtotime("last Sunday"));
            break;

         case 'month30d':
            $begin = date('Y-m-d H:i:s', strtotime("-1 month"));
            break;

         case 'monthfirstday':
            $begin = date('Y-m-d H:i:s', strtotime("first day of this month"));
            break;

         case 'year365day':
            $begin = date('Y-m-d H:i:s', strtotime("-1 year"));
            break;

         case 'yearjanuary':
            $begin = date('Y-m-d H:i:s', strtotime("first day of this year"));
            break;

      }
      return array(
          'begin' => $begin,
          'end'   => date('Y-m-d H:i:s')
      );
   }

}

?>