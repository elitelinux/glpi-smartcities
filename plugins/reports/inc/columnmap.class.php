<?php
/**
 * @version $Id: columnmap.class.php 294 2015-05-24 23:46:03Z yllen $
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

/**
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumnMap extends PluginReportsColumn {

   private $map;


   function __construct($name, $title, $map=array(), $options=array()) {

      parent::__construct($name, $title, $options);

      if (count($map)) {
         $this->map = $map;
      } else {
         switch ($name) {
            case 'status':
               $this->map = Ticket::getAllStatusArray();
               break;

            case 'impact':
               $this->map = getImpactLabelsArray();
               break;

            case 'urgency':
               $this->map = getUrgencyLabelsArray();
               break;

            case 'priority':
               $this->map = getPriorityLabelsArray();
               break;

            default:
               $this->map = array();
         }
      }
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])){
         if (isset($this->map[$row[$this->name]])) {
            return $this->map[$row[$this->name]];
         }
         return $row[$this->name];
      }
      return '';
   }
}
?>