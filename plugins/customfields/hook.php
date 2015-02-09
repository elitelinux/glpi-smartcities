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
// Purpose of file: Code for hooks, etc.
// ----------------------------------------------------------------------

// ** DATABASE HOOKS ** //

/**
 * Define dropdown relations for use by GLPI
 *
 * @return Array Relations from the dropdowns
 */

function plugin_customfields_getDatabaseRelations()
{
   //TODO: add in relations for multiselects?
   global $DB;

   $plugin = new Plugin();

   $relations = array();
   $query     = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `entities` != ''
                   AND `deleted` = 0
                   AND `data_type` = 'dropdown'
             ORDER BY `itemtype`";
   $result    = $DB->query($query);

   while ($data = $DB->fetch_assoc($result)) {
      $relations[$data['dropdown_table']] = array(
         plugin_customfields_table($data['itemtype']) => $data['system_name']
      );
   }

   $entities = array();
   $query    = "SELECT `dropdown_table`
             FROM `glpi_plugin_customfields_dropdowns`
             WHERE `has_entities` = 1";
   $result   = $DB->query($query);

   while ($data = $DB->fetch_assoc($result)) {
      $entities[$data['dropdown_table']] = 'entities_id';
   }
   if (!empty($entities)) {
      $relations['glpi_entities'] = $entities;
   }
   return $relations;
}

// ** SEARCH HOOKS ** //

/**
 * Define search options for each device type that has custom fields.
 * 'Search options' are also used by GLPI for logging and mass updates.
 *
 * @param $itemtype Item type
 * @return array Search options
 */

function plugin_customfields_getAddSearchOptions($itemtype)
{
   global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES, $DB;

   //TODO: Rewrite this function, based on old code
   //--but note that logging appears to work w/o separate item
   $sopt = array();
   if (in_array($itemtype, $ACTIVE_CUSTOMFIELDS_TYPES)) {
      $query = "SELECT `glpi_plugin_customfields_fields`.*,
                       `glpi_plugin_customfields_dropdowns`.`is_tree`
                FROM `glpi_plugin_customfields_fields`
                LEFT JOIN `glpi_plugin_customfields_dropdowns`
                  ON `glpi_plugin_customfields_dropdowns`.`system_name`
                        = `glpi_plugin_customfields_fields`.`system_name`
                WHERE `glpi_plugin_customfields_fields`.`itemtype` = '$itemtype'
                        AND data_type != 'sectionhead'
                ORDER BY
                  `glpi_plugin_customfields_fields`.`sort_order`,
                  `glpi_plugin_customfields_fields`.`id`,
                  `glpi_plugin_customfields_fields`.`label`";

      $i = 5200;
      foreach ($DB->request($query) as $search) {
         /** 2 options created :
          * - one for search and displaypreference
          * - second for massive action
          **/

         $sopt[$i]['table']         = plugin_customfields_table($itemtype);
         $sopt[$i]['field']         = $search['system_name'];
         if (strpos($_SERVER['SCRIPT_NAME'], "datainjection/ajax/dropdownChooseField.php") === false) {
         	$sopt[$i]['linkfield']     = '';
         } else {
         	// linkfield needs to be filled only to make the custom field visible when 
         	// creating the data import template
         	$sopt[$i]['linkfield']     = $search['system_name'];
         }
         $sopt[$i]['name']          = __('Title','customfields')
            . " - " . $search['label'];
         $sopt[$i]['massiveaction'] = false;

         $sopt[$i]['injectable']    = true;

         if ($search['data_type'] == "general") {
            $opt[$i]['checktype']   = "text";
            $opt[$i]['displaytype'] = "text";
         }
         if ($search['data_type'] == "number") {
            $opt[$i]['checktype']   = "integer";
            $opt[$i]['displaytype'] = "integer";
         }
         if ($search['data_type'] == "yesno") {
            $opt[$i]['checktype']   = "bool";
            $opt[$i]['displaytype'] = "bool";
         }
         if ($search['data_type'] == "date") {
            $opt[$i]['checktype']   = "date";
            $opt[$i]['displaytype'] = "date";
         }
         if ($search['data_type'] == "money") {
            $opt[$i]['checktype']   = "float";
            $opt[$i]['displaytype'] = "decimal";
         }
         if ($search['data_type'] == "note") {
            $opt[$i]['checktype']   = "multiline_text";
            $opt[$i]['displaytype'] = "multiline_text";
         }
         if ($search['data_type'] == "text") {
            $opt[$i]['checktype']   = "multiline_text";
            $opt[$i]['displaytype'] = "multiline_text";
         }
         if ($search['data_type'] == "sectionhead") {
            $opt[$i]['injectable'] = false;
         }

         // No option for disable displaypreferences, check page executed

         if (strpos($_SERVER['SCRIPT_NAME'], "common.tabs.php") === false) {
            $sopt[$i + 2000]['table'] = plugin_customfields_table($itemtype);
            $sopt[$i + 2000]['field']     = $search['system_name'];
            $sopt[$i + 2000]['linkfield'] = $search['system_name'];
            $sopt[$i + 2000]['name']  = __('Title','customfields')
               . " - " . $search['label'];
            $sopt[$i + 2000]['nosearch']  = true;
            $sopt[$i + 2000]['nosort']    = true;
         }

         if ($search['data_type'] == "dropdown") {

            $sopt[$i]['table']      = 'glpi_plugin_customfields_dropdownsitems';
            $sopt[$i]['datatype']   = "dropdown";
            $sopt[$i]['displaytype'] = "dropdown";
            $sopt[$i]['checktype'] = "text";
            //$sopt[$i]['searchtype'] = "contains";
            $sopt[$i]['field']      = "name";
            $sopt[$i]['linkfield']  = $search['system_name'];
            $sopt[$i]['joinparams'] = array(
               'beforejoin' => array(
                  'table' => plugin_customfields_table($itemtype),
                  'joinparams' => array(
                     'jointype' => 'child'
                  )
               )
            );
         }
         $i++;
      }
   }

   return $sopt;

}

/**
 * Define how to join the tables when doing a search
 *
 * @see Search::addLeftJoin()
 */

function plugin_customfields_addLeftJoin(
   $itemtype,
   $ref_table,
   $new_table,
   $linkfield,
   &$already_link_tables
) {

   global $DB;
   
   $out = "";

   // Join data table
   
   $type_table = plugin_customfields_table($itemtype);

   if ($new_table == $type_table) {
      $out = " LEFT JOIN `$new_table`
                  ON (`$ref_table`.`id` = `$new_table`.`id`)";
      return $out;
   }

   // Join a custom dropdown

   $query  = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `dropdown_table` = '$new_table'
                   AND `itemtype` = '$itemtype'
                   AND `deleted` = 0
                   AND `entities` != ''";
   $result = $DB->query($query);
   $out    = "";

   if ($DB->numrows($result)) {
      $out .= addLeftJoin(
         $itemtype,
         $ref_table,
         $already_link_tables,
         $type_table,
         'id'
      );

      $out .= " LEFT JOIN `$new_table`
      ON (`$new_table`.`id` = `$type_table`.`$linkfield`) ";
      
   }

   return $out;

}

// ** VARIOUS HOOKS ** //

/**
 * Hook to process Mass Update & transfer
 *
 * @param $item Item
 * @return object unmodified item (we only update our custom fields)
 */

function plugin_pre_item_update_customfields($item)
{

   global $ACTIVE_CUSTOMFIELDS_TYPES;
   
   if (empty($ACTIVE_CUSTOMFIELDS_TYPES)) {
      return '';
   }
   
   // If update isn't set, then this is a mass update or transfer, not a regular update
   if (
      !isset($item->input['_already_called_']) &&
      in_array($item->getType(), $ACTIVE_CUSTOMFIELDS_TYPES)
   ) {

      // Instantiate custom field object

      $cf_itemtype      = getItemTypeForTable(
         plugin_customfields_table($item->getType())
      );

      $plugin_custfield = new $cf_itemtype;
      $plugin_custfield->update($item->input);

   }

   // return the original data, not our additional data
   
   return $item;

}

/**
 * Hook done on add item case
 * If in Auto Activate mode, add a record for the custom fields when a device
 * is added
 *
 * @param $obj Object to be added
 * @return bool Success
 */

function plugin_item_add_customfields($obj)
{
   global $DB, $ACTIVE_CUSTOMFIELDS_TYPES;

   $type = get_class($obj);
   $id   = $obj->fields['id'];
   
   if (CUSTOMFIELDS_AUTOACTIVATE && !empty($ACTIVE_CUSTOMFIELDS_TYPES)) {
      
      if (in_array($type, $ACTIVE_CUSTOMFIELDS_TYPES)) {
         $table  = plugin_customfields_table($type);
         $sql    = "INSERT INTO `$table`
                        (`id`)
                 VALUES ('" . intval($id) . "')";
         $result = $DB->query($sql);
         return ($result ? true : false);
      }
   }
   return false;
}


/**
 * Hook done on purge item case
 *
 * @param $parm Object to be purged
 * @return bool Success
 */

function plugin_item_purge_customfields($parm)
{
   global $ALL_CUSTOMFIELDS_TYPES, $DB;
   
   // Must delete custom fields when main item is purged, 
   // even if custom fields for this device are currently disabled
   if (
      in_array($parm->getType(), $ALL_CUSTOMFIELDS_TYPES)
      && ($table = plugin_customfields_table($parm->getType()))
   ) {

      $sql    = "DELETE FROM $table where id=" . $parm->getID();

      $DB->query($sql);

      return true;

   }

   return false;
}

/**
 * Display fields for massive actions
 *
 * @param array $options Massive Actions options
 * @return bool Success
 */

function plugin_customfields_MassiveActionsFieldsDisplay($options = array())
{
   global $DB;

   $type      = $options['itemtype'];
   $table     = $options['options']['table'];
   $field     = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];

   // Get configuration of the custom field

   $query  = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `itemtype` = '$type'
                   AND `system_name` = '$field'";
   $result = $DB->query($query);

   if ($data = $DB->fetch_assoc($result)) {

      switch ($data['data_type']) {
         case 'dropdown':
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
                  'name' => $data['system_name'],
                  'entity' => $_SESSION['glpiactive_entity']
               )
            );
            break;

         case 'yesno':
            dropdown::showYesNo($field, 0);
            break;

         case 'date':
            Html::showDateFormItem($field, '', true, true);
            break;

         case 'money':
            echo '<input type="text" size="16" value="'
               . Html::formatNumber(0, true)
               . '" name="'
               . $field
               . '"/>';
            break;

         default:
            $item = new $type;
            Html::autocompletionTextField($item, $field);
            break;
      }

      return true;

   }

   return false;

}

/**
 * Display items from the search.
 *
 * @see Search::giveItem()
 */

function plugin_customfields_giveItem($itemtype, $ID, $data, $num, $meta = 0)
{
   global $DB, $LANG;
   
   $searchopt =& Search::getOptions($itemtype);
   
   $NAME = "ITEM_";
   if ($meta) {
      $NAME = "META_";
   }
   
   if (!isset($data[$NAME . $num]))
      return;
   
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];
   $linkfield = $searchopt[$ID]["linkfield"];
   
   if (strpos($table, "glpi_plugin_customfields_dropdownsitems") !== false) {
      switch ($field) {
         case "plugin_customfields_dropdowns_id":
            return Dropdown::getDropdownName(
               "glpi_plugin_customfields_dropdowns",
               $data[$NAME . $num]
            );
            break;
         case "plugin_customfields_dropdownsitems_id":
            return Dropdown::getDropdownName(
               "glpi_plugin_customfields_dropdownsitems",
               $data[$NAME . $num]
            );
            break;
         case "name":
            return $data[$NAME . $num];
            break;
      }
   } elseif (strpos($table, "glpi_plugin_customfields_") !== false) {
      $query  = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `system_name` = '$field'";
      $result = $DB->query($query);
      
      if ($data_db = $DB->fetch_assoc($result)) {
         switch ($data_db['data_type']) {
            case 'dropdown':
               return Dropdown::getDropdownName(
                  "glpi_plugin_customfields_dropdownsitems",
                  $data[$NAME . $num]
               );
               break;
            
            case 'yesno':
               return Dropdown::getYesNo($data[$NAME . $num]);
               break;
            
            case 'date':
               Html::convDate($data[$NAME . $num]);
               return Html::convDate($data[$NAME . $num]);
               break;
            
            case 'money':
               return Html::formatNumber($data[$NAME . $num]);
               break;
            
            default:
               return $data[$NAME . $num];
               break;

         }

      }

   }

}

// ** PLUGIN TO PLUGIN COMPATIBILITY ** //

/**
 * Initialization of features related to other plugins
 * This method runs after initialization of all plugins   
 * 
 * 
 */

function plugin_customfields_postinit() {
   global $PLUGIN_HOOKS, $DB, $ALL_CUSTOMFIELDS_TYPES, $ACTIVE_CUSTOMFIELDS_TYPES;
   // $plugin = new Plugin();
   // if ($plugin->isInstalled('otherPlugin') && $plugin->isActivated('otherPlugin')) {
      
   // }

   $query  = "SELECT `itemtype`, `enabled`
                   FROM `glpi_plugin_customfields_itemtypes`
                   WHERE `itemtype` <> 'Version'";
   $result = $DB->query($query);
    
   while ($data = $DB->fetch_assoc($result)) {
      $ALL_CUSTOMFIELDS_TYPES[] = $data['itemtype'];
      if ($data['enabled']) {
         include('inc/virtual_classes.php');
         $ACTIVE_CUSTOMFIELDS_TYPES[] = $data['itemtype'];
         Plugin::registerClass('PluginCustomfields' . $data['itemtype'], array(
            'addtabon' => array(
               $data['itemtype']
            )
         ));
      }
   }
    
   // Hooks for add item, update item (for active types)

   foreach ($ACTIVE_CUSTOMFIELDS_TYPES as $type) {
      $PLUGIN_HOOKS['item_add']['customfields'][$type] =
         'plugin_item_add_customfields';
      $PLUGIN_HOOKS['pre_item_update']['customfields'][$type] =
         'plugin_pre_item_update_customfields';
   }

   // Hooks for purge item
   
   foreach ($ALL_CUSTOMFIELDS_TYPES as $type) {
      $PLUGIN_HOOKS['item_purge']['customfields'][$type] =
        'plugin_item_purge_customfields';
   }

}

// ** SETUP HOOKS ** //

/**
 * Install Custom fields plugin
 *
 * @return bool Success
 */

function plugin_customfields_install()
{
   include_once(GLPI_ROOT . "/plugins/customfields/inc/install.function.php");
   return pluginCustomfieldsInstall();
}

/**
 * Uninstall custom fields plugin
 *
 * @return bool Success
 */

function plugin_customfields_uninstall()
{
   include_once(GLPI_ROOT . "/plugins/customfields/inc/install.function.php");
   return pluginCustomfieldsUninstall();
}