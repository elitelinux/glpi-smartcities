<?php
/**
 * @version $Id: ticketstatuscriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Ticket status selection criteria
**/
class PluginReportsTicketStatusCriteria extends PluginReportsArrayCriteria {

   private $choice = array();


   /**
    * @param $report
    * @param $name      (default 'status')
    * @param $label     (default '')
    * @param $option    (default 1)
   **/
   function __construct($report, $name='status', $label='', $option=1) {

      if (is_array($option)) {
         foreach ($option as $opt) {
            $tab[$opt] = Ticket::getStatus($opt);
         }

      } else if ($option == 1) {
         $tab = Ticket::getAllStatusArray(true);

      } else {
         $tab = Ticket::getAllStatusArray(false);
      }

      // Parent is PluginReportsArrayCriteria
      parent::__construct($report, $name, ($label ? $label : _n('Status', 'Statuses', 1)), $tab);
   }


   /**
    * Get SQL code associated with the criteria
    *
    * @see plugins/reports/inc/PluginReportsArrayCriteria::getSqlCriteriasRestriction()
   **/
   public function getSqlCriteriasRestriction($link='AND') {

      $status = $this->getParameterValue();
      switch ($status) {
         case "notold" :
            $list  = implode("','", Ticket::getNewStatusArray());
            $list .= implode("','", Ticket::getProcessStatusArray());
            $list .= "','".Ticket::WAITING;
            break;

         case "old" :
            $list = implode("','", Ticket::getClosedStatusArray());
            break;

         case "process" :
            $list = implode("','", Ticket::getProcessStatusArray());
            break;

         case Ticket::INCOMING :
         case Ticket::ASSIGNED :
         case Ticket::PLANNED :
         case Ticket::WAITING :
         case Ticket::SOLVED :
         case Ticket::CLOSED :
            $list = $status;
            break;

         case "all" :
         default :
            return '';
      }
      return $link . " " . $this->getSqlField() . " IN ('".$list."') ";
   }

}
?>