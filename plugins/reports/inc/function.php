<?php
/**
 * @version $Id: function.php 294 2015-05-24 23:46:03Z yllen $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/


/**
 * Search for reports in all activated plugins
 *
 * @return tab : an array which contains all the reports found (name => plugin)
**/
function searchReport($all = false) {
   global $DB;

   $tab = array ();
   $filter = array('state' => Plugin::ACTIVATED);
   if ($all) {
      $filter = "";
   }
   foreach ($DB->request('glpi_plugins', $filter) as $plug) {
      foreach (glob(GLPI_ROOT.'/plugins/'.$plug['directory'].'/report/*', GLOB_ONLYDIR) as $path) {
         $tab[basename($path)] = $plug['directory'];
         includeLocales(basename($path), $plug['directory']);
      }
   }
   return $tab;
}


/**
 * Include locales for a specific report
 *
 * @param $report_name  the name of the report to use
 * @param $plugin       plugins name (default 'reports')
 *
 * @return boolean, true if locale found
**/
function includeLocales($report_name, $plugin='reports') {
   global $CFG_GLPI, $LANG;

   $prefix = GLPI_ROOT . "/plugins/$plugin/report/". $report_name ."/" . $report_name;

   if (isset ($_SESSION["glpilanguage"])
       && file_exists($prefix . "." . $_SESSION["glpilanguage"].".php")) {

      include_once  ($prefix . "." . $_SESSION["glpilanguage"].".php");

   } else if (file_exists($prefix . ".en_GB.php")) {
      include_once  ($prefix . ".en_GB.php");

   } else {
      // At least defined report name
      $name = $report_name.'_report_title';
      $LANG['plugin_'.$plugin][$report_name] = __($report_name.'_report_title', $plugin);
      // For dev
      if ($LANG['plugin_'.$plugin][$report_name] == $report_name.'_report_title') {
         Toolbox::logInFile('php-errors',
                            "includeLocales($name, $plugin) => not found\n");
      }
  //    return false;
   }

   return true;
}


/**
 * Manage display and export of an sql query
 *
 * @param $name             name of the report
 * @param $sql              the sql query to execute
 * @param $cols     array   which contains the columns and their name to display
 * @param $subname          second level of name to display (default '')
 * @param $group    array   which contains all the fields to use in GROUP BY sql instruction
**/
function simpleReport($name, $sql, $cols=array(), $subname="", $group=array()) {
   global $DB, $CFG_GLPI;

   $report = new AutoReport($name);

   if (count($cols)) {
      $report->setColumns($cols);
   }

   if (!empty($subname)) {
      $report->setSubName($subname);
   }

   if (count($group)) {
      $report->setGroupBy($group);
   }

   $report->setSqlRequest($sql);
   $report->execute();
}


function getPriorityLabelsArray() {

   return array("1" => Ticket::getPriorityName(1),
                "2" => Ticket::getPriorityName(2),
                "3" => Ticket::getPriorityName(3),
                "4" => Ticket::getPriorityName(4),
                "5" => Ticket::getPriorityName(5),
                "6" => Ticket::getPriorityName(6));
}


function getImpactLabelsArray() {

   return array("1" => Ticket::getImpactName(1),
                "2" => Ticket::getImpactName(2),
                "3" => Ticket::getImpactName(3),
                "4" => Ticket::getImpactName(4),
                "5" => Ticket::getImpactName(5));
}


function getUrgencyLabelsArray() {

   return array("1" => Ticket::getUrgencyName(1),
                "2" => Ticket::getUrgencyName(2),
                "3" => Ticket::getUrgencyName(3),
                "4" => Ticket::getUrgencyName(4),
                "5" => Ticket::getUrgencyName(5));
}


function getReportConfigPage($plugin,$report_name) {
   return GLPI_ROOT."/plugins/$plugin/report/$report_name/".$report_name.".config".".php";
}
?>