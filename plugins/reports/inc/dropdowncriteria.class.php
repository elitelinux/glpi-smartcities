<?php
/**
 * @version $Id: dropdowncriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
 * Manage criterias from dropdown tables
 */
class PluginReportsDropdownCriteria extends PluginReportsAutoCriteria {

   // TODO review this to use itemtype class as primary option
   //Drodown table
   private $table = "";

   //Should display dropdown's childrens value
   private $childrens = false;

   ///Use entity restriction in the dropdown ? (default is current entity)
   private $entity_restrict = -1;

   //Display dropdown comments
   private $displayComments = false;

   // search for zero if true, else treat zero as "all" (no criteria)
   private $searchzero = false;

   // For special condition
   private $condition = '';


   /**
    * @param $report
    * @param $name
    * @param $tableortype  (default '')
    * @param $label        (default '')
    * @param $condition    (default '')
   **/
   function __construct($report, $name, $tableortype='', $label='', $condition='') {

      parent::__construct($report, $name, $name, $label);

      $this->condition = $condition;

      if (empty($tableortype)) {
         $this->table = getTableNameForForeignKeyField($name);

      } else if (preg_match("/^glpi_/", $tableortype)) {
         $this->table = $tableortype;

      } else if ($tableortype == NOT_AVAILABLE) {
         $this->table = NOT_AVAILABLE;

      } else {
         $this->table = getTableForItemType($tableortype);
      }
   }


   /**
    * Get criteria's related table
   **/
   public function getTable() {
      return $this->table;
   }


   /**
    * Get criteria's related table
   **/
   public function getItemType() {
      return getItemTypeForTable($this->table);
   }


   /**
    * Will display dropdown childrens (in table in hierarchical)
   **/
   public function setWithChildrens() {
      global $CFG_GLPI;

      //if (in_array($this->getTable(), $CFG_GLPI["dropdowntree_tables"])) {
         // TODO find a solution to check is children exists
      $this->childrens = true;
      //}
   }


   /**
    * Will display dropdown childrens (in table in hierarchical)
   **/
   public function setSearchZero() {
      $this->searchzero = true;
   }


   /**
    * Set default criteria value to 0 and entity restriction to current entity only
   **/
   public function setDefaultValues() {

      $this->addParameter($this->getName(), 0);
      $this->setEntityRestriction($_SESSION["glpiactive_entity"]);
      $this->setDisplayComments();
   }


   /**
    * Show dropdown comments (enable by defaults)
   **/
   public function setDisplayComments() {
      $this->displayComments = true;
   }


   /**
    * Hide dropdown comments
   **/
   public function setNoDisplayComments () {
      $this->displayComments = false;
   }


   /**
    * Get display comments status
   **/
   public function getDisplayComments() {
      return $this->displayComments;
   }


   /**
    * Change criteria's label
    *
    * @param label   the new label to display
    * @param name    the name of the criteria whose label should be changed
    *                (if no name is provided, the default criteria will be used)
    *                (default '')
   **/
   public function setCriteriaLabel ($label, $name='') {

      if ($name == '') {
         $this->criterias_labels[$this->name] = $label;
      } else {
         $this->criterias_labels[$name] = $label;
      }
   }


   /**
    * Change entity restriction
    *
    * @param $restriction
    * Values are :
    * REPORTS_NO_ENTITY_RESTRICTION : no entity restriction (everything is displayed)
    * REPORTS_CURRENT_ENTITY : only values from the current entity
    * REPORTS_SUB_ENTITIES : values from the current entity + sub-entities
   **/
   public function setEntityRestriction($restriction) {
      global $CFG_GLPI;

      switch ($restriction) {
         case REPORTS_NO_ENTITY_RESTRICTION :
            $this->entity_restrict = -1;
            break;

         case REPORTS_CURRENT_ENTITY :
            $this->entity_restrict = $_SESSION["glpiactive_entity"];
            break;

         case REPORTS_SUB_ENTITIES :
            $this->entity_restrict = getSonsOf('glpi_entities',$_SESSION["glpiactive_entity"]);
            break;
      }
   }


   /**
    * Get entity restrict status
   **/
   public function getEntityRestrict() {
      return $this->entity_restrict;
   }


   /**
    * Get criteria's subtitle
   **/
   public function getSubName() {

      $value = $this->getParameterValue();
      if ($value) {
         return $this->getCriteriaLabel()." : ".Dropdown::getDropdownName($this->getTable(), $value);
      }

      if ($this->searchzero) {
         // zero
         return sprintf(__('%1$s: %2$s'), $this->getCriteriaLabel(), __('None'));
      }

      // All
      return '';
   }


   /**
    * Display criteria in the criteria's selection form
   **/
   public function displayCriteria() {

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      $this->displayDropdownCriteria();
      $this->getReport()->endColumn();
   }


   /**
    * Display dropdown
   **/
   public function displayDropdownCriteria() {

      $options = array('name'     => $this->getName(),
                       'value'    => $this->getParameterValue(),
                       'comments' => $this->getDisplayComments(),
                       'entity'   => $this->getEntityRestrict());

      if ($this->condition) {
         $options['condition'] = $this->condition;
      }
      Dropdown::show($this->getItemType(), $options);
   }


   /**
    * Get SQL code associated with the criteria
    *
    * @see plugins/reports/inc/PluginReportsAutoCriteria::getSqlCriteriasRestriction()
   **/
   public function getSqlCriteriasRestriction($link='AND') {

      if ($this->getParameterValue() || $this->searchzero) {
         if (!$this->childrens) {
            return $link . " " . $this->getSqlField() . "='" . $this->getParameterValue() . "' ";
         }
         if ($this->getParameterValue()) {
            return $link . " " . $this->getSqlField() .
                   " IN (" . implode(',', getSonsOf($this->getTable(),
                                                    $this->getParameterValue())) . ") ";
         }
         // 0 + its child means ALL
      }
      // Zero => means ALL => no criteria
      return '';
   }

}
?>