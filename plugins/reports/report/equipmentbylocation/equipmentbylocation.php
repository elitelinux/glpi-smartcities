<?php
/**
 * @version $Id: equipmentbylocation.php 296 2015-05-25 00:14:30Z yllen $
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
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
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
//TRANS: The name of the report = Number of equipments by location
$report = new PluginReportsAutoReport(__('equipmentbylocation_report_title', 'reports'));

$report->setColumns(array(new PluginReportsColumn('entity', __('Entity')),
                          new PluginReportsColumn('location', __('Location')),
                          new PluginReportsColumnInteger('computernumber',
                                                          _n('Computer', 'Computers', 2)),
                          new PluginReportsColumnInteger('networknumber',
                                                         _n('Network', 'Networks', 2)),
                          new PluginReportsColumnInteger('monitornumber',
                                                         _n('Monitor', 'Monitors', 2)),
                          new PluginReportsColumnInteger('printernumber',
                                                         _n('Printer', 'Printers', 2)),
                          new PluginReportsColumnInteger('peripheralnumber',
                                                          _n('Device', 'Devices', 2)),
                          new PluginReportsColumnInteger('phonenumber', _n('Phone', 'Phones', 2))));

$query = "SELECT i.`entity`, i.`location`, i.`computernumber`, i.`networknumber`,
                 i.`monitornumber`, i.`printernumber`, j.`peripheralnumber`, l.`phonenumber`
          FROM (SELECT g.`entity`, g.`location`, g.`computernumber`, g.`networknumber`,
                       g.`monitornumber`, h.`printernumber`, g.`id`
                FROM (SELECT e.`entity`, e.`location`, e.`computernumber`, e.`networknumber`,
                             f.`monitornumber`, e.`id`
                      FROM (SELECT c.`entity`, c.`location`, c.`computernumber`, d.`networknumber`,
                                   c.`id`
                            FROM (SELECT a.`entity`, a.`location`, b.`computernumber`, a.`id`
                                  FROM (SELECT `glpi_entities`.`completename` AS entity,
                                               `glpi_locations`.`completename` AS location,
                                               `glpi_locations`.`id` AS id
                                        FROM `glpi_locations`
                                        LEFT JOIN `glpi_entities`
                                          ON (`glpi_locations`.`entities_id`=`glpi_entities`.`id`) ".
                                        getEntitiesRestrictRequest(" WHERE ", "glpi_locations").") a
                                  LEFT OUTER JOIN (SELECT count(*) AS computernumber,
                                                          `glpi_computers`.`locations_id` AS id
                                                   FROM `glpi_computers`
                                                   WHERE is_deleted=0 AND is_template=0
                                                   ".getEntitiesRestrictRequest(" AND ", "glpi_computers")."
                                                   GROUP BY `glpi_computers`.`locations_id`) b
                                       ON (a.id = b.id)
                                 ) c
                            LEFT OUTER JOIN (SELECT count(*) AS networknumber,
                                                    `glpi_networkequipments`.`locations_id` AS id
                                             FROM `glpi_networkequipments`
                                             WHERE is_deleted=0 AND is_template=0
                                             ".getEntitiesRestrictRequest(" AND ", "glpi_networkequipments")."
                                             GROUP BY `glpi_networkequipments`.`locations_id`) d
                                 ON (c.id = d.id)
                           ) e
                      LEFT OUTER JOIN (SELECT count(*) AS monitornumber,
                                              `glpi_monitors`.`locations_id` AS id
                                       FROM `glpi_monitors`
                                       WHERE is_deleted=0 AND is_template=0
                                       ".getEntitiesRestrictRequest(" AND ", "glpi_monitors")."
                                       GROUP BY `glpi_monitors`.`locations_id`) f
                           ON (e.id = f.id)
                     ) g
                LEFT OUTER JOIN (SELECT count(*) AS printernumber,
                                        `glpi_printers`.`locations_id` AS id
                                 FROM `glpi_printers`
                                 WHERE is_deleted=0 AND is_template=0
                                 ".getEntitiesRestrictRequest(" AND ", "glpi_printers")."
                                 GROUP BY `glpi_printers`.`locations_id`) h
                     ON (g.id = h.id)
               ) i
          LEFT OUTER JOIN (SELECT count(*) AS peripheralnumber,
                                  `glpi_peripherals`.`locations_id` AS id
                              FROM `glpi_peripherals`
                              WHERE is_deleted=0 AND is_template=0
                              ".getEntitiesRestrictRequest(" AND ", "glpi_peripherals")."
                              GROUP BY `glpi_peripherals`.`locations_id`) j
               ON (i.id = j.id)
          LEFT OUTER JOIN (SELECT count(*) AS phonenumber,
                                  `glpi_phones`.`locations_id` AS id
                           FROM `glpi_phones`
                           WHERE is_deleted=0 AND is_template=0
                           ".getEntitiesRestrictRequest(" AND ", "glpi_phones")."
                           GROUP BY `glpi_phones`.`locations_id`) l
               ON (i.id = l.id)
          ORDER BY i.entity, i.location";

$report->setGroupBy("entity");
$report->setSqlRequest($query);
$report->execute();
?>