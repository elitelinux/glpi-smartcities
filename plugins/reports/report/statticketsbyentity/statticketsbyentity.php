<?php
/**
 * @version $Id: statticketsbyentity.php 297 2015-05-30 21:34:55Z yllen $
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

//	Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1;

// Initialization of the variables
include ("../../../../inc/includes.php");

//TRANS: The name of the report = Helpdesk requesters and tickets by entity
$report = new PluginReportsAutoReport(__('statticketsbyentity_report_title', 'reports'));

//Report's search criterias
$prof = new PluginReportsDropdownCriteria($report, 'profiles_id', 'glpi_profiles',
                                          __('Profile'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $cols = array(new PluginReportsColumn('name', __('Entity'),
                                         array('sorton' => '`glpi_entities`.`completename`')),
                 new PluginReportsColumnInteger('nbusers', __('Users count', 'reports'),
                                                array('withtotal' => true,
                                                      'sorton'    => 'nbusers')),
                 new PluginReportsColumnInteger('number', __('Tickets count', 'reports'),
                                                array('withtotal' => true,
                                                      'sorton'    => 'number')),
                 new PluginReportsColumnDateTime('mindate', __('Older', 'reports'),
                                                 array('sorton' => 'mindate')),
                 new PluginReportsColumnDateTime('maxdate', __('Newer', 'reports'),
                                                 array('sorton' => 'maxdate')));
   $report->setColumns($cols);

   $subcpt = "SELECT COUNT(*)
              FROM `glpi_profiles_users`
              WHERE `glpi_profiles_users`.`entities_id`=`glpi_entities`.`id` ".
              $prof->getSqlCriteriasRestriction();

   $query = "SELECT `glpi_entities`.`completename` AS name,
                    ($subcpt) as nbusers,
                    COUNT(`glpi_tickets`.`id`) AS number,
                    MIN(`glpi_tickets`.`date`) as mindate,
                    MAX(`glpi_tickets`.`date`) as maxdate
             FROM `glpi_entities`
             INNER JOIN `glpi_tickets` ON (`glpi_tickets`.`entities_id`=`glpi_entities`.`id`)
             WHERE NOT `glpi_tickets`.`is_deleted` ".
                   getEntitiesRestrictRequest('AND', "glpi_entities") .
            "GROUP BY `glpi_entities`.`id`".
            $report->getOrderBy('name');

   $report->setSqlRequest($query);
   $report->execute(array('withtotal'=>true));

} else {
   Html::footer();
}
?>