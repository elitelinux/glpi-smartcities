<?php
/**
 * @version $Id: location.php 297 2015-05-30 21:34:55Z yllen $
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
$DBCONNECTION_REQUIRED = 0; // not really a big SQL request

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Location tree
$report = new PluginReportsAutoReport(__('location_report_title', 'reports'));

$report->setColumns(array(new PluginReportsColumn('entity', __('Entity'),
                                                  array('sorton' => 'entity,location')),
                          new PluginReportsColumn('location', __('Location'),
                                                  array('sorton' => 'location')),
                          new PluginReportsColumnLink('link', _n('Link', 'Links', 2),'Location',
                                                  array('sorton' => '`glpi_locations`.`name`'))));

// SQL statement
$query = "SELECT `glpi_entities`.`completename` AS entity,
                 `glpi_locations`.`completename` AS location,
                 `glpi_locations`.`id` AS link
          FROM `glpi_locations`
          LEFT JOIN `glpi_entities` ON (`glpi_locations`.`entities_id` = `glpi_entities`.`id`)" .
          getEntitiesRestrictRequest(" WHERE ", "glpi_locations") .
          $report->getOrderBy('entity');

$report->setGroupBy('entity');
$report->setSqlRequest($query);
$report->execute();
?>