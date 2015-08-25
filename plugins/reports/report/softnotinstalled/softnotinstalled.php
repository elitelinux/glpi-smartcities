<?php
/**
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
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

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Detailed report of software installation by status
$report = new PluginReportsAutoReport(__('softnotinstalled_report_title', 'reports'));

$soft = new PluginReportsTextCriteria($report, 'software', _n('Software', 'Software', 1));
$soft->setSqlField("`glpi_softwares`.`name`");

$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns(array(new PluginReportsColumnLink('computer', __('Computer'),'Computer',
                                                         array('sorton' => 'glpi_computers.name')),
                             new PluginReportsColumn('operatingsystems', __('Operating system'),
                                                     array('sorton' => 'operatingsystems')),
                             new PluginReportsColumn('state', __('Status'),
                                                     array('sorton' => 'state')),
                             new PluginReportsColumn('entity', __('Entity'),
                                                     array('sorton' => 'entity,location')),
                             new PluginReportsColumn('location',
                                                     sprintf(__('%1$s - %2$s'), __('Location'),
                                                             __('Computer')),
                                                     array('sorton' => 'location'))));

   $query = "SELECT `glpi_computers`.`id` AS computer,
                    `glpi_states`.`name` AS state,
                    `glpi_operatingsystems`.`name` as operatingsystems,
                    `glpi_locations`.`completename` as location,
                    `glpi_entities`.`completename` as entity
             FROM `glpi_computers`
             LEFT JOIN `glpi_states`
                  ON (`glpi_states`.`id` = `glpi_computers`.`states_id`)
             LEFT JOIN `glpi_operatingsystems`
                  ON (`glpi_operatingsystems`.`id` = `glpi_computers`.`operatingsystems_id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
             LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_computers`.`entities_id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_computers') ."
                   AND `glpi_computers`.`is_template` = 0
                   AND `glpi_computers`.`is_deleted` = 0
                   AND `glpi_computers`.`id`
                     NOT IN (SELECT `glpi_computers`.`id`
                             FROM `glpi_softwares`
                             INNER JOIN `glpi_softwareversions`
                                 ON (`glpi_softwares`.`id` = `glpi_softwareversions`.`softwares_id`)
                             INNER JOIN `glpi_computers_softwareversions`
                                 ON (`glpi_computers_softwareversions`.`softwareversions_id`
                                       = `glpi_softwareversions`.`id`)
                             INNER JOIN `glpi_computers`
                                 ON (`glpi_computers_softwareversions`.`computers_id`
                                       = `glpi_computers`.`id`) ".
                             getEntitiesRestrictRequest('WHERE', 'glpi_computers') .
                                    $report->addSqlCriteriasRestriction().")".
             $report->getOrderby('computer', true);


   $report->setSqlRequest($query);
   $report->execute();
} else {
   Html::footer();
}
?>