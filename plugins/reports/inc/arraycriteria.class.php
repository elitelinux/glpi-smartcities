<?php
/**
 * @version $Id: arraycriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 */
class PluginReportsArrayCriteria extends PluginReportsDropdownCriteria {
   private $choice = array();

   function __construct($report, $name, $label='', $options=array()) {

      parent::__construct($report, $name, NOT_AVAILABLE,
                          ($label ? $label : _n('Criterion', 'Criteria', 2)));
      $this->choice = $options;
   }


   function getSubName() {

      $val = $this->getParameterValue();
      if (empty($val) || $val=='all') {
         return '';
      }
      return " " . sprintf(__('%1$s: %2$s'), $this->getCriteriaLabel(), $this->choice[$val]);
   }


   public function displayDropdownCriteria() {

      Dropdown::showFromArray($this->getName(), $this->choice,
                              array('value' => $this->getParameterValue()));
   }


   /**
    * Get SQL code associated with the criteria
    */
   public function getSqlCriteriasRestriction($link = 'AND') {

      $val = $this->getParameterValue();
      if (empty($val) || ($val == 'all')) {
         return '';
      }
      return $link . " " . $this->getSqlField() . "='$val' ";
   }
}
?>