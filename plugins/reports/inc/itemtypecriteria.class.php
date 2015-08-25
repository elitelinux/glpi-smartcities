<?php
/**
 * @version $Id: itemtypecriteria.class.php 294 2015-05-24 23:46:03Z yllen $
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
class PluginReportsItemTypeCriteria extends PluginReportsDropdownCriteria {

   private $types = array();


   /**
    * @param $report
    * @param $name            (default 'itemtype')
    * @param $label           (default '')
    * @param $types     array
    * @param $ignored   array
   **/
   function __construct($report, $name='itemtype', $label='', $types=array(), $ignored=array()) {
      global $CFG_GLPI;

      parent::__construct($report, $name, NOT_AVAILABLE, ($label ? $label : __('Item type')));

      if (is_array($types) && count($types)) {
         // $types is an hashtable of itemtype => display name
         $this->types = $types;
      } else if (is_string($types) && isset($CFG_GLPI[$types])) {
         // $types is the name of an configured type hashtable (infocom_types, doc_types, ...)
         foreach($CFG_GLPI[$types] as $itemtype) {
            if (($item = getItemForItemtype($itemtype)) && !in_array($itemtype, $ignored)) {
               $this->types[$itemtype] = $item->getTypeName();
            }
         }
         $this->types['all'] = __('All');
      } else {
         // No types, use helpdesk_types
         $this->types     = Ticket::getAllTypesForHelpdesk();
         $this->types['all'] = __('All');
      }
   }


   function getSubName() {

      $itemtype = $this->getParameterValue();
      if ($itemtype && ($item = getItemForItemtype($itemtype))) {
         $name = $item->getTypeName();
      } else {
         // All
         return '';
      }
      return " " . $this->getCriteriaLabel() . " : " . $name;
   }


   public function displayDropdownCriteria() {
      ksort($this->types);

      Dropdown::showFromArray($this->getName(), $this->types,
                              array('value'=> $this->getParameterValue()));
   }

}
?>