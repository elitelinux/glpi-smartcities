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

function pluginMonitoringGetCurrentVersion($version) {
   global $DB;

   if ((!TableExists("glpi_plugin_monitoring_configs"))) {
      return '0';
   } else if (!FieldExists("glpi_plugin_monitoring_configs", "timezones")) {
      // Version before 0.80+1.0 (test version)
      return "1.0.0";
   } else if (!FieldExists("glpi_plugin_monitoring_configs", "version")) {
      return "0.80+1.0";
   } else if (FieldExists("glpi_plugin_monitoring_configs", "version")) {
      $query = "SELECT `version`
          FROM `glpi_plugin_monitoring_configs`
          WHERE `id` = '1'";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $data = $DB->fetch_assoc($result);
         if (is_null($data['version'])
                 || $data['version'] == '') {
            $data['version'] = '0.80+1.0';
         }
         if ($data['version'] != $version) {
            return $data['version'];
         }
      }else {
         return "0.80+1.0";
      }
   } else {
      return $version;
   }
   return $version;
}



function pluginMonitoringUpdate($current_version, $migrationname='Migration') {
   global $DB;

   $migration = new $migrationname($current_version);

   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/templates')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/weathermapbg')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/weathermapbg");
   }

   $unavailability_recalculate = 0;
   if (!TableExists("glpi_plugin_monitoring_unavailabilities")
           || !FieldExists("glpi_plugin_monitoring_unavailabilities", "duration")) {
      $unavailability_recalculate = 1;
   }

   $unavailability_reset = 0;
   if (!TableExists("glpi_plugin_monitoring_unavailabilities")) {
      $unavailability_reset = 1;
   }

    /*
    * Table glpi_plugin_monitoring_servicescatalogs
    */
      $newTable = "glpi_plugin_monitoring_servicescatalogs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_recursive',
                                 'is_recursive',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'comment',
                                 'comment',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'last_check',
                                 'last_check',
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state',
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'state_type',
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_checks_id',
                                 'plugin_monitoring_checks_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'calendars_id',
                                 'calendars_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         $migration->addField($newTable,
                              'name',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_recursive',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'last_check',
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              'event',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'state',
                              "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'state_type',
                              "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_monitoring_checks_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'calendars_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledged',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledgeconfirmed',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'acknowledge_comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'acknowledge_users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'notification_interval',
                              "int(4) NOT NULL DEFAULT '30'");
         $migration->addField($newTable,
                              'business_priority',
                              "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                              'is_generic',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_monitoring_servicescatalogs_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "name");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_componentscatalogs
    */
      $newTable = "glpi_plugin_monitoring_componentscatalogs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_recursive',
                                 'is_recursive',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'comment',
                                 'comment',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'notification_interval',
                                 'notification_interval',
                                 "int(4) NOT NULL DEFAULT '30'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'name',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_recursive',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'notification_interval',
                              "int(4) NOT NULL DEFAULT '30'");
         $migration->addKey($newTable,
                            "name");
      $migration->migrationOneTable($newTable);


    /*
     * glpi_plugin_monitoring_perfdatas
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_perfdatas';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'         => array('type'    => 'autoincrement',    'value'   => ''),
         'name'       => array('type'    => 'string',           'value'   => NULL),
         'perfdata'   => array('type'    => 'text',             'value'   => NULL)
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);


    /*
     * glpi_plugin_monitoring_customitems_counters
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_customitems_counters';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'              => array('type'    => 'autoincrement', 'value'   => ''),
         'name'            => array('type'    => 'string',        'value'   => NULL),
         'entities_id'     => array('type'    => 'integer',       'value'   => NULL),
         'is_recursive'    => array('type'    => 'bool',          'value'   => NULL),
         'type'            => array('type'    => 'string',        'value'   => NULL),
         'aggregate_items' => array('type'    => 'text',          'value'   => NULL),
         'time'            => array('type'    => 'string',        'value'   => NULL),
         'time_specific'   => array('type'    => 'string',        'value'   => NULL)
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);


    /*
     * glpi_plugin_monitoring_customitems_gauges
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_customitems_gauges';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'              => array('type'    => 'autoincrement', 'value'   => ''),
         'name'            => array('type'    => 'string',        'value'   => NULL),
         'entities_id'     => array('type'    => 'integer',       'value'   => NULL),
         'is_recursive'    => array('type'    => 'bool',          'value'   => NULL),
         'type'            => array('type'    => 'string',        'value'   => NULL),
         'aggregate_items' => array('type'    => 'text',          'value'   => NULL),
         'aggregate_warn'  => array('type'    => 'text',          'value'   => NULL),
         'aggregate_crit'  => array('type'    => 'text',          'value'   => NULL),
         'aggregate_limit' => array('type'    => 'text',          'value'   => NULL),
         'time'            => array('type'    => 'string',        'value'   => NULL),
         'time_specific'   => array('type'    => 'string',        'value'   => NULL)
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);


    /*
     * glpi_plugin_monitoring_perfdatadetails
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_perfdatadetails';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement',  'value'   => ''),
         'name'         => array('type'    => 'string',         'value'   => NULL),
         'dynamic_name' => array('type'    => 'bool',           'value'   => NULL),
         'plugin_monitoring_perfdatas_id' => array('type'    => 'integer',    'value'   => NULL),
         'position'     => array('type'    => "int(2) NOT NULL DEFAULT '0'",  'value'   => NULL),
         'dsname_num'   => array('type'    => 'tinyint(1) NOT NULL DEFAULT "1"',           'value'   => NULL),
         'dsname1'      => array('type'    => 'string',         'value'   => NULL),
         'dsname2'      => array('type'    => 'string',         'value'   => NULL),
         'dsname3'      => array('type'    => 'string',         'value'   => NULL),
         'dsname4'      => array('type'    => 'string',         'value'   => NULL),
         'dsname5'      => array('type'    => 'string',         'value'   => NULL),
         'dsname6'      => array('type'    => 'string',         'value'   => NULL),
         'dsname7'      => array('type'    => 'string',         'value'   => NULL),
         'dsname8'      => array('type'    => 'string',         'value'   => NULL),
         'dsname9'      => array('type'    => 'string',         'value'   => NULL),
         'dsname10'     => array('type'    => 'string',         'value'   => NULL),
         'dsname11'     => array('type'    => 'string',         'value'   => NULL),
         'dsname12'     => array('type'    => 'string',         'value'   => NULL),
         'dsname13'     => array('type'    => 'string',         'value'   => NULL),
         'dsname14'     => array('type'    => 'string',         'value'   => NULL),
         'dsname15'     => array('type'    => 'string',         'value'   => NULL),
         'dsnameincr1'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr2'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr3'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr4'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr5'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr6'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr7'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr8'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr9'  => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr10' => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr11' => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr12' => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr13' => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr14' => array('type'    => 'bool',           'value'   => 0),
         'dsnameincr15' => array('type'    => 'bool',           'value'   => 0),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array(array('field' => 'plugin_monitoring_perfdatas_id', 'name' => '', 'type' => 'INDEX'));

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);

      if (countElementsInTable('glpi_plugin_monitoring_perfdatas') == 0) {
         include (GLPI_ROOT . "/plugins/monitoring/inc/perfdata.class.php");
         include (GLPI_ROOT . "/plugins/monitoring/inc/perfdatadetail.class.php");
         PluginMonitoringPerfdata::initDB();
      }


    /*
    * Table glpi_plugin_monitoring_components
    */
      if (TableExists('glpi_plugin_monitoring_components')) {
         $a_data = getAllDatasFromTable('glpi_plugin_monitoring_components');
         foreach ($a_data as $data) {
            if (!is_numeric($data['graph_template'])) {
               $a_perfs = getAllDatasFromTable('glpi_plugin_monitoring_perfdatas',
                       '`name`="'.$data['graph_template'].'"');
               if (count($a_perfs) == 0) {
                  $DB->query("UPDATE `glpi_plugin_monitoring_components`
                     SET `graph_template`='0'
                     WHERE `id`='".$data['id']."'");
               } else {
                  $a_perf = current($a_perfs);
                  $DB->query("UPDATE `glpi_plugin_monitoring_components`
                     SET `graph_template`='".$a_perf['id']."'
                     WHERE `id`='".$data['id']."'");
               }
            }
         }
      }

      $newTable = "glpi_plugin_monitoring_components";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         // 0.84+1.3
         $migration->changeField($newTable,
                                 'description',
                                 'description',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'plugin_monitoring_commands_id',
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'arguments',
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'plugin_monitoring_checks_id',
                                 'plugin_monitoring_checks_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'active_checks_enabled',
                                 'active_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'passive_checks_enabled',
                                 'passive_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'calendars_id',
                                 'calendars_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'remotesystem',
                                 'remotesystem',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'is_arguments',
                                 'is_arguments',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'alias_command',
                                 'alias_command',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'graph_template',
                                 'graph_template',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'link',
                                 'link',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'is_weathermap',
                                 'is_weathermap',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'weathermap_regex',
                                 'weathermap_regex_in',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'perfname',
                                 'perfname',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'perfnameinvert',
                                 'perfnameinvert',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'perfnamecolor',
                                 'perfnamecolor',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         // 0.84+1.3
         $migration->addField($newTable,
                                 'description',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'plugin_monitoring_checks_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'active_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'passive_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'calendars_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'remotesystem',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'is_arguments',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'alias_command',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'graph_template',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'link',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'is_weathermap',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'weathermap_regex_in',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'weathermap_regex_out',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'perfname',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'perfnameinvert',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'perfnamecolor',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'plugin_monitoring_eventhandlers_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'freshness_count',
                              "int(6) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'freshness_type',
                              "varchar(255) DEFAULT 'seconds'");

         $migration->addKey($newTable,
                            "plugin_monitoring_commands_id");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_componentscatalogs_components
    */
      $newTable = "glpi_plugin_monitoring_componentscatalogs_components";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_components_id',
                                 'plugin_monitoring_components_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'plugin_monitoring_components_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array('plugin_monitoring_componentscalalog_id',
                                  'plugin_monitoring_components_id'),
                            "unicity",
                            "UNIQUE");
         $migration->addKey($newTable,
                            "plugin_monitoring_componentscalalog_id");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_componentscatalogs_hosts
    */
      $newTable = "glpi_plugin_monitoring_componentscatalogs_hosts";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_static',
                                 'is_static',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'is_static',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->addKey($newTable,
                            array('itemtype','items_id'),
                            'itemtype');
         $migration->addKey($newTable,
                            'plugin_monitoring_componentscalalog_id');
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_componentscatalogs_rules
    */
      $newTable = "glpi_plugin_monitoring_componentscatalogs_rules";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'condition',
                                 'condition',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->dropField($newTable,
                               'entities_id');
         $migration->dropField($newTable,
                               'is_recursive');
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_componentscalalog_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'itemtype',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'condition',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            'plugin_monitoring_componentscalalog_id');
      $migration->migrationOneTable($newTable);

      $DB->query("UPDATE `glpi_plugin_monitoring_componentscatalogs_rules`
              SET `itemtype`='Computer' WHERE `itemtype`='PluginMonitoringComputer'");


    /*
    * Table glpi_plugin_monitoring_services
    */
      $newTable = "glpi_plugin_monitoring_services";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'plugin_monitoring_components_id',
                                 'plugin_monitoring_components_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_componentscatalogs_hosts_id',
                                 'plugin_monitoring_componentscatalogs_hosts_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         // Event should contain up to 4096 bytes (Nagios plugin specification)
         $migration->changeField($newTable,
                                 'event',
                                 'event',
                                 "varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL");
         // $migration->changeField($newTable,
                                 // 'event',
                                 // 'event',
                                 // "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state',
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state_type',
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'last_check',
                                 'last_check',
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 'arguments',
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'networkports_id',
                                 'networkports_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->dropField($newTable,
                               'alias_command');
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'plugin_monitoring_components_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'plugin_monitoring_componentscatalogs_hosts_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'event',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'last_check',
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'networkports_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledged',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledgeconfirmed',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'acknowledge_comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'acknowledge_users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array('state',
                                  'state_type'),
                            'state');
         $migration->addKey($newTable,
                            'plugin_monitoring_componentscatalogs_hosts_id');
         $migration->addKey($newTable,
                            'last_check');
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contacttemplates
    */
      $newTable = "glpi_plugin_monitoring_contacttemplates";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'is_default',
                                 'is_default',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'shinken_administrator',
                                 'shinken_administrator',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'shinken_can_submit_commands',
                                 'shinken_can_submit_commands',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notifications_enabled',
                                 'host_notifications_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notifications_enabled',
                                 'service_notifications_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notification_period',
                                 'service_notification_period',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notification_period',
                                 'host_notification_period',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'service_notification_options_w',
                                 'service_notification_options_w',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notification_options_u',
                                 'service_notification_options_u',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notification_options_c',
                                 'service_notification_options_c',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notification_options_r',
                                 'service_notification_options_r',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'service_notification_options_f',
                                 'service_notification_options_f',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'service_notification_options_n',
                                 'service_notification_options_n',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notification_options_d',
                                 'host_notification_options_d',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'host_notification_options_u',
                                 'host_notification_options_u',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'host_notification_options_r',
                                 'host_notification_options_r',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'host_notification_options_f',
                                 'host_notification_options_f',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notification_options_s',
                                 'host_notification_options_s',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notification_options_n',
                                 'host_notification_options_n',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'service_notification_commands',
                                 'service_notification_commands',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'host_notification_commands',
                                 'host_notification_commands',
                                 "int(11) NOT NULL DEFAULT '0'");
       $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'is_default',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'shinken_administrator',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'shinken_can_submit_commands',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notifications_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notifications_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notification_period',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notification_period',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'service_notification_options_w',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notification_options_u',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notification_options_c',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notification_options_r',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'service_notification_options_f',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'service_notification_options_n',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notification_options_d',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'host_notification_options_u',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'host_notification_options_r',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'host_notification_options_f',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notification_options_s',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notification_options_n',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'service_notification_commands',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'host_notification_commands',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contacts
    */
      $newTable = "glpi_plugin_monitoring_contacts";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'users_id',
                                 'users_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_contacttemplates_id',
                                 'plugin_monitoring_contacttemplates_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'users_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'plugin_monitoring_contacttemplates_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contacts_items
    */
      $newTable = "glpi_plugin_monitoring_contacts_items";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'users_id',
                                 'users_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'groups_id',
                                 'groups_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'users_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'groups_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_commandtemplates
    */
      $newTable = "glpi_plugin_monitoring_commandtemplates";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_commands_id',
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'key',
                                 'key',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'key',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_rrdtooltemplates
    */
      $newTable = "glpi_plugin_monitoring_rrdtooltemplates";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_commands_id',
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'key',
                                 'key',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'key',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_configs
    */
      $newTable = "glpi_plugin_monitoring_configs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'timezones',
                                 'timezones',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[\"0\"]'");
         $migration->changeField($newTable,
                                 'version',
                                 'version',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'logretention',
                                 'logretention',
                                 "int(5) NOT NULL DEFAULT '30'");
         $migration->changeField($newTable,
                                 'nrpe_prefix_contener',
                                 'nrpe_prefix_contener',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'append_id_hostname',
                                 'append_id_hostname',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->dropField($newTable,
                              'phppath');
         $migration->dropField($newTable,
                              'rrdtoolpath');
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'timezones',
                              "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[\"0\"]'");
         $migration->addField($newTable,
                              'version',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'logretention',
                              "int(5) NOT NULL DEFAULT '30'");
         $migration->addField($newTable,
                              'extradebug',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'nrpe_prefix_contener',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'append_id_hostname',
                              "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);




    /*
    * Table glpi_plugin_monitoring_displayviews
    */
      $newTable = "glpi_plugin_monitoring_displayviews";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_recursive',
                                 'is_recursive',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'is_active',
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'users_id',
                                 'users_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'counter',
                                 'counter',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'in_central',
                                 'in_central',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'width',
                                 'width',
                                 "int(5) NOT NULL DEFAULT '950'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'name',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_recursive',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_active',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'counter',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'in_central',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'width',
                              "int(5) NOT NULL DEFAULT '950'");
         $migration->addField($newTable,
                              'is_frontview',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_displayviews_groups
    */
      $newTable = "glpi_plugin_monitoring_displayviews_groups";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->addField($newTable,
                              'pluginmonitoringdisplayviews_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'groups_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_recursive',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "pluginmonitoringdisplayviews_id");
         $migration->addKey($newTable,
                            "groups_id");
         $migration->addKey($newTable,
                            "entities_id");
         $migration->addKey($newTable,
                            "is_recursive");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_displayviews_users
    */
      $newTable = "glpi_plugin_monitoring_displayviews_users";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->addField($newTable,
                              'pluginmonitoringdisplayviews_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "pluginmonitoringdisplayviews_id");
         $migration->addKey($newTable,
                            "users_id");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_displayviews_items
    */
      $newTable = "glpi_plugin_monitoring_displayviews_items";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_displayviews_id',
                                 'plugin_monitoring_displayviews_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'x',
                                 'x',
                                 "int(5) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'y',
                                 'y',
                                 "int(5) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'extra_infos',
                                 'extra_infos',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'plugin_monitoring_displayviews_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'x',
                              "int(5) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'y',
                              "int(5) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'items_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'extra_infos',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'is_minemap',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "plugin_monitoring_displayviews_id");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_displayviews_rules
    */
      $newTable = "glpi_plugin_monitoring_displayviews_rules";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'plugin_monitoring_displayviews_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'name',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'type',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'condition',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            'plugin_monitoring_displayviews_id');
      $migration->migrationOneTable($newTable);



   /*
    * Table glpi_plugin_monitoring_entities
    */
      $newTable = "glpi_plugin_monitoring_entities";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'tag',
                                 'tag',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'entities_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'tag',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addKey($newTable,
                            "entities_id");
         $migration->addKey($newTable,
                            "tag");
      $migration->migrationOneTable($newTable);


    /*
    * Table glpi_plugin_monitoring_hostaddresses
    */
      $newTable = "glpi_plugin_monitoring_hostaddresses";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'networkports_id',
                                 'networkports_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'ipaddresses_id',
                                 'ipaddresses_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                                 'networkports_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'ipaddresses_id',
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_hostconfigs
    */
      $a_hostsconfig = array();
      if (TableExists('glpi_plugin_monitoring_hostconfigs')
              && (FieldExists('glpi_plugin_monitoring_hostconfigs', 'plugin_monitoring_commands_id'))) {
         $a_hostsconfig = getAllDatasFromTable('glpi_plugin_monitoring_hostconfigs');
      }

      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_hostconfigs';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type' => 'autoincrement', 'value'   => ''),
         'items_id'     => array('type' => 'integer',       'value'   => NULL),
         'itemtype'     => array('type' => 'varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL',
                                 'value'   => NULL),
         'plugin_monitoring_components_id' =>
                           array('type' => 'integer',       'value'   => NULL),
         'plugin_monitoring_realms_id' =>
                           array('type' => 'integer',       'value'   => NULL),
         'computers_id' => array('type' => 'integer',       'value'   => NULL),
         'jetlag'       => array('type' => 'varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL',
                                 'value'   => '0'),
      );

      $a_table['oldfields']  = array(
          'plugin_monitoring_commands_id',
          'plugin_monitoring_checks_id',
          'calendars_id'
      );

      $a_table['renamefields'] = array();

      $a_table['keys'] = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);

      if (count($a_hostsconfig) > 0) {
         // Convert commands by components
         foreach ($a_hostsconfig as $data) {
            $a_components = getAllDatasFromTable(
                    'glpi_plugin_monitoring_components',
                    "`plugin_monitoring_commands_id`='".$data['plugin_monitoring_commands_id']."'"
                    );
            if (count($a_components) > 0) {
               $a_component = current($a_components);
               $DB->query("UPDATE `".$a_table['name']."`
                  SET `plugin_monitoring_components_id`='".$a_component['id']."'
                  WHERE `id`='".$data['id']."'");
            }
         }
      }



    /*
    * Table glpi_plugin_monitoring_hosts
    */
      $newTable = "glpi_plugin_monitoring_hosts";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
      if (TableExists($newTable)) {
         $a_hosts = getAllDatasFromTable($newTable,
                    "`itemtype`='Computer'");
      }
         // Duplicate field with event
         $migration->dropField($newTable,
                                 'ouput');
      $migration->migrationOneTable($newTable);

         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'entities_id',
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         // Event should contain up to 4096 bytes (Nagios plugin specification)
         $migration->changeField($newTable,
                                 'event',
                                 'event',
                                 "varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state',
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state_type',
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'last_check',
                                 'last_check',
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 'dependencies',
                                 'dependencies',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'perf_data',
                                 'perf_data',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         // Duplicate field with event
         // $migration->changeField($newTable,
                                 // 'output',
                                 // 'output',
                                 // "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'latency',
                                 'latency',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'execution_time',
                                 'execution_time',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");

      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'entities_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledged',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'is_acknowledgeconfirmed',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'acknowledge_comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'acknowledge_users_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'items_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'event',
                              "varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'state',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'state_type',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'last_check',
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              'dependencies',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'perf_data',
                              "text COLLATE utf8_unicode_ci DEFAULT NULL");
         // Duplicate field with event
         // $migration->addField($newTable,
                              // 'output',
                              // "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'latency',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'execution_time',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addKey($newTable,
                            array('itemtype','items_id'),
                            'itemtype');
      $migration->migrationOneTable($newTable);

      if (count($a_hosts) > 0) {
         // Convert commands by components
         foreach ($a_hosts as $data) {
            $a_computers = getAllDatasFromTable(
                    'glpi_computers',
                    "`id`='".$data['items_id']."'"
                    );
            if (count($a_computers) > 0) {
               $a_computer = current($a_computers);
               $DB->query("UPDATE `".$newTable."`
                  SET `entities_id`='".$a_computer['entities_id']."'
                  WHERE `id`='".$data['id']."'");
            }
         }
      }



    /*
    * Table glpi_plugin_monitoring_logs
    */
      $newTable = "glpi_plugin_monitoring_logs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` bigint(30) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "bigint(30) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'date_mod',
                                 'date_mod',
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 'user_name',
                                 'user_name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'action',
                                 'action',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'value',
                                 'value',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'date_mod',
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              'user_name',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'items_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'action',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'value',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_networkports
    */
      $newTable = "glpi_plugin_monitoring_networkports";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'networkports_id',
                                 'networkports_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'items_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'networkports_id',
                              "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_realms
    */
      $newTable = "glpi_plugin_monitoring_realms";
      $insertrealm = 0;
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
         $insertrealm = 1;
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'comment',
                                 'comment',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'date_mod',
                                 'date_mod',
                                 "datetime DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'name',
                              "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                              'comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                              'date_mod',
                              "datetime DEFAULT NULL");
      $migration->migrationOneTable($newTable);
      if ($insertrealm == '1') {
         $query = "INSERT INTO `glpi_plugin_monitoring_realms`
            (`id` ,`name` ,`comment` ,`date_mod`) VALUES (NULL , 'All', NULL , NULL)";
         $DB->query($query);
      }



    /*
    * Table glpi_plugin_monitoring_serviceevents
    */
      $newTable = "glpi_plugin_monitoring_serviceevents";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` bigint(30) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         // Duplicate field with event
         $migration->dropField($newTable,
                                 'ouput');
      $migration->migrationOneTable($newTable);

         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "bigint(30) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_services_id',
                                 'plugin_monitoring_services_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'date',
                                 'date',
                                 "datetime DEFAULT NULL");
         // Event should contain up to 4096 bytes (Nagios plugin specification)
         $migration->changeField($newTable,
                                 'event',
                                 'event',
                                 "varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'perf_data',
                                 'perf_data',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         // Duplicate field with event
         // $migration->changeField($newTable,
                                 // 'output',
                                 // 'output',
                                 // "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'state',
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'state_type',
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'latency',
                                 'latency',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'execution_time',
                                 'execution_time',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'unavailability',
                                 'unavailability',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'plugin_monitoring_services_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'date',
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 'event',
                                 "varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'perf_data',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         // Duplicate field with event
         // $migration->addField($newTable,
                                 // 'output',
                                 // "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'state',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'state_type',
                                 "varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'latency',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'execution_time',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'unavailability',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            "plugin_monitoring_services_id");
         $migration->addKey($newTable,
                            array('plugin_monitoring_services_id',
                                  'date'),
                            "plugin_monitoring_services_id_2");
         $migration->addKey($newTable,
                            array('unavailability',
                                  'state_type',
                                  'plugin_monitoring_services_id'),
                            "unavailability");
         $migration->addKey($newTable,
                            array('plugin_monitoring_services_id',
                                  'id'),
                            "plugin_monitoring_services_id_3");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_commands
    */
      $newTable = "glpi_plugin_monitoring_commands";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'is_active',
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_name',
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_line',
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'poller_tag',
                                 'poller_tag',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'module_type',
                                 'module_type',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'arguments',
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->dropField($newTable,
                                 'regex');
         $migration->dropField($newTable,
                                 'legend');
         $migration->dropField($newTable,
                                 'unit');
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'poller_tag',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'module_type',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            "name");
         $migration->addKey($newTable,
                            "command_name");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_checks
    */
      $newTable = "glpi_plugin_monitoring_checks";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'max_check_attempts',
                                 'max_check_attempts',
                                 "int(2) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'check_interval',
                                 'check_interval',
                                 "int(5) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'retry_interval',
                                 'retry_interval',
                                 "int(5) NOT NULL DEFAULT '1'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'max_check_attempts',
                                 "int(2) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'check_interval',
                                 "int(5) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'retry_interval',
                                 "int(5) NOT NULL DEFAULT '1'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_businessrules
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_businessrules';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement',  'value'   => ''),
         'plugin_monitoring_businessrulegroups_id'
                        => array('type'    => 'integer',        'value'   => 0),
         'plugin_monitoring_services_id'
                        => array('type'    => 'integer',        'value'   => 0),
         'is_dynamic'
                        => array('type'    => 'bool',           'value'   => 0),
         'is_generic'
                        => array('type'    => 'bool',           'value'   => 0),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
     * glpi_plugin_monitoring_businessrules_components
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_businessrules_components';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement',  'value'   => ''),
         'plugin_monitoring_businessrulegroups_id'
                        => array('type'    => 'integer',        'value'   => 0),
         'plugin_monitoring_componentscatalogs_components_id'
                        => array('type'    => 'integer',        'value'   => 0),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);


    /*
    * Table glpi_plugin_monitoring_businessrulegroups
    */
      $newTable = "glpi_plugin_monitoring_businessrulegroups";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'plugin_monitoring_servicescatalogs_id',
                                 'plugin_monitoring_servicescatalogs_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'operator',
                                 'operator',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'plugin_monitoring_servicescatalogs_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'operator',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_eventhandlers
    */
      $newTable = "glpi_plugin_monitoring_eventhandlers";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'is_active',
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_name',
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_line',
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            "name");
         $migration->addKey($newTable,
                            "command_name");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_notificationcommands
    */
      $newTable = "glpi_plugin_monitoring_notificationcommands";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'is_active',
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_name',
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'command_line',
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'reactionner_tag',
                                 'reactionner_tag',
                                 "varchar(255) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'module_type',
                                 'module_type',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'is_active',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_name',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'command_line',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'reactionner_tag',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'module_type',
                                 "varchar(255) DEFAULT NULL");
         $migration->addKey($newTable,
                            "name");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contactgroups
    */
      $newTable = "glpi_plugin_monitoring_contactgroups";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contacts_contactgroups
    */
      $newTable = "glpi_plugin_monitoring_contacts_contactgroups";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_contacts_id',
                                 'plugin_monitoring_contacts_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_contactgroups_id',
                                 'plugin_monitoring_contactgroups_id',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         $migration->addField($newTable,
                                 'plugin_monitoring_contacts_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'plugin_monitoring_contactgroups_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array('plugin_monitoring_contacts_id',
                                  'plugin_monitoring_contactgroups_id'),
                            "unicity",
                            "UNIQUE");
         $migration->addKey($newTable,
                            "plugin_monitoring_contactgroups_id");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_contactgroups_contactgroups
    */
      $newTable = "glpi_plugin_monitoring_contactgroups_contactgroups";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_contactgroups_id_1',
                                 'plugin_monitoring_contactgroups_id_1',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_contactgroups_id_2',
                                 'plugin_monitoring_contactgroups_id_2',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         $migration->addField($newTable,
                                 'plugin_monitoring_contactgroups_id_1',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'plugin_monitoring_contactgroups_id_2',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array('plugin_monitoring_contactgroups_id_1',
                                  'plugin_monitoring_contactgroups_id_2'),
                            "unicity",
                            "UNIQUE");
         $migration->addKey($newTable,
                            "plugin_monitoring_contactgroups_id_2");
      $migration->migrationOneTable($newTable);



    /*
     * glpi_plugin_monitoring_profiles
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_profiles';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'profiles_id'     => array('type'    => 'integer',       'value'   => NULL),
         'config'             => array('type'    => 'char',          'value'   => NULL),
         'config_views'       => array('type'    => 'char',          'value'   => NULL),
         'config_sliders'     => array('type'    => 'char',          'value'   => NULL),
         'config_services_catalogs'   => array('type'    => 'char',          'value'   => NULL),
         'config_components_catalogs' => array('type'    => 'char',          'value'   => NULL),
         'config_weathermap'  => array('type'    => 'char',          'value'   => NULL),
         'dashboard'          => array('type'    => 'char',          'value'   => NULL),
         'dashboard_system_status'    => array('type'    => 'char',          'value'   => NULL),
         'dashboard_hosts_status'     => array('type'    => 'char',          'value'   => NULL),
         'dashboard_all_ressources'   => array('type'    => 'char',          'value'   => NULL),
         'dashboard_views'    => array('type'    => 'char',          'value'   => NULL),
         'dashboard_sliders'  => array('type'    => 'char',          'value'   => NULL),
         'dashboard_services_catalogs'   => array('type'    => 'char',          'value'   => NULL),
         'dashboard_components_catalogs' => array('type'    => 'char',          'value'   => NULL),
         'dashboard_perfdatas'        => array('type'    => 'char',          'value'   => NULL),
         'homepage'           => array('type'    => 'char',          'value'   => NULL),
         'homepage_views'     => array('type'    => 'char',          'value'   => NULL),
         'homepage_services_catalogs'   => array('type'    => 'char',          'value'   => NULL),
         'homepage_components_catalogs' => array('type'    => 'char',          'value'   => NULL),
         'homepage_system_status'       => array('type'    => 'char',          'value'   => NULL),
         'homepage_hosts_status'        => array('type'    => 'char',          'value'   => NULL),
         'homepage_perfdatas'           => array('type'    => 'char',          'value'   => NULL),
         'homepage_all_ressources'      => array('type'    => 'char',          'value'   => NULL),
         'acknowledge'        => array('type'    => 'char',          'value'   => NULL),
         'downtime'           => array('type'    => 'char',          'value'   => NULL),
         'counters'           => array('type'    => 'char',          'value'   => NULL),
         'restartshinken'     => array('type'    => 'char',          'value'   => NULL),
         'host_command'       => array('type'    => 'char',          'value'   => NULL),
      );

      $a_table['oldfields']  = array(
          'servicescatalog',
          'view',
          'componentscatalog',
          'viewshomepage',
          'weathermap',
          'component',
          'command',
          'check',
          'allressources');

      $a_table['renamefields'] = array();

      $a_table['keys']   = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_servicedefs
    */
      $newTable = "glpi_plugin_monitoring_servicedefs";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'plugin_monitoring_commands_id',
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'arguments',
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'plugin_monitoring_checks_id',
                                 'plugin_monitoring_checks_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'active_checks_enabled',
                                 'active_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'passive_checks_enabled',
                                 'passive_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->changeField($newTable,
                                 'calendars_id',
                                 'calendars_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'remotesystem',
                                 'remotesystem',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'is_arguments',
                                 'is_arguments',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'alias_command',
                                 'alias_command',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->changeField($newTable,
                                 'aliasperfdata_commands_id',
                                 'aliasperfdata_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'link',
                                 'link',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'plugin_monitoring_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'arguments',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'plugin_monitoring_checks_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'active_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'passive_checks_enabled',
                                 "tinyint(1) NOT NULL DEFAULT '1'");
         $migration->addField($newTable,
                                 'calendars_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'remotesystem',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                                 'is_arguments',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'alias_command',
                                 "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addField($newTable,
                                 'aliasperfdata_commands_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'link',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_sliders
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_sliders';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement', 'value'   => ''),
         'name'         => array('type'    => 'string',        'value'   => NULL),
         'entities_id'  => array('type'    => 'integer',       'value'   => NULL),
         'is_recursive' => array('type'    => 'bool',          'value'   => NULL),
         'is_active'    => array('type'    => 'bool',          'value'   => NULL),
         'users_id'     => array('type'    => 'integer',       'value'   => NULL),
         'in_central'   => array('type'    => 'bool',          'value'   => NULL),
         'is_frontview' => array('type'    => 'bool',          'value'   => NULL),
         'comment'      => array('type'    => 'text',          'value'   => NULL),
         'duration'     => array('type'    => 'integer',       'value'   => '2'),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_sliders_groups
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_sliders_groups';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement', 'value'   => ''),
         'pluginmonitoringsliders_id'  => array('type' => 'integer', 'value' => NULL),
         'groups_id'    => array('type'    => 'integer',       'value'   => NULL),
         'entities_id'  => array('type'    => 'integer',       'value'   => '-1'),
         'is_recursive' => array('type'    => 'bool',          'value'   => NULL),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array(
          array(
              'field' => 'pluginmonitoringsliders_id',
              'name' => '',
              'type' => 'INDEX'),
          array(
              'field' => 'groups_id',
              'name' => '',
              'type' => 'INDEX'),
          array(
              'field' => 'entities_id',
              'name' => '',
              'type' => 'INDEX'),
          array(
              'field' => 'is_recursive',
              'name' => '',
              'type' => 'INDEX'));

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_sliders_users
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_sliders_users';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement', 'value'   => ''),
         'pluginmonitoringsliders_id'  => array('type' => 'integer', 'value' => NULL),
         'users_id'    => array('type'    => 'integer',       'value'   => NULL),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array(
          array(
              'field' => 'pluginmonitoringsliders_id',
              'name' => '',
              'type' => 'INDEX'),
          array(
              'field' => 'users_id',
              'name' => '',
              'type' => 'INDEX'));

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_sliders_items
    */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_sliders_items';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'          => array('type' => 'autoincrement', 'value'   => ''),
         'plugin_monitoring_sliders_id' => array('type' => 'integer', 'value' => NULL),
         'items_id'    => array('type' => 'integer',       'value'   => NULL),
         'itemtype'    => array('type' => 'varchar(100) DEFAULT NULL','value'   => NULL),
         'extra_infos'    => array('type' => 'string',        'value'   => NULL),
         'is_minemap'  => array('type' => 'bool',          'value'   => NULL),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array(
          array(
              'field' => 'plugin_monitoring_sliders_id',
              'name' => '',
              'type' => 'INDEX'));

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_unavaibilities
    */
      $newTable = "glpi_plugin_monitoring_unavailabilities";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);

         $migration->addField($newTable,
                              'plugin_monitoring_services_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'begin_date',
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              'end_date',
                              "datetime DEFAULT NULL");
         $migration->addField($newTable,
                              'duration',
                              "int(15) NOT NULL DEFAULT '0'");
         // Scheduled unavailability
         $migration->addField($newTable,
                              'scheduled',
                              "tinyint(1) NOT NULL DEFAULT '0'");
         // Unavailability details
         $migration->addField($newTable,
                              'details',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
         $migration->addKey($newTable,
                            "plugin_monitoring_services_id");
         $migration->migrationOneTable($newTable);
      }
/*
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         if (!FieldExists($newTable, "plugin_monitoring_services_id")) {
            $migration->changeField($newTable,
                                    'items_id',
                                    'plugin_monitoring_services_id',
                                    "int(11) NOT NULL DEFAULT '0'");
         }
      $migration->migrationOneTable($newTable);
         $migration->changeField($newTable,
                                 'plugin_monitoring_services_id',
                                 'plugin_monitoring_services_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'begin_date',
                                 'begin_date',
                                 "datetime DEFAULT NULL");
         $migration->changeField($newTable,
                                 'end_date',
                                 'end_date',
                                 "datetime DEFAULT NULL");
         $migration->dropField($newTable,
                                 'itemtype');
      $migration->migrationOneTable($newTable);
*/


    /*
     * glpi_plugin_monitoring_unavailabilitystates
     */
      $a_table = array();
      $a_table['name'] = 'glpi_plugin_monitoring_unavailabilitystates';
      $a_table['oldname'] = array();

      $a_table['fields']  = array(
         'id'           => array('type'    => 'autoincrement',  'value'   => ''),
         'plugin_monitoring_services_id'
                        => array('type'    => 'integer',        'value'   => 0),
         'plugin_monitoring_serviceevents_id'
                        => array('type'    => 'integer',        'value'   => 0),
      );

      $a_table['oldfields']  = array();

      $a_table['renamefields'] = array();

      $a_table['keys'] = array();

      $a_table['oldkeys'] = array();

      migrateTablesMonitoring($migration, $a_table);



    /*
    * Table glpi_plugin_monitoring_weathermaps
    */
      $newTable = "glpi_plugin_monitoring_weathermaps";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'width',
                                 'width',
                                 "smallint(6) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'height',
                                 'height',
                                 "smallint(6) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'background',
                                 'background',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'name',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'width',
                              "smallint(6) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'height',
                              "smallint(6) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'background',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_weathermapnodes
    */
      $newTable = "glpi_plugin_monitoring_weathermapnodes";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'name',
                                 'name',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'plugin_monitoring_weathermaps_id',
                                 'plugin_monitoring_weathermaps_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'items_id',
                                 'items_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'itemtype',
                                 'itemtype',
                                 "varchar(100) DEFAULT NULL");
         $migration->changeField($newTable,
                                 'x',
                                 'x',
                                 "smallint(6) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'y',
                                 'y',
                                 "smallint(6) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'name',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'plugin_monitoring_weathermaps_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'items_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'itemtype',
                              "varchar(100) DEFAULT NULL");
         $migration->addField($newTable,
                              'x',
                              "smallint(6) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'y',
                              "smallint(6) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'position',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT 'middle'");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_weathermaplinks
    */
      $newTable = "glpi_plugin_monitoring_weathermaplinks";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->changeField($newTable,
                                 'id',
                                 'id',
                                 "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($newTable,
                                 'plugin_monitoring_weathermapnodes_id_1',
                                 'plugin_monitoring_weathermapnodes_id_1',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_weathermapnodes_id_2',
                                 'plugin_monitoring_weathermapnodes_id_2',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'plugin_monitoring_services_id',
                                 'plugin_monitoring_services_id',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->changeField($newTable,
                                 'bandwidth_in',
                                 'bandwidth_in',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->changeField($newTable,
                                 'bandwidth_out',
                                 'bandwidth_out',
                                 "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);
         $migration->addField($newTable,
                              'plugin_monitoring_weathermapnodes_id_1',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_monitoring_weathermapnodes_id_2',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_monitoring_services_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'bandwidth_in',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'bandwidth_out',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_shinkenwebservices
    */
      $newTable = "glpi_plugin_monitoring_shinkenwebservices";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->addField($newTable,
                              'url',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'action',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'cnt',
                              "tinyint(2) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'fields_string',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);



    /*
    * Table glpi_plugin_monitoring_tags
    */
      $newTable = "glpi_plugin_monitoring_tags";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `".$newTable."` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        PRIMARY KEY (`id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query);
      }
         $migration->addField($newTable,
                              'tag',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'ip',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'iplock',
                              "bool");
         $migration->addField($newTable,
                              'username',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'password',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($newTable,
                              'port',
                              "varchar(255) COLLATE utf8_unicode_ci DEFAULT '7760'");
         $migration->addField($newTable,
                              'comment',
                              "text DEFAULT NULL COLLATE utf8_unicode_ci");
      $migration->migrationOneTable($newTable);


    /*
    * Table glpi_plugin_monitoring_downtimes
    */
      $newTable = "glpi_plugin_monitoring_downtimes";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `$newTable` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `plugin_monitoring_hosts_id` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `plugin_monitoring_hosts_id` (`plugin_monitoring_hosts_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
         $migration->addField($newTable,
                                 'start_time',
                                 "datetime NOT NULL DEFAULT '2014-01-01 00:00:00'");
         $migration->addField($newTable,
                                 'end_time',
                                 "datetime NOT NULL DEFAULT '2014-01-01 00:00:00'");
         $migration->addField($newTable,
                                 'flexible',
                                 "tinyint(1) DEFAULT '1'");
         $migration->addField($newTable,
                                 'duration',
                                 "int(1) DEFAULT '24'");
         $migration->addField($newTable,
                                 'duration_type',
                                 "varchar(64) DEFAULT 'days'");
         $migration->addField($newTable,
                                 'comment',
                                 "varchar(1024) DEFAULT NULL");
         $migration->addField($newTable,
                                 'users_id',
                                 "int(11) DEFAULT '-1'");
         $migration->addField($newTable,
                                 'tickets_id',
                                 "int(11) DEFAULT '0'");
         $migration->addField($newTable,
                                 'services',
                                 "tinyint(1) DEFAULT '0'");
         $migration->addField($newTable,
                                 'notified',
                                 "tinyint(1) DEFAULT '0'");
         $migration->addField($newTable,
                                 'expired',
                                 "tinyint(1) DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         $migration->changeField($newTable,
                                 'flexible',
                                 'flexible',
                                 "tinyint(1) DEFAULT '0'");
         $migration->changeField($newTable,
                                 'notified',
                                 'notified',
                                 "tinyint(1) DEFAULT '0'");
         $migration->changeField($newTable,
                                 'expired',
                                 'expired',
                                 "tinyint(1) DEFAULT '0'");
      $migration->migrationOneTable($newTable);


    /*
    * Table glpi_plugin_monitoring_acknowledges
    */
      $newTable = "glpi_plugin_monitoring_acknowledges";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `$newTable` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `itemtype` varchar(100) DEFAULT 'Host',
                    `items_id` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`),
                    KEY `itemtype` (`itemtype`,`items_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
         $migration->addField($newTable,
                                 'start_time',
                                 "datetime NOT NULL DEFAULT '2014-01-01 00:00:00'");
         $migration->addField($newTable,
                                 'end_time',
                                 "datetime NOT NULL DEFAULT '2014-01-01 00:00:00'");
         $migration->addField($newTable,
                                 'sticky',
                                 "tinyint(1) DEFAULT '1'");
         $migration->addField($newTable,
                                 'persistent',
                                 "tinyint(1) DEFAULT '1'");
         $migration->addField($newTable,
                                 'notify',
                                 "tinyint(1) DEFAULT '1'");
         $migration->addField($newTable,
                                 'comment',
                                 "varchar(1024) DEFAULT NULL");
         $migration->addField($newTable,
                                 'users_id',
                                 "int(11) DEFAULT '-1'");
         $migration->addField($newTable,
                                 'notified',
                                 "tinyint(1) DEFAULT '0'");
         $migration->addField($newTable,
                                 'expired',
                                 "tinyint(1) DEFAULT '0'");
      $migration->migrationOneTable($newTable);

         $migration->changeField($newTable,
                                 'expired',
                                 'expired',
                                 "tinyint(1) DEFAULT '0'");
      $migration->migrationOneTable($newTable);

    /*
    * Table glpi_plugin_monitoring_hostcounters
    */
      $newTable = "glpi_plugin_monitoring_hostcounters";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `$newTable` (
                     `id` INT(11) NOT NULL AUTO_INCREMENT,
                     `hostname` VARCHAR(255) DEFAULT NULL,
                     PRIMARY KEY (`id`),
                     KEY `hostname` (`hostname`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
         $migration->addField($newTable,
                                 'date',
                                 "datetime DEFAULT NULL");
         $migration->addField($newTable,
                                 'counter',
                                 "varchar(255) DEFAULT NULL");
         $migration->addField($newTable,
                                 'value',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'updated',
                                 "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addKey($newTable,
                            array('hostname','date','updated'),
                            'updated');
      $migration->migrationOneTable($newTable);


    /*
    * Table glpi_plugin_monitoring_hostdailycounters
    */
      $newTable = "glpi_plugin_monitoring_hostdailycounters";
      if (!TableExists($newTable)) {
         $query = "CREATE TABLE `$newTable` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `hostname` VARCHAR(255) NOT NULL DEFAULT '',
                    `day` DATE NOT NULL DEFAULT '2013-01-01',
                    `dayname` varchar(16) NOT NULL DEFAULT '',
                     PRIMARY KEY (`id`),
                     KEY (`hostname`,`day`),
                     KEY (`hostname`,`dayname`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
/*
         $migration->addField($newTable,
                              'plugin_monitoring_services_id',
                              "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                              'plugin_monitoring_services_id2',
                              "int(11) NOT NULL DEFAULT '0'");
*/
         $migration->addField($newTable,
                                 'counters',
                                 "varchar(4096) NOT NULL DEFAULT ''");
         $migration->dropField($newTable,
                              'plugin_monitoring_services_id');
         $migration->dropField($newTable,
                              'plugin_monitoring_services_id2');
         $migration->addField($newTable,
                                 'dayname',
                                 "varchar(16) NOT NULL DEFAULT ''");
         $migration->addField($newTable,
                                 'cPaperChanged',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPrinterChanged',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cBinEmptied',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPagesInitial',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPagesTotal',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPagesToday',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPagesRemaining',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cRetractedInitial',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cRetractedTotal',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cRetractedToday',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cRetractedRemaining',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cPaperLoad',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->dropField($newTable,
                               'cCardsInsertedOk');
         $migration->dropField($newTable,
                               'cCardsInsertedKo');
         $migration->dropField($newTable,
                               'cCardsRemoved');
         $migration->addField($newTable,
                                 'cCardsInsertedOkToday',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cCardsInsertedOkTotal',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cCardsInsertedKoToday',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cCardsInsertedKoTotal',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cCardsRemovedToday',
                                 "int(11) NOT NULL DEFAULT '0'");
         $migration->addField($newTable,
                                 'cCardsRemovedTotal',
                                 "int(11) NOT NULL DEFAULT '0'");
      $migration->migrationOneTable($newTable);


   /*
    * Table Delete old table not used
    */
      if (TableExists("glpi_plugin_monitoring_servicesuggests")) {
         $DB->query("DROP TABLE `glpi_plugin_monitoring_servicesuggests`");
      }
      if (TableExists("glpi_plugin_monitoring_servicegraphs")) {
         $DB->query("DROP TABLE `glpi_plugin_monitoring_servicegraphs`");
      }
      if (TableExists("glpi_plugin_monitoring_securities")) {
         $DB->query("DROP TABLE `glpi_plugin_monitoring_securities`");
      }


   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/templates')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates");
   }

   // Update calendars
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



   // Update crontasks
   $crontask = new CronTask();
   if (!$crontask->getFromDBbyName('PluginMonitoringDowntime', 'DowntimesExpired')) {
      CronTask::Register('PluginMonitoringDowntime', 'DowntimesExpired', '3600',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   }
   if (!$crontask->getFromDBbyName('PluginMonitoringLog', 'cleanlogs')) {
      CronTask::Register('PluginMonitoringLog', 'cleanlogs', '96400',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   }
   if (!$crontask->getFromDBbyName('PluginMonitoringUnavailability', 'unavailability')) {
      CronTask::Register('PluginMonitoringUnavailability', 'Unavailability', '300',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   }
   if (!$crontask->getFromDBbyName('PluginMonitoringDisplayview_rule', 'replayallviewrules')) {
      CronTask::Register('PluginMonitoringDisplayview_rule', 'replayallviewrules', '1200',
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
   }
   if ($crontask->getFromDBbyName('PluginMonitoringUnavaibility', 'unavaibility')) {
      $crontask->getFromDBbyName('PluginMonitoringUnavaibility', 'unavaibility');
      $crontask->delete($crontask->fields);
   }
   if ($crontask->getFromDBbyName('PluginMonitoringServiceevent', 'updaterrd')) {
      $crontask->getFromDBbyName('PluginMonitoringServiceevent', 'updaterrd');
      $crontask->delete($crontask->fields);
   }
   if ($crontask->getFromDBbyName('PluginMonitoringHostdailycounter', 'DailyCounters')) {
      $crontask->getFromDBbyName('PluginMonitoringHostdailycounter', 'DailyCounters');
      $crontask->delete($crontask->fields);
   }

   include (GLPI_ROOT . "/plugins/monitoring/inc/command.class.php");
   $pmCommand = new PluginMonitoringCommand();
   $a_list = $pmCommand->find();
   $check_dummy_found = false;
   $restart_shinken_found = false;
   $host_action_found = false;
   foreach ($a_list as $data) {
      if ($data['command_name'] == "check_dummy") {
         $check_dummy_found = true;
      }
      if ($data['command_name'] == "restart_shinken") {
         $restart_shinken_found = true;
      }
      if ($data['command_name'] == "host_action") {
         $host_action_found = true;
      }
   }
   if (! $host_action_found) {
      // Host action command
      $pmCommand = new PluginMonitoringCommand();
      $input = array();
      $input['name'] = "Host action";
      $input['command_name'] = "host_action";
      $input['command_line'] = $DB->escape("host_action");
      $pmCommand->add($input);
   }
   if (! $restart_shinken_found) {
      // Restart shinken command
      $pmCommand = new PluginMonitoringCommand();
      $input = array();
      $input['name'] = "Restart Shinken";
      $input['command_name'] = "restart_shinken";
      $input['command_line'] = $DB->escape("nohup sh -c '/usr/local/shinken/bin/stop_arbiter.sh && sleep 3 && /usr/local/shinken/bin/launch_arbiter.sh' > /dev/null 2>&1 &");
      $pmCommand->add($input);
   }
   if (! $check_dummy_found) {
      // Check dummy command
      $pmCommand = new PluginMonitoringCommand();
      $input = array();
      $input['name'] = "Dummy check";
      $input['command_name'] = "check_dummy";
      $input['command_line'] = $DB->escape("\$PLUGINSDIR\$/check_dummy \$ARG1\$ \"\$ARG2$\"");
      $arg = array();
      $arg['ARG1'] = 'INTEGER: dummy status code';
      $arg['ARG2'] = 'TEXT: dummy status output text';
      $input['arguments'] = exportArrayToDB($arg);
      $pmCommand->add($input);
   }

   /*
    * Clean services not having hosts
    */
   $query = "SELECT `glpi_plugin_monitoring_services`.* FROM `glpi_plugin_monitoring_services`
      LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
         ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` = `plugin_monitoring_componentscatalogs_hosts_id`
   WHERE `is_static` IS NULL";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $queryd = "DELETE FROM `glpi_plugin_monitoring_services`
         WHERE `id`='".$data['id']."'";
      $DB->query($queryd);
   }

   /*
    * Clean components catalog not having hosts
    */
   $query = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
      FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
      LEFT JOIN `glpi_computers`
         ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`)
      WHERE (`glpi_computers`.`name` IS NULL);";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $queryd = "DELETE FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `id`='".$data['id']."'";
      $DB->query($queryd);
   }

   /*
    * Clean services not having valide component
    */
   $query = "SELECT `glpi_plugin_monitoring_services`.`id`
      FROM `glpi_plugin_monitoring_services`
      LEFT JOIN `glpi_plugin_monitoring_components`
         ON (`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
      WHERE (`glpi_plugin_monitoring_components`.`id` IS NULL);";
   $result = $DB->query($query);
   include (GLPI_ROOT . "/plugins/monitoring/inc/service.class.php");
   $pmService = new PluginMonitoringService();
   while ($data=$DB->fetch_array($result)) {
      $pmService->delete($data);
   }


   // Update hosts config
   include (GLPI_ROOT . "/plugins/monitoring/inc/hostconfig.class.php");
   $pmHostconfig = new PluginMonitoringHostconfig();
   $pmHostconfig->initConfig();

   // Verify hosts
   include (GLPI_ROOT . "/plugins/monitoring/inc/host.class.php");
   $pmHost = new PluginMonitoringHost();
   $pmHost->verifyHosts();


   if ($insertrealm == '1') {
      // Insert into hostconfigs
      $query = "UPDATE `glpi_plugin_monitoring_hostconfigs`
         SET `plugin_monitoring_realms_id` = '1'
         WHERE `items_id` = '0'
            AND `itemtype` = 'Entity'";
      $DB->query($query);
   }

   include (GLPI_ROOT . "/plugins/monitoring/inc/config.class.php");
   $pmConfig = new PluginMonitoringConfig();
   $pmConfig->initConfig();


   // Add user monitoring if not defined
   if (!countElementsInTable('glpi_users', "`name`='monitoring'")) {
      // Create
      $input = array('name' => 'monitoring');
      $user = new User();
      $user->add($input);
   }

   // Update items of views to be compatible with step of 10px instead 1 px
   $query = "SELECT *
      FROM `glpi_plugin_monitoring_displayviews_items`";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $x = 10 * round($data['x'] / 10);
      $y = 10 * round($data['y'] / 10);

      $DB->query("UPDATE `glpi_plugin_monitoring_displayviews_items` "
              . "SET `x`='".$x."', `y`='".$y."' "
              . "WHERE `id`='".$data['id']."'");
   }


   // Udpate componentcatalog_rules with new search engine (0.85)
   $query = "SELECT *
      FROM `glpi_plugin_monitoring_componentscatalogs_rules`";
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $data_array = importArrayFromDB($data['condition']);
      if (!isset($data_array['searchtype'])) {
         continue;
      }
      $criteria = array();
      foreach ($data_array['field'] as $num=>$value) {
         $criteria[$num]['field'] = $value;
      }
      unset($data_array['field']);
      foreach ($data_array['searchtype'] as $num=>$value) {
         $criteria[$num]['searchtype'] = $value;
      }
      unset($data_array['searchtype']);
      foreach ($data_array['contains'] as $num=>$value) {
         $criteria[$num]['value'] = $value;
      }
      unset($data_array['contains']);
      if (isset($data_array['link'])) {
         foreach ($data_array['link'] as $num=>$value) {
            $criteria[$num]['link'] = $value;
         }
         unset($data_array['link']);
      }
      $data_array['criteria'] = $criteria;
      unset($data_array['_glpi_csrf_token']);
      $DB->query("UPDATE `glpi_plugin_monitoring_componentscatalogs_rules` "
              . "SET `condition`='".exportArrayToDB($data_array)."' "
              . "WHERE `id`='".$data['id']."'");
   }



   // * Update unavailability (with table glpi_plugin_monitoring_unavailabilitystates)
//      include (GLPI_ROOT . "/plugins/monitoring/inc/unavailabilitystate.class.php");
//      $pmUnavailabilityState = new PluginMonitoringUnavailabilityState();
//      $query = "SELECT * FROM `glpi_plugin_monitoring_services`";
//      $result = $DB->query($query);
//      while ($data=$DB->fetch_array($result)) {
//         if ($pmUnavailabilityState->getLastID($data['id']) == 0) {
//            $query2 = "SELECT id FROM `glpi_plugin_monitoring_serviceevents`
//               WHERE `plugin_monitoring_services_id`='".$data['id']."'
//                  AND `unavailability`='1'
//               ORDER BY id DESC
//               LIMIT 1";
//            $result2 = $DB->query($query2);
//            while ($data2=$DB->fetch_array($result2)) {
//               $pmUnavailabilityState->setLastID($data['id'], $data2['id']);
//            }
//         }
//      }


   $query = "UPDATE `glpi_plugin_monitoring_configs`
      SET `version`='".PLUGIN_MONITORING_VERSION."'
         WHERE `id`='1'";
   $DB->query($query);
}



function migrateTablesMonitoring($migration, $a_table) {
   global $DB;

   foreach ($a_table['oldname'] as $oldtable) {
      $migration->renameTable($oldtable, $a_table['name']);
   }

   if (!TableExists($a_table['name'])) {
      $query = "CREATE TABLE `".$a_table['name']."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
      $DB->query($query);
   }

   foreach ($a_table['renamefields'] as $old=>$new) {
      $migration->changeField($a_table['name'],
                              $old,
                              $new,
                              $a_table['fields'][$new]['type'],
                              array('value' => $a_table['fields'][$new]['value'],
                                    'update'=> TRUE));
   }

   foreach ($a_table['oldfields'] as $field) {
      $migration->dropField($a_table['name'],
                            $field);
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['fields'] as $field=>$data) {
      $migration->changeField($a_table['name'],
                              $field,
                              $field,
                              $data['type'],
                              array('value' => $data['value']));
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['fields'] as $field=>$data) {
      $migration->addField($a_table['name'],
                           $field,
                           $data['type'],
                           array('value' => $data['value']));
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['oldkeys'] as $field) {
      $migration->dropKey($a_table['name'],
                          $field);
   }
   $migration->migrationOneTable($a_table['name']);

   foreach ($a_table['keys'] as $data) {
      $migration->addKey($a_table['name'],
                         $data['field'],
                         $data['name'],
                         $data['type']);
   }
   $migration->migrationOneTable($a_table['name']);

   $DB->list_fields($a_table['name'], FALSE);
}

?>
