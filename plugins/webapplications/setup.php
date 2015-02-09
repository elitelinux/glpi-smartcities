<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_webapplications() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['webapplications'] = true;
   //load changeprofile function
   $PLUGIN_HOOKS['change_profile']['webapplications']   = array('PluginWebapplicationsProfile',
                                                                'initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['webapplications'] = true;

   if (class_exists('PluginWebapplicationsWebapplication_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['plugin_datainjection_populate']['webapplications']
                                       = 'plugin_datainjection_populate_webapplications';
   }

   // Params : plugin name - string type - number - class - table - form page
   Plugin::registerClass('PluginWebapplicationsWebapplication',
                         array('linkgroup_tech_types'         => true,
                               'linkuser_tech_types'          => true,
                               'document_types'          => true,
                               'contract_types'          => true,
                               'ticket_types'            => true,
                               'helpdesk_visible_types'  => true,
                               'addtabon' => 'Supplier'));
   
   Plugin::registerClass('PluginWebapplicationsProfile', array('addtabon' => array('Profile')));
   
   if (class_exists('PluginAccountsAccount')) {
      PluginAccountsAccount::registerType('PluginWebapplicationsWebapplication');
   }
   
   if (class_exists('PluginCertificatesCertificate')) {
      PluginCertificatesCertificate::registerType('PluginWebapplicationsWebapplication');
   }
   
   //if glpi is loaded
   if (Session::getLoginUserID()) {

      //if environment plugin is installed
      $plugin = new Plugin();
      if (!$plugin->isActivated('environment') 
         && Session::haveRight("plugin_webapplications", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['webapplications'] = array('assets'   => 'PluginWebapplicationsMenu');
      }
      
      if (Session::haveRight("plugin_webapplications", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['webapplications']=1;
      }

      if (Session::haveRight("plugin_webapplications", READ)
          || Session::haveRight("config",UPDATE)) {
       }

      // Import from Data_Injection plugin
//      $PLUGIN_HOOKS['migratetypes']['webapplications']
 //                                   = 'plugin_datainjection_migratetypes_webapplications';
      $PLUGIN_HOOKS['plugin_pdf']['PluginWebapplicationsWebapplication']
                                 = 'PluginWebapplicationsWebapplicationPDF';
   }
   
   // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['webapplications'] = 'plugin_webapplications_postinit';
}


// Get the name and the version of the plugin - Needed
function plugin_version_webapplications() {

   return array('name'          => _n('Web application' , 'Web applications' ,2, 'webapplications'),
                'version'        => '2.0.0',
                'license'        => 'GPLv2+',
                'oldname'        => 'appweb',
                'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
                'homepage'       =>'https://forge.indepnet.net/projects/webapplications',
                'minGlpiVersion' => '0.85');
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_webapplications_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      _e('This plugin requires GLPI >= 0.85', 'webapplications');
      return false;
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_webapplications_check_config() {
   return true;
}

function plugin_datainjection_migratetypes_webapplications($types) {

   $types[1300] = 'PluginWebapplicationsWebapplication';
   return $types;
}

?>