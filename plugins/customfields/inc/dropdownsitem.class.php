<?php

/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org/
----------------------------------------------------------------------

LICENSE

This file is part of GLPI.

GLPI is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Handling of dropdown-items
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

/**
 * Class PluginCustomfieldsDropdownsItem
 *
 * Items for custom drop downs
 */

class PluginCustomfieldsDropdownsItem extends CommonTreeDropdown
{

   /**
    * @see CommonDBTM::canView()
    */

   static function canView() {
      return true;
   }

   /**
    * @see CommonDBTM::canCreate()
    */

   static function canCreate() {
      return true;
   }

   /**
    * @see CommonGLPI::getFormURL()
    */

   static function getFormURL($full = true) {
      global $CFG_GLPI;
      
      $dir = ($full ? $CFG_GLPI['root_doc'] : '');
      $dir .= "/plugins/customfields";
      $get = "";
      
      if (isset($_GET['popup'])) {
         $get = "?popup=" . $_GET['popup'];
      }
      
      return "$dir/front/dropdownsitem.form.php$get";
   }

   /**
    * @see CommonGLPI::defineTabs()
    */

   function defineTabs($options = array()) {
      global $LANG;
      
      return array();
   }

   /**
    * Handle item_empty-hook
    *
    * @param CommonDBTM $item Item to work on
    */

   static function item_empty(CommonDBTM $item) {
      global $_REQUEST;

      if (isset($_REQUEST['name'])) {
         $item->fields['name'] = $_REQUEST['name'];
      }
   }

   /**
    * @see CommonDropdown::displaySpecificTypeField()
    */

   function displaySpecificTypeField($ID, $field = array()) {
      
      switch ($field['type']) {

         case "plugin_customfields_dropdowns_id";

            // We have a dropdown.

            if (isset($_REQUEST['plugin_customfields_dropdowns_id'])) {
               $ID = $_REQUEST['plugin_customfields_dropdowns_id'];
            } elseif (isset($this->fields[$field['name']])) {
               $ID = $this->fields[$field['name']];
            } else
               $ID = -1;
            
            Dropdown::show(
               getItemTypeForTable(
                  getTableNameForForeignKeyField($field['name'])
               ),
               array(
                  'value' => $ID,
                  'name' => $field['name'],
                  'entity' => $this->getEntityID(),
                  'auto_submit' => true
               )
            );

            break;

         case "plugin_customfields_dropdownsitems_id";

            // We have a dropdown item

            $condition = "plugin_customfields_dropdowns_id = -1";

            if (isset($_REQUEST['plugin_customfields_dropdowns_id'])) {

               $condition = "plugin_customfields_dropdowns_id = '"
                  . $_REQUEST['plugin_customfields_dropdowns_id']
                  . "'";
            }
            if ($field['name'] == 'entities_id') {

               $restrict = -1;

            } else {

               $restrict = $this->getEntityID();

            }

            Dropdown::show(
               getItemTypeForTable($this->getTable()),
               array(
                  'value' => $this->fields[$field['name']],
                  'name' => $field['name'],
                  'comments' => false,
                  'entity' => $restrict,
                  'used' => (
                     $ID > 0
                        ? getSonsOf($this->getTable(), $ID)
                        : array()),
                  'condition' => $condition
               )
            );
            break;

      }

   }

   /**
    * @see CommonGLPI::getTypeName()
    */

   static function getTypeName($nb = 0) {
      global $LANG;
      
      return __('Add Custom Dropdown','customfields');

   }

   /**
    * @see CommonDropdown::getAdditionalFields()
    */

   function getAdditionalFields() {

      global $LANG;
      
      $fields   = array();

      // Add dropdowns

      $fields[] = array(
         'name' => 'plugin_customfields_dropdowns_id',
         'label' => __('Custom Dropdown','customfields'),
         'type' => 'plugin_customfields_dropdowns_id',
         'list' => false
      );

      // Add dropdown items

      $fields[] = array(
         'name' => 'plugin_customfields_dropdownsitems_id',
         'label' => __('As child of'),
         'type' => 'plugin_customfields_dropdownsitems_id',
         'list' => false
      );
      
      return $fields;
      
   }

   /**
    * @see CommonDBTM::getSearchOptions()
    */

   function getSearchOptions() {
      global $LANG;
      
      $tab = parent::getSearchOptions();
      
      $tab[3]['table'] = 'glpi_plugin_customfields_dropdownsitems';
      $tab[3]['field'] = 'plugin_customfields_dropdowns_id';
      $tab[3]['name']  = __('Custom Dropdown','customfields');
      
      $tab[4]['table'] = 'glpi_plugin_customfields_dropdownsitems';
      $tab[4]['field'] = 'plugin_customfields_dropdownsitems_id';
      $tab[4]['name']  = __('As child of');
      
      return $tab;
   }

   /**
    * @see CommonDBTM::prepareInputForAdd()
    */

   function prepareInputForAdd($input) {
      global $LANG;
      
      // Check for mandatory fields

      $mandatory_ok = true;
      
      if
         (!isset($input["plugin_customfields_dropdowns_id"]) ||
         empty($input["plugin_customfields_dropdowns_id"]))
      {

         Session::addMessageAfterRedirect(
            __('You have not selected Dropdown','customfields'),
            false,
            ERROR
         );

         $mandatory_ok = false;

      }
      
      if (!$mandatory_ok) {

         return false;

      }
      
      return parent::prepareInputForAdd($input);

   }

}