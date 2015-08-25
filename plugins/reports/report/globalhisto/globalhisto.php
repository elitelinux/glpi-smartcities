<?php
/**
 * @version $Id: globalhisto.php 297 2015-05-30 21:34:55Z yllen $
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
$DBCONNECTION_REQUIRED  = 0; // not really a big SQL request

include ("../../../../inc/includes.php");
//TRANS: The name of the report = Global History (for Test / example only)
$report = new PluginReportsAutoReport(__('globalhisto_report_title', 'reports'));

//Report's search criterias
//Possible current values are :
// - date-interval
// - time-interval
// - group
new PluginReportsDateIntervalCriteria($report, "date_mod");

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array('id'            => __('ID'),
                             'date_mod'      => __('Date'),
                             'user_name'     => __('User'),
                             'linked_action' => _x('noun','Update')));

   //Colunmns mappings if needed
   $columns_mappings = array('linked_action'
                              => array(Log::HISTORY_DELETE_ITEM
                                          => __('Delete the item'),
                                       Log::HISTORY_RESTORE_ITEM
                                          => __('Restore the item'),
                                       Log::HISTORY_ADD_DEVICE
                                          => __('Add the component'),
                                       Log::HISTORY_UPDATE_DEVICE
                                          => __('modification of components', 'reports'),
                                       Log::HISTORY_DELETE_DEVICE
                                          => __('Delete the component'),
                                       Log::HISTORY_INSTALL_SOFTWARE
                                          => __('Install the software'),
                                       Log::HISTORY_UNINSTALL_SOFTWARE
                                          => __('Uninstall the software'),
                                       Log::HISTORY_DISCONNECT_DEVICE
                                          => __('Logout'),
                                       Log::HISTORY_CONNECT_DEVICE
                                          => __('Connection'),
                                       Log::HISTORY_LOCK_DEVICE
                                          => __('Lock the item'),
                                       Log::HISTORY_UNLOCK_DEVICE
                                          => __('Unlock the item'),
                                       Log::HISTORY_LOG_SIMPLE_MESSAGE => ""));

   $report->setColumnsMappings($columns_mappings);

   $query = "SELECT `id`, `date_mod`, `user_name`, `linked_action`
             FROM `glpi_logs` ".
             $report->addSqlCriteriasRestriction("WHERE")."
             ORDER BY `date_mod`";

   $report->setSqlRequest($query);
   $report->execute();
}

Html::footer();
?>