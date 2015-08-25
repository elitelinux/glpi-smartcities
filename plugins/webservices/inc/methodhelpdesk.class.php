<?php
/**
 * @version $Id: methodhelpdesk.class.php 402 2015-05-23 18:38:44Z yllen $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebservicesMethodHelpdesk extends PluginWebservicesMethodCommon {


   /**
    * Change the current entity(ies) of a authenticated user
    *
    * @param $params    array of options
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodListHelpdeskTypes($params, $protocol) {

      if (isset($params['help'])) {
         return array('help' => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($_SESSION["glpiactiveprofile"]["helpdesk_hardware"])
          || !$_SESSION["glpiactiveprofile"]["helpdesk_hardware"]) {
         // No right to attach a item to a tickeet
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $resp[] = array('id'   => '',
                      'name' => __('General'));

      if ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"] & pow(2, Ticket::HELPDESK_MY_HARDWARE)) {
         $resp[] = array('id'   => 'my',
                         'name' => __('My devices'));
      }

      if ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"] & pow(2, Ticket::HELPDESK_ALL_HARDWARE)) {
         $types = Ticket::getAllTypesForHelpdesk();
         foreach ($types as $id => $name) {
            $resp[] = array('id'   => $id,
                            'name' => $name);
         }
      }
      return $resp;
   }


   /**
    * List the items for a Helpesk
    * for an authenticated user
    *
    * @param $params    array of options (itemtype, name, group, itemsubtype, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodListHelpdeskItems($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('itemtype'    => 'string,mandatory',
                      'count'       => 'bool,optional',
                      'start'       => 'integer,optional',
                      'limit'       => 'integer,optional',
                      'group'       => 'integer,optional',
                      'name'        => 'string,optional',
                      'state'       => 'integer,optional',
                      'id2name'     => 'bool,optional',
                      'entity'      => 'integer,optional',
                      'help'        => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($_SESSION["glpiactiveprofile"]["helpdesk_hardware"])
          || !$_SESSION["glpiactiveprofile"]["helpdesk_hardware"]) {
         // No right to attach a item to a tickeet
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWEDl);
      }

      if (!isset($params['itemtype'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      if ($params['itemtype']
          && ($params['itemtype'] != 'my')
          && !class_exists($params['itemtype'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER);
      }

      if (!isset($_SESSION["glpiactiveprofile"]["helpdesk_hardware"])
          || !$_SESSION["glpiactiveprofile"]["helpdesk_hardware"]) {
         // No right to attach a item to a tickeet
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, $protocol);
      }

      $start = 0;
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }

      $limit = $_SESSION['glpilist_limit'];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }

      $resp = array ();
      if (isset($params['count'])) {
         $resp['count'] = 0;
      }

      // Partial code from dropdownMyDevices
      if (($params['itemtype'] == 'my')
          && $_SESSION["glpiactiveprofile"]["helpdesk_hardware"]
          & pow(2, Ticket::HELPDESK_MY_HARDWARE)) {
         // My items
         foreach ($CFG_GLPI["linkuser_types"] as $type) {
            if (($limit > 0)
                && ($item = getItemForItemtype($type))
                && Ticket::isPossibleToAssignType($type)) {
               $table = getTableForItemType($type);

               $where = " `is_deleted` = '0'
                         AND (`users_id` ='" . Session::getLoginUserID() . "' ";
               if (count($_SESSION['glpigroups'])
                   && Session::haveRight("show_group_hardware", "1")
                   && in_array($type, $CFG_GLPI["linkgroup_types"])) {

                  $where .= " OR `groups_id` IN(" . implode(',', $_SESSION['glpigroups']) . ")";
               }
               $where .= ")";

               if ($item->maybeTemplate()) {
                  $where .= " AND `is_template` = '0' ";
               }
               $where .= getEntitiesRestrictRequest(" AND", $table, '', '', $item->maybeRecursive());

               $nb = countElementsInTable($table, $where);
               if (isset($params['count'])) {
                  // Only count
                  $resp['count'] += $nb;
               } else if ($start >= $nb) {
                  // Skip this type
                  $start -= $nb;
               } else {
                  $query = "SELECT *
                            FROM `$table`
                            WHERE $where
                            ORDER BY `id`
                            LIMIT $start,$limit";

                  foreach ($DB->request($query) as $data) {
                     $out             = array ();
                     $out['itemtype'] = $type;
                     $out['id']       = $data['id'];
                     $out['name']     = $data['name'];
                     $output          = $data['name'];

                     $from            = array('serial',
                                              'otherserial',
                                              'users_id',
                                              'groups_id',
                                       //     'itemsubtype',
                                              'states_id');

                     foreach ($from as $num => $field) {
                        if (isset($data[$field])) {
                           $out[$from[$num]] = $data[$field];
                        }
                     }
                     $resp[] = $out;

                     // For next type
                     if ($start) {
                        $start--;
                     }
                     $limit--;
                  }
               }
            } // allowed
         } // each type
      } // My items

      $type = $params['itemtype'];
      if ($type && ($type !='my')
          && ($item = getItemForItemtype($type))
          && ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"]
          & pow(2, Ticket::HELPDESK_ALL_HARDWARE))
          && Ticket::isPossibleToAssignType($type)) {
         // All items of a type
         $table = getTableForItemType($type);

         // Entity
         if (isset($params['entity'])) {
            if (!Session::haveAccessToEntity($params['entity'])) {
               return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
            }
            $ent = $params['entity'];
         } else {
            $ent = '';
         }
         $where = getEntitiesRestrictRequest('', $table, '', $ent, $item->maybeRecursive());

         if ($item->maybeDeleted()) {
            $where .= " AND `is_deleted` ='0'";
         }

         if ($item->maybeTemplate()) {
            $where .= " AND `is_template` = '0'";
         }

         if (in_array($type, $CFG_GLPI["helpdesk_visible_types"])) {
            $where .= " AND `is_helpdesk_visible` = '1' ";
         }

         if (isset($params['group'])
             && is_numeric($params['group'])
             && in_array($type, $CFG_GLPI["linkgroup_types"])
             && !in_array($type, $CFG_GLPI["helpdesk_visible_types"])) {

            $where .= " AND `groups_id` = '" . $params['group'] . "'";
         }

         if (isset($params['name'])) {
            $where .= " AND `name` LIKE '" . addslashes($params['name']) . "'";
         }

         if (isset($params['state'])
             && is_numeric($params['state'])
             && in_array($type, $CFG_GLPI["state_types"])) {

            $where .= " AND `states_id` = '" . $params['state'] . "'";
         }

         if (isset($params['count'])) {
            $resp['count'] = countElementsInTable($table, $where);
         } else {
            $query = "SELECT *
                      FROM `$table`
                      WHERE $where
                      ORDER BY `id`
                      LIMIT $start,$limit";

            foreach ($DB->request($query) as $data) {
               $out             = array ();
               $out['itemtype'] = $type;
               $out['id']       = $data['id'];
               $out['name']     = $data['name'];
               $output          = $data['name'];

               $from            = array('serial',
                                        'otherserial',
                                        'locations_id',
                                        'users_id',
                                        'groups_id',
                                        'states_id');

               foreach ($from as $field) {
                  if (isset($data[$field])) {
                     $out[$field] = $data[$field];
                  }
               }
               $resp[] = $out;
            }
         }
      } // All items

      if (isset($params['id2name'])) {
         foreach ($resp as $k => $v) {
            if (isset($resp[$k]['users_id'])) {
               $resp[$k]['users_name'] = Html::clean(getUserName($resp[$k]['users_id']));
            }
            if (isset($resp[$k]['groups_id'])) {
               $resp[$k]['groups_name']
                        = Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                $resp[$k]['groups_id']));
            }
            if (isset($resp[$k]['states_id'])) {
               $resp[$k]['states_name']
                        = Html::clean(Dropdown::getDropdownName('glpi_states',
                                                                $resp[$k]['states_id']));
            }
            if (isset($resp[$k]['locations_id'])) {
               $resp[$k]['glpi_locations']
                        = Html::clean(Dropdown::getDropdownName('glpi_locations',
                                                                $resp[$k]['locations_id']));
            }
         }
      }

      return $resp;
   }


   /**
    * Create a new ticket
    * for an authenticated user
    *
    * @param $params    array of options
    *    (entity, user, group, date, itemtype, itemid, title, content, urgency, category)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodCreateTicket($params=array(), $protocol) {
      global $CFG_GLPI;

      if (isset($params['help'])) {
         return array('content'                 => 'string,mandatory',
                      'title'                   => 'string,optional',
                      'entity'                  => 'integer,optional',
                      'urgency'                 => 'integer,optional',
                      'impact'                  => 'integer,optional',
                      'category'                => 'integer,optional',
                      'user'                    => 'integer,optional',
                      'requester'               => 'integer,optional',
                      'observer'                => 'integer,optional',
                      'group'                   => 'integer,optional',
                      'groupassign'             => 'integer,optional',
                      'date'                    => 'datetime,optional',
                      'type'                    => 'integer,optional',
                      'category'                => 'integer,optional',
                      'itemtype'                => 'string,optional',
                      'item'                    => 'integer,optional',
                      'source'                  => 'string,optional',
                      'user_email'              => 'string,optional',
                      'use_email_notification'  => 'bool,optional',
                      'help'                    => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      // ignore config for content : always mandatory
      if ((!isset($params['content']) || empty($params['content']))) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'content');
      }

      // Source of the ticket, dynamically created
      if (isset($params['source'])) {
         if (empty($params['content'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'source');
         }
         $source = Dropdown::importExternal('RequestType', $params['source']);
      } else {
         $source = Dropdown::importExternal('RequestType', 'WebServices');
      }

      // ===== Build the Ticket =====
      // author : always the logged user
      $data = array('_users_id_requester'
                                 => Session::getLoginUserID(), // Requester / Victime
                    'users_id_recipient'
                                 => Session::getLoginUserID(), // Recorder
                    'requesttypes_id'
                                 => $source,
                    'status'     => Ticket::INCOMING,
                    'content'    => addslashes(Toolbox::clean_cross_side_scripting_deep($params["content"])),
                    'itemtype'   => '',
                    'type'       => Ticket::INCIDENT_TYPE,
                    'items_id'   => 0);

      // Title : optional (default = start of contents set by add method)
      if (isset($params['title'])) {
         $data['name'] = addslashes(Toolbox::clean_cross_side_scripting_deep($params['title']));
      }

      // entity : optionnal, default = current one
      if (!isset($params['entity'])) {
         $data['entities_id'] = $_SESSION['glpiactive_entity'];
      } else {
         if (!is_numeric($params['entity'])
             || !in_array($params['entity'], $_SESSION['glpiactiveentities'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'entity');
         }
         $data['entities_id'] = $params['entity'];
      }

      // user (author) : optionnal,  default = current one
      if (isset($params['user'])) {
         if (!is_numeric($params['user'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'user');
         }
         $data['_users_id_requester'] = $params['user'];
      }

      // Email notification
      if (isset($params['user_email'])) {
         if (!NotificationMail::isUserAddressValid($params['user_email'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'user_email');
         }
         $data['_users_id_requester_notif']['alternative_email'] = $params['user_email'];
         $data['_users_id_requester_notif']['use_notification']  = 1;

      } else if (isset($params['use_email_notification']) && $params['use_email_notification']) {
         $data['_users_id_requester_notif']['use_notification']  = 1;

      } else if (isset($params['use_email_notification']) && !$params['use_email_notification']) {
         $data['_users_id_requester_notif']['use_notification']  = 0;
      }

      if (isset($params['requester'])) {
         if (is_array($params['requester'])) {
            foreach ($params['requester'] as $id) {
               if (is_numeric($id) && $id > 0) {
                  $data['_additional_requesters'][] = array('users_id'         => $id,
                                                            'use_notification' => true);
               } else {
                  return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'requester');
               }
            }
         } else if (is_numeric($params['requester']) && ($params['requester'] > 0)) {
            $data['_additional_requesters'][] = array('users_id'         => $params['requester'],
                                                      'use_notification' => true);
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'requester');
         }
      }

      if (isset($params['victim'])) {
         if (is_array($params['victim'])) {
            foreach ($params['victim'] as $id) {
               if (is_numeric($id) && ($id > 0)) {
                  $data['_additional_requesters'][] = array('users_id'         => $id,
                                                            'use_notification' => false);
               } else {
                  return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'victim');
               }
            }
         } else if (is_numeric($params['victim']) && ($params['victim'] > 0)) {
            $data['_additional_requesters'][] = array('users_id'         => $params['victim'],
                                                      'use_notification' => false);
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'victim');
         }
      }

      if (isset($params['observer'])) {
         if (is_array($params['observer'])) {
            foreach ($params['observer'] as $id) {
               if (is_numeric($id) && ($id > 0)) {
                  $data['_additional_observers'][] = array('users_id'         => $id,
                                                           'use_notification' => true);
               } else {
                  return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'observer');
               }
            }
         } else if (is_numeric($params['observer']) && ($params['observer'] > 0)) {
               $data['_additional_observers'][] = array('users_id'         => $params['observer'],
                                                        'use_notification' => true);
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'observer');
         }
      }

      // group (author) : optionnal,  default = none
      if (!isset($params['group'])) {
         $data['_groups_id_requester'] = 0;
      } else {
         if (!is_numeric($params['group'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'group');
         }
         $data['_groups_id_requester'] = $params['group'];
      }

      // groupassign (technicians group) : optionnal,  default = none
      if (!isset($params['groupassign'])) {
         $data['_groups_id_assign'] = 0;
      } else {
         if (!is_numeric($params['groupassign'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'groupassign');
         }
         $data['_groups_id_assign'] = $params['groupassign'];
      }

      // date (open) : optional, default set by add method
      if (isset($params['date'])) {
         if (preg_match(WEBSERVICES_REGEX_DATETIME, $params['date'])) {
            $data['date'] = $params['date'];
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'date');
         }
      }

      if (isset($params['itemtype']) && empty($params['itemtype'])) {
         unset($params['itemtype']);
      }
      if (isset($params['item']) && !$params['item']) {
         unset($params['item']);
      }
      // Item type + id
      if (isset($params['itemtype'])) {
         if (!isset($params['item'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'item');
         }
         if (!class_exists($params['itemtype'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'itemtype=' . $params['itemtype']);
         }
      }

      if (isset($params['item'])) {
         if (!isset($params['itemtype'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '','itemtype');
         }
         if (!is_numeric($params['item']) || $params['item'] <= 0) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'item=' . $params['item']);
         }

         // Both ok
         $data['itemtype'] = $params['itemtype'];
         $data['items_id'] = $params['item'];
      }

      // Hack for compatibility with previous version
      if (isset($params['urgence'])) {
         $params['urgency'] = $params['urgence'];
      }

      // urgence (priority while not exists) : optionnal,  default = 3
      if (!isset($params['urgency'])) {
         $data['urgency'] = 3;

      } else if ((!is_numeric($params['urgency'])
                  || ($params['urgency'] < 1)
                  || ($params['urgency'] > 5))
                 || (isset($params['urgency'])
                     && !($CFG_GLPI['urgency_mask']&(1<<$params["urgency"])))) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'urgency');

      } else {
         $data['urgency'] = $params['urgency'];
      }

      if (isset($params['impact'])) {
         if ((!is_numeric($params['impact'])
              || ($params['impact'] < 1)
              || ($params['impact'] > 5))
             || (isset($params['impact'])
                 && !($CFG_GLPI['impact_mask']&(1<<$params["impact"])))) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'impact');
         } else {
            $data['impact'] = $params['impact'];
         }
      }

      // category : optionnal
      if (isset($params['category'])) {
         if (!is_numeric($params['category']) || ($params['category'] < 1)) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'category');
         }
         $data['itilcategories_id'] = $params['category'];
      }

      // type : optionnal (default = INCIDENT)
      if (isset($params['type'])) {
         $types = Ticket::getTypes();
         if (!is_numeric($params['type']) || !isset($types[$params['type']])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'type');
         }
         $data['type'] = $params['type'];
      }
      $ticket = new Ticket();
      if ($newID = $ticket->add($data)) {
         return self::methodGetTicket(array('ticket' => $newID), $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',self::getDisplayError());
   }


   static function methodAddTicketObserver($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'  => 'integer,mandatory',
                      'user'    => 'integer,optional',
                      'help'    => 'bool,optional');
      }

      $ticket = new Ticket();

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }
      if (!is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'ticket=' . $params['ticket']);
      }
      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $ticket_user = new Ticket_User();
      $input = array('tickets_id'       => $ticket->getID(),
                     'users_id'         => Session::getLoginUserID(),
                     'use_notification' => '1',
                     'type'             => CommonITILActor::OBSERVER);

      if (isset($params['user'])) {
         if (!is_numeric($params['user'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'user=' . $params['user']);
         }
         $input['users_id'] = $params['user'];
         if (!$ticket_user->can(-1, UPDATE, $input)) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }
      }

      if ($ticket->isUser(CommonITILActor::OBSERVER, $input['users_id'] )) {
         return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                            'Already an observer for this ticket');
      }

      if ($ticket_user->add($input)) {
         return self::methodGetTicket(array('ticket' => $params['ticket']), $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',self::getDisplayError());
   }


   /**
    * Answer to the ticket satisfaction survey
    * for an authenticated user
    *
    * @param $params    array of options (ticket, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable as glpi.getTicket
   **/
   static function methodsetTicketSatisfaction($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'       => 'integer,mandatory',
                      'id2name'      => 'bool,optional',
                      'satisfaction' => 'integer,mandatory',
                      'comment'      => 'text,optional',
                      'help'         => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $ticket = new Ticket();

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }

      if (!isset($params['satisfaction'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'satisfaction');
      }

      if (!is_numeric($params['satisfaction'])
          || ($params['satisfaction'] < 0)
          || ($params['satisfaction'] > 5)) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'satisfaction=' . $params['satisfaction']);
      }

      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND, '', 'ticket');
      }

      $inquest = new TicketSatisfaction();
      if (!$inquest->getFromDB($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND, '', 'satisfaction');
      }

      $input = array('id'           => $inquest->getField('id'),
                     'tickets_id'   => $inquest->getField('tickets_id'),
                     'satisfaction' => $params['satisfaction']);
      if (isset($params['comment'])) {
         $input['comment'] = addslashes($params['comment']);
      }

      if (!$inquest->can($params['ticket'], UPDATE)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if ($inquest->update($input)) {
         unset($params['satisfaction'], $params['comment']);
         return self::methodGetTicket($params, $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',self::getDisplayError());
   }


   /**
    * Answer to a ticket validation request
    * for an authenticated user
    *
    * @param $params    array of options (ticket, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable as glpi.getTicket
   **/
   static function methodsetTicketValidation($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('approval' => 'integer,mandatory',
                      'status'   => 'integer,mandatory',
                      'comment'  => 'text,optional',
                      'help'     => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['approval'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'approval');
      }

      if (!isset($params['status'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'status');
      }

      $tabstatus = TicketValidation::getAllStatusArray();
      if (!isset($tabstatus[$params['status']])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'status=' . $params['status']);
      }

      if ($params['status'] == TicketValidation::REFUSED && !isset($params['comment'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'comment');
      }

      $valid = new TicketValidation();
      if (!$valid->getFromDB($params['approval'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND, '', 'approval');
      }

      $input = array('id'     => $valid->getField('id'),
                     'status' => $params['status']);
      if (isset($params['comment'])) {
         $input['comment_validation'] = addslashes($params['comment']);
      }

      $ticket = new Ticket();
      if ($ticket->getFromDB($valid->getField('tickets_id'))) {
         $tickettype = $ticket->fields['type'];
      }

      if ((($ticketype == 1)
           && !$valid->can($params['approval'], TicketValidation::VALIDATEINCIDENT))
          || (($ticketype == 2)
              && !$valid->can($params['approval'], TicketValidation::VALIDATEREQUEST))) {

         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if ($valid->update($input)) {
         unset($params['approval'], $params['status'], $params['comment']);
         $params['ticket'] = $valid->getField('tickets_id');
         return self::methodGetTicket($params, $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',self::getDisplayError());
   }


   /**
    * Get a ticket information, with its followup
    * for an authenticated user
    *
    * @param $params    array of options (ticket, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodGetTicket($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'  => 'integer,mandatory',
                      'id2name' => 'bool,optional',
                      'help'    => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $ticket = new Ticket();

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }

      if (!is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'ticket=' . $params['ticket']);
      }

      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $resp = $ticket->fields;
      if ($resp['itemtype']) {
         $item = getItemForItemtype($resp['itemtype']);
      } else {
         $item = false;
      }
      $resp['solution'] = Html::clean(Toolbox::unclean_cross_side_scripting_deep($resp['solution']));

      $nextaction = new SlaLevel_Ticket();
      if ($ticket->fields['slas_id'] && $nextaction->getFromDBForTicket($ticket->fields['id'])) {
         $resp['slalevels_next_id']   = $nextaction->fields['slalevels_id'];
         $resp['slalevels_next_date'] = $nextaction->fields['date'];
      } else {
         $resp['slalevels_next_id']   = 0;
         $resp['slalevels_next_date'] = '';
      }

      if (isset($params['id2name'])) {
         $resp['users_name_recipient']
               = Html::clean(getUserName($ticket->fields['users_id_recipient']));
         $resp['users_name_lastupdater']
               = Html::clean(getUserName($ticket->fields['users_id_lastupdater']));
         $resp['ticketcategories_name']
               = Html::clean(Dropdown::getDropdownName('glpi_itilcategories',
                                                       $ticket->fields['itilcategories_id']));
         $resp['entities_name']
               = Html::clean(Dropdown::getDropdownName('glpi_entities', $resp['entities_id']));
         $resp['status_name']
               = Html::clean($ticket->getStatus($resp['status']));
         $resp['requesttypes_name']
               = Html::clean(Dropdown::getDropdownName('glpi_requesttypes',
                                                       $resp['requesttypes_id']));
         $resp['solutiontypes_name']
               = Html::clean(Dropdown::getDropdownName('glpi_solutiontypes',
                                                       $resp['solutiontypes_id']));
         $resp['slas_name']
               = Html::clean(Dropdown::getDropdownName('glpi_slas', $resp['slas_id']));
         $resp['slalevels_name']
               = Html::clean(Dropdown::getDropdownName('glpi_slalevels', $resp['slalevels_id']));
         $resp['slalevels_next_name']
               = Html::clean(Dropdown::getDropdownName('glpi_slalevels',
                                                       $resp['slalevels_next_id']));
         $resp['urgency_name']
               = Html::clean(Ticket::getUrgencyName($resp['urgency']));
         $resp['impact_name']
               = Html::clean(Ticket::getImpactName($resp['impact']));
         $resp['priority_name']
               = Html::clean(Ticket::getPriorityName($resp['priority']));
         $resp['type_name']
               = Html::clean(Ticket::getTicketTypeName($resp['type']));
         $resp['global_validation_name']
               = Html::clean(TicketValidation::getStatus($resp['global_validation']));
         $resp['locations_name']
               = Html::clean(Dropdown::getDropdownName('glpi_locations', $resp['locations_id']));

         if ($item && $item->getFromDB($resp['items_id'])) {
            $resp['items_name']     = Html::clean($item->getNameID());
            $resp['itemtype_name']  = Html::clean($item->getTypeName());
         } else {
            $resp['items_name']     = __('General');
            $resp['itemtype_name']  = '';
         }
      }

      $resp['users']          = array();
      $resp['groups']         = array();
      $resp['followups']      = array ();
      $resp['tasks']          = array ();
      $resp['documents']      = array ();
      $resp['events']         = array ();
      $resp['validations']    = array ();
      $resp['satisfaction']   = array ();

      if (Session::haveRightsOr('followup', array(TicketFollowup::SEEPUBLIC,
                                                  TicketFollowup::SEEPRIVATE))) {
         // Followups
         $query = "SELECT *
                   FROM `glpi_ticketfollowups`
                   WHERE `tickets_id` = '" . $params['ticket'] . "' ";

         if (!Session::haveRight('followup', TicketFollowup::SEEPRIVATE)) {
            $query .= " AND (`is_private` = '0'
                             OR `users_id` = '" . Session::getLoginUserID() . "' ) ";
         }
         $query .= " ORDER BY `date` DESC";

         foreach ($DB->request($query) as $data) {
            if (isset($params['id2name'])) {
               $data['users_name']
                     = Html::clean(getUserName($data['users_id']));
               $data['requesttypes_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_requesttypes',
                                                             $data['requesttypes_id']));
            }
            $resp['followups'][] = $data;
         }
      }
      if (Session::haveRightsOr('task', array(TicketTask::SEEPUBLIC, TicketTask::SEEPRIVATE))) {

         // Tasks
         $query = "SELECT *
                   FROM `glpi_tickettasks`
                   WHERE `tickets_id` = '" . $params['ticket'] . "' ";

         if (!Session::haveRight('task', TicketTask::SEEPRIVATE)) {
            $query .= " AND (`is_private` = '0'
                             OR `users_id` = '" . Session::getLoginUserID() . "' ) ";
         }
         $query .= " ORDER BY `date` DESC";

         foreach ($DB->request($query) as $data) {
            if (isset($params['id2name'])) {
               $data['users_name']
                     = Html::clean(getUserName($data['users_id']));
               $data['taskcategories_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_taskcategories',
                                                             $data['taskcategories_id']));
            }
            $resp['tasks'][] = $data;
         }
      }

      // Documents
      $resp['documents'] = PluginWebservicesMethodTools::getDocForItem($ticket,
                                                                       isset($params['id2name']));

      // History
      $resp['events'] = Log::getHistoryData($ticket, 0, $_SESSION['glpilist_limit']);
      foreach ($resp['events'] as $key => $val) {
         $resp['events'][$key]['change'] = Html::clean($resp['events'][$key]['change']);
      }

      if (Session::haveRightsOr('ticketvalidation', array(TicketValidation::CREATEREQUEST,
                                                          TicketValidation::CREATEINCIDENT,
                                                          TicketValidation::VALIDATEREQUEST,
                                                          TicketValidation::VALIDATEINCIDENT))) {
         $query = "SELECT *
                   FROM `glpi_ticketvalidations`
                   WHERE `tickets_id` = '".$params['ticket']."' ";
         foreach ($DB->request($query) as $data) {
            if (isset($params['id2name'])) {
               $data['users_name']          = Html::clean(getUserName($data['users_id']));
               $data['users_name_validate'] = Html::clean(getUserName($data['users_id_validate']));
               $data['status_name']         = TicketValidation::getStatus($data['status']);
            }
            $resp['validations'][] = $data;
         }
      }

      // Users & Groups
      $tabtmp = array(CommonITILActor::REQUESTER => 'requester',
                      CommonITILActor::OBSERVER  => 'observer',
                      CommonITILActor::ASSIGN    => 'assign');
      foreach ($tabtmp as $num => $name) {
         $resp['users'][$name] = array();
         foreach ($ticket->getUsers($num) as $user) {
            if (isset($params['id2name'])) {
               if ($user['users_id']) {
                  $user['users_name'] = Html::clean(getUserName($user['users_id']));
               } else {
                  $user['users_name'] = $user['alternative_email'];
               }
            }
            unset($user['tickets_id']);
            unset($user['type']);
            $resp['users'][$name][] = $user;
         }
         $resp['groups'][$name] = array();
         foreach ($ticket->getGroups($num) as $group) {
            if (isset($params['id2name'])) {
               $group['groups_name'] = Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                             $group['groups_id']));
            }
            unset($group['tickets_id']);
            unset($group['type']);
            $resp['groups'][$name][] = $group;
         }
      }
      // Suppliers
      $resp['suppliers']['assign'] = array();
      foreach ($ticket->getSuppliers(CommonITILActor::ASSIGN) as $supplier) {
         if (isset($params['id2name'])) {
            $supplier['suppliers_name']
               = Html::clean(Dropdown::getDropdownName('glpi_suppliers',
                                                       $supplier['suppliers_id']));
         }
         unset($supplier['tickets_id']);
         unset($supplier['type']);
         $resp['suppliers'][$name][] = $supplier;
      }


      // Satisfaction
      $satisfaction = new TicketSatisfaction();
      if ($satisfaction->getFromDB($params['ticket'])) {
         $resp['satisfaction'] = $satisfaction->fields;

      }

      return $resp;
   }


   /**
    * Add a followup to a existing ticket
    * for an authenticated user
    *
    * @param $params array of options (ticket, content)
    * @param $protocol
    *
    * @return array of hashtable
   **/
   static function methodAddTicketFollowup($params, $protocol) {

      if (isset($params['help'])) {
         return array('ticket'      => 'integer,mandatory',
                      'content'     => 'string,mandatory',
                      'users_login' => 'string,optional',
                      'close'       => 'bool,optional',
                      'reopen'      => 'bool,optional',
                      'source'      => 'string,optional',
                      'private'     => 'bool,optional',
                      'help'        => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }
      $ticket = new Ticket();

      if (isset($params['users_login']) && is_numeric($params['users_login'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'users_login should be a string');
      }

      if (isset($params['users_login']) && is_string($params['users_login'])) {
         $user = new User();
         if (!$users_id = $user->getIdByName($params['users_login']))
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'unable to get users_id with the users_login');
      }

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }

      if (!is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'ticket');
      }

      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      if (!$ticket->canAddFollowups()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (in_array($ticket->fields["status"], $ticket->getSolvedStatusArray())
          && !$ticket->canApprove()) {// Logged user not allowed

         if (isset($users_id)) {// If we get the users id
            $approbationSolution = self::checkApprobationSolution($users_id, $ticket);
            if (!$approbationSolution) {
               return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
            }
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }
      }

      if (!isset($params['content'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'content');
      }

      // Source of the ticket, dynamically created
      if (isset($params['source'])) {
         if (empty($params['content'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'source');
         }
         $source = Dropdown::importExternal('RequestType', $params['source']);
      } else {
         $source = Dropdown::importExternal('RequestType', 'WebServices');
      }

      $private = (isset($params['private']) && $params['private'] ? 1 : 0);

      $followup = new TicketFollowup();
      $user     = 0;
      if (isset($users_id)) {
         $user = $users_id;
      }
      $data = array('tickets_id'       => $params['ticket'],
                    'requesttypes_id'  => $source,
                    'is_private'       => $private,
                    'users_id'         => $user,
                    'content'          => addslashes(Toolbox::clean_cross_side_scripting_deep($params["content"])));

      if (isset($params['close'])) {
         if (isset($params['reopen'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'can\'t use both reopen and close options');
         }

         if (in_array($ticket->fields["status"], $ticket->getSolvedStatusArray())) {
            $data['add_close'] = 1;
            if (isset($users_id)) {
               $data['users_id'] = $users_id;
            }
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'close for not solved ticket');
         }
      }

      if (isset($params['reopen'])) {
         if (in_array($ticket->fields['status'], array(Ticket::SOLVED, Ticket::WAITING))) {
            $data['add_reopen'] = 1;
            if (isset($users_id)) {
               $data['users_id'] = $users_id;
            }
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                               'reopen for not solved or waiting ticket');
         }
      }

      if (in_array($ticket->fields["status"], $ticket->getSolvedStatusArray())
          && !isset($params['close'])
          && !isset($params['reopen'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'missing reopen/close option for solved ticket');
      }

      if (in_array($ticket->fields["status"], $ticket->getClosedStatusArray())) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'cannot add to a closed ticket');
      }

      if ($followup->add($data)) {
         return self::methodGetTicket(array('ticket' => $params['ticket']), $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '', self::getDisplayError());
   }


     /**
    * check right for Approve ticket Solution
    * for an authenticated user and a particular user
    *
    * @param $users_id  user id used for check ticket right
    * @param $ticket    ticket object
    *
    * @return array of hashtable
   **/
   static function checkApprobationSolution ($users_id, Ticket $ticket) {

      if (!($ticket->fields["users_id_recipient"] === $users_id
              || $ticket->isUser(CommonITILActor::REQUESTER, $users_id)
              || (sizeof(Group_User::getUserGroups($users_id) > 0)
                  && $ticket->haveAGroup(CommonITILActor::REQUESTER,
                                         Group_User::getUserGroups($users_id))))) {
         return false;
      }

      return true;
   }


   /**
    * Add a document to a existing ticket
    * for an authenticated user
    *
    * @param $params array of options (ticket, uri, name, base64, comment)
    *        only one of uri and base64 must be set
    *        name is mandatory when base64 set, for extension check (filename)
    * @param $protocol     the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodAddTicketDocument($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'  => 'integer,mandatory',
                      'uri'     => 'string,optional',
                      'base64'  => 'string,optional',
                      'content' => 'string,optional',
                      'close'   => 'bool,optional',
                      'reopen'  => 'bool,optional',
                      'source'  => 'string,optional',
                      'private' => 'bool,optional',
                      'help'    => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }
      $ticket = new Ticket();

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }

      if (!is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'ticket');
      }

      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      if (in_array($ticket->fields["status"], $ticket->getClosedStatusArray())) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'closed ticket');
      }

      if (!$ticket->canAddFollowups()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'access denied');
      }

      if (isset($params['name']) && !empty($params['name'])) {
         $document_name = addslashes($params['name']);
      } else {
         $document_name = addslashes(sprintf(__('%1$s %2$s'), _x('phone', 'Number'),
                                             $ticket->fields['id']));
      }
      $filename = tempnam(GLPI_TMP_DIR, 'PWS');
      $response = parent::uploadDocument($params, $protocol, $filename, $document_name);
      //An error occured during document upload
      if (parent::isError($protocol, $response)) {
         return $response;
      }

      $doc          = new Document();
      $documentitem = new Document_Item();
      $docid        = $doc->getFromDBbyContent($ticket->fields["entities_id"], $filename);
      if ($docid) {
         $input = array('itemtype'     => $ticket->getType(),
                        'items_id'     => $ticket->getID(),
                        'documents_id' => $doc->getID());

         if ($DB->request('glpi_documents_items', $input)->numrows()) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'document already associated to this ticket');
         }
         $new = $documentitem->add($input);

      } else {
         $input = array('itemtype'              => $ticket->getType(),
                        'items_id'              => $ticket->getID(),
                        'tickets_id'            => $ticket->getID(),
                        'entities_id'           => $ticket->getEntityID(),
                        'is_recursive'          => $ticket->isRecursive(),
                        'documentcategories_id' => $CFG_GLPI["documentcategories_id_forticket"]);
         $new = $doc->add($input);
      }

      // to not add it twice during followup
      unset($_FILES['filename']);

      if (!$new) {
         return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '', self::getDisplayError());
      }

      if (isset($params['comment']) && !empty($params['comment'])) {
         $params['content'] = $params['comment'];
         unset($params['comment']);
      }

      if (isset($params['content']) && !empty($params['content'])) {
         return self::methodAddTicketFollowup($params, $protocol);
      }

      return self::methodGetTicket(array('ticket' => $params['ticket']), $protocol);
   }


   /**
    * List the tickets for an authenticated user
    *
    * @param $params    array of options (author, group, category, status, startdate, enddate, itemtype)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable
   **/
   static function methodListTickets($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('count'     => 'bool,optional',
                      'start'     => 'integer,optional',
                      'limit'     => 'integer,optional',
                      'user'      => 'integer,optional',
                      'recipient' => 'integer,optional',
                      'mine'      => 'bool,optional',
                      'group'     => 'integer,optional',
                      'mygroups'  => 'bool,optional',
                      'category'  => 'integer,optional',
                      'status'    => 'integer,optional',
                      'startdate' => 'datetime,optional',
                      'enddate'   => 'datetime,optional',
                      'itemtype'  => 'string,optional',
                      'item'      => 'integer,optional',
                      'entity'    => 'integer,optional',
                      'satisfaction'
                                  => 'integer,optional',
                      'approval'  => 'text,optional',
                      'approver'  => 'integer,optional',
                      'id2name'   => 'bool,optional',
                      'order'     => 'array,optional',
                      'help'      => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $resp  = array();
      $start = 0;
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }
      $limit = $_SESSION['glpilist_limit'];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }

      $where = $join = '';

      // User (victim)
      if (isset($params['user'])) {
         if (!is_numeric($params['user']) || ($params['user'] < 0)) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'user');
         }
         if (Session::haveRightsOr('ticket', array(Ticket::READALL, Ticket::READGROUP))
             || ($params['user'] == Session::getLoginUserID())) {
            // restrict to author parameter
            $where = " AND `glpi_tickets_users_request`.`users_id` = '" . $params['user'] . "'";
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }

      } else {
         if (Session::haveRightsOr('ticket', array(Ticket::READALL, Ticket::READGROUP))) {
            $where = ''; // Restrict will come from group (if needed)
         } else {
            // Only connected user's tickets'
            $where = " AND `glpi_tickets_users_request`.`users_id`
                           = '" . Session::getLoginUserID() . "'";
         }
      }

      // Group
      if (isset($params['group'])) {
         if (!is_numeric($params['group']) || ($params['group'] < 0)) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'group');
         }

         if (Session::haveRight('ticket', Ticket::READALL)
             || (Session::haveRight('ticket', Ticket::READGROUP)
                 && in_array($params['group'], $_SESSION['glpigroups']))) {
            // restrict to group parameter
            $where = " AND `glpi_groups_tickets_request`.`groups_id` = '" . $params['group'] . "'";
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }

      } else {
         if (Session::haveRight('ticket', Ticket::READGROUP)
             && !Session::haveRight('ticket', Ticket::READALL)) {

            // Connected user's group'
            if (count($_SESSION['glpigroups']) > 0) {
               $where = " AND `glpi_groups_tickets_request`.`groups_id`
                              IN (" . implode(',', $_SESSION['glpigroups']) . ")";
            } else {
               $where = " AND `glpi_tickets_users_request`.`users_id`
                              = '".Session::getLoginUserID()."'";
            }
         }
      }

      // Security
      if (empty($where) && !Session::haveRight('ticket', Ticket::READALL)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'profil');
      }

      // Recipient (person creating the ticket)
      if (isset($params['recipient'])) {
         if (!is_numeric($params['recipient']) || $params['recipient'] < 0) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'recipient');
         }
         // restrict to recipient parameter
         $where = " AND `users_id_recipient`='" . $params['recipient'] . "'";
      }

      // Mine (user or recipient for the ticket)
      if (isset($params['mine'])) {
         $where = " AND (`glpi_tickets_users_request`.`users_id` = '".Session::getLoginUserID()."'
                         OR `users_id_recipient` = '" . Session::getLoginUserID() . "')";
      }

      // Mygroups
      if (isset($param['mygroups'])) {
         $where = " AND `glpi_groups_tickets`.`groups_id`
                        IN (" . implode(',', $_SESSION['glpigroups']) . ")";
      }

      // Entity
      if (isset($params['entity'])) {
         if (!Session::haveAccessToEntity($params['entity'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
         }
         $where = getEntitiesRestrictRequest("WHERE", "glpi_tickets", '', $params['entity']) .
                     $where;
      } else {
         $where = getEntitiesRestrictRequest("WHERE", "glpi_tickets") .
                     $where;
      }

      // Category
      if (isset($params['category'])) {
         if (!is_numeric($params['category']) || ($params['category'] <= 0)) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'category');
         }
         $where .= " AND " . getRealQueryForTreeItem("glpi_itilcategories", $params['category'],
                                                      "glpi_tickets.itilcategories_id");
      }

      if (isset($params['approval']) || isset($params['approver'])) {
         $join .= "INNER JOIN `glpi_ticketvalidations`
                         ON (`glpi_tickets`.`id` = `glpi_ticketvalidations`.`tickets_id` ) ";

         if (isset($params['approver']) && is_numeric($params['approver'])) {
            $where .= " AND `glpi_ticketvalidations`.`users_id_validate`=".$params['approver'];
         }
         $tabstatus = TicketValidation::getAllStatusArray();
         if (isset($params['approval']) && isset($tabstatus[$params['approval']])) {
            $where .= " AND `glpi_ticketvalidations`.`status`='".$params['approval']."'";
         }
      }

      if (isset($params['satisfaction'])) {
         $join .= "INNER JOIN `glpi_ticketsatisfactions`
                        ON (`glpi_tickets`.`id` = `glpi_ticketsatisfactions`.`tickets_id` ) ";
         switch ($params['satisfaction']) {
            case 1:
               $where .= " AND `glpi_ticketsatisfactions`.`date_answered` IS NULL";
               break;

            case 2:
               $where .= " AND `glpi_ticketsatisfactions`.`date_answered` IS NOT NULL";
               break;

            default:
               // survey exists (by Inner Join)
         }
         $params['status'] = Ticket::CLOSED;
      }

      // Status
      if (isset($params['status'])) {
         $status = '';
         foreach (Ticket::getAllStatusArray(true) as $key => $val) {
            $status[] = $key;
         }
         if (!in_array($params['status'], $status)) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'status');
         }
         switch ($params['status']) {
            case 'all':
               // No restriction
               break;

            case 'notclosed' :
               $status = Ticket::getAllStatusArray();
               unset($status[Ticket::CLOSED]);
               $where .= " AND `glpi_tickets`.`status` IN ('".implode("','",array_keys($status))."') ";
               break;

            case 'notold' :
               $status = Ticket::getAllStatusArray();
               unset($status[Ticket::SOLVED], $status[Ticket::CLOSED]);
               $where .= " AND `glpi_tickets`.`status` IN ('".implode("','",array_keys($status))."') ";
               break;

            case 'old' :
               $status = array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray());
               $where .= " AND `glpi_tickets`.`status` IN ('".implode("','",$status)."') ";
               break;

            case 'process' :
               $status = Ticket::getProcessStatusArray();
               $where .= " AND `glpi_tickets`.`status` IN ('".implode("','",$status)."') ";
               break;

            default :
               $where .= " AND `glpi_tickets`.`status` = '" . $params['status'] . "' ";
         }
      }

      // Dates
      if (isset($params["startdate"])) {
         if (preg_match(WEBSERVICES_REGEX_DATETIME, $params["startdate"])
             || preg_match(WEBSERVICES_REGEX_DATE, $params["startdate"])) {

            $where .= " AND `glpi_tickets`.`date` >= '" . $params['startdate'] . "' ";
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'date');
         }
      }

      if (isset($params["enddate"])) {
         if (preg_match(WEBSERVICES_REGEX_DATETIME, $params["enddate"])
             || preg_match(WEBSERVICES_REGEX_DATE, $params["enddate"])) {

            $where .= " AND `glpi_tickets`.`date` <= '" . $params['enddate'] . "' ";
         } else {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'date');
         }
      }

      if (isset($params['itemtype'])) {
         if (!empty($params['itemtype']) && !class_exists($params['itemtype'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'itemtype');
         }
         $where .= " AND `glpi_tickets`.`itemtype`='" . $params['itemtype'] . "'";
      }

      if (isset($params['item'])) {
         if (!isset($params['itemtype'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '','itemtype');
         }
         if (!is_numeric($params['item']) || $params['item'] <= 0) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'item');
         }
         $where .= " AND `glpi_tickets`.`items_id`='" . $params['item'] . "'";
      }

      $orders = array();
      if (isset($params['order'])) {
         if (is_array($params['order'])) {
            $tab = $params['order'];
         } else {
            $tab = array($params['order']=>'DESC');
         }
         foreach ($tab as $key => $val) {
            if ($val != 'ASC') {
               $val = 'DESC';
            }
            $sqlkey = array('id'           => '`glpi_tickets`.`id`',
                            'date'         => '`glpi_tickets`.`date`',
                            'closedate'    => '`glpi_tickets`.`closedate`',
                            'date_mod'     => '`glpi_tickets`.`date_mod`',
                            'status'       => '`glpi_tickets`.`status`',
                            'entities_id'  => '`glpi_tickets`.`entities_id`',
                            'priority'     => '`glpi_tickets`.`priority`');
            if (isset($sqlkey[$key])) {
               $orders[] = $sqlkey[$key]." $val";
            } else {
               return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '','order=$key');
            }
         }
      }

      if (count($orders)) {
         $order = implode(',',$orders);
      } else {
         $order = "`glpi_tickets`.`date_mod` DESC";
      }

      $resp = array ();
      if (isset($params['count'])) {
         $query = "SELECT COUNT(DISTINCT `glpi_tickets`.`id`) AS count
                   FROM `glpi_tickets`
                   $join
                   LEFT JOIN `glpi_tickets_users` AS glpi_tickets_users_request
                        ON (`glpi_tickets`.`id` = `glpi_tickets_users_request`.`tickets_id`
                            AND `glpi_tickets_users_request`.`type` = 1)
                   LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_request
                        ON (`glpi_tickets`.`id` = `glpi_groups_tickets_request`.`tickets_id`
                            AND `glpi_groups_tickets_request`.`type` = 1 )
                   $where";

         $resp = $DB->request($query)->next();
         //debug $resp['query'] = $query;
      } else {
         $query = "SELECT `glpi_tickets`.*,
                          GROUP_CONCAT(DISTINCT `glpi_tickets_users_request`.`users_id` SEPARATOR ',')
                                 AS users_id_request,
                          GROUP_CONCAT(DISTINCT `glpi_tickets_users_observer`.`users_id` SEPARATOR ',')
                                 AS users_id_observer,
                          GROUP_CONCAT(DISTINCT `glpi_tickets_users_assign`.`users_id` SEPARATOR ',')
                                 AS users_id_assign,
                          GROUP_CONCAT(DISTINCT `glpi_groups_tickets_request`.`groups_id` SEPARATOR ',')
                                 AS groups_id_request,
                          GROUP_CONCAT(DISTINCT `glpi_groups_tickets_observer`.`groups_id` SEPARATOR ',')
                                 AS groups_id_observer,
                          GROUP_CONCAT(DISTINCT `glpi_groups_tickets_assign`.`groups_id` SEPARATOR ',')
                                 AS groups_id_assign
                   FROM `glpi_tickets`
                   $join
                   LEFT JOIN `glpi_tickets_users` AS glpi_tickets_users_request
                        ON (`glpi_tickets`.`id` = `glpi_tickets_users_request`.`tickets_id`
                            AND `glpi_tickets_users_request`.`type` = 1)
                   LEFT JOIN `glpi_tickets_users` AS glpi_tickets_users_assign
                        ON (`glpi_tickets`.`id` = `glpi_tickets_users_assign`.`tickets_id`
                            AND `glpi_tickets_users_assign`.`type` = 2)
                   LEFT JOIN `glpi_tickets_users` AS glpi_tickets_users_observer
                        ON (`glpi_tickets`.`id` = `glpi_tickets_users_observer`.`tickets_id`
                            AND `glpi_tickets_users_observer`.`type` = 3)
                   LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_request
                        ON (`glpi_tickets`.`id` = `glpi_groups_tickets_request`.`tickets_id`
                            AND `glpi_groups_tickets_request`.`type` = 1)
                   LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_assign
                        ON (`glpi_tickets`.`id` = `glpi_groups_tickets_assign`.`tickets_id`
                            AND `glpi_groups_tickets_assign`.`type` = 2)
                   LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_observer
                        ON (`glpi_tickets`.`id` = `glpi_groups_tickets_observer`.`tickets_id`
                            AND `glpi_groups_tickets_observer`.`type` = 3)
                   $where
                   GROUP BY `glpi_tickets`.`id`
                   ORDER BY $order
                   LIMIT $start,$limit";

         foreach ($DB->request($query) as $data) {
            $tmp                        = explode(',', $data['users_id_request']);
            $data['users']['requester'] = array();
            foreach($tmp as $id) {
               $data['users']['requester'][]['id'] = $id;
            }

            $tmp                       = explode(',', $data['users_id_observer']);
            $data['users']['observer'] = array();
            foreach($tmp as $id) {
               $data['users']['observer'][]['id'] = $id;
            }

            $tmp                     = explode(',', $data['users_id_assign']);
            $data['users']['assign'] = array();
            foreach($tmp as $id) {
               $data['users']['assign'][]['id'] = $id;
            }

            $tmp                         = explode(',', $data['groups_id_request']);
            $data['groups']['requester'] = array();
            foreach($tmp as $id) {
               $data['groups']['requester'][]['id'] = $id;
            }

            $tmp                        = explode(',', $data['groups_id_observer']);
            $data['groups']['observer'] = array();
            foreach($tmp as $id) {
               $data['groups']['observer'][]['id'] = $id;
            }

            $tmp                      = explode(',', $data['groups_id_assign']);
            $data['groups']['assign'] = array();
            foreach($tmp as $id) {
               $data['groups']['assign'][]['id'] = $id;
            }

            unset($data['groups_id_request'], $data['groups_id_observer'], $data['groups_id_assign'],
                  $data['users_id_request'], $data['users_id_observer'], $data['users_id_assign']);

            $data['solution']
                  = Html::clean(Toolbox::unclean_cross_side_scripting_deep($data['solution']));

            if (isset($params['id2name'])) {
               if ($data['itemtype'] && ($item = getItemForItemtype($data['itemtype']))) {
                  $data['itemtype_name']  = Html::clean($item->getTypeName());
                  if ($item->getFromDB($data['items_id'])) {
                     $data['items_name']  = Html::clean($item->getNameID());
                  } else {
                     $data['items_name']  = NOT_AVAILABLE;
                  }
               }
               foreach ($data['groups'] as $type => $tab) {
                  foreach ($tab as $key => $grp) {
                     $data['groups'][$type][$key]['name']
                           =  Html::clean(Dropdown::getDropdownName('glpi_groups', $grp['id']));
                  }
               }
               foreach ($data['users'] as $type => $tab) {
                  foreach ($tab as $key => $usr) {
                     $data['users'][$type][$key]['name'] =  Html::clean(getUserName($usr['id']));
                  }
               }

               $data['status_name']
                     = Html::clean(Ticket::getStatus($data['status']));
               $data['urgency_name']
                     = Ticket::getUrgencyName($data['urgency']);
               $data['impact_name']
                     = Ticket::getImpactName($data['impact']);
               $data['priority_name']
                     = Ticket::getPriorityName($data['priority']);
               $data['users_name_recipient']
                     = Html::clean(getUserName($data['users_id_recipient']));
               $data['entities_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_entities', $data['entities_id']));
               $data['suppliers_name_assign']
                     = Html::clean(Dropdown::getDropdownName('glpi_suppliers',
                                                             $data['suppliers_id_assign']));
               $data['ticketcategories_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_itilcategories',
                                                             $data['itilcategories_id']));
               $data['requesttypes_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_requesttypes',
                                                             $data['requesttypes_id']));
               $data['solutiontypes_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_solutiontypes',
                                                             $data['solutiontypes_id']));
               $data['slas_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_slas', $data['slas_id']));
               $data['slalevels_name']
                     = Html::clean(Dropdown::getDropdownName('glpi_slalevels',
                                                             $data['slalevels_id']));
               $data['global_validation_name']
                     = Html::clean(TicketValidation::getStatus($data['global_validation']));
            }
            $resp[] = $data;
         }
      }

      return $resp;
   }


//New methods
   static function getTickets($protocol, $params=array()) {
      global $DB,$WEBSERVICE_LINKED_OBJECTS;

      $item = new $params['options']['itemtype']();
      $resp = array();

      if ($item->can($params['data']['id'], READ)) {
          $query = "SELECT ".Ticket::getCommonSelect()."
                    FROM `glpi_tickets` ".Ticket::getCommonLeftJoin()."
                    WHERE (`items_id` = '".$params['data']['id']."'
                          AND `itemtype` = '".$params['options']['itemtype']."') ".
                          getEntitiesRestrictRequest("AND","glpi_tickets")."
                    ORDER BY `glpi_tickets`.`date_mod` DESC";

          $output    = array();
          $ticket    = new Ticket();

          foreach ($DB->request($query) as $data) {
             $params = array('data'          => $data,
                             'searchOptions' => $ticket->getSearchOptions(),
                             'options'       => $params['options']);
             parent::formatDataForOutput($params, $output);
          }
      }
      return $output;
   }


   static function getTicketFollowups($protocol, $params=array()) {
      return self::getTicketLinkedObjects($protocol, $params);
   }


   static function getTicketTasks($protocol, $params=array()) {
      return self::getTicketLinkedObjects($protocol, $params);
   }


   static function getTicketValidations($protocol, $params=array()) {

      $params['options']['orderby_date'] = 'submission_date';
      return self::getTicketLinkedObjects($protocol, $params);
   }


   static function getTicketSolutions($protocol, $params=array()) {
      return self::getTicketLinkedObjects($protocol, $params);
   }


   static function getTicketLinkedObjects($protocol, $params=array()) {
      global $DB;

      //New task or followup
      $item   = new $params['options']['linked_itemtype']();
      $output = $resp = array();

      if ($item->can(-1, READ)) {

         if (isset($params['options']['orderby_date'])) {
            $date = $params['options']['orderby_date'];
         } else {
            $date = 'date';
         }

         $RESTRICT = "";
         if ($item->maybePrivate()
             && ((($item == 'Followup')
                  && !Session::haveRightsOr('followup', array(TicketFollowup::SEEPUBLIC,
                                                              TicketFollowup::SEEPRIVATE)))
                 || (($item == 'Task')
                     && !Session::haveRightsOr('task', array(TicketTask::SEEPUBLIC,
                                                     TicketTask::SEEPRIVATE))))) {
            $RESTRICT = " AND (`is_private` = '0'
                               OR `users_id` ='".Session::getLoginUserID()."') ";
         }

         // Get Number of Followups
         $query = "SELECT *
                   FROM `".$item->getTable()."`
                   WHERE `tickets_id` = '".$params['data']['id']."'
                         $RESTRICT
                   ORDER BY `$date` DESC";

          foreach ($DB->request($query) as $data) {
             $resp   = array();
             $params = array('data'          => $data,
                             'searchOptions' => $item->getSearchOptions(),
                             'options'       => $params['options']);
             parent::formatDataForOutput($params, $resp);
             $output[] = $resp;
          }
      }
      return $output;
   }


   /**
    * Solution of a ticket for an authenticated user
    *
    * @param $params    array of options (ticket, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable as glpi.getTicket
    **/
   static function methodsetTicketSolution($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'       => 'integer,mandatory',
                      'id2name'      => 'bool,optional',
                      'type'         => 'integer,optional',
                      'solution'     => 'text,mandatory',
                      'help'         => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $ticket = new Ticket();

      if (!$ticket->canSolve()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }


      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }

      if (!isset($params['solution'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'solution');
      }

      if (isset($params['type']) && !is_numeric($params['type'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'type=' . $params['type']);
      }

      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND, '', 'ticket');
      }

      if (!$ticket->getFromDB($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND, '', 'solution');
      }

      $input = array('id'       => $ticket->getField('id'),
                     'solution' => addslashes(Toolbox::clean_cross_side_scripting_deep($params['solution'])),
                     'status'   => Ticket::SOLVED);

      if (isset($params['type'])) {
         $input['solutiontypes_id'] = $params['type'];
      }

      if ($ticket->update($input)) {
         unset($params['solution'], $params['type']);
         return self::methodGetTicket($params, $protocol);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',self::getDisplayError());
   }


   /**
    * Assign and actor in a ticket for an authenticated user
    *
    * @param $params    array of options (ticket, id2name)
    * @param $protocol        the communication protocol used
    *
    * @return array of hashtable as glpi.getTicket
    **/
   static function methodsetTicketAssign($params, $protocol) {
      global $DB, $CFG_GLPI;

      if (isset($params['help'])) {
         return array('ticket'                  => 'integer,mandatory',
                      'user'                    => 'integer,optional',
                      'supplier'                => 'integer,optional',
                      'group'                   => 'integer,optional',
                      'user_email'              => 'string,optional',
                      'use_email_notification'  => 'bool,optional',
                      'help'                    => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!Session::haveRight('ticket', Ticket::ASSIGN)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

       if (!isset($params['user'])
           && !isset($params['group'])
           && !isset($params['supplier'])) {
          return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '',
                             'user or group or supplier');
       }
      $ticket = new Ticket();

      if (!isset($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'ticket');
      }
      if (!is_numeric($params['ticket'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                            'ticket=' . $params['ticket']);
      }
      if (!$ticket->can($params['ticket'], READ)) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }

      $ticket_user = new Ticket_User();
      $user        = array('tickets_id'   => $params['ticket'],
                           'type'         => CommonITILActor::ASSIGN);

      // technician : optionnal,  default = none
      if (isset($params['user'])) {
         if (!is_numeric($params['user'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'user');
         }


         $user['users_id'] = $params['user'];
         if ($ticket->getFromDB($params['ticket'])) {
            $entity = $ticket->getField('entities_id');
         }
         if (!$ticket_user->can(-1, UPDATE, $user)
             || !self::checkUserRights($params['user'], 'ticket', Ticket::OWN, $entity)) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }
         if ($ticket->isUser(CommonITILActor::ASSIGN, $user['users_id'] )) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'User already assign for this ticket');
         }

         if (isset($params['user_email'])) {
            if (!NotificationMail::isUserAddressValid($params['user_email'])) {
               return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'user_email');
            }
            $user['alternative_email'] = $params['user_email'];
            $user['use_notification']  = 1;
         } else if (isset($params['use_email_notification']) && $params['use_email_notification']) {
            $user['_additional_assigns'][] = array('users_id'         => $params['user'],
                                                   'use_notification' => 1);
         } else if (isset($params['use_email_notification']) && !$params['use_email_notification']) {
           $user['_additional_assigns'][] = array('users_id'         => $params['user'],
                                                   'use_notification' => 0);
         }

         if (!$ticket_user->add($user)) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'user not assign');
         }
      }

      // group (technicians group) : optionnal,  default = none
      $group_ticket = new Group_Ticket();
      $group = array('tickets_id' => $params['ticket'],
                     'type'       => CommonITILActor::ASSIGN);

      if (isset($params['group'])) {

         if (!is_numeric($params['group'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'group');
         }
         $group['groups_id'] = $params['group'];
         if (!$group_ticket->can(-1, UPDATE, $group)) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }
         if ($ticket->isGroup(CommonITILActor::ASSIGN, $params['group'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'Group already assign for this ticket');
         }
         if (!$group_ticket->add($group)) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'group not assign');
         }
      }

      // supplier to assign : optionnal,  default = none
      $supplier_ticket = new Supplier_Ticket();
      $supplier = array('tickets_id' => $params['ticket'],
                        'type'       => CommonITILActor::ASSIGN);

      if (isset($params['supplier'])) {
         if (!is_numeric($params['supplier'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '', 'supplier');
         }
         $supplier['suppliers_id'] = $params['supplier'];
         if (!$supplier_ticket->can(-1, UPDATE, $supplier)) {
            return self::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
         }
         if ($ticket->isSupplier(CommonITILActor::ASSIGN, $params['supplier'])) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                  'Supplier already assign for this ticket');
         }

         if (!$supplier_ticket->add($supplier)) {
            return self::Error($protocol, WEBSERVICES_ERROR_FAILED, '',
                               'supplier not assign');
         }
      }

      return self::methodGetTicket(array('ticket' => $params['ticket']), $protocol);
   }
}
?>