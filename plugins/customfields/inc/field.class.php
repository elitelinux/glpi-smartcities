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
// Purpose of file: Handling of custom fields.
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

/**
 * Class PluginCustomfieldsField
 * 
 * Handling of custom fields
 */

class PluginCustomfieldsField extends CommonDBTM
{
	
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuContent() {
   	  return 'Custom Fields';
      return __('Custom Fields');
   }

   /**
    * @see CommonDBTM::getTabNameForItem()
    */

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES;

      if (in_array($item->getType(), $ACTIVE_CUSTOMFIELDS_TYPES)) {

         return __('Title','customfields');

      }
      
      return "";
   }

   /**
    * @see CommonDBTM::displayTabContentForItem
    */

   static function displayTabContentForItem(
      CommonGLPI $item,
      $tabnum = 1,
      $withtemplate = 0
   ) {
      global $ACTIVE_CUSTOMFIELDS_TYPES;
      
      $itemType = $item->getType();

      if (in_array($itemType, $ACTIVE_CUSTOMFIELDS_TYPES)) {

         $customFieldsItemType = "PluginCustomfields" . $itemType;
         $customFieldsItem     = new $customFieldsItemType();
         $ID                   = $item->getField("id");

         $customFieldsItem->showForm($ID);

      }

      return true;
   }

   /**
    * Show form for item (used by overriding virtual classes)
    *
    * @param $id ID of customfield
    * @param array $options Addtional options
    * @return bool Success
    */

   function showForm($id, $options = array())
   {
      global $LANG, $CFG_GLPI, $DB;

      // ACL check

      if (!Session::haveRight("profile", READ)) {
         //return false;
      }
      
      $associatedItemType = $this->associatedItemType();
      $canread = $associatedItemType::canView();
      $canedit = $associatedItemType::canUpdate();
      
      if ($canread != true) {
         return false;
      }
      
      // Set target
      
      $target = $CFG_GLPI["root_doc"]
         . "/plugins/customfields/front/field.form.php";

      if (isset($options['target'])) {

         $target = $options['target'];

      }
      
      $itemType           = $this->getType();
      $table              = $itemType::getTable();
      
      $sql    = "SELECT *
	  		    FROM `$table`
	            WHERE `id` = $id";
      $result = $DB->query($sql);
      
      $associatedItemCustomValues = $DB->fetch_assoc($result);
      
      $DB->free_result($result);

      $associatedTable = $associatedItemType::getTable();
      $entity = 0;
      if (!in_array($associatedItemType, array('ComputerDisk', 'NetworkPort', 'Entity', 'SoftwareVersion', 'SoftwareLicense'))) {
         $query = "SELECT entities_id
                   FROM $associatedTable WHERE id= '$id'";
         $result = $DB->query($query);
         if ( $result != false ) {
            $number = $DB->numrows($result);
            if ($number == 1) {
               $data = $DB->fetch_array($result);
               $entity = $data['entities_id'];
            }
         }
      }
      $field_uses = false;

      // Select customfield configuration

      $sql = "SELECT `label`, `system_name`, `data_type`, `default_value`,
                     `entities`
              FROM `glpi_plugin_customfields_fields`
              WHERE `deleted` = '0' AND `itemtype` = '$associatedItemType' 
              ORDER BY `sort_order` ASC, `label` ASC";

      $result             = $DB->query($sql);
      $currentSectionName = '';
      
      echo "<form action='" . $target . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      while ($data = $DB->fetch_assoc($result)) {
         if ($data['entities'] != '*') {
            $entities = explode(',', $data['entities']);
            // don't process the field if it shouldn't be shown for this entity
            if (!in_array($entity, $entities)) {
               continue;
            }
         }
         switch ($data['data_type']) {

            case 'sectionhead':

               // Display section header

               $currentSectionName = $data['label'];
               echo "<tr><th colspan='2' class='center b'>" . $currentSectionName;
               echo "</th></tr>";
               break;

            default:
                
               $field_uses = true;
               // Label

               if ($currentSectionName == '') {
                  $currentSectionName = "&nbsp;";
                  echo "<tr><th colspan='2' class='center b'>"
                     . $currentSectionName;
                  echo "</th></tr>";
               }

               $fieldName         = $data['system_name'];

               echo "<tr><td>" . $data['label'] . "</td><td>";

               // Check readonly and restricted
               
               $readonly = false;

               if (
                  (array_key_exists('restricted', $data)) &&
                  ($data['restricted'])
               ) {

                  $checkfield = $data['itemtype'] . '_' . $data['system_name'];

                  $prof       = new pluginCustomfieldsProfile();

                  if (!$prof->fieldHaveRight($checkfield, 'r')) {

                     // User has no access right. Skip the field.

                     continue;

                  }

                  if (!$prof->fieldHaveRight($checkfield, 'w')) {

                     // User has read, but not write right. Set the field to
                     // readonly

                     $readonly = true;

                  }

               }

               // The current value comes from the data table
               
               if ($data['data_type'] != 'sectionhead') {
                  $value = $associatedItemCustomValues[$fieldName];
               }

               // Display input widgets based on the data type
               
               switch ($data['data_type']) {

                  case 'general':

                     # Single line input

                     if (!$readonly) {

                        echo '<input type="text" size="20" value="'
                           . $value
                           . '" name="'
                           . $fieldName
                           . '"/>';

                     } else {

                        plugin_customfields_showValue($value);

                     }

                     break;
                  
                  case 'dropdown':

                     # Dropdown

                     if (!$readonly) {

                        $dropdown_obj = new PluginCustomfieldsDropdown;
                        $tmp          = $dropdown_obj->find(
                           "system_name = '" . $data['system_name'] . "'"
                        );
                        $dropdown     = array_shift($tmp);
                        
                        Dropdown::show(
                           'PluginCustomfieldsDropdownsItem',
                           array(
                              'condition' => $dropdown['id']
                                 . " = plugin_customfields_dropdowns_id",
                              'name' => $fieldName,
                              'value' => $value,
                              'entity' => $_SESSION['glpiactive_entity']
                           )
                        );
                     }

                     break;
                  
                  case 'date':

                     # Date input

                     $editcalendar = !$readonly;

                     Html::showDateFormItem(
                        $fieldName,
                        $value,
                        true,
                        $editcalendar
                     );

                     break;
                  
                  case 'money':

                     # Money input

                     if (!$readonly) {

                        echo '<input type="text" size="16" value="'
                           . Html::formatNumber($value,true)
                           . '" name="'
                           . $fieldName
                           . '"/>';

                     } else {

                        plugin_customfields_showValue(
                           Html::formatNumber($value, true)
                        );

                     }

                     break;
                  
                  case 'yesno':

                     # Checkbox

                     if (!$readonly) {

                        Dropdown::showYesNo($fieldName, $value);

                     } else {

                        plugin_customfields_showValue(
                           Dropdown::getYesNo($fieldName, $value)
                        );

                     }

                     break;
                  
                  case 'notes':

                     # Multiline input

                     if (!$readonly) {

                        echo '<textarea name="'
                           . $fieldName
                           . '" rows="4" cols="35">'
                           . $value
                           . '</textarea>';

                     } else {

                        plugin_customfields_showValue(
                           $value,
                           'height:6em;width:23em;'
                        );

                     }

                     break;
                  
                  case 'number':

                     # Number

                     if (!$readonly) {

                        echo '<input type="text" size="10" value="'
                           . $value
                           . '" name="'
                           . $fieldName
                           . '"/>';

                     } else {

                        plugin_customfields_showValue($value);

                     }

                     break;

                  case 'text':
							
                     # Textarea

                     if (!$readonly) {

                        echo '<textarea name="'
                           . $fieldName
                           . '" rows="4" cols="35">'
                           . $value
                           . '</textarea>';

                     } else {

                        plugin_customfields_showValue(
                           $value,
                           'height:6em;width:23em;'
                        );

                     }

                     break;
               }
               
               echo "</td></tr>";

         }

      }

      $DB->free_result($result);
      
      if ($field_uses) {
         if ($canedit) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center' colspan='2'>";
            echo "<input type='hidden' name='id' value='$id'>";
            echo "<input type='hidden' name='customfielditemtype'
               value='$itemType'>";
            echo "<input type='submit' name='update_customfield' value='"
               . _sx('button', 'Save')
               . "' class='submit'>";
            echo "</td></tr>";
         }
      } else {
         echo __('No fields available','customfields');
      }
              
      echo "</table>";
      Html::closeForm();

   }

   /**
    * @see CommonDBTM::post_addItem()
    */

   function post_addItem()
   {
      
      // Just call post_updateitem, because custom fields are not really
      // "added"
      
      $this->post_updateItem();
   }

   /**
    * Add History Log after updating a custom field
    *
    * @see CommonDBTM::post_updateItem()
    */
   
   function post_updateItem($history = 1)
   {
      
      $oldvalues = array();
      $newvalues = array();
      
      foreach ($this->updates as $field) {
         
         $oldvalues = $field . " (" . $this->oldvalues[$field] . ")";
         $newvalues = $field . " (" . $this->fields[$field] . ")";
         
         Log::history(
            $this->fields["id"],
            $this->associatedItemType(), 
            array(
               0,
               $oldvalues,
               $newvalues
            ),
            0, 
            Log::HISTORY_UPDATE_SUBITEM
         );

      }
      
   }

}