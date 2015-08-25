<?php
/**
 * @version $Id: zombies.php 297 2015-05-30 21:34:55Z yllen $
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

$USEDBREPLICATE         = 0;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Users with no right
$report = new PluginReportsAutoReport(__('zombies_report_title', 'reports'));

$name = new PluginReportsTextCriteria($report, 'name', __('Login'));

$tab = array(0 => __('No'),
             1 => __('Yes'));
$filter = new PluginReportsArrayCriteria($report, 'tickets', __('With no ticket', 'reports'), $tab);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();
   $report->delCriteria('tickets');

   $cols = array(new PluginReportsColumnItemCheckbox('id', 'User'),
                 new PluginReportsColumnLink('id2', __('User'), 'User',
                                             array('with_comment' => true,
                                                   'with_navigate' => true)),
                 new PluginReportsColumn('name', __('Login'), array('sorton' => 'name')),
                 new PluginReportsColumn('email', __('Email')),
                 new PluginReportsColumn('phone', __('Phone')),
                 new PluginReportsColumn('location', __('Location')),
                 new PluginReportsColumnDate('last_login', __('Last login'),
                                             array('sorton' => 'last_login')));

   if (!$filter->getParameterValue()) {
      $cols[] = new PluginReportsColumnInteger('nb1', __('Writer'),  array('with_zero' => false,
                                                                           'sorton'    => 'nb1'));
      $cols[] = new PluginReportsColumnInteger('nb2', __('Requester'), array('with_zero' => false,
                                                                             'sorton'    => 'nb2'));
      $cols[] = new PluginReportsColumnInteger('nb3', __('Watcher'), array('with_zero' => false,
                                                                           'sorton'    => 'nb3'));
      $cols[] = new PluginReportsColumnInteger('nb4', __('Technician'), array('with_zero' => false,
                                                                              'sorton'    => 'nb4'));
   }

   $report->setColumns($cols);

   $query = "SELECT `glpi_users`.`id`, `glpi_users`.`id` AS id2, `glpi_users`.`name`, `last_login`,
                    (SELECT COUNT(*)
                       FROM `glpi_tickets`
                       WHERE `glpi_users`.`id` = `glpi_tickets`.`users_id_recipient`
                    ) AS nb1,
                    (SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".CommonITILActor::REQUESTER."
                    ) AS nb2,
                    (SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".CommonITILActor::OBSERVER."
                    ) AS nb3,
                    (SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".CommonITILActor::ASSIGN."
                    ) AS nb4,
                    `phone`, `glpi_locations`.`completename` as location,
                    `glpi_useremails`.`email`
             FROM `glpi_users`
             LEFT JOIN `glpi_locations`
                    ON `glpi_locations`.`id` = `glpi_users`.`locations_id`
             LEFT JOIN `glpi_useremails`
                    ON `glpi_useremails`.`users_id` = `glpi_users`.`id`
                   AND `glpi_useremails`.`is_default`
             WHERE `glpi_users`.`id` NOT IN (
                   SELECT distinct `users_id`
                   FROM `glpi_profiles_users`
                   )
             AND `glpi_users`.`is_deleted`=0 ".
             $report->addSqlCriteriasRestriction('AND');
   if ($filter->getParameterValue()) {
      $query .= " HAVING nb1=0 AND nb2=0 AND nb3=0  AND nb4=0 ";
   }
   $query .= $report->getOrderBy('name');

   $report->setSqlRequest($query);
   $report->execute(array('withmassiveaction' => 'User'));

} else {
   Html::Footer();
}
