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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginMoreticketTicket extends CommonITILObject {
   
   static $rightname = "plugin_moreticket";
   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   public static function getTypeName($nb=0) {

      return _n('Ticket','Tickets',$nb);
   }
   
   static function emptyTicket(Ticket $ticket) {
      if (!empty($_POST)) {
         self::setSessions($_POST);
      }
   }

   static function beforeAdd(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }
      PluginMoreticketWaitingTicket::preAddWaitingTicket($ticket);
      PluginMoreticketCloseTicket::preAddCloseTicket($ticket);
   }
   
   static function afterAdd(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }
      PluginMoreticketWaitingTicket::postAddWaitingTicket($ticket);
      PluginMoreticketCloseTicket::postAddCloseTicket($ticket);
   }
   
   static function beforeUpdate(Ticket $ticket) {
      
      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      PluginMoreticketWaitingTicket::preUpdateWaitingTicket($ticket);

   }
   
   static function afterUpdate(Ticket $ticket) {
      
      PluginMoreticketWaitingTicket::postUpdateWaitingTicket($ticket);
   }
   
   static function setSessions($input){
      
      foreach($input as $key => $values){
         switch($key){
            case 'reason':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key]   = $values;
               break;
            case 'plugin_moreticket_waitingtypes_id':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key]  = $values;
               break;
            case 'date_report':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $values;
               break;
            case 'solutiontemplates_id':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
            case 'solutiontypes_id':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
            case 'solution':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
         }
      }
   }
}

?>