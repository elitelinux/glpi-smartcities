<?php
/**
 * @version $Id: applicationsbylocation.php 297 2015-05-30 21:34:55Z yllen $
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
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Applications by locations and versions
$report = new PluginReportsAutoReport(__('applicationsbylocation_report_title', 'reports'));

$softwarecategories = new PluginReportsSoftwareCategoriesCriteria($report, 'softwarecategories',
                                                                  __('Software category'));
$softwarecategories->setSqlField("`glpi_softwarecategories`.`id`");

$software = new PluginReportsSoftwareCriteria($report, 'software', __('Applications', 'reports'));
$software->setSqlField("`glpi_softwares`.`id`");

$statecpt = new PluginReportsStatusCriteria($report, 'statecpt', __('Computer status', 'reports'));
$statecpt->setSqlField("`glpi_computers`.`states_id`");

$location = new PluginReportsLocationCriteria($report, 'location', _n('Location', 'Locations', 2));
$location->setSqlField("`glpi_computers`.`locations_id`");


$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns(array(new PluginReportsColumnLink('soft', _n('Software', 'Software', 1),
                                                         'Software',
                                                         array('sorton' => 'soft,version')),
                             new PluginReportsColumnLink('locat', _n('Location', 'Locations', 1),
                                                         'Location',
                                                         array('sorton' => 'glpi_locations.name')),
                             new PluginReportsColumnLink('computer', _n('Computer', 'Computers', 1),
                                                         'Computer',
                                                         array('sorton' => 'glpi_computers.name')),
                             new PluginReportsColumn('statecpt', _n('Status', 'Statuses', 1)),
                             new PluginReportsColumnLink('version', __('Version name'),
                                                         'SoftwareVersion'),
                             new PluginReportsColumnLink('user', _n('User', 'Users', 1), 'User',
                                                         array('sorton' => 'glpi_users.name'))
                      ));

   $query = "SELECT `glpi_softwareversions`.`softwares_id` AS soft,
                    `glpi_softwareversions`.`name` AS software,
                    `glpi_locations`.`id` AS locat,
                    `glpi_computers`.`id` AS computer,
                    `state_ver`.`name` AS statever,
                    `state_cpt`.`name` AS statecpt,
                    `glpi_locations`.`name` as location,
                    `glpi_softwareversions`.`id` AS version,
                    `glpi_computers`.`users_id` AS user
             FROM `glpi_softwareversions`
             INNER JOIN `glpi_computers_softwareversions`
                   ON (`glpi_computers_softwareversions`.`softwareversions_id` = `glpi_softwareversions`.`id`)
             INNER JOIN `glpi_computers`
                   ON (`glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id`)
             INNER JOIN `glpi_softwares`
                   ON (`glpi_softwares`.`id` = `glpi_softwareversions`.`softwares_id`)
             LEFT JOIN `glpi_softwarecategories`
                  ON (`glpi_softwares`.`softwarecategories_id` = `glpi_softwarecategories`.`id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
             LEFT JOIN `glpi_states` state_ver
                  ON (`state_ver`.`id` = `glpi_softwareversions`.`states_id`)
             LEFT JOIN `glpi_states` state_cpt
                  ON (`state_cpt`.`id` = `glpi_computers`.`states_id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_softwareversions') .
             $report->addSqlCriteriasRestriction().
             "ORDER BY soft ASC, locat ASC";

   $report->setSqlRequest($query);
   $report->execute();
}
?>