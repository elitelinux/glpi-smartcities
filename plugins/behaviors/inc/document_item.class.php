<?php
/**
 * @version $Id: document_item.class.php 172 2014-11-15 17:41:55Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    David Durieux
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2014

 --------------------------------------------------------------------------
*/

class PluginBehaviorsDocument_Item {


   static function addEvents(NotificationTargetTicket $target) {

      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('add_notif')) {
         Plugin::loadLang('behaviors');

         $target->events['plugin_behaviors_document_itemnew']  = __('Add document to ticket',
                                                                    'behaviors');
         $target->events['plugin_behaviors_document_itemdel']  = __('Delete document to ticket',
                                                                    'behaviors');
      }
   }


   static function afterAdd(Document_Item $document_item) {

      $config = PluginBehaviorsConfig::getInstance();
      if ($config->getField('add_notif')
          && ($document_item->input['itemtype'] == 'Ticket')
          && ($_POST['itemtype'] == 'Ticket')) {// prevent not in case of create ticket
         $ticket = new Ticket();
         $ticket->getFromDB($document_item->input['items_id']);

         NotificationEvent::raiseEvent('plugin_behaviors_document_itemnew', $ticket);
      }
   }


   static function afterPurge(Document_Item $document_item) {

      $config = PluginBehaviorsConfig::getInstance();
      if ($config->getField('add_notif')
          && ($document_item->fields['itemtype'] == 'Ticket')
          && isset($_POST['item'])) { // prevent not use in case of purge ticket

         $ticket = new Ticket();
         $ticket->getFromDB($document_item->fields['items_id']);

         NotificationEvent::raiseEvent('plugin_behaviors_document_itemdel', $ticket);
      }
   }

}
