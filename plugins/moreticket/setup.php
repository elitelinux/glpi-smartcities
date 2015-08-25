<?php

/*
  -------------------------------------------------------------------------
  Moreticket plugin for GLPI
  Copyright (C) 2013 by the Moreticket Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Moreticket.

  Moreticket is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Moreticket is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Moreticket. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_moreticket() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['add_css']['moreticket']        = 'moreticket.css';
   $PLUGIN_HOOKS['csrf_compliant']['moreticket'] = true;
   $PLUGIN_HOOKS['change_profile']['moreticket'] = array('PluginMoreticketProfile', 'initProfile');


   if (Session::getLoginUserID()) {
      Plugin::registerClass('PluginMoreticketProfile', array('addtabon' => 'Profile'));

      if (class_exists('PluginMoreticketProfile')) { // only if plugin activated
         $config = new PluginMoreticketConfig();

         if (Session::haveRight("plugin_moreticket", UPDATE)) {
            if (strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false 
               && ($config->useWaiting() == true || $config->useSolution() == true)) {
               $PLUGIN_HOOKS['add_javascript']['moreticket'][] = 'scripts/moreticket.js';
               $PLUGIN_HOOKS['add_javascript']['moreticket'][] = 'scripts/moreticket.js.php';
            }
            
            $PLUGIN_HOOKS['config_page']['moreticket'] = 'front/config.form.php';

            $PLUGIN_HOOKS['pre_item_update']['moreticket'] = array('Ticket' => array('PluginMoreticketTicket', 'beforeUpdate'));
            $PLUGIN_HOOKS['pre_item_add']['moreticket']    = array('Ticket' => array('PluginMoreticketTicket', 'beforeAdd'));
            $PLUGIN_HOOKS['item_add']['moreticket']        = array('Ticket' => array('PluginMoreticketTicket', 'afterAdd'));
            $PLUGIN_HOOKS['item_update']['moreticket']     = array('Ticket' => array('PluginMoreticketTicket', 'afterUpdate'));
            $PLUGIN_HOOKS['item_empty']['moreticket']      = array('Ticket' => array('PluginMoreticketTicket', 'emptyTicket'));
         }
         
         if (Session::haveRight('plugin_moreticket', READ)) {
            Plugin::registerClass('PluginMoreticketWaitingTicket', array('addtabon' => 'Ticket'));
            Plugin::registerClass('PluginMoreticketCloseTicket', array('addtabon' => 'Ticket'));
         }
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_moreticket() {

   return array(
      'name'           => __('More ticket', 'moreticket'),
      'version'        => "1.1.3",
      'author'         => "Infotel",
      'homepage'       => "https://github.com/InfotelGLPI/moreticket",
      'license'        => 'GPLv2+',
      'minGlpiVersion' => "0.85"
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_moreticket_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '0.86', 'ge')) {
      _e('This plugin requires GLPI >= 0.85', 'moreticket');
   } else {
      return true;
   }
   return false;
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_moreticket_check_config() {
   return true;
}

?>
