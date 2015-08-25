<?php
/**
 * @version $Id: hook.php 395 2014-11-16 18:39:27Z yllen $
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

function plugin_webservices_registerMethods() {
   global $WEBSERVICES_METHOD;

   // Not authenticated method
   $WEBSERVICES_METHOD['glpi.test']
                              = array('PluginWebservicesMethodCommon','methodTest');
   $WEBSERVICES_METHOD['glpi.status']
                              = array('PluginWebservicesMethodCommon','methodStatus');
   $WEBSERVICES_METHOD['glpi.listAllMethods']
                              = array('PluginWebservicesMethodCommon','methodList');
   $WEBSERVICES_METHOD['glpi.listEntities']
                              = array('PluginWebservicesMethodCommon','methodListEntities');
   $WEBSERVICES_METHOD['glpi.doLogin']
                              = array('PluginWebservicesMethodSession','methodLogin');
   $WEBSERVICES_METHOD['glpi.listKnowBaseItems']
                              = array('PluginWebservicesMethodTools','methodListKnowBaseItems');
   $WEBSERVICES_METHOD['glpi.getKnowBaseItem']
                              = array('PluginWebservicesMethodTools','methodGetKnowBaseItem');
   $WEBSERVICES_METHOD['glpi.getDocument']
                              = array('PluginWebservicesMethodInventaire','methodGetDocument');

   // Authenticated method - Session
   $WEBSERVICES_METHOD['glpi.doLogout']
                              = array('PluginWebservicesMethodSession','methodLogout');
   $WEBSERVICES_METHOD['glpi.getMyInfo']
                              = array('PluginWebservicesMethodSession','methodGetMyInfo');
   $WEBSERVICES_METHOD['glpi.listMyProfiles']
                              = array('PluginWebservicesMethodSession','methodListMyProfiles');
   $WEBSERVICES_METHOD['glpi.setMyProfile']
                              = array('PluginWebservicesMethodSession','methodSetMyProfile');
   $WEBSERVICES_METHOD['glpi.listMyEntities']
                              = array('PluginWebservicesMethodSession','methodListMyEntities');
   $WEBSERVICES_METHOD['glpi.setMyEntity']
                              = array('PluginWebservicesMethodSession','methodSetMyEntity');

   // Authenticated method - Others
   $WEBSERVICES_METHOD['glpi.listDropdownValues']
                              = array('PluginWebservicesMethodCommon','methodListDropdownValues');
   $WEBSERVICES_METHOD['glpi.listHelpdeskTypes']
                              = array('PluginWebservicesMethodHelpdesk','methodListHelpdeskTypes');
   $WEBSERVICES_METHOD['glpi.listHelpdeskItems']
                              = array('PluginWebservicesMethodHelpdesk','methodListHelpdeskItems');
   $WEBSERVICES_METHOD['glpi.listTickets']
                              = array('PluginWebservicesMethodHelpdesk','methodListTickets');
   $WEBSERVICES_METHOD['glpi.listGroups']
                              = array('PluginWebservicesMethodInventaire','methodListGroups');
   $WEBSERVICES_METHOD['glpi.listUsers']
                              = array('PluginWebservicesMethodInventaire','methodListUsers');

   //Inventory
   $WEBSERVICES_METHOD['glpi.listInventoryObjects']
                              = array('PluginWebservicesMethodInventaire','methodListInventoryObjects');
   $WEBSERVICES_METHOD['glpi.listObjects']
                              = array('PluginWebservicesMethodInventaire','methodListObjects');
   $WEBSERVICES_METHOD['glpi.getObject']
                              = array('PluginWebservicesMethodInventaire','methodGetObject');

   // Inventory : write methods
   $WEBSERVICES_METHOD['glpi.createObjects']
                              = array('PluginWebservicesMethodInventaire','methodCreateObjects');
   $WEBSERVICES_METHOD['glpi.deleteObjects']
                              = array('PluginWebservicesMethodInventaire','methodDeleteObjects');
   $WEBSERVICES_METHOD['glpi.updateObjects']
                              = array('PluginWebservicesMethodInventaire','methodUpdateObjects');
   $WEBSERVICES_METHOD['glpi.linkObjects']
                              = array('PluginWebservicesMethodInventaire','methodLinkObjects');

   //Inventor : generic methods
   $WEBSERVICES_METHOD['glpi.getInfocoms']
                              = array('PluginWebservicesMethodInventaire','methodGetItemInfocoms');
   $WEBSERVICES_METHOD['glpi.getContracts']
                              = array('PluginWebservicesMethodInventaire','methodGetItemContracts');

   //Inventory : computer
   $WEBSERVICES_METHOD['glpi.getNetworkports']
                              = array('PluginWebservicesMethodInventaire','methodGetNetworkports');
   $WEBSERVICES_METHOD['glpi.getPhones']
                              = array('PluginWebservicesMethodInventaire','methodGetPhones');


   //Helpdesk
   $WEBSERVICES_METHOD['glpi.getTicket']
                              = array('PluginWebservicesMethodHelpdesk','methodGetTicket');
   $WEBSERVICES_METHOD['glpi.createTicket']
                              = array('PluginWebservicesMethodHelpdesk','methodCreateTicket');
   $WEBSERVICES_METHOD['glpi.addTicketFollowup']
                              = array('PluginWebservicesMethodHelpdesk','methodAddTicketFollowup');
   $WEBSERVICES_METHOD['glpi.addTicketDocument']
                              = array('PluginWebservicesMethodHelpdesk','methodAddTicketDocument');
   $WEBSERVICES_METHOD['glpi.addTicketObserver']
                              = array('PluginWebservicesMethodHelpdesk','methodAddTicketObserver');
   $WEBSERVICES_METHOD['glpi.setTicketSatisfaction']
                              = array('PluginWebservicesMethodHelpdesk','methodsetTicketSatisfaction');
   $WEBSERVICES_METHOD['glpi.setTicketValidation']
                              = array('PluginWebservicesMethodHelpdesk','methodsetTicketValidation');
   $WEBSERVICES_METHOD['glpi.setTicketSolution']
                              = array('PluginWebservicesMethodHelpdesk','methodsetTicketSolution');
   $WEBSERVICES_METHOD['glpi.setTicketAssign']
                              = array('PluginWebservicesMethodHelpdesk','methodsetTicketAssign');
}


// Install process for plugin : need to return true if succeeded
function plugin_webservices_install() {

   $migration = new Migration(130);

   // No autoload when plugin is not activated
   require_once 'inc/client.class.php';
   PluginWebservicesClient::install($migration);

   $migration->executeMigration();

   return true;
}


// Uninstall process for plugin : need to return true if succeeded
function plugin_webservices_uninstall() {

   // No autoload when plugin is not activated
   require_once 'inc/client.class.php';
   PluginWebservicesClient::uninstall();

   return true;
}


function plugin_webservices_giveItem($type,$ID,$data,$num) {

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_webservices_clients.do_log" :
            switch ($data["ITEM_$num"]) {
               case 2 :
                  return _n('Log', 'Logs', 2);

               case 1:
                  return __('Historical');

               default:
                  return __('No');
            }
         break;
   }
}


function plugin_webservices_addSelect($type,$ID,$num) {

   $searchopt = &Search::getOptions($type);

   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case 'glpi_plugin_webservices_clients.ip' :
         return " CONCAT(INET_NTOA(ip_start), ' - ', INET_NTOA(ip_end)) AS ITEM_$num, ";
   }
   return '';
}


function plugin_webservices_addWhere($link,$nott,$type,$ID,$val) {

   $NOT        = ($nott ? " NOT" : "");
   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case 'glpi_plugin_webservices_clients.ip' :
         return " $link $NOT (INET_ATON('$val') >= ip_start
                              AND INET_ATON('$val') <= ip_end) ";

      case 'pattern' :
         return " $link '$val' $NOT REGEXP pattern ";
   }
   return '';
}


function plugin_webservices_addOrderBy($type,$ID,$order,$key=0) {

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case 'glpi_plugin_webservices_clients.ip' :
         return " ORDER BY INET_NTOA(`ip_start`) $order,
                           INET_NTOA(`ip_end`) $order ";
   }
   return '';
}


function cron_plugin_webservices() {

   Toolbox::logInFile('webservices', "cron called\n");
   plugin_webservices_soap_create_wdsl();
   return 1;

}
?>