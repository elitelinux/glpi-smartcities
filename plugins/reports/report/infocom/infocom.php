<?php
/**
 * @version $Id: infocom.php 297 2015-05-30 21:34:55Z yllen $
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

/*
 * ----------------------------------------------------------------------
 *    Big UNION to have a report including all inventory
 * ----------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

/*
 * TODO : add more criteria
 *
 * - num_immo not empry
 * - otherserial not empty
 * - etc
 *
 */
//TRANS: The name of the report = Financial information
$report = new PluginReportsAutoReport(__('infocom_report_title', 'reports'));

$ignored = array('Cartridge', 'CartridgeItem', 'Consumable', 'ConsumableItem', 'Software');

$date = new PluginReportsDateIntervalCriteria($report, '`glpi_infocoms`.`buy_date`',
                                              __('Date of purchase'));
$type = new PluginReportsItemTypeCriteria($report, 'itemtype', '', 'infocom_types', $ignored);
$budg = new PluginReportsDropdownCriteria($report, '`glpi_infocoms`.`budgets_id`', 'Budget',
                                          __('Budget'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $cols = array(new PluginReportsColumnType('itemtype', __('Item type')),
                 new PluginReportsColumn('manufacturer', __('Manufacturer')),
                 new PluginReportsColumn('type', __('Type')),
                 new PluginReportsColumn('model', __('Model')),
                 new PluginReportsColumnTypeLink('itemid', __('Name'), 'itemtype'),
                 new PluginReportsColumn('serial', __('Serial number')),
                 new PluginReportsColumn('otherserial', __('Inventory number')),
                 new PluginReportsColumn('location', __('Location')),
                 new PluginReportsColumn('building', __('Building number')),
                 new PluginReportsColumn('room', __('Room number')),
                 new PluginReportsColumnLink('groups_id', __('Group'), 'Group'),
                 new PluginReportsColumn('state', __('Status')),
                 new PluginReportsColumn('immo_number', __('Immobilization number')),
                 new PluginReportsColumnDate('buy_date', __('Date of purchase')),
                 new PluginReportsColumnDate('use_date', __('Startup date')),
                 new PluginReportsColumnDate('warranty_date', __('Start date of warranty')),
                 new PluginReportsColumnInteger('warranty_duration', __('Warranty duration')),
                 new PluginReportsColumnInteger('warranty_info', __('Warranty information')),
                 new PluginReportsColumnLink('suppliers_id', __('Supplier'), "Supplier"),
                 new PluginReportsColumnDate('order_date', __('Order date')),
                 new PluginReportsColumn('order_number', __('Order number')),
                 new PluginReportsColumnDate('delivery_date', __('Delivery date')),
                 new PluginReportsColumn('delivery_number', __('Delivery form')),
                 new PluginReportsColumnFloat('value', __('Value')),
                 new PluginReportsColumnFloat('warranty_value', __('Warranty extension value')),
                 new PluginReportsColumnInteger('sink_time', __('Amortization duration')),
                 new PluginReportsColumnInteger('sink_type', __('Amortization type')),
                 new PluginReportsColumnFloat('sink_coeff',__('Amortization coefficient')),
                 new PluginReportsColumn('bill', __('Invoice number')),
                 new PluginReportsColumn('budget', __('Budget')),
                 new PluginReportsColumnDate('inventory_date', __('Date of last physical inventory')));

   $report->setColumns($cols);
   $sel = $type->getParameterValue();
   if ($sel && $sel != "all") {
      $types = array($sel);
   } else {
      $types = array_diff($CFG_GLPI['infocom_types'], $ignored);
   }

   $sql = '';
   foreach ($types as $itemtype) {
      $item = new $itemtype;
      $table = $item->getTable();

      $select = "SELECT '$itemtype' as itemtype,
                        `$table`.id AS itemid";

      $from = "FROM `$table` ";

      if ($itemtype == 'SoftwareLicense') {
         $select .= ", `glpi_manufacturers`.`name` AS manufacturer";
         $from   .= "LEFT JOIN `glpi_softwares`
                        ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
                     LEFT JOIN `glpi_manufacturers`
                        ON (`glpi_manufacturers`.`id` = `glpi_softwares`.`manufacturers_id`) ";

      } else if ($item->isField('manufacturers_id')) {
         $select .= ", `glpi_manufacturers`.`name` AS manufacturer";
         $from   .= "LEFT JOIN `glpi_manufacturers`
                        ON (`glpi_manufacturers`.`id` = `$table`.`manufacturers_id`) ";

      } else {
         $select .= ", '' AS manufacturer";
      }

      $typeclass = $itemtype.'Type';
      $typetable = getTableForItemType($typeclass);
      if (TableExists($typetable)) {
         $typeitem  = new $typeclass;
         $typefkey  = $typeitem->getForeignKeyField();

         $select .= ", `$typetable`.`name` AS type";
         $from .= "LEFT JOIN `$typetable`
                        ON (`$typetable`.`id` = `$table`.`$typefkey`) ";
      } else {
         $select .= ", '' AS type";
      }

      $modelclass = $itemtype.'Model';
      $modeltable = getTableForItemType($modelclass);
      if ($itemtype == 'SoftwareLicense') {
         $select .= ", CONCAT(glpi_softwares.name,' ',buyversion.name) AS model";
         $from .= "LEFT JOIN `glpi_softwareversions` AS buyversion
                          ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`) ";

      } else if (TableExists($modeltable)) {
         $modelitem  = new $modelclass();
         $modelitem  = $modelitem->getForeignKeyField();

         $select .= ", `$modeltable`.`name` AS model";
         $from .= "LEFT JOIN `$modeltable`
                        ON (`$modeltable`.`id` = `$table`.`$modelitem`) ";
      } else {
         $select .= ", '' AS model";
      }

      if ($item->isField('serial')) {
         $select .= ", `$table`.`serial`";
      } else {
         $select .= ", '' AS `serial`";
      }

      if ($item->isField('otherserial')) {
         $select .= ", `$table`.`otherserial`";
         $where   = "WHERE (`$table`.`otherserial` != ''
                            OR `glpi_infocoms`.`immo_number` !='') ";
      } else {
         $select .= ", '' AS `otherserial`";
         $where   = "WHERE 1 ";
      }

      if ($item->isField('groups_id')) {
         $select .= ", `$table`.`groups_id`";
      } else {
         $select .= ", 0 AS `groups_id`";
      }

      if ($item->isField('states_id')) {
         $select .= ", `glpi_states`.`name` AS state";
         $from   .= "LEFT JOIN `glpi_states`
                         ON (`glpi_states`.`id` = `$table`.`states_id`)";
      } else {
         $select .= ", '' AS `state`";
      }

      if ($item->isField('locations_id')) {
         $select .= ", `glpi_locations`.`completename` AS location
                     , `glpi_locations`.`building`
                     , `glpi_locations`.`room`";
         $from   .= "LEFT JOIN `glpi_locations`
                         ON (`glpi_locations`.`id` = `$table`.`locations_id`)";
      } else {
         $select .= ", '' AS location, '' AS building, '' AS room";
      }

      $select .= ", `glpi_infocoms`.*
                  , `glpi_infocoms`.`suppliers_id` AS supplier
                  , `glpi_budgets`.`name` AS budget";
      $from   .= "LEFT JOIN `glpi_infocoms`
                      ON (`glpi_infocoms`.`itemtype` = '$itemtype'
                          AND `glpi_infocoms`.`items_id` = `$table`.`id`)
                  LEFT JOIN `glpi_budgets`
                      ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)";

      if ($item->maybeDeleted()) {
         $where .= " AND `$table`.`is_deleted` = 0 ";
      }

      if ($item->maybeTemplate()) {
         $where .= " AND `$table`.`is_template` = 0 ";
      }

      if ($item->isEntityAssign()) {
         $where .= getEntitiesRestrictRequest(" AND ", $table);
      }

      $where .= $budg->getSqlCriteriasRestriction();
      $where .= $date->getSqlCriteriasRestriction();

      if ($sql) {
         $sql .= " UNION ";
      }
      $sql .= "($select $from $where)";
   }
   $report->setGroupBy('entity');
   $report->setSqlRequest($sql);
   $report->execute();

} else {
   Html::footer();
}
?>