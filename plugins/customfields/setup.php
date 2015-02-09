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
// Purpose of file: Used to initialize the plugin and define its actions.
// ----------------------------------------------------------------------

define ("PLUGIN_CUSTOMFIELDS_VERSION", "1.7");

// Minimal GLPI version, inclusive
define ("PLUGIN_CUSTOMFIELDS_GLPI_MIN_VERSION", "0.85");
// Maximum GLPI version, exclusive
define ("PLUGIN_CUSTOMFIELDS_GLPI_MAX_VERSION", "0.86");

// If auto activate set to true, custom fields will be automatically
// added when a new record is inserted. If set to false, users must
// click 'Activate custom fields' to add additional information.
define('CUSTOMFIELDS_AUTOACTIVATE', true);

// This is the last version that any tables changed.  This version may be
// older than the plugin version if there were no changes db changes.
define('CUSTOMFIELDS_DB_VERSION_REQUIRED', 160); // 1.6

global $ACTIVE_CUSTOMFIELDS_TYPES, $ALL_CUSTOMFIELDS_TYPES;
$ACTIVE_CUSTOMFIELDS_TYPES = array();
$ALL_CUSTOMFIELDS_TYPES    = array();

include_once('inc/function.php');
include_once('inc/install.function.php');
include_once('inc/itemtype.class.php');
include_once('inc/profile.class.php');
include_once('inc/dropdown.class.php');

/**
 * Initialize the plugin's hooks
 */

function plugin_init_customfields()
{
   global $PLUGIN_HOOKS, $DB, $ACTIVE_CUSTOMFIELDS_TYPES,
          $ALL_CUSTOMFIELDS_TYPES;
   
   $PLUGIN_HOOKS['csrf_compliant']['customfields'] = true;
   
   $PLUGIN_HOOKS['change_profile']['customfields'] = array(
      'PluginCustomfieldsProfile',
      'changeprofile'
   );
   
   // Register classes

   Plugin::registerClass('PluginCustomfieldsDropdowns');
   Plugin::registerClass('PluginCustomfieldsFields');
   
   if (isset($_SESSION['glpiID'])) {
      $plugin = new Plugin();
      
      if ($plugin->isInstalled("customfields") && $plugin->isActivated("customfields")) {
         // enable a tab for reading / setting access rights for the plugin
         Plugin::registerClass('PluginCustomfieldsProfile', 
            array('addtabon' => 'Profile')
         );
               
         // Display a menu entry in the main menu if the user has
         // configuration rights

         if (Session::haveRight('config', UPDATE)) {
//             $PLUGIN_HOOKS['menu_entry']['customfields'] = true;
         	 $PLUGIN_HOOKS["menu_toadd"]['customfields'] = array('plugins'  => 'PluginCustomfieldsConfig');
         }
          
         // initiate empty dropdowns
         $PLUGIN_HOOKS['item_empty']['customfields'] = array(
            'PluginCustomfieldsDropdownsItem' =>
            'PluginCustomfieldsDropdownsItem::item_empty'
         );
      }
      
      // Indicate where the configuration page can be found
      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['customfields'] = 'front/config.form.php';
      }
      
      // Hook for initialization after initialization of all other plugins
      $PLUGIN_HOOKS['post_init']['customfields'] = 'plugin_customfields_postinit';
      
   }
}

/**
 * Get the name and the version of the plugin
 *
 * @return array Version information
 */

function plugin_version_customfields()
{
   global $LANG;
   return array(
      'name' => __('Title','customfields'),
      'author' => 'Oregon State Data Center, Nelly Mahu Lasson, Dennis Ploeger, Dethegeek',
      'license' => 'GPLv2+',
      'homepage' => 'https://forge.indepnet.net/projects/show/customfields',
      'minGlpiVersion' => PLUGIN_CUSTOMFIELDS_GLPI_MIN_VERSION,
      'version' => PLUGIN_CUSTOMFIELDS_VERSION
   );
}

/**
 * Checks prerequisites before install. May print errors or add message after
 * redirect
 *
 * @return bool Success
 */

function plugin_customfields_check_prerequisites()
{
   if (version_compare(GLPI_VERSION, PLUGIN_CUSTOMFIELDS_GLPI_MIN_VERSION, 'ge') && version_compare(GLPI_VERSION, PLUGIN_CUSTOMFIELDS_GLPI_MAX_VERSION, 'lt')) {

      return true;

   } else {

      echo "This plugin requires GLPI >= " . PLUGIN_CUSTOMFIELDS_GLPI_MIN_VERSION . " and < " . PLUGIN_CUSTOMFIELDS_GLPI_MAX_VERSION;
      return false;
      
   }
}

/**
 * We skip this configuration test
 *
 * @return bool always True
 */

function plugin_customfields_check_config()
{
   return true;
}