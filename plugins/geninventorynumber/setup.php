<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
LICENSE

This file is part of the geninventorynumber plugin.

geninventorynumber plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

geninventorynumber plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
@package   geninventorynumber
@author    the geninventorynumber plugin team
@copyright Copyright (c) 2008-2013 geninventorynumber plugin team
@license   GPLv2+
http://www.gnu.org/licenses/gpl.txt
@link      https://forge.indepnet.net/projects/geninventorynumber
@link      http://www.glpi-project.org/
@since     2008
---------------------------------------------------------------------- */

function plugin_init_geninventorynumber() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $GENINVENTORYNUMBER_TYPES;

   $PLUGIN_HOOKS['csrf_compliant']['geninventorynumber'] = true;
   $PLUGIN_HOOKS['post_init']['geninventorynumber'] = 'plugin_geninventorynumber_postinit';
       
   $GENINVENTORYNUMBER_TYPES = array ('Computer', 'Monitor', 'Printer', 'NetworkEquipment',
                                       'Peripheral', 'Phone', 'SoftwareLicense');
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('geninventorynumber') && $plugin->isActivated('geninventorynumber')
      && (Session::haveRight("config", CREATE))) {
      $PLUGIN_HOOKS['use_massive_action']['geninventorynumber'] = 1;

      Plugin::registerClass('PluginGeninventorynumberProfile',
                            array('addtabon' => array('Profile')));
      Plugin::registerClass('PluginGeninventorynumberConfig');
      Plugin::registerClass('PluginGeninventorynumberConfigField');
      
      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS["menu_toadd"]['geninventorynumber'] = array ('tools' => 'PluginGeninventorynumberConfig');        
      }
   }
}

function plugin_version_geninventorynumber() {
   return array ('name'           => __('geninventorynumber', 'geninventorynumber'),
                   'minGlpiVersion' => '0.85',
                   'version'        => '0.85+1.0',
                   'author'         => "<a href='http://www.teclib.com'>TECLIB'</a> + KK",
                   'homepage'       => 'https://github.com/teclib/geninventorynumber');
}

function plugin_geninventorynumber_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.91','ge')) {
      echo "This plugin requires 0.85 or higher";
   } else {
      return true;
   }
}

/**
 * Compatibility check
 *
 * @return	bool	True if plugin compatible with configuration
 */
function plugin_geninventorynumber_check_config() {
   return true;
}
