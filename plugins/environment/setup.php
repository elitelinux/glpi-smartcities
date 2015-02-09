<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Environment plugin for GLPI
 Copyright (C) 2003-2011 by the Environment Development Team.

 https://forge.indepnet.net/projects/environment
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Environment.

 Environment is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Environment is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Environment. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Init the hooks of the plugins -Needed
function plugin_init_environment() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['environment'] = true;
   $PLUGIN_HOOKS['change_profile']['environment'] = 
                        array('PluginEnvironmentProfile','initProfile');
      
   if (Session::getLoginUserID()) {
      
      Plugin::registerClass('PluginEnvironmentProfile',
                         array('addtabon' => 'Profile'));
                         
      if (Session::haveRight("plugin_environment", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['environment'] = array('assets'   => 'PluginEnvironmentDisplay');
         
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_environment() {

   return array (
      'name' => __('Environment', 'environment'),
      'version' => '1.8.0',
      'license' => 'GPLv2+',
      'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'=>'https://forge.indepnet.net/projects/environment',
      'minGlpiVersion' => '0.85',// For compatibility / no install in version < 0.85
   );

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_environment_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      _e('This plugin requires GLPI >= 0.85', 'environment');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_environment_check_config() {
   return true;
}

?>