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

$closeTicket = new PluginMoreticketCloseTicket();

if(isset($_POST["add"])){
   $closeTicket->check(-1, UPDATE, $_POST);
        
   $doc = new Document();
   $doc->check(-1, CREATE, $_POST);
   $DocId = $doc->add($_POST);

   $test = $closeTicket->add(array('requesters_id' => $_POST['requesters_id'],
                                   'tickets_id'    => $_POST['tickets_id'],
                                   'date'          => $_POST['date'],
                                   'comment'       => $_POST['comment'], 
                                   'documents_id'  => $DocId));
   Html::back();
}

?>