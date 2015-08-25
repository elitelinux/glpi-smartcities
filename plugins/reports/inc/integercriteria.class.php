<?php
/**
 * @version $Id: integercriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * User titles selection criteria
 */
class PluginReportsIntegerCriteria extends PluginReportsDropdownCriteria {

   private $signe = '=';
   private $min   = 0;
   private $max   = 100;
   private $coef  = 1;


   /**
    * @param $report
    * @param $name            (default 'value')
    * @param $label           (default '')
    * @param $signe           (default '')
    * @param $min             (default 0)
    * @param $max             (default 100)
    * @param $coef            (default 1)
    * @param $unit            (default '')
   **/
   function __construct($report, $name='value', $label='', $signe='', $min=0, $max=100, $coef=1,
                        $unit='') {

      parent::__construct($report, $name, NOT_AVAILABLE, ($label ? $label : __('Value')));

      $this->setOptions($signe,$min,$max,$coef,$unit);
   }


   function setDefaultValues() {

      $this->addParameter($this->getName(),0);
      $this->addParameter($this->getName().'_sign','<=');
   }


   /**
    * @param $signe     (default '')
    * @param $min       (default 0)
    * @param $max       (default 100)
    * @param $coef      (default 1)
    * @param $unit      (default '')
   **/
   function setOptions($signe='', $min=0, $max=100, $coef=1, $unit='') {

      $this->signe = $signe;
      $this->min   = $min;
      $this->max   = $max;
      $this->coef  = $coef;
      $this->unit  = $unit;
   }


   function displayCriteria() {

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      if (empty($this->signe)) {
         Dropdown::showFromArray($this->getName()."_sign",
                                 array('<='    => '<=',
                                       '>='    => '>='),
                                 array('value' => Toolbox::unclean_cross_side_scripting_deep($this->getParameter($this->getName()."_sign"))));
         echo "&nbsp;";
      }
      Dropdown::showInteger($this->getName(), $this->getParameterValue(), $this->min, $this->max, 1);
      echo '&nbsp; '.$this->unit;

      $this->getReport()->endColumn();
   }


   /**
    * Get criteria's subtitle
   **/
   public function getSubName() {

      $value = $this->getParameterValue();
      return $this->getCriteriaLabel().' '.$this->getSign()." $value ".$this->unit;
   }


   function getSign() {

      if (empty($this->signe)) {
         return Toolbox::unclean_cross_side_scripting_deep($this->getParameter($this->getName()."_sign"));
      }
      return $this->signe;
   }


   /**
    * @see plugins/reports/inc/PluginReportsDropdownCriteria::getSqlCriteriasRestriction()
   **/
   function getSqlCriteriasRestriction($link='AND') {

      $param = $this->getParameterValue();
      return $link." ".$this->getSqlField().$this->getSign()."'".($param*$this->coef)."' ";
   }

}
?>