<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_certificates() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['certificates'] = true;
   $PLUGIN_HOOKS['change_profile']['certificates'] = array('PluginCertificatesProfile','initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['certificates'] = true;
   

   if (Session::getLoginUserID()) {
   
      // Params : plugin name - string type - number - attributes
      Plugin::registerClass('PluginCertificatesCertificate', array(
         'linkgroup_tech_types' => true,
         'linkuser_tech_types' => true,
         'document_types' => true,
         'helpdesk_visible_types' => true,
         'ticket_types'         => true,
         'contract_types' => true,
         'notificationtemplates_types' => true
      ));
      
      Plugin::registerClass('PluginCertificatesConfig',
                         array('addtabon' => 'CronTask'));
                         
      Plugin::registerClass('PluginCertificatesProfile',
                         array('addtabon' => 'Profile'));

      if (class_exists('PluginAccountsAccount')) {
         PluginAccountsAccount::registerType('PluginCertificatesCertificate');
      }
   
      $plugin = new Plugin();
      if (!$plugin->isActivated('environment') 
         && Session::haveRight("plugin_certificates", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['certificates'] = array('assets'   => 'PluginCertificatesMenu');
      }
      if (Session::haveRight("plugin_certificates", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['certificates']=1;
      }

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['certificates'] = 'plugin_certificates_postinit';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_certificates() {

   return array (
      'name' => _n('Certificate', 'Certificates', 2, 'certificates'),
      'version' => '2.0.0',
      'license' => 'GPLv2+',
      'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'=>'https://forge.indepnet.net/projects/certificates',
      'minGlpiVersion' => '0.85',// For compatibility / no install in version < 0.85
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_certificates_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      _e('This plugin requires GLPI >= 0.85', 'certificates');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_certificates_check_config() {
   return true;
}

?>