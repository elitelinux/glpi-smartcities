<?php
/*
 * -------------------------------------------------------------------------
Timezones management plugin 
Copyright (C) 2015 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Timezones management plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// Purpose of file: to setup time zone management plugin to GLPI
// ----------------------------------------------------------------------

/**
 * Summary of plugin_init_timezones
 */
function plugin_init_timezones() {
    global $PLUGIN_HOOKS,$CFG_GLPI;


   Plugin::registerClass('PluginTimezonesUser',
                         array('addtabon'                    => array('Preference', 'User')));

   // Init session
   $PLUGIN_HOOKS['init_session']['timezones'] = 'plugin_init_session_timezones';

   $PLUGIN_HOOKS['post_init']['timezones'] = 'plugin_timezones_postinit';

   $PLUGIN_HOOKS['pre_item_update']['timezones'] 
      = array( 'User' => array( 'PluginTimezonesUser', 'preItemUpdate'));

   $PLUGIN_HOOKS['item_update']['timezones'] 
      = array( 'TicketTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ProblemTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ProjectTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ChangeTask' => 'plugin_item_add_update_timezones_tasks'
                ,'Config' => 'plugin_item_add_update_timezones_dbconnection'
              );

   $PLUGIN_HOOKS['item_add']['timezones'] 
      = array( 'TicketTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ProblemTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ProjectTask' => 'plugin_item_add_update_timezones_tasks'
                ,'ChangeTask' => 'plugin_item_add_update_timezones_tasks'
                ,'Config' => 'plugin_item_add_update_timezones_dbconnection'
              );


   $PLUGIN_HOOKS['add_javascript']['timezones'] = "js/tz.php";   
   
   $PLUGIN_HOOKS['csrf_compliant']['timezones'] = true;

   //$PLUGIN_HOOKS['use_massive_action']['timezones'] = 1;

}

/**
 * Summary of plugin_version_timezones
 * @return string[]
 */
function plugin_version_timezones() {

   return array('name'           => 'Timezones',
                'version'        => '1.2.0',
                'author'         => 'Olivier Moron',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/timezones',
                'minGlpiVersion' => '0.83');// For compatibility / no install in version < 0.83
}


/**
 * Summary of plugin_timezones_check_prerequisites
 * @return bool
 */
function plugin_timezones_check_prerequisites() {
    global $DB, $LANG;
   
    // Strict version check (could be less strict, or could allow various version)
   if (version_compare(GLPI_VERSION,'0.83','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
        echo $LANG['timezones']['glpiversion'];
        return false;
   }

   // check if mySQL time_zones tables are empty
   $query = "SELECT * FROM mysql.time_zone_name" ;
   $res = $DB->query( $query ) ;
   if( $DB->numrows( $res ) == 0 ) {
       echo $LANG['timezones']['timezonetables'] ;
       return false ;
   }

   return true;
}


/**
 * Summary of plugin_timezones_check_config
 * @param mixed $verbose 
 * @return bool
 */
function plugin_timezones_check_config($verbose=false) {
   global $DB,$LANG;
   
    // check if all datetime fields of the glpi db have been converted to timestamp otherwise, timezone management can't be done correctly
    $query = "SELECT DISTINCT( TABLE_NAME ) from `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA = '".$DB->dbdefault."' AND TABLE_NAME LIKE 'glpi_%' AND COLUMN_TYPE IN ('DATETIME'); "; 
    $res = $DB->query( $query ) ;
    if( $DB->numrows( $res ) > 0 ) {
        if( $verbose ) {
            echo $LANG['timezones']['dbnotconverted'];
        }
        return false ;
    }

   return true;
}

