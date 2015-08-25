<?php
/**
 * @version $Id: softversioninstallations.php 297 2015-05-30 21:34:55Z yllen $
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

//TRANS: The name of the report = Not installed important software (plural)
$report = new PluginReportsAutoReport(__('softversionsinstallations_report_title', 'reports'));

$statever = new PluginReportsStatusCriteria($report, 'statever',
                                            __('Software version status', 'reports'));
$statever->setSqlField("`glpi_softwareversions`.`states_id`");

$statecpt = new PluginReportsStatusCriteria($report, 'statecpt',
                                            __('Computer status', 'reports'));
$statecpt->setSqlField("`glpi_computers`.`states_id`");


$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns(array(new PluginReportsColumnLink('software', _n('Software', 'Software', 1),
                                                         'Software',
                                                         array('sorton' => 'software,version')),
                             new PluginReportsColumnLink('version', __('Version'),
                                                         'SoftwareVersion'),
                             new PluginReportsColumn('statever', __('Status')),
                             new PluginReportsColumnLink('computer', __('Computer'),'Computer',
                                                         array('sorton' => 'glpi_computers.name')),
                             new PluginReportsColumn('statecpt', __('Status')),
                             new PluginReportsColumn('location', __('Location'),
                                                     array('sorton' => 'location'))));

   $query = "SELECT `glpi_softwareversions`.`softwares_id` AS software,
                    `glpi_softwareversions`.`id` AS version,
                    `glpi_computers`.`id` AS computer,
                    `state_ver`.`name` AS statever,
                    `state_cpt`.`name` AS statecpt,
                    `glpi_locations`.`completename` as location
             FROM `glpi_softwareversions`
             INNER JOIN `glpi_computers_softwareversions`
                  ON (`glpi_computers_softwareversions`.`softwareversions_id`
                        = `glpi_softwareversions`.`id`)
             INNER JOIN `glpi_computers`
                  ON (`glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
             LEFT JOIN `glpi_states` state_ver
                  ON (`state_ver`.`id` = `glpi_softwareversions`.`states_id`)
             LEFT JOIN `glpi_states` state_cpt
                  ON (`state_cpt`.`id` = `glpi_computers`.`states_id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_softwareversions') .
             $report->addSqlCriteriasRestriction().
             $report->getOrderby('software', true);

   $report->setSqlRequest($query);
   $report->execute();
} else {
   Html::footer();
}
?>