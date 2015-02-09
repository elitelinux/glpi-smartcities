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
   @since     2012

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringPerfdata extends CommonDBTM {

   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_perfdata';

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Graph template', 'monitoring');
   }


   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = parent::getRights();
      $values[self::HOMEPAGE]    = __('See in homepage', 'monitoring');
      $values[self::DASHBOARD]   = __('See in dashboard', 'monitoring');

      return $values;
   }



   static function initDB() {
      $pmPerfdata       = new PluginMonitoringPerfdata();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      // * check_ping
      $input = array(
          'name'     => 'check_ping',
          'perfdata' => 'rta=7.306000ms;1.000000;2.000000;0.000000 pl=0%;1;30;0'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'rta',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'response_time',
          'dsname2'                        => 'warning_limit_rta',
          'dsname3'                        => 'critical_limit_rta',
          'dsname4'                        => 'other_rta'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'pl',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 4,
          'dsname1'                        => 'packet_loss',
          'dsname2'                        => 'warning_limit_pl',
          'dsname3'                        => 'critical_limit_pl',
          'dsname4'                        => 'other_pl'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_cpu_usage
      $input = array(
          'name'     => 'check_cpu_usage',
          'perfdata' => 'cpu_usage=6%;80;100; cpu_user=3%; cpu_system=3%;'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'cpu_usage',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'usage',
          'dsname2'                        => 'usage_warning',
          'dsname3'                        => 'usage_critical'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'cpu_user',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 1,
          'dsname1'                        => 'user'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'cpu_system',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 1,
          'dsname1'                        => 'system'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_load
      $input = array(
          'name'     => 'check_load',
          'perfdata' => 'load1=0.090;1.000;2.000;0; load5=0.090;1.000;2.000;0; load15=0.074;1.000;2.000;0;'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'load1',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'load1min_current',
          'dsname2'                        => 'load1min_warning',
          'dsname3'                        => 'load1min_critical',
          'dsname4'                        => 'load1min_other'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'load5',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 4,
          'dsname1'                        => 'load5min_current',
          'dsname2'                        => 'load5min_warning',
          'dsname3'                        => 'load5min_critical',
          'dsname4'                        => 'load5min_other'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'load15',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 4,
          'dsname1'                        => 'load15min_current',
          'dsname2'                        => 'load15min_warning',
          'dsname3'                        => 'load15min_critical',
          'dsname4'                        => 'load15min_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_mem
      $input = array(
          'name'     => 'check_mem',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'pct',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 1,
          'dsname1'                        => 'memory_used'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_users
      $input = array(
          'name'     => 'check_users',
          'perfdata' => 'users=1;2;5;0'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'users',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'users_current',
          'dsname2'                        => 'users_warning',
          'dsname3'                        => 'users_critical',
          'dsname4'                        => 'users_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_iftraffic41
      $input = array(
          'name'     => 'check_iftraffic41',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'inUsage',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'inpercentcurr',
          'dsname2'                        => 'inpercentwarn',
          'dsname3'                        => 'inpercentcrit'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outUsage',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 3,
          'dsname1'                        => 'outpercentcurr',
          'dsname2'                        => 'outpercentwarn',
          'dsname3'                        => 'outpercentcrit'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inBandwidth',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inbps'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outBandwidth',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outbps'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inAbsolut',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inbound'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outAbsolut',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 6,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outbound'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_iftraffic5
      $input = array(
          'name'     => 'check_iftraffic5',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'inUse',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inUse'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outUse',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outUse'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'Warn',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 1,
          'dsname1'                        => 'Warn'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'Crit',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 1,
          'dsname1'                        => 'Crit'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inBW',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inBW'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outBW',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 6,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outBW'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inUcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 7,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inUcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inMcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 8,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inMcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inBcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 9,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inBcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outUcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 10,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outUcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outMcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 11,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outMcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outBcast',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 12,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outBcast'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inDis',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 13,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inDis'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'inErr',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 14,
          'dsname_num'                     => 1,
          'dsname1'                        => 'inErr'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outDis',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 15,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outDis'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'outErr',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 16,
          'dsname_num'                     => 1,
          'dsname1'                        => 'outErr'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_pf
      $input = array(
          'name'     => 'check_pf',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'current',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'states_current',
          'dsname2'                        => 'states_warning',
          'dsname3'                        => 'states_critical'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'percent',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 1,
          'dsname1'                        => 'percent'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'limit',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 1,
          'dsname1'                        => 'limit'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_dig
      $input = array(
          'name'     => 'check_dig',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'time_current',
          'dsname2'                        => 'time_warning',
          'dsname3'                        => 'time_critical',
          'dsname4'                        => 'time_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_disk
      $input = array(
          'name'     => 'check_disk',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => '',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 5,
          'dsname1'                        => 'used',
          'dsname2'                        => 'used_warning',
          'dsname3'                        => 'used_critical',
          'dsname4'                        => 'used_other',
          'dsname5'                        => 'totalcapacity'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_dns
      $input = array(
          'name'     => 'check_dns',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'time_current',
          'dsname2'                        => 'time_warning',
          'dsname3'                        => 'time_critical',
          'dsname4'                        => 'time_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_http
      $input = array(
          'name'     => 'check_http',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'time_current',
          'dsname2'                        => 'time_warning',
          'dsname3'                        => 'time_critical',
          'dsname4'                        => 'time_other'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'size',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 4,
          'dsname1'                        => 'size_current',
          'dsname2'                        => 'size_warning',
          'dsname3'                        => 'size_critical',
          'dsname4'                        => 'size_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_pop
      $input = array(
          'name'     => 'check_pop',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 5,
          'dsname1'                        => 'time_current',
          'dsname2'                        => 'time_warning',
          'dsname3'                        => 'time_critical',
          'dsname4'                        => 'time_other',
          'dsname5'                        => 'time_timeout'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_smtp
      $input = array(
          'name'     => 'check_smtp',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'time_current',
          'dsname2'                        => 'time_warning',
          'dsname3'                        => 'time_critical',
          'dsname4'                        => 'time_other'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_mysql_health connection_time
      $input = array(
          'name'     => 'check_mysql_health connection_time',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'connection-time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'connection-time_current',
          'dsname2'                        => 'connection-time_warning',
          'dsname3'                        => 'connection-time_critical'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_mysql_health tmp_disk_tables
      $input = array(
          'name'     => 'check_mysql_health tmp_disk_tables',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'pct_tmp_table_on_disk',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'tmp_table_on_disk_current',
          'dsname2'                        => 'tmp_table_on_disk_warning',
          'dsname3'                        => 'tmp_table_on_disk_critical'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'pct_tmp_table_on_disk_now',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 3,
          'dsname1'                        => 'tmp_table_on_disk_now_current',
          'dsname2'                        => 'tmp_table_on_disk_now_warning',
          'dsname3'                        => 'tmp_table_on_disk_now_critical'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_mysql_health threads_connected
      $input = array(
          'name'     => 'check_mysql_health threads_connected',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'threads_connected',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'threads_connected_current',
          'dsname2'                        => 'threads_connected_warning',
          'dsname3'                        => 'threads_connected_critical'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_snmp_memory
      $input = array(
          'name'     => 'check_snmp_memory',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'total',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'memory_total',
          'dsname2'                        => 'memory_warning',
          'dsname3'                        => 'memory_critical',
          'dsname4'                        => 'memory_other'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'used',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 4,
          'dsname1'                        => 'memory_used',
          'dsname2'                        => 'memory_other1',
          'dsname3'                        => 'memory_other2',
          'dsname4'                        => 'memory_other3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'swap',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 4,
          'dsname1'                        => 'swap_used',
          'dsname2'                        => 'swap_other1',
          'dsname3'                        => 'swap_other2',
          'dsname4'                        => 'swap_other3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'buffer',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 4,
          'dsname1'                        => 'buffer_used',
          'dsname2'                        => 'buffer_other1',
          'dsname3'                        => 'buffer_other2',
          'dsname4'                        => 'buffer_other3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'cache',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 4,
          'dsname1'                        => 'cache_used',
          'dsname2'                        => 'cache_other1',
          'dsname3'                        => 'cache_other2',
          'dsname4'                        => 'cache_other3'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_snmp_load stand
      $input = array(
          'name'     => 'check_snmp_load stand',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'cpu_used',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'cpu_load',
          'dsname2'                        => 'cpu_warning',
          'dsname3'                        => 'cpu_critical'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_snmp_storage
      $input = array(
          'name'     => 'check_snmp_storage',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => '',
          'dynamic_name'                   => 1,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 5,
          'dsname1'                        => 'used',
          'dsname2'                        => 'warning',
          'dsname3'                        => 'critical',
          'dsname4'                        => 'other',
          'dsname5'                        => 'total'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_tcp
      $input = array(
          'name'     => 'check_tcp',
          'perfdata' => 'time=0.064284s;;;0.000000;10.000000'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 5,
          'dsname1'                        => 'response_time',
          'dsname2'                        => 'warning',
          'dsname3'                        => 'critical',
          'dsname4'                        => 'other',
          'dsname5'                        => 'timeout'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_iostat_bsd
      $input = array(
          'name'     => 'check_iostat_bsd',
          'perfdata' => 'tps=7.325;;; tpsr=3.175;;; tpsw=4.15;;; reads=55.95KB;;; writes=78.7KB;;; svc_t=0.85;;;'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'tps',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 3,
          'dsname1'                        => 'IOTPS_read_write',
          'dsname2'                        => 'value1.2',
          'dsname3'                        => 'value1.3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'tpsr',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 3,
          'dsname1'                        => 'IOTPS_read',
          'dsname2'                        => 'value2.2',
          'dsname3'                        => 'value2.3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'tpsw',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 3,
          'dsname1'                        => 'IOTPS_write',
          'dsname2'                        => 'value3.2',
          'dsname3'                        => 'value3.3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'reads',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 3,
          'dsname1'                        => 'Kbps_read',
          'dsname2'                        => 'value4.2',
          'dsname3'                        => 'value4.3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'writes',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 3,
          'dsname1'                        => 'Kbps_write',
          'dsname2'                        => 'value5.2',
          'dsname3'                        => 'value5.3'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'svc_t',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 6,
          'dsname_num'                     => 3,
          'dsname1'                        => 'transactiontime',
          'dsname2'                        => 'value6.2',
          'dsname3'                        => 'value6.3'
      );
      $pmPerfdataDetail->add($inputd);

      // * cucumber_nagios
      $input = array(
          'name'     => 'cucumber_nagios',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'passed',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 1,
          'dsname1'                        => 'passed'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'failed',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 1,
          'dsname1'                        => 'failed'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'nosteps',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 1,
          'dsname1'                        => 'nosteps'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'total',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 1,
          'dsname1'                        => 'total'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 1,
          'dsname1'                        => 'time'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_snmp_tcp
      $input = array(
          'name'     => 'check_snmp_tcp',
          'perfdata' => ''
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'time',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 5,
          'dsname1'                        => 'response_time',
          'dsname2'                        => 'warning',
          'dsname3'                        => 'critical',
          'dsname4'                        => 'other',
          'dsname5'                        => 'timeout'
      );
      $pmPerfdataDetail->add($inputd);

      // * check_nginx_status
      $input = array(
          'name'     => 'check_nginx_status',
          'perfdata' => 'Writing=1;;;; Reading=0;;;; Waiting=9;;;; Active=10;;;; ReqPerSec=1.964401;;;; ConnPerSec=0.190939;;;; ReqPerConn=8.167504;;;;'
      );
      $id = $pmPerfdata->add($input);

      $inputd = array(
          'name'                           => 'Writing',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 1,
          'dsname_num'                     => 4,
          'dsname1'                        => 'Writing',
          'dsname2'                        => 'value1.2',
          'dsname3'                        => 'value1.3',
          'dsname4'                        => 'value1.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'Reading',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 2,
          'dsname_num'                     => 4,
          'dsname1'                        => 'Reading',
          'dsname2'                        => 'value2.2',
          'dsname3'                        => 'value2.3',
          'dsname4'                        => 'value2.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'Waiting',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 3,
          'dsname_num'                     => 4,
          'dsname1'                        => 'Waiting',
          'dsname2'                        => 'value3.2',
          'dsname3'                        => 'value3.3',
          'dsname4'                        => 'value3.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'Active',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 4,
          'dsname_num'                     => 4,
          'dsname1'                        => 'Active',
          'dsname2'                        => 'value4.2',
          'dsname3'                        => 'value4.3',
          'dsname4'                        => 'value4.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'ReqPerSec',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 5,
          'dsname_num'                     => 4,
          'dsname1'                        => 'ReqPerSec',
          'dsname2'                        => 'value5.2',
          'dsname3'                        => 'value5.3',
          'dsname4'                        => 'value5.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'ConnPerSec',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 6,
          'dsname_num'                     => 4,
          'dsname1'                        => 'ConnPerSec',
          'dsname2'                        => 'value6.2',
          'dsname3'                        => 'value6.3',
          'dsname4'                        => 'value6.4'
      );
      $pmPerfdataDetail->add($inputd);

      $inputd = array(
          'name'                           => 'ReqPerConn',
          'dynamic_name'                   => 0,
          'plugin_monitoring_perfdatas_id' => $id,
          'position'                       => 7,
          'dsname_num'                     => 4,
          'dsname1'                        => 'ReqPerConn',
          'dsname2'                        => 'value7.2',
          'dsname3'                        => 'value7.3',
          'dsname4'                        => 'value7.4'
      );
      $pmPerfdataDetail->add($inputd);

   }



   /**
   * Display form for perfdata
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {

      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>";
      echo __('Name')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name", 1,
                             $this->getType());
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";
      // * perfdata
      echo "<td>".__('A perfdata for this check', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      echo "<input type='name' name='perfdata' value=\"".$this->fields['perfdata']."\" size='80'/>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      if ($this->fields['id'] > 0) {
         $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();
         $pmPerfdataDetail->showDetails($this->fields['id']);

         $pmPerfdataDetail->updateDetailForPerfdata($this->fields['perfdata'], $this->fields['id']);
      }

      return true;
   }



   function post_addItem() {

      if ($this->fields['perfdata'] != ''
              && !isset($_SESSION['plugin_monitoring_installation'])) {
       PluginMonitoringPerfdataDetail::updateDetailForPerfdata(
               Toolbox::stripslashes_deep($this->fields['perfdata']), $this->fields['id']);
      }
   }



   function post_updateItem($history=1) {
      if ($this->fields['perfdata'] != ''
              && !isset($_SESSION['plugin_monitoring_installation'])) {
         PluginMonitoringPerfdataDetail::updateDetailForPerfdata(
                 $this->fields['perfdata'], $this->fields['id']);
      }
   }



   function post_purgeItem() {
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();
      $a_perfdatas = $pmPerfdataDetail->find("`plugin_monitoring_perfdatas_id`='".$this->fields['id']."'");
      foreach ($a_perfdatas as $data) {
         $pmPerfdataDetail->delete($data);
      }
   }



   static function getArrayPerfdata($perfdatas_id) {

      $pmPerfdata       = new PluginMonitoringPerfdata();
      $pmPerfdataDetail = new PluginMonitoringPerfdataDetail();

      $data = array();
      $data['parseperfdata'] = array();
      if (!$pmPerfdata->getFromDB($perfdatas_id)) {
         $data['command'] = '';
         return $data;
      }

      $data['command'] = $pmPerfdata->fields['name'];

      $a_perfdatadetails = $pmPerfdataDetail->find("`plugin_monitoring_perfdatas_id`='".$perfdatas_id."'", "position");
      foreach ($a_perfdatadetails as $a_perfdatadetail) {
         $ds = array();
         $a_incremental = array();
         for ($i=1; $i<=$a_perfdatadetail['dsname_num']; $i++) {
            $ds[] = array('dsname' => $a_perfdatadetail['dsname'.$i]);
            $a_incremental[] = $a_perfdatadetail['dsnameincr'.$i];
         }
         $name = $a_perfdatadetail['name'];
         if ($a_perfdatadetail['dynamic_name']) {
            $name = "*";
         }
         $data['parseperfdata'][] = array('name'        => $name,
                                          'DS'          => $ds,
                                          'incremental' => $a_incremental);
      }
      return $data;
   }



   static function splitPerfdata($perfdata) {

      $a_perfdata = array();
      if (strstr($perfdata, "'")
              || (strstr($perfdata, '"'))) {

         preg_match_all("/[^ ?]([^\=]*\=[^ ]*)/", trim($perfdata), $a_perfdata);
         return $a_perfdata[0];
      } else {
         $a_perfdata = explode(" ", trim($perfdata));
         return $a_perfdata;
      }
   }
}
?>