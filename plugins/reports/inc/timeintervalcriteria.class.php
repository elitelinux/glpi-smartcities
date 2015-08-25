<?php
/**
 * @version $Id: timeintervalcriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Criteria which allows to select a time interval
 */
class PluginReportsTimeIntervalCriteria extends PluginReportsAutoCriteria {


   /**
    * @param $report
    * @param $name      (default time-interval)
    * @param $label     (default '')
   **/
   function __construct($report, $name='time-interval', $label='') {
      parent::__construct($report, $name, $name, $label);
   }


   public function setDefaultValues() {

      $this->setStartTime(date("Y-m-d"));
      $this->setEndTime(date("Y-m-d"));
   }


   function setStartTime($starttime) {
      $this->addParameter('starttime',$starttime);
   }


   function setEndtime($endtime) {
      $this->addParameter('endtime',$endtime);
   }


   function displayCriteria() {

      $this->getReport()->startColumn();

      printf(__('Start at %s'), __('Number pending', 'reports'));
      echo "&nbsp;&nbsp;";
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Dropdown::showHours("starttime", $this->getParameter('starttime'));
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      printf(__('End at %s'), __('Number pending', 'reports'));
      echo "&nbsp;&nbsp;";
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Dropdown::showHours("endtime", $this->getParameter('endtime'));
      $this->getReport()->endColumn();
   }


   /**
    * @see plugins/reports/inc/PluginReportsAutoCriteria::getSqlCriteriasRestriction()
   **/
   function getSqlCriteriasRestriction($link='AND') {

      if ($this->getParameter("starttime") < $this->getParameter("endtime")) {
         // ex  08:00:00 <= time < 18:00:00
         return " $link TIME(".$this->getSqlField().") >= '".$this->getParameter('starttime'). ":00'
                 AND TIME(" .$this->getSqlField(). ") < '" .$this->getParameter('endtime'). ":00'";
      }
      // ex time < 08:00:00 or 18:00:00 <= time
      return " $link (TIME(". $this->getSqlField().") >= '".$this->getParameter('starttime').":00'
                      OR TIME(".$this->getSqlField().") < '".$this->getParameter('endtime').":00')";
   }


   function getSubName() {

      $title = $this->getCriteriaLabel($this->getName());
      if (empty($title)) {
         if ($this->getName() == 'date-interval') {
            $title = __('Date interval', 'reports');
         } if ($this->getName() == 'time-interval') {
            $title = __('Time interval', 'reports');
         }
      }
      return sprintf(__('%1$s (%2$s)'), "&nbsp;" . $title,
                     $this->getParameter('starttime') . "," . $this->getParameter('endtime'));
   }

}
?>