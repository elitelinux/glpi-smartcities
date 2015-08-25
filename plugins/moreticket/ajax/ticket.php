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

include ('../../../inc/includes.php');

Session::checkLoginUser();

//Html::header_nocache();

if (!isset($_POST['tickets_id']) || empty($_POST['tickets_id'])){
   $_POST['tickets_id'] = 0;
}

if (isset($_POST['action'])) {
   switch ($_POST['action']) {
      case 'showForm':
         header("Content-Type: text/html; charset=UTF-8");
         // Ticket is waiting
         $waiting_ticket = new PluginMoreticketWaitingTicket();
         $waiting_ticket->showForm($_POST['tickets_id']);
         // Ticket is closed
         if (isset($_POST['type']) && $_POST['type'] == 'add') {
            $close_ticket = new PluginMoreticketCloseTicket();
            $close_ticket->showForm($_POST['tickets_id']);
         }
         break;
   }
}
?>