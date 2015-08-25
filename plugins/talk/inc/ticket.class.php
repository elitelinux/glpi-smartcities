<?php

class PluginTalkTicket extends CommonGLPI {
   static $rightname = "plugin_talk_is_active";
   const ACTIVE = 1024;

   static function getTypeName($nb=0) {
      return __("Processing ticket", "talk");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item instanceOf Ticket) {
         $timeline = self::geTimelineItems($item, '');
         $nb_elements = count($timeline);
         return self::createTabEntry(self::getTypeName(2), $nb_elements);
      }
      return '';
   }

   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
   **/
   function getRights($interface='central') {
      $values = array(self::ACTIVE => __('Active'));
      return $values;
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item instanceof Ticket) {
         return self::showForTicket($item, $withtemplate);
      }
      return true;
   }

   static function showForTicket(Ticket $ticket, $withtemplate = array()) {
      $rand = mt_rand();

      echo "<div class='talk_box'>";
      self::showForm($ticket, $rand);
      self::showHistory($ticket, $rand);
      echo "</div>";
   }

   static function showForm(Ticket $ticket, $rand) {
      global $CFG_GLPI;

      //check global rights
      if (!Session::haveRight("ticket", Ticket::READMY)
       && !Session::haveRightsOr("followup", array(TicketFollowup::SEEPUBLIC, 
                                                   TicketFollowup::SEEPRIVATE))) {
         return false;
      }

      // javascript function for add and edit items
      echo "<script type='text/javascript' >\n";
      ?>
      function getUrlVar(key) {
         var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search);
         return result && unescape(result[1]) || "";
      }
      <?php

      echo "function viewAddSubitem" . $ticket->fields['id'] . "$rand(itemtype) {\n";
      $params = array('type'       => 'itemtype',
                      'parenttype' => 'Ticket',
                      'tickets_id' => $ticket->fields['id'],
                      'load_kb_sol'=> "load_kb_sol_value",
                      'id'         => -1);
      $out = Ajax::updateItemJsCode("viewitem" . $ticket->fields['id'] . "$rand",
                             $CFG_GLPI["root_doc"]."/plugins/talk/ajax/viewsubitem.php", $params, "", false);
      $out = str_replace("\"load_kb_sol_value\"", "getUrlVar('load_kb_sol')", $out);
      echo str_replace("\"itemtype\"", "itemtype", $out);
      echo "};";
      $out = "function viewEditSubitem" . $ticket->fields['id'] . "$rand(e, itemtype, items_id, o) {\n
               var target = e.target || window.event.srcElement;
               if (target.nodeName == 'a') return;
               if (target.className == 'read_more_button') return;";
      $params = array('type'       => 'itemtype',
                      'parenttype' => 'Ticket',
                      'tickets_id' => $ticket->fields['id'],
                      'id'         => 'items_id');
      $out.= Ajax::updateItemJsCode("viewitem" . $ticket->fields['id'] . "$rand",
                             $CFG_GLPI["root_doc"]."/plugins/talk/ajax/viewsubitem.php", $params, "", false);
      $out = str_replace("\"itemtype\"", "itemtype", $out);
      $out = str_replace("\"items_id\"", "items_id", $out);
      echo $out;

      //scroll to edit form
      echo "window.scrollTo(0,110);";

      // add a mark to currently edited element
      echo "var found_active = $('.talk_active');
            i = found_active.length;
            while(i--) {
               var classes = found_active[i].className.replace( /(?:^|\s)talk_active(?!\S)/ , '' );
               found_active[i].className = classes;
            }
            o.className = o.className + ' talk_active';
      };";
      echo "</script>\n";
      
      //check sub-items rights
      $tmp = array('tickets_id' => $ticket->getID());
      $fup             = new TicketFollowup;
      $ttask           = new TicketTask;

      $canadd_fup      = TicketFollowup::canCreate() && $fup->can(-1, UPDATE, $tmp);
      $canadd_task     = TicketTask::canCreate() && $ttask->can(-1, UPDATE, $tmp);
      $canadd_document = Document::canCreate();
      $canadd_solution = Ticket::canUpdate() && $ticket->canSolve();

      if (!$canadd_fup && !$canadd_task && !$canadd_document && !$canadd_solution ) {
         return false;
      }

      //show choices
      if ($ticket->fields["status"] != CommonITILObject::SOLVED
         && $ticket->fields["status"] != CommonITILObject::CLOSED) {
         echo "<h2>"._sx('button', 'Add')." : </h2>";
         echo "<div class='talk_form'>";
         echo "<ul class='talk_choices'>";
         if ($canadd_fup) {   
            echo "<li class='followup' onclick='".
                 "javascript:viewAddSubitem".$ticket->fields['id']."$rand(\"TicketFollowup\");'>"
                 .__("Followup")."</li>";
         }
         if ($canadd_task) {   
            echo "<li class='task' onclick='".
                 "javascript:viewAddSubitem".$ticket->fields['id']."$rand(\"TicketTask\");'>"
                 .__("Task")."</li>";
         }
         if ($canadd_document) { 
            echo "<li class='document' onclick='".
                 "javascript:viewAddSubitem".$ticket->fields['id']."$rand(\"Document_Item\");'>"
                 .__("Document")."</li>";
         }
         if ($canadd_solution) { 
            echo "<li class='solution' onclick='".
                 "javascript:viewAddSubitem".$ticket->fields['id']."$rand(\"Solution\");'>"
                 .__("Solution")."</li>";
            self::addJavascriptForViewAddSubitem($ticket, $rand);

         }
         echo "</ul>"; // talk_choices
         echo "<div class='clear'>&nbsp;</div>";
         echo "</div>"; //end talk_form      
      } 

      echo "<div class='ajax_box' id='viewitem" . $ticket->fields['id'] . "$rand'></div>\n";

   }

   static function geTimelineItems(Ticket $ticket, $rand) {
      global $DB, $CFG_GLPI;

      $timeline = array();

      $user                  = new User;
      $group                 = new Group;
      $followup_obj          = new TicketFollowup;
      $task_obj              = new TicketTask;
      $document_item_obj     = new Document_Item;
      $ticket_valitation_obj = new TicketValidation;

      //checks rights
      $showpublic = Session::haveRightsOr("followup", array(TicketFollowup::SEEPUBLIC, 
                                                            TicketFollowup::SEEPRIVATE))
                 && Session::haveRightsOr("task",     array(TicketTask::SEEPUBLIC, 
                                                            TicketTask::SEEPRIVATE));
      $restrict_fup = $restrict_task = "";
      if (!Session::haveRight("ticket", TicketFollowup::SEEPRIVATE)) {
         $restrict_fup = " AND (`is_private` = '0'
                            OR `users_id` ='" . Session::getLoginUserID() . "') ";
      }
      if (!Session::haveRight("ticket", TicketTask::SEEPRIVATE)) {
         $restrict_task = " AND (`is_private` = '0'
                            OR `users_id` ='" . Session::getLoginUserID() . "') ";
      }


      if (!$showpublic) {
         $restrict = " AND 1 = 0";
      }

      //add ticket followups to timeline
      $followups = $followup_obj->find("tickets_id = ".$ticket->getID()." $restrict_fup", 'date DESC');
      foreach ($followups as $followups_id => $followup) {
         $followup_obj->getFromDB($followups_id);
         $can_edit = $followup_obj->canUpdateItem();
         $followup['can_edit'] = $can_edit;
         $timeline[$followup['date']."_followup_".$followups_id] = array('type' => 'TicketFollowup', 'item' => $followup);
      }


      //add ticket tasks to timeline
      $tasks = $task_obj->find("tickets_id = ".$ticket->getID()." $restrict_task", 'date DESC');
      foreach ($tasks as $tasks_id => $task) {
         $task_obj->getFromDB($tasks_id);
         $can_edit = $task_obj->canUpdateItem();
         $task['can_edit'] = $can_edit;
         $timeline[$task['date']."_task_".$tasks_id] = array('type' => 'TicketTask', 'item' => $task);
      }


      //add ticket documents to timeline
      $document_obj = new Document;
      $document_items = $document_item_obj->find("itemtype = 'Ticket' AND items_id = ".$ticket->getID());
      foreach ($document_items as $document_item) {
         $document_obj->getFromDB($document_item['documents_id']);
         $timeline[$document_obj->fields['date_mod']."_document_".$document_item['documents_id']] 
            = array('type' => 'Document_Item', 'item' => $document_obj->fields);
      }

      //add assign changes
      /*$log_obj = new Log;
      $gassign_items = $log_obj->find("itemtype = 'Ticket' AND items_id = ".$ticket->getID()." 
                                       AND itemtype_link = 'Group' AND linked_action = '15'");

      foreach ($gassign_items as $logs_id => $gassign) {
         //find group
         $group_name = preg_replace("#(.*)\s\([0-9]*\)#", "$1", $gassign['new_value']);
         $groups = $group->find("name = '$group_name'");
         $first_group = array_shift($groups);
         $group->getFromDB($first_group['id']);
         $content = __("Assigned to")." : ".
                    "<img src='".$CFG_GLPI['root_doc']."/plugins/talk/pics/group.png' class='group_assign' />".
                    "&nbsp;<strong>".$group->getLink()."</strong>";

         //find user
         $user_name = preg_replace("#(.*)\s\([0-9]*\)#", "$1", $gassign['user_name']);
         $users = $user->find("CONCAT(firstname, ' ', realname) = '$user_name'");
         $first_user = array_shift($users);
         if ($first_user == NULL) {
            $first_user['id'] = false;
         }

         $timeline[$gassign['date_mod']."_assign_".$logs_id] = array('type' => 'Assign', 
                                                                     'item' => array(
                                                                        'date'     => $gassign['date_mod'],
                                                                        'content'  => $content,
                                                                        'can_edit' => false,
                                                                        'users_id' => $first_user['id']
                                                                     ));
      }*/

      //add existing solution
      if (!empty($ticket->fields['solution'])) {
         $users_id = 0;
         $solution_date = $ticket->fields['solvedate'];

         //search date and user of last solution in glpi_logs
         if ($res_solution = $DB->query("SELECT date_mod AS solution_date, user_name FROM glpi_logs
                                     WHERE itemtype = 'Ticket' 
                                     AND items_id = ".$ticket->getID()."
                                     AND id_search_option = 24
                                     ORDER BY id DESC
                                     LIMIT 1")) {
            $data_solution = $DB->fetch_assoc($res_solution);
            if (!empty($data_solution['solution_date'])) $solution_date = $data_solution['solution_date'];
            
            // find user
            if (!empty($data_solution['user_name'])) {
               $users_id = addslashes(trim(preg_replace("/.*\(([0-9]+)\)/", "$1", $data_solution['user_name'])));
            }
         }

         // fix trouble with html_entity_decode who skip accented characters (on windows browser)
         $solution_content = preg_replace_callback("/(&#[0-9]+;)/", function($m) { 
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); 
         }, $ticket->fields['solution']);
      
         $timeline[$solution_date."_solution"] 
            = array('type' => 'Solution', 'item' => array('id'               => 0,
                                                          'content'          => Html::clean(html_entity_decode($solution_content)),
                                                          'date'             => $solution_date, 
                                                          'users_id'         => $users_id, 
                                                          'solutiontypes_id' => $ticket->fields['solutiontypes_id'],
                                                          'can_edit'         => Ticket::canUpdate() && $ticket->canSolve()));
      }

      // add ticket validation to timeline
       if ($ticket->fields['type'] == Ticket::DEMAND_TYPE && 
            (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST) 
          || Session::haveRight('ticketvalidation', TicketValidation::CREATEREQUEST))
       || $ticket->fields['type'] == Ticket::INCIDENT_TYPE &&
            (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
          || Session::haveRight('ticketvalidation', TicketValidation::CREATEINCIDENT))
          ) {
        
         $ticket_validations = $ticket_valitation_obj->find('tickets_id = '.$ticket->getID());
         foreach ($ticket_validations as $validations_id => $validation) {
            $canedit = $ticket_valitation_obj->can($validations_id, UPDATE);
            $user->getFromDB($validation['users_id_validate']);
            $timeline[$validation['submission_date']."_validation_".$validations_id] 
               = array('type' => 'TicketValidation', 'item' => array(
                  'id'        => $validations_id,
                  'date'      => $validation['submission_date'],
                  'content'   => __('Validation request')." => ".$user->getlink().
                                 "<br>".$validation['comment_submission'],
                  'users_id'  => $validation['users_id'], 
                  'can_edit'  => $canedit
               ));

            if (!empty($validation['validation_date'])) {
               $timeline[$validation['validation_date']."_validation_".$validations_id] 
               = array('type' => 'TicketValidation', 'item' => array(
                  'id'        => $validations_id,
                  'date'      => $validation['validation_date'],
                  'content'   => __('Validation request answer')." : ".
                                 _sx('status', 
                                     ucfirst(TicketValidation::getStatus($validation['status'])))."<br>".
                                 $validation['comment_validation'],
                  'users_id'  => $validation['users_id_validate'], 
                  'status'    => "status_".$validation['status'], 
                  'can_edit'  => $canedit
               ));
            }
         }
      }

      //reverse sort timeline items by key (date)
      krsort($timeline);
         
      return $timeline;
   }

   static function addJavascriptForViewAddSubitem(Ticket $ticket, $rand) {
      echo "<script>";
      echo 'function getUrlVar(key) {
               var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search);
               return result && unescape(result[1]) || "";
            }';
      echo "if (getUrlVar('load_kb_sol') != '') {";
      echo "   viewAddSubitem".$ticket->fields['id']."$rand(\"Solution\");";
      echo "}";
      echo "</script>";
   }

   static function showHistory(Ticket $ticket, $rand) {
      global $CFG_GLPI, $DB;

      //get ticket actors
      $ticket_users_keys = self::prepareTicketUser($ticket);

      $user = new User;
      $followup_obj = new TicketFollowup;
      $pics_url = "../plugins/talk/pics";
      
      $timeline = self::geTimelineItems($ticket, $rand);

      //include lib for parsing url 
      require GLPI_ROOT."/plugins/talk/lib/urllinker.php";

      //display timeline
      echo "<div class='talk_history'>";

      $tmp = array_values($timeline);
      $first_item = array_shift($tmp);

      //don't display title on solution approbation
      if ($first_item['type'] != 'Solution' 
         || $ticket->fields["status"] != CommonITILObject::SOLVED) {
         self::showHistoryHeader();
      }


      $timeline_index = 0;
      foreach ($timeline as $item) {
         $item_i = $item['item'];

         // don't display empty followup (ex : solution approbation)
         if ($item['type'] == 'TicketFollowup' && empty($item_i['content'])) {
            continue;
         }

         self::addJavascriptForViewAddSubitem($ticket, $rand);

         $date = "";
         if (isset($item_i['date'])) $date = $item_i['date'];
         if (isset($item_i['date_mod'])) $date = $item_i['date_mod'];
         
         // check if curent item user is assignee or requester
         $user_position = 'left';
         if (isset($ticket_users_keys[$item_i['users_id']]) 
            && $ticket_users_keys[$item_i['users_id']] == CommonItilActor::ASSIGN
            || $item['type'] == 'Assign') {
            $user_position = 'right';
         }

         //display solution in middle
         if ($timeline_index == 0 && $item['type'] == "Solution" 
            && $ticket->fields["status"] == CommonITILObject::SOLVED) {
            $user_position.= ' middle';
         }
         
         echo "<div class='h_item $user_position'>";

         echo "<div class='h_info'>";
         echo "<div class='h_date'>".Html::convDateTime($date)."</div>";
         if ($item_i['users_id'] !== false) {
            echo "<div class='h_user'>";
            if (isset($item_i['users_id']) && $item_i['users_id'] != 0) {
               $user->getFromDB($item_i['users_id']);
               echo "<div class='tooltip_picture_border'>";
               echo "<img class='user_picture' alt=\"".__s('Picture')."\" src='".
                  User::getThumbnailURLForPicture($user->fields['picture'])."'>";
               echo "</div>";
               echo $user->getLink();
            } else echo __("Requester");
            echo "</div>";
         }
         echo "</div>";

         echo "<div class='h_content ".$item['type'].
              ((isset($item_i['status'])) ? " ".$item_i['status'] : "").
              "'";
         if (!in_array($item['type'], array('Document_Item', 'Assign')) && $item_i['can_edit']) {     
            echo " ondblclick='javascript:viewEditSubitem".$ticket->fields['id']."$rand(event, \"".$item['type']."\", ".$item_i['id'].", this)'";
         }
         echo ">";
         if (isset($item_i['requesttypes_id']) 
               && file_exists("$pics_url/".$item_i['requesttypes_id'].".png")) {
            echo "<img src='$pics_url/".$item_i['requesttypes_id'].".png' title='' class='h_requesttype' />";
         }

         if (isset($item_i['content'])) {
            $content = $item_i['content'];
            $content = linkUrlsInTrustedHtml($content);
            //$content = nl2br($content);

            $long_text = "";
            if(substr_count($content, "<br") > 30 || strlen($content) > 2000) {
               $long_text = "long_text";
            }

            echo "<div class='item_content $long_text'>";
            echo "<p>";
            echo $content;
            echo "</p>";
            if (!empty($long_text)) {
               echo "<p class='read_more'>";
               echo "<a class='read_more_button'>.....</a>";
               echo "</p>";
            }
            echo "</div>";
         }

         echo "<div class='b_right'>";
            if (isset($item_i['solutiontypes_id']) && !empty($item_i['solutiontypes_id'])) {
               echo Dropdown::getDropdownName("glpi_solutiontypes", $item_i['solutiontypes_id'])."<br>";
            }
            if (isset($item_i['taskcategories_id']) && !empty($item_i['taskcategories_id'])) {
               echo Dropdown::getDropdownName("glpi_taskcategories", $item_i['taskcategories_id'])."<br>";
            }
            if (isset($item_i['actiontime']) && !empty($item_i['actiontime'])) {
               echo "<span class='actiontime'>";
               echo Html::timestampToString($item_i['actiontime'], false);
               echo "</span>";
            }
            if (isset($item_i['state'])) {
               echo "<span class='state state_".$item_i['state']."'>";
               echo Planning::getState($item_i['state']);
               echo "</span>";
            }
            if (isset($item_i['begin'])) {
               echo "<span class='planification'>";
               echo Html::convDateTime($item_i["begin"]);
               echo " => ";
               echo Html::convDateTime($item_i["end"]);
               echo "</span>";
            }
            if (isset($item_i['users_id_tech'])) {
               echo "<div class='users_id_tech'>";
               $user->getFromDB($item_i['users_id_tech']);
               echo "<div class='tooltip_picture_border'>";
               echo "<img class='user_picture' alt=\"".__s('Picture')."\" src='".
                  User::getThumbnailURLForPicture($user->fields['picture'])."'>";
               echo "</div>";
               echo $user->getLink();
               echo "</div>";
            }

            // show "is_private" icon
            if (isset($item_i['is_private']) && $item_i['is_private']) {
               echo "<div class='private'>";
               echo __('Private');
               echo "</div>";
            }
      
         echo "</div>";

         if ($item['type'] == 'Document_Item') {
            $filename = $item_i['filename'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            echo "<img src='";
            if (empty($filename)) {
               $filename = $item_i['name'];
            }
            if (file_exists(GLPI_ROOT."/pics/icones/$ext-dist.png")) {
               echo $CFG_GLPI['root_doc']."/pics/icones/$ext-dist.png";
            } else {
               echo "$pics_url/file.png";
            }
            echo "' title='file' />&nbsp;";
            echo "<a href='".$CFG_GLPI['root_doc']."/front/document.send.php?docid=".$item_i['id']
                ."&tickets_id=".$ticket->getID()
                ."' target='_blank'>$filename";
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'bmp'))) {
               echo "<div class='talk_img_preview'>";
               echo "<img src='".$CFG_GLPI['root_doc']."/front/document.send.php?docid=".$item_i['id']
                ."&tickets_id=".$ticket->getID()
                ."'/>";
               echo "</div>";
            }

            echo "</a>";
            if (!empty($item_i['mime'])) echo "&nbsp;(".$item_i['mime'].")";
            echo "<a href='".$CFG_GLPI['root_doc'].
                 "/front/document.form.php?id=".$item_i['id']."' class='edit_document' title='".
                 _sx("button", "Update")."'>";
            echo "<img src='../plugins/talk/pics/edit.png' /></a>";
            echo "<a href='".$CFG_GLPI['root_doc'].
                 "/plugins/talk/front/item.form.php?delete_document&documents_id=".$item_i['id'].
                 "&tickets_id=".$ticket->getID()."' class='delete_document' title='".
                 _sx("button", "Delete permanently")."'>";
            echo "<img src='../plugins/talk/pics/delete.png' /></a>";            
         }
         echo "</div>"; //end h_content

         echo "</div>"; //end  h_item

         if ($timeline_index == 0 && $item['type'] == "Solution" 
            && $ticket->fields["status"] == CommonITILObject::SOLVED) {
            echo "<div class='break'></div>";
            echo "<div class='approbation_form'>";
            $followup_obj->showApprobationForm($ticket);
            echo "</div>";
            echo "<hr class='approbation_separator' />";
            self::showHistoryHeader();
         }
         $timeline_index++;
      } // end foreach timeline
      echo "<div class='break'></div>";

      // recall ticket content
      echo "<div class='h_item middle'>";
         echo "<div class='h_info'>";
         echo "<div class='h_date'>".Html::convDateTime($ticket->fields['date'])."</div>";
            echo "<div class='h_user'>";
               $user->getFromDB($ticket->fields['users_id_recipient']);
               echo "<div class='tooltip_picture_border'>";
               echo "<img class='user_picture' alt=\"".__s('Picture')."\" src='".
                  User::getThumbnailURLForPicture($user->fields['picture'])."'>";
               echo "</div>";
               echo $user->getLink();
            echo "</div>";
         echo "</div>";
         echo "<div class='h_content TicketContent'>";
            echo "<div class='b_right'>".__("Ticket recall", 'talk')."</div>";
            echo "<div class='ticket_title'>";
            echo html_entity_decode($ticket->fields['name']);
            echo "</div>";
            echo "<div class='ticket_description'>";
            echo html_entity_decode($ticket->fields['content']);
            echo "</div>";
         echo "</div>";
      echo "</div>";
      echo "<div class='break'></div>";

      // end timeline
      echo "</div>";
      echo "<script type='text/javascript'>read_more();</script>";
   }

   static function showHistoryHeader() {
      echo "<h2>".__("Actions historical", "talk")." : </h2>";
      self::filterTimeline();
   }

   static function filterTimeline() {
      global $CFG_GLPI;

      $pics_url = $CFG_GLPI['root_doc']."/plugins/talk/pics";
      echo "<div class='filter_timeline'>";
      echo "<label>".__("Timeline filter", "talk")." : </label>";
      echo "<ul>";
      echo "<li><a class='reset' title=\"".__("Reset display options").
         "\"><img src='$pics_url/reset.png' /></a></li>";
      echo "<li><a class='Solution' title='".__("Solution").
         "'><img src='$pics_url/solution_min.png' /></a></li>";
      echo "<li><a class='TicketValidation' title='".__("Validation").
         "'><img src='$pics_url/validation_min.png' /></a></li>";
      echo "<li><a class='Document_Item' title='".__("Document").
         "'><img src='$pics_url/document_min.png' /></a></li>";
      echo "<li><a class='TicketTask' title='".__("Task").
         "'><img src='$pics_url/task_min.png' /></a></li>";
      echo "<li><a class='TicketFollowup' title='".__("Followup").
         "'><img src='$pics_url/followup_min.png' /></a></li>";
      echo "</ul>";
      echo "</div>";

      echo "<script type='text/javascript'>filter_timeline();</script>";
   }

   static function prepareTicketUser(Ticket $ticket) {
      global $DB;

      $query = "SELECT
            DISTINCT users_id, type
         FROM (
            SELECT usr.id as users_id, tu.type as type
            FROM `glpi_tickets_users` tu
            LEFT JOIN glpi_users usr
               ON tu.users_id = usr.id
            WHERE tu.`tickets_id` = ".$ticket->getId()."
            
            UNION 
            
            SELECT usr.id as users_id, gt.type as type
            FROM glpi_groups_tickets gt
            LEFT JOIN glpi_groups_users gu
               ON gu.groups_id = gt.groups_id
            LEFT JOIN glpi_users usr
               ON gu.users_id = usr.id
            WHERE gt.tickets_id = ".$ticket->getId()."
            
            UNION 
            
            SELECT usr.id as users_id, '2' as type
            FROM glpi_profiles prof
            LEFT JOIN glpi_profiles_users pu
               ON pu.profiles_id = prof.id
            LEFT JOIN glpi_users usr
               ON usr.id = pu.users_id
            LEFT JOIN glpi_profilerights pr
               ON pr.profiles_id = prof.id
            WHERE pr.name = 'ticket'
               AND pr.rights & ".Ticket::OWN." = ".Ticket::OWN."
         ) AS allactors
         WHERE type != ".CommonItilActor::OBSERVER."
         GROUP BY users_id
         ORDER BY type DESC";
      $res = $DB->query($query);
      $ticket_users_keys = array();
      while ($current_tu = $DB->fetch_assoc($res)) {
         $ticket_users_keys[$current_tu['users_id']] = $current_tu['type'];
      }

      return $ticket_users_keys;
   }

   static function showSubForm(CommonDBTM $item, $id, $params) {
      if ($item instanceof Document_Item) {
         self::showSubFormDocument_Item($params['tickets_id'], $params);

      } else if ($item instanceof TicketFollowup || $item instanceof TicketTask) {
         self::showMultipartForm($item, $id, $params);

      } else if (method_exists($item, "showForm")) {
         $item->showForm($id, $params);

      }
   }

   static function showMultipartForm($item, $id, $params)  {
      $classname = get_class($item);

      //get html of followup form
      ob_start();
      $item->showForm($id, $params);
      $fup_form_html = ob_get_contents();
      ob_clean();

      //get html of document form
      $params['no_form'] = true;
      self::showSubFormDocument_Item($params['tickets_id'], $params);
      $doc_form_html = ob_get_contents();
      ob_end_clean();

      if (preg_match("/<input type=['\"]submit['\"].*name=['\"]update['\"]/", $fup_form_html) == false) {
         //replace action param to redirect to talk controller (only for add)
         $fup_form_html = str_replace("front/".strtolower($classname).".form.php", 
                                      "plugins/talk/front/item.form.php?".strtolower($classname)."=1", 
                                      $fup_form_html);

         //add multipart attribute to permit doc upload
         $fup_form_html = str_replace("<form ", 
                                      "<form enctype='multipart/form-data'", 
                                      $fup_form_html);        

         //insert document upload                                           
         $fup_form_html = preg_replace("/(<tr class='tab_bg_2'><td class='center' colspan='4'><input type=['\"]submit['\"].*>)/", 
                                       "<tr><td>".$doc_form_html."</td></tr>$0", $fup_form_html, 1);

         //replace submit button by a splitted button who can change ticket status
         if (isset($_SESSION["glpiactiveprofile"])
             && $_SESSION["glpiactiveprofile"]["interface"] == "central") {
            $fup_form_html = preg_replace("/<input type=['\"]submit['\"].*>/", 
                                          self::getSplittedSubmitButtonHtml($params['tickets_id']), 
                                          $fup_form_html, 1); //only one occurence
         } else {
            $ticket = new Ticket;
            $ticket->getFromDB($params['tickets_id']);
            if (in_array($ticket->fields['status'], array(CommonITILObject::WAITING, 
                                                          CommonITILObject::SOLVED, 
                                                          CommonITILObject::CLOSED))) {
               $status_input = "<input type='hidden' name='status' value='".CommonITILObject::ASSIGNED."'>";
               $fup_form_html = preg_replace("/<input type='submit'.*>/", 
                                             $status_input."$0",
                                             $fup_form_html, 
                                             1); //only one occurence
            }
         }
      }

      echo $fup_form_html;
   }

   static function showSubFormDocument_Item($ID, $params) {
      global $DB, $CFG_GLPI;

      $item = new Ticket;
      $item->getFromDB($ID);

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      $linkparam = '';

      if (get_class($item) == 'Ticket') {
         $linkparam = "&amp;tickets_id=".$item->fields['id'];
      }

      $canedit       =  $item->canAddItem('Document') && Document::canView();
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();
      $order = "DESC";
      $sort = "`assocdate`";
      
      $query = "SELECT `glpi_documents_items`.`id` AS assocID,
                       `glpi_documents_items`.`date_mod` AS assocdate,
                       `glpi_entities`.`id` AS entityID,
                       `glpi_entities`.`completename` AS entity,
                       `glpi_documentcategories`.`completename` AS headings,
                       `glpi_documents`.*
                FROM `glpi_documents_items`
                LEFT JOIN `glpi_documents`
                          ON (`glpi_documents_items`.`documents_id`=`glpi_documents`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_documents`.`entities_id`=`glpi_entities`.`id`)
                LEFT JOIN `glpi_documentcategories`
                        ON (`glpi_documents`.`documentcategories_id`=`glpi_documentcategories`.`id`)
                WHERE `glpi_documents_items`.`items_id` = '$ID'
                      AND `glpi_documents_items`.`itemtype` = '".$item->getType()."' ";

      if (Session::getLoginUserID()) {
         $query .= getEntitiesRestrictRequest(" AND","glpi_documents",'','',true);
      } else {
         // Anonymous access from FAQ
         $query .= " AND `glpi_documents`.`entities_id`= '0' ";
      }

      // Document : search links in both order using union
      if ($item->getType() == 'Document') {
         $query .= "UNION
                    SELECT `glpi_documents_items`.`id` AS assocID,
                           `glpi_documents_items`.`date_mod` AS assocdate,
                           `glpi_entities`.`id` AS entityID,
                           `glpi_entities`.`completename` AS entity,
                           `glpi_documentcategories`.`completename` AS headings,
                           `glpi_documents`.*
                    FROM `glpi_documents_items`
                    LEFT JOIN `glpi_documents`
                              ON (`glpi_documents_items`.`items_id`=`glpi_documents`.`id`)
                    LEFT JOIN `glpi_entities`
                              ON (`glpi_documents`.`entities_id`=`glpi_entities`.`id`)
                    LEFT JOIN `glpi_documentcategories`
                              ON (`glpi_documents`.`documentcategories_id`=`glpi_documentcategories`.`id`)
                    WHERE `glpi_documents_items`.`documents_id` = '$ID'
                          AND `glpi_documents_items`.`itemtype` = '".$item->getType()."' ";

         if (Session::getLoginUserID()) {
            $query .= getEntitiesRestrictRequest(" AND","glpi_documents",'','',true);
         } else {
            // Anonymous access from FAQ
            $query .= " AND `glpi_documents`.`entities_id`='0' ";
         }
      }
      $query .= " ORDER BY $sort $order ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $documents = array();
      $used      = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $documents[$data['assocID']] = $data;
            $used[$data['id']]           = $data['id'];
         }
      }

      if ($item->canAddItem('Document') && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >=0 ) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_documents",'',$entities,true);
         $q = "SELECT COUNT(*)
               FROM `glpi_documents`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);


         if ($item->getType() == 'Document') {
            $used[$ID] = $ID;
         }

         if (!isset($params['no_form']) || $params['no_form'] == false) {
            echo "<div class='firstbloc'>";
            echo "<form name='documentitem_form$rand' id='documentitem_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('Document')."'  enctype=\"multipart/form-data\">";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='5'>".__('Add a document')."</th></tr>";
         }
         echo "<tr class='tab_bg_1'>";

         if (!isset($params['no_form']) || $params['no_form'] == false) {
            echo "<td class='center'>";
            _e('Heading');
            echo "</td><td>";
            DocumentCategory::dropdown(array('entity' => $entities));
            echo "</td>";
            echo "<td class='right'>";
         } else {
            echo "<td class='center'>".__('Add a document')."</td>";
            echo "<td style='padding-left:50px'>";
         }
         echo "<input type='hidden' name='entities_id' value='$entity'>";
         echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
         echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
         echo "<input type='hidden' name='items_id' value='$ID'>";
         if ($item->getType() == 'Ticket') {
            echo "<input type='hidden' name='tickets_id' value='$ID'>";
         }
         echo Html::file();
         echo "</td><td class='left'>";
         echo "(".Document::getMaxUploadSize().")&nbsp;";
         echo "</td>";

         if (!isset($params['no_form']) || $params['no_form'] == false) {
            echo "<td class='center' width='20%'>";
            echo "<input type='submit' name='add' value=\""._sx('button', 'Add a new file')."\"
                   class='submit'></td>";
         }
         echo "</tr>";

         if (!isset($params['no_form']) || $params['no_form'] == false) {
            echo "</table>";
            Html::closeForm();
         }

         if (Session::haveRight('document', READ) && $nb > count($used) &&
            (!isset($params['no_form']) || $params['no_form'] == false)) {
            echo "<form name='document_form$rand' id='document_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('Document')."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
               echo "<input type='hidden' name='documentcategories_id' value='".
                      $CFG_GLPI["documentcategories_id_forticket"]."'>";
            }

            Document::dropdown(array('entity' => $entities ,
                                     'used'   => $used));
            echo "</td>";
            echo "<td class='center' width='20%'>";
            echo "<input type='submit' name='add' value=\"".
                     _sx('button', 'Associate an existing document')."\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }
      

   }

   static function showSubFormSolution($ID) {
      $ticket = new Ticket;
      $ticket->getFromDB($ID);
      if (isset($_REQUEST['load_kb_sol']) && !empty($_REQUEST['load_kb_sol'])) {
         $ticket->showSolutionForm($_REQUEST['load_kb_sol']);
      } else {
         $ticket->showSolutionForm();
      }
   }

   static function getSplittedSubmitButtonHtml($tickets_id, $action = "add") {
      $locale = _sx('button', 'Add');
      $ticket = new Ticket;
      $ticket->getFromDB($tickets_id);
      $ticket_users = self::prepareTicketUser($ticket);
      $actor_type = $ticket_users[Session::getLoginUserID()];

      $all_status = Ticket::getAllowedStatusArray($ticket->fields['status']);

      if ($actor_type == CommonITILActor::REQUESTER) {
         $ticket->fields['status'] = CommonITILObject::ASSIGNED;
      }

      $html = "<div class='x-split-button' id='x-split-button'>
      <input type='submit' value='$locale' name='$action' class='x-button x-button-main'>
         <span class='x-button x-button-drop'>&nbsp;</span>
         <ul class='x-button-drop-menu'>";
      foreach ($all_status as $status_key => $status_label) {
         $checked = "";
         if ($status_key == $ticket->fields['status']) {
            $checked = "checked='checked'";
         }
         $html.= "<li><input type='radio' id='status_radio_$status_key' name='status' $checked value='$status_key'>";
         $html.= "<label for='status_radio_$status_key'>";
         $html.= "<img src='".Ticket::getStatusIconURL($status_key)."' />&nbsp;";
         $html.= $status_label;
         $html.= "</label>";
         $html.= "</li>";
      }
      $html.= "</ul>
      </div>";

      $html.= "<script type='text/javascript'>split_button();</script>";
      return $html;
   }
   
}
