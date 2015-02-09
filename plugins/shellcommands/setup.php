<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Shellcommands plugin for GLPI
  Copyright (C) 2003-2011 by the Shellcommands Development Team.

  https://forge.indepnet.net/projects/shellcommands
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Shellcommands.

  Shellcommands is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Shellcommands is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_shellcommands() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['shellcommands'] = true;
   $PLUGIN_HOOKS['change_profile']['shellcommands'] = array('PluginShellcommandsProfile', 'changeProfile');
   //Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['shellcommands'] = array('Profile' => array('PluginShellcommandsProfile', 'purgeProfiles'));
   
   $PLUGIN_HOOKS['add_css']['shellcommands'] = array('shellcommands.css');
   $PLUGIN_HOOKS['add_javascript']['shellcommands'] = array('shellcommands.js');

   if (Session::getLoginUserID()) {
      Plugin::registerClass('PluginShellcommandsProfile', array('addtabon' => 'Profile'));
      if (Session::haveRight("plugin_shellcommands", READ)) {
         // Menu
         $PLUGIN_HOOKS['helpdesk_menu_entry']['shellcommands'] = '/front/menu.php';
         $PLUGIN_HOOKS['menu_entry']['shellcommands']          = 'front/menu.php';
         $PLUGIN_HOOKS['menu_toadd']['shellcommands'] = array('tools' => 'PluginShellcommandsShellcommand');
      }

      $PLUGIN_HOOKS['use_massive_action']['shellcommands'] = 1;

      $PLUGIN_HOOKS['post_init']['shellcommands'] = 'plugin_shellcommands_postinit';
      
   }
   
   $PLUGIN_HOOKS['webservices']['shellcommands'] = 'plugin_shellcommands_registerWebservicesMethods';
}

// Get the name and the version of the plugin - Needed
function plugin_version_shellcommands() {
   return array(
       'name' => _n('Shell Command', 'Shell Commands', 2, 'shellcommands'),
       'version' => '1.8.0',
       'license' => 'GPLv2+',
       'oldname' => 'cmd',
       'author' => 'Xavier Caillaud',
       'homepage' => 'https://forge.indepnet.net/projects/show/shellcommands',
       'minGlpiVersion' => '0.85', // For compatibility / no install in version < 0.84
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_shellcommands_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '0.86', 'ge')) {
      _e('This plugin requires GLPI >= 0.85', 'shellcommands');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_shellcommands_check_config() {
   return true;
}


?>