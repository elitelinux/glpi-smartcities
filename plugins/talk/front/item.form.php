<?php
include ('../../../inc/includes.php');
Session::checkLoginUser();


//add followup
if (isset($_REQUEST['ticketfollowup'])) {
   $fup = new TicketFollowup();
   if (isset($_POST["add"])) {

      $fup->check(-1,'w',$_POST);
      $fup->add($_POST);

      Event::log($fup->getField('tickets_id'), "ticket", 4, "tracking",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a followup'), $_SESSION["glpiname"]));

   }
}

//add task
if (isset($_REQUEST['tickettask'])) {
   $ttask = new TicketTask();
   if (isset($_POST["add"])) {

      $ttask->check(-1,'w',$_POST);
      $ttask->add($_POST);

      Event::log($ttask->getField('tickets_id'), "ticket", 4, "tracking",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a task'), $_SESSION["glpiname"]));

   }
}

//add document
if (isset($_REQUEST['filename']) && !empty($_REQUEST['filename'])) {
   $doc = new Document();
   if (isset($_POST["add"])) {
      $doc->check(-1,'w',$_POST);

      if ($newID = $doc->add($_POST)) {
         Event::log($newID, "documents", 4, "login",
                    sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $doc->fields["name"]));
      }
   }
}

//change ticket status
if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
   $ticket = new Ticket;
   $ticket->update(array('id'     => intval($_REQUEST['tickets_id']), 
                         'status' => intval($_REQUEST['status'])));
}

//delete document
if (isset($_REQUEST['delete_document'])) {
   $document_item = new Document_Item;
   $found_document_items = $document_item->find("itemtype = 'Ticket' ".
                                                " AND items_id = ".intval($_REQUEST['tickets_id']).
                                                " AND documents_id = ".intval($_REQUEST['documents_id']));
   foreach ($found_document_items  as $item) {
      $document_item->delete($item, true);
   }
}

Html::back();