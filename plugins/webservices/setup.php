<?php
/**
 * @version $Id: setup.php 395 2014-11-16 18:39:27Z yllen $
 -------------------------------------------------------------------------
 LICENSE

 This file is part of Webservices plugin for GLPI.

 Webservices is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Webservices. If not, see <http://www.gnu.org/licenses/>.

 @package   Webservices
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2014 Webservices plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/webservices
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_webservices() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $WEBSERVICE_LINKED_OBJECTS;

   Plugin::registerClass('PluginWebservicesClient');

   $PLUGIN_HOOKS['csrf_compliant']['webservices'] = true;

   $PLUGIN_HOOKS["menu_toadd"]['webservices'] = array('config'  => 'PluginWebservicesClient');

   $PLUGIN_HOOKS['webservices']['webservices'] = 'plugin_webservices_registerMethods';

   //Store objects that can be retrieved when querying another object
   $WEBSERVICE_LINKED_OBJECTS
      = array('with_infocom'     => array('help'           => 'bool, optional',
                                          'itemtype'       => 'Infocom',
                                          'allowed_types'  => $CFG_GLPI['infocom_types'],
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_networkport' => array('help'           => 'bool, optional',
                                          'itemtype'       => 'NetworkPort',
                                          'allowed_types'  => plugin_webservices_getNetworkPortItemtypes(),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_phone'       => array('help'           => 'bool, optional (Computer only)',
                                          'itemtype'       => 'Phone',
                                          'allowed_types'  => array('Computer'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_printer'     => array('help'           => 'bool', 'optional (Computer only)',
                                          'itemtype'       => 'Printer',
                                          'allowed_types'  => array('Computer'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_monitor'     => array('help'           => 'bool', 'optional (Computer only)',
                                          'itemtype'       => 'Monitor',
                                          'allowed_types'  => array('Computer'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_peripheral'  => array('help'           => 'bool', 'optional (Computer only)',
                                          'itemtype'       => 'Peripheral',
                                          'allowed_types'  => array('Computer'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_document'    => array('help'           => 'bool', 'optional',
                                          'itemtype'       => 'Document',
                                          'allowed_types'  => plugin_webservices_getDocumentItemtypes(),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_ticket'      => array('help'           => 'bool', 'optional',
                                          'itemtype'       => 'Ticket',
                                          'allowed_types'  => plugin_webservices_getTicketItemtypes(),
                                          'class'          => 'PluginWebservicesMethodHelpdesk'),

              'with_tickettask'  => array('help'           => 'bool', 'optional (Ticket only)',
                                          'itemtype'       => 'TicketTask',
                                          'allowed_types'  => array('Ticket'),
                                          'class'          => 'PluginWebservicesMethodHelpdesk'),

              'with_ticketfollowup'
                                 => array('help'           => 'bool', 'optional (Ticket only)',
                                          'itemtype'       => 'TicketFollowup',
                                          'allowed_types'  => array('Ticket'),
                                          'class'          => 'PluginWebservicesMethodHelpdesk'),
              'with_ticketvalidation'
                                 => array('help'           => 'bool', 'optional (Ticket only)',
                                          'itemtype'       => 'TicketValidation',
                                          'allowed_types'  => array('Ticket'),
                                          'class'          => 'PluginWebservicesMethodHelpdesk'),

              'with_reservation' => array('help'           => 'bool',
                                          'itemtype'       => 'Reservation',
                                          'allowed_types'  => $CFG_GLPI['reservation_types'],
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_software'    => array('help'           => 'bool',
                                          'itemtype'       => 'Software',
                                          'allowed_types'  => array('Computer'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_softwareversion'
                                 => array('help'           => 'bool',
                                          'itemtype'       => 'SoftwareVersion',
                                          'allowed_types'  => array('Software'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_softwarelicense'
                                 => array('help'           => 'bool',
                                          'itemtype'       => 'SoftwareLicense',
                                          'allowed_types'  => array('Software'),
                                          'class'          => 'PluginWebservicesMethodInventaire'),

              'with_contract'    => array('help'           => 'bool',
                                          'itemtype'       => 'Contract',
                                          'allowed_types'  => $CFG_GLPI['contract_types'],
                                          'class'          => 'PluginWebservicesMethodInventaire')
   );
}


function plugin_version_webservices() {

   return array('name'           => __('Web Services', 'webservices'),
                'version'        => '1.5.0',
                'author'         => 'Remi Collet, Nelly Mahu-Lasson',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/webservices',
                'minGlpiVersion' => '0.85');
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_webservices_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      _e('Incompatible GLPI version. Requires 0.85', 'webservices');
   } else if (!extension_loaded("soap")) {
      _e('Incompatible PHP Installation. Requires module soap', 'webservices');
   } else if (!function_exists("xmlrpc_encode")) {
      _e('Incompatible PHP Installation. Requires module xmlrpc', 'webservices');
   } else if (!function_exists("json_encode")) {
      _e('Incompatible PHP Installation. Requires module json', 'webservices');
   } else {
      return true;
   }
   return false;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_webservices_check_config() {
   return TableExists("glpi_plugin_webservices_clients");
}


function plugin_webservices_getDocumentItemtypes() {
   global $CFG_GLPI;

   return $CFG_GLPI['document_types'];
}


function plugin_webservices_getNetworkPortItemtypes() {
   global $CFG_GLPI;

   return $CFG_GLPI['networkport_types'];
}


function plugin_webservices_getTicketItemtypes() {
   global $CFG_GLPI;

   return $CFG_GLPI['ticket_types'];

}
?>
