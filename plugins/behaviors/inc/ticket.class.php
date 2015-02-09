<?php
/**
 * @version $Id: ticket.class.php 172 2014-11-15 17:41:55Z yllen $
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
 @author    Remi Collet
 @copyright Copyright (c) 2010-2014 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsTicket {


   static function addEvents(NotificationTargetTicket $target) {

      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('add_notif')) {
         Plugin::loadLang('behaviors');
         $target->events['plugin_behaviors_ticketnewtech'] = __('Assign to a technician', 'behaviors');
         $target->events['plugin_behaviors_ticketnewgrp']  = __('Assign to a group', 'behaviors');
         $target->events['plugin_behaviors_ticketreopen']  = __('Reopen ticket', 'behaviors');
         PluginBehaviorsDocument_Item::addEvents($target);
      }
   }


   static function beforeAdd(Ticket $ticket) {
      global $DB;

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      //Toolbox::logDebug("PluginBehaviorsTicket::beforeAdd(), Ticket=", $ticket);
      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('tickets_id_format')) {
         $max = 0;
         $sql = 'SELECT MAX( id ) AS max
                 FROM `glpi_tickets`';
         foreach ($DB->request($sql) as $data) {
            $max = $data['max'];
         }
         $want = date($config->getField('tickets_id_format'));
         if ($max < $want) {
            $DB->query("ALTER TABLE `glpi_tickets` AUTO_INCREMENT=$want");
         }
      }

      if (!isset($ticket->input['_auto_import'])
          && isset($_SESSION['glpiactiveprofile']['interface'])
          && ($_SESSION['glpiactiveprofile']['interface'] == 'central')) {

         if ($config->getField('is_requester_mandatory')
             && !$ticket->input['_users_id_requester']
             && (!isset($ticket->input['_users_id_requester_notif']['alternative_email'])
                 || empty($ticket->input['_users_id_requester_notif']['alternative_email']))) {
            Session::addMessageAfterRedirect(__('Requester is mandatory', 'behaviors'), true, ERROR);
            $ticket->input = array();
            return true;

         }
      }

      if ($config->getField('use_requester_item_group')
          && isset($ticket->input['itemtype'])
          && isset($ticket->input['items_id'])
          && ($ticket->input['items_id'] > 0)
          && ($item = getItemForItemtype($ticket->input['itemtype']))
          && (!isset($ticket->input['_groups_id_requester'])
              || ($ticket->input['_groups_id_requester'] <= 0))) {

         if ($item->isField('groups_id')
             && $item->getFromDB($ticket->input['items_id'])) {
            $ticket->input['_groups_id_requester'] = $item->getField('groups_id');
        }
      }

      // No Auto set Import for external source -> Duplicate from Ticket->prepareInputForAdd()
      if (!isset($ticket->input['_auto_import'])) {
         if (!isset($ticket->input['_users_id_requester'])) {
            if ($uid = Session::getLoginUserID()) {
               $ticket->input['_users_id_requester'] = $uid;
            }
         }
      }

      if ($config->getField('use_requester_user_group')
          && isset($ticket->input['_users_id_requester'])
          && ($ticket->input['_users_id_requester'] > 0)
          && (!isset($ticket->input['_groups_id_requester']) || $ticket->input['_groups_id_requester']<=0)) {

            if ($config->getField('use_requester_user_group') == 1) {
               // First group
               $ticket->input['_groups_id_requester']
                  = PluginBehaviorsUser::getRequesterGroup($ticket->input['entities_id'],
                                                           $ticket->input['_users_id_requester'],
                                                           true);
            } else {
               // All groups
               $g = PluginBehaviorsUser::getRequesterGroup($ticket->input['entities_id'],
                                                           $ticket->input['_users_id_requester'],
                                                           false);
               if (count($g)) {
                  $ticket->input['_groups_id_requester'] = array_shift($g);
               }
               if (count($g)) {
                  $ticket->input['_additional_groups_requesters'] = $g;
               }
            }
      }
      // Toolbox::logDebug("PluginBehaviorsTicket::beforeAdd(), Updated input=", $ticket->input);
   }


   static function afterPrepareAdd(Ticket $ticket) {
      global $DB;

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      // Toolbox::logDebug("PluginBehaviorsTicket::afterPrepareAdd(), Ticket=", $ticket);
      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('use_assign_user_group')
          && isset($ticket->input['_users_id_assign'])
          && ($ticket->input['_users_id_assign'] > 0)
          && (!isset($ticket->input['_groups_id_assign'])
              || ($ticket->input['_groups_id_assign'] <= 0))) {

         if ($config->getField('use_assign_user_group')==1) {
            // First group
            $ticket->input['_groups_id_assign']
               = PluginBehaviorsUser::getTechnicianGroup($ticket->input['entities_id'],
                                                         $ticket->input['_users_id_assign'],
                                                         true);
         } else {
            // All groups
            $ticket->input['_additional_groups_assigns']
               = PluginBehaviorsUser::getTechnicianGroup($ticket->input['entities_id'],
                                                         $ticket->input['_users_id_assign'],
                                                         false);
         }
      }
   }


   static function beforeUpdate(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      //Toolbox::logDebug("PluginBehaviorsTicket::beforeUpdate(), Ticket=", $ticket);
      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('ticket', Ticket::OWN)) {
         return false; // No check
      }

      if (isset($ticket->input['date'])) {
         if ($config->getField('is_ticketdate_locked')) {
            unset($ticket->input['date']);
         }
      }

      if (isset($ticket->input['_read_date_mod'])
          && $config->getField('use_lock')
          && ($ticket->input['_read_date_mod'] != $ticket->fields['date_mod'])) {

         $msg = sprintf(__('%1$s (%2$s)'), __("Can't save, item have been updated", "behaviors"),
                           getUserName($ticket->fields['users_id_lastupdater']).', '.
                           Html::convDateTime($config->fields['date_mod']));

         Session::addMessageAfterRedirect($msg, true, ERROR);
         return $ticket->input = false;
      }

      $soltyp  = (isset($ticket->input['solutiontypes_id'])
                        ? $ticket->input['solutiontypes_id']
                        : $ticket->fields['solutiontypes_id']);
      $dur     = (isset($ticket->input['actiontime'])
                        ? $ticket->input['actiontime']
                        : $ticket->fields['actiontime']);
      $soldesc = (isset($ticket->input['solution'])
                        ? $ticket->input['solution']
                        : $ticket->fields['solution']);
      $cat    = (isset($ticket->input['itilcategories_id'])
                        ? $ticket->input['itilcategories_id']
                        : $ticket->fields['itilcategories_id']);

      // Wand to solve/close the ticket
      if ((isset($ticket->input['solutiontypes_id']) && $ticket->input['solutiontypes_id'])
          || (isset($ticket->input['solution']) && $ticket->input['solution'])
          || (isset($ticket->input['status'])
              && in_array($ticket->input['status'],
                          array(implode("','",Ticket::getSolvedStatusArray()),
                                implode("','",Ticket::getclosedStatusArray()))))) {

         if ($config->getField('is_ticketrealtime_mandatory')) {
            if (!$dur) {
               unset($ticket->input['status']);
               unset($ticket->input['solution']);
               unset($ticket->input['solutiontypes_id']);
               Session::addMessageAfterRedirect(__('You cannot close a ticket without duration',
                                                   'behaviors'), true, ERROR);
            }
         }
         if ($config->getField('is_ticketsolutiontype_mandatory')) {
            if (!$soltyp) {
               unset($ticket->input['status']);
               unset($ticket->input['solution']);
               unset($ticket->input['solutiontypes_id']);
               Session::addMessageAfterRedirect(__('You cannot close a ticket without solution type',
                                                   'behaviors'), true, ERROR);
            }
         }
         if ($config->getField('is_ticketsolution_mandatory')) {
            if (!$soldesc) {
               unset($ticket->input['status']);
               unset($ticket->input['solution']);
               unset($ticket->input['solutiontypes_id']);
               Session::addMessageAfterRedirect(__('You cannot close a ticket without solution description',
                                                   'behaviors'), true, ERROR);
            }
         }
         if ($config->getField('is_ticketcategory_mandatory')) {
            if (!$cat) {
               unset($ticket->input['status']);
               unset($ticket->input['solution']);
               unset($ticket->input['solutiontypes_id']);
               Session::addMessageAfterRedirect(__("You cannot close a ticket without ticket's category",
                                                   'behaviors'), true, ERROR);
            }
         }

      }
   }


   static function onNewTicket() {

      if (isset($_SESSION['glpiactiveprofile']['interface'])
          && ($_SESSION['glpiactiveprofile']['interface'] == 'central')) {

         if (strstr($_SERVER['PHP_SELF'], "/front/ticket.form.php")
             && isset($_POST['id'])
             && ($_POST['id'] == 0)
             && !isset($_GET['id'])) {

            $config = PluginBehaviorsConfig::getInstance();

            // Only if config to add the "first" group
            if (($config->getField('use_requester_user_group') == 1)
                && isset($_POST['_users_id_requester']) && ($_POST['_users_id_requester'] > 0)
                && (!isset($_POST['_groups_id_requester'])
                    || ($_POST['_groups_id_requester'] <= 0)
                    || (isset($_SESSION['glpi_behaviors_auto_group'])
                        && ($_SESSION['glpi_behaviors_auto_group']
                              == $_POST['_groups_id_requester'])))) {

               // Select first group of this user
               $grp = PluginBehaviorsUser::getRequesterGroup($_POST['entities_id'],
                                                             $_POST['_users_id_requester'],
                                                             true);
               $_SESSION['glpi_behaviors_auto_group'] = $grp;
               $_REQUEST['_groups_id_requester']      = $grp;

            } else if (($config->getField('use_requester_user_group') == 1)
                && isset($_POST['_users_id_requester']) && ($_POST['_users_id_requester'] <= 0)
                && isset($_POST['_groups_id_requester'])
                && isset($_SESSION['glpi_behaviors_auto_group'])
                && ($_SESSION['glpi_behaviors_auto_group'] == $_POST['_groups_id_requester'])) {

               // clear user, so clear group
               $_SESSION['glpi_behaviors_auto_group'] = 0;
               $_REQUEST['_groups_id_requester']      = 0;
            } else {
               unset($_SESSION['glpi_behaviors_auto_group']);
            }
         } else {
            unset($_SESSION['glpi_behaviors_auto_group']);
         }
      }
   }


   static function afterUpdate(Ticket $ticket) {
      // Toolbox::logDebug("PluginBehaviorsTicket::afterUpdate(), Ticket=", $ticket);

      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('add_notif')
          && in_array('status', $ticket->updates)
          && in_array($ticket->oldvalues['status'],
                      array(implode("','",Ticket::getSolvedStatusArray()),
                            implode("','",Ticket::getclosedStatusArray())))
          && !in_array($ticket->input['status'],
                       array(implode("','",Ticket::getSolvedStatusArray()),
                             implode("','",Ticket::getclosedStatusArray())))) {

         NotificationEvent::raiseEvent('plugin_behaviors_ticketreopen', $ticket);
      }
   }
}
