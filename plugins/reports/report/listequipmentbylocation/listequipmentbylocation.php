<?php
/**
 * @version $Id: listequipmentbylocation.php 297 2015-05-30 21:34:55Z yllen $
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

//TRANS: The name of the report = List of equipments by location
$report = new PluginReportsAutoReport(__('listequipmentbylocation_report_title', 'reports'));
$loc    = new PluginReportsLocationCriteria($report);

$report->setColumns(array(new PluginReportsColumnType('itemtype', __('Type')),
                          new PluginReportsColumnTypeLink('items_id', __('Item'),
                                                          'itemtype', array('with_comment' => 1)),
                          new PluginReportsColumn('serial', __('Serial number')),
                          new PluginReportsColumn('otherserial', __('Inventory number')),
                          new PluginReportsColumnModelType('models_id', __('Model'),
                                                           'itemtype', array('with_comment' => 1)),
                          new PluginReportsColumnTypeType('types_id', __('Type'),
                                                          'itemtype', array('with_comment' => 1))));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $query = getSqlSubRequest("Computer",$loc,new Computer());
   foreach($CFG_GLPI["infocom_types"] as $itemtype) {
      $obj = new $itemtype;
      if ($obj->isField('locations_id')) {
         $query.= "UNION (".getSqlSubRequest($itemtype,$loc,$obj).")";
      }
   }
   $report->setGroupBy("entity","itemtype");
   $report->setSqlRequest($query);
   $report->execute();
}
else {
   Html::footer();
}


function getSqlSubRequest($itemtype,$loc,$obj) {

   $table     = getTableForItemType($itemtype);
   $models_id = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Model'));
   $types_id  = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Type'));
   $fields    = array('name'        => 'name',
                      'serial'      => 'serial',
                      'otherserial' => 'otherserial',
                      $models_id    => 'models_id',
                      $types_id     => 'types_id');

   $query_where = "SELECT '$itemtype' AS itemtype,
                          `$table`.`id` AS items_id,
                          `$table`.`locations_id`";

   foreach ($fields as $field => $alias) {
      if ($obj->isField($field)) {
         $query_where .= ", `$table`.`$field` AS $alias";
      } else {
         $query_where .= ", '' AS $alias";
      }
   }

   $query_where .= " FROM `$table` ";

   if ($obj->isEntityAssign()) {
      $query_where .= getEntitiesRestrictRequest('WHERE', "$table");
   } else {
      $query_where .= 'WHERE 1';
   }

   if ($obj->maybeTemplate()) {
      $query_where .= " AND `is_template`='0'";
   }

   if ($obj->maybeDeleted()) {
      $query_where .= " AND `is_deleted`='0'";
   }

   $query_where .= $loc->getSqlCriteriasRestriction();

   return $query_where;
}
?>