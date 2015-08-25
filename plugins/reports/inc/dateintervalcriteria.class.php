<?php
/**
 * @version $Id: dateintervalcriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Criteria which allows to select a date interval
 */
class PluginReportsDateIntervalCriteria extends PluginReportsAutoCriteria {

   function __construct($report, $name='date-interval', $label='', $start='', $end='') {

      parent::__construct($report, $name, $name, $label);

      $this->addCriteriaLabel($this->getName()."_1",
                              ($start ? $start : ($label ? __('After') : __('Start date'))));
      $this->addCriteriaLabel($this->getName()."_2",
                              ($end ? $end : ($label ? __('Before') : __('End date'))));
   }


   public function setStartDate($startdate) {
      $this->addParameter($this->getName()."_1", $startdate);
   }


   function setEndDate($enddate) {
      $this->addParameter($this->getName()."_2", $enddate);
   }


   public function getStartDate() {

      $start = $this->getParameter($this->getName()."_1");
      $end   = $this->getParameter($this->getName()."_2");

      return (($start == 'NULL') || ($end == 'NULL') || ($start < $end) ? $start : $end);
   }


   public function getEndDate() {

      $start = $this->getParameter($this->getName()."_1");
      $end   = $this->getParameter($this->getName()."_2");

      return (($start == 'NULL') || ($end == 'NULL') || ($start < $end) ? $end : $start);
   }


   public function setDefaultValues() {

      $this->setStartDate('NULL');
      $this->setEndDate('NULL');
   }


   public function displayCriteria() {

      $this->getReport()->startColumn();
      $name = $this->getCriteriaLabel($this->getName());
      if ($name) {
         echo "$name, ";
      }
      echo $this->getCriteriaLabel($this->getName()."_1").'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Html::showDateFormItem($this->getName()."_1", $this->getStartDate(), false);
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      if ($name) {
         echo "$name, ";
      }
      echo $this->getCriteriaLabel($this->getName()."_2").'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Html::showDateFormItem($this->getName()."_2", $this->getEndDate(), false);
      $this->getReport()->endColumn();
   }


   public function getSqlCriteriasRestriction($link = 'AND') {

      $start = $this->getStartDate();
      $end   = $this->getEndDate();

      if (($start == 'NULL') && ($end == 'NULL')) {
         return '';
      }

      $sql = '';
      if ($start != 'NULL') {
         $sql .= $this->getSqlField() . ">= '" . $this->getStartDate() . " 00:00:00'";
      }

      if (($start != 'NULL') && ($end != 'NULL')) {
         $sql .= ' AND ';
      }

      if ($end != 'NULL') {
         $sql .= $this->getSqlField() . "<='" . $this->getEndDate() . " 23:59:59' ";
      }

      return $link . " ($sql)";
   }


   function getSubName() {

      $start = $this->getStartDate();
      $end   = $this->getEndDate();
      $title = $this->getCriteriaLabel($this->getName());

      if (($start == 'NULL') && ($end == 'NULL')) {
         return '';
      }
      if (empty($title)) {
         if ($this->getName() == 'date-interval') {
            $title = __('Date interval', 'reports');
         } if ($this->getName() == 'time-interval') {
            $title = __('Time interval', 'reports');
         }
      }

      if ($start == 'NULL') {
         return $title . ', ' . sprintf(__('%1$s %2$s'), __('Before'), Html::convDate($end));
      }

      if ($end == 'NULL') {
         return $title . ', ' . sprintf(__('%1$s %2$s'), __('After'), Html::convDate($start));
      }

      return sprintf(__('%1$s (%2$s)'), $title, Html::convDate($start) . ',' .Html::convDate($end));
   }

}
?>