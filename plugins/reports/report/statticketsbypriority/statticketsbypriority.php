<?php
/**
 * @version $Id: statticketsbypriority.php 297 2015-05-30 21:34:55Z yllen $
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

//TRANS: The name of the report = Tickets no closed, sorted by priority
$report = new PluginReportsAutoReport(__('statticketsbypriority_report_title', 'reports'));

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', __('Opening date'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(new PluginReportsColumnMap('priority', __('Priority'), array(),
                                                        array('sorton' => '`priority`, `date`')),
                             new PluginReportsColumnDateTime('date', __('Opening date'),
                                                             array('sorton' => '`date`')),
                             new PluginReportsColumn('id2', __('ID')),
                             new PluginReportsColumnLink('id', __('Title'), 'Ticket'),
                             new PluginReportsColumn('groupname', __('Group'),
                                                     array('sorton' => '`glpi_groups_tickets`.`groups_id`, `date`'))));

   $query = "SELECT `glpi_tickets`.`priority`, DATE(`glpi_tickets`.`date`) AS date,
                    `glpi_tickets`.`id`, `glpi_tickets`.`id` AS id2,
                    `glpi_groups`.`name` AS groupname
             FROM `glpi_tickets`
             LEFT JOIN `glpi_groups_tickets`
                  ON (`glpi_groups_tickets`.`tickets_id` = `glpi_tickets`.`id`
                      AND `glpi_groups_tickets`.`type` = '".CommonITILActor::ASSIGN."')
             LEFT JOIN `glpi_groups` ON (`glpi_groups_tickets`.`groups_id` = `glpi_groups`.`id`)
             WHERE `glpi_tickets`.`status` NOT IN ('solved', 'closed')
                  AND NOT `glpi_tickets`.`is_deleted` ".
                  $report->addSqlCriteriasRestriction() .
                  getEntitiesRestrictRequest(' AND ', 'glpi_tickets').
             $report->getOrderBy('priority');

   $report->setSqlRequest($query);
   $report->execute();

} else {
   Html::footer();
}
?>