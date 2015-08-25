<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the simcard plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Simcard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   simcard
 @author    the simcard plugin team
 @copyright Copyright (c) 2010-2011 Simcard plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define ("PLUGIN_SIMCARD_VERSION", "1.4");

// Minimal GLPI version, inclusive
define ("PLUGIN_SIMCARD_GLPI_MIN_VERSION", "0.85");
// Maximum GLPI version, exclusive
define ("PLUGIN_SIMCARD_GLPI_MAX_VERSION", "0.86");

// Init the hooks of the plugins -Needed
function plugin_init_simcard() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
    
   $PLUGIN_HOOKS['csrf_compliant']['simcard'] = true;
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('simcard') && $plugin->isActivated('simcard')) {
      
      //load changeprofile function
   	$PLUGIN_HOOKS['change_profile']['simcard']   = array('PluginSimcardProfile','changeProfile');
      
      $PLUGIN_HOOKS['assign_to_ticket']['simcard'] = true;

      $PLUGIN_HOOKS['pre_item_purge']['simcard'] =
         array('Profile' => array('PluginSimcardsProfile', 'purgeProfiles'));
      $PLUGIN_HOOKS['plugin_datainjection_populate']['simcard']
         = 'plugin_datainjection_populate_simcard';
      $PLUGIN_HOOKS['item_purge']['simcard'] = array();
      
      foreach (PluginSimcardSimcard_Item::getClasses() as $type) {
         $PLUGIN_HOOKS['item_purge']['simcard'][$type] = 'plugin_item_purge_simcard';
       }
       
      Plugin::registerClass('PluginSimcardSimcard_Item',
                            array('addtabon' => PluginSimcardSimcard_Item::getClasses()));
      Plugin::registerClass('PluginSimcardProfile',
                            array('addtabon' => 'Profile'));
                            
      // Params : plugin name - string type - number - class - table - form page
      Plugin::registerClass('PluginSimcardSimcard',
                            array('linkgroup_types'        => true,
                                  'linkuser_types'         => true,
                                  'document_types'         => true,
                                  'contract_types'         => true,
                                  'ticket_types'           => true,
                                  'helpdesk_visible_types' => true,
                                  'infocom_types'          => true,
                                  'unicity_types'          => true,
                                  'reservation_types'      => true));
       array_push($CFG_GLPI['state_types'], 'PluginSimcardSimcard');
      //if glpi is loaded
      if (Session::getLoginUserID()) {
          
         // Display a menu entry ?
         //if (Session::haveRight("simcard", "r")) {
         if (PluginSimcardSimcard::canCreate() 
            || PluginSimcardSimcard::canUpdate ()
            || PluginSimcardSimcard::canDelete()
            || PluginSimcardSimcard::canView())
         {
            //menu entry
            //$PLUGIN_HOOKS['menu_entry']['simcard'] = 'front/simcard.php';
         	$PLUGIN_HOOKS['menu_toadd']['simcard'] = array('assets' => 'PluginSimcardSimcard');
            //search link
            //$PLUGIN_HOOKS['submenu_entry']['simcard']['options']['simcard']['links']['search']
            //   = '/plugins/simcard/front/simcard.php';
            //add simcard to items details
            $PLUGIN_HOOKS['headings']['simcard']           = 'plugin_get_headings_simcard';
            $PLUGIN_HOOKS['headings_action']['simcard']    = 'plugin_headings_actions_simcard';
            $PLUGIN_HOOKS['headings_actionpdf']['simcard'] = 'plugin_headings_actionpdf_simcard';
         }
             
         //if (Session::haveRight("simcard", "w")) {
         if (PluginSimcardSimcard::canCreate()) {
            //add link
//             $PLUGIN_HOOKS['submenu_entry']['simcard']['options']['simcard']['links']['add']
//                = '/front/setup.templates.php?itemtype=PluginSimcardSimcard&add=1';
//             $PLUGIN_HOOKS['submenu_entry']['simcard']['options']['simcard']['links']['template']
//                = '/front/setup.templates.php?itemtype=PluginSimcardSimcard&add=0';
            
            //use massiveaction in the plugin
            $PLUGIN_HOOKS['use_massive_action']['simcard'] = 1;
         }

          
         // Import from Data_Injection plugin
         $PLUGIN_HOOKS['migratetypes']['simcard']             = 'plugin_datainjection_migratetypes_simcard';
         $PLUGIN_HOOKS['menu']['simcard']                     = true;
         $PLUGIN_HOOKS['post_init']['simcard']                = 'plugin_simcard_postinit';
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_simcard() {
   global $LANG;

   $author = "<a href='http://www.elsendero.es'>El Sendero</a>";
   $author.= ", <a href='http://www.teclib.com'>Walid Nouh</a>";
   $author.= ", Anthony Piesset";
   $author.= ", <a href='mailto:dethegeek@gmail.com'>Dethegeek</a>";
   return array ('name'           => __s('Sim cards management', 'simcard'),
                   'version'        => PLUGIN_SIMCARD_VERSION,
                   'author'         => $author,
                   'license'        => 'GPLv2+',
                   'homepage'       => 'https://forge.indepnet.net/projects/show/simcard',
                   'minGlpiVersion' => PLUGIN_SIMCARD_GLPI_MIN_VERSION);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_simcard_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_SIMCARD_GLPI_MIN_VERSION, 'lt') || version_compare(GLPI_VERSION, PLUGIN_SIMCARD_GLPI_MAX_VERSION, 'ge')) {
      echo "This plugin requires GLPI >= " . PLUGIN_SIMCARD_GLPI_MIN_VERSION . " and GLPI < " . PLUGIN_SIMCARD_GLPI_MAX_VERSION;
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_simcard_check_config() {
   return true;
}

/**
 * 
 * Migrate itemtype integer (0.72) to string (0.80)
 * 
 * @param array $types
 * @return string
 */
function plugin_datainjection_migratetypes_simcard($types) {
   $types[1300] = 'PluginSimcardsSimcard';
   return $types;
}

?>
