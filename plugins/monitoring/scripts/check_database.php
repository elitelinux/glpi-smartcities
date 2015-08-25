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

chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

include ("../../../inc/includes.php");

// Init debug variable
$_SESSION['glpi_use_mode'] = Session::DEBUG_MODE;
$_SESSION['glpilanguage']  = "en_GB";

Session::LoadLanguage();

// Only show errors
$CFG_GLPI["debug_sql"]        = $CFG_GLPI["debug_vars"] = 0;
$CFG_GLPI["use_log_in_files"] = 1;
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
//set_error_handler('userErrorHandlerDebug');

$DB = new DB();
if (!$DB->connected) {
   die("No DB connection\n");
}

$comparaisonSQLFile = "plugin_monitoring-empty.sql";
// See http://joefreeman.co.uk/blog/2009/07/php-script-to-compare-mysql-database-schemas/

$file_content = file_get_contents("../../monitoring/install/mysql/".$comparaisonSQLFile);
$a_lines = explode("\n", $file_content);

$a_tables_ref = array();
$current_table = '';
foreach ($a_lines as $line) {
   if (strstr($line, "CREATE TABLE ")) {
      $matches = array();
      preg_match("/`(.*)`/", $line, $matches);
      $current_table = $matches[1];
   } else {
      if (preg_match("/^`/", trim($line))) {
         $s_line = explode("`", $line);
         $s_type = explode("COMMENT", $s_line[2]);
         $s_type[0] = trim($s_type[0]);
         $s_type[0] = str_replace(" COLLATE utf8_unicode_ci", "", $s_type[0]);
         $s_type[0] = str_replace(" CHARACTER SET utf8", "", $s_type[0]);
         $a_tables_ref[$current_table][$s_line[1]] = str_replace(",", "", $s_type[0]);
      }
   }
}

// * Get tables from MySQL
$a_tables_db = array();
$a_tables = array();
// SHOW TABLES;
$query = "SHOW TABLES";
$result = $DB->query($query);
while ($data=$DB->fetch_array($result)) {
  if (strstr($data[0], "monitoring")) {

      $data[0] = str_replace(" COLLATE utf8_unicode_ci", "", $data[0]);
      $data[0] = str_replace("( ", "(", $data[0]);
      $data[0] = str_replace(" )", ")", $data[0]);
      $a_tables[] = $data[0];
   }
}

foreach($a_tables as $table) {
   $query = "SHOW CREATE TABLE ".$table;
   $result = $DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      $a_lines = explode("\n", $data['Create Table']);

      foreach ($a_lines as $line) {
         if (strstr($line, "CREATE TABLE ")) {
            $matches = array();
            preg_match("/`(.*)`/", $line, $matches);
            $current_table = $matches[1];
         } else {
            if (preg_match("/^`/", trim($line))) {
               $s_line = explode("`", $line);
               $s_type = explode("COMMENT", $s_line[2]);
               $s_type[0] = trim($s_type[0]);
               $s_type[0] = str_replace(" COLLATE utf8_unicode_ci", "", $s_type[0]);
               $s_type[0] = str_replace(" CHARACTER SET utf8", "", $s_type[0]);
               $s_type[0] = str_replace(",", "", $s_type[0]);
               if (trim($s_type[0]) == 'text'
                  || trim($s_type[0]) == 'longtext') {
                  $s_type[0] .= ' DEFAULT NULL';
               }
               $a_tables_db[$current_table][$s_line[1]] = $s_type[0];
            }
         }
      }
   }
}

$a_tables_ref_tableonly = array();
foreach ($a_tables_ref as $table=>$data) {
   $a_tables_ref_tableonly[] = $table;
}
$a_tables_db_tableonly = array();
foreach ($a_tables_db as $table=>$data) {
   $a_tables_db_tableonly[] = $table;
}

 // Compare
$tables_toremove = array_diff($a_tables_db_tableonly, $a_tables_ref_tableonly);
$tables_toadd = array_diff($a_tables_ref_tableonly, $a_tables_db_tableonly);

// See tables missing or to delete
if (count($tables_toadd) > 0) {
   echo "Tables missing :\n";
   print_r($tables_toadd);
   echo "================\n";
}
if (count($tables_toremove) > 0) {
   echo "Tables to delete :\n";
   print_r($tables_toremove);
   echo "================\n";
}

// See if fields are same
foreach ($a_tables_db as $table=>$data) {
   if (isset($a_tables_ref[$table])) {
      $fields_toremove = array_diff_assoc($data, $a_tables_ref[$table]);
      $fields_toadd = array_diff_assoc($a_tables_ref[$table], $data);
      $diff = "======= DB ============== Ref =======> ".$table."\n";
      $diff .= print_r($data, TRUE);
      $diff .= print_r($a_tables_ref[$table], TRUE);

      if (count($fields_toadd) > 0) {
         echo "Fields missing/not good in ".$when." ".$table." into ".$diff."\n";
         print_r($fields_toadd);
      }
      if (count($fields_toremove) > 0) {
         echo "Fields to delete in ".$when." ".$table." into ".$diff."\n";
         print_r($fields_toremove);
      }
   }
}

?>