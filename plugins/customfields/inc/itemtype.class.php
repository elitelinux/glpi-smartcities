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
// Purpose of file: Customfields itemtype configuration
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

/**
 * Class PluginCustomfieldsItemtype
 *
 * Customfields itemtype configuration
 */

class PluginCustomfieldsItemtype extends CommonDBTM
{

   /**
    * Constructor. Adds itemtype and various initialisations.
    *
    * @param string $itemtype
    */

   function __construct($itemtype = "")
   {
      $this->type      = $itemtype;
      $this->dohistory = true;
      $this->forceTable(plugin_customfields_table($itemtype));
   }

   /**
    * Check access restrictions to this item
    *
    * @param $itemtype
    * @return bool
    */

   function getRestricted($itemtype)
   {
      global $DB;
      
      // TODO : This method is useless because restrictions has been disabled when
      // porting to GLPI 0.84
      
      // Something is probably wrong with this method :
      // if an itemtype has several restricted fields
      // then only the first field column name (in DB)
      // is fetched. 
      
      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `restricted` = 1";
      
      if ($result = $DB->query($query)) {
         if ($data = $DB->fetch_assoc($result)) {
            $right = $itemtype . "_" . $data['system_name'];
         }
      }
      $query = "SELECT *
                FROM `glpi_plugin_customfields_profiles`
                WHERE `$right` = 'w'";
      
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            return Session::haveRight($itemtype, 'w');
         }
      }
      
   }
   
   /**
    * Register a new item type brought by an other plugin
    *
    * @param $itemtype
    * 
    */
   static function registerItemtype($itemType)
   {
      global $DB, $ALL_CUSTOMFIELDS_TYPES;
      
      // Check if the new itemtype has already been registered
      $query  = "SELECT `enabled`
                 FROM `glpi_plugin_customfields_itemtypes`
                 WHERE `itemtype` = '$itemType'";
      $result = $DB->query($query);
      
      if ($DB->numrows($result) == 0) {
         
         // The new item type needs to be registered
         $enabled = 0;
         $query  = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                         (`itemtype`,`enabled`)
                    VALUES ('$itemType', '$enabled')";
         
         $result = $DB->query($query);
          
      }
      $ALL_CUSTOMFIELDS_TYPES[] = $itemType;
   }
   
   /**
    * Unregister an item type brought by an other plugin
    *
    * @param $itemtype
    * 
    */
   static function unregisterItemType($itemType)
   {
      global $DB;
      
      // Check if the itemtype has already been registered
      $query  = "SELECT `enabled`
      FROM `glpi_plugin_customfields_itemtypes`
      WHERE `itemtype` = '$itemType'";
      $result = $DB->query($query);
      
      if ($DB->numrows($result) != 0) {
         
         // The itemtype must be disabled before unregistration
         //  Has no effect if the itemtype is not enabled
         plugin_customfields_disable_device($itemType);
          
         // The new item type needs to be unregistered
         
         // Remove the fields definition for the itemtype being deleted
         $query  = "DELETE FROM `glpi_plugin_customfields_fields`
                    WHERE `itemtype` = '$itemType'";
          
         $result = $DB->query($query) or die($DB->error());;
      
         // Remove the itemtype from the itemtypes supported by the plugin 
         $query  = "DELETE FROM `glpi_plugin_customfields_itemtypes`
                    WHERE `itemtype` = '$itemType'";
          
         $result = $DB->query($query) or die($DB->error());;
         
         // Remove all data for the itemtype
         $table       = plugin_customfields_table($itemType);
         if ($table) {
            $query = "DROP TABLE IF EXISTS `$table`";
            $DB->query($query) or die($DB->error());
         }
         
         // Unregister the itemtype
         $query  = "DELETE FROM `glpi_plugin_customfields_itemtypes`
         WHERE `itemtype` = '$itemType'";
         $DB->query($query) or die($DB->error());
          
      }
   }

}