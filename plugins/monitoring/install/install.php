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

function pluginMonitoringInstall($version) {
   global $DB,$CFG_GLPI;

   // ** Insert in DB
   $DB_file = GLPI_ROOT ."/plugins/monitoring/install/mysql/plugin_monitoring-empty.sql";
   $DBf_handle = fopen($DB_file, "rt");
   $sql_query = fread($DBf_handle, filesize($DB_file));
   fclose($DBf_handle);
   foreach ( explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) $sql_line=Toolbox::stripslashes_deep($sql_line);
      if (!empty($sql_line)) $DB->query($sql_line);
   }

   include (GLPI_ROOT . "/plugins/monitoring/inc/profile.class.php");
   $pmProfile = new PluginMonitoringProfile();
   $pmProfile->initProfile();
   include (GLPI_ROOT . "/plugins/monitoring/inc/command.class.php");
   $pmCommand = new PluginMonitoringCommand();
   $pmCommand->initCommands();
   include (GLPI_ROOT . "/plugins/monitoring/inc/notificationcommand.class.php");
   $pmNotificationcommand = new PluginMonitoringNotificationcommand();
   $pmNotificationcommand->initCommands();
   include (GLPI_ROOT . "/plugins/monitoring/inc/check.class.php");
   $pmCheck = new PluginMonitoringCheck();
   $pmCheck->initChecks();

   include (GLPI_ROOT . "/plugins/monitoring/inc/perfdata.class.php");
   include (GLPI_ROOT . "/plugins/monitoring/inc/perfdatadetail.class.php");
   PluginMonitoringPerfdata::initDB();

   include (GLPI_ROOT . "/plugins/monitoring/inc/hostconfig.class.php");
   $pmHostconfig = new PluginMonitoringHostconfig();
   $pmHostconfig->initConfig();

   include (GLPI_ROOT . "/plugins/monitoring/inc/config.class.php");
   $pmConfig = new PluginMonitoringConfig();
   $pmConfig->initConfig();
   $query = "UPDATE `glpi_plugin_monitoring_configs`
      SET `version`='".PLUGIN_MONITORING_VERSION."'
         WHERE `id`='1'";
   $DB->query($query);

   $query = "SELECT * FROM `glpi_calendars`
      WHERE `name`='24x7'
      LIMIT 1";
   $result=$DB->query($query);
   if ($DB->numrows($result) == 0) {
      $calendar = new Calendar();
      $input = array();
      $input['name'] = '24x7';
      $input['is_recursive'] = 1;
      $calendars_id = $calendar->add($input);

      $calendarSegment = new CalendarSegment();
      $input = array();
      $input['calendars_id'] = $calendars_id;
      $input['is_recursive'] = 1;
      $input['begin'] = '00:00:00';
      $input['end'] = '24:00:00';
      $input['day'] = '0';
      $calendarSegment->add($input);
      $input['day'] = '1';
      $calendarSegment->add($input);
      $input['day'] = '2';
      $calendarSegment->add($input);
      $input['day'] = '3';
      $calendarSegment->add($input);
      $input['day'] = '4';
      $calendarSegment->add($input);
      $input['day'] = '5';
      $calendarSegment->add($input);
      $input['day'] = '6';
      $calendarSegment->add($input);
   }

   // Fred
   $query = "SELECT * FROM `glpi_calendars`
      WHERE `name`='WorkingDays'
      LIMIT 1";
   $result=$DB->query($query);
   if ($DB->numrows($result) == 0) {
      $calendar = new Calendar();
      $input = array();
      $input['name'] = 'WorkingDays';
      $input['is_recursive'] = 1;
      $calendars_id = $calendar->add($input);

      $calendarSegment = new CalendarSegment();
      $input = array();
      $input['calendars_id'] = $calendars_id;
      $input['is_recursive'] = 1;
      $input['begin'] = '08:00:00';
      $input['end'] = '18:00:00';
      $input['day'] = '0';
      $calendarSegment->add($input);
      $input['day'] = '1';
      $calendarSegment->add($input);
      $input['day'] = '2';
      $calendarSegment->add($input);
      $input['day'] = '3';
      $calendarSegment->add($input);
      $input['day'] = '4';
      $calendarSegment->add($input);
      $input['day'] = '5';
      $calendarSegment->add($input);
      $input['day'] = '6';
      $calendarSegment->add($input);
   }

   // Add user monitoring if not defined
   if (!countElementsInTable('glpi_users', "`name`='monitoring'")) {
      // Create
      $input = array('name' => 'monitoring');
      $user = new User();
      $user->add($input);
   }


   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/templates')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/weathermapbg')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/weathermapbg");
   }

   CronTask::Register('PluginMonitoringDowntime', 'cronDowntimesExpired', '3600',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   CronTask::Register('PluginMonitoringLog', 'cleanlogs', '96400',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   CronTask::Register('PluginMonitoringUnavailability', 'unavailability', '300',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   CronTask::Register('PluginMonitoringDisplayview_rule', 'replayallviewrules', '1200',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));

}


function pluginMonitoringUninstall() {
   global $DB;

   if (file_exists(GLPI_PLUGIN_DOC_DIR.'/monitoring')) {
      require_once GLPI_ROOT . "/plugins/monitoring/inc/config.class.php";
      $pmConfig = new PluginMonitoringConfig();
      $pmConfig->rrmdir(GLPI_PLUGIN_DOC_DIR.'/monitoring');
   }

   $query = "SHOW TABLES;";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if (strstr($data[0],"glpi_plugin_monitoring_")) {
         $query_delete = "DROP TABLE `".$data[0]."`;";
         $DB->query($query_delete) or die($DB->error());
      }
   }
   return true;
}

?>