<?php
/**
 * @version $Id: tickettypecriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Ticket category selection criteria
 */
class PluginReportsTicketTypeCriteria extends PluginReportsArrayCriteria {


   /**
    * @param $report
    * @param $name      (default 'type')
    * @param $label     (default '')
   **/
   function __construct($report, $name='type', $label='') {

      $options = array('all' => Dropdown::EMPTY_VALUE);
      foreach (Ticket::getTypes() as $k => $v) {
         $options[$k] = $v;
      }

      parent::__construct($report, $name, ($label ? $label : _n('Type', 'Type', 1)), $options);
   }
}
