<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_init_racks() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['racks']   = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['racks']   = array('PluginRacksProfile',
                                                                'initProfile');
                                                                
   $plugin = new Plugin();
   if ($plugin->isInstalled('racks') && $plugin->isActivated('racks')) {
   
      $PLUGIN_HOOKS['assign_to_ticket']['racks'] = true;
      Plugin::registerClass('PluginRacksRack', 
                            array('document_types'       => true,
                                  'unicity_types'        => true,
                                  'linkgroup_tech_types' => true,
                                  'linkuser_tech_types'  => true,
                                  'infocom_types'        => true,
                                  'ticket_types'         => true));
      Plugin::registerClass('PluginRacksProfile',
                            array('addtabon' => 'Profile'));

      $types = array('PluginAppliancesAppliance', 
                     'PluginManufacturersimportsConfig', 
                     'PluginTreeviewConfig', 
                     'PluginPositionsPosition');
      foreach ($types as $itemtype) {
         if (class_exists($itemtype)) {
            $itemtype::registerType('PluginRacksRack');
         }
      }
      
      //If treeview plugin is installed, add rack as a type of item 
      //that can be shown in the tree
      if (class_exists('PluginTreeviewConfig')) {
         $PLUGIN_HOOKS['treeview']['PluginRacksRack'] = '../racks/pics/racks.png';
      }
      
      if (Session::getLoginUserID()) {
      
         include_once (GLPI_ROOT."/plugins/racks/inc/rack.class.php");
         
         if (PluginRacksRack::canView()) {
            //Display menu entry only if user has right to see it !
            $PLUGIN_HOOKS["menu_toadd"]['racks'] = array('assets'  => 'PluginRacksMenu');
            $PLUGIN_HOOKS['use_massive_action']['racks'] = 1;
         }

         if (PluginRacksRack::canCreate() 
            || Config::canUpdate()) {
            $PLUGIN_HOOKS['config_page']['racks'] = 'front/config.form.php';
         }

         $PLUGIN_HOOKS['add_css']['racks']   = "racks.css";
         $PLUGIN_HOOKS['post_init']['racks'] = 'plugin_racks_postinit';
         $PLUGIN_HOOKS['reports']['racks']   = 
            array('front/report.php' => __("Report - Bays management","racks"));
      }
   
   }
}

function plugin_version_racks() {
   return array ('name'           => _n('Rack enclosure management', 
                                        'Rack enclosures management', 
                                        2, 'racks'),
                  'version'        => '1.5.0',
                  'oldname'        => 'rack',
                  'license'        => 'GPLv2+',
                  'author'         => 'Philippe Béchu, Walid Nouh, Xavier Caillaud',
                  'homepage'       => 'https://forge.indepnet.net/projects/racks',
                  'minGlpiVersion' => '0.85');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_racks_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85', 'lt') 
      || version_compare(GLPI_VERSION,'0.86', 'ge')) {
      _e('This plugin requires GLPI >= 0.85', 'racks');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_racks_check_config() {
   return true;
}
?>