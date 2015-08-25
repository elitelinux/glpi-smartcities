<?php
/**
 * @version $Id: prioritycriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Priority selection criteria
**/
class PluginReportsPriorityCriteria extends PluginReportsAutoCriteria {


   /**
    * @param $report
    * @param $name      (default 'priority')
    * @param $label     (default '')
   **/
   function __construct($report, $name='priority', $label='') {

      parent::__construct($report, $name, $name, ($label ? $label : __('Priority')));
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);
   }


   public function displayCriteria() {

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Ticket::dropdownPriority($this->getName(), $this->getParameterValue(), 1);
      $this->getReport()->endColumn();
   }


   function getSubName() {

      if (!$this->getParameterValue()) {
         $priority = __('All');

      } else {
         if ($this->getParameterValue() < 0) {
            $priority = sprintf(__('%1$s %2$s'), __('At least', 'reports'),
                                 Ticket::getPriorityName(abs($this->getParameterValue())));
         } else {
            $priority = Ticket::getPriorityName($this->getParameterValue());
         }
      }
      return " " . $this->getCriteriaLabel() . " : " . $priority;
   }


   /**
    * @param $priority
   **/
   function setDefaultPriorityValue($priority) {
      $this->addParameter($this->getName(), $priority);
   }


   /**
    * @see plugins/reports/inc/PluginReportsAutoCriteria::getSqlCriteriasRestriction()
   */
   public function getSqlCriteriasRestriction($link='AND') {
      //If value > 0 : a priority is selected
      //If value == 0 : no priority selected
      //If value < 0 : means "priority above the priority selected"

      if ($this->getParameterValue() > 0) {
         return $link . " " . $this->getSqlField() . "= '" . $this->getParameterValue() . "'";
      }

      if ($this->getParameterValue() < 0) {
         return $link . " " . $this->getSqlField() . ">= '" . abs($this->getParameterValue()) ."'";
      }
   }

}
?>