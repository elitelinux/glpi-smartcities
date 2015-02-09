<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Additionalalerts plugin for GLPI
 Copyright (C) 2003-2011 by the Additionalalerts Development Team.

 https://forge.indepnet.net/projects/additionalalerts
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Additionalalerts.

 Additionalalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Additionalalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with additionalalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_additionalalerts() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['additionalalerts'] = true;
   $PLUGIN_HOOKS['change_profile']['additionalalerts'] = array('PluginAdditionalalertsProfile','initProfile');
   
   Plugin::registerClass('PluginAdditionalalertsInfocomAlert', array(
      'notificationtemplates_types' => true,
      'addtabon' => 'CronTask'
   ));

   Plugin::registerClass('PluginAdditionalalertsOcsAlert', array(
      'notificationtemplates_types' => true,
      'addtabon' => 'CronTask'
   ));
   
   Plugin::registerClass('PluginAdditionalalertsProfile',
                         array('addtabon' => 'Profile'));
   
   Plugin::registerClass('PluginAdditionalalertsConfig',
                         array('addtabon' => array('NotificationMailSetting','Entity')));
                         
   if (Session::getLoginUserID()) {
      // Display a menu entry ?
      if (Session::haveRight("plugin_additionalalerts", READ)) {
         $PLUGIN_HOOKS["menu_toadd"]['additionalalerts'] = array('config'  => 'PluginAdditionalalertsMenu');
      }
   }

}

// Get the name and the version of the plugin - Needed
function plugin_version_additionalalerts() {

   return array (
      'name' => _n('Other alert', 'Others alerts', 2, 'additionalalerts'),
      'version' => '1.7.0',
      'license' => 'GPLv2+',
      'oldname' => 'alerting',
      'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'oldname' => 'alerting',
      'homepage'=>'https://forge.indepnet.net/projects/additionalalerts',
      'minGlpiVersion' => '0.85',// For compatibility / no install in version < 0.85
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_additionalalerts_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      _e('This plugin requires GLPI >= 0.85', 'additionalalerts');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_additionalalerts_check_config() {
   return true;
}

?>